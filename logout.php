<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
session_destroy();
header('Location: '.SITE_URL.'/index.php');
exit;
