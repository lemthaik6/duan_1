<?php 

session_start();

require_once './configs/env.php';
require_once './configs/helper.php';

// If Composer packages are installed, load the autoloader so libraries like PHPMailer are available
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $fileName = "$class.php";

    $fileModel = PATH_MODEL . $fileName;
    $fileController = PATH_CONTROLLER . $fileName;

    if (is_readable($fileModel)) {
        require_once $fileModel;
    } elseif (is_readable($fileController)) {
        require_once $fileController;
    }
});

// Điều hướng
require_once './routes/index.php';
