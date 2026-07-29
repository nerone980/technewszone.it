<?php
// FILE: index.php — instrada verso mobile o desktop in base allo user agent
function isMobile() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool)preg_match('/(android|avantgo|blackberry|iphone|ipod|mobi|palm|phone|iemobile|opera mini)/i', $ua);
}

if (isMobile()) {
    include __DIR__ . '/view_mobile.php';
} else {
    include __DIR__ . '/view_desktop.php';
}
