<?php

require_once __DIR__ . '/env.php';

cargarEnv(__DIR__ . '/../.env');

return [
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'username' => getenv('MAIL_USERNAME'),
    'password' => getenv('MAIL_PASSWORD'),
    'port' => getenv('MAIL_PORT') ?: 587,
    'secure' => getenv('MAIL_SECURE') ?: 'tls',
    'from_email' => getenv('MAIL_FROM_EMAIL'),
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'TicKeep'
];