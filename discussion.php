<?php
require_once('database/db.php');
require_once('model/contact.php');
require_once('model/message.php');
require_once('model/user.php');

$parameters = array(
	':token' => null,
	':contact' => null
);

foreach ( $_GET as $key => $value ) {
	$parameters[":$key"] = $value;
}

$userParameters = array(
	array_shift(array_keys($parameters)) => array_shift($parameters)
);

$json = array(
	'error' => true
);

$config = require_once('database/config.php');
$db = new DB($config['dsn'], $config['username'], $config['password'], $config['options']);

$user = $db->find('User', 'user', 'token = :token', $userParameters);

if ( $user !== false ) {
	$parameters[":user"] = $user->id;
	$messages = $db->search('Message', 'message', 'contact = :contact AND user = :user OR contact = :user AND user = :contact', $parameters, 'date desc');
	
	foreach ( $messages as $message ) {
		$message->id = (int) $message->id;
		if ( $message->user == $user->id ) {
			$message->sent = true;
		}
		unset($message->id);
		unset($message->contact);
	}
	$json = array(
		'error' => false,
		'messages' => $messages
	);
}
// echo json_encode($json, JSON_PRETTY_PRINT);            5.4 required!!
echo json_encode($json);