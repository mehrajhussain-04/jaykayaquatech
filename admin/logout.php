<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

// Accept both a direct GET (link fallback) and the confirm-modal's POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

do_logout();
redirect('login.php');
