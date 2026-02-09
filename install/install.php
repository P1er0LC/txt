<?php
const ALTUMCODE = 66;
define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
require_once ROOT_PATH . 'vendor/autoload.php';
require_once ROOT_PATH . 'app/includes/product.php';

function get_ip() {
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {

        if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim(reset($ips));
        } else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

    } else if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
        return $_SERVER['REMOTE_ADDR'];
    } else if(array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    return '';
}

$altumcode_api = 'https://api2.altumcode.com/validate';

/* Make sure the product wasn't already installed */
if(file_exists(ROOT_PATH . 'install/installed')) {
    die();
}

/* Make sure all the required fields are present */
$required_fields = ['license_key', 'database_host', 'database_name', 'database_username', 'database_password', 'installation_url'];

foreach($required_fields as $field) {
    if(!isset($_POST[$field])) {
        die(json_encode([
            'status' => 'error',
            'message' => 'One of the required fields are missing.'
        ]));
    }
}

foreach(['database_host', 'database_name', 'database_username', 'database_password'] as $key) {
    $_POST[$key] = str_replace('\'', '\\\'', $_POST[$key]);
}

/* Make sure the database details are correct */
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $database = new mysqli(
        $_POST['database_host'],
        $_POST['database_username'],
        $_POST['database_password'],
        $_POST['database_name']
    );
} catch(\Exception $exception) {
    die(json_encode([
        'status' => 'error',
        'message' => 'The database connection has failed: ' . $exception->getMessage()
    ]));
}

if($database->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'The database connection has failed!'
    ]));
}

$database->set_charset('utf8mb4');

/* BYPASS LICENSE CHECK FOR LOCAL DEVELOPMENT */
$bypass_license = true; // ⚠️ SOLO PARA DESARROLLO - Cambiar a false en producción

if($bypass_license) {
    // Simular respuesta exitosa sin validar licencia
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->status = 'success';
    
    // Generar SQL básico de instalación (las tablas se crearán después)
    $response->body->sql = "
        SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
        SET AUTOCOMMIT = 0;
        START TRANSACTION;
--         SET time_zone = '+00:00';
        
          `password` varchar(128) DEFAULT NULL,
          `name` varchar(64) DEFAULT NULL,
          `billing` text,
          `api_key` varchar(32) DEFAULT NULL,
          `token_code` varchar(32) DEFAULT NULL,
          `twofa_secret` varchar(16) DEFAULT NULL,
          `anti_phishing_code` varchar(8) DEFAULT NULL,
          `one_time_login_code` varchar(32) DEFAULT NULL,
          `pending_email` varchar(128) DEFAULT NULL,
          `email_activation_code` varchar(32) DEFAULT NULL,
          `lost_password_code` varchar(32) DEFAULT NULL,
          `type` tinyint(4) NOT NULL DEFAULT '0',
          `status` tinyint(4) NOT NULL DEFAULT '0',
          `plan_id` int(11) NOT NULL DEFAULT '0',
          `plan_expiration_date` datetime DEFAULT NULL,
          `plan_settings` text,
          `plan_trial_done` tinyint(4) DEFAULT '0',
          `plan_expiry_reminder` tinyint(4) DEFAULT '0',
          `payment_subscription_id` varchar(64) DEFAULT NULL,
          `payment_processor` varchar(16) DEFAULT NULL,
          `payment_total_amount` float DEFAULT NULL,
          `payment_currency` varchar(4) DEFAULT NULL,
          `referral_key` varchar(32) DEFAULT NULL,
          `referred_by` varchar(32) DEFAULT NULL,
          `referred_by_has_converted` tinyint(4) DEFAULT '0',
          `language` varchar(32) DEFAULT 'english',
          `timezone` varchar(32) DEFAULT 'UTC',
          `datetime` datetime DEFAULT NULL,
          `ip` varchar(64) DEFAULT NULL,
          `continent_code` varchar(8) DEFAULT NULL,
          `country` varchar(32) DEFAULT NULL,
          `city_name` varchar(32) DEFAULT NULL,
          `device_type` varchar(16) DEFAULT NULL,
          `browser_language` varchar(32) DEFAULT NULL,
          `browser_name` varchar(32) DEFAULT NULL,
          `os_name` varchar(16) DEFAULT NULL,
          `last_activity` datetime DEFAULT NULL,
          `total_logins` int(11) DEFAULT '0',
          `user_deletion_reminder` tinyint(4) DEFAULT '0',
          `source` varchar(32) DEFAULT 'direct',
          `device_fcm_token` varchar(255) DEFAULT NULL,
          `is_newsletter_subscribed` tinyint(4) DEFAULT '1',
          PRIMARY KEY (`user_id`),
          UNIQUE KEY `email` (`email`),
          KEY `plan_id` (`plan_id`),
          KEY `api_key` (`api_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- SEPARATOR --
        
        INSERT INTO `users` (`user_id`, `email`, `password`, `name`, `type`, `status`, `plan_id`, `language`, `timezone`, `datetime`, `ip`) VALUES (1, 'admin@demo.com', '$2y$10\$uFNO14fRKz1V8VhHZJMv.OBoNCHjqRHqdU5VU9I1l.YpHY8zZlXYC', 'AltumCode', 1, 1, 'custom', 'english', 'UTC', NOW(), '127.0.0.1');
        
        -- SEPARATOR --
    ";
} else {
    /* Make sure the license is correct */
    $response = \Unirest\Request::post($altumcode_api, [], [
        'type'              => 'installation',
        'license_key'       => $_POST['license_key'],
        'installation_url'  => $_POST['installation_url'],
        'product_key'       => PRODUCT_KEY,
        'product_name'      => PRODUCT_NAME,
        'product_version'   => '2.0.0',
        'server_ip'         => $_SERVER['SERVER_ADDR'],
        'client_ip'         => get_ip(),
        'newsletter_email'  => $_POST['newsletter_email'],
        'newsletter_name'   => $_POST['newsletter_name']
    ]);

    if(!isset($response->body->status)) {
        die(json_encode([
            'status' => 'error',
            'message' => $response->raw_body
        ]));
    }

    if($response->body->status == 'error') {
        die(json_encode([
            'status' => 'error',
            'message' => $response->body->message
        ]));
    }
}

/* Success check */
if($response->body->status == 'success') {

    /* Prepare the config file content */
    $config_content =
<<<ALTUM
<?php

/* Configuration of the site */
define('DATABASE_SERVER',   '{$_POST['database_host']}');
define('DATABASE_USERNAME', '{$_POST['database_username']}');
define('DATABASE_PASSWORD', '{$_POST['database_password']}');
define('DATABASE_NAME',     '{$_POST['database_name']}');
define('SITE_URL',          '{$_POST['installation_url']}');

ALTUM;

    /* Write the new config file */
    file_put_contents(ROOT_PATH . 'config.php', $config_content);

    /* Run SQL */
    $dump = array_filter(explode('-- SEPARATOR --', $response->body->sql));

    foreach($dump as $query) {
        $database->query($query);

        if($database->error) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Error when running the database queries: ' . $database->error
            ]));
        }
    }

    /* Create the installed file */
    file_put_contents(ROOT_PATH . 'install/installed', '');

    /* Determine all the languages available in the directory */
    foreach(glob(ROOT_PATH . 'app/languages/cache/*.php') as $file_path) {
        unlink($file_path);
    }

    die(json_encode([
        'status' => 'success',
        'message' => ''
    ]));
}
