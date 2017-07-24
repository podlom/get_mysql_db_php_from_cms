<?php

/**
 * Detect popular CMS MySQL database settings and output set up DB and User script
 *
 * PHP version 5+
 *
 * @category PHP_CLI_Scripts
 * @package  Get_Create_Mysql_Db
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
    echo "Script usage: php " . __FILE__ . " '" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "folder'" . PHP_EOL;
}

if (isset($argv[1])) {
    if (strlen($argv[1]) > 0) {
        $dirName = $argv[1];
    } else {
        plmUsage();
        return 2;
    }
} else {
    plmUsage();
    return 3;
}

$disPlayResults = 0;
$dbs[0] = array(
    'host' => 'localhost',
    'name' => '',
    'user' => '',
    'pass' => '',
);
$commentStart = '/*';
$commentEnd = '*/';

echo $commentStart . ' Checking directory: ' . $dirName . ' ' . $commentEnd . PHP_EOL;

require_once 'inc' . DIRECTORY_SEPARATOR . 'detect_cms.php';

$sSql = '';
$numDbs = 0;
foreach ($dbs as $db) {
    $sSql .= 'CREATE DATABASE `' . $db['name'] .
    '` /*!40100 DEFAULT CHARACTER SET utf8 */;' . PHP_EOL . 
    'GRANT ALL ON `' . $db['name'] . 
    '`.* TO ' . "'" . $db['user'] . 
    "'@'" . $db['host'] . "'" . ' IDENTIFIED BY ' . 
    "'" . $db['pass'] . "'" . ';' . PHP_EOL;
    ++ $numDbs;
}
$sHead = 'Set up user and database script is:';
if ($numDbs > 1) {
    $sHead = 'Set up users and databases script is:';
}
$msgTemplate =<<<EOM

{$commentStart}
{$sHead}
{$commentEnd}

{$sSql}
FLUSH PRIVILEGES;

EOM;

if (1 == $disPlayResults) {
    echo $msgTemplate . PHP_EOL;
    return 0;
} else {
    echo $commentStart . ' Error: can`t generate SQL for this application type ' . $commentEnd . PHP_EOL;
    return 1;
}
