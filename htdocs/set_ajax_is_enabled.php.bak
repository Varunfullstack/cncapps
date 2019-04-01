<?php
require_once($_SERVER['DOCUMENT_ROOT'] .	'/.config.php');
require_once(CONFIG_PATH_SC_CLASSES .		'ajax.php');
$ajax = & new SC_Ajax;
$ajax->request($_SERVER['REQUEST_URI']);
$_SESSION['ajax_is_enabled'] = true;
$ajax->response();
exit;
?>