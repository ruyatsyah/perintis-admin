<?php
require 'vendor/autoload.php';
$data = ['test' => '"quotes"'];
$json = json_encode($data);
echo "Normal: " . $json . "\n";
echo "Escaped once: " . e($json) . "\n";
echo "Escaped twice: " . e(e($json)) . "\n";
