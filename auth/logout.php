<?php
session_start();
session_destroy();
header('Location: /schoolm/index.php');
exit();