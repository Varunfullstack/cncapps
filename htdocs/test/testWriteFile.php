<?php

require_once '../config.inc.php';

var_dump(get_current_user());

$file = tmpfile();

$path = stream_get_meta_data($file)['uri']; // eg: /tmp/phpFx0513a

var_dump($path);