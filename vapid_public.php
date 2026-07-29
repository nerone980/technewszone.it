<?php
// FILE: vapid_public.php — fornisce la chiave pubblica VAPID al browser
require_once __DIR__ . '/push_config.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['publicKey' => VAPID_PUBLIC_KEY]);
