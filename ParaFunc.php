<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

function trackerName()
{
	//If you modify this project, please change this value to something of your own, as a courtesy to your users and ours
	return "ParaTracker";
}

function trackerVersion()
{
	//Return the version number
	return 1.5;
}

function versionNumber()
{
	//I know this function's name is cryptic, but it existed long before trackerName() and trackerVersion() and I'm not changing all the rest of the code to match...
	return(trackerName() . " " . strval(trackerVersion()));
}

define('webServerName', getWebServerName());

//Define the default skin, to be used throughout this file.
//This variable must reference an actual .css file in the skins folder, or it will break stuff. Do not include the file path or extension!
//"JSON" is also a valid value.
//Default value is "Metallic Console"
$defaultSkin = "Metallic Console";


if (!isset($safeToExecuteParaFunc))
{
	displayError("ParaFunc.php is a library file and can not be run directly!<br />Try running ParaTrackerStatic.php or ParaTrackerDynamic.php instead.", time(), "");
	exit();
}

//Now that validation is complete, declare this value
if(!isset($analyticsBackground))
{
	$analyticsBackground = 0;
}
define("analyticsBackground", $analyticsBackground);

//If we are running from within analytics, the client address will not exist. Let's give the error log something useful
if(analyticsBackground)
{
	define("clientAddress", "Analytics");
}
else
{
	define("clientAddress", $_SERVER['REMOTE_ADDR']);
}

//This block is here to suppress error messages
$trackerTagline = "";
$fileExtList = array();
$dynamicIPAddressPath = "";
$serverIPAddress = "";
$serverPort = "";
$personalDynamicTrackerMessage = "";
$floodProtectTimeout = "";
$loginFloodProtect = 5;
$executeDynamicInstructionsPage = "0";
$backgroundColor = "";
$backgroundOpacity = "100";
$textColor = "";
$playerListColor1 = "";
$playerListColor1Opacity = "100";
$playerListColor2 = "";
$playerListColor2Opacity = "100";
$scrollShaftColor = "";
$scrollThumbColor = "";
$scrollShaftOpacity = "100";
$customFont = "";
$customSkin = "";
$geoipCountryCode = "";
$paraTrackerSkin = "";
$displayGameName = "";
$pgUser = "";
$pgPass = "";
$pgName = "";
$pgHost = "";
$pgPort = "";
$enablePGSQL = "0";
$webServerDomain = "";
$emailEnabled = "";
$useSMTP = "";
$smtpAddress = "";
$smtpPort = "";
$SMTPSecure = true;
$smtpUsername = "";
$smtpPassword = "";
$emailFromAddress = "";
$serverToolsAllowEmailAdministrators = "";
$emailAdministrators = array();
$emailAdminReports = "";
$cleanupInterval = "";
$deleteInterval = "";
$loadLimit = "";
$cleanupLogSize = "";
$errorLogSize = "";
$mapreqEnabled = "";
$analyticsEnabled = "";
$analyticsFrontEndEnabled = "";
$mapreqTextMessage = "";
$cullDatabase = 0;
$databaseCullTime = 0;
$connectionAttempts = 2;
$enableCustomDefaultLevelshots = 0;
$customDefaultLevelshot = "";


//For safety, these MUST be forced to an initial value!
$logPath = "logs";
$infoPath = "info";
$skinsPath = "skins";
$utilitiesPath = "utilities";

//Declare these too
$serverSettingsFilename = "serverSettings.php";
$serverSecurityLogFilename = "SecurityLog.php";

//The default skin must be defined here, before skin validation takes place
define("defaultSkin", $defaultSkin);

//If this file is executed directly, then echoing this value here will display the version number before exiting.
//Either way, the version number will be visible.
echo "<!-- " . versionNumber() . " ";

if (file_exists("ParaConfig.php"))
{
	include_once 'ParaConfig.php';
}
else
{
	writeNewConfigFile();

	//This must be called before calling file_exists!
	clearstatcache();

	if (file_exists("ParaConfig.php"))
	{
		echo "<!-- --><h3>ParaConfig.php not found! A default config file has been written to disk.<br />Please add an IP Address and port to it.</h3>";
		exit();
	}
}

// Make sure this is an array so things don't bug out later...
if(!is_array($emailAdministrators)) {
	$emailAdministrators = array($emailAdministrators);
}
define("emailAdministrators", $emailAdministrators);

if(file_exists("GameInfo.php"))
{
	$safeToExecuteGameInfo = "1";
	include("GameInfo.php");
}
else
{
	displayError( "GameInfo.php not found!", "", "");
	exit();
}

//Validate the log stuff so it can be used later
$logPath = trim($logPath, '/') . '/';
$infoPath = trim($infoPath, '/') . '/';
$skinsPath = trim($skinsPath, '/') . '/';
$utilitiesPath = trim($utilitiesPath, '/') . '/';

$serverSettingsFilename = trim($serverSettingsFilename);
$serverSecurityLogFilename = trim($serverSecurityLogFilename);

define("logPath", $logPath);
define("infoPath", $infoPath);
define("skinsPath", $skinsPath);
define("utilitiesPath", $utilitiesPath);

define("serverSettingsFilename" , $serverSettingsFilename);
define("serverSecurityLogFilename" , $serverSecurityLogFilename);

//This is an array of log file names that are permitted to be displayed.
$permittedLogFiles = array("cleanupLog.php", "errorLog.php", serverSecurityLogFilename);
define("permittedLogFiles", $permittedLogFiles);

$errorLogSize = numericValidator($errorLogSize, 100, 100000, 10000);
define("errorLogSize", $errorLogSize);

//These IF statements will avoid warning messages during validation
if(!isset($dynamicTrackerCalledFromCorrectFile))
{
	$dynamicTrackerCalledFromCorrectFile = "0";
}
if(!isset($calledFromRCon))
{
	$calledFromRCon = "0";
}
if(!isset($calledFromParam))
{
	$calledFromParam = "0";
}
if(!isset($calledFromElsewhere))
{
	$calledFromElsewhere = "0";
}
if(!isset($calledFromAnalytics))
{
	$calledFromAnalytics = "0";
}


/*
Before we go any further, let's validate ALL input from the config file!
To validate booleans:
$variableName = booleanValidator($variableName, defaultValue);

To evaluate numeric values:
$variableName = numericValidator($variableName, minValue, maxValue, defaultValue);

To evaluate strings:
$variableName = stringValidator($variableName, maxLength, defaultValue);

To check a value for null:
$variableName = basicValidator($variableName, defaultValue);
*/

//These values MUST be evaluated first, because they are used in the IP address validation.
//All ParaTracker files call this same file, so we need to be sure which file is calling,
//and what to do about it.
$dynamicTrackerCalledFromCorrectFile = booleanValidator($dynamicTrackerCalledFromCorrectFile, 0);
$dynamicTrackerEnabled = booleanValidator($dynamicTrackerEnabled, 0);

if($dynamicTrackerEnabled == "1")
{
	$serverToolsEnabled = booleanValidator($serverToolsEnabled, 1);
}
else
{
	$serverToolsEnabled = 0;
}

define("dynamicTrackerEnabled", $dynamicTrackerEnabled);
define("serverToolsEnabled", $serverToolsEnabled);

$enablePGSQL = booleanValidator($enablePGSQL, 0);
if ($enablePGSQL == "1")
{
	if (!extension_loaded('pgsql'))
	{
		echo ' pgsql extension not enabled in php.ini ';
		$enablePGSQL = 0;
	}
}
define("enablePGSQL", $enablePGSQL);


$analyticsEnabled = booleanValidator($analyticsEnabled, 0);
if(enablePGSQL == "0")
{
	$analyticsEnabled = "0";
}
define("analyticsEnabled", $analyticsEnabled);

if(analyticsEnabled == 0)
{
	$analyticsFrontEndEnabled = 0;
}
else
{
	$analyticsFrontEndEnabled = booleanValidator($analyticsFrontEndEnabled, 1);
}

define("analyticsFrontEndEnabled", $analyticsFrontEndEnabled);


if($dynamicTrackerCalledFromCorrectFile == "1" && $calledFromAnalytics == "0")
{
	if(dynamicTrackerEnabled == "1")
	{
		if(isset($_GET["ip"]))
		{
			$serverIPAddress = $_GET["ip"];
		}
		else
		{
			//Terminate the script with an instruction page if no IP address was given!
			$executeDynamicInstructionsPage = "1";
		}
	}
	else
	{
		displayError(trackerName() . ' Dynamic mode is disabled! Dynamic mode must be enabled in ParaConfig.php.', "", "");
		exit();
	}

	if($executeDynamicInstructionsPage == "0")
	{
		if(isset($_GET["port"]))
		{
			$serverPort = $_GET["port"];
		}
	}
}
else
{
	//Was this file caled from RCon.php? If so, we can't execute the instructions page.
	if(dynamicTrackerEnabled == "1" && $calledFromRCon == "1")
	{
		//We were called from RCon.php, we are running in dynamic mode, and no IP address was given.
		if(isset($_GET["ip"]))
		{
			$serverIPAddress = $_GET["ip"];
		}
		else
		{
			if(!isset($serverIPAddress))
			{
				displayError("Cannot use RCon without an IP address!", "", "");
			}
		}
		if(isset($_GET["port"]))
		{
			$serverPort = $_GET["port"];
		}
	}
	elseif($calledFromAnalytics == "1")
	{
		if(analyticsEnabled != "1")
		{
			displayError("Analytics is disabled!<br>Analytics must be enabled in ParaConfig.php!", "", "");
			exit();
		}
		//We were called from analytics. Get the IP and port.
		if(isset($_GET["ip"]))
		{
			$serverIPAddress = $_GET["ip"];
		}
		if(isset($_GET["port"]))
		{
			$serverPort = $_GET["port"];
		}
	}
}

//Before initializing, let's validate the database stuff
$pgUser = basicValidator($pgUser, "postgres");
$pgPass = basicValidator($pgPass, "");
$pgName = basicValidator($pgName, strtolower(trackerName()));
$pgHost = basicValidator($pgHost, "localhost");
$pgPort = basicValidator($pgPort, "");

define("pgUser", $pgUser);
define("pgPass", $pgPass);
define("pgName", $pgName);
define("pgHost", $pgHost);
define("pgPort", $pgPort);
$pgCon = null;

$admin = false;
$adminUser = "";

$temp = ipAndPortValidator($serverIPAddress, $serverPort, dynamicTrackerEnabled);
$serverIPAddress = "$temp[0]";
$serverPort = $temp[1];

if($executeDynamicInstructionsPage == "0" && $calledFromAnalytics == "0" && $calledFromElsewhere == "0")
{
	$paraTrackerSkin = skinValidator($paraTrackerSkin, $customSkin);
}
else
{
	//This line prevents a skin file from being mistakenly applied to the dynamic instructions page or the analytics page.
	$paraTrackerSkin = "";
}

