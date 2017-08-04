<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


function versionNumber()
{
    //Return a string of the version number
    //If you modify this project, PLEASE change this value to something of your own, as a courtesy to your users
    return("ParaTracker 1.4");
}

//Define the default skin, to be used throughout this file.
//This variable must reference an actual .css file in the skins folder, or it will break stuff. Do not include the file path or extension!
//"JSON" is also a valid value.
//Default value is "Metallic Console"
$defaultSkin = "Metallic Console";


if (!isset($safeToExecuteParaFunc))
{
    displayError("ParaFunc.php is a library file and can not be run directly!<br />Try running ParaTrackerStatic.php or ParaTrackerDynamic.php instead.", $lastRefreshTime, "");
    exit();
}

//Now that validation is complete, declare this value
if(!isset($analyticsBackground))
{
    $analyticsBackground = 0;
}
define("analyticsBackground", $analyticsBackground);

//This block is here to suppress error messages
$dynamicIPAddressPath = "";
$serverIPAddress = "";
$serverPort = "";
$personalDynamicTrackerMessage = "";
$lastRefreshTime = "";
$floodProtectTimeout = "";
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
$emailEnabled = "";
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

//For safety, these MUST be forced to an initial value!
$logPath = "logs";
$infoPath = "info";
$skinsPath = "skins";
$utilitiesPath = "utilities";

//The default skin must be defined here, before skin validation takes place
define("defaultSkin", $defaultSkin);

//If this file is executed directly, then echoing this value here will display the version number before exiting.
//Either way, the version number will be visible.
echo "<!-- " . versionNumber() . " ";

if (file_exists("ParaConfig.php"))
{
    include 'ParaConfig.php';
}
else
{
    writeNewConfigFile();
    if (file_exists("ParaConfig.php"))
    {
        echo "<!-- --><h3>ParaConfig.php not found! A default config file has been written to disk.<br />Please add an IP Address and port to it.</h3>";
        exit();
    }
    else
    {
        echo "<!-- --><h3>ParaConfig.php not found! Attempted to write a default config file, but failed!<br />Make sure ParaTracker has file system access, and that the disk is not full!</h3>";
        exit();
    }
}

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

define("logPath", $logPath);
define("infoPath", $infoPath);
define("skinsPath", $skinsPath);
define("utilitiesPath", $utilitiesPath);

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
    if($dynamicTrackerEnabled == "1")
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
        displayError("ParaTracker Dynamic mode is disabled! Dynamic mode must be enabled in ParaConfig.php.", "", "");
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
    if($dynamicTrackerEnabled == "1" && $calledFromRCon == "1")
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
$pgName = basicValidator($pgName, "paratracker");
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

