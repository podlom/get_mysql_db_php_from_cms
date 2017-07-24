<?php

/**
 * Detect popular CMS MySQL database settings and output delete DB and User script
 *
 * PHP version 5
 *
 * @category PHP_CLI_Scripts
 * @package  Get_Drop_Mysql_Db
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

echo 'Checking directory: ' . $dirName . PHP_EOL;

$disPlayResults = 0;
$dbs[0] = array(
    'host' => 'localhost',
    'name' => '',
    'user' => '',
    'pass' => '',
);

require_once 'inc' . DIRECTORY_SEPARATOR . 'detect_cms.php';

$sSql = '';
$numDbs = 0;
foreach ($dbs as $db) {
    $sSql .= 'DROP DATABASE `' . $db['name'] . '`;' . PHP_EOL . 
    'DROP USER ' . "'" . $db['user'] . "'@'" . $db['host'] . "'" . ';' . PHP_EOL;
    ++ $numDbs;
}
$sHead = 'Drop database & user SQL script is:';
if ($numDbs > 1) {
    $sHead = 'Drop databases & users SQL script is:';
}
$msgTemplate =<<<EOM

/*

{$sHead}

*/

{$sSql}
FLUSH PRIVILEGES;

EOM;

if (1 == $disPlayResults) {
    echo $msgTemplate . PHP_EOL;
} else {
    echo 'Can`t generate SQL for this application type;' . PHP_EOL;
}
