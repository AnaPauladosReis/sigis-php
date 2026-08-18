<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