if (enablePGSQL)
{
    global $pgCon;
    $connectString = 'host=' . pgHost . ' dbname=' . pgName . ' user=' . pgUser;
    if (!empty(pgPass)) $connectString .= " password=" . pgPass;
    if (!empty(pgPort)) $connectString .= " port=" . pgPort;
    $pgCon = pg_connect($connectString);
    if (!$pgCon)
    {
        displayError("Could not establish database connection", $lastRefreshTime, "");
        exit();
    }

    pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS account')
        or displayError('could not create account schema', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS account.user (id BIGSERIAL PRIMARY KEY, username VARCHAR(64) UNIQUE NOT NULL, email TEXT UNIQUE, passhash VARCHAR(128) NOT NULL, salt VARCHAR(16) NOT NULL)')
        or displayError('could not create account.user table', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS account.session (user_id BIGSERIAL PRIMARY KEY REFERENCES account.user (id) ON UPDATE CASCADE ON DELETE CASCADE, token VARCHAR(64) NOT NULL, expires TIMESTAMP NOT NULL)')
        or displayError('could not create account.session table', $lastRefreshTime, "");
        
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS mapreq (
			id BIGSERIAL PRIMARY KEY,
			game_name VARCHAR(128) NOT NULL CHECK (game_name <> \'\'),
			bsp_name VARCHAR(128) NOT NULL CHECK (bsp_name <> \'\'),
			dl_link TEXT CHECK (dl_link <> \'\'),
			entry_date TIMESTAMP DEFAULT NOW(),
			useradded BOOL NOT NULL DEFAULT false,
			UNIQUE(game_name, bsp_name)
			)
		') or displayError('could not create map request (mapreq) table', $lastRefreshTime, "");
        
    pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS tracker')
        or displayError('could not create tracker schema', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.server (
			id BIGSERIAL PRIMARY KEY,
			location VARCHAR(128) NOT NULL,
			port INT NOT NULL CHECK (port > 0 AND port < 65536),
			active BOOL NOT NULL DEFAULT TRUE,
			UNIQUE( location, port )
			)
		') or displayError('could not create server (tracker.server) table', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.cpuload (
			entrydate TIMESTAMP PRIMARY KEY DEFAULT NOW(),
			load REAL NOT NULL
			)
		') or displayError('could not create server (tracker.cpuload) table', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS tracker.displayerror (
			entrydate TIMESTAMP PRIMARY KEY DEFAULT NOW()
			)
		') or displayError('could not create server (tracker.displayerror) table', $lastRefreshTime, "");
		
    pg_query($pgCon, 'CREATE SCHEMA IF NOT EXISTS analytics')
        or displayError('could not create analytics schema', $lastRefreshTime, "");
		
	/* gamename */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.gamename (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create gamename (analytics.gamename) table', $lastRefreshTime, "");
		
	/* hostname */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.hostname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create hostname (analytics.hostname) table', $lastRefreshTime, "");
		
	/* mapname */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.mapname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create mapname (analytics.mapname) table', $lastRefreshTime, "");
		
	/* modname */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.modname (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create modname (analytics.modname) table', $lastRefreshTime, "");
		
	/* gametype */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.gametype (
				id BIGSERIAL PRIMARY KEY,
				name VARCHAR(256) NOT NULL UNIQUE CHECK (name <> \'\')
			)
		') or displayError('could not create gametype (analytics.gametype) table', $lastRefreshTime, "");
		
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
		') or displayError('could not create gamename record (analytics.record) table', $lastRefreshTime, "");
		
	/* frame */
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.frame (
				id BIGSERIAL PRIMARY KEY,
				server_id BIGINT NOT NULL REFERENCES tracker.server (id) ON UPDATE CASCADE ON DELETE CASCADE,
				entrydate TIMESTAMP NOT NULL DEFAULT NOW(),
				record_id BIGINT REFERENCES analytics.record (id) ON UPDATE CASCADE ON DELETE CASCADE
			)
		') or displayError('could not create uptime (analytics.frame) table', $lastRefreshTime, "");
    pg_query($pgCon, 'CREATE TABLE IF NOT EXISTS analytics.runtimes (
				startdate TIMESTAMP PRIMARY KEY,
				enddate TIMESTAMP NOT NULL DEFAULT NOW()
			)
		') or displayError('could not create uptime (analytics.runtimes) table', $lastRefreshTime, "");
		
    $admin = adminCheck();
}

if($executeDynamicInstructionsPage == "0" && $calledFromAnalytics == "0" && $calledFromElsewhere == "0")
{
    //By default, static mode will already have given us an IP address before all of this took place.
    //So, now that we have the IP address and port from our source of choice, MAKE SURE to validate them before we go ANY further!
    //The port must be validated first, because it is used in IP address validation.
    $serverPort = numericValidator($serverPort, 1, 65535, 29070);
    $serverIPAddress = ipAddressValidator($serverIPAddress, $serverPort, $dynamicTrackerEnabled);

    $paraTrackerSkin = skinValidator($paraTrackerSkin, $customSkin);
}
else
{
    //This line prevents a skin file from being mistakenly applied to the dynamic instructions page or the analytics page.
    $paraTrackerSkin = "";
}


if($dynamicTrackerCalledFromCorrectFile == "1" || $calledFromParam == "1" || $calledFromRCon == "1")
{
    //We are running in Dynamic mode. Check to see if a skin file was specified in the URL.
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

//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$floodProtectTimeout = numericValidator($floodProtectTimeout, 5, 1200, 15);
$floodProtectTimeout = numericValidator($floodProtectTimeout, $connectionTimeout, 1200, $floodProtectTimeout);

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
$levelshotTransitionAnimation = numericValidator(round($levelshotTransitionAnimation), 0, 999, 0);
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

$RConFloodProtect = numericValidator($RConFloodProtect, 10, 3600, 20);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$RConFloodProtect = numericValidator($RConFloodProtect, $connectionTimeout, 3600, 20);
$RConLogSize = numericValidator($RConLogSize, 100, 100000, 1000);

$cleanupInterval = numericValidator($cleanupInterval, 10, 3600, 60);
$deleteInterval = numericValidator($deleteInterval, 1, 30, 7);

//Need to make sure deleteInterval is greater than cleanupInterval.
//cleanupInterval is given in minutes, and deleteInterval is given in days, so divide by 60.
//Multiply by 2 for the final value.
$deleteInterval = numericValidator($deleteInterval, $cleanupInterval / 60 * 2, 30, 7);

$loadLimit = numericValidator($loadLimit, 50, 100, 90);
$cleanupLogSize = numericValidator($cleanupLogSize, 100, 100000, 10000);

$mapreqEnabled = booleanValidator($mapreqEnabled, 0);
if(enablePGSQL == 0 || !file_exists(utilitiesPath . 'MapReq.php') || $dynamicTrackerEnabled == '0')
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
        echo ' Composer does not appear to be installed. ParaTracker expects Maxmind GeoIP2 to be installed via Composer. GeoIP has been disabled and will be ignored... ';
        $enableGeoIP = 0;
    }
    else
    {
        //Composer appears to be installed. Load GeoIP2!
        include_once 'vendor/autoload.php';

        if (!class_exists('GeoIp2\Database\Reader'))
        {
            echo ' Maxmind GeoIP2 library does not seem to be present. ParaTracker expects this library to be installed via Composer. GeoIP has been disabled and will be ignored... ';
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

$emailEnabled = booleanValidator($emailEnabled, 0);
define("emailEnabled", $emailEnabled);

define("floodProtectTimeout", $floodProtectTimeout);
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
define("dynamicTrackerEnabled", $dynamicTrackerEnabled);
define("RConEnable", $RConEnable);
define("RConMaximumMessageSize", $RConMaximumMessageSize);
define("RConFloodProtect", $RConFloodProtect);
define("RConLogSize", $RConLogSize);

define("enableGeoIP", $enableGeoIP);
define("geoIPPath", $geoIPPath);

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

define("customFont", $customFont);
define("customSkin", $customSkin);

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
    cleanupInfoFolder($cleanupInterval, $deleteInterval, $loadLimit, $cleanupLogSize);
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

//This needs to run every time the tracker is run. Otherwise the "No connection" pages will be missing the counter
autoRefreshScript();

function checkForMissingFiles($dynamicIPAddressPath)
{
    if(!checkFileExistence("connectionErrorMessage.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("errorMessage.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("levelshots.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("RConTime.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("serverDump.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("time.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("JSONServerInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("JSONParsedInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("JSONParams.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("JSONPlayerInfo.txt", infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkFileExistence("postgresData.txt", infoPath . $dynamicIPAddressPath)) return 0;

    return 1;
}

function checkFileExistence($filename, $folder)
{
    if (!file_exists($folder . $filename))
    {
        file_put_contents($folder . $filename, "");
        if (!file_exists($folder . $filename))
        {
            displayError("Failed to create file " . $folder . $filename . "!<br />Make sure ParaTracker has file system access, and that the disk is not full!", $lastRefreshTime, $dynamicIPAddressPath);
            return 0;
        }
    }
    return 1;
}

function checkDirectoryExistence($dirname)
{
    if (!file_exists($dirname))
    {
        mkdir($dirname);
    }
    if (!file_exists($dirname))
    {
        displayError("Failed to create directory " . $dirname . " in ParaTracker folder!<br />Cannot continue without file system access!", "", "");
        return 0;
    }
    return 1;
}

function checkLevelshotDirectories($levelshotFolder)
{
    $levelshotFolder = strtolower($levelshotFolder);

/*
    There is no point to this check now. If the folder does not exist it is
    because someone screwed up GameInfo.php.

    //We need to convert any matching directory name to lowercase
    if(!file_exists("images/levelshots/" . $levelshotFolder))
    {
        $exit = "0";
    }
*/
    $levelshotFolder = "images/levelshots/" . $levelshotFolder . "/";
    return $levelshotFolder;
}

function checkForAndDoUpdateIfNecessary($dynamicIPAddressPath)
{
    //Let's make sure we have a legitimate address first...
    if(empty($dynamicIPAddressPath) || $dynamicIPAddressPath == "-") return 0;

    //Let's make sure all the files we need are in place for this server
    //Between each check we should quit if it failed
    if(!checkDirectoryExistence(infoPath . $dynamicIPAddressPath)) return 0;
    if(!checkDirectoryExistence(logPath . $dynamicIPAddressPath)) return 0;

    if(!checkForMissingFiles($dynamicIPAddressPath)) return 0;

    //Check to see if a refresh is already in progress, and if it is, wait a reasonable amount of time for it to finish
    checkTimeDelay($dynamicIPAddressPath);

    $lastRefreshTime = numericValidator(file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt"), "", "", "0");

        if ($lastRefreshTime + floodProtectTimeout < time())
        {
            //Prevent users from aborting the page! This will reduce load on both the game server and the web server
            //by forcing the refresh to finish.
            ignore_user_abort(true);

            //Check to see if we were forced here. If so, change the refresh time value so that other users will wait for our refresh. This will prevent an accidental DOS of the server during high traffic.
            if(substr(trim(file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt")), 0, 4) == "wait")
            {
                file_put_contents(infoPath . $dynamicIPAddressPath . "time.txt", "wait" . rand(0, getrandmax()));
            }


            file_put_contents(infoPath . $dynamicIPAddressPath . "time.txt", "wait");

            //Remove any lingering error messages. We will write a new one later if we encounter another error.
            file_put_contents(infoPath . $dynamicIPAddressPath . "errorMessage.txt", "");

            $return = doUpdate($lastRefreshTime, $dynamicIPAddressPath);

            file_put_contents(infoPath . $dynamicIPAddressPath . "time.txt", time());

            //Allow users to abort the page again.
            ignore_user_abort(false);

            return $return;
        }
        else
        {
            return 1;
        }

}

function doUpdate($lastRefreshTime, $dynamicIPAddressPath)
{
    //Before we start, wipe out the parameter lists, the levelshots list, and the GeoIP flag data.
    //If we encounter an error later, the data will not remain
    file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONServerInfo.txt', '');
    file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONParsedInfo.txt', '');
    file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONParams.txt', '');
    file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONPlayerInfo.txt', '');
    file_put_contents(infoPath . $dynamicIPAddressPath . 'levelshots.txt', '');
    file_put_contents(infoPath . $dynamicIPAddressPath . 'postgresData.txt', '');

    //And let's declare a variable for the game name
    $gameName = "";

    //On with the good stuff! Connect to the server.
    $brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
    $serverIPAddress = $brokenAddress[0];
    $serverPort = $brokenAddress[1];

    //Set this value to measure the server's ping
    $serverPing = microtime(true);

    //Connect to the server, but remove all invalid characters to prevent issues later
    $s = connectToServerAndGetResponse($serverIPAddress, $serverPort, str_repeat(chr(255),4) . "getstatus\n", $lastRefreshTime);

    //Parse the server's ping.
    $serverPing = number_format(((microtime(true) - $serverPing) * 1000), 0);

    if(!$s)
    {
        //Set this value to measure the server's ping
        $serverPing = microtime(true);

        //If the connection failed, let's try one more time, just in case the message was lost somewhere
        $s = connectToServerAndGetResponse($serverIPAddress, $serverPort, str_repeat(chr(255),4) . "getstatus\n", $lastRefreshTime);

        //Parse the server's ping.
        $serverPing = number_format(((microtime(true) - $serverPing) * 1000), 0);
    }

    //This file is used for determining if the server connection was successful and regenerating dynamic content, plus it's good for debugging
    file_put_contents(infoPath . $dynamicIPAddressPath . "serverDump.txt", $s);

    if(!$s) return 0;

    //Server responded!

    //Mark the time in microseconds so we can see how long this takes.
	$parseTimer = microtime(true);

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
	$gameName = parseGameName($cvars_hash, $cvars_hash_decolorized, $lastRefreshTime, $dynamicIPAddressPath);
	//If the gameName could not be determined we must terminate.
	//Return 1, because if we return 0 the code above will try to connect to the server again
	if($gameName == "") return 1;

	//Insert game-specific function execution here
	$gameFunctionParserReturn = ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized, $lastRefreshTime, $dynamicIPAddressPath);

	//Remove the variables that were returned.
	//We must assume that they were returned in the correct order!
	$gametype = array_shift($gameFunctionParserReturn);
	$levelshotFolder = array_shift($gameFunctionParserReturn);
	$mapname = pathValidator(array_shift($gameFunctionParserReturn));
	$modName = array_shift($gameFunctionParserReturn);
	$sv_hostname = array_shift($gameFunctionParserReturn);
	$sv_maxclients = array_shift($gameFunctionParserReturn);

	//The rest is all BitFlag data.
	$BitFlags = $gameFunctionParserReturn;

	//The following function detects how many levelshots exist on the server, and passes a buffer of information back, the final count of levelshots, and whether they fade or not
	$levelshotCount = levelshotfinder($dynamicIPAddressPath, $mapname, $levelshotFolder, $gameName, 0);

	//This next block should only run if analytics is enabled
	if(analyticsEnabled)
	{
		//We need to write the Postgres data file, to be used for analytics.
		writePostgresDataFile($gameName, $dynamicIPAddressPath, $sv_hostname, $mapname, $modName, $gametype, $playerParseCount);

	    //DO NOT do any database work if we came from the analyticsBackground process!!
	    if(!analyticsBackground)
	    {
		    global $pgCon;
		    //Add the server to the server table if it's not already there
		    pg_query_params($pgCon, 'INSERT INTO tracker.server (location, port) VALUES ($1, $2) ON CONFLICT (location, port) DO UPDATE SET active = TRUE', array(strtolower($serverIPAddress), $serverPort));
	    }
	}

	//This has to be last, because the timer will output on this page
	parseToJSON($dynamicIPAddressPath, $gameName, $gametype, $mapname, $flag, $countryName, $cvar_array_single, $parseTimer, $serverPing, $BitFlags, $player_array, $playerParseCount, $sv_maxclients);

	return 1;
}

function writePostgresDataFile($gameName, $dynamicIPAddressPath, $sv_hostname, $mapname, $modName, $gametype, $playerParseCount)
{
    $gameName = stringClean($gameName);
    $sv_hostname = stringClean($sv_hostname);
    $mapname = stringClean($mapname);
    $modName = stringClean($modName);
    $gametype = stringClean($gametype);
    $playerParseCount = intval($playerParseCount);
    $output = $gameName . chr(0x00) . $sv_hostname . chr(0x00) . $mapname . chr(0x00) . $modName . chr(0x00) . $gametype . chr(0x00) . $playerParseCount;
    file_put_contents(infoPath . $dynamicIPAddressPath . 'postgresData.txt', $output);
}

function readPostgresDataFile($dynamicIPAddressPath)
{
    $fileData = strval(file_get_contents(infoPath . $dynamicIPAddressPath . 'postgresData.txt'));

    if($fileData != "")
    {
        $stuff = explode(chr(0x00), $fileData);

        //Online status (boolean),  $gameName, $sv_hostname, $mapname, $modName, $gametype, $playerParseCount
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
    //The following line trims leading white space
    $input = ltrim($input);

    //This is an array of garbage characters to be removed
    $filterArray = array("¬â‚", "€", "â", "¬", "");

    $count = count($filterArray);

    //Loop forward
    for($i = 0; $i < $count; $i++)
    {
        $input = ltrim($input, $filterArray[$i]);
    }

    //Loop backward
    for($i = $count - 1; $i < 0; $i--)
    {
        $input = ltrim($input, $filterArray[$i]);
    }

    //The following line trims remaining white space
    $input = ltrim($input);
    return $input;
}

function parseGameName($cvars_hash, $cvars_hash_decolorized, $lastRefreshTime, $dynamicIPAddressPath)
{
    //This function checks for variables specific to individual games, and sends them to the tracker.

    //Initialize this to null, so we can test against it later.
    $gameName = "";

    //Most games use the 'version' variable to identify which game is running. Try that first.
    if(isset($cvars_hash_decolorized["gamename"]) && $cvars_hash_decolorized["gamename"] != "")
    {
        $gameName = detectGameName(removeColorization($cvars_hash_decolorized["gamename"]));
    }
    if($gameName == "")
    {
        //Some games, like Jedi Academy and Jedi Outcast, use a 'version' variable to identify the game. Try that next.
        //This can only be checked for AFTER the 'gamename' variable, because some games use both variables.
        if(isset($cvars_hash_decolorized["version"]) && $cvars_hash_decolorized["version"] != "")
        {
            $gameName = detectGameName(removeColorization($cvars_hash_decolorized["version"]));
        }
        if($gameName == "")
        {
            //Tremulous uses 'com_gamename' to identify the game. Try that next.
            if(isset($cvars_hash_decolorized["com_gamename"]) && $cvars_hash_decolorized["com_gamename"] != "")
            {
                    $gameName = detectGameName(removeColorization($cvars_hash_decolorized["com_gamename"]));
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

        displayError("Unrecognized Game: " . $error . "<br />Please contact the ParaTracker team and request support!<br />" . $dynamicIPAddressPath, $lastRefreshTime, $dynamicIPAddressPath);
        return "";
    }

return $gameName;
}

function makeFunctionSafeName($input)
{
    $input = preg_replace("/[^a-z0-9]/", "", strtolower($input));
    return $input;
}

function ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized, $lastRefreshTime, $dynamicIPAddressPath)
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
                displayError("No game data found for '" . $gameName . "'!<br>Contact the ParaTracker team with the game name, as this is a bug that must be fixed.", $lastRefreshTime, $dynamicIPAddressPath);
            }
            else
            {
                echo " Could not load game data for " . $gameName . "! This error is not fatal, but ParaTracker cannot parse gametypes or GameInfo. ";
            }
        }
        return $GameInfoData;
}

function parseToJSON($dynamicIPAddressPath, $gameName, $gametype, $mapname, $flag, $countryName, $cvar_array_single, $parseTimer, $serverPing, $BitFlags, $player_array, $playerParseCount, $sv_maxclients)
{
        $returnArray = array();
        $BitFlagsIndex = array();

        //This array is used for parsing bit flag data
        $bitFlagArray = array();

        //This array is used for serverInfo
        $serverInfoArray = array();

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

                //Preload these things into the serverInfo array
                array_push($serverInfoArray, JSONString("maxPlayers", $sv_maxclients));
                array_push($serverInfoArray, JSONString("gamename", $gameName));
                array_push($serverInfoArray, JSONString("mapname", $mapname));
                array_push($serverInfoArray, JSONString("gametype", $gametype));
                array_push($serverInfoArray, JSONString("geoIPcountryCode", $flag));
                array_push($serverInfoArray, JSONString("geoIPcountryName", $countryName));

                //Let's get parsing.
                foreach($cvar_array_single as $cvar)
                {
                    //This array is used for parsing bit flag data
                    $bitFlagParseArray = array();

                    array_push($buf2, JSONString($cvar['name'], $cvar['value']));

                    if (($cvar['name'] == 'sv_hostname') || ($cvar['name'] == 'hostname'))
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
                                    array_push($bitFlagArray, array(JSONString("name", $cvar['name']), JSONArray("flags", "\"Miscount detected!\"", 0)));
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

                array_push($serverInfoArray, JSONString("serverPing", $serverPing));
                $parseTimer = number_format(((microtime(true) - $parseTimer) * 1000), 3);
                array_push($serverInfoArray, JSONString("parseTime", $parseTimer));

                file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONServerInfo.txt', JSONObject("serverInfo", $serverInfoArray, 0));
                file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONParsedInfo.txt', JSONArray("parsedInfo", $bitFlagArray, 4));
                file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONParams.txt', JSONObject("info", $buf2, 3));
                file_put_contents(infoPath . $dynamicIPAddressPath . 'JSONPlayerInfo.txt', JSONArray("players", $playerListbuffer, 4));
}

function levelshotFinder($dynamicIPAddressPath, $mapName, $levelshotFolder, $gameName, $stopAfterOneShot)
{
                //Let's make sure the levelshotfolder we were given is correct first.
		        $levelshotFolder = checkLevelshotDirectories($levelshotFolder);

                $levelshotCheckName = strtolower($mapName);
                $levelshotBuffer = '';

                $levelshotCount = 0;
                $levelshotIndex = 1;
            $foundLevelshot = 0;
                do
                {

                    //Reset this value every iteration so we can check to see if levelshots are being found
                    $foundLevelshot = 0;

                    //Check for a PNG first
                    if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.png'))
                    {
                        $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.png';
                        $foundLevelshot = 1;
                    }
                    else
                    {
                    //Failed to find a PNG, so let's check for a JPG
                        if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.jpg'))
                        {
                            $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.jpg';
                            $foundLevelshot = 1;
                        }
                        else
                        {
                            //Also failed to find a JPG, so let's check for a GIF
                            if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.gif'))
                            {
                                $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.gif';
                                $foundLevelshot = 1;
                            }
                            else
                            {
                            //Could not find any images. One last check - is this the first iteration of the loop?
                            //If so, we need to try and find a levelshot no matter what. Let's see if the user was
                            //silly and forgot to add an underscore and number to the file name, and if so, we'll
                            //just use that one. If not, we'll have to default to a placeholder for missing images.
                                if ($levelshotCount == 0)
                                {
                                //Checking for a PNG again:
                                        if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.png'))
                                        {
                                        $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '.png';
                                            }
                                            else
                                            {
                                                //And checking for a JPG again:
                                            if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.jpg'))
                                            {
                                                $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '.jpg';
                                            }
                                            else
                                            {
                                                //Lastly...checking for a GIF.
                                                if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.gif'))
                                                {
                                                $levelshotBuffer .= $levelshotFolder . $levelshotCheckName . '.gif';
                                                }
                                                else
                                                {
                                                    //Could not find a levelshot! Use the default 'missing picture' image and close out
                                                    $levelshotBuffer .= "images/missing.gif";

                                                    //Check to see if Postgres is active. If it is, let's automatically
                                                    //insert the map into the missing levelshots database.
                                                    if(enablePGSQL == "1")
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
                                }
                            }
                        }
                    }

                if ($foundLevelshot == 1)
                {
                    $levelshotBuffer .= "\n";
                    $levelshotCount++;
                    $levelshotIndex++;
                }
                else
                {
                    $levelshotBuffer = implode(":#:", explode("\n", trim($levelshotBuffer)));
                }

                } While ($stopAfterOneShot == 0 && $foundLevelshot == 1 && $levelshotCount < maximumLevelshots);

//This code prevents the Javascript that follows from seeing a value of 0 levelshots when none are found.
//There will always be a minimum of one levelshot. A placeholder is used if none is found.
if ($levelshotCount == 0 && $stopAfterOneShot == 0)
{
    $levelshotCount = 1;
}

if($dynamicIPAddressPath != "")
{
    file_put_contents(infoPath . $dynamicIPAddressPath . 'levelshots.txt', trim($levelshotBuffer));
}

return $levelshotCount;

}

function autoRefreshScript()
{
$output = "";
    if(enableAutoRefresh == "1")
    {
        $output .= '<script type="text/javascript">
var refreshTimer = "' . autoRefreshTimer . '";
var refreshCancelled = "0";
var replaceStuff = "";

initializeTimer();

</script>
';
    }
}

function levelshotJavascriptAndCSS()
{
    $output = '<script>
    var runSetup = 1;   //This variable allows the setup script to execute
    var timer = 0;  //Used for setting re-execution timeout
    var originalStyleData = "";   //Used to contain the original CSS info while fading.
    var levelshotTransitionAnimation = ' . levelshotTransitionAnimation . ';    //This value specifies the I.D. of the levelshot transition to use. The value is 0 if transitions are to be random
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
    $lastRefreshTime = file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt");

    if($lastRefreshTime == "")
    {
        //If time.txt is empty, then this is the first time ParaTracker has ever connected to this server.
        //We need to skip waiting, go back and update!
        return;
    }

$lastRefreshTime = numericValidator($lastRefreshTime, "", "", "wait");

$i = 0;
$sleepTimer = "0.15"; //This variable sets the number of seconds PHP will wait before checking to see if the refresh is complete.
$checkWaitValue = file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt");  //This variable is used to check if the wait value changes below
$fileInput = $checkWaitValue;

    //connectionTimeout needs to be multiplied by two, because doUpdate will attempt to connect twice before giving up.
    while ($lastRefreshTime == "wait" && $i < (connectionTimeout * 2 + refreshTimeout))
    {
        //infoPath/time.txt indicated that a refresh is in progress. Wait a little bit so it can finish. If it goes too long, we'll continue on, and force a refresh.
        usleep($sleepTimer * 1000000);
        $fileInput = file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt");

        if($checkWaitValue != $fileInput && stripos($fileInput, "wait" !== false))
        {
            //Another client has started a refresh! Let's start our wait period over so we don't DoS the game server by accident.
            $checkWaitValue = file_get_contents(infoPath . $dynamicIPAddressPath . "time.txt");
            $i = 0;
        }
        $lastRefreshTime = numericValidator($fileInput, "", "", "wait");
        $i += $sleepTimer;
    }

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

function numericValidator($input, $minValue, $maxValue, $defaultValue)
{
//To use this function, use the following:
//$variableName = numericValidator($variableName, min, max, default);
//If you do not wish to pass a value, just pass a null string "" instead.

    //First, let's trim any possible white space that may have been left accidentally
    $input = trim($input);

    //Is the input numeric? If not, just give it the default value.
    if (is_numeric($input))
    {
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
        //Value checks out! Leave it be and do nothing.
        else
        {
        }
    }
    else
    {
        //Non-numeric value detected! Force the default.
        $input = $defaultValue;
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
        if($working[$i] == ".." || $working[$i] == ".")
        {
            //Value jumps back a folder.
            $depth--;
            if($depth < 0)
            {
                displayError("Access is forbidden to '" . $input . "'!<br>Cannot go outside the intended folder.<br>This event has been logged.", "", "");
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

function stringValidator($input, $maxLength, $defaultValue)
{
    //Is the string null? If not, continue.
    if ($input != "")
    {	
		$input = stringClean($input);
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
//        $input = str_replace("'", "&#39;", $input);  //Removing these will break the levelshot path for any game with an apostrophe in the name
        $input = str_replace("\"", "&quot;", $input);
//        $input = str_replace(".", "&#46;", $input);  //Commented this out, because it breaks the IP address validator, which results in blank trackers
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

function ipAddressValidator($input, $serverPort, $dynamicTrackerEnabled)
{
    //Remove whitespace
    $input = trim($input);

    //Check to see if an address was supplied
    if($input == "")
    {
        //No address. Are we running in dynamic mode?
        if($dynamicTrackerEnabled == "0")
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
            //DNS test failed. Just error out.
            displayError('Invalid domain name! ' . stringValidator($input, "", "") . '<br />Check the address and try again.', "", "");
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
        if(substr(strtolower($customSkin), -4) == ".css")
        {
            $customSkin = substr($customSkin, 0, count($customSkin) - 5);
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
    $output = htmlDeclarations($serverIPAddress . " Parameter List", "");

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
    </td></tr></table><h4 class="center">ParaTracker 1.3.4 - Server info parsed in <span id="parseTime"></span> milliseconds.</h4><h5>Copyright &copy; 1837 Rick Astley. No rights reserved. Batteries not included. Void where prohibited.<br />Your mileage may vary. Please drink and drive responsibly.</h5><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></body></html>';

    return $output;
}

function getHumanReadableFilesize($file)
{
    $val = filesize($file);
    if ($val < 2048) return '<span class="bytes">' . $val . ' B</span>';
    else if ($val < 2097152) return '<span class="kilobytes">' . round($val / 1024, 2) . ' KiB</span>';
    else return '<span class="megabytes">' . round($val / 1048576, 2) . ' MiB</span>';
}

function displayError($errorMessage, $lastRefreshTime, $dynamicIPAddressPath)
{
    $serverIPAddress = "";
    $serverPort = "";
    $serverAddressStuff = "";

    if($dynamicIPAddressPath != "")
    {
        $brokenAddress = breakDynamicAddressPath($dynamicIPAddressPath);
        $serverIPAddress = $brokenAddress[0];
        $serverPort = $brokenAddress[1];
    }

    if(trim($errorMessage) == "")
    {
        $errorMessage = "An unknown error has occurred! ParaTracker must terminate.";
    }

    if(!empty($dynamicIPAddressPath)) $serverAddressStuff = "Server being tracked: " . $serverIPAddress . ":" . $serverPort;

    //Let's log this event...
    $errorLog = date(DATE_RFC2822) . "  Client IP Address: " . $_SERVER['REMOTE_ADDR'] . "  " . $serverAddressStuff . "  Error message: " . $errorMessage;
    writeToLogFile("errorLog.php", $errorLog, errorLogSize);

    //If postgres is enabled, we need to log this event to the database
    if(enablePGSQL)
    {
        global $pgCon;
        pg_query($pgCon, 'INSERT INTO tracker.displayerror DEFAULT VALUES');
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
    echo $errorMessage;

    if(defined('analyticsBackground') && !analyticsBackground)
    {
        //We are not running the analytics background process, so terminate here.
        //If no file path was given, flood protection will not be necessary, as ParaTracker never had a chance to contact the server.
        //so it is safe to terminate regardless of whether there was a file path or not.
        exit();
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

function htmlDeclarations($pageTitle, $filePath)
{
    $pageTitle = stringValidator($pageTitle, "", "ParaTracker - The Ultimate Quake III Server Tracker");
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
    //The output returned will be an array. Position 0 is a full game list, position 1 is a filtered game list (Useful for hiding duplicate game entries)
    $gameList = $gameListArray[1];
    $gameOutput = "";

    $gameOutput .= implode($gameList, ', ') . ', ';

    $urlWithoutParameters = explode('?', $_SERVER["REQUEST_URI"], 2);
    $currentURL = $_SERVER['HTTP_HOST'] . $urlWithoutParameters[0];

    $output = htmlDeclarations("", "");

    $output .= '<script src="js/ParaScript.js"></script><meta name="keywords" content="Free PHP server tracker, server, tracker, ID Tech 3, JediTracker, Jedi Tracker, ' . $gameOutput .'Game Tracker, Custom Colors, JSON, Bit Value Calculator, Bit Flag Calculator, Bit Mask Calculator">
  <meta name="description" content="Free Server Tracker for ' . $gameOutput . 'Written in PHP, with custom colors, JSON compatible, Bit Value Calculator">
  <meta name="author" content="Parabolic Minds">

    <script src="js/Bitflags.js"></script>

    </head><body id="body" class="dynamicConfigPage dynamicConfigPageStyle" onhashchange="changeSetupPageFunction()">
';

    $output .= '<div class="paraTrackerTestFrameTexturePreload"></div>';
    $output .= '<div class="utilitiesTopRow">';

    $output .= '<br /><h1>' . versionNumber() . ' - Dynamic Mode</h1>
    <i id="dynamicTrackerMessage">' . $personalDynamicTrackerMessage . '<br /><br /></i>';

    $output .= '<p><a class="dynamicFormButtons dynamicFormButtonsStyle utilitiesButton" id="utilitiesButton" onclick="document.location.hash=\'utilities\'"></a></p></div>';

    $output .= '<div id="utilities" class="utilitiesButtonRow utilitiesDiv collapsedFrame">';

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
            //$output .= '<div class="loginText">Log in to access all ParaTracker features</div>';
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
    $output .= '<p class="dynamicPageWidth"><a id="bitValueCalculatorButton" onclick="document.location.hash=\'bitValueCalculator\'" class="dynamicFormButtons dynamicFormButtonsStyle">Bit Value Calculator</a>';

if(mapreqEnabled == "1")
{
    $output .= '<a id="mapreqButton" onclick="document.location.hash=\'mapreq\'" class="dynamicFormButtons dynamicFormButtonsStyle">Levelshot Requests</a>';
}

if(analyticsFrontEndEnabled == "1")
{
    $output .= '<a id="analyticsButton" onclick="document.location.hash=\'analytics\'" class="dynamicFormButtons dynamicFormButtonsStyle">Tracker Analytics</a>';
}

if(admin)
{
    $output .= '<a id="logViewerButton" onclick="document.location.hash=\'logViewer\'" class="dynamicFormButtons dynamicFormButtonsStyle">Log Viewer</a>';
    $output .= '<a id="accountManagementButton" onclick="document.location.hash=\'accountManagement\'" class="dynamicFormButtons dynamicFormButtonsStyle">Account Management</a>';
}

$output .= '</p><br><br></div><div class="utilitiesDiv utilitiesBottomRow">';

//Levelshot requests form
if (mapreqEnabled == "1")
{
    $output .= '<div id="mapreqDiv" class="mapreqDiv collapsedFrame"><span class="reqforminfo">&gt;&gt;&gt; Is ParaTracker missing levelshots? Fill out the below form to request levelshots for a specific map. &lt;&lt;&lt;</span><br>';
    $output .= '<iframe src="' . utilitiesPath . 'MapReq.php" class="mapReqFrame" allowtransparency="true"></iframe></div>';
}


//Let's add analytics here
if(analyticsFrontEndEnabled == "1")
{
    $output .= '<div id="analyticsDiv" class="analyticsFrame utilitiesIframe collapsedFrame">
    <iframe src="' . utilitiesPath . 'Analytics.php" class="analyticsFrame"></iframe>
    </div>';
}

//Add the log viewer and account management
if(admin)
{
    $output .= '<div id="logViewerDiv" class="logViewerDiv utilitiesIframe collapsedFrame"><iframe src="' . utilitiesPath . 'LogViewer.php" class="logViewerFrame"></iframe></div>';

    $output .= '<div id="accountManagementDiv" class="accountManagementFrame utilitiesIframe collapsedFrame"><iframe src="' . utilitiesPath . 'AccountManagement.php" class="accountManagementFrame"></iframe></div>';
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
        $bitFlagData = array_slice($bitFlagData, 6);

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
                array_push($JSONIteratorArray, JSONObject("", $bitFlagsIteratorArray, 0));
            }
            array_push($JSONOutputArray, JSONString("gamename", $testName));
            array_push($JSONOutputArray, JSONString("gameClassName", "game_" . makeFunctionSafeName($testName)));
            array_push($JSONOutputArray, JSONArray("bitflags", $JSONIteratorArray, 0));
            array_push($JSONOutput, $JSONOutputArray);
        }
    }
    $JSONOutput = '<script type="text/javascript">var bitflags_raw = ' . JSONArray("", $JSONOutput, 4) . '</script>';

//Add the HTML we need for the bit value calculator
$output .= '<div id="bitValueCalculatorDiv" class="collapsedFrame"><h3 class="dynamicPageWidth" style="margin-top:0;">Bit value calculator:</h3>';
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

    $output .= '<div id="trackerSetup" class="expandedFrame">';

    $output .= '<h3 style="margin-top: 0;">Supported Games:</h3><div class="centerTable"><table class="centerTable"><tr>';

        $colorNumber = 0;

        $countGames = count($gameList);
        //Loop through the array of stuff listed
        for($i = 0; $i < $countGames; $i++)
        {
            //DO NOT make the directory list value lowercase here! It will make all dynamic game names appear in lowercase on the tracker
            $output .= '<td class="' . makeFunctionSafeName($gameList[$i]) . ' fiftyPercentWidth">' . $gameList[$i] . '</td>';

            //Was this an even trip through the array, but also not the last trip? If so, start a new table row.
            if($i % 2 != 0 && $i + 1 != count($gameList))
            {
                $output .= "</tr><tr>";
            }
        }

    $output .= '</tr></table></div><br />';

$output .= '<h3 class="dynamicPageWidth">Enter the data below to get a URL you can use for ParaTracker Dynamic:</h3>

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
            $skinList[$skinCount] = substr($directoryList[$i], 0, count($directoryList[$i]) - 5);
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
        //These must be re-declared every iteration
        $width = "";
        $height = "";

        $skinFile = file_get_contents(skinsPath . $skinList[$i] . ".css");

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
        $output .= '&nbsp;&nbsp;&nbsp;<span class="gameColor5">Height: </span><input id="customHeight" maxlength="7" size="7" type="text" value="" placeholder="300" onchange="createURL()" /></h3><h4><span class="gameColor1">Warning:</span> <span class="gameColor3">ParaTracker cannot detect problems with custom skins!</span></h4>';

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
        $output .= '&nbsp;&nbsp;&nbsp;<span class="gameColor1">Transition Time:&nbsp;</span><input id="levelshotTransitionTime" maxlength="5" size="3" type="text" value="" placeholder="' . levelshotTransitionTime . '" onchange="createURL()" /><br /><span class="smallText">(Times are given in seconds. Decimals are accepted.)</span></div>';
    }

    $output .= '<h2 class="gameColor3">Colors</h2><h4>All colors are in hexadecimal (#123456).<br /><span class="smallText">These settings do not apply to JSON skins.</span></h4><p><span class="gameColor2">Background Color:</span>&nbsp;&nbsp;# <input id="backgroundColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
    $output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="backgroundOpacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';
    $output .= '<p><span class="gameColor0">Player List Color 1:</span>&nbsp;&nbsp;# <input id="playerListColor1" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
    $output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="playerListColor1Opacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';
    $output .= '<p><span class="gameColor9">Player List Color 2:</span>&nbsp;&nbsp;# <input id="playerListColor2" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';
    $output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="playerListColor2Opacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %<br /><span class="smallText">(Opacity only works when a background color is applied)</span></p>';
    $output .= '<p><span class="gameColor5">Scroll Shaft Color</span>&nbsp;&nbsp;# <input id="scrollShaftColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';

//    $output .= '<span class="gameColor4">&nbsp;&nbsp;&nbsp;<strong>Opacity:</strong>&nbsp;</span><input id="scrollShaftOpacity" maxlength="3" size="3" type="text" value="" placeholder="100" onchange="createURL()" /> %';

    $output .= '</p>';
    $output .= '<p><span class="gameColor5">Scroll Thumb Color</span>&nbsp;&nbsp;# <input id="scrollThumbColor" maxlength="6" size="7" type="text" value="" onchange="createURL()" /> ';

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
WE COMPLY WITH ALL LAWS AND REGULATIONS REGARDING THE USE OF LAWS AND REGULATIONS. WE PROMISE THAT THIS THING IS A THING. THIS THING COLLECTS INFORMATION. THIS INFORMATION IS THEN USED TO MAKE MISINFORMATION. THIS MISINFORMATION IS THEN SOLD TO THE MOST NONEXISTENT BIDDER. BY READING THIS, YOU AGREE. CLICK NEXT TO CONTINUE. OTHERWISE, CONTINUE ANYWAY AND SEE IF WE CARE. WOULD YOU LIKE TO SET PARATRACKER AS YOUR HOME PAGE? TOO BAD, WE DID IT ALREADY. WE ALSO INSTALLED A BROWSER TOOLBAR WITHOUT ASKING, BECAUSE TOOLBARS ARE COOL AND SO ARE WE.
</h6>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />Nope, nothing here. Promise.
    </div>
    </body>
    </html>';

    echo '-->' . $output;

    exit();
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
    $serverPort = substr($dynamicIPAddressPath, $split + 1);

    return (array($serverAddress, $serverPort));
}

function connectToServerAndGetResponse($serverIPAddress, $serverPort, $messageToSend, $lastRefreshTime)
{
        $dynamicIPAddressPath = makeDynamicAddressPath($serverIPAddress, $serverPort);
        $s = "";
        $errstr = "";

        $fp = fsockopen("udp://" . $serverIPAddress, $serverPort, $errno, $errstr, 30);
        if(!fwrite($fp, $messageToSend))
        {
            $errstr = "Could not open the connection to the game server!\n<br>Make sure your web host allows outgoing connections.";
            file_put_contents(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
            return 0;
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
            displayError('Received maximum data allowance!<br />' . strlen($s) . ' bytes received, the limit is ' . maximumServerInfoSize . '<br />Check to see if you are connected to the correct server or increase $maximumServerInfoSize in ParaConfig.php.', $lastRefreshTime, $dynamicIPAddressPath);
            return 0;
        }

        if(!$s)
        {
            if(microtime(true) - $connectionTimer >= connectionTimeout)
            {
                $errstr = "No response.";
                file_put_contents(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
                return 0;
            }
            else
            {
                //I think if we get here the connection was refused....but I'm not sure. So we'll go with the same message as above
//                $errstr = "Connection refused.";
                $errstr = "No response.";
                file_put_contents(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));
                return 0;
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

function sendReceiveRConCommand($serverIPAddress, $serverPort, $lastRefreshTime, $RConPassword, $RConCommand)
{
//Make sure the log file exists before we do anything else
if(!checkFileExistence("RConLog.php", logPath . makeDynamicAddressPath($serverIPAddress, $serverPort))) return 'Could not create RCon log file! Command not sent.';

$serverResponse = "";
$output = "";
$s = "";
$RConLog = "";
$RConLog2 = "";

if ($RConPassword != "" && $RConCommand != "")
        {
                $output .= '';

                $s = connectToServerAndGetResponse($serverIPAddress, $serverPort, str_repeat(chr(255),4) . 'RCon ' . $RConPassword . ' ' . $RConCommand, $lastRefreshTime);

                if($s)
                {
                    $serverResponse = $s;

                    //Replace line breaks for the RCon log only
                    $newRConLogEntry = str_replace(chr(0x0A), '\n', $serverResponse);

                    //Validate the rest!
                    $serverResponse = stringValidator($serverResponse, "", "");

                    //Now we format the remaining data in a readable fashion
                    $serverResponse = str_replace('ÿÿÿÿprint', '', $serverResponse);
                    $serverResponse = str_replace(chr(0x0A), '<br />', trim($serverResponse));

                    //Check for exploits in the response that might trigger some PHP code
                    $serverResponse = stringValidator($serverResponse, "", "");
                }
                else
                {
                    $serverResponse = 'No response from server at ' . $serverIPAddress . ':' . $serverPort . '!';
                    $newRConLogEntry = $serverResponse;
                }

                $output .= $serverResponse;
        }


    //Log time!
    $RConLog = date(DATE_RFC2822) . "  Client IP Address: " . $_SERVER['REMOTE_ADDR'] . "  Command: " . $RConCommand . "  Response: " . $newRConLogEntry . $RConLog2;
    writeToLogFile(makeDynamicAddressPath($serverIPAddress, $serverPort) . "RConLog.php", $RConLog, RConLogSize);

    return $output;
}

function renderNormalHTMLPage($dynamicIPAddressPath)
{

$output = htmlDeclarations("ParaTracker - The Ultimate Quake III Server Tracker", "");

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
$output .= '<div class="ParaTrackerLogo textColor"></div>';

//This adds the ParaTracker text to the page.
$output .= '<div id="paraTrackerVersion" class="ParaTrackerText textColor">' . versionNumber() . '</div>';

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
    $output .= '<div id="analyticsButton" onclick="analytics_window();"><div id="analyticsButtonText" class="analyticsButton textColor"></div></div>';
}

//This adds the RCon button to the page.
$output .= '<div id="rconButton" onclick="rcon_window();"><div id="rconButtonText" class="rconButton textColor"></div></div>';

//This adds the Param button to the page.
$output .= '<div id="paramButton" onclick="param_window();"><div id="paramButtonText" class="paramButton textColor"></div></div>';

//This adds the reconnect button to the page
$output .= '<div id="reconnectButton" onclick="pageReload();"><div id="reconnectButtonText" class="reconnectButton textColor"></div></div>';

//This adds the levelshots to the page.
$output .= '<div id="levelshotPreload2" class="levelshotFrame levelshotSize textColor" onclick="levelshotClick()">
<div id="levelshotPreload1" class="levelshotSize"></div>
<div id="bottomLayerFade" class="levelshotSize"></div>
<div id="topLayerFade" class="levelshotSize"></div>
<div id="loading" class="levelshotSize loadingLevelshot textColor collapsedFrame"></div>
</div>';

//This adds the frame to the page.
$output .= '<div class="TrackerFrame textColor"></div>';

//This line adds a compatibility warning in case JavaScript is disabled
$output .= '<noscript>JavaScript is disabled. ParaTracker requires JavaScript!</noscript>';

//This adds the refresh timer and the timer script to the page.
$output .= '<div onclick="toggleReload()"><div id="refreshTimer" class="reloadTimer textColor" title="Click to cancel auto-refresh"></div></div>';

$output .= '</div></div>
</body>
</html>';

return $output;
}

function renderJSONOutput($dynamicIPAddressPath)
{
    $outputArray = array();
    $temp = breakDynamicAddressPath($dynamicIPAddressPath);
    $serverIPAddress = $temp[0];
    $serverPort = $temp[1];

    //Add the version number to the output array
    array_push($outputArray, JSONString("version", versionNumber()));

    //Add the relevant config data and the server ping to the output
    array_push($outputArray, JSONBoolean("filterOffendingServerNameSymbols", filterOffendingServerNameSymbols));
    array_push($outputArray, JSONNumber("levelshotTransitionTime", levelshotTransitionTime)); //This value takes the transition time value given in ParaConfig and passes it to the Javascript.
    array_push($outputArray, JSONNumber("levelshotDisplayTime", levelshotDisplayTime)); //This value takes the display time given in ParaConfig and passes it to the Javascript.
    array_push($outputArray, JSONNumber("allowTransitions", levelshotTransitionsEnabled));  //Used to test whether fading levelshots is disabled.

    array_push($outputArray, JSONBoolean("enableGeoIP", enableGeoIP));  //Used to test whether GeoIP is enabled
    array_push($outputArray, JSONBoolean("enableAutoRefresh", enableAutoRefresh));  //Used to disable the auto-refresh timer

    array_push($outputArray, JSONString("serverIPAddress", $serverIPAddress));
    array_push($outputArray, JSONString("serverPort", $serverPort));
    array_push($outputArray, JSONString("serverNumericAddress", gethostbyname($serverIPAddress)));
    array_push($outputArray, JSONString("paraTrackerSkin", paraTrackerSkin));
    array_push($outputArray, JSONBoolean("levelshotTransitionsEnabled", levelshotTransitionsEnabled));
    array_push($outputArray, JSONString("levelshotTransitionAnimation", levelshotTransitionAnimation));
    array_push($outputArray, JSONString("noPlayersOnlineMessage", noPlayersOnlineMessage));
    array_push($outputArray, JSONBoolean("enableAutoRefresh", enableAutoRefresh));
    array_push($outputArray, JSONNumber("autoRefreshTimer", autoRefreshTimer));
    array_push($outputArray, JSONBoolean("RConEnable", RConEnable));
    array_push($outputArray, JSONNumber("RConFloodProtect", RConFloodProtect));
    array_push($outputArray, JSONBoolean("displayGameName", displayGameName));
    array_push($outputArray, JSONString("utilitiesPath", utilitiesPath));

    array_push($outputArray, JSONString("connectionErrorMessage", file_get_contents(infoPath . $dynamicIPAddressPath . "connectionErrorMessage.txt")));

    //Time for levelshot stuff. Get the array from the file, explode it and validate it as an array of strings
    array_push($outputArray, JSONArray("levelshotsArray", explode(":#:", file_get_contents(infoPath . $dynamicIPAddressPath . "levelshots.txt")), 3));


    array_push($outputArray, JSONNumber("reconnectTimeout", floodProtectTimeout));

    //Now let's include the rest of the data (It is already parsed correctly in several text files)
    $file1 = file_get_contents(infoPath . $dynamicIPAddressPath . "JSONServerInfo.txt");
    $file2 = file_get_contents(infoPath . $dynamicIPAddressPath . "JSONParsedInfo.txt");
    $file3 = file_get_contents(infoPath . $dynamicIPAddressPath . "JSONParams.txt");
    $file4 = file_get_contents(infoPath . $dynamicIPAddressPath . "JSONPlayerInfo.txt");

    //If these files are empty, then the server connection failed, and we need to communicate that with the tracker
    if($file1 != "" && $file2 != "" && $file3 != "" && $file4 != "")
    {
        array_push($outputArray, JSONBoolean("serverOnline", 1));
        array_push($outputArray, $file1);
        array_push($outputArray, $file2);
        array_push($outputArray, $file3);
        array_push($outputArray, $file4);
    }
    else
    {
        array_push($outputArray, JSONBoolean("serverOnline", 0));
    }

$output = JSONObject("", $outputArray, 0);

return $output;
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

function JSONObject($variableName, $input, $mode)
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
    if($input == "1" || $input == "true" || $input == "yes" || strtolower($input == "t"))
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
                $input[$i] = JSONObject("", $input[$i], 0);
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
    $serverIPAddress = $brokenAddress[0];
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

    $serverAddress = $broken[0];
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
    $loadAverage = "0";
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
        if($directoryList[$i] != "." && $directoryList[$i] != "..")
        {
            array_push($output, $directoryList[$i]);
        }
    }
return $output;
}

function cleanupInfoFolder($cleanupInterval, $deleteInterval, $loadLimit, $cleanupLogSize)
{
    $cleanupTimer = microtime(true);
    $loadAverage = getSystemLoadAverage();

    //Let's make these useful
    $deleteInterval = $deleteInterval * 86400;
    $deleteInterval = time() - $deleteInterval;
    $cleanupInterval = $cleanupInterval * 60;

    //Get the info folder's timeout file and validate it
    $currentTimer = numericValidator(file_get_contents(infoPath . "cleanupTimer.txt"), "", "", "0");

    //If the delete interval is larger than the current timer, ParaTracker was likely offline or not working, so no data should be cleaned up this time.
    //Running cleanup wrongfully will mess up analytics.
    if($currentTimer < $deleteInterval)
    {
        $warningMessage = date(DATE_RFC2822) . ": Warning: massive gap in cleanup timer detected! ParaTracker assumed offline for an extended period of time. Foregoing cleanup and resetting timer to preserve data.";
        writeToLogFile("cleanupLog.php", array($warningMessage, ""), $cleanupLogSize);
        echo $warningMessage;
        file_put_contents(infoPath . "cleanupTimer.txt", time());
        return "";
    }

    if($currentTimer + $cleanupInterval < time() && $currentTimer > 0)
    {
        //Prevent users from aborting the page so the cleanup will always finish!
        ignore_user_abort(true);

        //First thing's first, we need to prevent a second cleanup from running.
        file_put_contents(infoPath . "cleanupTimer.txt", time());

        //Let's start a new cleanup log entry.
        $cleanupLog = array("Running cleanup on " . date(DATE_RFC2822) . ":");

        if($loadAverage > $loadLimit)
        {
            array_push($cleanupLog, "Server load is too high! Load is currently " . $loadAverage . "%, the limit is " . $loadLimit . "%. Cleanup cancelled.");
        }
        else
        {
            array_push($cleanupLog, "Server load OK! " . $loadAverage . "% load, threshold for cancellation is " . $loadLimit . "%. Starting cleanup...");

            //Time to run cleanup!
            echo " Running cleanup... ";

            $directoryList = getDirectoryList(infoPath);
            $count = count($directoryList);

            //Loop through the array of folders, and check the time values on everything. If the folder hasn't been refreshed in a while, delete it.
            for($i = 0; $i < $count; $i++)
            {
                //Make sure we are only cleaning up directories, and not files!
                if(is_dir(infoPath . $directoryList[$i]))
                {
                    if(file_exists(infoPath . $directoryList[$i] . "/time.txt"))
                    {
                        $currentInfoFolderTime = numericValidator(file_get_contents(infoPath . $directoryList[$i] . "/time.txt"), "", "", "wait");
                    }
                    else
                    {
                        $currentInfoFolderTime = 0;
                    }

                    //If we're using analytics, the database value should override the last refresh time
                    if(analyticsEnabled && checkDatabaseForServerTime($directoryList[$i]) < $deleteInterval)
                    {
                        array_push($cleanupLog, "Database requires deletion of:  " . infoPath . $directoryList[$i]);
                        $currentInfoFolderTime = $deleteInterval - 1;
                    }

                    if($currentInfoFolderTime < $deleteInterval && $currentInfoFolderTime != "wait")
                    {
                        array_push($cleanupLog, "Emptying:  " . infoPath . $directoryList[$i]);
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
                            $deletionMessage = 'Deleted:  ' . infoPath . $directoryList[$i];
                            if(disableServerInDatabase($directoryList[$i]))
                            {
                                $deletionMessage .= ' Successfully disabled server in database.';
                            }
                            else
                            {
                                $deletionMessage .= ' Failed to disable server in database.';
                            }
                            array_push($cleanupLog, $deletionMessage);
                        }
                         else
                        {
                            //Failed! Add it to the cleanup log.
                            $deletionMessage = 'Failed to delete:  ' . infoPath . $directoryList[$i];
                            if(analyticsEnabled && disableServerInDatabase($directoryList[$i]))
                            {
                                $deletionMessage .= ' Successfully disabled server in database.';
                            }
                            else
                            {
                                $deletionMessage .= ' Failed to disable server in database.';
                            }
                            array_push($cleanupLog, $deletionMessage);
                        }
                    }
                    else
                    {
//                        array_push($cleanupLog, "Ignored:  " . infoPath . $directoryList[$i] . " Time is too soon. " . $currentInfoFolderTime . " < " . $deleteInterval);
                    }
                }
            }

            //Now let's clean up the map requests, if they are enabled.
            if(mapreqEnabled)
            {
                array_push($cleanupLog, "Done! Starting cleanup of levelshot requests...");
                $mapsArray = mapreq_get_maps();
                $count2 = count($mapsArray);
                for($i = 0; $i < $count2; $i++)
                {
                    $game_name = $mapsArray[$i]['game_name'];
                    $bsp_name = $mapsArray[$i]['bsp_name'];

                    //Insert game-specific function execution here
		            $gameFunctionParserReturn = ParseGameData($game_name, "", "", "", "");
                    $levelshotFolder = $gameFunctionParserReturn[1];

                    $shotNumber = levelshotfinder("", $bsp_name, $levelshotFolder, $game_name, 1);

                    if($shotNumber > 0)
                    {
                        array_push($cleanupLog, "Levelshots found for " . $game_name . " - '" . $bsp_name . "', removing request!");
                        mapreq_delete_map($game_name, $bsp_name);
                    }
                }
                array_push($cleanupLog, 'Finished cleanup! Took ' . number_format(((microtime(true) - $cleanupTimer) * 1000), 0) . ' milliseconds');
            }

        }
    //Add a space at the end of the cleanup log, for readability
    $cleanupLog = array_merge($cleanupLog, array(""));
    writeToLogFile("cleanupLog.php", $cleanupLog, $cleanupLogSize);

    //Allow users to abort the page again.
    ignore_user_abort(false);
    }
}

// returns array of hashmap (with 'game_name' and 'bsp_name'), i.e. $data[2]['bsp_name']
function mapreq_get_maps()
{
  global $pgCon;
  return pg_fetch_all(pg_query($pgCon, 'SELECT game_name, bsp_name FROM mapreq'));
}

function mapreq_delete_map($game_name, $bsp_name)
{
  global $pgCon;
  pg_query_params($pgCon, 'DELETE FROM mapreq WHERE game_name = $1 AND bsp_name = $2', array($game_name, $bsp_name));
}

function readLogFile($filename)
{
    //This function reads in a log file, trims the header and footer, and returns it as an array
    return explode("\n", substr(file_get_contents(logPath . $filename), strlen(logHeader($filename)), strlen(logFooter()) * -1));
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
        $output .= '<tr><td class="logViewerNumber">' . ($i + 1) . '</td><td class="logViewerText">' . stringValidator($logContents[$i], "", "", "") . '</td></tr>';
    }

    $output = '<h2 class="logTitle">' . $input . '</h2><p class="logSize">' . $i .' lines</p>' . logGoBackDirectoryLink() . $output;

    return '<table>' . $output . '</table>' . logGoBackDirectoryLink();
}

function logGoBackDirectoryLink()
{
    $output = '<p><a href="' . this_file . '?path=' . rtrim(filepath, '/') . '/.." class="logLink">Go Back</a></p>';
    return $output;
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
// ParaTracker forces a minimum value of 5 seconds between snapshots. Maximum is 1200 seconds.
// This value cannot be lower than the value of $connectionTimeout (below).
// Default is 15 seconds.
$floodProtectTimeout = "15";

// This value is the number of seconds ParaTracker will wait for a response from the game server
// before timing out. If the first attempt fails, a second attempt will be made.
// ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
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

// This is a 6 character hexadecimal value that specifies the color of the scrollbar shaft.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$scrollShaftColor = "";

// This is a 6 character hexadecimal value that specifies the color of the scrollbar thumb.
// The skin chosen will already have it\'s own color; this value will override it, if desired.
// Default value is "".
$scrollThumbColor = "";

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

// ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be searched for first, JPGs second,
// and GIFs third. If no images are found, a placeholder image will be displayed instead.

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
// Valid values are whole numbers between 0 to 999 (No decimals).
// Setting this value to 0 will play a random animation from this list.
// Default value is 0
// The default transitions are as follows:
// Transition 1: Fade
// Transition 2: Fade to black
// Transition 3: Hue shift
// Transition 4: Skew
// Transition 5: Horizontal Stretch
// Transition 6: Stretch and rebound
// Transition 7: Slide to left
// Transition 8: Slide to right
// Transition 9: Slide to top
// Transition 10: Slide to bottom
// Transition 11: Spin and fly to top left
// Transition 12: Spin and fly to top right
// Transition 13: Fall away and spin
// Transition 14: Zoom in
// Transition 15: Blur
$levelshotTransitionAnimation = "0";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better.
// Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


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
// Default is 30 seconds.
$autoRefreshTimer = "30";

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
// Minimum is 10 seconds, maximum is 3600.
// Cannot be lower than the value of $connectionTimeout.
// Default is 20 seconds.
$RConFloodProtect = "20";


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


/*==================================================================================================*/
// EMAIL SETTINGS
// EMAIL SETTINGS

// These settings are for email functions.
// Emails are sent by SendEmails.php, which is found in the $utilitiesPath folder.
// SendEmails.php must be activated by a cron job, that runs at the frequency you want to receive administrative reports.

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

    // If using SMTP, this variable is the username we will use for the SMTP server
    // Default is ""
    $smtpUsername = "";

    // If using SMTP, this variable is the password we will use for the SMTP server
    // Default is ""
    $smtpPassword = "";

// This is the address that will be used to send the email.
// Default is ""
$emailFromAddress = "";

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

// RCon events are logged in RConLog.php. Each server has it\'s own unique RCon log, which is found in the
// $logPath folder, inside a folder named for the server\'s IP address and port.
// This setting will determine the maximum number of lines that will be stored in the log file
// before the old entries are truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 1000 lines.
$RConLogSize = "1000";


/*==================================================================================================*/
// End of config file

/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

?>';

    file_put_contents('ParaConfig.php', $configBuffer);
}

?>
