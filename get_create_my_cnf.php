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

$commentStart = '# ';
$commentEnd = '';

echo $commentStart . ' Checking directory: ' . $dirName . ' ' . $commentEnd . PHP_EOL;

require_once 'inc' . DIRECTORY_SEPARATOR . 'detect_cms.php';

if ($disPlayResults == 1) {
    $msgTemplate =<<<EOM
#
# Example usage in mysql, mysqldump parameter: --defaults-file=.my.cnf
# Save output below as .my.cnf file and run command: chmod -v 600 .my.cnf
#
[client]
host='{$dbs[0]['host']}'
user='{$dbs[0]['user']}'
password='{$dbs[0]['pass']}'

EOM;

    echo $msgTemplate . PHP_EOL;
    return 0;
} else {
    echo $commentStart . ' Error: can`t generate SQL for this application type ' . $commentEnd . PHP_EOL;
    return 1;
}
