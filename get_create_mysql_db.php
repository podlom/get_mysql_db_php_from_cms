<?php

/**
 * Detect popular CMS MySQL database settings and output set up DB and User script
 *
 * @author Taras Shkodenko <taras@shkodenko.com>
 */

$dirName = '';

function plmUsage() 
{
    echo "Script usage: php -f " . __FILE__ . " '/path/to/folder'" . PHP_EOL;
}

if(isset($argv[1]) ) {
    if(strlen($argv[1]) > 0 ) {
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
$configFile = '';
$configFile = $dirName . DIRECTORY_SEPARATOR . 'wp-config.php';
if (file_exists($configFile)) {
    // WordPress CMS ?
    $fileLines = file($configFile);
    $numConfigLines = count($fileLines);
    if ($numConfigLines > 0) {
        foreach($fileLines as $line1) {
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
    if (strlen($dbs[0]['name']) > 0 && strlen($dbs[0]['user']) > 0 && strlen($dbs[0]['pass']) > 0) {
        $versionFile = $dirName . DIRECTORY_SEPARATOR . '/wp-includes/version.php';
        include_once $versionFile;
        echo '/* Detected WordPress CMS version '  . $wp_version . ' */' . PHP_EOL;
        $disPlayResults = 1;
    }
} else {
    // Drupal ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/default/settings.php';
    if (file_exists($configFile)) {
        include_once $configFile;
        echo '/* Loaded default config file ' . $configFile . ' */' . PHP_EOL;
        if (isset($db_url)) {
            // Drupal 6 ?
            echo '/* Detected Drupal 6 */' . PHP_EOL;
            $ap1 = parse_url($db_url);
            // echo 'Parsed URL: ' . var_export($ap1, 1) . PHP_EOL;
            $dbs[0]['host'] = $ap1['host'];
            $dbs[0]['name'] = substr($ap1['path'], 1);
            $dbs[0]['user'] = $ap1['user'];
            $dbs[0]['pass'] = $ap1['pass'];
            if (strlen($dbs[0]['name']) > 0 && strlen($dbs[0]['user']) > 0 && strlen($dbs[0]['pass']) > 0) {
                $disPlayResults = 1;
            }
        } else {
            if (isset($databases)) {
                // Drupal 7 ?
                echo '/* Detected Drupal 7 */' . PHP_EOL;
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
                    echo '/* Loaded Drupal multisite config file ' . $multisiteConfig . ' */' . PHP_EOL;
                    if (is_array($sites) && (count($sites) > 0)) {
                        $j = 0;
                        foreach ($sites as $domain => $configFolder) {
                            $configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/' . $configFolder . '/settings.php';
                            if (file_exists($configFile)) {
                                include_once $configFile;
                                echo '/* Loaded config file ' . $configFile . ' for (sub)domain ' . $domain . ' */' . PHP_EOL;
                                //
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
                                            echo '/* Skipped db settings because the same already exists. */' . PHP_EOL;
                                        }
                                    }
                                } else {
                                    echo '/* Databases: ' . var_export($databases, 1) . ' */';
                                }
                                //
                            }
                        }    
                    } else {
                        echo '/* Problems with reading multisite configuration */';
                    }
                }
                if (strlen($dbs[0]['name']) > 0 && strlen($dbs[0]['user']) > 0 && strlen($dbs[0]['pass']) > 0) {
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
        echo '/* Detected Joomla! CMS ' . $versionTxt . ' */' . PHP_EOL;
        //
        $dbs[0]['host'] = $jc1->host;
        $dbs[0]['name'] = $jc1->db;
        $dbs[0]['user'] = $jc1->user;
        $dbs[0]['pass'] = $jc1->password;
        if (strlen($dbs[0]['name']) > 0 && strlen($dbs[0]['user']) > 0 && strlen($dbs[0]['pass']) > 0) {
            $disPlayResults = 1;    
        }
    }
    // Magento ?
    $configFile = $dirName . DIRECTORY_SEPARATOR . 'app/etc/local.xml';
    if (file_exists($configFile)) {
        if (!function_exists('simplexml_load_file')) {
            echo 'Error: can`t work without simplexml_load_file function. Please install SimpleExml support in PHP.' . PHP_EOL;
            exit;
        }
        $xmlConfig = simplexml_load_file($configFile);
        $conn = $xmlConfig->global->resources->default_setup->connection;
        $dbs[0]['host'] = (string) $conn->host;
        $dbs[0]['name'] = (string) $conn->dbname;
        $dbs[0]['user'] = (string) $conn->username;
        $dbs[0]['pass'] = (string) $conn->password;
        //		
        if (strlen($dbs[0]['name']) > 0 && strlen($dbs[0]['user']) > 0 && strlen($dbs[0]['pass']) > 0) {
            $disPlayResults = 1;    
        }
        //
        $verFile = $dirName . DIRECTORY_SEPARATOR . 'app/Mage.php';
        if (file_exists($verFile)) {
            include_once $verFile;
            $version = Mage::getVersion();
            $versionTxt = '';
            if (strlen($version) > 0) {
                $versionTxt = 'version: ' . $version;
            }
            echo '/* Detected Magento ' . $versionTxt . ' */' . PHP_EOL;
        }
    }
}

$sSql = '';
$numDbs = 0;
foreach ($dbs as $db) {
    $sSql .= 'CREATE DATABASE `' . $db['name'] . '` /*!40100 DEFAULT CHARACTER SET utf8 */;' . PHP_EOL . 
    'GRANT ALL ON `' . $db['name'] . '`.* TO ' . "'" . $db['user']. 
    "'@'" . $db['host'] . "'" . ' IDENTIFIED BY ' . "'" . $db['pass'] . "'" . ';' . PHP_EOL;
    ++ $numDbs;
}
$sHead = 'Set up user and database script is:';
if ($numDbs > 1) {
    $sHead = 'Set up users and databases script is:';
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
