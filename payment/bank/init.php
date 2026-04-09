<?php
ob_start();
session_start();
require_once __DIR__ . '/../../admin/inc/config.php';
require_once __DIR__ . '/../../admin/inc/functions.php';

// Bank transfer checkout has been removed. Keep this endpoint as a safe redirect.
safe_redirect('../../frontend/checkout.php');