if (enablePGSQL)
{
	global $pgCon;
	$connectString = 'host=' . pgHost . ' dbname=' . pgName . ' user=' . pgUser;
	if (!empty(pgPass)) $connectString .= " password=" . pgPass;
	if (!empty(pgPort)) $connectString .= " port=" . pgPort;
	$pgCon = pg_connect($connectString);
	if (!$pgCon)
	{
		displayError("Could not establish database connection", time(), "");
		exit();
	}

	pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS account')
		or displayError('could not create account schema', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS account.user (id BIGSERIAL PRIMARY KEY, username VARCHAR(64) UNIQUE NOT NULL, email TEXT UNIQUE, passhash VARCHAR(128) NOT NULL, salt VARCHAR(16) NOT NULL)')
		or displayError('could not create account.user table', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS account.session (user_id BIGSERIAL PRIMARY KEY REFERENCES account.user (id) ON UPDATE CASCADE ON DELETE CASCADE, token VARCHAR(64) NOT NULL, expires TIMESTAMP NOT NULL)')
		or displayError('could not create account.session table', time(), "");
		
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS mapreq (
			id BIGSERIAL PRIMARY KEY,
			game_name VARCHAR(128) NOT NULL CHECK (game_name <> \'\'),
			bsp_name VARCHAR(128) NOT NULL CHECK (bsp_name <> \'\'),
			dl_link TEXT CHECK (dl_link <> \'\'),
			entry_date TIMESTAMP DEFAULT NOW(),
			email TEXT,
			useradded BOOL NOT NULL DEFAULT false,
			UNIQUE(game_name, bsp_name)
			)
		') or displayError('could not create map request (mapreq) table', time(), "");
		
	pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS tracker')
		or displayError('could not create tracker schema', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.server (
			id BIGSERIAL PRIMARY KEY,
			location VARCHAR(128) NOT NULL,
			port INT NOT NULL CHECK (port > 0 AND port < 65536),
			active BOOL NOT NULL DEFAULT TRUE,
			UNIQUE( location, port )
			)
		') or displayError('could not create server (tracker.server) table', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.cpuload (
			entrydate TIMESTAMP PRIMARY KEY DEFAULT NOW(),
			load REAL NOT NULL
			)
		') or displayError('could not create server (tracker.cpuload) table', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.displayerror (
			entrydate TIMESTAMP PRIMARY KEY DEFAULT NOW()
			)
		') or displayError('could not create server (tracker.displayerror) table', time(), "");
		
	pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS analytics')
		or displayError('could not create analytics schema', time(), "");
		
	/* gamename */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.gamename (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create gamename (analytics.gamename) table', time(), "");
		
	/* hostname */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.hostname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create hostname (analytics.hostname) table', time(), "");
		
	/* mapname */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.mapname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create mapname (analytics.mapname) table', time(), "");
		
	/* modname */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.modname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create modname (analytics.modname) table', time(), "");
		
	/* gametype */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.gametype (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create gametype (analytics.gametype) table', time(), "");
		
	/* server record */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.record (
				id BIGSERIAL PRIMARY KEY,
				gamename_id BIGINT NOT NULL REFERENCES analytics.gamename (id) ON UPDATE CASCADE ON DELETE CASCADE,
				hostname_id BIGINT NOT NULL REFERENCES analytics.hostname (id) ON UPDATE CASCADE ON DELETE CASCADE,
				mapname_id BIGINT NOT NULL REFERENCES analytics.mapname (id) ON UPDATE CASCADE ON DELETE CASCADE,
				modname_id BIGINT NOT NULL REFERENCES analytics.modname (id) ON UPDATE CASCADE ON DELETE CASCADE,
				gametype_id BIGINT NOT NULL REFERENCES analytics.gametype (id) ON UPDATE CASCADE ON DELETE CASCADE,
				player_count INT NOT NULL
			)
		') or displayError('could not create gamename record (analytics.record) table', time(), "");
		
	/* frame */
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.frame (
				id BIGSERIAL PRIMARY KEY,
				server_id BIGINT NOT NULL REFERENCES tracker.server (id) ON UPDATE CASCADE ON DELETE CASCADE,
				entrydate TIMESTAMP NOT NULL DEFAULT NOW(),
				record_id BIGINT REFERENCES analytics.record (id) ON UPDATE CASCADE ON DELETE CASCADE
			)
		') or displayError('could not create uptime (analytics.frame) table', time(), "");
	pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.runtimes (
				startdate TIMESTAMP PRIMARY KEY,
				enddate TIMESTAMP NOT NULL DEFAULT NOW()
			)
		') or displayError('could not create uptime (analytics.runtimes) table', time(), "");
		
	$admin = adminCheck();
}

if($dynamicTrackerCalledFromCorrectFile == "1" || $calledFromParam == "1" || $calledFromRCon == "1")
{
	//We are running in Dynamic mode.
	//Check to see if a skin file was specified in the URL.
	if(isset($_GET["skin"]))
	{
		//A skin was specified - load it in.
		$paraTrackerSkin = rawurldecode($_GET["skin"]);

		if(trim(strtolower($paraTrackerSkin)) == "custom" && isset($_GET["customSkin"]))
		{
			//A custom skin was specified - load it in as well.
			$customSkin = rawurldecode($_GET["customSkin"]);
			$customSkin = skinValidator($paraTrackerSkin, $customSkin);
		}
		else
		{
			$paraTrackerSkin = skinValidator($paraTrackerSkin, "");
		}
	}
}

$connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2);

$trackerTagline = stringValidator($trackerTagline, "", "");

//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$floodProtectTimeout = numericValidator($floodProtectTimeout, 2, 60, 5);
$floodProtectTimeout = numericValidator($floodProtectTimeout, $connectionTimeout, 1200, $floodProtectTimeout);

$loginFloodProtect = numericValidator($loginFloodProtect, 2, 15, 5);

$connectionAttempts = numericValidator($connectionAttempts, 1, 10, 2);

$refreshTimeout = numericValidator($refreshTimeout, 1, 15, 3);

$backgroundColor = colorValidator($backgroundColor);
$backgroundOpacity = numericValidator($backgroundOpacity, 0, 100, 100);
$playerListColor1 = colorValidator($playerListColor1);
$playerListColor1Opacity = numericValidator($playerListColor1Opacity, 0, 100, 100);
$playerListColor2 = colorValidator($playerListColor2);
$playerListColor2Opacity = numericValidator($playerListColor2Opacity, 0, 100, 100);
$scrollShaftColor = colorValidator($scrollShaftColor);
$scrollThumbColor = colorValidator($scrollThumbColor);
//$scrollShaftOpacity = numericValidator($scrollShaftOpacity, 0, 100, 100);

$textColor = colorValidator($textColor);
$customFont = stringValidator($customFont, "50", "");

$levelshotTransitionsEnabled = booleanValidator($levelshotTransitionsEnabled, 1);
$levelshotDisplayTime = numericValidator($levelshotDisplayTime, 1, 15, 3);
$levelshotTransitionTime = numericValidator($levelshotTransitionTime, 0.1, 5, 1);
$levelshotTransitionAnimation = numericValidator($levelshotTransitionAnimation, 0, 32767, 3);
$maximumLevelshots = numericValidator($maximumLevelshots, 1, 99, 20);

$displayGameName = booleanValidator($displayGameName, 1);
$filterOffendingServerNameSymbols = booleanValidator($filterOffendingServerNameSymbols, 1);

$noPlayersOnlineMessage = stringValidator($noPlayersOnlineMessage, "", "No players online.");

$enableAutoRefresh = booleanValidator($enableAutoRefresh, 1);

//Have to validate this one twice to make sure it isn't lower than the floodprotect limit
$autoRefreshTimer = numericValidator($autoRefreshTimer, 10, 300, 30);
$autoRefreshTimer = intval(numericValidator($autoRefreshTimer, $floodProtectTimeout, 300, $autoRefreshTimer));

$maximumServerInfoSize = numericValidator($maximumServerInfoSize, 2000, 50000, 16384);

$RConEnable = booleanValidator($RConEnable, 0);
$RConMaximumMessageSize = numericValidator($RConMaximumMessageSize, 20, 10000, 200);

$RConFloodProtect = numericValidator($RConFloodProtect, 5, 20, 10);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$RConFloodProtect = numericValidator($RConFloodProtect, $connectionTimeout, 3600, 20);
$SecurityLogSize = numericValidator($SecurityLogSize, 100, 100000, 2000);

$cleanupInterval = numericValidator($cleanupInterval, 10, 3600, 60);
$deleteInterval = numericValidator($deleteInterval, 1, 30, 7);

//Need to make sure deleteInterval is greater than cleanupInterval.
//cleanupInterval is given in minutes, and deleteInterval is given in days, so divide by 1440.
$deleteInterval = numericValidator($deleteInterval, $cleanupInterval / 1440, 30, 7);

define('deleteInterval', $deleteInterval);

$loadLimit = numericValidator($loadLimit, 50, 100, 90);
$cleanupLogSize = numericValidator($cleanupLogSize, 100, 100000, 10000);

$mapreqEnabled = booleanValidator($mapreqEnabled, 0);


$cullDatabase = booleanValidator($cullDatabase, 0);
//If analytics is disabled, cullDatabase must also be disabled
if($analyticsEnabled != 1)
{
	$cullDatabase = 0;
}
$databaseCullTime = numericValidator($databaseCullTime, 90, 2000, 370);


if(enablePGSQL == 0 || !file_exists(utilitiesPath . 'MapReq.php') || dynamicTrackerEnabled == '0')
{
	$mapreqEnabled = "0";
}
define("mapreqEnabled", $mapreqEnabled);

//The IP address has already been validated, so we can use it for a directory name
//Make sure we convert the path to lowercase when creating folders for it, or else the flood protection could be bypassed!
if($executeDynamicInstructionsPage == "1")
{
	$dynamicIPAddressPath = "";
}
else
{
	$dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);
}

$enableGeoIP = booleanValidator($enableGeoIP, 0);


//If we are running in dynamic mode and we are not running the setup page, let's check for override values on a few more settings.
if($dynamicTrackerCalledFromCorrectFile == "1" && $executeDynamicInstructionsPage != "1")
{
	//If the config file has disabled levelshot transitions, we must abide by it. Otherwise, override it.
	if(isset($_GET["levelshotsEnabled"]) && $levelshotTransitionsEnabled == "1")
	{
		$levelshotTransitionsEnabled = booleanValidator($_GET["levelshotsEnabled"], 1);
	}

	if(isset($_GET["levelshotTransitionAnimation"]))
	{
		$levelshotTransitionAnimation = numericValidator($_GET["levelshotTransitionAnimation"], 0, 32767, 3);
	}

	if(isset($_GET["levelshotDisplayTime"]))
	{
		$levelshotDisplayTime = numericValidator($_GET["levelshotDisplayTime"], 1, 15, $levelshotDisplayTime);
	}

	if(isset($_GET["levelshotTransitionTime"]))
	{
		$levelshotTransitionTime = numericValidator($_GET["levelshotTransitionTime"], 0.1, 5, $levelshotTransitionTime);
	}

	if(isset($_GET["enableAutoRefresh"]) && $enableAutoRefresh == "1")
	{
		$enableAutoRefresh = booleanValidator($_GET["enableAutoRefresh"], $enableAutoRefresh);
	}

	if(isset($_GET["font"]))
	{
		$customFont = stringValidator(rawurldecode($_GET["font"]), "", "");
	}

	if(isset($_GET["backgroundColor"]))
	{
		$backgroundColor = colorValidator($_GET["backgroundColor"]);
	}

	if(isset($_GET["backgroundOpacity"]))
	{
		$backgroundOpacity = numericValidator($_GET["backgroundOpacity"], 0, 100, 100);
	}

	if(isset($_GET["textColor"]))
	{
		$textColor = colorValidator($_GET["textColor"]);
	}

	if(isset($_GET["playerListColor1"]))
	{
		$playerListColor1 = colorValidator($_GET["playerListColor1"]);
	}

	if(isset($_GET["playerListColor1Opacity"]))
	{
		$playerListColor1Opacity = numericValidator($_GET["playerListColor1Opacity"], 0, 100, 100);
	}

	if(isset($_GET["playerListColor2"]))
	{
		$playerListColor2 = colorValidator($_GET["playerListColor2"]);
	}

	if(isset($_GET["playerListColor2Opacity"]))
	{
		$playerListColor2Opacity = numericValidator($_GET["playerListColor2Opacity"], 0, 100, 100);
	}

	if(isset($_GET["scrollShaftColor"]))
	{
		$scrollShaftColor = colorValidator($_GET["scrollShaftColor"]);
	}

	if(isset($_GET["scrollThumbColor"]))
	{
		$scrollThumbColor = colorValidator($_GET["scrollThumbColor"]);
	}

/*
	if(isset($_GET["scrollShaftOpacity"]))
	{
		$scrollShaftOpacity = numericValidator($_GET["scrollShaftOpacity"], 0, 100, 100);
	}
*/

	if(isset($_GET["displayGameName"]))
	{
		$displayGameName = booleanValidator($_GET["displayGameName"], $displayGameName);
	}

	if(isset($_GET["enableGeoIP"]) && $enableGeoIP == "1")
	{
		$enableGeoIP = booleanValidator($_GET["enableGeoIP"], $enableGeoIP);
	}

	if(isset($_GET["filterOffendingServerNameSymbols"]))
	{
		$filterOffendingServerNameSymbols = booleanValidator($_GET["filterOffendingServerNameSymbols"], $filterOffendingServerNameSymbols);
	}
}

if(isset($calledFromRCon) && $calledFromRCon)
{
	if(isset($_GET["scrollShaftColor"]))
	{
		$scrollShaftColor = colorValidator($_GET["scrollShaftColor"]);
	}

	if(isset($_GET["scrollThumbColor"]))
	{
		$scrollThumbColor = colorValidator($_GET["scrollThumbColor"]);
	}
}

if($enableGeoIP == "1")
{
	if(!file_exists('vendor/autoload.php'))
	{
		echo ' Composer does not appear to be installed. ' . trackerName() . ' expects Maxmind GeoIP2 to be installed via Composer. GeoIP has been disabled and will be ignored... ';
		$enableGeoIP = 0;
	}
	else
	{
		//Composer appears to be installed. Load GeoIP2!
		include_once 'vendor/autoload.php';

		if (!class_exists('GeoIp2\Database\Reader'))
		{
			echo ' Maxmind GeoIP2 library does not seem to be present. ' . trackerName() . ' expects this library to be installed via Composer. GeoIP has been disabled and will be ignored... ';
			$enableGeoIP = 0;
		}
		else
		{
			//If GeoIP is enabled but the file is not found, disable it.
			if(!file_exists($geoIPPath))
			{
				echo ' Could not find a GeoIP Country Database. "' . $geoIPPath . '"' . "\n" . 'GeoIP has been disabled and will be ignored... ';
				$enableGeoIP = 0;
				$geoIPPath = "";
			}
		}
	}
}

// Validating this is a bad idea, it can break stuff. It is specified manually
// in the config file, so it should be fine to leave it unvalidated
// $webServerDomain = stringValidator($webServerDomain, 255, "");
define("webServerDomain", $webServerDomain);

$emailEnabled = booleanValidator($emailEnabled, 0);
define("emailEnabled", $emailEnabled);

if(emailEnabled)
{
	$serverToolsAllowEmailAdministrators = booleanValidator($serverToolsAllowEmailAdministrators, false);
} else {
	$serverToolsAllowEmailAdministrators = false;
}
define("serverToolsAllowEmailAdministrators", $serverToolsAllowEmailAdministrators);

// This is needed for serverTools, so it needs validated here
$useSMTP = booleanValidator($useSMTP, 0);
define("useSMTP", $useSMTP);

$emailAdminReports = booleanValidator($emailAdminReports, 0);
define("emailAdminReports", $emailAdminReports);

define("floodProtectTimeout", $floodProtectTimeout);
define("loginFloodProtect", $loginFloodProtect);
define("connectionAttempts", $connectionAttempts);
define("connectionTimeout", $connectionTimeout);
define("refreshTimeout", $refreshTimeout);
define("paraTrackerSkin", $paraTrackerSkin);
define("levelshotTransitionsEnabled", $levelshotTransitionsEnabled);
define("levelshotDisplayTime", $levelshotDisplayTime);
define("levelshotTransitionTime", $levelshotTransitionTime);
define("levelshotTransitionAnimation", $levelshotTransitionAnimation);
define("maximumLevelshots", $maximumLevelshots);
define("displayGameName", $displayGameName);
define("filterOffendingServerNameSymbols", $filterOffendingServerNameSymbols);
define("noPlayersOnlineMessage", $noPlayersOnlineMessage);
define("enableAutoRefresh", $enableAutoRefresh);
define("autoRefreshTimer", $autoRefreshTimer);
define("maximumServerInfoSize", $maximumServerInfoSize);
define("RConEnable", $RConEnable);
define("RConMaximumMessageSize", $RConMaximumMessageSize);
define("RConFloodProtect", $RConFloodProtect);
define("SecurityLogSize", $SecurityLogSize);

define("enableGeoIP", $enableGeoIP);
define("geoIPPath", $geoIPPath);

define("cullDatabase", $cullDatabase);
define("databaseCullTime", $databaseCullTime);

define("backgroundColor", $backgroundColor);
define("backgroundOpacity", $backgroundOpacity);
define("textColor", $textColor);
define("playerListColor1", $playerListColor1);
define("playerListColor1Opacity", $playerListColor1Opacity);
define("playerListColor2", $playerListColor2);
define("playerListColor2Opacity", $playerListColor2Opacity);
define("scrollShaftColor", $scrollShaftColor);
define("scrollThumbColor", $scrollThumbColor);
//define("scrollShaftOpacity", $scrollShaftOpacity);

if(mapreqEnabled)
{
	//If mapreq is enabled, there will be text over the missing levelshot, so there is no need to have the "No image found" text
	define("levelshotPlaceholder", 'images/loading.gif');
}
else
{
	define("levelshotPlaceholder", 'images/missing.gif');
}
define("mapreqTextMessage", $mapreqTextMessage);

define("customFont", $customFont);
define("customSkin", $customSkin);


$enableCustomDefaultLevelshots = booleanValidator($enableCustomDefaultLevelshots, 1);
define("enableCustomDefaultLevelshots", $enableCustomDefaultLevelshots);
if($dynamicTrackerCalledFromCorrectFile == "1" && $enableCustomDefaultLevelshots)
{
	//Now, check to see if we were given a custom default levelshot
	if(isset($_GET["customDefaultLevelshot"]))
	{
		$customDefaultLevelshot = rawurldecode($_GET["customDefaultLevelshot"]);
	}
}
define("customDefaultLevelshot", $customDefaultLevelshot);


define("trackerTagline", $trackerTagline);

//Convert all file extensions to lowercase before defining, and remove the "." if there is one
for($i = 0; $i < count($fileExtList); $i++)
{
	$fileExtList[$i] = strtolower(ltrim($fileExtList[$i], '.'));
}
define("fileExtList", $fileExtList);

//Make sure these directories exist before we do anything
checkDirectoryExistence(infoPath);
checkDirectoryExistence(logPath);
checkDirectoryExistence(skinsPath);
checkDirectoryExistence(utilitiesPath);

//Make sure the necessary tracker files are in place
checkFileExistence("errorLog.php", logPath);
checkFileExistence("cleanupLog.php", logPath);
checkFileExistence("cleanupTimer.txt", infoPath);
checkFileExistence("emailTimer.txt", infoPath);


//Only run this if analytics is disabled. Otherwise, it will be handled by AnalyticsBackground.php
if(!analyticsEnabled)
{
	cleanupInfoFolder($cleanupInterval, deleteInterval, $loadLimit, $cleanupLogSize, 0);
}

if(serverToolsEnabled)
{
	if(!isset($doNotExecuteServerTools)) $doNotExecuteServerTools = true;
	include_once utilitiesPath . 'ServerTools.php';		// We'll need this later on to send emails.
}

function adminCheck()
{
	global $pgCon;
	global $adminUser;

	if (!empty($_POST['logOut'])){
		if(!empty($_COOKIE['actoken']))
		{
			setcookie("actoken", "", 0, "", "", false, true);
			pg_query_params($pgCon, 'DELETE FROM account.session WHERE token = $1', array($_COOKIE['actoken']));
		}
		return false;
	}

	if (!empty($_POST['username']) || !empty($_POST['password'])) {
		$actcheck = pg_fetch_all(pg_query_params($pgCon, 'SELECT id, passhash, salt FROM account.user WHERE username = $1', array($_POST['username'])));
		if (!empty($actcheck)) {
			$passtest = $_POST['password'] . $actcheck[0]["salt"];
			$passtesth = hash("sha512", $passtest);
			if ($passtesth == $actcheck[0]["passhash"]) {
				$actoken = bin2hex(random_bytes(32));
				pg_query_params($pgCon, 'INSERT INTO account.session (user_id, token, expires) VALUES ($1, $2, NOW() + interval\'86400 seconds\') ON CONFLICT (user_id) DO UPDATE SET token = $2, expires = NOW() + interval\'86400 seconds\'',
					array($actcheck[0]['id'], $actoken));
				setcookie("actoken", $actoken, 0, "", "", false, true);
				$adminUser = $_POST['username'];
				return true;
			}
		}
	} else if (!empty($_COOKIE['actoken'])) {
		$actoken = $_COOKIE['actoken'];
		if (!empty($actoken)) {
			pg_query('DELETE FROM account.session WHERE expires < NOW()');
			$actcheck = pg_fetch_all(pg_query_params($pgCon, 'SELECT account.user.username FROM account.session INNER JOIN account.user ON account.session.user_id = account.user.id WHERE account.session.token = $1', array($actoken)));
			if (!empty($actcheck)) {
				$adminUser = $actcheck[0]['username'];
				return true;
			}
		}
	}
	return false;
}

define("admin", $admin);
define("adminUser", $adminUser);

if($executeDynamicInstructionsPage == "1")
{
	dynamicInstructionsPage($personalDynamicTrackerMessage);
}

function checkForMissingFiles($dynamicIPAddressPath)
{
	if(!checkFileExistence("connectionErrorMessage.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("errorMessage.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("RConTime.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("loginTime.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("serverDump.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("time.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("JSONServerInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("JSONParsedInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("JSONParams.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("JSONPlayerInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkFileExistence("postgresData.txt", infoPath . $dynamicIPAddressPath)) return 0;

	if(!checkServerSettingsFileExistence($dynamicIPAddressPath)) return 0;

	//If we are using the old RCon file name, we need to address that!
	if(file_exists(logPath . $dynamicIPAddressPath . "RConLog.php"))
	{
		if(!file_exists(logPath . $dynamicIPAddressPath . serverSecurityLogFilename))
		{
			renameSecurityLogFile($dynamicIPAddressPath);
			unlink(logPath . $dynamicIPAddressPath . "RConLog.php");
		}
		else
		{
			unlink(logPath . $dynamicIPAddressPath . "RConLog.php");
		}
	}

	return 1;
}

function renameSecurityLogFile($dynamicIPAddressPath)
{
	//This function converts the old log files from SecurityLog.php to the new filename specified in $serverSecurityLogFilename
	//Do not remove this...it isn't hurting anything by being left here
	$oldFilename = "RConLog.php";

	$splitPoint = strrpos($oldFilename, "/");
	if($splitPoint != 0)
	{
		$splitPoint++;
	}
	$logFileName = substr($oldFilename, $splitPoint);

	$output = readLogFile($dynamicIPAddressPath . $oldFilename);
	$output = logHeader($dynamicIPAddressPath . serverSecurityLogFilename) . implode("\n", $output) . logFooter();

	$oldPath = logPath . $dynamicIPAddressPath . $oldFilename;
	$newPath = logPath . $dynamicIPAddressPath . serverSecurityLogFilename;

	file_put_contents($newPath, $output);
}

function checkFileExistence($filename, $folder)
{
	if (!file_exists($folder . $filename))
	{
		writeFileOut($folder . $filename, "");
		if (!file_exists($folder . $filename))
		{
			displayError("Failed to create file: '" . $folder . $filename . "'! Make sure " . trackerName() . " has file system access, and that the disk is not full!", time(), $dynamicIPAddressPath);
			return 0;
		}
	}
	return 1;
}

function clearCookie($input)
{
	setcookie($input, '', 0, '', '', false, true);
}

function getCookieData($input)
{
	if(isset($_COOKIE[$input])) return $_COOKIE[$input];
	return false;
}

function getPostData($input)
{
	if(isset($_POST[$input]) && $_POST[$input] != "") return $_POST[$input];
	return false;
}

function getPostDataForForm($input)
{
	if(isset($_POST[$input]) && $_POST[$input] != "") return $_POST[$input];
	return "";
}

function checkServerSettingsFileExistence($dynamicIPAddressPath)
{
	$testPath = infoPath . $dynamicIPAddressPath . serverSettingsFilename;
	// If the file does not exist or is null, we need to fix that
	if (!file_exists($testPath) || file_get_contents($testPath) == "")
	{
		writeSettingsFile($dynamicIPAddressPath, array());
		if (!file_exists($testPath))
		{
			displayError("Failed to create file: '" . $testPath . "'! Make sure " . trackerName() . " has file system access, and that the disk is not full!", time(), $dynamicIPAddressPath);
			return 0;
		}
	}
	return 1;
}

function checkDirectoryExistence($dirname)
{
	//This must be called before calling file_exists!
	clearstatcache();

	if (!file_exists($dirname))
	{
		if (!mkdir($dirname))
		{
			displayError("Failed to create directory: '" . $dirname . "'! Cannot continue without file system access!", "", "");
			return 0;
		}
	}

	return 1;
}

function setFilePermissions($inputFile)
{
	return chmod($inputFile, 0775);
}

function checkForAndDoUpdateIfNecessary($dynamicIPAddressPath)
{
	//Let's make sure we have a legitimate address first...
	if(empty($dynamicIPAddressPath) || $dynamicIPAddressPath == "-") return 0;

	$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
	$serverIPAddress = $serverAddress = "$brokenAddress[0]";
	$serverPort = $brokenAddress[1];

	//Now let's validate the address for safety.
	$temp = ipAndPortValidator($serverIPAddress, $serverPort, dynamicTrackerEnabled);
	$serverIPAddress = "$temp[0]";
	$serverPort = $temp[1];

	if(empty($serverIPAddress) || empty($serverPort)) return 0;

	//The dynamicIPAddressPath cannot be used for the data - it will need it's own variable
	$dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);

	//Let's make sure all the files we need are in place for this server
	//Between each check we should quit if it failed
	if(!checkDirectoryExistence(infoPath . $dynamicIPAddressPath)) return 0;
	if(!checkDirectoryExistence(logPath . $dynamicIPAddressPath)) return 0;

	if(!checkForMissingFiles($dynamicIPAddressPath)) return 0;

	//Check to see if a refresh is already in progress, and if it is, wait a reasonable amount of time for it to finish
	checkTimeDelay($dynamicIPAddressPath);

	$lastRefreshTime = numericValidator(readFileIn(infoPath . $dynamicIPAddressPath . "time.txt"), "", "", "0");

		if ($lastRefreshTime + floodProtectTimeout < time())
		{
			//Prevent users from aborting the page! This will reduce load on both the game server and the web server
			//by forcing the refresh to finish.
			ignore_user_abort(true);

			//Check to see if we were forced here. If so, change the refresh time value so that other users will wait for our refresh. This will prevent an accidental DOS of the server during high traffic.
			if(substr(trim(readFileIn(infoPath . $dynamicIPAddressPath . "time.txt")), 0, 4) == "wait")
			{
				writeFileOut(infoPath . $dynamicIPAddressPath . "time.txt", "wait" . rand(0, getrandmax()));
			}

			writeFileOut(infoPath . $dynamicIPAddressPath . "time.txt", "wait");

			//Remove any lingering error messages. We will write a new one later if we encounter another error.
			writeFileOut(infoPath . $dynamicIPAddressPath . "errorMessage.txt", "");

			$return = doUpdate($dynamicIPAddressPath);

			writeFileOut(infoPath . $dynamicIPAddressPath . "time.txt", time());

			//Allow users to abort the page again.
			ignore_user_abort(false);

			return $return;
		}
		else
		{
			return 1;
		}

}

function doUpdate($dynamicIPAddressPath)
{
	//Before we start, wipe out the levelshots data and the postgres data.
	writeFileOut(infoPath . $dynamicIPAddressPath . 'postgresData.txt', '');

	//And let's declare a variable for the game name
	$gameName = "";

	//On with the good stuff! Connect to the server.
	$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
	$serverIPAddress = $serverAddress = "$brokenAddress[0]";
	$serverPort = $brokenAddress[1];

	$serverSettings = readSettingsFile($dynamicIPAddressPath);

	if(isset($serverSettings['serverDDOSKey']))
	{
		$serverDDOSKey = $serverSettings['serverDDOSKey'];
	} else {
		$serverDDOSKey = null;
	}

	$message = str_repeat(chr(255),4) . chr(02) . "getstatus\n";
	if($serverDDOSKey !== null)
	{
		$message .= $serverDDOSKey . "\n";
	}

	$s = false;
	$i = 0;
	while($i < connectionAttempts && $s === false)
	{
		$i++;

		//Set this value to measure the server's ping
		$serverPing = microtime(true);

		//Connect to the server, but remove all invalid characters to prevent issues later
		$s = connectToServerAndGetResponse($serverIPAddress, $serverPort, $message);

		//Parse the server's ping.
		$serverPing = number_format(((microtime(true) - $serverPing) * 1000), 0);
	}

	//WE CAN NOT just write a server dump without making sure that the DDOS key has been removed!
	//The server dump is written in plain text and we can't be giving this out.
	if($s !== false) $s = str_replace($serverDDOSKey, "", $s);

	//This file is used for determining if the server connection was successful and regenerating dynamic content, plus it's good for debugging
	writeFileOut(infoPath . $dynamicIPAddressPath . "serverDump.txt", $s);

	$serverOnline = false;

	//Declare this stuff up here so the parser doesn't freak out
	$gametype = "";
	$mapName = "";
	$modName = "";
	$flag = "";
	$countryName = "";
	$cvar_array_single = "";
	$parseTimer = "";
	$BitFlags = "";
	$player_array = "";
	$playerParseCount = "";
	$sv_maxclients = "";
	$team1score = "";
	$team2score = "";
	$team3score = "";
	$team4score = "";
	$levelshotFolder = "";
	$defaultLevelshot = "";

	if($s != "")
	{
		//Server is online! Mark the time in microseconds so we can see how long this takes.
		$parseTimer = microtime(true);

		$serverOnline = true;

		$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
		$serverIPAddress = "$brokenAddress[0]";
		$serverPort = $brokenAddress[1];

		//Initialize these to suppress errors
		$flag = "";
		$countryName = "";

		//If GeoIP is enabled, let's get the flag and country data first
		if(enableGeoIP == 1)
		{
				$geoip_dbr = new GeoIp2\Database\Reader(geoIPPath);
				$actualIP = gethostbyname($serverIPAddress);
				$flag = stringValidator(strtolower($geoip_dbr->country($actualIP)->country->isoCode), "", "");
				$countryName = stringValidator($geoip_dbr->country($actualIP)->country->name, "", "");
		}

		//Now, we call a function to parse the data
		$dataParserReturn = quake3dataParser($s);

		//Organize the data that came back in the array
		$cvar_array_single = $dataParserReturn[0];
		$cvars_hash = $dataParserReturn[1];
		$cvars_hash_decolorized = $dataParserReturn[2];
		$player_array = $dataParserReturn[3];
		$playerParseCount = $dataParserReturn[4];

		//Now we need to parse any data that is unique to each individual game.
		//First, let's find the game name from the server's response.
		$gameName = parseGameName($cvars_hash, $cvars_hash_decolorized, $dynamicIPAddressPath);
		//If the gameName could not be determined we must terminate here.
		//Return 1, because if we return 0 the code above will try to connect to the server again
		if($gameName == "") return 1;

		//Insert game-specific function execution here
		$gameFunctionParserReturn = ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized, $dynamicIPAddressPath);

		//Remove the variables that were returned.
		//We must assume that they were returned in the correct order!
		$gametype = array_shift($gameFunctionParserReturn);
		$levelshotFolder = array_shift($gameFunctionParserReturn);
		$defaultLevelshot = array_shift($gameFunctionParserReturn);
		$mapName = pathValidator(array_shift($gameFunctionParserReturn));
		$modName = array_shift($gameFunctionParserReturn);
		$sv_hostname = array_shift($gameFunctionParserReturn);
		$sv_maxclients = array_shift($gameFunctionParserReturn);

		$team1score = array_shift($gameFunctionParserReturn);
		$team2score = array_shift($gameFunctionParserReturn);
		$team3score = array_shift($gameFunctionParserReturn);
		$team4score = array_shift($gameFunctionParserReturn);

		if($defaultLevelshot == "") $defaultLevelshot = levelshotPlaceholder;

		//The rest is all BitFlag data.
		$BitFlags = $gameFunctionParserReturn;

		//This next block should only run if analytics is enabled
		if(analyticsEnabled)
		{
			//We need to write the Postgres data file, to be used for analytics.
			writePostgresDataFile($gameName, $dynamicIPAddressPath, $sv_hostname, $mapName, $modName, $gametype, $playerParseCount);

			//DO NOT do any database work if we came from the analyticsBackground process!!
			if(!analyticsBackground)
			{
				global $pgCon;
				//Add the server to the server table if it's not already there
				pg_query_params($pgCon, 'INSERT INTO tracker.server (location, port) VALUES ($1, $2) ON CONFLICT (location, port) DO UPDATE SET active = TRUE', array(strtolower($serverIPAddress), $serverPort));
			}
		}

		// If the server is online, we need to reset the offline email timer
		if(analyticsEnabled && emailEnabled && serverToolsEnabled && isset($serverSettings['emailAlerts']['reasons']['serverOffline']))
		{
			$serverSettings['emailAlerts']['reasons']['serverOffline']['lastEmailTime'] = 0;
			writeSettingsFile($dynamicIPAddressPath, $serverSettings);
		}

	}

	//This has to be last, because the timer will output on this page
	parseToJSON($dynamicIPAddressPath, $serverAddress, $gameName, $gametype, $mapName, $modName, $flag, $countryName, $cvar_array_single, $parseTimer, $serverPing, $BitFlags, $player_array, $playerParseCount, $sv_maxclients, $team1score, $team2score, $team3score, $team4score, $levelshotFolder, $serverOnline, $defaultLevelshot);

	if($serverOnline) return $serverOnline;

	// Server is offline! See if we need to send an email alert
	if(analyticsEnabled && emailEnabled && serverToolsEnabled && isset($serverSettings['emailAlerts']['reasons']['serverOffline']['active']) && $serverSettings['emailAlerts']['reasons']['serverOffline']['active'])
	{
		$lastSeenTime = checkDatabaseForServerTime($dynamicIPAddressPath);

		// Check last seen time against serverSettings time
		if(!isset($serverSettings['emailAlerts']['reasons']['serverOffline']['offlineTime'])) $serverSettings['emailAlerts']['reasons']['serverOffline']['offlineTime'] = 30;
		if(!isset($serverSettings['emailAlerts']['reasons']['serverOffline']['lastEmailTime'])) $serverSettings['emailAlerts']['reasons']['serverOffline']['lastEmailTime'] = 0;

		$timeBeforeFirstEmail = numericValidator($serverSettings['emailAlerts']['reasons']['serverOffline']['offlineTime'], 15, 3600, 30) * 60;
		$lastAlertTime = $serverSettings['emailAlerts']['reasons']['serverOffline']['lastEmailTime'];

		$timePlusTolerance = time() - 120;	// Subtract some time here, that way in case analytics got bogged down this will still happen on time

		if( $lastSeenTime + $timeBeforeFirstEmail <= $timePlusTolerance		// If the time is too great, send an email
		&& $lastAlertTime + 86400 <= $timePlusTolerance )		// Limit to one email per day
		{
				$serverSettings['emailAlerts']['reasons']['serverOffline']['lastEmailTime'] = time();

				$subject = 'Game Server Offline!';
				$message = '<p style="margin-bottom: 0px;">This message was sent to inform you that ' . trackerName() . ' did not</p>
				<p style="margin-top: 0px; margin-bottom: 0px;">get a response from your game server at:</p>
				<h4 style="margin-top: 0px;"><span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail($serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . $serverPort . '</span>.</h4>
				<p>The server has been presumed offline.</p>';

				sendEmailAlertsWithUnsubscribeTokens($serverIPAddress, $serverPort, $serverSettings, $subject, $message);
				writeSettingsFile($dynamicIPAddressPath, $serverSettings);
		}
	}

	return false;
}

function writePostgresDataFile($gameName, $dynamicIPAddressPath, $sv_hostname, $mapName, $modName, $gametype, $playerParseCount)
{
	$gameName = stringClean($gameName);
	$sv_hostname = stringClean($sv_hostname);
	$mapName = stringClean($mapName);
	$modName = stringClean($modName);
	$gametype = stringClean($gametype);
	$playerParseCount = intval($playerParseCount);
	$output = $gameName . chr(0x00) . $sv_hostname . chr(0x00) . $mapName . chr(0x00) . $modName . chr(0x00) . $gametype . chr(0x00) . $playerParseCount;
	writeFileOut(infoPath . $dynamicIPAddressPath . 'postgresData.txt', $output);
}

function readPostgresDataFile($dynamicIPAddressPath)
{
	$fileData = strval(readFileIn(infoPath . $dynamicIPAddressPath . 'postgresData.txt'));

	if($fileData != "")
	{
		$stuff = explode(chr(0x00), $fileData);

		//Online status (boolean),  $gameName, $sv_hostname, $mapName, $modName, $gametype, $playerParseCount
		return array(1, $stuff[0], $stuff[1], $stuff[2], $stuff[3], $stuff[4],  $stuff[5]);
	}
	else
	{
		return array(0);
	}
}

function decolorizeArray($input)
{
	//This function removes all colorization from the input.
	//It is used to make game detection and parsing more foolproof.

	return removeColorization($input);
}

function removeColorization($input)
{
	//This function removes colorization from the input string.
	$output = str_replace('^0', '^9', $input);
	$output = str_replace('^1', '^9', $output);
	$output = str_replace('^2', '^9', $output);
	$output = str_replace('^3', '^9', $output);
	$output = str_replace('^4', '^9', $output);
	$output = str_replace('^5', '^9', $output);
	$output = str_replace('^6', '^9', $output);
	$output = str_replace('^7', '^9', $output);
	$output = str_replace('^8', '^9', $output);
	$output = str_replace('^9', '', $output);
	return $output;
}

function quake3dataParser($s)
{
	//Remove junk characters from the beginning
	$s = ltrim($s, "ÿ");

	$player_array = array();
	//Split the info first, then we'll loop through and remove any dangerous characters
		$sections = explode("\n", $s);
		array_pop($sections);
		$cvars_array = explode('\\', $sections[1]);

				//This block parses the CVars from each other
				$cvarCount = 0;
				$count = count($cvars_array);
				//Start counting at 1 to get rid of the unnecessary stuff at the start of the dump
				for($i = 1; $i < $count - 1; $i += 2)
				{
						$cvar_array_single[$cvarCount++] = array("name" => $cvars_array[$i], "value" => $cvars_array[$i+1]);
						$cvars_hash[$cvars_array[$i]] = $cvars_array[$i+1];
						$cvars_hash_decolorized[removeColorization($cvars_array[$i])] = $cvars_array[$i+1];
				}

				//Now, let's alphabetize the CVars so the list is easier to read
				$cvar_array_single = array_sort($cvar_array_single, "name", false);

				//This loop parses the players from each other
				$playerParseCount = 0;

				$count = count($sections);
				for($i = 2; $i < $count; $i++)
				{
						//Initialize these
						$player_name = "";
						$player_score = "";
						$player_ping = "";
						$player_team = "0";

						$player_name = explode('"', $sections[$i], 3)[1];

						$playerData = substr($sections[$i], 0, strpos($sections[$i], '"') - 1);
						$playerData = explode(' ', $playerData, 3);
						if(isset($playerData[0]))
						{
							$player_score = $playerData[0];
						}
						if(isset($playerData[1]))
						{
							$player_ping = $playerData[1];
						}
						if(isset($playerData[2]))
						{
							$player_team = $playerData[2];
						}
						$player_array[$i] = array("score" => $player_score, "ping" => $player_ping, "team" => $player_team, "name" => $player_name);
						$playerParseCount++;
				}
				return(array($cvar_array_single, $cvars_hash, $cvars_hash_decolorized, $player_array, $playerParseCount));
}

function removeOffendingServerNameCharacters($input)
{
	$color = '';

	$count = strlen($input);
	for($i = 0; $i < $count; $i++)
	{
		$test = ord($input[$i]);
		if($test > 32 && $test < 127)
		{
			if($input[$i] == '^') // We might be dealing with a color...we need to preserve the color, and still remove nonsense chars
			{
				if($i + 2 < $count)	// Make sure counting this as a color and going beyond is still within bounds
				{
					$test = ord($input[$i + 1]);
					if($test >= 48 && $test <= 57)
					{
						// We are dealing with a color! Skip ahead.
						$i++;
						$color = '^' . $input[$i];
						continue;
					}
				}
			}
			break;
		}
	}

	return $color . substr($input, $i);
}

function parseGameName($cvars_hash, $cvars_hash_decolorized, $dynamicIPAddressPath)
{
	//This function checks for variables specific to individual games, and sends them to the tracker.

	//Initialize this to null, so we can test against it later.
	$gameName = "";

	//Most games use the 'gamename' variable to identify which game is running. Try that first.
	if(isset($cvars_hash_decolorized["gamename"]) && $cvars_hash_decolorized["gamename"] != "")
	{
		$gameName = detectGameName(removeColorization($cvars_hash_decolorized["gamename"]));
	}
	if($gameName == "")
	{
		//Tremulous and RTCW use 'com_gamename' to identify the game. Try that next.
		if(isset($cvars_hash_decolorized["com_gamename"]) && $cvars_hash_decolorized["com_gamename"] != "")
		{
			$gameName = detectGameName(removeColorization($cvars_hash_decolorized["com_gamename"]));
		}
		if($gameName == "")
		{
			//Some games, like Jedi Academy and Jedi Outcast, use a 'version' variable to identify the game. Try that next.
			//This can only be checked for AFTER the 'gamename' variable, because some games use both variables.
			if(isset($cvars_hash_decolorized["version"]) && $cvars_hash_decolorized["version"] != "")
			{
				$gameName = detectGameName(removeColorization($cvars_hash_decolorized["version"]));
			}
		}
		if($gameName == "")
		{
			//sof2 uses game_version as well as gamename.
			if(isset($cvars_hash_decolorized["game_version"]) && $cvars_hash_decolorized["game_version"] != "")
			{
					$gameName = detectGameName(removeColorization($cvars_hash_decolorized["game_version"]));
			}
		}
	}

	if($gameName == "")
	{
		$error = "";
		if(isset($cvars_hash_decolorized["gamename"]))
		{
			$error .= "<br />gamename: " . $cvars_hash_decolorized["gamename"];
		}
		if(isset($cvars_hash_decolorized["version"]))
		{
			$error .= "<br />version: " . $cvars_hash_decolorized["version"];
		}
		if(isset($cvars_hash_decolorized["com_gamename"]))
		{
			$error .= "<br />com_gamename: " . $cvars_hash_decolorized["com_gamename"];
		}
		if(isset($cvars_hash_decolorized["game_version"]))
		{
			$error .= "<br />game_version: " . $cvars_hash_decolorized["game_version"];
		}
		if($error == "")
		{
			$error = "No data!";
		}

		echo " Unrecognized Game: " . $error . "<br />\nPlease contact the " . trackerName() . " team and request support!<br />" . $dynamicIPAddressPath . " \n";
		return "Unrecognized Game";
	}

return $gameName;
}

function makeFunctionSafeName($input)
{
	$input = preg_replace("/[^a-z0-9]/", "", strtolower($input));
	return $input;
}

function ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized, $dynamicIPAddressPath)
{
	//Initialize this
	$GameInfoData = array();

		//We'll need a new copy of game name to toy with for this part
		//Pull out invalid characters and make it lowercase
		$GameInfoGameName = makeFunctionSafeName($gameName);

		if(function_exists($GameInfoGameName) && is_callable($GameInfoGameName))
		{
			//Call the function
			$GameInfoData = $GameInfoGameName($cvars_hash, $cvars_hash_decolorized);
		}
		else
		{
			if(!is_callable($GameInfoGameName))
			{
				displayError("No game data found for '" . $gameName . "'!<br>Contact the " . trackerName() . " team with the game name, as this is a bug that must be fixed.", time(), $dynamicIPAddressPath);
			}
			else
			{
				echo " Could not load game data for " . $gameName . "! This error is not fatal, but " . trackerName() . " cannot parse gametypes or GameInfo. ";
			}
		}
		return $GameInfoData;
}

function parseToJSON($dynamicIPAddressPath, $serverAddress, $gameName, $gametype, $mapName, $modName, $flag, $countryName, $cvar_array_single, $parseTimer, $serverPing, $BitFlags, $player_array, $playerParseCount, $sv_maxclients, $team1score, $team2score, $team3score, $team4score, $levelshotFolder, $serverOnline, $missingLevelshotPlaceholder = levelshotPlaceholder)
{
	$returnArray = array();
	$BitFlagsIndex = array();

	//This array is used for parsing bit flag data
	$bitFlagArray = array();

	//This array is used for serverInfo
	$serverInfoArray = array();

	if($serverOnline)
	{
		//This array is used for the raw CVar data
		$buf2 = array();

		//This array is used for the final output
		$buf = array();

		//See if there is any BitFlag data to parse.
		if(count($BitFlags) > 1)
		{
			$BitFlagsIndex = array_shift($BitFlags);

			//Parse the arrays into variables named after the CVars
			for($i = 0; $i < count($BitFlagsIndex); $i++)
			{
			${$BitFlagsIndex[$i]} = $BitFlags[$i];
			}
		}
		else
		{
			//There is no BitFlag data to parse, so we will just declare an empty array for the index.
			$BitFlagsIndex = array();
		}

		array_push($serverInfoArray, JSONString("maxPlayers", $sv_maxclients));
		array_push($serverInfoArray, JSONString("gamename", $gameName));
		array_push($serverInfoArray, JSONString("modName", $modName));
		array_push($serverInfoArray, JSONString("mapname", $mapName));
		array_push($serverInfoArray, JSONString("gametype", $gametype));
		array_push($serverInfoArray, JSONString("geoIPcountryCode", $flag));
		array_push($serverInfoArray, JSONString("geoIPcountryName", $countryName));

		array_push($serverInfoArray, JSONString("team1score", $team1score));
		array_push($serverInfoArray, JSONString("team2score", $team2score));
		array_push($serverInfoArray, JSONString("team3score", $team3score));
		array_push($serverInfoArray, JSONString("team4score", $team4score));

		array_push($serverInfoArray, JSONString("levelshotPlaceholder", $missingLevelshotPlaceholder));

		//Let's get parsing.
		foreach($cvar_array_single as $cvar)
		{
			//This array is used for parsing bit flag data
			$bitFlagParseArray = array();

			// DO NOT put the challenge in the JSON. It reveals the DDOS key to every client.
			if (strcasecmp($cvar['name'], 'challenge') == 0) continue;

			array_push($buf2, JSONString($cvar['name'], $cvar['value']));

			if ((strcasecmp($cvar['name'], 'sv_hostname') == 0) || (strcasecmp($cvar['name'], 'hostname') == 0))
			{
				//Need to check for offending symbols and remove them from server names, since it's obnoxious and everybody does it.
				//There is no need to check if the filter is enabled or not, since the function handles that on it's own
				$filteredName = removeOffendingServerNameCharacters($cvar['value']);

				array_push($serverInfoArray, JSONString("servernameUnfiltered", $cvar['value']));
				array_push($serverInfoArray, JSONString("servername", $filteredName));
			}
			else
			{
				//We need to check for the BitFlag variables here, and calculate them if there is a match

				//This variable will allow the parsing to terminate when the matching bitflag array is found
				$foundMatch = 0;
				for($i = 0; $i < count($BitFlagsIndex) && $foundMatch == 0; $i++)
				{
					$test = removeColorization(strtolower($cvar['name']));

					if($test == strtolower($BitFlagsIndex[$i]))
					{
						$foundMatch = 1;
						$returnArray = bitvalueCalculator($test, $cvar['value'], ${$BitFlagsIndex[$i]});
						array_shift($returnArray);
						$index = count($returnArray);

						if ($cvar['value'] >= pow(2, count(${$BitFlagsIndex[$i]})))
						{
							//Miscount detected! Array does not have enough values
							array_push($bitFlagArray, array(JSONString("name", $cvar['name']), JSONArray("flags", "\"Error: miscount detected!\"", 0)));
						}
						else
						{
							array_push($bitFlagArray, array(JSONString("name", $cvar['name']), JSONArray("flags", $returnArray, 3)));
						}
					}
				}
			}
		}

		//Now let's parse players.
		$playerListbuffer = array();
		$player_count = 0;

		if($playerParseCount > 0)
		{
			foreach($player_array as &$player)
			{
				array_push($playerListbuffer, array(JSONString("name", $player["name"]), JSONNumber("score", $player["score"]), JSONString("ping", $player["ping"]), JSONString("team", $player["team"])));
			}
		}

		//The following function finds how many levelshots exist for the map on the web server and passes back an array
		$temp = levelshotFinder($dynamicIPAddressPath, $mapName, $levelshotFolder, $gameName);
		array_push($serverInfoArray, JSONArray("levelshotsArray", $temp, 3));

		array_push($serverInfoArray, JSONString("serverPing", $serverPing));
		$parseTimer = number_format(((microtime(true) - $parseTimer) * 1000), 3);
		array_push($serverInfoArray, JSONString("parseTime", $parseTimer));

		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONParsedInfo.txt', JSONArray("parsedInfo", $bitFlagArray, 4));
		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONParams.txt', JSONObject("info", $buf2, 3));
		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONPlayerInfo.txt', JSONArray("players", $playerListbuffer, 4));
	}
	else
	{
		//Server is offline! Wipe these clean
		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONParsedInfo.txt', '');
		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONParams.txt', '');
		writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONPlayerInfo.txt', '');
	}

	$temp = breakDynamicAddressPath($dynamicIPAddressPath);
	$serverIPAddress = "$temp[0]";
	$serverPort = $temp[1];

	array_push($serverInfoArray, JSONBoolean("serverOnline", $serverOnline));
	array_push($serverInfoArray, JSONString("serverIPAddress", $serverAddress));
	array_push($serverInfoArray, JSONString("serverPort", $serverPort));
	array_push($serverInfoArray, JSONString("serverNumericAddress", gethostbyname($serverIPAddress)));
	array_push($serverInfoArray, JSONNumber("lastServerRefreshTime", time()));

	writeFileOut(infoPath . $dynamicIPAddressPath . 'JSONServerInfo.txt', JSONObject("serverInfo", $serverInfoArray, 0));
}

function levelshotFinder($dynamicIPAddressPath, $mapName, $levelshotFolder, $gameName, $defaultLevelshotImage = levelshotPlaceholder, $upperLimit = maximumLevelshots)
{
	$levelshotBuffer = array();

	//If this is null, and it might be, we need to force it to the placeholder image
	if($defaultLevelshotImage == "") $defaultLevelshotImage = levelshotPlaceholder;

	$levelshotCount = 0;
	$levelshotIndex = 1;
	$foundLevelshot = 0;

	if(strtolower($levelshotFolder) != 'unknown')
	{

		$levelshotFolder = strtolower($levelshotFolder);
		$levelshotFolder = "images/levelshots/" . $levelshotFolder . "/";

		$levelshotCheckName = strtolower($mapName);
		do
		{

			//Reset this value every iteration so we can check to see if levelshots are being found
			$foundLevelshot = 0;

			for($i = 0; $i < count(fileExtList) && $foundLevelshot == 0; $i++)
			{
				$checkName = $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . "." . fileExtList[$i];
				if(file_exists($checkName))
				{
					array_push($levelshotBuffer, $checkName);
					$foundLevelshot = 1;
				}
			}

			if ($foundLevelshot == 1)
			{
				$levelshotCount++;
				$levelshotIndex++;
			}
		} While ($foundLevelshot == 1 && $levelshotCount < $upperLimit);

		if($levelshotCount == 0 && $foundLevelshot == 0)
		{
			//Could not find any images on the first pass. We need to try and find a levelshot no matter what.
			//Let's see if maybe the user was silly and forgot to add an underscore and number to the file name, and
			//if so, we'll just use that one. If not, we'll have to default to a placeholder for missing images.
			for($i = 0; $i < count(fileExtList) && $foundLevelshot == 0; $i++)
			{
				$checkName = $levelshotFolder . $levelshotCheckName . "." . fileExtList[$i];
				if(file_exists($checkName))
				{
					array_push($levelshotBuffer, $checkName);
					$foundLevelshot = 1;
					$levelshotCount++;
				}
			}

			if($foundLevelshot == 0)
			{
				//Could not find a levelshot! Javascript will use the "Missing image" instead.
				//Check to see if Postgres is active. If it is, let's automatically
				//insert the map into the missing levelshots database.
				if(mapreqEnabled && strtolower($levelshotFolder) != "unknown")
				{
					global $pgCon;
					pg_query_params($pgCon, '
					INSERT INTO mapreq (game_name, bsp_name)
					VALUES ($1, $2)
					ON CONFLICT (game_name, bsp_name) DO NOTHING', array($gameName, $levelshotCheckName))
					or echoStuff(" Could not auto-insert data into map table! ");
				}
			}
		}

	}

	if($dynamicIPAddressPath != "")
	{
		return $levelshotBuffer;
	}
	else
	{
		if($dynamicIPAddressPath == "cleanup")
		{
			//We are in a levelshot cleanup. Just return the number, 0 or otherwise.
			return $levelshotCount;
		}
		else
		{
			//We must be here to get a levelshot for analytics. Just spit out the link to the levelshot image
			if($levelshotCount == 0)
			{
				return $defaultLevelshotImage;
			}
			else
			{
				return $levelshotBuffer;
			}
		}
	}

	return $levelshotBuffer;
}

function levelshotJavascriptAndCSS()
{
	$output = '<script>
	var runSetup = 1;   //This variable allows the setup script to execute
	var timer = 0;  //Used for setting re-execution timeout
	var originalStyleData = "";   //Used to contain the original CSS info while fading.
	var levelshotTransitionAnimation = ' . levelshotTransitionAnimation . ';    //This value uses a bit field to determine which levelshot transitions to use.
	var animationList = []; //This initializes an array to be used for detecting the number of levelshot transitions
</script>';

return $output;
}

function passConfigValuesToJavascript()
{
	$output = '<script type="text/javascript">
	var paraTrackerSkin = "' . paraTrackerSkin . '";
	var customSkin = "' . customSkin . '";
	</script>';

	return($output);
}

function checkTimeDelay($dynamicIPAddressPath)
{
	$temp = breakDynamicAddressPath($dynamicIPAddressPath);
//	$temp[0] = gethostbyname("$temp[0]");	// Not sure why this is here..?
	makeDynamicAddressPath("$temp[0]", $temp[1]);
	$lastRefreshTime = readFileIn(infoPath . $dynamicIPAddressPath . "time.txt");

	if($lastRefreshTime == "")
	{
		//If time.txt is empty, then this is the first time ParaTracker has ever connected to this server.
		//We need to skip waiting, go back and update!
		return;
	}

	$lastRefreshTime = numericValidator($lastRefreshTime, "", "", "wait");

	$i = 0;
	$sleepTimer = "0.15"; //This variable sets the number of seconds PHP will wait before checking to see if the refresh is complete.
	$checkWaitValue = readFileIn(infoPath . $dynamicIPAddressPath . "time.txt");  //This variable is used to check if the wait value changes below
	$fileInput = $checkWaitValue;

	//connectionTimeout needs to be multiplied by connectionAttempts, because doUpdate will attempt to connect several times before giving up.
	while ($lastRefreshTime == "wait" && $i < (connectionTimeout * connectionAttempts + refreshTimeout))
	{
		//infoPath/time.txt indicated that a refresh is in progress. Wait a little bit so it can finish. If it goes too long, we'll continue on, and force a refresh.
		usleep($sleepTimer * 1000000);
		$fileInput = readFileIn(infoPath . $dynamicIPAddressPath . "time.txt");

		if($checkWaitValue != $fileInput && stripos($fileInput, "wait" !== false))
		{
			//Another client has started a refresh! Let's start our wait period over so we don't DoS the game server by accident.
			$checkWaitValue = readFileIn(infoPath . $dynamicIPAddressPath . "time.txt");
			$i = 0;
		}
		$lastRefreshTime = numericValidator($fileInput, "", "", "wait");
		$i += $sleepTimer;
	}

}

function array_removeIndex($array, $index, $length = 1)
{
	$output = array();
	for($i = 0; $i < count($array); $i++)
	{
		if($i == $index) continue;
		array_push($output, $array[$i]);
	}
	return $output;
}

function array_sort($a, $subkey, $direction)
{
		foreach($a as $k => $v)
		{
				$b[$k] = strtolower($v[$subkey]);
		}
		$direction == true ? arsort($b) : asort($b);
		foreach($b as $key=>$val)
		{
				$c[] = $a[$key];
		}
		return $c;
}

function booleanValidator($input, $defaultValue)
{
	//To use this function, use the following:
	//$variableName = booleanValidator($variableName, defaultValue);

	//First, let's trim any possible white space that may have been left accidentally
	$input = strtolower(trim($input));

	//The config file allows for a value of 1 or the string "yes" to be used for booleans.
	//Everything else must evaluate to false.

	if ($input == "1" || $input == "yes" || $input == "true" || $input == "t")
	{
		$input = 1;
	}
	else
	{
		if($input == "" && $defaultValue == "1")
		{
			$input = 1;
		}
		else
		{
			$input = 0;
		}
	}
	return $input;
}

function convertToRGBA($input)
{
	//colorValidator already ensured that the input will be 6 characters long
	return hexdec(substr($input, 0, 2)) . ', ' . hexdec(substr($input, 2, 2)) . ', ' . hexdec(substr($input, 4, 2));
}

function colorValidator($input)
{
	//Get rid of all non-hex characters on the input.
	$input = trim($input);
	$input = preg_replace("/[^A-F0-9]/", "", strtoupper($input));

	if(strlen($input) > 6)
	{
		$input = substr($input, 0, 6);
	}

	//If the string we received is 6 characters, it is a 6 digit hex color
	if(strlen($input) == "6")
	{
		return $input;
	}

	//If the string we received is 3 characters, it is a 3 digit hex color
	if(strlen($input) == "3")
	{
		return str_repeat(substr($input, 0, 1), 2) . str_repeat(substr($input, 1, 1), 2) . str_repeat(substr($input, 2, 1), 2);
	}

	//If the string we received is 2 characters, it is a 1 digit hex color, with two digits
	if(strlen($input) == "2")
	{
		return str_repeat($input, 3);
	}

	//If the string we received is 1 character, it is a 1 digit hex color
	if(strlen($input) == "1")
	{
		return str_repeat($input, 6);
	}

	return "";
}

function echoStuff($input)
{
	echo $input;
}

function echoArray($input)
{
	echo "\n";
	var_dump($input);
	echo "\n";
}

function numericValidator($input, $minValue, $maxValue, $defaultValue)
{
//To use this function, use the following:
//$variableName = numericValidator($variableName, min, max, default);
//If you do not wish to pass a value, just pass a null string "" instead.

	//First, let's trim any possible white space that may have been left accidentally
	$input = trim($input);

	//Have to check this against "0" as well, otherwise skins break
	if(empty($input) && $input !== "0")
	{
		//No input given! Assume default value.
		return $defaultValue;
	}

	//Make sure we actually got a number
	$input = floatval($input);
	
	//Did the user pass a minvalue? If not, move on.
	if ($minValue != "" && $input < $minValue)
	{
		$input = $minValue;
	}
	//Did the user pass a maxvalue? If not, move on.
	elseif ($maxValue != "" && $input > $maxValue)
	{
		$input = $maxValue;
	}

	return $input;
}

function basicValidator($input, $defaultValue)
{
	if(!isset($input) || $input == "")
	{
		return $defaultValue;
	}
	else
	{
		return $input;
	}
}

function pathValidator($input)
{
	str_replace("\\", "/", $input);
	str_replace("//", "/", $input);
	ltrim($input, "/");
	$working = explode("/", $input);
	$count = count($working);
	$newFileList = array();
	$depth = 0;
	for($i = 0; $i < $count && $depth >= 0; $i++)
	{
		if($working[$i] == ".." || $working[$i] == "." || $working[$i] == "~")
		{
			//Value jumps back a folder.
			$depth--;
			if($depth < 0)
			{
				displayError("Access is forbidden to '" . $input . "'!<br>Cannot go outside the intended folder.<br>This event has been logged.", "", "");
				exit();
				return "";
			}
			array_pop($newFileList);
		}
		else if(!empty($working[$i]))
		{
			//Value is legitimate. Add it to the new array.
			array_push($newFileList, $working[$i]);
			$depth++;
		}
	}

	return implode('/', $newFileList);
}

function stringClean($input) {
	//This function removes white space from incoming strings.
	$input = strval($input);
	$return = "";
	$count = strlen($input);
	for ($i = 0; $i < $count; $i++) {
		//Make sure to cast the variable as a string, or else the comparison breaks
		$c = "$input[$i]";
		if ($c >= ' ') $return .= $c;
	}
	return $return;
}

function stringValidator($input, $maxLength, $defaultValue, $runStringClean = true)
{
	//Is the string null? If not, continue.
	if ($input != "")
	{
		if($runStringClean) $input = stringClean($input);
		//Check to see if a maxLength was given, and if the string exceeds the maximum length.
		if ($maxLength != "" && $maxLength > 0 && strlen($input) > $maxLength)
		{
			//Trim down to the maximum length.
			$input = substr($input, 0, $maxLength);
		}
		//Trim whitespace from the end of the string. There's no reason to leave it there.
		//I will leave whitespace at the beginning, though, because people might use spaces
		//or tabs to align things. So, we'll use rtrim() instead of trim().
		$input = rtrim($input);

		//Check for and replace any invalid or dangerous characters.
		//Players could connect with malicious names, but also if the tracker connects
		//to the wrong IP address, code could be injected onto the clients when the web
		//page loads.
		//< and > are dangerous because they could add HTML to the tracker page.
		//{ and } are dangerous because they could allow javascript to be added as well.
		//Also removing equals signs for the same reason.
		//Double quotes are being removed as well.
		//Also removing colons to prevent http:// and stuff like that from getting through.
		//I know this is a bit over-protective, but safety first.
		$input = str_replace("<", "&lt;", $input);
		$input = str_replace(">", "&gt;", $input);
		$input = str_replace("{", "&#123;", $input);
		$input = str_replace("}", "&#125;", $input);
		$input = str_replace("=", "&#61;", $input);
//		$input = str_replace("'", "&#39;", $input);		//Removing these will break the levelshot path for any game with an apostrophe in the name
		$input = str_replace("\"", "&quot;", $input);
//		$input = str_replace(".", "&#46;", $input);		//Commented this out, because it breaks the IP address validator, which results in blank trackers
		$input = str_replace(":", "&#58;", $input);
		$input = str_replace("?", "&#63;", $input);
		//Also, do not allow the termination of a comment block
		$input = str_replace("*/", "&#42;&#47;", $input);
	}
	else
	{
		//String is null! Force default value.
		$input = $defaultValue;
	}
	return $input;
}

function protectPathValidator($input)
{
	$input = str_replace("..", "&#46;", $input);
	return $input;
}

function emailValidator($input)
{
	// This function will return a boolean false if the email address is invalid. If true, it will return the address in a validated state

	$input = strtolower(trim($input));

	if(filter_var($input, FILTER_VALIDATE_EMAIL)) {
		return $input;
	}
	return false;
}

function ipAndPortValidator($serverIPAddress, $serverPort)
{
	//By default, static mode will already have given us an IP address before all of this took place.
	//So, now that we have the IP address and port from our source of choice, MAKE SURE to validate them before we go ANY further!
	if($serverPort != "") $serverPort = numericValidator($serverPort, 1, 65535, 29070);
	if($serverIPAddress != "") $serverIPAddress = ipAddressValidator($serverIPAddress);

	//Check for path exploits
	if(strpos($serverPort, "..") !== false || strpos($serverIPAddress, "..") !== false)
	{
		displayError("Server address exploit detected! This event has been logged.", "", "");
		return array("", "");
	}

	return array($serverIPAddress, $serverPort);
}

function ipAddressValidator($input)
{
	//Remove whitespace
	$input = trim($input);

	//Check to see if an address was supplied
	if($input == "")
	{
		//No address. Are we running in dynamic mode?
		if(dynamicTrackerEnabled == "0")
		{
			//We are in static mode, so ParaConfig.php is the problem
			displayError("No server address specified in ParaConfig.php!", "", "");
			return "";
		}
		else
		{
			//We are in Dynamic mode, so the user did not give an address
			displayError('Invalid IP address! ' . stringValidator($input, "", "") . '<br />Please specify an IP Address.', "", "");
			return "";
		}
	}

	//Use a PHP function to check validity
	if (!filter_var($input, FILTER_VALIDATE_IP) && $input != "localhost")
	{
		$test = gethostbyname($input);

		//gethostbyname returns the input string on failure. So, to test if this is a failure, we test it against itself
		if($test == $input)
		{
			//DNS test failed.
			displayError('Invalid address! ' . stringValidator($input, "", "") . '<br />Check the address and try again.', "", "", false);
			return "";
		}
		else
		{
			if(!filter_var($test, FILTER_VALIDATE_IP))
			{
				displayError('Invalid server address! ' . stringValidator($input, "", "") . '<br />Check the address and try again.</h3>', "", "");
				return "";
			}
		}
	}

	//Check for an ipv6 address, and add brackets if it is one
	if(strpos($input, ':') !== false) return '[' . $input . ']';

	//Otherwise, return the address/domain as-is
	return $input;
}

function skinValidator($paraTrackerSkin, $customSkin)
{
	$paraTrackerSkin = trim($paraTrackerSkin);
	$customSkin = trim($customSkin);

	if(strtolower($paraTrackerSkin) == "json")
	{
		return "json";
	}

	//Are we refreshing the tracker? Give back a response of json
	if(isset($_GET["JSONReload"]) && booleanValidator($_GET["JSONReload"], 0) == "1")
	{
		return "json";
	}

	//First and foremost, we'll check custom skins first, to avoid unnecessary error messages.
	if(strtolower($paraTrackerSkin) == "custom" && $customSkin != "")
	{
		$customSkin = trim($customSkin);
		//If an external skin file was specified, we need to check for double quotes to prevent exploits.
		if(strtolower(substr($customSkin, -4)) == ".css")
		{
			$customSkin = substr($customSkin, 0, -4);
		}

		if(strpos($customSkin, '"') !== 0)
		{
			return $customSkin;
		}
		else
		{
			echo " Double quotes can not be used in a custom skin path! Ignoring... ";
			$customSkin == "";
		}
	}

	//Prevent slashes, periods, and colons. This will stop people from adding a file extension, a URL, or a ../ into the file name
	if(strpos($paraTrackerSkin, ".") !== false ||strpos($paraTrackerSkin, "/") !== false ||strpos($paraTrackerSkin, "\\") !== false ||strpos($paraTrackerSkin, ":") !== false)
	{
		echo " Invalid skin specified! Slashes, colons, and periods are forbidden in skin file names. Assuming default skin... ";
		$paraTrackerSkin = defaultSkin;
	}

	if(strtolower($paraTrackerSkin) == "custom")
	{
		echo " '". $paraTrackerSkin . "' is a reserved value, and cannot be used as a skin name! Assuming default skin...  ";
		$paraTrackerSkin = defaultSkin;
	}

	$paraTrackerSkin = stringValidator($paraTrackerSkin, "", defaultSkin);

	if(!file_exists(skinsPath . $paraTrackerSkin . ".css"))
	{
		echo " Invalid skin specified! Skin names must have a lowercase file extension, cannot have slashes ( '\' or '/' ) and must refer to an actual CSS file. Assuming default skin... ";
		$paraTrackerSkin = defaultSkin;

		if(!file_exists(skinsPath . $paraTrackerSkin . ".css"))
		{
			displayError(" Invalid skin specified!\nDefault skin could not be found! ", $lastRefreshTime, "");
		}
		else
		{
		//Non-fatal error; revert to default skin.
		$paraTrackerSkin = defaultSkin;
		}
	}
	return $paraTrackerSkin;
}

function renderParamPage($serverIPAddress, $serverPort)
{
	$output = htmlDeclarations($serverIPAddress . ' Parameter List', '');

	if(scrollShaftColor != "")
	{
		$output .= '<style>::-webkit-scrollbar-track{background-color: rgba(' . convertToRGBA(scrollShaftColor) . ', 100);}</style>';
	}
	if(scrollThumbColor != "")
	{
		$output .= '<style>::-webkit-scrollbar-thumb{background-color: rgba(' . convertToRGBA(scrollThumbColor) . ', 100);}</style>';
	}

	$output .= '<script src="js/ParaScript.js"></script>';
	$output .= '<script src="js/Param.js"></script>';
	$output .= '<script>var data = ' . renderJSONOutput(makeDynamicAddressPath($serverIPAddress, $serverPort)) . ';</script>';
	$output .= '</head>
				<body id="bodyElement" class="cvars_page">
				<span id="serverName" class="CVarHeading"></span><br />
				<span id="gameName" class="CVarGameHeading"></span><br />
				<span class="CVarServerAddress">' . $serverIPAddress . ':' . $serverPort . '</span><br />
				<span id="CVarServerNumericAddress" class="CVarServerAddress"></span><br />
				Ping: <span id="serverPing" class="serverPing"></span> ms<br /><br />
				<table class="FullSizeCenter"><tr><td>
				<table id="paramDataTable"></table>
	</td></tr></table><h4 class="center">' . versionNumber() . ' - Server info parsed in <span id="parseTime"></span> milliseconds.</h4><h5>Copyright &copy; 1837 Rick Astley. No rights reserved. Batteries not included. Void where prohibited.<br />Your mileage may vary. Please drink and drive responsibly.</h5><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></body></html>';

	return $output;
}

function getHumanReadableFilesize($file)
{
	$val = filesize($file);
	$sizes = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
	$i = 0;
	while($i < count($sizes))
	{
		if($val > 1024)
		{
			$val = $val / 1024;
			$i++;
			continue;
		}
		break;
	}
	return '<span class="' . strtolower($sizes[$i]) . '">' . round($val, 2) . ' ' . $sizes[$i] . '</span>';
}

function writeFileOut($filePath, $contents)
{
	$data = file_put_contents($filePath, $contents);
	setFilePermissions($filePath);
	if($data === false)
	{
		displayError("Could not write to file: " . $filePath . "    Writing: '". $contents . "'", "", "");
	}
	return $data;
}

function readFileIn($filePath)
{
	setFilePermissions($filePath);
	$data = file_get_contents($filePath);
	if($data === false)
	{
		displayError("Could not read file: " . $filePath, "", "");
	}
	return $data;
}

function displayError($errorMessage, $lastRefreshTime, $dynamicIPAddressPath, $logError = true)
{
	$serverIPAddress = "";
	$serverPort = "";
	$serverAddressStuff = "";

	if($dynamicIPAddressPath != "")
	{
		$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
		$serverIPAddress = protectPathValidator("$brokenAddress[0]");
		$serverPort = protectPathValidator($brokenAddress[1]);
	}

	if(trim($errorMessage) == "")
	{
		$errorMessage = "An unknown error has occurred! " . trackerName() . " must terminate.";
	}

//	Commented this out temporarily because the server address is getting lost somewhere...
//	if(!empty($dynamicIPAddressPath)) $serverAddressStuff = "Server being tracked: '" . $serverIPAddress . ":" . $serverPort . "'";
	if(!empty($dynamicIPAddressPath)) $serverAddressStuff = "Server being tracked: '" . $dynamicIPAddressPath . "'";

	if($logError)
	{
		//Let's log this event...
		$errorLog = date(DATE_RFC2822) . "  Client IP Address: " . clientAddress . "  " . stringValidator($serverAddressStuff, "", "") . "  Error message: " . $errorMessage;
		writeToLogFile("errorLog.php", $errorLog, errorLogSize);
	
		//If postgres is enabled, we need to log this event to the database so the admin status email can see what's going on
		if(enablePGSQL)
		{
			global $pgCon;
			//This function may be called before pgCon is defined
			if(!empty($pgCon))
			{
				pg_query($pgCon, 'INSERT INTO tracker.displayerror DEFAULT VALUES');
			}
		}
	}
	echo "<!--";

	$errorMessage = '<!-- --><h3 class="errorMessage">' . $errorMessage . '</h3>';

	//Error detected and ParaTracker is terminating. Check to see if we have a file path and refresh time data.
	if($lastRefreshTime != "" && $dynamicIPAddressPath != "")
	{
		//We have a file path! Write the error message to a file, update both of the refresh timers, and terminate!
		file_put_contents(infoPath . $dynamicIPAddressPath . "errorMessage.txt", $errorMessage);
		file_put_contents(infoPath . $dynamicIPAddressPath . "time.txt", time());
		file_put_contents(infoPath . $dynamicIPAddressPath . "RConTime.txt", time());
	}

	if(!defined('analyticsBackground') || !analyticsBackground && (!defined('forceAnalyticsBackgroundRun') || !forceAnalyticsBackgroundRun ))
	{
		//We are not running the analytics background process, so echo the message and terminate here.
		//If no file path was given, flood protection will not be necessary, as ParaTracker never had a chance to contact the server.
		//so it is safe to terminate regardless of whether there was a file path or not.
		echo $errorMessage;
	}
}

function bitvalueCalculator($cvarName, $cvarValue, $arrayList)
{
			$iBlewItUp = array();
			$toBeExploded = "";

				$index = count($arrayList);
				//Sort through the bits in the value given, and for every 1, output the matching array value
				for ($i = 0; $i < $index; $i++)
				{
					if ($cvarValue & (1 << $i))
					{
						if(trim($arrayList[$i]) != "")
						{
							//Make sure the value isn't blank before adding it. Otherwise we'll end up with spaces in the list.
							$toBeExploded = "\n" . $arrayList[$i] . $toBeExploded;
						}
					}
				}

	$iBlewItUp = explode("\n", $toBeExploded);
	sort($iBlewItUp);

	return $iBlewItUp;
}

function htmlDeclarations($pageTitle, $filePath = '')
{
	if($pageTitle == '')
	{
		// For some reason this doesn't work in the function declaration...
		$pageTitle = trackerTagline;
	}
	$pageTitle = versionNumber() . ' - ' . $pageTitle;
	$pageTitle = stringValidator($pageTitle, "", "");
	$output = '<!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="' . $filePath . 'css/ParaStyle.css" type="text/css" />
	<link rel="stylesheet" href="' . $filePath . 'css/LevelshotAnimations.css" type="text/css" />';

	//If a skin is defined, then include it here in the header
	if(paraTrackerSkin)
	{
		if(strtolower(paraTrackerSkin) == "custom")
		{
			$output .= '<link rel="stylesheet" href="' . customSkin . '.css" type="text/css" />';
		}
		else
		{
			$output .= '<link rel="stylesheet" href="' . $filePath . skinsPath . paraTrackerSkin . '.css" type="text/css" />';
		}
	}

	$output .= '<title>' . $pageTitle . '</title>';

	$output .= passConfigValuesToJavascript();

	return $output;
}

function colorize($input)
{
	//Check for any color inputs, and replace them with HTML tags
	$colorized_string = str_replace('^0', '</span><span class="color0">', $input);
	$colorized_string = str_replace('^1', '</span><span class="color1">', $colorized_string);
	$colorized_string = str_replace('^2', '</span><span class="color2">', $colorized_string);
	$colorized_string = str_replace('^3', '</span><span class="color3">', $colorized_string);
	$colorized_string = str_replace('^4', '</span><span class="color4">', $colorized_string);
	$colorized_string = str_replace('^5', '</span><span class="color5">', $colorized_string);
	$colorized_string = str_replace('^6', '</span><span class="color6">', $colorized_string);
	$colorized_string = str_replace('^7', '</span><span class="color7">', $colorized_string);
	$colorized_string = str_replace('^8', '</span><span class="color8">', $colorized_string);
	$colorized_string = str_replace('^9', '</span><span class="color9">', $colorized_string);

	//If the input doesn't match the current output string, then we must have found colors in the string.
	//So, we need to wrap it with span tags for color 7 before returning it.
	if($input != $colorized_string)
	{
		return '<span class="color7">' . $colorized_string . "</span>";
	}
	//If we made it here, no colors were applied, so we can return the original string
	return $input;
}

function pageNotificationSuccess($msg) {
	$output = '<div class="notificationSuccess"><span class="notificationText">';
	$output .= $msg;
	$output .= '</span></div>';
	return $output;
}

function pageNotificationFailure($msg) {
	$output = '<div class="notificationFail"><span class="notificationText">';
	$output .= $msg;
	$output .= '</span></div>';
	return $output;
}

function pageNotificationInformation($msg) {
	$output = '<div class="notificationInfo"><span class="notificationText">';
	$output .= $msg;
	$output .= '</span></div>';
	return $output;
}

function dynamicInstructionsPage($personalDynamicTrackerMessage)
{
	global $pgCon;
	global $admin;

	$gameListArray = detectGameName("");
	//The output returned will be an array. Position 0 is a full game list, position 1 is a filtered game list (Useful for hiding duplicate game entries that still should be detected as separate games)
	$gameList = $gameListArray[1];
	$gameOutput = "";

	$gameOutput .= implode(', ', $gameList) . ', ';

	$urlWithoutParameters = explode('?', $_SERVER["REQUEST_URI"], 2);
	$currentURL = $_SERVER['HTTP_HOST'] . $urlWithoutParameters[0];

	$output = htmlDeclarations('', '');

	$output .= '<script src="js/ParaScript.js"></script><meta name="keywords" content="Free PHP server tracker, server, tracker, ID Tech 3, JediTracker, Jedi Tracker, ' . $gameOutput .'Game Tracker, Custom Colors, JSON, Bitfield calculator, Bit Value Calculator, Bit Flag Calculator, bitflag calculator, Bit Mask Calculator, Bitmask Calculator">
<meta name="description" content="Free Server Tracker for ' . $gameOutput . 'Written in PHP, with custom colors, JSON compatible, Bit Value Calculator">
<meta name="author" content="Parabolic Minds">

	<script src="js/Bitflags.js"></script>

	</head><body id="body" class="dynamicConfigPage dynamicConfigPageStyle" onhashchange="changeSetupPageFunction()">
';

	$output .= '<div class="paraTrackerTestFrameTexturePreload"></div>';
	$output .= '<div class="utilitiesTopRow">';

	$output .= '<br /><h1 class="noTopMargin"><span class="paraTrackerColor">' . trackerName() . '</span> <span class="paraTrackerVersionColor">' . strval(trackerVersion()) . '</span></h1>';

	if($personalDynamicTrackerMessage != '')
	{
		$output .= '<i id="dynamicTrackerMessage">' . $personalDynamicTrackerMessage . '<br /><br /></i>';
	}

	$output .= '<div id="utilities" class="utilitiesButtonRow utilitiesDiv collapsedFrame">';

	$output .= '<p><a class="dynamicFormButtons dynamicFormButtonsStyle utilitiesButton" id="closeUtilitiesButton" href="#"> Close ParaTracker Utilities </a></p>';

	//Add the login prompt for admins
	if (enablePGSQL)
	{
		$output .= '<form method="POST">
					<div id="adminlogin">
';
		if (admin)
		{
			$output .= pageNotificationInformation('LOGGED IN AS ADMIN: ' . adminUser);
		}
		else if (!empty($_POST['username']) || !empty($_POST['password']))
		{
			$output .= pageNotificationFailure('LOGIN FAILED');
		}
		else
		{
			//$output .= '<div class="loginText">Log in to access all ' . trackerName() . ' features</div>';
		}

		$output .= '<div class="loginRow">';

		if(admin)
		{
			$output .= '<input type="text" class="adminentry" style="display:none;" name="logOut" value="1">';
			$output .= '<input type="submit" class="adminentry" value="LOG OUT">';
		}
		else
		{
			$output .= '<input type="text" class="adminentry" name="username" placeholder="username">
						<input type="password" class="adminentry" name="password" placeholder="password">
						<input type="submit" class="adminentry" value="LOG IN">';
		}

						$output .= '</div></div></form>';
	}


	//Add the buttons to the utilities page
	$output .= '<p class="dynamicPageWidth"><a id="bitValueCalculatorButton" href="#bitValueCalculator" class="dynamicFormButtons dynamicFormButtonsStyle">Bit Value Calculator</a>';

if(mapreqEnabled == "1")
{
	$output .= '<a id="mapreqButton" href="#mapreq" class="dynamicFormButtons dynamicFormButtonsStyle">Levelshot Requests</a>';
}

if(dynamicTrackerEnabled == "1")
{
	$output .= '<a id="serverToolsButton" href="#serverTools" class="dynamicFormButtons dynamicFormButtonsStyle">Server Tools</a>';
}

if(analyticsFrontEndEnabled == "1")
{
	$output .= '<a id="analyticsButton" href="#analytics" class="dynamicFormButtons dynamicFormButtonsStyle">Tracker Analytics</a>';
}

if(admin)
{
	$output .= '<a id="logViewerButton" href="#logViewer" class="dynamicFormButtons dynamicFormButtonsStyle">Log Viewer</a>';
	$output .= '<a id="adminInfoButton" href="#adminInfo" class="dynamicFormButtons dynamicFormButtonsStyle">Admin Info</a>';
}

$output .= '</p><br><br></div></div><div class="utilitiesDiv utilitiesBottomRow">';

//Levelshot requests form
if (mapreqEnabled == "1")
{
	$output .= '<div id="mapreqDiv" class="mapreqDiv collapsedFrame"><span class="reqforminfo">&gt;&gt;&gt; Fill out the form below to request levelshots for a specific map. &lt;&lt;&lt;</span><br>';
	$output .= '<span class="reqforminfo">If you are submitting your own levelshots, submit them at full resolution (Do not resize them).<br>
	It is highly recommended that levelshots have no post processing applied to them, including effects and text.<br>
	For best results, include a link to download the map.</span><br>';

	//Find out if we were given a game and/or BSP argument, and pass that on to mapreq
	$mapReqDataArray = array();
	if (isset($_GET["gameReq"]))
	{
		array_push($mapReqDataArray, 'gameReq=' . stringValidator($_GET["gameReq"], "", ""));
	}
	if (isset($_GET["bspReq"]))
	{
		array_push($mapReqDataArray, 'bspReq=' . stringValidator($_GET["bspReq"], "", ""));
	}
	$mapReqDataArray = implode('&', $mapReqDataArray);
	$output .= '<iframe src="' . utilitiesPath . 'MapReq.php?' . $mapReqDataArray . '" class="mapReqFrame" allowtransparency="true"></iframe></div>';
}

//Add the server tools here
if(dynamicTrackerEnabled == "1" && serverToolsEnabled == "1")
{
	$output .= '<div id="serverToolsDiv" class="serverToolsDiv serverToolsFrame utilitiesIframe collapsedFrame">
	<iframe src="' . utilitiesPath . 'ServerTools.php" class="serverToolsFrame"></iframe>
	</div>';
}

//Let's add analytics here
if(analyticsFrontEndEnabled == "1")
{
	$output .= '<div id="analyticsDiv" class="analyticsFrame utilitiesIframe collapsedFrame">
	<iframe src="' . utilitiesPath . 'Stats.php" class="analyticsFrame"></iframe>
	</div>';
}


//Add the log viewer and admin info
if(admin)
{
	$output .= '<div id="logViewerDiv" class="logViewerDiv utilitiesIframe collapsedFrame"><iframe src="' . utilitiesPath . 'LogViewer.php" class="logViewerFrame"></iframe></div>';

	$output .= '<div id="adminInfoDiv" class="adminInfoFrame utilitiesIframe collapsedFrame"><iframe src="' . utilitiesPath . 'AdminInfo.php" class="adminInfoFrame"></iframe></div>';
}


	//Let's add a bit value calculator, just in case someone needs it in the future
	$output .= '<div id="bitFlagCalculator" class="">';

	$gameList = $gameListArray[1];

	$JSONOutput = array();
	$JSONOutputArray = array();
	$JSONIteratorArray = array();

	//Let's add the bit value calculator feature here
	foreach($gameList as $testName)
	{
		$JSONOutputArray = array();
		$testArray = array();

		$bitFlagData = ParseGameData($testName, "", "", "", "");
		//Remove the stuff we don't need. All we want right now is bit values.
		$bitFlagData = array_slice($bitFlagData, 11);

		if(count($bitFlagData) > 1)
		{
			$JSONIteratorArray = array();

			//If we got here, there must be bitFlags in this game.
			//This next chunk unpacks the individual game data and declares the variables
			if(count($bitFlagData) > 1)
			{
						$bitFlagsIndex = array_shift($bitFlagData);

						//Parse the arrays into variables named after the CVars
						for($i = 0; $i < count($bitFlagsIndex); $i++)
						{
						${$bitFlagsIndex[$i]} = $bitFlagData[$i];
						}
						//Now that the arrays are defined as variables, sort them alphanumerically
				usort($bitFlagsIndex, 'strnatcasecmp');
			}

			//Next, we need to parse the arrays looking for empty data
			$count = count($bitFlagsIndex);
			for($i = 0; $i < $count; $i++)
			{
				$bitFlagsIteratorArray = array();

				$count2 = count(${$bitFlagsIndex[$i]});

				$testArray = ${$bitFlagsIndex[$i]};

				for($j = 0; $j < $count2; $j++)
				{
					if(empty(trim($testArray[$j])))
					{
						$testArray[$j] = "(Unused)";
					}
				}
				array_push($bitFlagsIteratorArray, JSONString("setname", $bitFlagsIndex[$i]));
				array_push($bitFlagsIteratorArray,  JSONArray("flags", $testArray, 3));
				array_push($JSONIteratorArray, JSONObject("", $bitFlagsIteratorArray));
			}
			array_push($JSONOutputArray, JSONString("gamename", $testName));
			array_push($JSONOutputArray, JSONString("gameClassName", "game_" . makeFunctionSafeName($testName)));
			array_push($JSONOutputArray, JSONArray("bitflags", $JSONIteratorArray, 0));
			array_push($JSONOutput, $JSONOutputArray);
		}
	}
	$JSONOutput = '<script type="text/javascript">var bitflags_raw = ' . JSONArray("", $JSONOutput, 4) . '</script>';

//Add the HTML we need for the bit value calculator
$output .= '<div id="bitValueCalculatorDiv" class="collapsedFrame">';
$output .= '<div id="bitflags_top" class="bitValueCalculator">
<div id="bitflags_tabdisplay"></div>
<select id="bitflags_bitselect" onchange="bitflags_setchange(this.selectedIndex)"></select>
<div id="bitflags_tabregion"></div>
</div></div>';

//Add the JSON stuff to the HTML output
$output .= $JSONOutput;

$output .= '</div>';
//End of bit value calculator

$output .= '</div><p class="collapsedFrame">Current page URL:<br /><input type="text" size="80" id="currentURL" value="' . $currentURL . '" readonly /></p>
	<br />';

	if(enablePGSQL)
	{
		$trackedCount = pg_fetch_all(pg_query($pgCon, "SELECT COUNT(*) FROM tracker.server WHERE active = true"))[0]['count'];
	}
	else
	{
		$trackedCount = count(getDirectoryOnlyList(infoPath));
	}

	$output .= '</div>';

	$output .= '<div id="trackerSetup" class="expandedFrame">';

	$output .= '<p><a class="dynamicFormButtons dynamicFormButtonsStyle utilitiesButton" id="openUtilitiesButton" href="#utilities"> Open ParaTracker Utilities </a></p>';

	$serverListPath = utilitiesPath . 'ServerList.php#';
	$countGames = count($gameList);
	$output .= '<h3><a href="' .$serverListPath . rawurlencode('game=All Supported Games') . '" target="_blank" class="gameListTableHover fiftyPercentWidth" title="Click here for a full list of tracked servers">Tracking <strong>' . $trackedCount . '</strong> Server' . ($trackedCount == 1 ? '' : 's') . ' with ' . $countGames . ' Supported Games</a></h3>';

	$output .= '<div class="centerTable"><table class="centerTable gameListTable gameListTableHover"><tr>';

		$colorNumber = 0;

		//Loop through the array of stuff listed
		for($i = 0; $i < $countGames; $i++)
		{
			//DO NOT make the directory list value lowercase here! It will make all dynamic game names appear in lowercase on the tracker
			$output .= '<td class="' . makeFunctionSafeName($gameList[$i]) . ' fiftyPercentWidth gameListTableEntries"><a href="' . $serverListPath . 'game=' . rawurlencode($gameList[$i]) . '" target="_blank" title="Click here to see all tracked servers for ' . $gameList[$i] . '">' . $gameList[$i] . '</a></td>';

			//Was this an even trip through the array, but also not the last trip? If so, start a new table row.
			if($i % 2 != 0 && $i + 1 != count($gameList))
			{
				$output .= "</tr><tr>";
			}
		}

	$output .= '</tr></table></div><br />';

	$output .= '<h3 class="dynamicPageWidth">Enter the data below to generate a URL or HTML code you can use to add ' . trackerName() . ' on your website:</h3>

	<form>

	<h3 class="dynamicPageWidth"><span class="gameColor1">Server IP Address:</span> <input type="text" size="46" onchange="createURL()" id="IPAddress" value="" /></h3>
	<h3 class="dynamicPageWidth"><span class="gameColor1">Server port number:</span> <input type="text" size="15" onchange="createURL()" id="PortNumber" value="" /></h3>';


	//Let's dynamically find the CSS files, and parse the resolution of the tracker from them
	$output .= '<h3 class="dynamicPageWidth"><span class="gameColor3">Skin:</span><br />';
	$directoryList = getDirectoryList(skinsPath);

	//Sort the array in a readable fashion
	usort($directoryList, 'strnatcasecmp');

	$skinList = array();
	$skinCount = 0;

	//Loop through the array of stuff given
	for($i = 0; $i < count($directoryList); $i++)
	{
		//Ignore Template.css, json.css and custom.css (which cannot exist), and make sure the file extension on the detected file is ".css"
		if(strtolower($directoryList[$i]) != "template.css" && strtolower($directoryList[$i]) != "custom.css" && strtolower($directoryList[$i]) != "json.css" && substr(strtolower($directoryList[$i]), -4) == ".css")
		{
			$skinList[$skinCount] = substr($directoryList[$i], 0, -4);
			$skinCount ++;
		}
	}

	//Initialize these here, in case no match is found below
	$width = "";
	$height = "";

	$output .= '<select ID="skinID" name="skinID" onchange="createURL()">';

	//Let's parse the CSS files and find out what the dimensions of the skin are.
	for($i = 0; $i < count($skinList); $i++)
	{
		$dimensions = getSkinFileDimensions($skinList[$i]);
		$width = $dimensions[0];
		$height = $dimensions[1];

		$output .= '<option ';

		if($skinList[$i] == defaultSkin)
		{
			$output .= 'selected="selected" ';
		}
		$output .= 'value="' . $skinList[$i] . ':#:' . $width . ':#:' . $height . '">' . $skinList[$i];

		if($width != "" && $height != "")
		{
			$output .= ' (' . $width . ' x ' . $height . ')';
		}
		$output .= '</option>
';
	}

	$output .= '<option value="JSON:#:800:#:800">JSON (Text-only response for clientside Javascript parsing)</option>';
	$output .= '<option value="Custom">Custom (External file and width/height must be specified)</option>';
	$output .= '</select></h3><div id="externalSkinFile" class="customSkinSelections collapsedFrame">';

		$output .= '<h3><span class="gameColor5">External file URL:<br /></span><input id="customSkin" maxlength="150" size="70" type="text" value="" placeholder="http://" onchange="createURL()" /></h3>';
		$output .= '<h3><span class="gameColor5">Width: </span><input id="customWidth" maxlength="7" size="7" type="text" value="" placeholder="300" onchange="createURL()" />';
		$output .= '&nbsp;&nbsp;&nbsp;<span class="gameColor5">Height: </span><input id="customHeight" maxlength="7" size="7" type="text" value="" placeholder="300" onchange="createURL()" /></h3><h4><span class="gameColor1">Warning:</span> <span class="gameColor3">' . trackerName() . ' cannot detect problems with custom skins!</span></h4>';

		$output .= '<p><a class="dynamicFormButtons dynamicFormButtonsStyle" onclick="expandContractDiv(\'skinDownloadList\')">Downloadable skin list</a></p>';
		$output .= '<div id="skinDownloadList" class="skinDownloadSelections collapsedFrame">';

	//Loop through the array of stuff again, this time to assemble a downloadable list of skins
	for($i = 2; $i < count($directoryList); $i++)
	{
		//Ignore json.css and custom.css (which cannot exist), and make sure the file extension on the detected file is ".css"
		if(strtolower($directoryList[$i]) != "json.css" && strtolower($directoryList[$i]) != "custom.css" && substr(strtolower($directoryList[$i]), -4) == ".css")
		{
			$output .= '<a href="' . skinsPath . rawurlencode($directoryList[$i]) . '" class="skinDownloadLink" download>' . $directoryList[$i] . '</a><br />';
		}
	}


		$output .= '</div></div><br />';

	$output .= '<p class="dynamicPageWidth"><a onclick="expandContractDiv(' . "'colorSelections'" . ')" class="dynamicFormButtons dynamicFormButtonsStyle"> Show/Hide Visual Adjustments </a></p>';

	$output .= '<div id="colorSelections" class="collapsedFrame">';
	$output .= '<div class="colorSelections">';

	$output .= '<h2 class="gameColor3">Tracker Settings</h2>';
		$output .= '<p><input type="checkbox" id="displayGameName" onchange="createURL()" checked /> <span class="gameColor7">Display game name</span></p>';

		$output .= '<p><input type="checkbox" id="filterOffendingServerNameSymbols" onchange="createURL()" checked /> <span class="gameColor7">Trim nonsense characters from server names</span></p>';

	if(enableAutoRefresh == "1")
	{
		$output .= '<p><input type="checkbox" id="enableAutoRefresh" onchange="createURL()" checked /> <span class="gameColor7">Enable Auto-Refresh</span></p>';
	}

	if(enableGeoIP == "1")
	{
		$output .= '<p><input type="checkbox" id="enableGeoIP" onchange="createURL()" checked /> <span class="gameColor7">Enable GeoIP Country Flags</span></p>';
	}

	if(levelshotTransitionsEnabled == "1")
	{
		$output .= '<div class="levelshotBorder"><p style="margin-top:0;"><input type="checkbox" id="levelshotsEnabled" onchange="createURL()" checked /><span class="gameColor7">Enable automatic levelshot transitions</span></p>';
		$output .= '<span class="gameColor2">Display Time:&nbsp;</span><input id="levelshotDisplayTime" maxlength="5" size="3" type="text" value="" placeholder="' . levelshotDisplayTime . '" onchange="createURL()" />';
		$output .= '&nbsp;&nbsp;&nbsp;<span class="gameColor1">Transition Time:&nbsp;</span><input id="levelshotTransitionTime" maxlength="5" size="3" type="text" value="" placeholder="' . levelshotTransitionTime . '" onchange="createURL()" /><br /><span class="smallText">(Times are given in seconds. Decimals are accepted.)</span><br />';

		if(enableCustomDefaultLevelshots)
		{
			$output .= '<br /><span class="gameColor5">Custom default levelshot</span> <span class="smallText">(Used only when none is found):</span><br />
			<input id="customDefaultLevelshot" size="55" type="text" value="" onchange="createURL()" /><br />';
		}

		$output .= '<br><p><a class="dynamicFormButtons dynamicFormButtonsStyle" onclick="expandContractDiv(\'transitionList\')">Choose Transitions</a></p>';

		$output .= '<div id="transitionList" class="transitionListSelections collapsedFrame">

		<div class="transitionButtonContainer"><a class="dynamicFormButtons dynamicFormButtonsStyle" onclick="selectAllTransitions()">Select All</a></div>
		<div class="transitionButtonContainer"><a class="dynamicFormButtons dynamicFormButtonsStyle" onclick="selectNoTransitions()">Select None</a></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition1" checked="checked" onchange="createURL()"> Fade<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition1;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition2" checked="checked" onchange="createURL()"> Smooth Fade<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition2;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition3" onchange="createURL()"> Hue Shift<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition3;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition4" onchange="createURL()"> Skew<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition4;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition5" onchange="createURL()"> Horizontal Stretch<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition5;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition6" onchange="createURL()"> Stretch and Rebound<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition6;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition7" onchange="createURL()"> Slide Left<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition7;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition8" onchange="createURL()"> Slide Right<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition8;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition9" onchange="createURL()"> Slide Top<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition9;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition10" onchange="createURL()"> Slide Bottom<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition10;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition11" onchange="createURL()"> Spin, Fly Left<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition11;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition12" onchange="createURL()"> Spin, Fly Right<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition12;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition13" onchange="createURL()"> Fall Away<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition13;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition14" onchange="createURL()"> Zoom<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition14;" class="sampleOne"></div>
		</div></div>

		<div class="transitionSampleContainer"><input type="checkbox" id="transition15" onchange="createURL()"> Blur<br><div class="levelshotSample">
		<div class="sampleTwo"></div><div style="animation-name:levelshotTransition15;" class="sampleOne"></div>
		</div></div>

		</div>';

		$output .= '</div>';

	}

	$output .= '<h2 class="gameColor3">Colors</h2><h4>All colors are in hexadecimal (#123456).<br /><span class="smallText">These settings do not apply to JSON skins.</span></h4><p><span class="gameColor2">Background Color:</span>&nbsp;&nbsp;# <input id="backgroundColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
	$output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="backgroundOpacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';
	$output .= '<p><span class="gameColor0">Player List Color 1:</span>&nbsp;&nbsp;# <input id="playerListColor1" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
	$output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="playerListColor1Opacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';
	$output .= '<p><span class="gameColor9">Player List Color 2:</span>&nbsp;&nbsp;# <input id="playerListColor2" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
	$output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="playerListColor2Opacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';

	/*	Commented out because the scroll bars have been removed. The validation and other functionality for them remains in place.
	$output .= '<p>';
	$output .= '<span class="gameColor5">Scroll Shaft Color</span>&nbsp;&nbsp;# <input id="scrollShaftColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';

//    $output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="scrollShaftOpacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %';

	$output .= '</p>';
	$output .= '<p><span class="gameColor5">Scroll Thumb Color</span>&nbsp;&nbsp;# <input id="scrollThumbColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
*/

	$output .= '<h2><span class="gameColor3">Text</span><br /><span class="smallText">These settings do not apply to JSON skins.</span></h2>';
	$output .= '<p><span class="gameColor7">Text Color:</span>&nbsp;&nbsp;# <input id="textColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /><span class="gameColor7">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Font:</span>&nbsp;&nbsp;<input id="font" maxlength="50" size="30" type="text" value="" onchange="createURL()" /><br /><span class="smallText">(Color changes do not affect colorized text)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Font families are also acceptable)</span></p>';

	$output .= '</div></div>';
	$output .= '<p class="dynamicPageWidth"><button type="button" class="dynamicFormButtons dynamicFormButtonsStyle" onclick="createURL()"> Generate! </button></p>

	<div id="paraTrackerTestFrame" class="collapsedFrame" ><h2>Sample Tracker:</h2>
	<p class="smallText">
	<input type="checkbox" id="textureToggle" value="on" onchange="toggleTextureBackground()"> Toggle texture background<br />
	<input type="checkbox" id="backgroundToggle" value="on" onchange="toggleGradientBackground()"> Toggle colored background<br />
	(Useful for testing transparency)</p>
	<div id="paraTrackerTestFrameContent2" class="paraTrackerTestFrame"><div id="paraTrackerTestFrameContent" class="" ></div></div></div>

	<p class="dynamicPageWidth"><br />Direct link:<br /><textarea rows="5" cols="120" id="finalURL" readonly></textarea></p>
	<p class="dynamicPageWidth">HTML code to insert on a web page:<br /><textarea rows="7" cols="120" id="finalURLHTML" readonly></textarea></p>

	</form>
	<h6 class="dynamicPageWidth">Trademark&#8482; Pen Pineapple Apple Pen, no rights deserved. The use of this product will not cavse any damnification to your vehicle.</h6>
	<h6 class="dynamicPageWidth">
WE COMPLY WITH ALL LAWS AND REGULATIONS REGARDING THE USE OF LAWS AND REGULATIONS. WE PROMISE THAT THIS THING IS A THING. THIS THING COLLECTS INFORMATION. THIS INFORMATION IS THEN USED TO MAKE MISINFORMATION. THIS MISINFORMATION IS THEN SOLD TO THE MOST NONEXISTENT BIDDER. BY READING THIS, YOU AGREE. CLICK NEXT TO CONTINUE. OTHERWISE, CONTINUE ANYWAY AND SEE IF WE CARE. WOULD YOU LIKE TO SET ' . strtoupper(trackerName()) . ' AS YOUR HOME PAGE? TOO BAD, WE DID IT ALREADY. WE ALSO INSTALLED A BROWSER TOOLBAR WITHOUT ASKING, BECAUSE TOOLBARS ARE COOL AND SO ARE WE.
</h6>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />Nope, nothing here. Promise.
	</div>
	</body>
	</html>';

	echo '-->' . $output;

	exit();
}

function getSkinFileDimensions($skinFile)
{
	$width = 0;
	$height = 0;

		$skinFile = readFileIn(skinsPath . $skinFile . ".css");

		//Declare a variable to be used as a strpos offset counter
		$strOffset = strpos($skinFile, ".ParaTrackerSize");

		//Size data is kept in the class ".ParaTrackerSize". First let's see if it exists in this file at all.
		while($strOffset !== false)
		{
			$strOffset = strpos($skinFile, "{", $strOffset) + 1;

			//Now that we've found it, we need to load in everything between the brackets
			$skinResolution = substr($skinFile, $strOffset, strpos($skinFile, "}", $strOffset) - $strOffset);

			//Now that we've got the styles we need, we should put them into an array
			$skinResolution = explode(";", $skinResolution);

			for($k = 0; $k < count($skinResolution); $k++)
			{
				$testCondition = explode(":", $skinResolution[$k]);
				if(strtolower(trim($testCondition[0])) == "width")
				{
					//Remove the "px" suffix and trim the value, since it will only clutter up the results
					$width = trim(str_ireplace("px", "", $testCondition[1]));
				}
				if(strtolower(trim($testCondition[0])) == "height")
				{
					//Remove the "px" suffix and trim the value, since it will only clutter up the results
					$height = trim(str_ireplace("px", "", $testCondition[1]));
				}
			}
			$strOffset = strpos($skinFile, ".ParaTrackerSize", $strOffset);
		}
	return array($width, $height);
}

function makeDynamicAddressPath($serverIPAddress, $serverPort)
{
	return strtolower($serverIPAddress . "-" . $serverPort . "/");
}

function breakDynamicAddressPath($dynamicIPAddressPath)
{
	$dynamicIPAddressPath = rtrim($dynamicIPAddressPath, "/");
	$split = strrpos($dynamicIPAddressPath, "-");
	$serverAddress = strtolower(substr($dynamicIPAddressPath, 0, $split));
	$serverPort = intval(substr($dynamicIPAddressPath, $split + 1));

	return (array($serverAddress, $serverPort));
}

function connectToServerAndGetResponse($serverIPAddress, $serverPort, $messageToSend)
{
		$dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);
		$s = "";
		$errstr = "";

		$fp = fsockopen("udp://" . $serverIPAddress, $serverPort, $errno, $errstr, 30);
		if(!fwrite($fp, $messageToSend))
		{
			$errstr = "Could not open the connection to the game server!\n<br>Make sure your web host allows outgoing connections.";
			displayError("Could not open the connection to the server at '" . $dynamicIPAddressPath . "'    Make sure your web host allows outgoing connections!", time(), $dynamicIPAddressPath);
			writeFileOut(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
			return false;
		}
		else
		{
			$connectionTimer = microtime(true);
			stream_set_timeout($fp, connectionTimeout);
			$s = fread($fp, maximumServerInfoSize);
			fclose($fp);
		}

		if(strlen($s) >= maximumServerInfoSize)
		{
			displayError('Received maximum data allowance!<br />' . strlen($s) . ' bytes received, the limit is ' . maximumServerInfoSize . '<br />Check to see if you are connected to the correct server or increase $maximumServerInfoSize in ParaConfig.php.', time(), $dynamicIPAddressPath);
			return false;
		}

		if(!$s)
		{
			if(microtime(true) - $connectionTimer >= connectionTimeout)
			{
				$errstr = "No response.";
				writeFileOut(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
				return false;
			}
			else
			{
				//I think if we get here the connection was refused....but I'm not sure. So we'll go with the same message as above
//                $errstr = "Connection refused.";
				$errstr = "No response.";
				writeFileOut(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
				return false;
			}
		}
		else
		{
			//Convert encoding from ANSI to ASCII. If this fails due to illegal characters, leave it as-is.
			return(convertFromANSI($s));
		}

		return($s);
}

function convertFromANSI($input)
{
	//This function converts the incoming string from ANSI to UTF-8, and checks for success.
	//If it fails, it outputs the string it was given.

	$convertedEncoding = mb_convert_encoding($input, "UTF-8", "Windows-1252");

		if($convertedEncoding !== false)
		{
			$input = $convertedEncoding;
		}
		return $input;
}

function sendReceiveRConCommand($serverIPAddress, $serverPort, $RConPassword, $RConCommand, $clientAddressName = clientAddress)
{
	$dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);

	//Make sure the log file exists before we do anything else
	if(!checkFileExistence(serverSecurityLogFilename, logPath . $dynamicIPAddressPath)) return 'Could not create RCon log file! Command not sent.';

	$serverResponse = "";
	$output = "";
	$s = "";
	$SecurityLog = "";
	$newSecurityLogEntry = "";

	if ($RConPassword != "" && $RConCommand != "")
	{
		$output .= '';

//		It seems the chr(02) part breaks RCon. Medal of Honor requires the chr(02) in getstatus requests....I do not know if RCon will work without it, and I cannot test it. However, it seems
//		that other games are broken by the chr(02) and thus it must not be sent.
//		$s = connectToServerAndGetResponse($serverIPAddress, $serverPort, str_repeat(chr(255),4) . chr(02) . 'RCon ' . $RConPassword . ' ' . $RConCommand);
		$command = str_repeat(chr(255),4) . 'RCon ' . $RConPassword . ' ' . $RConCommand;

		//For the DDOS key to be effective, it has to also affect RCon.
		//RCon requires a \n before the key, or it will screw up the commands.
		$serverDDOSKey = getServerSettingValueFromFile(makeDynamicAddressPath($serverIPAddress, $serverPort), "serverDDOSKey");
		if($serverDDOSKey !== null)
		{
			$command .= "\n" . $serverDDOSKey;
		}


		$s = connectToServerAndGetResponse($serverIPAddress, $serverPort, $command);

		if($s)
		{
			$serverResponse = $s;

			//Replace line breaks for the security log only
			$newSecurityLogEntry = str_replace(chr(0x0A), '\n', $serverResponse);

			//Now we format the remaining data in a readable fashion
			$serverResponse = str_replace('ÿÿÿÿprint', '', $serverResponse);
			$serverResponse = str_replace(chr(0x0A), '<br>', trim($serverResponse));

			//Check for exploits in the response that might trigger some PHP code
			$serverResponse = stringValidator($serverResponse, "", "");

			//stringValidator disables HTML tags - so restore line breaks only, for readability
			$serverResponse = str_replace('&lt;br&gt;', "<br>", trim($serverResponse));
		}
		else
		{
			$serverResponse = 'No response from server at ' . $serverIPAddress . ':' . $serverPort . '!';
			$newSecurityLogEntry = $serverResponse;
		}

		$output .= $serverResponse;
	}


	//Log time!
	$SecurityLog = "RCON Command: " . $RConCommand . "  Response: " . $newSecurityLogEntry;
	writeSecurityLogEntry($dynamicIPAddressPath, $SecurityLog);


	//Email time!
	if(emailEnabled && serverToolsEnabled && !defined('doNotEmailForRCON'))
	{
		$serverSettings = readSettingsFile($dynamicIPAddressPath);
		if(isset($serverSettings['emailAlerts']['reasons']['rconCommandSent']['active']) && $serverSettings['emailAlerts']['reasons']['rconCommandSent']['active'])
		{
			$subject = 'RCON Usage';
			$message = '<p>This message was sent to notify you that ' . trackerName() . ' has been used to send an RCON command to your game server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail($serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . $serverPort . '</span>.</p>
			<p>If this action was not by you or another server administrator, your game server\'s RCON password is compromised and should be changed immediately.</p>
			<h3 style="' . emailHeadingColor3 . '">RCON Command: </h3>
			<p>' . fixHyperlinksForGmail(stringValidator($RConCommand, 10000, '')) . '</p>
			<h3 style="' . emailHeadingColor4 . '">Game Server Response: </h3>
			<p>' . fixHyperlinksForGmail(stringValidator($serverResponse, 10000, '')) . '</p>';

			sendEmailAlertsWithUnsubscribeTokens($serverIPAddress, $serverPort, $serverSettings, $subject, $message);
		}
	}

	return $output;
}

function getWebServerName()
{
	if(isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) return $_SERVER['SERVER_NAME'];
	return '';
}


function renderNormalHTMLPage($dynamicIPAddressPath)
{
	$output = "";

	//Let's add in the dynamic colors...
	//If dynamic mode is disabled these will already have been declared as null, so no worries
	//Also, these will not be sent with the JSON response, so they must be included here

	if(backgroundColor != "")
	{
		$output .= '<style>.BackgroundColorImage{background: none; background-color: rgba(' . convertToRGBA(backgroundColor) . ', ' . backgroundOpacity / 100  . ');}</style>';
	}
	if(textColor != "")
	{
		$output .= '<style>.textColor{color: #' . textColor . ';}</style>';
	}
	if(customFont != "")
	{
		$output .= '<style>.textColor{font-family: ' . customFont . ';}</style>';
	}
	if(playerListColor1 != "")
	{
		//background: none; removes the old background (Gradient, for instance) before applying the new color.
		$output .= '<style>.playerRow1{background: none; background-color: rgba(' . convertToRGBA(playerListColor1) . ', ' . playerListColor1Opacity / 100  . ');}</style>';
	}
	if(playerListColor2 != "")
	{
		//background: none; removes the old background (Gradient, for instance) before applying the new color.
		$output .= '<style>.playerRow2{background: none; background-color: rgba(' . convertToRGBA(playerListColor2) . ', ' . playerListColor2Opacity / 100  . ');}</style>';
	}
	/*
	if(scrollShaftOpacity != "")
	{
		$output .= '<style>::-webkit-scrollbar{opacity: ' . scrollShaftOpacity / 100 . ');}</style>';
	}
	*/
	if(scrollShaftColor != "")
	{
		$output .= '<style>::-webkit-scrollbar-track{background-color: rgba(' . convertToRGBA(scrollShaftColor) . ', 100);}</style>';
	}
	if(scrollThumbColor != "")
	{
		$output .= '<style>::-webkit-scrollbar-thumb{background-color: rgba(' . convertToRGBA(scrollThumbColor) . ', 100);}</style>';
	}


	//Add the JSON stuff here so we have data to work with
	$output .= '<script src="js/ParaScript.js"></script><script>document.addEventListener("DOMContentLoaded", function(event){firstExecution();})</script><script>;
	data = ' . renderJSONOutput($dynamicIPAddressPath) . ';
	var JSONOutput = "";
	</script>';

	$output .= '</head>';

	//This adds the default formatting to the page. It removes the padding and margins, sets the size, and hides any overflow.
	$output .= '<body class="ParaTrackerPage textColor">';

	//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
	$output .= '<div class="ParaTrackerSize textColor">';

	//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
	$output .= '<div class="BackgroundColorImage textColor">';

	//This adds six custom DIVs to the page, to be used however the skin creator desires.
	$output .= '<div class="CustomDiv1 textColor"></div>';
	$output .= '<div class="CustomDiv2 textColor"></div>';
	$output .= '<div class="CustomDiv3 textColor"></div>';
	$output .= '<div class="CustomDiv4 textColor"></div>';
	$output .= '<div class="CustomDiv5 textColor"></div>';
	$output .= '<div class="CustomDiv6 textColor"></div>';

	//This adds the ParaTracker logo to the page.
	$output .= '<div class="ParaTrackerLogo textColor" title="Click to launch ' . webServerName . '" onclick="setup_window()"></div>';

	//This adds the ParaTracker text to the page.
	$output .= '<div id="paraTrackerVersion" class="ParaTrackerText textColor" title="Click to launch ' . webServerName . '" onclick="setup_window()">' . strval(versionNumber()) . '</div>';

	//This adds the server name to the page.
	$output .= '<div id="serverName" class="serverName color7 textColor"></div>';

	//This adds the optional country flag to the page. This feature only works when GeoIP is installed.
	$output .= '<div id="geoIPFlag" class="countryFlag textColor"></div>';

	//This adds the game name to the page.
	$output .= '<div id="gameTitle" class="gameTitle textColor"></div>';

	//This adds the player list table to the page.
	$output .= '<div id="playerList" class="playerTable textColor"></div>';

	//This adds the player count to the page.
	$output .= '<div id="playerCount" class="playerCount textColor"></div>';

	//This adds the map name to the page.
	$output .= '<div id="mapName" class="mapName textColor"></div>';

	//This adds the mod name to the page.
	$output .= '<div id="modName" class="modName textColor"></div>';

	//This adds the gametype to the page.
	$output .= '<div id="gametype" class="gametype textColor"></div>';

	//This adds the IP Address and blinking cursor to the page.
	$output .= '<div id="IPAddress" class="IPAddress textColor"><span id="blinker" class="blinkingCursor" style="animation-duration: 1s; animation-fill-mode: forwards; animation-iteration-count: infinite; animation-name: blink;">_</span></div>';

	//This adds the ping of the game server's response to the web server.
	$output .= '<div id="serverPing" class="ServerPing textColor"></div>';

	//This adds the error message to the page, if the tracker fails to connect
	$output .= '<div id="errorMessage" class="paraTrackerError textColor"><br /><br /></div>
	<div id="errorAddress" class="paraTrackerErrorAddress textColor"></div>
	</div>';

	//This adds the name score ping header to the page.
	$output .= '<div id="nameScorePing" class="nameScorePing textColor"><div id="nameHeader" title="Click to sort players by name" onclick="sortPlayersByNameClick()" class="playerNameSize playerNameHeader textColor"></div><div id="scoreHeader" title="Click to sort players by score" onclick="sortPlayersByScoreClick()"class="playerScoreSize playerScoreHeader textColor"></div><div id="pingHeader" title="Click to sort players by ping" onclick="sortPlayersByPingClick()"class="playerPingSize playerPingHeader textColor"></div><div id="teamHeader"  onclick="sortPlayersByTeamClick()"class="playerTeamSize playerTeamHeader textColor"></div></div>';

	if(analyticsFrontEndEnabled == "1")
	{
		//If analytics are enabled, this adds the analytics button to the page.
		$output .= '<div id="analyticsButton" onclick="analytics_window();"><div id="analyticsButtonText" class="analyticsButton textColor" title="Click to launch ' . trackerName() . ' Analytics"></div></div>';
	}

	//This adds the RCon button to the page.
	$output .= '<div id="rconButton" onclick="rcon_window();"><div id="rconButtonText" class="rconButton textColor"></div></div>';

	//This adds the Param button to the page.
	$output .= '<div id="paramButton" onclick="param_window();"><div id="paramButtonText" class="paramButton textColor"></div></div>';

	//This adds the reconnect button to the page
	$output .= '<div id="reconnectButton" onclick="pageReload();"><div id="reconnectButtonText" class="reconnectButton textColor"></div></div>';

	//This adds the levelshots to the page.
	$output .= '<div id="levelshotPreload2" class="levelshotFrame levelshotSize textColor">
	<div id="levelshotPreload1" class="levelshotSize"></div>
	<div id="bottomLayerFade" class="levelshotSize"></div>
	<div id="topLayerFade" class="levelshotSize mapreqTextPlacement"></div>
	<div id="loading" class="levelshotSize loadingLevelshot textColor collapsedFrame"></div>
	</div>';

	//This adds the frame to the page.
	$output .= '<div class="TrackerFrame textColor"></div>';

	//This line adds a compatibility warning in case JavaScript is disabled
	$output .= '<noscript>JavaScript is disabled. ' . trackerName() . ' requires JavaScript!</noscript>';

	//This adds the refresh timer and the timer script to the page.
	$output .= '<div onclick="toggleReload()"><div id="refreshTimer" class="reloadTimer textColor" title="Click to cancel auto-refresh"></div></div>';

	$output .= '</div></div>
	</body>
	</html>';

	return $output;
}

function makeDynamicIPAddressPathNumeric($dynamicIPAddressPath)
{
	$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
	$serverIPAddress = "$brokenAddress[0]";
	$serverPort = $brokenAddress[1];
	$dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);

	return $dynamicIPAddressPath;
}

function renderJSONOutput($dynamicIPAddressPath)
{
	$outputArray = array();
	$tempArray = array();

	$dynamicIPAddressPath = makeDynamicIPAddressPathNumeric($dynamicIPAddressPath);

	$outputArray = renderParaTrackerSettingsJSON($dynamicIPAddressPath);

	//Assemble the server-specific info
	$tempArray = renderServerInfoJSON($dynamicIPAddressPath);
	$count = count($tempArray);
	for($i = 0; $i < $count; $i++)
	{
		array_push($outputArray, $tempArray[$i]);
	}

	return JSONObject("", $outputArray);
}

function renderAllTrackedServers($forceCachedInfo = false)
{
	$addressArray = array();
	$outputArray = array();

	$output = renderParaTrackerSettingsJSON();

	//The database takes a while to respond. It is much faster to just used the cached files for this
	if(false && analyticsEnabled && $forceCachedInfo !== true)
	{
		//Get a list from the database.
		$serverList = getServerListFromDatabase();
		$count = count($serverList);
		for($i = 0; $i < $count; $i++)
		{
			array_push($addressArray, $serverList[$i]["location"] . "-" . $serverList[$i]["port"] . "/");
		}

	}
	else
	{
		//No database, so just use the entire info folder.
		$addressArray = getDirectoryOnlyList(infoPath);
		$count = count($addressArray);
		for($i = 0; $i < $count; $i++)
		{
			$addressArray[$i] .= '/';
		}
	}

	//Cull private servers from the public list
	$count = count($addressArray);
	for($i = 0; $i < $count; $i++)
	{
		$check = getServerSettingValueFromFile($addressArray[$i], 'serverIsPrivate');
		if($check === true) continue;
		array_push($outputArray, $addressArray[$i]);
	}

	array_push($output, JSONArray("serverList", renderMultipleServerJSONOutput($outputArray), 4));

	return JSONObject("", $output);
}

function renderMultipleServerJSONOutput($dynamicIPAddressPathArray)
{
	$outputArray = array();
	$tempArray = array();
	$serverOutputArray = array();

	//Assemble the server-specific info
	$count = count($dynamicIPAddressPathArray);
	for($i = 0; $i < $count; $i++)
	{
		$results = renderServerInfoJSON($dynamicIPAddressPathArray[$i]);
		if(count($results) > 0)
		{
			array_push($tempArray, $results);
		}
	}

	return $tempArray;
}

function renderParaTrackerSettingsJSON()
{
	$outputArray = array();
	$serverSettings = array();

	//Add the tracker settings
	array_push($outputArray, JSONString("tracker", trackerName()));
	array_push($outputArray, JSONString("version", trackerVersion()));
	array_push($outputArray, JSONNumber("reconnectTimeout", floodProtectTimeout));
	array_push($outputArray, JSONBoolean("mapreqEnabled", mapreqEnabled));
	array_push($outputArray, JSONString("mapreqTextMessage", mapreqTextMessage));
	array_push($outputArray, JSONString("noPlayersOnlineMessage", noPlayersOnlineMessage));
	array_push($outputArray, JSONNumber("autoRefreshTimer", autoRefreshTimer));
	array_push($outputArray, JSONBoolean("RConEnable", RConEnable));
	array_push($outputArray, JSONNumber("RConFloodProtect", RConFloodProtect));
	array_push($outputArray, JSONString("utilitiesPath", utilitiesPath));

	//Add the server settings which can be customized by the users
	array_push($serverSettings, JSONBoolean("enableGeoIP", enableGeoIP));  //Used to test whether GeoIP is enabled
	array_push($serverSettings, JSONBoolean("enableAutoRefresh", enableAutoRefresh));  //Used to disable the auto-refresh timer
	array_push($serverSettings, JSONBoolean("levelshotTransitionsEnabled", levelshotTransitionsEnabled));
	array_push($serverSettings, JSONNumber("levelshotTransitionAnimation", levelshotTransitionAnimation));
	array_push($serverSettings, JSONBoolean("displayGameName", displayGameName));
	array_push($serverSettings, JSONBoolean("filterOffendingServerNameSymbols", filterOffendingServerNameSymbols));
	array_push($serverSettings, JSONNumber("levelshotTransitionTime", levelshotTransitionTime)); //This value takes the transition time value given in ParaConfig and passes it to the Javascript.
	array_push($serverSettings, JSONNumber("levelshotDisplayTime", levelshotDisplayTime)); //This value takes the display time given in ParaConfig and passes it to the Javascript.
	array_push($serverSettings, JSONNumber("allowTransitions", levelshotTransitionsEnabled));  //Used to test whether fading levelshots is disabled.
	array_push($serverSettings, JSONString("customDefaultLevelshot", customDefaultLevelshot));

	return array_merge($outputArray, $serverSettings);
}

function renderServerInfoJSON($dynamicIPAddressPath)
{
	$outputArray = array();

	$necessaryFiles = array(	infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt",
								infoPath . $dynamicIPAddressPath . "JSONParsedInfo.txt",
								infoPath . $dynamicIPAddressPath . "JSONParams.txt",
								infoPath . $dynamicIPAddressPath . "JSONPlayerInfo.txt",
								infoPath . $dynamicIPAddressPath . "JSONServerInfo.txt");
	for($i = 0; $i < count($necessaryFiles); $i++)
	{
		if(!file_exists($necessaryFiles[$i]))
		{
			$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
			array_push($outputArray, '"serverInfo":{"serverOnline":false,"serverIPAddress":"' . $brokenAddress[0] . '","serverPort":' . intval($brokenAddress[1]) . ',"connectionErrorMessage":"Error connecting to server!"}');
			return $outputArray;
		}
	}

	array_push($outputArray, JSONString("connectionErrorMessage", readFileIn(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt")));

	//This info must always be included
	$serverInfo = readFileIn(infoPath . $dynamicIPAddressPath . "JSONServerInfo.txt");
	array_push($outputArray, $serverInfo);

	//Now let's include the rest of the data (It is already parsed in several text files)
	$parsedInfo = readFileIn(infoPath . $dynamicIPAddressPath . "JSONParsedInfo.txt");
	$params = readFileIn(infoPath . $dynamicIPAddressPath . "JSONParams.txt");
	$players = readFileIn(infoPath . $dynamicIPAddressPath . "JSONPlayerInfo.txt");

	//If these files are empty, then the server connection failed, so don't bother with them
	if($parsedInfo != "" && $params != "" && $players != "")
	{
		array_push($outputArray, $parsedInfo);
		array_push($outputArray, $params);
		array_push($outputArray, $players);
	}

	return $outputArray;
}

function JSONVariable($input)
{
	//If this function was called with no input, it means there was no variable name.
	//So, let's give nothing back.
	if($input == "")
	{
		return "";
	}

	if(substr(trim($input), 0, 1) == '"' && substr(trim($input), -1, 1) == '"')
	{
		//There are quotes around the variable. Remove them.
		$input = substr($input, 1, -1);
	}

	//Double quotes must be removed from variable names, the name must be wrapped in double quotes,
	//and a colon must be added at the end.
	return '"' . replaceQuotes($input) . '":';
}

function removeStrings($input)
{
	$output = "";
	$test = array();

	if(strpos($input, '"') !== 0)
	{
		$test = explode('"', $input);
		$count = count($test);

		for($i = 0; $i < strlen($count); $i = $i + 2)
		{
			$output .= $test[$i];
		}
	}
	else
	{
		return $output;
	}
}

function JSONAutoValidate($input)
{
	//Declare this to make the rest easier
	$variableName = "";

	if(strpos(removeStrings($input), ":") !== 0)
	{
		//Found a colon - let's remove the variable name.
		$exploded = explode(":", removeStrings($input));
		$variableName = JSONVariable($exploded[0]);
		$input = $exploded[1];
	}

	if(is_numeric($input))
	{
		//Numeric input. Validate as a number.
		return JSONNumber($variableName, $input);
	}
	else
	if(strtolower($input) == "true" || strtolower($input) == "false")
	{
		//Boolean input. Validate as boolean.
		return JSONBoolean($variableName, $input);
	}
	else
	{
		if(is_array($input))
		{
			//Array input. Validate as an array.
			return JSONArray($variableName, $input, 0);
		}
		else
		{
			if(substr(trim($input), 0, 1) == "[" && substr(trim($input), -1, 1) == "]")
			{
				//Array input. Validate as an array.
				return JSONArray($variableName, $input);
			}
			else
			{
				if(substr(trim($input), 0, 1) == '"' && substr(trim($input), -1, 1) == '"')
				{
					//Input is already a string. Remove the quotes just in case, validate and return.
					return JSONString($variableName, substr($input, 1, -1));
				}
				else
				{
					//Nothing left, so input must be a string. Validate it.
					return JSONString($variableName, $input);
				}
			}
		}
	}
}

function JSONObject($variableName, $input)
{
	//This function accepts an array, with all the JSON contents pre-validated.
	//Validate the variable name first
	$variableName = JSONVariable($variableName);
/*
	if(is_array($input))
	{
		$input = JSONValidateArray(4, $input);
	}
*/
	//The return is a string formatted as a JSON Object.
	if(is_array($input))
	{
		return $variableName . "{" . implode(",", $input) . "}";
	}
	else
	{
		return $variableName . '{' . $input . '}';
	}
}

function JSONArray($variableName, $input, $mode)
{
	//This function accepts an array, and returns a JSON string
	//Validate the variable name first
	$variableName = JSONVariable($variableName);
	if(is_array($input))
	{
		$input = JSONValidateArray($mode, $input);
	}
	//Check to see if the array is empty - if so, pass that on
	if(empty($input))
	{
		return $variableName . '[]';
	}
	//Return a string of the variable name, and the array delimited with commas
	if(is_array($input))
	{
		return $variableName . '[' . implode(',', $input) . ']';
	}
	else
	{
		return $variableName . '[' . $input . ']';
	}
}

function JSONString($variableName, $input)
{
	//Validate the variable name first
	$variableName = JSONVariable($variableName);
	if(is_array($input))
	{
		$input = JSONValidateArray(3, $input);
	}

	if(!isset($input) || $input == null)
	{
		//Replace quotes on the input string, and return.
		return $variableName . 'null';
	}
	else
	{
		//Replace quotes on the input string, and return.
		return $variableName . '"' . replaceQuotes(stringClean($input)) . '"';
	}
}

function JSONNumber($variableName, $input)
{
	//Validate the variable name first
	$variableName = JSONVariable($variableName);
	$input = strtolower(trim($input));
	if(is_array($input))
	{
		$input = JSONValidateArray(2, $input);
	}
	if(is_numeric($input))
	{
		return $variableName . $input;
	}
	else if($input == "")
	{
		return $variableName . "null";
	}
	else
	{
		return $variableName . "0";
	}
}

function JSONBoolean($variableName, $input)
{
	//Validate the variable name first
	$variableName = JSONVariable($variableName);
	$input = strtolower(trim($input));
	if(is_array($input))
	{
		$input = JSONValidateArray(1, $input);
	}
	if($input === 1 || $input === true || (is_string($input) && ( strtolower($input == "t") || $input == "1" || $input == "true" || $input == "yes") ))
	{
		return $variableName . "true";
	}
	else
	{
		return $variableName . "false";
	}
}

function JSONValidateArray($mode, $input)
{
	//Validate $mode. 0 means do not validate.
	if(isset($mode))
	{
		$mode = numericValidator($mode, 0, 5, 0);
	}
	else
	{
		$mode = 0;
	}
	$count = count($input);
	//Mode 1 validates as a boolean
	if($mode == "1")
	{
		for($i = 0; $i < $count; $i++)
		{
				$input[$i] = JSONBoolean("", $input[$i]);
		}
	}
	//Mode 2 validates as a number
	elseif($mode == "2")
	{
		for($i = 0; $i < $count; $i++)
		{
				$input[$i] = JSONNumber("", $input[$i]);
		}
	}
	//Mode 3 validates as a string
	elseif($mode == "3")
	{
		for($i = 0; $i < $count; $i++)
		{
				$input[$i] = JSONString("", $input[$i]);
		}
	}
	//Mode 4 validates as an object
	elseif($mode == "4")
	{
		for($i = 0; $i < $count; $i++)
		{
				$input[$i] = JSONObject("", $input[$i]);
		}
	}
	//Mode 5 validates as an array
	elseif($mode == "5")
	{
		for($i = 0; $i < $count; $i++)
		{
				$input[$i] = JSONArray("", $input[$i], 0);
		}
	}
	return $input;
}

function replaceQuotes($input)
{
	//This function replaces incoming double quotes and back slashes with escape characters for JSON validation
	$input = str_replace('\\', "\\\\", $input);
	$input = str_replace('"', '\\"', $input);

	return $input;
}

function checkDatabaseForServerTime($dynamicIPAddressPath)
{
	if($dynamicIPAddressPath == "-" || $dynamicIPAddressPath == "") return 0;
	global $pgCon;

	$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
	$serverIPAddress = "$brokenAddress[0]";
	$serverPort = $brokenAddress[1];

	$server_id_fetch = pg_fetch_row(pg_query_params($pgCon, '
	SELECT id
	FROM tracker.server
	WHERE location = $1
	AND port = $2',
	array($serverIPAddress, $serverPort)));

if (empty($server_id_fetch)) return "0";
	$server_id = $server_id_fetch[0];

	$lastup_fetch = pg_fetch_all(pg_query_params($pgCon, '
	SELECT entrydate
	FROM analytics.frame
	WHERE server_id = $1
	AND record_id IS NOT NULL
	ORDER BY entrydate DESC 
	LIMIT 1',
	array($server_id)));
	if (empty($lastup_fetch)) return "0";
	$lastup = strtotime($lastup_fetch[0]['entrydate']);

	return $lastup;
}

function disableServerInDatabase($input)
{
	if($input == "") return 0;
	$broken = breakDynamicAddressPath($input);

	$serverAddress = "$broken[0]";
	$serverPort = $broken[1];

	global $pgCon;
	if(!pg_query_params($pgCon, 'UPDATE tracker.server SET active = FALSE WHERE location = $1 AND port = $2', array($serverAddress, $serverPort))) return 0;
	return 1;
}

function getSystemLoadAverage()
{
	//This feature only works on Linux.
	//If we are on any other OS, the function needs to return
	//a 0, falsely indicating 0 load so as to not break anything.
	$loadAverage = 0;
	if(function_exists("sys_getloadavg"))
	{
		$loadArray = sys_getloadavg();
		$cpuCores = shell_exec("cat /proc/cpuinfo | grep processor | wc -l");

		$loadAverage = $loadArray[2] / intval(trim($cpuCores));
	}
	return round($loadAverage * 100, 2);
}

function getDirectoryList($input)
{
	$directoryList = scandir($input);

	$output = array();

	$count = count($directoryList);
	for($i = 0; $i < $count; $i++)
	{
		if($directoryList[$i] == "." || $directoryList[$i] == ".."  || $directoryList[$i] == "" )
		{
			continue;
		}
		array_push($output, $directoryList[$i]);
	}
	return $output;
}

function getDirectoryOnlyList($input)
{
	$directoryList = scandir($input);

	$output = array();

	$count = count($directoryList);
	for($i = 0; $i < $count; $i++)
	{
		if(!is_dir($input . '/' . $directoryList[$i]) || $directoryList[$i] == "." || $directoryList[$i] == ".."  || $directoryList[$i] == "")
		{
			continue;
		}
		array_push($output, $directoryList[$i]);
	}
	return $output;
}

function cleanupInfoFolder($cleanupInterval, $deleteInterval, $loadLimit, $cleanupLogSize, $forced)
{
	$loadAverage = getSystemLoadAverage();

	$forced = booleanValidator($forced, false);

	//Let's make these useful
	//Anything LESS than $deleteInterval will be deleted
	$deleteInterval = $deleteInterval * 86400;
	$deleteInterval = time() - $deleteInterval;
	$cleanupInterval = $cleanupInterval * 60;

	//Get the info folder's timeout file and validate it
	$currentTimer = numericValidator(readFileIn(infoPath . "cleanupTimer.txt"), "", "", "0");

	//If the delete interval is larger than the current timer, ParaTracker was likely offline or not working, so no data should be cleaned up this time.
	//Running cleanup wrongfully will mess up analytics.
	if($currentTimer < $deleteInterval)
	{
		$warningMessage = date(DATE_RFC2822) . ": Warning: massive gap in cleanup timer detected! " . trackerName() . " assumed offline for an extended period of time. Foregoing cleanup and resetting timer to preserve data.";
		writeToLogFile("cleanupLog.php", array($warningMessage, ""), $cleanupLogSize);
		echo $warningMessage;
		writeFileOut(infoPath . "cleanupTimer.txt", time());
		return "";
	}

	if(($currentTimer + $cleanupInterval < time() && $currentTimer > 0) || $forced)
	{
		$totalCleanupTimer = microtime(true);
		$cleanupTimer = microtime(true);

		//Prevent users from aborting the page so the cleanup will always finish!
		ignore_user_abort(true);

		//Ignore the time limit so the cleanup always gets a chance to run completely
		set_time_limit(0);

		//First thing's first, we need to prevent a second cleanup from running.
		writeFileOut(infoPath . "cleanupTimer.txt", time());

		//Let's start a new cleanup log entry.
		$cleanupLog = array("Running cleanup on " . date(DATE_RFC2822) . ":");

		if($loadAverage > $loadLimit && !$forced)
		{
			array_push($cleanupLog, "Server load is too high! Load is currently " . $loadAverage . "%, the limit is " . $loadLimit . "%. Cleanup cancelled.");
		}
		else
		{
			if($forced)
			{
				array_push($cleanupLog, "Forced to run by admin. Server at " . $loadAverage . "% load, threshold for cancellation is " . $loadLimit . "%. Starting cleanup...");
			}
			else
			{
				array_push($cleanupLog, "Server load OK! " . $loadAverage . "% load, threshold for cancellation is " . $loadLimit . "%. Starting cleanup...");
			}

			//Time to run cleanup!
			echo " Running cleanup... ";
			
			if(serverToolsEnabled && emailEnabled) include_once utilitiesPath . 'ServerTools.php';	// We'll need this later on to send emails

			$directoryList = getDirectoryList(infoPath);
			$count = count($directoryList);

			//Loop through the array of folders, and check the time values on everything. If the folder hasn't been refreshed in a while, delete it.
			for($i = 0; $i < $count; $i++)
			{
				//Make sure we are only cleaning up directories, and not files!
				if(is_dir(infoPath . $directoryList[$i]))
				{
					//If we're using analytics, the database value should provide the last refresh time
					if(analyticsEnabled)
					{
						$testTime = checkDatabaseForServerTime($directoryList[$i]);
						if($testTime < $deleteInterval)
						{
							array_push($cleanupLog, "    -> Database requires deletion of:  " . infoPath . $directoryList[$i]);
							$currentInfoFolderTime = $deleteInterval - 1;
						}
						else
						{
							$currentInfoFolderTime = microtime(true);
						}
					}
					else
					{
						//This must be called before calling file_exists!
						clearstatcache();

						//We are not using analytics, so check time.txt for the last time the server was contacted
						if(file_exists(infoPath . $directoryList[$i] . "/time.txt"))
						{
							//If we receive a non-numeric answer, that's because the file reads "wait" - the server is currently being contacted.
							//So, non-numeric values must default to the current time to prevent deletion.
							$currentInfoFolderTime = numericValidator(readFileIn(infoPath . $directoryList[$i] . "/time.txt"), "", "", microtime(true));
						}
						else
						{
							$currentInfoFolderTime = 0;
						}
					}

					if($currentInfoFolderTime < $deleteInterval)
					{
						if(serverToolsEnabled && emailEnabled) {
							// If there is an email address attached to this server, send out the email warning about deletion
							sendServerRemovalEmail($directoryList[$i]);
						}

						array_push($cleanupLog, "    Emptying:  " . infoPath . $directoryList[$i]);
						//The timer is older than the threshold. Delete the entire folder.
						//All files must be deleted before the directory can be removed.
						$directoryList2 = getDirectoryList(infoPath . $directoryList[$i]);
						$count2 = count($directoryList2);
						for($j = 0; $j < $count2; $j++)
						{
							if(!unlink(infoPath . $directoryList[$i] . "/" . $directoryList2[$j]))
							{
								array_push($cleanupLog, "    Failed to delete:  " . infoPath . $directoryList[$i] . "/" . $directoryList2[$j]);
							}
						}
						if(rmdir(infoPath . $directoryList[$i]))
						{
							//Success! Add it to the cleanup log.
							$deletionMessage = '    Deleted:   ' . infoPath . $directoryList[$i];
							if(disableServerInDatabase($directoryList[$i]))
							{
								$deletionMessage .= '  Successfully disabled server in database.';
							}
							else
							{
								$deletionMessage .= '  Failed to disable server in database.';
							}
							array_push($cleanupLog, $deletionMessage);
						}
							else
						{
							//Failed! Add it to the cleanup log.
							$deletionMessage = '    Failed to delete:  ' . infoPath . $directoryList[$i];
							if(analyticsEnabled && disableServerInDatabase($directoryList[$i]))
							{
								$deletionMessage .= '  Successfully disabled server in database.';
							}
							else
							{
								$deletionMessage .= '  Failed to disable server in database.';
							}
							array_push($cleanupLog, $deletionMessage);
						}
					}
					else
					{
						//Commented out because it was generating too many junk messages
						//array_push($cleanupLog, "Ignored:  " . infoPath . $directoryList[$i] . " Time is too soon. " . $currentInfoFolderTime . " < " . $deleteInterval);
					}
				}
			}
			array_push($cleanupLog, 'Done! Took ' . number_format(((microtime(true) - $cleanupTimer) * 1000), 0) . ' milliseconds.');

			//Now let's clean up the map requests, if they are enabled.
			if(mapreqEnabled)
			{
				array_push($cleanupLog, 'Starting cleanup of levelshot requests...');
				$cleanupTimer = microtime(true);
				$mapsArray = mapreq_get_maps();
				$count2 = count($mapsArray);

				$supportedGameList = detectGameName("")[0];

				$previousGame_name = "";
				for($i = 0; $i < $count2; $i++)
				{
					$game_name = $mapsArray[$i]['game_name'];
					$bsp_name = $mapsArray[$i]['bsp_name'];

					//Check to see if the game exists in the first place. If it does not, remove all entries for it.
					if(!checkMatch($game_name, $supportedGameList))
					{
						array_push($cleanupLog, "    Unsupported game: '" . $game_name . "' - removing entry '" . $bsp_name . "'!");
						mapreq_delete_map($game_name, $bsp_name);
						continue;
					}

					//Check to see if we're parsing the same game as before. This should reduce calls to the game parser
					if($game_name != $previousGame_name)
					{
						//Insert game-specific function execution here
						$gameFunctionParserReturn = ParseGameData($game_name, "", "", "", "");
						$levelshotFolder = $gameFunctionParserReturn[1];
					}
					//Now that we're past game detection, set this value
					$previousGame_name = $game_name;

					$shotNumber = count(levelshotfinder("cleanup", $bsp_name, $levelshotFolder, $game_name, "", 1));

					if($shotNumber > 0)
					{
						array_push($cleanupLog, "    Levelshots found for " . $game_name . " - '" . $bsp_name . "', removing request!");
						mapreq_delete_map($game_name, $bsp_name);
					}
				}
				array_push($cleanupLog, 'Done! Took ' . number_format(((microtime(true) - $cleanupTimer) * 1000), 0) . ' milliseconds.');
			}

			if(cullDatabase)
			{
				//The database may need to have old data culled out. Check it!
				array_push($cleanupLog, 'Culling database...');
				$cleanupTimer = microtime(true);

				cullOldDatabaseEntries();
				array_push($cleanupLog, 'Finished! Took ' . number_format(((microtime(true) - $cleanupTimer) * 1000), 0) . ' milliseconds.');
			}

			array_push($cleanupLog, 'Cleanup complete! Took ' . number_format(((microtime(true) - $totalCleanupTimer) * 1000), 0) . ' milliseconds.');

		}
		//Add a space at the end of the cleanup log, for readability
		$cleanupLog = array_merge($cleanupLog, array(""));
		writeToLogFile("cleanupLog.php", $cleanupLog, $cleanupLogSize);

		//Allow users to abort the page again.
		ignore_user_abort(false);

		//Allow the time limit to run as normal again.
		set_time_limit(30);
	}
}

function cullOldDatabaseEntries()
{
	if(cullDatabase)
	{
		global $pgCon;
		$cullTime = strtotime('-' . databaseCullTime . ' days');

		pg_query($pgCon, 'BEGIN');
		pg_query_params($pgCon, 'DELETE FROM analytics.record USING analytics.frame WHERE record_id = record.id AND entrydate < to_timestamp($1)', array($cullTime));
		pg_query_params($pgCon, 'DELETE FROM analytics.frame WHERE entrydate < to_timestamp($1)', array($cullTime));
		pg_query($pgCon, 'COMMIT');
	}
}

function checkMatch($inputGame, $inputArray)
{
	$count = count($inputArray);
	for($i = 0; $i < $count; $i++)
	{
		if($inputGame == $inputArray[$i])
		{
			return true;
		}
	}
	return false;
}

// returns array of hashmap (with 'game_name' and 'bsp_name'), i.e. $data[2]['bsp_name']
function mapreq_get_maps()
{
global $pgCon;
return pg_fetch_all(pg_query($pgCon, 'SELECT game_name, bsp_name FROM mapreq ORDER BY game_name ASC'));
}

function mapreq_delete_map($game_name, $bsp_name)
{
global $pgCon;
pg_query_params($pgCon, 'DELETE FROM mapreq WHERE game_name = $1 AND bsp_name = $2', array($game_name, $bsp_name));
}

function adminInfoGoBackLink()
{
	return '<a href="AdminInfo.php" class="logLink">Go Back</a>';
}

function getOptionLength($input)
{
	$temp = strrpos($input, ':');
	return array($temp, strlen($input) - $temp);
echo "\n" . $temp . "\n";
}

function padOutputAndImplode($input, $glue)
{
	//This function adds spaces to lines at the beginning and end, to align them for the best readability

	$pad = '&nbsp;';

	$LPadMax = 0;
	$RPadMax = 0;
	$count = count($input);
	for($i = 0; $i < $count; $i++)
	{
		$options = getOptionLength($input[$i]);
		$check = $options[0];
		$offset = $options[1];
		if($check > $LPadMax) $LPadMax = $check;
		if($offset > $RPadMax) $RPadMax = $offset;
	}

	for($i = 0; $i < $count; $i++)
	{
		$options = getOptionLength($input[$i]);
		$Lpad = $options[0];
		$Rpad = $options[1];
		$input[$i] = str_repeat($pad, $LPadMax - $Lpad) . $input[$i] . str_repeat($pad, $RPadMax - $Rpad);
	}

	return implode($glue, $input);
}

function padOutputAddHyperlinksAndImplode($input, $glue)
{
	//This function adds spaces to lines at the beginning and end, to align them for the best readability

	//This variable is what is used to pad the output
	$pad = '&nbsp;';

	//This variable is used to pad the port numbers by a specific number.
	$linkSpacing = 3;

	$LPadMax = 0;
	$RPadMax = 0;
	$count = count($input);
	for($i = 0; $i < $count; $i++)
	{
		$options = getOptionLength($input[$i]);
		$check = $options[0];
		$offset = $options[1];
		if($check > $LPadMax) $LPadMax = $check;
		if($offset > $RPadMax) $RPadMax = $offset;
	}

	for($i = 0; $i < $count; $i++)
	{
		$options = getOptionLength($input[$i]);
		$Lpad = $options[0];
		$Rpad = $options[1];
		$ipaddress = explode(":", $input[$i])[0];
		$port = explode(":", $input[$i])[1];

		$input[$i] = '<span class="noSelect">' . str_repeat($pad, $LPadMax - $Lpad) . '</span>' . $input[$i] . '<span class="noSelect">' . str_repeat($pad, $linkSpacing + ($RPadMax - strlen($port))) . '<a href="https://' . getWebServerName() . '/ParaTrackerDynamic.php?ip=' . $ipaddress . '&port=' . $port . '" target="_blank" class="adminTrackLink">Track</a></span>';
	}

	return implode($glue, $input);
}

function padStringBefore($inputString, $length, $padding = '&nbsp;')
{
	$padAmt = $length - strlen($inputString);
	if($padAmt < 1) return $inputString;
	return str_repeat($padding, $padAmt) . $inputString;
}

function padStringAfter($inputString, $length, $padding = '&nbsp;')
{
	$padAmt = $length - strlen($inputString);
	if($padAmt < 1) return $inputString;
	return $inputString . str_repeat($padding, $padAmt);
}

function getServerListFromDatabase()
{
	return pg_fetch_all(pg_query(
	"WITH svrec AS (
	SELECT server.id, server.location, server.port, MAX(analytics.frame.record_id) AS record_id 
	FROM tracker.server 
	INNER JOIN analytics.frame ON server_id = server.id
	WHERE active = true 
	GROUP BY server.id 
	)
	SELECT svrec.id, location, port, gamename.name 
	FROM svrec 
	INNER JOIN analytics.record ON record.id = record_id 
	INNER JOIN analytics.gamename ON gamename.id = record.gamename_id
	ORDER BY id"));
}

function countDatabaseReturn($input)
{
	if($input === false || $input == 0)
	{
		return 0;
	}
	else
	{
		return count($input);
	}
}

function checkPlural($input, $count)
{
	if($count != 1) return $input . 's';
	return $input;
}

function readLogFile($filename)
{
	//This function reads in a log file, trims the header and footer, and returns it as an array
	if(!file_exists(logPath . $filename)) return array();

	setFilePermissions(logPath . $filename);

	return explode("\n", substr(readFileIn(logPath . $filename), strlen(logHeader($filename)), strlen(logFooter()) * -1));
}

function writeToLogFile($filename, $input, $logSizeLimit)
{
	$splitPoint = strrpos($filename, "/");
	if($splitPoint != 0)
	{
		$splitPoint++;
	}
	$logFileName = substr($filename, $splitPoint);
	$input = removeLogExploits($input);

	//This function accepts a string or array as input, merges it with the old log file, trims it to the correct length, and writes the result to the log file
	$oldLogFile = readLogFile($filename);
	if(!is_array($input))
	{
		$input = explode("\n", $input);
	}
	$input = array_merge($input, $oldLogFile);
	$input = array_slice($input, 0, $logSizeLimit);
	$input = logHeader($filename) . implode("\n", $input) . logFooter();
	file_put_contents(logPath . $filename, $input);
}

function renderLogFile($input)
{
	$output = "";
	$logContents = readLogFile($input);
	$count = count($logContents);
	for($i = 0; $i < $count; $i++)
	{
		$output .= '<tr><td class="logViewerNumber">' . ($i + 1) . '</td><td class="logViewerText logViewerRow' . ($i % 2) + 1 . '"><pre class="logViewerPre">' . stringValidator($logContents[$i], "", "", "") . '</pre></td></tr>';
	}

	$lines = '<p class="logSize noTopMargin noBottomMargin"><b>' . $i .' lines</b></p>';

	return $lines . logGoBackDirectoryLink() . '<table class="logTable">' . $output . '</table>';
}

function logGoBackDirectoryLink()
{
	if(defined('this_file'))
	{
		return '<p><a href="' . this_file . '?path=' . rtrim(filepath, '/') . '/.." class="logLink">Go Back</a></p>';
	}
	return '';
}

function logViewerLink($filepath, $filename)
{
	return '<a href="' . this_file . '?path=' . rtrim($filepath, '/') . '" class="logLink">' . $filename . '</a><br>';
}

function removeLogExploits($input)
{
	$input = str_replace("\n", "\\n", $input);
	$input = str_replace('<?', ' EXPLOIT REMOVED (LessThan, QuestionMark) ', $input);
	$input = str_replace('?>', ' EXPLOIT REMOVED (QuestionMark, GreaterThan) ', $input); $input = str_replace("*/", ' EXPLOIT REMOVED (Asterisk, ForwardSlash) ', $input);
	return $input;
}

function logHeader($filename)
{
	return "<?php \necho '<h3 class=\"errorMessage\">Log access is restricted to administrators only.<br />You must log in as an administrator to view " . $filename . "!</h3>';\n exit(); \n/*  LOG ENTRIES:\n\n";
}

function logFooter()
{
	return "\n\n*/ ?> ";
}

function displayRootLogDirectory()
{
	$output = '';
	//List the files in the root folder.
	$logFileList = scandir(logPath);
	$count = count($logFileList);
	$count2 = count(permittedLogFiles);

	$output .= '<table class="logPathTable">';
	for($i = 2; $i < $count; $i++)
	{
		for($j = 0; $j < $count2; $j++)
		{
			if(strcmp($logFileList[$i], permittedLogFiles[$j]) == 0 && filesize(logPath . $logFileList[$i]) > 0)
			{
				$logFileList[$i] = stringValidator($logFileList[$i], "", "", "");

				$output .= '<tr class="logViewerRow' . ($j % 2) + 1 . '"><td class="logViewerPathTable">' . logViewerLink($logFileList[$i], $logFileList[$i] . '</td><td>' . getHumanReadableFilesize(logPath . $logFileList[$i])) . '</td></tr>';
			}
		}
	}
	$output .= '<tr class="logViewerRow' . (($j + 1) % 2) + 1 . '"><td class="logViewerPathTable">' . logViewerLink("SecurityLogs", "- Server Security Logs") . '</td><td></td></tr></table>';
	return $output;
}

function settingsFileHeader()
{
	return "<?php\necho \"There's nothing here for you. Were you expecting something??\";\nexit();/* \n";
}

function settingsFileFooter()
{
	return "\n*/ \n?> ";
}

function stripSettingsFileHeaderAndFooter($input)
{
	return substr($input, strlen(settingsFileHeader()), strlen(settingsFileFooter()) * -1);
}

function sendServerRemovalEmail($dynamicIPAddressPath)
{
	$brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
	
	$subject = 'Server no longer being tracked!';
	$message = '<div style="max-width: 500px; margin: 0 auto;">
				<p>This message was sent to inform you that ' . trackerName() . ' has not seen your game server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail($brokenAddress[0]) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . $brokenAddress[1] . '</span> for <span style="color: #FF0; font-weight: bold;">' . strval(deleteInterval) . '</span> days.</p>
				<br>
				<p>Due to this extended downtime, ' . trackerName() . ' is assuming your game server is permanently offline, and has stopped tracking it. All cached data and server tools settings for this game server have been deleted.</p>
				<br>
				<p>Thanks for using ' . trackerName() . ', we hope you found it useful.</p>
				<br>
				<p>Happy gaming!</p>
				</div>';

	return sendEmailAlertsWithUnsubscribeTokens($brokenAddress[0], $brokenAddress[1], readSettingsFile($dynamicIPAddressPath), $subject, $message, true);
}

function getServerEmailList($serverSettings)
{
	$output = array();

	if(isset($serverSettings['emailAlerts']['addresses']))
	{
		for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
		{
			if(isset($serverSettings['emailAlerts']['addresses'][$i]['verified']) && $serverSettings['emailAlerts']['addresses'][$i]['verified'])
			{
				array_push($output, $serverSettings['emailAlerts']['addresses'][$i]['address']);
			}
		}
	}

	return $output;
}

function readSettingsFile($dynamicIPAddressPath)
{
	if(!file_exists(infoPath . $dynamicIPAddressPath . '/' . serverSettingsFilename)) return array();

	setFilePermissions(infoPath . $dynamicIPAddressPath . '/' . serverSettingsFilename);

	$output = stripSettingsFileHeaderAndFooter(readFileIn(infoPath . $dynamicIPAddressPath . '/' . serverSettingsFilename));
	$output = json_decode($output, true);
	if(is_null($output))
	{
		return array();
	}

	return $output;
}

function writeSettingsFile($dynamicIPAddressPath, $settings)
{
	$output = settingsFileHeader() . json_encode($settings) . settingsFileFooter();
	writeFileOut(infoPath . $dynamicIPAddressPath . serverSettingsFilename, $output);
}

function changeServerSettingsFile($dynamicIPAddressPath, $newSettings)
{
	// This will change or add to the settings in a server's existing setting file
	$oldSettings = readSettingsFile($dynamicIPAddressPath);
	$newSettings = changeServerSettings($oldSettings, $newSettings);
	writeSettingsFile($dynamicIPAddressPath, $newSettings);
}

function changeServerSettings($oldSettings, $newSettings)
{
	return array_merge($oldSettings, $newSettings);
}

function getServerSettingValueFromFile($dynamicIPAddressPath, $settingToFind)
{
	return getServerSettingValue(readSettingsFile($dynamicIPAddressPath), $settingToFind);
}

function getServerSettingValue($settingsList, $settingToFind)
{
	if(array_key_exists($settingToFind, $settingsList))
	{
		return $settingsList[$settingToFind];
	}
	return null;
}

function removeServerSettingFromFile($dynamicIPAddressPath, $settingToRemove)
{
	// This will remove a setting in a server's existing setting file
	$settings = readSettingsFile($dynamicIPAddressPath);
	$settings = removeServerSetting($settings, $settingToRemove);
	writeSettingsFile($dynamicIPAddressPath, $settings);
}

function removeServerSetting($settingsList, $settingToRemove)
{
	unset($settingsList[$settingToRemove]);
	return $settingsList;
}

function generateToken($minSize = 30, $maxSize = 40)
{
	$output = "";

	$length = random_int($minSize, $maxSize);

	$mask = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$masklength = strlen($mask) - 1;

	for($i = 0; $i < $length; $i++)
	{
		$output .= $mask[random_int(0, $masklength)];
	}

	return trackerName() . "-" . $output;
}

function writeSecurityLogEntry($dynamicIPAddressPath, $message)
{
	$temp = breakDynamicAddressPath($dynamicIPAddressPath);
//	$temp[0] = gethostbyname("$temp[0]");
	$dynamicIPAddressPath = makeDynamicAddressPath("$temp[0]", $temp[1]);

	$SecurityLog = date(DATE_RFC2822) . "  Client IP Address: " . clientAddress . "  -->  " . $message;
	writeToLogFile($dynamicIPAddressPath . serverSecurityLogFilename, $SecurityLog, SecurityLogSize);
}


function writeNewConfigFile() {
	$configBuffer = '<?php

///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the configuration file for ParaTracker.
// If you want to edit fonts and colors, you should edit them
// in the css files found in the /skins folder.
// The visual settings found here are overrides only, and should be used with caution!

// ONLY modify the variables defined below, between the double quotes!
// Changing anything else can break the tracker!

// If this file ever breaks and you have no idea what is wrong, just delete it.
// The next time ParaTracker is run, it will create a new one for you.

// If you find any exploits in the code, please bring them to our attention immediately!
// Thank you and enjoy!


/*==================================================================================================*/
// TRACKER TAGLINE
// TRACKER TAGLINE

//This value appears in the title bar of the browser. It will show up in Google search results - choose wisely!
//Default value is "The Ultimate Quake III Server Tracker"
$trackerTagline = "The Ultimate Quake III Server Tracker";


/*==================================================================================================*/
// NETWORK SETTINGS
// NETWORK SETTINGS

// This is the IP Address of the server. Do not include the port number!
// By default, and for security, this value is empty. If ParaTracker is launched without a value here,
// it will display a message telling the user to check ParaConfig.php before running.
$serverIPAddress = "";

// Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
// The default port number for Jedi Outcast is 28070.
// If an invalid entry is given, this value will default to 29070.
//$serverPort = "";
$serverPort = "";

// This variable limits how many seconds are required between each snapshot of the server.
// This prevents high traffic on the tracker from bogging down the game server it is tracking.
// ParaTracker forces a minimum value of 2 seconds between snapshots. Maximum is 60 seconds.
// This value cannot be lower than the value of $connectionTimeout (below).
// Default is 5 seconds.
$floodProtectTimeout = "5";

// This value is the number of times ParaTracker will attempt to connect to a game server before giving up.
// Minimum value is 1, maximum value is 10.
// Default is 2.
$connectionAttempts = 2;

// This value is the number of seconds ParaTracker will wait for a response from the game server
// before timing out. ParaTracker forces a minimum value of 1 second, and will not allow values
// over 15 seconds.
// Not recommended to go above 5 seconds, as people will get impatient and leave.
// This setting also affects RCon wait times.
// Default is 2.5 seconds.
$connectionTimeout = "2.5";

// This value determines how many seconds ParaTracker will wait for a current refresh of
// the server info to complete, before giving up and forcing another one. Raise this value if your
// web server is busy or slow to reduce the load on the game server.
// Minimum is 1 second, maximum is 15 seconds.
// Default is 3 seconds.
$refreshTimeout = "3";


/*==================================================================================================*/
// VISUAL SETTINGS
// VISUAL SETTINGS

// This line specifies which skin file to load. Skins are found in the $skinsPath folder (Found near the bottom
// of this file), and they are all simple CSS files. The file name is case sensitive.
// ParaTracker will automatically search in the $skinsPath folder for the file specified, and it will automatically
// add the ".css" file extension. All you need to include here is the file name, minus the extension.
// You can make your own custom CSS skins. If you want to use JSON to make a fully custom skin, then
// set this value to "JSON" and the tracker will send an unformatted JSON response.
// Default value is "Metallic Console"
$paraTrackerSkin = "Metallic Console";

// The following visual settings are OVERRIDES ONLY. Use with caution!
// The following visual settings are OVERRIDES ONLY. Use with caution!

// This is a 6 character hexadecimal value that specifies the background color to be used.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$backgroundColor = "";

// This value is a percentage, from 0 to 100, of how opaque the background color will be.
// Default value is "100".
$backgroundOpacity = "100";

// This is a 6 character hexadecimal value that specifies the color of the odd rows on the player list.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$playerListColor1 = "";

// This value is a percentage, from 0 to 100, of how opaque the color of the odd rows on the player list will be.
// Default value is "100".
$playerListColor1Opacity = "100";

// This is a 6 character hexadecimal value that specifies the color of the even rows on the player list.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$playerListColor2 = "";

// This value is a percentage, from 0 to 100, of how opaque the color of the even rows on the player list will be.
// Default value is "100".
$playerListColor2Opacity = "100";

// This is a 6 character hexadecimal value that specifies the text color of all non-colorized text.
// It will not change the color of server names, mod names, map names, or player names.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$textColor = "";

// This value specifies a font to be used on the tracker page. Font families are also accepted.
// Make sure you use a common font so everyone can see it!
// Default value is "".
$customFont = "";


/*==================================================================================================*/
// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for in the images/levelshots folder.
// For instance, if the game is Jedi Academy and the map is mp/ffa5, ParaTracker will search for
// images named "ffa5" in the "images/levelshots/jedi academy/mp/" folder.

// For levelshots to animate, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three animated levelshots for mp/ffa5, the files would have to be
// named ffa5_1.jpg, ffa5_2.jpg, and ffa5_3.jpg

// The following array is a list of levelshot file extensions supported by the tracker.
// You can add as many extensions as you like. Add each file extension on a new line, between the double
// quotes, and make sure there is a comma at the end of each line, with the exception of the last line.
// Do not inclue the "." - just the letters of the extension. "jpg", "png", etc. When the levelshot finder
// searches for images, it will search in the order of this array and stop when it finds a match.
// Prioritize the extensions carefully!
// Example:
// $fileExtList = array(
// "webp",
// "png",
// "jpg",
// "gif"
// );
$fileExtList = array(
"webp",
"png",
"jpg",
"gif"
);

// The following value will enable or disable levelshot transitions. A value of 1 or "Yes" will allow them,
// and any other value will disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$levelshotTransitionsEnabled = "1";

// This is the number of seconds each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the number of seconds, each levelshot will take to transition into the next one.
// Note that transitions do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is 1 second.
$levelshotTransitionTime = "1";

// This is the animation that will be used for fading levelshots.
// If you want to change the animations, they are found in "css/LevelshotAnimations.css"
// Each transition is listed here. The user can pick which transitions are enabled
// by adding the values of the desired transitions together, and putting them
// into the variable below.
// Dynamic mode will allow users to override this setting.
// Minimum is 0 (No transitions). maximum is 32767.
// Default value is 3.
// The default transitions are as follows:
// Transition 1: Fade                   ( Value: 1 )
// Transition 2: Smooth Fade            ( Value: 2 )
// Transition 3: Hue Shift              ( Value: 4 )
// Transition 4: Skew                   ( Value: 8 )
// Transition 5: Horizontal Stretch     ( Value: 16 )
// Transition 6: Stretch and rebound    ( Value: 32 )
// Transition 7: Slide Left             ( Value: 64 )
// Transition 8: Slide Right            ( Value: 128 )
// Transition 9: Slide Top              ( Value: 256 )
// Transition 10: Slide Bottom          ( Value: 512 )
// Transition 11: Spin, Fly Left        ( Value: 1024 )
// Transition 12: Spin, Fly Right       ( Value: 2048 )
// Transition 13: Fall Away             ( Value: 4096 )
// Transition 14: Zoom                  ( Value: 8192 )
// Transition 15: Blur                  ( Value: 16384 )
$levelshotTransitionAnimation = "3";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better.
// Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";

// This value is boolean. When this variable is set to Yes or 1, it allows the user to specify
// custom levelshots in the tracker URL. If there are no levelshots found for a map, a custom
// levelshot may be used in place of the default levelshot for that particular game.
// This feature only works in dynamic mode.
// Default is 1.
$enableCustomDefaultLevelshots = "1";


/*==================================================================================================*/
// TRACKER SETTINGS
// TRACKER SETTINGS

// This value is boolean. When this variable is set to Yes or 1, the game name will be displayed
// in the tracker. Otherwise, it will be hidden.
// Default is 1.
$displayGameName = "1";

// This value is boolean. When this variable is set to Yes or 1, offending symbols will be
// filtered from the server name. Frequently, people will put unreadable symbols into their
// server names to get a higher alphabetical listing. This feature will remove the nonsense symbols.
// Default is 1.
$filterOffendingServerNameSymbols = "1";

// This message displays in place of the player list when nobody is online.
// Default is "No players online."
$noPlayersOnlineMessage = "No players online.";

// This value is boolean. When it is enabled, ParaTracker will automatically reload info from the
// game server, keeping the tracker up to date.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Enabled by default.
$enableAutoRefresh = "1";

// This value determines how many seconds ParaTracker waits between refreshes.
// Decimals are invalid and will be rounded.
// Minimum is 10 seconds, maximum is 300 seconds.
// Cannot be lower than the value of $floodProtectTimeout.
// Default is 15 seconds.
$autoRefreshTimer = "15";

// This variable will set the maximum number of characters ParaTracker will accept from the server.
// This prevents pranksters from sending 50MB back, in the event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// If this limit is met, ParaTracker will terminate with an error.
// Default is 16384 characters (One packet).
$maximumServerInfoSize = "16384";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and specify
// an IP address, port number and skin in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=Metallic%20Console"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that has not been found!
// If you do not understand what this feature is, then DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "0";

// This next setting enables server tools, which enables things like DDOS filtering for server owners,
// as well as viweing server security logs (RCon). This feature requires "Dynamic" ParaTracker (Above)
// to be enabled, otherwise it will be forcefully disabled.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Enabled by default.
$serverToolsEnabled = "1";

// The following setting is a personal message that will be displayed on ParaTrackerDynamic.php when a user is setting
// up ParaTracker for their own use. By default, this is simply a link to our GitHub, where you can download the program
// for free. The point is to encourage as many people as possible to run the software themselves, and not to rely on Dynamic
// mode too much.
// Default is: "ParaTracker is a free, open-source server tracker for Quake 3 based games! Download your own at http://github.com/ParabolicMinds/ParaTracker"
$personalDynamicTrackerMessage = "ParaTracker is a free, open-source server tracker for Quake 3 based games! Download your own at http://github.com/ParabolicMinds/ParaTracker";


/*==================================================================================================*/
// RCON SETTINGS
// RCON SETTINGS

// This value will enable or disable RCon.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default for security.
$RConEnable = "0";

// This value sets the maximum number of characters ParaTracker will send to the server.
// If the command or password is any larger than this, the command will not be sent.
// Minimum is 20 characters, maximum is 10000 characters.
// Default is 200 characters.
$RConMaximumMessageSize = "200";

// RCon flood protection forces the user to wait a certain number of seconds before sending another command.
// Note that this is not user-specific; if someone else is using RCon on your server, you will also have to wait
// to send the command.
// Minimum is 5 seconds, maximum is 20.
// Cannot be lower than the value of $connectionTimeout.
// Default is 10 seconds.
$RConFloodProtect = "10";


/*==================================================================================================*/
// GEOIP SETTINGS
// GEOIP SETTINGS

// This value is boolean. When this variable is set to Yes or 1, GeoIP will be enabled, which
// allows a country flag icon to be displayed on the tracker.
// GEOIP MUST BE INSTALLED ON THE WEB SERVER FOR THIS TO WORK.
// If ParaTracker does not find GeoIP, it will ignore this setting, and give an error message in an
// HTML comment at the top of the page.
// Default is 0.
$enableGeoIP = "0";

// For GeoIP to work, ParaTracker needs to know where to find the country database. This path
// needs to point to the GeoIP database file. Include the file name and extension.
// default value is ""
$geoIPPath = "";


/*==================================================================================================*/
// POSTGRESQL SETTINGS
// POSTGRESQL SETTINGS

// This value is boolean. When set to 1, ParaTracker will attempt to find a postgres SQL database. ParaTracker
// will connect to this database and use it for things like analytics and levelshot requests for maps.
// Do not enable this if you do not have postgres installed.
// Default is 0.
$enablePGSQL = "0";

// This is the user name used for the postgres database.
// Default is "postgres"
$pgUser = "postgres";

// This is the password used for the postgres database.
// Default is ""
$pgPass = "";

// This is the name of the postgres database.
// Default is "paratracker"
$pgName = "paratracker";

// This is the URL of the postgres database.
// Default is "localhost"
$pgHost = "localhost";

// This is the port used to connect to the postgres database.
// Default is "" (uses the default port of pg_connect)
$pgPort = "";


/*==================================================================================================*/
// ANALYTICS SETTINGS
// ANALYTICS SETTINGS

// To run analytics, you must set up a cron job to run AnalyticsBackground.php (found in the $utilitiesPath folder)
// at specific intervals. This process will connect to every server that has recently been tracked and
// store info in the database.

// This value is boolean. When this variable is set to Yes or 1, the analytics back end will be enabled.
// Analytics will save server data to a database over time, and allow users to view this data.
// Analytics REQUIRES pgsql to be enabled. If pgsql is disabled, analytics will also be disabled.
// Default value is 0.
$analyticsEnabled = "0";

// This value is boolean. When this variable is set to Yes or 1, the analytics front end will be enabled.
// This will allow users to view the saved analytics data from the database.
// This feature is here to help prevent people from overloading the server with false analytics requests,
// while at the same time allowing the back end to run.
// If the analytics back end is disabled, the front end will be disabled as well.
// Default value is 1.
$analyticsFrontEndEnabled = "1";


/*==================================================================================================*/
// MAPREQ SETTINGS
// MAPREQ SETTINGS

// This value is boolean. When set to "Yes" or "1", mapreq will be used to allow users to request
// levelshots of maps they like.
// mapreq REQUIRES pgsql and dynamic mode to be enabled. If pgsql or dynamic mode is disabled, mapreq will disable itself.
// Default is 0
$mapreqEnabled = "0";

// If mapreq is enabled and the server being tracked does not have levelshots, ParaTracker will display this message to
//encourage users to submit their own levelshots.
//Default is "Click here to add levelshots"
$mapreqTextMessage = "Click here to add levelshots";


/*==================================================================================================*/
// EMAIL SETTINGS
// EMAIL SETTINGS

// These settings are for email functions.
// Emails are sent by SendEmails.php, which is found in the $utilitiesPath folder.
// SendEmails.php must be activated by a cron job, that runs at the frequency you want to receive administrative reports.

// This value is the domain of the server ParaTracker is running on, for instance \'paratracker.dogi.us\'
// It is used in emails to link back to the web page (Some tools are run from command line so there is no domain)
$webServerDomain = "pt.dogi.us";

// This setting enables or disables e-mail functionality. For this to work, an administrator e-mail address must exist,
// and PHPMailer must be installed via composer.
// Default is 0
$emailEnabled = "0";

// This setting enables or disables the sending of admin reports.
// Default is 0
$emailAdminReports = "0";

// This setting is the path to PHPMailerAutoload.php.
// Default is "vendor/phpmailer/phpmailer/"
$emailPath = "vendor/phpmailer/phpmailer/";

// This variable controls whether emails will be sent with SMTP or not.
// Default is 0
$useSMTP = "0";

	// If using SMTP, this variable is the address of the SMTP server we will use
	// Default is ""
	$smtpAddress = "";

	// If using SMTP, this variable is the port of the SMTP server we will use
	// Default is ""
	$smtpPort = "";

	// If using SMTP, this will control if the connection is secure. Valid values are true and false
	// Default is true
	$SMTPSecure = true;

	// If using SMTP, this variable is the username we will use for the SMTP server
	// Default is ""
	$smtpUsername = "";

	// If using SMTP, this variable is the password we will use for the SMTP server
	// Default is ""
	$smtpPassword = "";

// This is the address that will be used to send the email.
// Default is ""
$emailFromAddress = "";

// Server tools has a feature to allow users to email the administrators once they have logged in.
// Default is 0.
$serverToolsAllowEmailAdministrators = 0;

// This is an array of administrator e-mail addresses. You can add as many as you like. Add each e-mail address
// on a new line, between the double quotes, and make sure there is a comma at the end of each line, with the
// exception of the last line.
// Example:
// $emailAdministrators = array(
// "adminNumberOne@nowhere.com",
// "adminNumberTwo@nowhere.com",
// "adminNumberThree@nowhere.com"
// );
$emailAdministrators = array(
"",
"",
"",
"",
""
);


/*==================================================================================================*/
// MAINTENANCE SETTINGS
// MAINTENANCE SETTINGS

// Every so often, ParaTracker will clean up the $infoPath folder and the levelshots request database
// by checking for outdated data and deleting it.
// This variable controls how many minutes ParaTracker will wait between cleanups.
// Note that if analytics is enabled, cleanup will only be run by AnalyticsBackground.php.
// Minimum is 10 minutes, maximum is 1440 minutes.
// Default is 60 minutes.
$cleanupInterval = "60";

// This variable is how many days old the server info must be to be deleted.
// If analytics are enabled, this value will instead determine how many days
// a server must be offline before it is no longer tracked.
// This variable cannot be lower than 2 times the amount of time specified in $cleanupInterval above.
// Minimum is 1 day, maximum is 30 days.
// Decimals are accepted.
// Default is 7 days.
$deleteInterval = "7";

// This variable sets what percentage of server load will cause the cleanup to be skipped.
// If ParaTracker is running on Windows, PHP cannot measure CPU load, so this setting will be ignored
// and cleanup will run regardless of the load.
// Minimum is 50, maximum is 100
// Default is 90
$loadLimit = "90";

// This variable is boolean. When this variable is set to Yes or 1, ParaTracker will remove old entries
// from the analytics database as part of the cleanup process.
// Default is 1.
$cullDatabase = "1";

// This variable determines how many days entries will be left in the analytics database before removal.
// This will have no effect if $cullDatabase (Above) is disabled.
// Minimum is 90 days, maximum is 2000 days.
// Default is 370 days.
$databaseCullTime = 370;


/*==================================================================================================*/
// PATH SETTINGS
// PATH SETTINGS

// This setting controls the temporary folder where server info will be stored between refreshes.
// Changing this while ParaTracker is live will result in all game servers being reloaded. Change with caution.
// Default is "info"
$infoPath = "info";

// This setting controls the folder where logs will be stored.
// Changing this while ParaTracker is live will result in all logs being reset. Change with caution.
// Default is "logs"
$logPath = "logs";

// This setting controls the path to the skins folder.
// Default is "skins"
$skinsPath = "skins";

// This setting controls the path to the utilities folder. Do not change this unless you are rearranging
// the internals of ParaTracker to match.
// Default is "utilities"
$utilitiesPath = "utilities";


/*==================================================================================================*/
// LOG SETTINGS
// LOG SETTINGS

// Errors are logged in errorLog.php, which is found in the $logPath folder.
// This setting will determine the maximum number of lines that will be stored in the log file
// before the old entries are truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 10000 lines.
$errorLogSize = "10000";

// Cleanup events are logged in cleanupLog.php, which is found in the $logPath folder.
// This is the maximum of lines the cleanup log can have before the excess is truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 10000 lines.
$cleanupLogSize = "10000";

// RCon events are logged in SecurityLog.php. Each server has it\'s own unique RCon log, which is found in the
// $logPath folder, inside a folder named for the server\'s IP address and port.
// This setting will determine the maximum number of lines that will be stored in the log file
// before the old entries are truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 1000 lines.
$SecurityLogSize = "1000";


/*==================================================================================================*/
// End of config file

/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

?>';

	if(!writeFileOut('ParaConfig.php', $configBuffer))
	{
		echo "<!-- --><h3>ParaConfig.php not found! Attempted to write a default config file, but failed!<br />Make sure ParaTracker has file system access, and that the disk is not full!</h3>";
		exit();
	}
}

?>
