<?php

/**
 * Detect popular CMS MySQL database settings and output .my.cnf
 *
 * PHP version 5
 *
 * @category PHP_CLI_Scripts
 * @package  Get_Сreate_My_Cnf
 * @author   Taras Shkodenko <taras@shkodenko.com>
 * @license  GNU GENERAL PUBLIC LICENSE Version 2
 * @link     http://www.shkodenko.com/
 */

$dirName = '';

/**
 * Display script usage
 *
 * @return void
 */
function plmUsage() 
{
    echo "Script usage: php -f " . __FILE__ . " '/path/to/folder'" . PHP_EOL;
}

if (isset($argv[1])) {
    if (strlen($argv[1]) > 0) {
        $dirName = $argv[1];
    } else {
        plmUsage();
        exit;
    }
} else {
    plmUsage();
    exit;
}

echo '# Checking directory: ' . $dirName . PHP_EOL;

$disPlayResults = 0;
$dbName = '';
$dbUser = '';
$dbPass = '';
$dbHost = 'localhost';
$configFile = '';
$configFile = $dirName . DIRECTORY_SEPARATOR . 'wp-config.php';
if (file_exists($configFile)) {
    // WordPress CMS ?
    echo '# Detected WordPress CMS' . PHP_EOL;
    $fileLines = file($configFile);
    $numConfigLines = count($fileLines);
    if ($numConfigLines > 0) {
        foreach ($fileLines as $line1) {
            if (preg_match("/define\('DB_NAME', '(.+)'\);/m", $line1, $m1)) {
                $dbName = $m1[1];
            }
            if (preg_match("/define\('DB_USER', '(.+)'\);/m", $line1, $m2)) {
                $dbUser = $m2[1];
            }
            if (preg_match("/define\('DB_HOST', '(.+)'\);/m", $line1, $m3)) {
                $dbHost = $m3[1];
            }
            if (preg_match("/define\('DB_PASSWORD', '(.+)'\);/m", $line1, $m3)) {
                $dbPass = $m3[1];
            }
        }
    }
    if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
        $disPlayResults = 1;
    }
} else {
    // Drupal ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/default/settings.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        if (isset($db_url)) {
            // Drupal 6 ?
            echo '# Detected Drupal 6' . PHP_EOL;
            $ap1 = parse_url($db_url);
            // echo 'Parsed URL: ' . var_export($ap1, 1) . PHP_EOL;
            $dbName = substr($ap1['path'], 1);
            $dbUser = $ap1['user'];
            $dbHost = $ap1['host'];
            $dbPass = $ap1['pass'];
            if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
                $disPlayResults = 1;
            }
        } else {
            if (isset($databases)) {
                // Drupal 7 ?
                echo '# Detected Drupal 7' . PHP_EOL;
                $dbName = $databases['default']['default']['database'];
                $dbUser = $databases['default']['default']['username'];
                $dbHost = $databases['default']['default']['host'];
                $dbPass = $databases['default']['default']['password'];
                if (strlen($dbName) > 0 
                    && strlen($dbUser) > 0 
                    && strlen($dbPass) > 0
                ) {
                    $disPlayResults = 1;    
                }
            }    
        }    
    }
    // Joomla! CMS?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'configuration.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        // Joomla!
        echo '# Detected Joomla! CMS' . PHP_EOL;
        $jc1 = new JConfig;
        $dbHost = $jc1->host;
        $dbUser = $jc1->user;
        $dbPass = $jc1->password;
        $dbName = $jc1->db;
        if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
            $disPlayResults = 1;    
        }
    }
    // Magento ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'app/etc/local.xml';
    if (file_exists($configFile)) {
        if (!function_exists('simplexml_load_file')) {
            echo 'Error: can`t work without simplexml_load_file function.' .
                ' Please install SimpleExml support in PHP.' . PHP_EOL;
            exit;
        }
        $xmlConfig = simplexml_load_file($configFile);
        echo '# Detected Magento' . PHP_EOL;
        $conn = $xmlConfig->global->resources->default_setup->connection;
        $dbHost = (string) $conn->host;
        $dbUser = (string) $conn->username;
        $dbPass = (string) $conn->password;
        $dbName = (string) $conn->dbname;
        if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
            $disPlayResults = 1;    
        }
    }
}

$msgTemplate =<<<EOM
#
# Example usage in mysql, mysqldump parameter: --defaults-file=.my.cnf
# Save output below as .my.cnf file and run command: chmod 600 .my.cnf
#
[client]
host='${dbHost}'
user='${dbUser}'
password='${dbPass}'

EOM;

if (1 == $disPlayResults) {
    echo $msgTemplate . PHP_EOL;
} else {
    echo 'Can`t generate .my.cnf for this application type;' . PHP_EOL;
}
