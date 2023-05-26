<?php
$_ENV['test888'] = "index test";
require(__DIR__ . "/../vendor/autoload.php");

echo "<pre>";
ChaosWD\Controller\VariableController::process(__DIR__);

print_r($_ENV);

echo "</pre>";