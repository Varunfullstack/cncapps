<?php
/**
*	Initialise and register any session variables in here
* i.e. variables whose value you wish to preserve for the life of the session
*/
global $loginMessage;
$loginMessage='';
$sess->register('loginMessage');
?>