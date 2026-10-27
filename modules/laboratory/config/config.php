<?php 
    
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
    // define('CURRENT_URI',basename($_SERVER['REQUEST_URI']));
    define('CURRENT_URI', isset($segments[0]) ? $segments[0] : '');
    define('CONFIG_PATH',BASE_URL.'/config');
