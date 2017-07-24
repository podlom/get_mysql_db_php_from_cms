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
$dbs[0] = array(
    'host' => 'localhost',
    'name' => '',
    'user' => '',
    'pass' => '',
);

require_once 'inc' . DIRECTORY_SEPARATOR . 'detect_cms.php';

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
