<?php

/**
 * Detect popular CMS MySQL database settings and output set up DB and User script
 *
 * @author Taras Shkodenko <taras@shkodenko.com>
 * 
 */

$dirName = '';

function plmUsage() {
	echo "Script usage: php -f " . __FILE__ . " '/path/to/folder'" . PHP_EOL;
}

if( isset($argv[1]) ) {
  if( strlen($argv[1]) > 0 ) {
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
$dbName = '';
$dbUser = '';
$dbPass = '';
$dbHost = 'localhost';
$configFile = '';
$configFile = $dirName . DIRECTORY_SEPARATOR . 'wp-config.php';
if (file_exists($configFile)) {
	// WordPress CMS ?
	$fileLines = file($configFile);
	$numConfigLines = count($fileLines);
	if ($numConfigLines > 0) {
		foreach($fileLines as $line1) {
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
		$versionFile = $dirName . DIRECTORY_SEPARATOR . '/wp-includes/version.php';
		require_once $versionFile;
		echo '/* Detected WordPress CMS version '  . $wp_version . ' */' . PHP_EOL;
		$disPlayResults = 1;
	}
} else {
	// Drupal ?
	$configFile = $dirName . DIRECTORY_SEPARATOR . 'sites/default/settings.php';
	if (file_exists($configFile)) {
		require_once $configFile;
		if (isset($db_url)) {
			// Drupal 6 ?
			echo '/* Detected Drupal 6 */' . PHP_EOL;
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
				echo '/* Detected Drupal 7 */' . PHP_EOL;
				$dbName = $databases['default']['default']['database'];
				$dbUser = $databases['default']['default']['username'];
				$dbHost = $databases['default']['default']['host'];
				$dbPass = $databases['default']['default']['password'];
				if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
					$disPlayResults = 1;	
				}
			}	
		}	
	}
	// Joomla! CMS?
	$configFile = $dirName . DIRECTORY_SEPARATOR . 'configuration.php';
	if (file_exists($configFile)) {
		require_once $configFile;
		// Joomla!
		$jc1 = new JConfig;
		$version = $versionTxt = '';
		$verFile = $dirName . DIRECTORY_SEPARATOR . 'libraries/cms/version/version.php';
		if (file_exists($verFile)) {
			define('_JEXEC', 1);
			require_once $verFile;
			$jv1 = new JVersion;
			$version = $jv1->RELEASE;
		}
		if (strlen($version) > 0) {
			$versionTxt = 'version: ' . $version;
		}
		echo '/* Detected Joomla! CMS ' . $versionTxt . ' */' . PHP_EOL;
		//
		$dbHost =$jc1->host;
		$dbUser = $jc1->user;
		$dbPass = $jc1->password;
		$dbName = $jc1->db;
		if (strlen($dbName) > 0 && strlen($dbUser) > 0 && strlen($dbPass) > 0) {
			$disPlayResults = 1;	
		}
	}
}

$msgTemplate =<<<EOM

/*

Set up user and database script is:

*/

CREATE DATABASE `${dbName}` /*!40100 DEFAULT CHARACTER SET utf8 */;
GRANT ALL ON `${dbName}`.* TO '${dbUser}'@'${dbHost}' IDENTIFIED BY '${dbPass}';
FLUSH PRIVILEGES;

EOM;

if (1 == $disPlayResults) {
	echo $msgTemplate . PHP_EOL;
} else {
	echo 'Can`t generate SQL for this application type;' . PHP_EOL;
}
