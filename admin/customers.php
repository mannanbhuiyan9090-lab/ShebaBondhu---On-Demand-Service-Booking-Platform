<?php
require_once __DIR__.'/../includes/config.php';
sessionStart(); requireAuth('admin');
header('Location: '.SITE_URL.'/admin/users.php?role=customer');
exit;
