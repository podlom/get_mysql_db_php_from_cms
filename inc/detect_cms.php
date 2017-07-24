<?php

/**
 * Detect popular CMS MySQL database settings
 *
 * PHP version 5
 *
 * @category PHP_CLI_Scripts
 * @package  Get_Drop_Mysql_Db
 * @author   Taras Shkodenko <taras@shkodenko.com>
 * @license  GNU GENERAL PUBLIC LICENSE Version 2
 * @link     http://www.shkodenko.com/
 */

global $dbs, $disPlayResults, $commentStart, $commentEnd;

$configFile = $dirName . DIRECTORY_SEPARATOR . 'wp-config.php';
if (file_exists($configFile)) {
    // WordPress CMS ?
    $fileLines = file($configFile);
    $numConfigLines = count($fileLines);
    if ($numConfigLines > 0) {
        foreach ($fileLines as $line1) {
            if (preg_match("/define\('DB_HOST', '(.+)'\);/m", $line1, $m3)) {
                $dbs[0]['host'] = $m3[1];
            }
            if (preg_match("/define\('DB_NAME', '(.+)'\);/m", $line1, $m1)) {
                $dbs[0]['name'] = $m1[1];
            }
            if (preg_match("/define\('DB_USER', '(.+)'\);/m", $line1, $m2)) {
                $dbs[0]['user'] = $m2[1];
            }
            if (preg_match("/define\('DB_PASSWORD', '(.+)'\);/m", $line1, $m3)) {
                $dbs[0]['pass'] = $m3[1];
            }
        }
    }
    if (strlen($dbs[0]['name']) > 0
        && strlen($dbs[0]['user']) > 0
    ) {
        $versionFile = $dirName . DIRECTORY_SEPARATOR . '/wp-includes/version.php';
        include_once $versionFile;
        echo $commentStart . ' Detected WordPress CMS version '  . $wp_version . ' ' . $commentEnd . PHP_EOL;
        $disPlayResults = 1;
    }
} else {
    // Drupal CMF ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/default/settings.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        echo '/* Loaded default config file ' . $configFile . ' */' . PHP_EOL;
        if (isset($db_url)) {
            // Drupal 6 ?
            echo '/* Detected Drupal 6 */' . PHP_EOL;
            $ap1 = parse_url($db_url);
            $dbs[0]['host'] = $ap1['host'];
            $dbs[0]['name'] = substr($ap1['path'], 1);
            $dbs[0]['user'] = $ap1['user'];
            $dbs[0]['pass'] = $ap1['pass'];
            if (strlen($dbs[0]['name']) > 0
                && strlen($dbs[0]['user']) > 0
            ) {
                $disPlayResults = 1;
            }
        } else {
            if (isset($databases)) {
                // Drupal 7+ ?
                echo $commentStart . ' Detected Drupal 7+ ' . ' ' . $commentEnd . PHP_EOL;
                if (count($databases > 0)) {
                    $i = 0;
                    foreach ($databases as $aDb) {
                        $dbs[$i]['host'] = $aDb['default']['host'];
                        $dbs[$i]['name'] = $aDb['default']['database'];
                        $dbs[$i]['user'] = $aDb['default']['username'];
                        $dbs[$i]['pass'] = $aDb['default']['password'];
                        ++ $i;
                    }
                } else {
                    $dbs[0]['host'] = $databases['default']['default']['host'];
                    $dbs[0]['name'] = $databases['default']['default']['database'];
                    $dbs[0]['user'] = $databases['default']['default']['username'];
                    $dbs[0]['pass'] = $databases['default']['default']['password'];
                }
                $multisiteConfig = $dirName . DIRECTORY_SEPARATOR . 'sites/sites.php';
                if (file_exists($multisiteConfig)) {
                    include_once $multisiteConfig;
                    echo $commentStart . ' Loaded Drupal multisite config file ' . $multisiteConfig . ' ' . $commentEnd . PHP_EOL;
                    if (is_array($sites) && (count($sites) > 0)) {
                        $j = 0;
                        foreach ($sites as $domain => $configFolder) {
                            $configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/' . $configFolder . '/settings.php';
                            if (file_exists($configFile)) {
                                include_once $configFile;
                                echo $commentStart . ' Loaded config file ' . $configFile . ' for (sub)domain ' . $domain . ' ' . $commentEnd . PHP_EOL;
                                if (count($databases > 0)) {
                                    foreach ($databases as $aDb) {
                                        if (($dbs[0]['host'] !== $aDb['default']['host'])
                                            && ($dbs[0]['name'] !== $aDb['default']['database'])
                                            && ($dbs[0]['user'] !== $aDb['default']['username'])
                                            && ($dbs[0]['pass'] !== $aDb['default']['password'])
                                        ) {
                                            ++ $j;
                                            $dbs[$j]['host'] = $aDb['default']['host'];
                                            $dbs[$j]['name'] = $aDb['default']['database'];
                                            $dbs[$j]['user'] = $aDb['default']['username'];
                                            $dbs[$j]['pass'] = $aDb['default']['password'];
                                        } else {
                                            echo $commentStart . ' Skipped db settings because the same already exists. ' . $commentEnd . PHP_EOL;
                                        }
                                    }
                                } else {
                                    echo $commentStart . ' Databases: ' . var_export($databases, 1) . ' ' . $commentEnd . PHP_EOL;
                                }
                            }
                        }
                    } else {
                        echo $commentStart . ' Problems with reading multisite configuration ' . $commentEnd . PHP_EOL;
                    }
                }
                if (strlen($dbs[0]['name']) > 0
                    && strlen($dbs[0]['user']) > 0
                ) {
                    $disPlayResults = 1;
                }
            }
        }
    }
    // Yii 2+ Framework basic application template?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db.php';
    if (file_exists($configFile)) {
        echo $commentStart . ' Yii 2+ Framework basic application template DB config: ' . $configFile . ' ' . $commentEnd . PHP_EOL;
        $dbParams = require $configFile;
        if (isset($dbParams['dsn'])) {
            $p1 = explode(':', $dbParams['dsn']);
            if ($p1[0] == 'mysql') {
                $p2 = explode(';', $p1[1]);
                if (count($p2) == 2) {
                    $p3 = explode('=', $p2[0]);
                    if ($p3[0] == 'host') {
                        $dbs[0]['host'] = $p3[1];
                    } elseif ($p3[0] == 'dbname') {
                        $dbs[0]['name'] = $p3[1];
                    }
                    $p4 = explode('=', $p2[1]);
                    if ($p4[0] == 'dbname') {
                        $dbs[0]['name'] = $p4[1];
                    } elseif ($p4[0] == 'host') {
                        $dbs[0]['host'] = $p4[1];
                    }
                    $dbs[0]['user'] = $dbParams['username'];
                    $dbs[0]['pass'] = $dbParams['password'];
                    if (strlen($dbs[0]['host']) > 0
                        && strlen($dbs[0]['name']) > 0
                        && strlen($dbs[0]['user']) > 0
                    ) {
                        $disPlayResults = 1;
                    }
                }
            } else {
                echo $commentStart . ' Error: can`t generate script for non-MySQL database: ' . $p1[0] . ' ' . $commentEnd . PHP_EOL;
            }
        }
    }
    // Yii 2+ Framework basic advanced template?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main-local.php';
    if (file_exists($configFile)) {
        echo $commentStart . ' Yii 2+ Framework advanced application template DB config: ' . $configFile . ' ' . $commentEnd . PHP_EOL;
        $dbParams = require $configFile;
        if (isset($dbParams['components']['db']['dsn'])) {
            $p1 = explode(':', $dbParams['components']['db']['dsn']);
            if ($p1[0] == 'mysql') {
                $p2 = explode(';', $p1[1]);
                if (count($p2) == 2) {
                    $p3 = explode('=', $p2[0]);
                    if ($p3[0] == 'host') {
                        $dbs[0]['host'] = $p3[1];
                    } elseif ($p3[0] == 'dbname') {
                        $dbs[0]['name'] = $p3[1];
                    }
                    $p4 = explode('=', $p2[1]);
                    if ($p4[0] == 'dbname') {
                        $dbs[0]['name'] = $p4[1];
                    } elseif ($p4[0] == 'host') {
                        $dbs[0]['host'] = $p4[1];
                    }
                    $dbs[0]['user'] = $dbParams['components']['db']['username'];
                    $dbs[0]['pass'] = $dbParams['components']['db']['password'];
                    if (strlen($dbs[0]['host']) > 0
                        && strlen($dbs[0]['name']) > 0
                        && strlen($dbs[0]['user']) > 0
                    ) {
                        $disPlayResults = 1;
                    }
                }
            } else {
                echo $commentStart . ' Error: can`t generate script for non-MySQL database: ' . $p1[0] . ' ' . $commentEnd . PHP_EOL;
            }
        }
    }
    // Joomla! CMS?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'configuration.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        $jc1 = new JConfig;
        $version = $versionTxt = '';
        $verFile = $dirName . DIRECTORY_SEPARATOR . 'libraries/cms/version/version.php';
        if (file_exists($verFile)) {
            define('_JEXEC', 1);
            include_once $verFile;
            $jv1 = new JVersion;
            $version = $jv1->RELEASE;
        }
        if (strlen($version) > 0) {
            $versionTxt = 'version: ' . $version;
        }
        echo $commentStart . ' Detected Joomla! CMS ' . $versionTxt . ' ' . $commentEnd . PHP_EOL;
        $dbs[0]['host'] = $jc1->host;
        $dbs[0]['name'] = $jc1->db;
        $dbs[0]['user'] = $jc1->user;
        $dbs[0]['pass'] = $jc1->password;
        if (strlen($dbs[0]['name']) > 0
            && strlen($dbs[0]['user']) > 0
        ) {
            $disPlayResults = 1;
        }
    }
    // Magento ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'app/etc/local.xml';
    if (file_exists($configFile)) {
        if (!function_exists('simplexml_load_file')) {
            echo 'Error: can`t work without simplexml_load_file function.' .
                ' Please install SimpleXml support in PHP.' . PHP_EOL;
            return 4;
        }
        $xmlConfig = simplexml_load_file($configFile);
        $conn = $xmlConfig->global->resources->default_setup->connection;
        $dbs[0]['host'] = (string) $conn->host;
        $dbs[0]['name'] = (string) $conn->dbname;
        $dbs[0]['user'] = (string) $conn->username;
        $dbs[0]['pass'] = (string) $conn->password;
        if (strlen($dbs[0]['name']) > 0
            && strlen($dbs[0]['user']) > 0
        ) {
            $disPlayResults = 1;
        }
        $verFile = $dirName . DIRECTORY_SEPARATOR . 'app/Mage.php';
        if (file_exists($verFile)) {
            include_once $verFile;
            $version = Mage::getVersion();
            $versionTxt = '';
            if (strlen($version) > 0) {
                $versionTxt = 'version: ' . $version;
            }
            echo $commentStart . ' Detected Magento ' . $versionTxt . ' ' . $commentEnd . PHP_EOL;
        }
    }
}
