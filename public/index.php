<?php
// Buffer sejak entry point publik, sebelum front controller atau view mengeluarkan output.
if (ob_get_level() === 0) { ob_start(); }
// Semua request aplikasi diarahkan ke front controller utama agar routing konsisten.
require dirname(__DIR__) . '/index.php';
