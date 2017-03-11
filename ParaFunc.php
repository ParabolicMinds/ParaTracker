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
    Return("ParaTracker 1.3");
}

//Define the default skin, to be used throughout this file. MAKE SURE it refers to an actual .css file in the skins folder, or it will break stuff. And do not include the file extension!
$defaultSkin = "Metallic Console";


//This block is here to suppress error messages
$lastRefreshTime = "";
$floodProtectTimeout = "";
$serverPort = "";
$executeDynamicInstructionsPage = "0";


define("defaultSkin", $defaultSkin);

if (!isset($safeToExecuteParaFunc))
{
    displayError("ParaFunc.php is a library file and can not be run directly!<br />Try running ParaTrackerStatic.php or ParaTrackerDynamic.php instead.", $lastRefreshTime);
}

//If this file is executed directly, then echoing this value here will display the version number before exiting.
//If the file is executed from one of the skin files, then this will end up in an HTML comment and will not be visible.
//Either way, the version number will be visible.
echo " " . versionNumber() . " ";


if (file_exists("ParaConfig.php"))
{
    include 'ParaConfig.php';
}
else
{
    writeNewConfigFile();
    if (file_exists("ParaConfig.php"))
    {
        displayError("ParaConfig.php not found! A default config file has been written to disk.<br />Please add an IP Address and port to it.", $lastRefreshTime);
    }
    else
    {
        displayError("ParaConfig.php not found! Attempted to write a default config file, but failed!<br />Make sure ParaTracker has file system access, and that the disk is not full!", $lastRefreshTime);
    }
}

if(file_exists("GameInfo.php"))
{
    $safeToExecuteGameInfo = "1";
    include("GameInfo.php");
}
else
{
    displayError( "GameInfo.php not found!", "");
}


//These three IF statements will avoid warning messages during validation
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

/*
Before we go any further, let's validate ALL input from the config file!
To validate booleans:
$variableName = booleanValidator($variableName, defaultValue);

To evaluate numeric values:
$variableName = numericValidator($variableName, minValue, maxValue, defaultValue);

To evaluate strings:
$variableName = stringValidator($variableName, maxLength, defaultValue);
*/

//These values MUST be evaluated first, because they are used in the IP address validation.
//All ParaTracker files call this same file, so we need to be sure which file is calling,
//and what to do about it.
$dynamicTrackerCalledFromCorrectFile = booleanValidator($dynamicTrackerCalledFromCorrectFile, 0);
$dynamicTrackerEnabled = booleanValidator($dynamicTrackerEnabled, 0);
$personalDynamicTrackerMessage = stringValidator($personalDynamicTrackerMessage, "", "");

if($dynamicTrackerEnabled == "1" && $dynamicTrackerCalledFromCorrectFile == "1")
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
                displayError("Cannot use RCon without an IP address!", "");
            }
        }
        if(isset($_GET["port"]))
        {
            $serverPort = $_GET["port"];
        }
    }
}

//By default, static mode will already have given us an IP address before all of this took place.
//So, now that we have the IP address and port from our source of choice, MAKE SURE to validate them before we go ANY further!
//The port must be validated first, because it is used in IP address validation.
$serverPort = numericValidator($serverPort, 1, 65535, 29070);
$serverIPAddress = ipAddressValidator($serverIPAddress, $serverPort);

//If the skin parameter is not set, we need to set it to the default value
if(isset($paraTrackerSkin))
{
    $paraTrackerSkin = skinValidator($paraTrackerSkin);
}
else
{
    $paraTrackerSkin = skinValidator($defaultSkin);
}

if($dynamicTrackerCalledFromCorrectFile == "1" || $calledFromParam == "1" || $calledFromRCon == "1")
{
    //We are running in Dynamic mode. Check to see if a skin file was specified in the URL.
    if(isset($_GET["skin"]))
    {
        //A skin was specified - load it in and validate it.
        $paraTrackerSkin = skinValidator(rawurldecode($_GET["skin"]));
    }
}


$connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2);
$floodProtectTimeout = numericValidator($floodProtectTimeout, 5, 1200, 15);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$floodProtectTimeout = numericValidator($floodProtectTimeout, $connectionTimeout, 1200, 10);
$refreshTimeout = numericValidator($refreshTimeout, 1, 15, 3);

$levelshotTransitionsEnabled = booleanValidator($levelshotTransitionsEnabled, 1);
$levelshotDisplayTime = numericValidator($levelshotDisplayTime, 1, 15, 3);
$levelshotTransitionTime = numericValidator($levelshotTransitionTime, 0.1, 5, 1);
$levelshotTransitionAnimation = numericValidator(round($levelshotTransitionAnimation), 0, 999, 0);
$maximumLevelshots = numericValidator($maximumLevelshots, 1, 99, 20);

$filterOffendingServerNameSymbols = booleanValidator($filterOffendingServerNameSymbols, 1);

$noPlayersOnlineMessage = stringValidator($noPlayersOnlineMessage, "", "No players online.");

$enableAutoRefresh = booleanValidator($enableAutoRefresh, 1);
//Have to validate this one twice to make sure it isn't lower than the floodprotect limit
$autoRefreshTimer = numericValidator($autoRefreshTimer, 10, 300, 30);
$autoRefreshTimer = intval(numericValidator($autoRefreshTimer, $floodProtectTimeout, 300, 30));
$maximumServerInfoSize = numericValidator($maximumServerInfoSize, 2000, 50000, 16384);

$RConEnable = booleanValidator($RConEnable, 0);
$RConMaximumMessageSize = numericValidator($RConMaximumMessageSize, 20, 10000, 100);

$RConFloodProtect = numericValidator($RConFloodProtect, 10, 3600, 20);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$RConFloodProtect = numericValidator($RConFloodProtect, $connectionTimeout, 3600, 20);
$RConLogSize = numericValidator($RConLogSize, 100, 100000, 1000);

$newWindowSnapToCorner = booleanValidator($newWindowSnapToCorner, 0);

$enableGeoIP = booleanValidator($enableGeoIP, 0);

if($enableGeoIP == "1")
{
    //If GeoIP is found, include it. If there is no GeoIP file found, disable it.
    if(file_exists($geoIPPath))
    {
        include($geoIPPath);
    }
    else
    {
        echo " Could not find GeoIP. Ignoring... ";
        $enableGeoIP = 0;
        $geoIPPath = "";
    }
}



//The IP address has already been validated, so we can use it for a directory name
//Make sure we convert the path to lowercase when creating folders for it, or else the flood protection could be bypassed!
$dynamicIPAddressPath = strtolower($serverIPAddress . "-" . $serverPort . "/");


//Now that everything has been validated, let's define all ParaConfig values as global constants to make things easier.
define("dynamicIPAddressPath", $dynamicIPAddressPath);
define("serverIPAddress", $serverIPAddress);
define("serverPort", $serverPort);
define("floodProtectTimeout", $floodProtectTimeout);
define("connectionTimeout", $connectionTimeout);
define("refreshTimeout", $refreshTimeout);
define("paraTrackerSkin", $paraTrackerSkin);
define("levelshotTransitionsEnabled", $levelshotTransitionsEnabled);
define("levelshotDisplayTime", $levelshotDisplayTime);
define("levelshotTransitionTime", $levelshotTransitionTime);
define("levelshotTransitionAnimation", $levelshotTransitionAnimation);
define("maximumLevelshots", $maximumLevelshots);
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
define("newWindowSnapToCorner", $newWindowSnapToCorner);
define("enableGeoIP", $enableGeoIP);
define("geoIPPath", $geoIPPath);

if($executeDynamicInstructionsPage == "1")
{
    dynamicInstructionsPage($personalDynamicTrackerMessage);
}

//Add some checks to make sure we have directories for the stuff
checkDirectoryExistence("info/");
checkDirectoryExistence("info/" . dynamicIPAddressPath);

checkDirectoryExistence("images/levelshots");

checkDirectoryExistence("logs/");
checkDirectoryExistence("logs/" . dynamicIPAddressPath);

//And now let's check to make sure we have access to the file system to write all the files we need. 
checkForMissingFiles();

//This needs to run every time the tracker is run. Otherwise the "No connection" pages will be missing the counter
autoRefreshScript();

function checkForMissingFiles()
{
    checkFileExistence("connectionErrorMessage.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("errorMessage.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("gametype.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("gamename.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("JSONParams.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("levelshots.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("mapname.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("mapname_raw.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("modname.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("param.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("playerCount.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("playerList.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("RConTime.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("RConLog.php", "logs/" . dynamicIPAddressPath);
    checkFileExistence("refreshCode.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("serverDump.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("serverPing.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("sv_hostname.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("sv_maxclients.txt", "info/" . dynamicIPAddressPath);
    checkFileExistence("time.txt", "info/" . dynamicIPAddressPath);
}

function checkFileExistence($filename, $folder)
{
    if (!file_exists($folder . $filename))
    {
        file_put_contents($folder . $filename, "");
        if (!file_exists($folder . $filename))
        {
            displayError("Failed to create file " . $folder . $filename . "!<br />Make sure ParaTracker has file system access, and that the disk is not full!", $lastRefreshTime);
        }
    }
}

function checkDirectoryExistence($dirname)
{
    if (!file_exists($dirname))
    {
        mkdir($dirname);
    }
    if (!file_exists($dirname))
    {
        displayError("Failed to create directory " . $dirname . " in ParaTracker folder!<br />Cannot continue without file system access!", "");
    }
}

function checkLevelshotDirectoriesAndConvertToLowercase($levelshotFolder)
{
    $levelshotFolder = strtolower($levelshotFolder);

    //We need to convert any matching directory name to lowercase
    if(!file_exists("images/levelshots/" . $levelshotFolder))
    {

        $exit = "0";
        $directoryList = scandir("images/levelshots/");

        //Loop through the array of stuff listed, and see if there's anything that matches the given game name
        for($i = 2; $i < count($directoryList) && $exit == "0"; $i++)
        {
            if(strtolower($directoryList[$i]) == $levelshotFolder)
            {
                //We've already compared the names in lowercase. If they do not match in uppercase, we need to change it all to lowercase.
                if($directoryList[$i] != $levelshotFolder)
                {
                    if(rename("images/levelshots/" . $directoryList[$i], "images/levelshots/" . strtolower($directoryList[$i])) == false)
                    {
                        echo ' Could not rename directory ' . $directoryList[$i] . '! Levelshots will not work. Continuing without them.';
                    }
                    $exit = "1";
                }
            }
        }
    }

    //If we cannot find a match, then we will just leave it be, and the levelshot code will fall back on the placeholder image

    $levelshotFolder = "images/levelshots/" . $levelshotFolder . "/";
    return $levelshotFolder;
}

function checkForAndDoUpdateIfNecessary()
{

    //Check to see if a refresh is already in progress, and if it is, wait a reasonable amount of time for it to finish
    checkTimeDelay();

    $lastRefreshTime = numericValidator(file_get_contents("info/" . dynamicIPAddressPath . "time.txt"), "", "", "0");

        if ($lastRefreshTime + floodProtectTimeout < time())
        {

            //Prevent users from aborting the page! This will reduce load on both the game server and the web server
            //by forcing the refresh to finish.
            ignore_user_abort(true);

            //Check to see if we were forced here. If so, change the refresh time value so that other users will wait for our refresh. This will prevent an accidental DOS of the server during high traffic.
            if(substr(trim(file_get_contents("info/" . dynamicIPAddressPath . "time.txt")), 0, 4) == "wait")
            {
                file_put_contents("info/" . dynamicIPAddressPath . "time.txt", "wait" . rand(0, getrandmax()));
            }


            file_put_contents("info/" . dynamicIPAddressPath . "time.txt", "wait");

            //Remove any lingering error messages. We will write a new one later if we encounter another error.
            file_put_contents("info/" . dynamicIPAddressPath . "errorMessage.txt", "");

            doUpdate($lastRefreshTime);

            file_put_contents("info/" . dynamicIPAddressPath . "time.txt", time());

            //Allow users to abort the page again.
            ignore_user_abort(false);

        }

}

function doUpdate($lastRefreshTime)
{
	//Before we start, wipe out the parameter list. That way, if we encounter an error later, the list does not remain
    file_put_contents('info/' . dynamicIPAddressPath . 'levelshots.txt', "");
    file_put_contents('info/' . dynamicIPAddressPath . 'param.txt', "");
    file_put_contents('info/' . dynamicIPAddressPath . 'JSONParams.txt', "");

    //And let's declare a variable for the game name
    $gameName = "";

    //On with the good stuff!
    $s = connectToServerAndGetResponse(str_repeat(chr(255),4) . "getstatus\n", $lastRefreshTime);

    //This line removes the first four characters. They serve no purpose for us.
    $s = substr($s, 4);

    //This file is used for determining if the server connection was successful and regenerating dynamic content, plus it's good for debugging
    file_put_contents("info/" . dynamicIPAddressPath . "serverDump.txt", $s);

	if(strlen($s))
	{
	    //Server responded!
	    
	    //Mark the time in microseconds so we can see how long this takes.
	    $parseTimer = microtime(true);

	    //Now, we call a function to parse the data
	    $dataParserReturn = dataParser($s);

	    //Organize the data that came back in the array
		$cvar_array_single = $dataParserReturn[0];
		$cvars_hash = $dataParserReturn[1];
		$player_array = $dataParserReturn[2];
		$playerParseCount = $dataParserReturn[3];

		//Remove all colorization from the CVar hashmap, and save it to a new array
		$cvars_hash_decolorized = decolorizeArray($cvars_hash);

		//Now we need to parse any data that is unique to each individual game.
		//First, let's find the game name from the server's response.
		$gameName = parseGameName($cvars_hash, $cvars_hash_decolorized, $lastRefreshTime);

		//Insert game-specific function execution here
		$gameFunctionParserReturn = ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized);

	    //Remove the variables that were returned.
	    //We must assume that they were returned in the correct order!
	    $gametype = array_shift($gameFunctionParserReturn);
	    $levelshotFolder = array_shift($gameFunctionParserReturn);
	    $mapname = array_shift($gameFunctionParserReturn);
	    $modName = array_shift($gameFunctionParserReturn);
	    $sv_hostname = array_shift($gameFunctionParserReturn);
	    $sv_maxclients = array_shift($gameFunctionParserReturn);


		//Next, let's check and make sure the $levelshotFolder value we were given is accurate.
		$levelshotFolder = checkLevelshotDirectoriesAndConvertToLowercase($levelshotFolder);

	    //The rest is all BitFlag data.
	    $BitFlags = $gameFunctionParserReturn;

		$player_count = playerList($player_array, $playerParseCount);

        //Now, let's write the stuff we know to the individual text files for later use.
		file_put_contents('info/' . dynamicIPAddressPath . 'gametype.txt', $gametype);
	    file_put_contents("info/" . dynamicIPAddressPath . "gamename.txt", $gameName);
		file_put_contents('info/' . dynamicIPAddressPath . 'mapname.txt', colorize($mapname));
		//This next line is needed for Dynamic Paratracker to use levelshots correctly
		file_put_contents('info/' . dynamicIPAddressPath . 'mapname_raw.txt', $mapname);
		file_put_contents('info/' . dynamicIPAddressPath . 'modname.txt', colorize($modName));
		file_put_contents("info/" . dynamicIPAddressPath . "playerCount.txt", $player_count);
		file_put_contents('info/' . dynamicIPAddressPath . 'sv_hostname.txt', removeOffendingServerNameCharacters($sv_hostname));
		file_put_contents('info/' . dynamicIPAddressPath . 'sv_maxclients.txt', $sv_maxclients);


		//The following function detects how many levelshots exist on the server, and passes a buffer of information back, the final count of levelshots, and whether they fade or not
		$levelshotCount = levelshotfinder($mapname, $levelshotFolder);

		//This has to be last, because the timer will output on this page
		cvarList($gameName, $cvar_array_single, $parseTimer, $BitFlags);

	}
}

function decolorizeArray($cvars_hash)
{
    //This function removes all colorization from the input array.
    //It is used to make game detection and parsing more foolproof.

    $cvars_hash_decolorized = removeColorization($cvars_hash);

    return $cvars_hash_decolorized;
}

function removeColorization($input)
{
    //This function removes colorization from the input string.
    $input = str_replace('^0', '', $input);
    $input = str_replace('^1', '', $input);
    $input = str_replace('^2', '', $input);
    $input = str_replace('^3', '', $input);
    $input = str_replace('^4', '', $input);
    $input = str_replace('^5', '', $input);
    $input = str_replace('^6', '', $input);
    $input = str_replace('^7', '', $input);
    $input = str_replace('^8', '', $input);
    $input = str_replace('^9', '', $input);

    return $input;
}

function dataParser($s)
{
    $player_array = "";
    //Split the info first, then we'll loop through and remove any dangerous characters
        $sections = explode("\n", $s);
        array_pop($sections);
        $cvars_array = explode('\\', $sections[1]);

		//This block parses the CVars from each other
		$cvarCount = 0;
		for($i = 1; $i < count($cvars_array) - 1; $i += 2)
		{
			$cvar_name = str_replace(array("\n", "\r"), '', $cvars_array[$i]);
			$cvar_value = str_replace(array("\n", "\r"), '', $cvars_array[$i+1]);
			//As we put them into the new array, let's validate them as well
            $cvar_name = stringValidator($cvar_name, "", "");
            $cvar_value = stringValidator($cvar_value, "", "");
			$cvar_array_single[$cvarCount++] = array("name" => $cvar_name, "value" => $cvar_value);
			$cvars_hash[$cvar_name] = $cvar_value; 
		}
		//Now, let's alphabetize the CVars so the list is easier to read
		$cvar_array_single = array_sort($cvar_array_single, "name", false);


		//This loop parses the players from each other
		$playerParseCount = 0;

		for($i = 2; $i < count($sections); $i++)
		{
        		$player_split = explode(' ', $sections[$i], 3);
			//As we put them into the new array, let's validate them as well
			$player_array[$i] = array("score" => stringValidator($player_split[0], "", ""), "ping" => stringValidator($player_split[1], "", ""), "name" => stringValidator(trim($player_split[2],'"'), "", ""));
			$playerParseCount++;
		}
		return(array($cvar_array_single, $cvars_hash, $player_array, $playerParseCount));
}

function removeOffendingServerNameCharacters($input)
{

    //Check to see if the offending characters are to be removed
    if (filterOffendingServerNameSymbols == 1)
    {
        //The following line removes the Euro symbol, €
        $input = str_replace('€', '', $input);

        //The following line removes the newline symbol, 
        $input = str_replace('', '', $input);


    }
    return $input;
}

function parseGameName($cvars_hash, $cvars_hash_decolorized, $lastRefreshTime)
{
    //This function checks for variables specific to individual games, and sends them to the tracker.

    //Initialize this to null, so we can test against it later.
    $gameName = "";

    //Most games use the 'version' variable to identify which game is running. Try that first.
    $gameName = detectGameName($cvars_hash_decolorized["gamename"]);
    if($gameName == false)
    {
        //Some games, like Jedi Academy and Jedi Outcast, use a 'version' variable to identify the game. Try that next.
        //This can only be checked for AFTER the 'gamename' variable, because some games use both variables.
        $gameName = detectGameName($cvars_hash_decolorized["version"]);
        if($gameName == false)
        {
            //Tremulous uses 'com_gamename' to identify the game. Try that next.
	            $gameName = detectGameName($cvars_hash_decolorized["com_gamename"]);
	    }
    }

if($gameName == "")
{
    displayError("The game name could not be detected!", $lastRefreshTime);
}

return $gameName;
}

function ParseGameData($gameName, $cvars_hash, $cvars_hash_decolorized)
{
    //Initialize this
    $GameInfoData = "";

	//We'll need a new copy of game name to toy with for this part
	//Pull out invalid characters and make it lowercase
	$GameInfoGameName = preg_replace("/[^a-z0-9]/", "", strtolower($gameName));

	if(function_exists($GameInfoGameName) && is_callable($GameInfoGameName))
	{
	    //Call the function
	    $GameInfoData = $GameInfoGameName($cvars_hash, $cvars_hash_decolorized);
	}
	else
	{
	    if(!is_callable($GameInfoGameName))
	    {
	        echo " Could not load bit flag data for " . $gameName . " due to an invalid function name! This error is not fatal, but ParaTracker cannot parse gametypes or GameInfo. Contact the ParaTracker team with the game name, as this is a bug that must be fixed. ";
	    }
	    else
	    {
	        echo " Could not find bit flag data for " . $gameName . "! This error is not fatal, but ParaTracker cannot parse gametypes or GameInfo. ";
	    }
	}
	return $GameInfoData;
}

function cvarList($gameName, $cvar_array_single, $parseTimer, $BitFlags)
{
        //$buf2 and $buf3 are used for JSON stuff.
        $buf2 = '"info":{';
        $buf3 = '"parsedInfo":[';
        $firstExecution = "1";
        $firstBitFlag = "1";

        $buf = '
		</head>
		<body class="cvars_page">
		<span class="CVarHeading">Server Cvars</span><br />
		<span class="CVarServerAddress">' . serverIPAddress . ":" . serverPort . '</span><br /><span class="CVarServerPing">Ping: ' . file_get_contents("info/" . dynamicIPAddressPath . "serverPing.txt") . ' ms</span><br /><br />
		<table class="FullSizeCenter"><tr><td><table><tr class="cvars_titleRow cvars_titleRowSize"><td class="nameColumnWidth">Name</td><td class="valueColumnWidth">Value</td></tr>' . "\n";
		$c = 1;

		//See if there is any BitFlag data to parse.
		if(count($BitFlags) > 1)
		{
		    $BitFlagsIndex = array_shift($BitFlags);

		    //Parse the arrays into variables named after the CVars
		    for($i = 0; $i < count($BitFlagsIndex); $i++)
		    {
	            $$BitFlagsIndex[$i] = $BitFlags[$i];
		    }
		}
		else
		{
		    //There is no BitFlag data to parse, so we will just declare an empty array for the index.
		    $BitFlagsIndex = array("");
		}

		foreach($cvar_array_single as $cvar)
		{
			if($firstExecution == "0")
		    {
		        //If this not our first time going through the array, let's add a comma to the JSON output
		        $buf2 .= ',';
		    }
		    $firstExecution = "0";
		    $buf2 .= '"' . $cvar['name'] . '":"' . $cvar['value'] . '"';

			$buf .= '<tr class="cvars_row' . $c . '"><td class="nameColumnWidth">' . $cvar['name'] . '</td><td class="valueColumnWidth">';

			if ((($cvar['name'] == 'gamename') || ($cvar['name'] == 'mapname')) && ((strpos(colorize($cvar['value']), $cvar['value'])) == FALSE))
			{
				$buf .= '<b>' . colorize($cvar['value']) . "</b><br />" . $cvar['value'];
			}
			else if (($cvar['name'] == 'sv_hostname') || ($cvar['name'] == 'hostname'))
			{
			    //Need to check for offending symbols and remove them from server names, since it's obnoxious and everybody does it.
			    //There is no need to check if the filter is enabled or not, since the function handles that on it's own
			    $filteredName = removeOffendingServerNameCharacters($cvar['value']);

			    $buf .= '<b>' . colorize($filteredName) . "</b>";

				if ((strpos(colorize($cvar['value']), $filteredName)) == FALSE || $filteredName != $cvar['value'])
			    {
			        $buf .= "<br />" . $cvar['value'];
			    }
			}
			else
			{
			    //We need to check for the BitFlag variables here, and calculate them if there is a match
			    $foundMatch = 0;
			    for($i = 0; $i < count($BitFlagsIndex) && $foundMatch == 0; $i++)
			    {
			        $cvar['name'] = strtolower($cvar['name']);

			        if($cvar['name'] == strtolower($BitFlagsIndex[$i]))
			        {
			            $foundMatch = 1;
			            $returnArray = bitvalueCalculator($cvar['name'], $cvar['value'], $$BitFlagsIndex[$i]);
			            array_shift($returnArray);

			            $buf .= '<div class="CVarExpandList" onclick="bitValueClick(' . "'" . $cvar['name'] . "'" .  ')"><i><b>' . $cvar['value'] . '</b><br /><i class="expandCollapse">(Click to expand/collapse)</i><div id="' . $cvar['name'] .  '" class="collapsedList"><br />';

		                if($firstBitFlag == "0")
		                {
		                    $buf3 .= ',';
		                }
		                $firstBitFlag = "0";


			            $index = count($returnArray);

			            if ($index < 1 || $cvar['value'] == "0")
			            {
			                $buf .= '<i>None</i>';
			                $buf3 .= '{"name":"' . $cvar['name'] . '","flags":[""]}';
			            }
			            elseif ($cvar['value'] >= pow(2, count($$BitFlagsIndex[$i])))
			            {
			                //Miscount detected! Array does not have enough values
			                $buf .= "<br />Miscount detected! Not enough values in the array for " . $cvar['name'] . ". Check GameInfo.php and add the missing values!</i></div></div>";
			                $buf3 .= '{"name":"' . $cvar['name'] . '","Error: Miscount detected"}';
			            }
			            else
			            {
			                $buf .=  implode("<br />", $returnArray);
			                $buf .= '</i></div></div>';
			                $buf3 .= '{"name":"' . $cvar['name'] . '","flags":["' . implode('","', $returnArray) . '"]}';
			            }

			        }
			    }
			    if($foundMatch == 0)
			    {
			        $buf .= $cvar['value'];
			    }
			}
			$buf .= '</td></tr>' . "\n";
			$c++;
			if($c > 2) $c = 1;
		}
		$buf .= '</table></td></tr></table><h4 class="center">' . versionNumber() . ' - Server info parsed in ' . number_format(((microtime(true) - $parseTimer) * 1000), 3) . ' milliseconds.</h4><h5>Copyright &copy; 1837 Rick Astley. No rights reserved. Batteries not included. Void where prohibited.<br />Your mileage may vary. Please drink and drive responsibly.</h5><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></body></html>';
		$buf = htmlDeclarations("Server CVars", "") . $buf;

		if($firstBitFlag == "0")
		{
		    $buf3 .= ',';
		}
		$buf3 .= '{"servername":"' . file_get_contents("info/" . dynamicIPAddressPath . "sv_hostname.txt") . '","gamename":"' . file_get_contents('info/' . dynamicIPAddressPath . 'gamename.txt') .  '","gametype":"' . file_get_contents('info/' . dynamicIPAddressPath . 'gametype.txt') . '"}],';
		file_put_contents('info/' . dynamicIPAddressPath . 'param.txt', $buf);
		file_put_contents('info/' . dynamicIPAddressPath . 'JSONParams.txt', $buf3 . $buf2);
}

function playerList($player_array, $playerParseCount)
{

		$playerListbuffer = '';
		$player_count = 0;

		if($playerParseCount > 0)
		{
		    //Sort by ping first, to move bots to the bottom. Higher pings go on top.
			$player_array = array_sort($player_array, "ping", true);
			//Now, sort by score. If a bot has a higher score than a player, they will be on top. But at least real players are more visible this way
			$player_array = array_sort($player_array, "score", true);

			$c = 1;
			foreach($player_array as &$player)
			{
				$player_name = str_replace(array("\n", "\r"), '', $player["name"]);
				$player_count++;
				$playerListbuffer .= "\n" . '
<div class="playerRow' . $c . '"><div class="playerName playerNameSize">'. colorize($player_name);
				$playerListbuffer .= '</div><div class="playerScore playerScoreSize">' . $player["score"] . '</div><div class="playerPing playerPingSize">' . $player["ping"] . '</div></div>';
				$c++;
				if($c > 2) $c = 1;
			}
			$playerListbuffer .= "\n";
		}
		else
		{
			$playerListbuffer .= '<div class="noPlayersOnline">&nbsp;' . noPlayersOnlineMessage . '</div>';
		}
		$playerListbuffer .= '<div></div>';
		$buf3='';
		file_put_contents('info/' . dynamicIPAddressPath . 'playerList.txt', $playerListbuffer);

		return $player_count;
}

function levelshotFinder($mapName, $levelshotFolder)
{
		$levelshotBuffer = '';

		$levelshotCount = 0;
		$levelshotIndex = 1;
	    $foundLevelshot = 0;
		do
		{

		    //Reset this value every iteration so we can check to see if levelshots are being found
		    $foundLevelshot = 0;

		    //Check for a PNG first
		    if(file_exists($levelshotFolder . $mapName . '_' . $levelshotIndex . '.png'))
		    {
		        $levelshotBuffer .= $levelshotFolder . $mapName . '_' . $levelshotIndex . '.png';
        		$foundLevelshot = 1;
		    }
		    else
		    {
		    //Failed to find a PNG, so let's check for a JPG
		        if(file_exists($levelshotFolder . $mapName . '_' . $levelshotIndex . '.jpg'))
		        {
		            $levelshotBuffer .= $levelshotFolder . $mapName . '_' . $levelshotIndex . '.jpg';
		            $foundLevelshot = 1;
		        }
		        else
		        {
		            //Also failed to find a JPG, so let's check for a GIF
		            if(file_exists($levelshotFolder . $mapName . '_' . $levelshotIndex . '.gif'))
		            {
		                $levelshotBuffer .= $levelshotFolder . $mapName . '_' . $levelshotIndex . '.gif';
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
		                	if(file_exists($levelshotFolder . $mapName . $levelshotIndex . '.png'))
		            		{
		                        $levelshotBuffer .= $levelshotFolder . $mapName . '.png';
        				    }
        				    else
        				    {
        				        //And checking for a JPG again:
		                	    if(file_exists($levelshotFolder . $mapName . $levelshotIndex . '.jpg'))
		                	    {
		                	        $levelshotBuffer .= $levelshotFolder . $mapName . '.jpg';
		                	    }
		                	    else
		                	    {
		                	        //Lastly...checking for a GIF.
		                	        if(file_exists($levelshotFolder . $mapName . $levelshotIndex . '.gif'))
		                	        {
		                                $levelshotBuffer .= $levelshotFolder . $mapName . '.gif';
		                	        }
		                	        else
		                	        {
		                	            //Could not find a levelshot! Use the default 'missing picture' image and close out
		                	            $levelshotBuffer .= "images/missing.gif";
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

		} While ($foundLevelshot == 1 && $levelshotCount < maximumLevelshots && levelshotTransitionsEnabled == 1);

//This code prevents the Javascript that follows from seeing a value of 0 levelshots when none are found.
//There will always be a minimum of one levelshot. A placeholder is used if none is found.
if ($levelshotCount == 0)
{
    $levelshotCount = 1;
}

file_put_contents('info/' . dynamicIPAddressPath . 'levelshots.txt', trim($levelshotBuffer));

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
file_put_contents("info/" . dynamicIPAddressPath . "refreshCode.txt", $output);
}

function levelshotJavascriptAndCSS()
{
    $output = '<script type="text/javascript">';

    $output .= '
    var timer = 0;  //Used for setting re-execution timeout
    var allowTransitions = ' . levelshotTransitionsEnabled . ';   //Used to test whether fading levelshots is disabled.
    var opac = 1;   //Opacity level for the top layer.
    var shot = 1;   //Levelshot number.
    var originalStyleData = "";   //Used to contain the original CSS info while fading.
    var mode = 1;   //0 means we are delaying between fades. 1 means a fade is in progress.
    var levelshotTransitionTime = ' . levelshotTransitionTime . ';      //This value takes the transition time value given in ParaConfig and passes it to the Javascript.
    var levelshotDisplayTime = ' . levelshotDisplayTime . ';    //This value takes the display time given in ParaConfig and passes it to the Javascript.
    var levelshotTransitionAnimation = ' . levelshotTransitionAnimation . ';    //This value specifies the I.D. of the levelshot transition to use. The value is 0 if transitions are to be random
    var animationList = []; //This initializes an array to be used for detecting the number of levelshot transitions

    var levelshots = "' . file_get_contents('info/' . dynamicIPAddressPath . 'levelshots.txt') . '";

    levelshots = levelshots.split(":#:");
    var maxLevelshots = levelshots.length;

    </script>';

return $output;
}


function passConfigValuesToJavascript()
{
		$output = '<script type="text/javascript">
		var RConEnable = "' . RConEnable . '";
		var newWindowSnapToCorner = "' . newWindowSnapToCorner . '";
		var serverIPAddress = "' . serverIPAddress . '";
		var serverPort = "' . serverPort . '";
		var paraTrackerSkin = "' . paraTrackerSkin . '";
		</script>';

    return($output);
}

function checkTimeDelay()
{
$lastRefreshTime = numericValidator(file_get_contents("info/" . dynamicIPAddressPath . "time.txt"), "", "", "wait");

$i = 0;
$sleepTimer = "0.15"; //This variable sets the number of seconds PHP will wait before checking to see if anything has changed.
$checkWaitValue = file_get_contents("info/" . dynamicIPAddressPath . "time.txt");  //This variable is used to check if the wait value changes below
$fileInput = $checkWaitValue;

while ($lastRefreshTime == "wait" && $i < (connectionTimeout + refreshTimeout))
{
    //info/time.txt indicated that a refresh is in progress. Wait a little bit so it can finish. If it goes too long, we'll continue on, and force a refresh.
    usleep($sleepTimer * 1000000);
    $fileInput = file_get_contents("info/" . dynamicIPAddressPath . "time.txt");
    if($checkWaitValue != $fileInput && stripos($fileInput, "wait" !== false))
    {
        //Another client has started a refresh! Let's start our wait period over so we don't DoS the game server by accident.
        $checkWaitValue = file_get_contents("info/" . dynamicIPAddressPath . "time.txt");
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
    $input = trim($input);

    //The config file allows for a value of 1 or the string "yes" to be used for booleans.
    //Everything else must evaluate to false.

    if ($input == "1" || strtolower($input) == "yes")
        {
            $input = 1;
        }
        else
        {
            if($input == "" && $defaultValue == "1")
            {
                //Not $input = $defaultValue - Same as above
                $input = 1;
            }
            {
                //Not $input = $defaultValue - Same as above
                $input = 0;
            }
        }
    return $input;
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

function stringValidator($input, $maxLength, $defaultValue)
{
    //Is the string null? If not, continue.
    if ($input != "")
    {
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

function ipAddressValidator($input, $serverPort)
{
    //Remove whitespace
    $input = trim($input);

    //Check to see if an address was supplied
    if($input == "")
    {
        //No address. Are we running in dynamic mode?
        if(dynamicTrackerEnabled == "0")
        {
            //We are in static mode, so ParaConfic.php is the problem
            displayError("No server address specified in ParaConfig.php!", $lastRefreshTime);
        }
        else
        {
            //We are in Dynamic mode, so the user did not give an address
            displayError('Invalid IP address! ' . stringValidator($input) . '<br />Please specify an IP Address.', $lastRefreshTime);
        }
    }

    //Use a PHP function to check validity
    if (!filter_var($input, FILTER_VALIDATE_IP) && $input != "localhost")
    {
        //getbyhostname returns the input string on failure. So, to test if this is a failure, we test it against itself
        if(gethostbyname($input) == $input)
        {
            //DNS test failed. Just error out.
            displayError('Invalid domain name! ' . stringValidator($input, "", "") . '<br />Check the address and try again.</h3>', $lastRefreshTime);
        }
        else
        {
            if(gethostbyname($input) == $input)
            {
                If(dynamicTrackerEnabled == "1")
                {
                    displayError('Invalid IP address! ' . stringValidator($input, "", "") . '<br />Check the IP address and try again.</h3>', $lastRefreshTime);
                }
                else
                {
                    displayError('Invalid IP address! ' . stringValidator($input, "", "") . '<br />Check the IP address in ParaConfig.php</h3>', $lastRefreshTime);
                }
            }
        }
    }

return $input;
}

function skinValidator($paraTrackerSkin)
{
    //Remove slashes, periods, and colons. This will prevent anyone from adding a file extension, a URL, or a ../ into the file name
    if(strpos($paraTrackerSkin, ".") !== false ||strpos($paraTrackerSkin, "/") !== false ||strpos($paraTrackerSkin, "\\") !== false ||strpos($paraTrackerSkin, ":") !== false)
    {
        echo " Invalid skin specified! Slashes, colons, and periods are forbidden in skin file names. Assuming default skin. ";
        $paraTrackerSkin = defaultSkin;
    }

    $paraTrackerSkin = stringValidator($paraTrackerSkin, "", defaultSkin);

    if(!file_exists("skins/" . $paraTrackerSkin . ".css") && strtolower($paraTrackerSkin) != "json")
    {
        $paraTrackerSkin = defaultSkin;
        echo " Invalid skin specified! Skin names must have a lowercase file extension, cannot have slashes ( '\' or '/' ) and must refer to an actual CSS file. Assuming default skin. ";

        if(!file_exists("skins/" . defaultSkin . ".css"))
        {
            displayError("Invalid skin specified!<br />Default skin could not be found!", $lastRefreshTime);
        }
        else
        {
        //Non-fatal error; revert to default skin and give a debug message.
        $paraTrackerSkin = defaultSkin;
        echo " Invalid skin specified! Assuming default skin. ";
        }
    }
    return $paraTrackerSkin;
}

function displayError($errorMessage, $lastRefreshTime)
{
    if(trim($errorMessage) == "")
    {
        $errorMessage = "An unknown error has occurred!<br />ParaTracker must terminate.";
    }
    $errorMessage = '<!-- --><h3 class="errorMessage">' . $errorMessage . '</h3>';

    //Error detected and ParaTracker is terminating. Check to see if we have a file path and refresh time data.
    if(dynamicIPAddressPath != "" && $lastRefreshTime != "")
    {
        //We have a file path! Write the error message to a file, update both of the refresh timers, and terminate!
        file_put_contents("info/" . dynamicIPAddressPath . "errorMessage.txt", $errorMessage);
        file_put_contents("info/" . dynamicIPAddressPath . "time.txt", time());
        file_put_contents("info/" . dynamicIPAddressPath . "RConTime.txt", time());
    }
    //If no file path was given, flood protection will not be necessary, as ParaTracker never had a chance to contact the server.
    //so it is safe to terminate regardless of whether there was a file path or not.
    echo $errorMessage;
    exit();
}

function bitvalueCalculator($cvarName, $cvarValue, $arrayList)
{
            $iBlewItUp = "";
            $toBeExploded = "";
             //$output = '<div class="CVarExpandList" onclick="bitValueClick(' . "'" . $cvarName . "'" .  ')"><b>' . $cvarValue . '</b><br /><i class="expandCollapse">(Click to expand/collapse)</i>';

                $index = count($arrayList);
                //Sort through the bits in the value given, and for every 1, output the matching array value
                for ($i = 0; $i < $index; $i++)
                {
                    if ($cvarValue & (1 << $i))
                    {
                        if($arrayList[$i] != "")
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
    $output = levelshotJavascriptAndCSS();
    $output .= '<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/ParaStyle.css" type="text/css" />
    <link rel="stylesheet" href="css/LevelshotAnimations.css" type="text/css" />';

    //If a skin is defined, then include it here in the header
    if(paraTrackerSkin != "")
    {
        $output .= '<link rel="stylesheet" href="skins/' . paraTrackerSkin . '.css" type="text/css" />';
    }

    $output .= '<title>' . $pageTitle . '</title>
    <script src="' . $filePath . 'js/ParaScript.js"></script>';

    $output .= passConfigValuesToJavascript();

    return $output;
}

function colorize($string)
{
    //First, we need to wrap color 7 around the text to be colorized
    $colorized_string = '<span class="color7">' . $string . "</span>";

    $colorized_string = str_replace('^0', '</span><span class="color0">', $colorized_string);
    $colorized_string = str_replace('^1', '</span><span class="color1">', $colorized_string);
    $colorized_string = str_replace('^2', '</span><span class="color2">', $colorized_string);
    $colorized_string = str_replace('^3', '</span><span class="color3">', $colorized_string);
    $colorized_string = str_replace('^4', '</span><span class="color4">', $colorized_string);
    $colorized_string = str_replace('^5', '</span><span class="color5">', $colorized_string);
    $colorized_string = str_replace('^6', '</span><span class="color6">', $colorized_string);
    $colorized_string = str_replace('^7', '</span><span class="color7">', $colorized_string);
    $colorized_string = str_replace('^8', '</span><span class="color8">', $colorized_string);
    $colorized_string = str_replace('^9', '</span><span class="color9">', $colorized_string);

    return $colorized_string;
}

function dynamicInstructionsPage($personalDynamicTrackerMessage)
{
    $urlWithoutParameters = explode('?', $_SERVER["REQUEST_URI"], 2);
    $currentURL = $_SERVER['HTTP_HOST'] . $urlWithoutParameters[0];

    $output = htmlDeclarations("", "");
    
    $output .= '<meta name="description" content="Free game server tracker for Quake 3 based games">
  <meta name="keywords" content="Free, Quake 3, Jedi Academy, Jedi Outcast, Server Tracker, PHP, JediTracker">
  <meta name="author" content="Parabolic Minds">

    </head><body class="dynamicConfigPage dynamicConfigPageStyle">
';

    $output .= '<div class="dynamicPageContainer"><div class="dynamicPageWidth"><h1>' . versionNumber() . ' - Dynamic Mode</h1>
    <i>' . $personalDynamicTrackerMessage . '</i>
    <h6>ParaConfig.php settings still apply, so for instance, if you want to enable RCon, or change levelshot options, it must be changed in ParaConfig.php, by the server owner.<br />For a full set of options, you can (and should) host your own ParaTracker.</h6>';

$output .= '<h3>Supported Games:</h3><div class="centerTable"><table class="centerTable"><tr>';

    $gameList = detectGameName("");

    $colorNumber = 0;

    //Loop through the array of stuff listed
    for($i = 0; $i < count($gameList); $i++)
    {
        //DO NOT make the directory list value lowercase here! It will make all dynamic game names appear in lowercase on the tracker
        $output .= '<td class="gameColor' . $colorNumber . '">&nbsp;&nbsp;' . $gameList[$i] . '&nbsp;&nbsp;</td>';

        $colorNumber++;
        if($colorNumber > 9)
        {
        $colorNumber = 0;
        }

        //Was this an even trip through the array? If so, start a new table row.
        if($i % 2 != 0)
        {
            $output .= "</tr><tr>";
        }
    }

        if($i % 2 == 0)
        {
            $output .= "</td><td>";
        }
$output .= "</td></tr></table></div>";

$output .= '<br /><p>Current page URL:<br /><input type="text" size="80" id="currentURL" value="' . $currentURL . '" readonly /></p>
    <br />
    <h3>Enter the appropriate data below to get a URL you can use for ParaTracker Dynamic:</h3>

    <form>

    <p>Server IP Address: <input type="text" size="46" oninput="clearOutputFields()" id="IPAddress" value="" /></p>
    <p>Server port number: <input type="text" size="15" oninput="clearOutputFields()" id="PortNumber" value="" /></p>';


    //Let's dynamically find the CSS files, and parse the resolution of the tracker from them
    $output .= '<p>Skin:<br />';
    $directoryList = scandir("skins/");

    //Sort the array in a readable fashion
    usort($directoryList, 'strnatcasecmp');

    $skinList = array("");
    $skinCount = 0;

    //Loop through the array of stuff listed, and see if there's anything that matches the given game name
    for($i = 2; $i < count($directoryList); $i++)
    {
        //Ignore Template.css, json.css (which cannot exist), and make sure the file extension on the detected file is ".css"
        if(strtolower($directoryList[$i]) != "template.css" && strtolower($directoryList[$i]) != "json.css" && substr(strtolower($directoryList[$i]), -4) == ".css")
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

        $skinFile = file_get_contents("skins/" . $skinList[$i] . ".css");

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
        $output .= '</option><br />';
    }

    $output .= '<option value="JSON:#:800:#:800">JSON (Text-only response for clientside Javascript parsing)</option>';
    $output .= '</select><br />';

    $output .= '<p><button type="button" class="dynamicFormButtons dynamicFormButtonsStyle" onclick="createURL()"> Generate! </button></p>
    <p>Direct link:<br /><textarea rows="3" cols="120" id="finalURL" readonly></textarea></p>
    <p>HTML code to insert on a web page:<br /><textarea rows="5" cols="120" id="finalURLHTML" readonly></textarea></p>

    </form>

    <div id="paraTrackerTestFrame" class="collapsedFrame" ><h2>Sample Tracker:</h2>
    <div id="paraTrackerTestFrameContent" class="paraTrackerTestFrame" ></div></div>

    <h6>Trademark&#8482; Pen Pineapple Apple Pen, no rights deserved. The use of this product will not cavse any damnification to your vehicle.</h6>
    <h6><p>
WE COMPLY WITH ALL LAWS AND REGULATIONS REGARDING THE USE OF LAWS AND REGULATIONS. WE PROMISE THAT THIS THING IS A THING. THIS THING COLLECTS INFORMATION. THIS INFORMATION IS THEN USED TO MAKE MISINFORMATION. THIS MISINFORMATION IS THEN SOLD TO THE MOST NONEXISTENT BIDDER. BY READING THIS, YOU AGREE. CLICK NEXT TO CONTINUE. OTHERWISE, CONTINUE ANYWAY AND SEE IF WE CARE. WOULD YOU LIKE TO SET PARATRACKER AS YOUR HOME PAGE? TOO BAD, WE DID IT ALREADY. WE ALSO INSTALLED A BROWSER TOOLBAR WITHOUT ASKING, BECAUSE TOOLBARS ARE COOL AND SO ARE WE.
</p>
</h6>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />Nope, nothing here. Promise.
    </div></div></body>
    </html>';

    echo '-->' . $output;    

    exit();
}

function connectToServerAndGetResponse($messageToSend, $lastRefreshTime)
{
	$s='';

	//Empty the server's previous ping from the file
	file_put_contents("info/" . dynamicIPAddressPath . "serverPing.txt", "");

	//Set this value to measure the server's ping
	$serverPing = microtime(true);
    $fp = fsockopen("udp://" . serverIPAddress, serverPort, $errno, $errstr, 30);
	if(!fwrite($fp, $messageToSend))
	{
	    $errstr = "Could not open the connection to the game server!\nMake sure your web host allows outgoing connections.";
	}
	else
	{
	    stream_set_timeout($fp, connectionTimeout);
        $s = fread($fp, maximumServerInfoSize);
	    fclose($fp);
	}

	file_put_contents("info/" . dynamicIPAddressPath . "serverPing.txt", number_format(((microtime(true) - $serverPing) * 1000), 0));

    if(strlen($s) >= maximumServerInfoSize)
    {
     displayError('Received maximum data allowance!<br />' . strlen($s) . ' bytes received, the limit is ' . maximumServerInfoSize . '<br />Check to see if you are connected to the correct server or increase $maximumServerInfoSize in ParaConfig.php.', $lastRefreshTime);
    }

	if($errstr == "" && $s == "")
	{
	    $errstr = "No response in " . connectionTimeout . " seconds.";
	}
	file_put_contents("info/" . dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));

	//Convert encoding from ANSI to ASCII. If this fails due to illegal characters, leave it as-is.
	$s = convertFromANSI($s);

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

function sendRecieveRConCommand($lastRefreshTime, $RConPassword, $RConCommand)
{
$serverResponse = "";
$output = "";
$s = "";
$RConLog = "";
$RConLog2 = "";

if ($RConPassword != "" && $RConCommand != "")
	{
		$output .= '';

		$s = connectToServerAndGetResponse(str_repeat(chr(255),4) . 'RCon ' . $RConPassword . ' ' . $RConCommand, $lastRefreshTime);

		if($s != "")
		{
		    $serverResponse = $s;
		    //Check for exploits in the response that might trigger some PHP code
		    $serverResponse = RConRemoveExploits($serverResponse);

			//Replace line breaks for the RCon log only
			$newRConLogEntry = str_replace(chr(0x0A), '\n', $serverResponse);

		    //Validate the rest!
		    $serverResponse = stringValidator($serverResponse, "", "");

		    //Now we format the remaining data in a readable fashion
			$serverResponse = str_replace('ÿÿÿÿprint', '', $serverResponse);
			$serverResponse = str_replace(chr(0x0A), '<br />', trim($serverResponse));
			//This next line apparently replaces spaces with....spaces? Not sure who added that but I'm commenting it out
			//$serverResponse = str_replace(chr(0x20), ' ', $serverResponse);

		}
		else
		{
		    $serverResponse = 'No response from server at ' . serverIPAddress . ':' . serverPort . '!';
		    $newRConLogEntry = $serverResponse;
		}

		$output .= $serverResponse;
	}


	//Log time!
    $RConLog2 = file_get_contents("logs/" . dynamicIPAddressPath . "RConLog.php");

    //Trim off the PHP tags and comment markers at the beginning and end of the file
    $RConLog2 = substr($RConLog2, 183, count($RConLog2) - 8);

    //If there are too many lines, truncate them
    $RConLogArray = explode("\n", $RConLog2);
    $RConLogArray = array_slice($RConLogArray, 0, RConLogSize);

    $RConLog2 = implode("\n", $RConLogArray);

    //Remove line breaks to prevent people from screwing up the line count in the log.
    //We will remove exploits from the command below, after the full command is assembled.
    $RConCommand = str_replace("\n", '\n', $RConCommand);

    //Assemble the new log entry.
    $RConLog = date(DATE_RFC2822) . "  Client IP Address: " . $_SERVER['REMOTE_ADDR'] . "  Command: " . $RConCommand . "  Response: " . $newRConLogEntry . $RConLog2;

    //Check for exploits before writing the new entry to the log. The command hasn't been validated, so this *must* happen a second time.
	$RConLog = RConRemoveExploits($RConLog);

    //Assemble the new log entry. This is the log, so validating anything other than what was already validated is a bad idea.
    $RConLog = RConLogHeader() . "\n" . $RConLog . RConLogFooter();

    //Write the newly appended RCon log to a file
    file_put_contents("logs/" . dynamicIPAddressPath . "RConLog.php", $RConLog);

    return $output;
}

function renderNormalHTMLPage($gameName)
{

$output = htmlDeclarations("ParaTracker - The Ultimate Quake III Server Tracker", "");
$output .= '</head>';

//This adds the default formatting to the page. It removes the padding and margins, sets the size, and hides any overflow.
$output .= '<body class="ParaTrackerPage">';

//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
$output .= '<div class="ParaTrackerSize">';

//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
$output .= '<div class="BackgroundColorImage">';

//This adds the ParaTracker logo to the page.
$output .= '<div class="ParaTrackerLogo"></div>';

//This adds three custom DIVs to the page, to be used however the skin creator desires.
$output .= '<div class="CustomDiv1"></div>';
$output .= '<div class="CustomDiv2"></div>';
$output .= '<div class="CustomDiv3"></div>';

//This adds the ParaTracker text to the page.
$output .= '<div class="ParaTrackerText">' . versionNumber() . '</div>';

//This adds the server name to the page.
$output .= '<div class="serverName">' . colorize(file_get_contents("info/" . dynamicIPAddressPath . "sv_hostname.txt")) . '</div>';

//This adds the game name to the page.
$output .= '<div class="gameTitle">' . file_get_contents("info/" . dynamicIPAddressPath . "gamename.txt") . '</div>';

//This adds the column headers for name, score and ping to the page.
$output .= '<div class="nameScorePing"><div class="playerNameSize playerNameHeader"></div><div class="playerScoreSize playerScoreHeader"></div><div class="playerPingSize playerPingHeader"></div></div>';

//This adds the player list table to the page.
$output .= '<div class="playerTable">' . file_get_contents("info/" . dynamicIPAddressPath . "playerList.txt") . '</div>';

//This adds the player count to the page.
$output .= '<div class="playerCount">' . file_get_contents("info/" . dynamicIPAddressPath . "playerCount.txt") . '/' . file_get_contents("info/" . dynamicIPAddressPath . "sv_maxclients.txt") . '</div>';

//This adds the map name to the page.
$output .= '<div class="mapName">' . file_get_contents("info/" . dynamicIPAddressPath . "mapname.txt") . '</div>';

//This adds the mod name to the page.
$output .= '<div class="modName">' . file_get_contents("info/" . dynamicIPAddressPath . "modname.txt") . '</div>';

//This adds the gametype to the page.
$output .= '<div class="gametype">' . file_get_contents("info/" . dynamicIPAddressPath . "gametype.txt") . '</div>';

//This adds the IP Address and blinking cursor to the page.
$output .= '<div class="IPAddress">' . serverIPAddress . ':' . serverPort . '<div class="blinkingCursor"></div></div>';

//This adds the ping of the game server's response to the web server.
$output .= '<div class="ServerPing">' . file_get_contents("info/" . dynamicIPAddressPath . "serverPing.txt") . '</div>';

//This adds the levelshots to the page.
$output .= '<div id="levelshotPreload2" class="levelshotFrame levelshotSize" onclick="levelshotClick()">
<div id="levelshotPreload1" class="levelshotSize"></div>
<div id="bottomLayerFade" class="levelshotSize"></div>
<div id="topLayerFade" class="levelshotSize"></div>
</div>';

if(enableGeoIP == 1)
{
    //This adds the optional country flag to the page. This feature only works when GeoIP is installed.
    $output .= '<div class="countryFlag">' . countryFlag . '</div>';
}

//If RCon is enabled, this adds the RCon button and the RCon image preloader to the page.
if (RConEnable == 1)
{
    $output .= '<div onclick="rcon_window();"><div class="rconButton"></div></div>';
    $output .= '<div class="rconPreload"></div>';
}

//This adds the Param button and Param image preloader to the page.
$output .= '<div onclick="param_window();"><div class="paramButton"></div></div>';
$output .= '<div class="paramPreload"></div>';

//This adds the frame to the page.
$output .= '<div class="TrackerFrame"></div>';

if(enableAutoRefresh == "1")
{
    //This adds the refresh timer and the timer script to the page.
    $output .= '<div onclick="toggleReload()"><div class="reloadTimer" title="Click to cancel auto-refresh" id="refreshTimerDiv"></div></div>' . file_get_contents("info/" . dynamicIPAddressPath . "refreshCode.txt");
}

$output .= '</div></div>
<script type="text/javascript">
startTimer = setTimeout(firstExecution(), 500);
</script>
</body>
</html>';

return $output;
}

function renderNoConnectionHTMLPage()
{
$output =  htmlDeclarations("ParaTracker - Could Not Connect To Server", "");
$output .= '<script type="text/javascript">
reconnectTimer = setTimeout("makeReconnectButtonVisible()", ' . (floodProtectTimeout * 1000 + 100) . ');
</script>';

$output .= '</head>';

//This adds the default formatting to the page. It removes the padding and margins, sets the size, and hides any overflow.
$output .= '<body class="ParaTrackerPage">';

//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
$output .= '<div class="ParaTrackerSize">';

//This adds the background color and image class to the page. It is an optional CSS feature that is only there for ease of use.
$output .= '<div class="BackgroundColorImage">';

//This adds the ParaTracker logo to the page.
$output .= '<div class="ParaTrackerLogo"></div>';

//This adds three custom DIVs to the page, to be used however the skin creator desires.
$output .= '<div class="CustomDiv1"></div>';
$output .= '<div class="CustomDiv2"></div>';
$output .= '<div class="CustomDiv3"></div>';

//This adds the ParaTracker text to the page.
$output .= '<div class="ParaTrackerTextNoConnection">' . versionNumber() . '</div>';

//This adds the ParaTracker logo to the page.
$output .= '<div class="paraTrackerError"><br /><br />' . file_get_contents("info/" . dynamicIPAddressPath . "connectionErrorMessage.txt") . '</div><div class="paraTrackerErrorAddress"><br /><br />' . serverIPAddress . ':' . serverPort . '<div class="noConnectionblinkingCursor">&nbsp;</div></div>
</div>';

//This adds the frame to the page.
$output .= '<div class="TrackerFrame"></div>';

if(enableAutoRefresh == "1")
{
    //This adds the refresh timer and the timer script to the page.
    $output .= '<div onclick="toggleReload()"><div class="reloadTimerNoConnection" title="Click to cancel auto-refresh" id="refreshTimerDiv"></div></div>' . file_get_contents("info/" . dynamicIPAddressPath . "refreshCode.txt");
}

//This adds the reconnect button to the page
$output .= '<span onclick="pageReload();"><div id="reconnectButton" class="reconnectButton hide"></div></span>';

//This adds the image preloader for the reconnect button to the page.
$output .= '<div class="reconnectPreload"></div>';

$output .= '</body></html>';

return $output;
}

function renderJSONPage()
{
    header("Content-Type: application/json");

    //Add the version number to the output
    $output = '{"version":"' . versionNumber() . '",';

    //Add the relevant config data and the server ping to the output
    $output .= '"serverIPAddress":"' . serverIPAddress . '",';
    $output .= '"serverPort":"' . serverPort . '",';
    $output .= '"serverPing":"' . file_get_contents("info/" . dynamicIPAddressPath . "serverPing.txt") . '",';
    $output .= '"paraTrackerSkin":"' . paraTrackerSkin . '",';
    $output .= '"levelshotTransitionsEnabled":' . convertBooleansToString(levelshotTransitionsEnabled) . ',';
    $output .= '"levelshotDisplayTime":"' . levelshotDisplayTime . '",';
    $output .= '"levelshotTransitionTime":"' . levelshotTransitionTime . '",';
    $output .= '"levelshotTransitionAnimation":"' . levelshotTransitionAnimation . '",';
    $output .= '"noPlayersOnlineMessage":"' . noPlayersOnlineMessage . '",';
    $output .= '"enableAutoRefresh":' . convertBooleansToString(enableAutoRefresh) . ',';
    $output .= '"autoRefreshTimer":"' . autoRefreshTimer . '",';
    $output .= '"RConEnable":' . convertBooleansToString(RConEnable) . ',';
    $output .= '"RConFloodProtect":"' . RConFloodProtect . '",';
    $output .= '"newWindowSnapToCorner":' . convertBooleansToString(newWindowSnapToCorner) . ',';

    //Time for levelshot stuff. Get the array from the file and explode it
    $levelshots = file_get_contents("info/" . dynamicIPAddressPath . "levelshots.txt");

    $levelshots = explode(":#:", $levelshots);

    //Declare the levelshot array name
    $output .= '"levelshotsArray":[';
    for($i = 0; $i < count($levelshots); $i++)
    {
        if($i != 0)
        {
            $output .= ",";
        }
        $output .= '"' . $levelshots[$i] . '"';
    }
    //End the levelshot array
    $output .= '],';

    //Now let's include the bitflags (They are already parsed correctly in a text file)
    $output .= file_get_contents("info/" . dynamicIPAddressPath . "JSONParams.txt");

    //Now, we call a function to parse the server response, then put it in JSON format
    $dump = file_get_contents("info/" . dynamicIPAddressPath . "serverDump.txt");
    $dataParserReturn = dataParser($dump);

    //Organize the data that came back in the array
    //$server_info = $dataParserReturn[0];  //We do not need this for JSON
    //$cvars_hash = $dataParserReturn[1];   //We do not need this for JSON
    $player_array = $dataParserReturn[2];
    $playerParseCount = $dataParserReturn[3];

    $output .= '},"players":[';
    for ($i = 2; $i < count($player_array); $i ++)
    {
        //$player_split = explode(' ', $player_array[$i], 3);
        if ($i != 2)
        {
            $output .= ",";
        }
    $output .= '{"name":"' . trim($player_array[$i]["name"]) . '",';
    $output .= '"score":"' . $player_array[$i]["score"] . '",';
    $output .= '"ping":"' . $player_array[$i]["ping"] . '"}';
    }
$output .= "]}";

ob_end_clean();

echo $output;

exit();

}

function convertBooleansToString($input)
{
    if($input == "0")
    {
        return "false";
    }
    else
    {
        return "true";
    }
}

function RConRemoveExploits($input)
{
    $input = str_replace("<?", ' EXPLOIT REMOVED (LessThan, QuestionMark) ', $input);
    $input = str_replace("?>", ' EXPLOIT REMOVED (QuestionMark, GreaterThan) ', $input);
    $input = str_replace("*/", ' EXPLOIT REMOVED (Asterisk, ForwardSlash) ', $input);
    return $input;
}

function RConLogHeader()
{
    $output = "<?php \n echo '<h3 class=" . '"errorMessage"' . ">RConLog.php can only be viewed with direct file system access.<br />Download it and open it in a text editor.</h3>';\n exit(); \n/*  LOG ENTRIES:\n";
    return $output;
}

function RConLogFooter()
{
    return "\n*/ ?> ";
}

function writeNewConfigFile()
{
$configBuffer = '<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the configuration file for ParaTracker.
// If you want to edit fonts and colors, they are found
// in the css files found in the /skins folder.
// You can change the skin used in static mode here, but there are
// no other visual settings.

// ONLY modify the variables defined below, between the double quotes!
// Changing anything else can break the tracker!

// If this file ever breaks and you have no idea what is wrong, just delete it.
// When ParaTracker is run, it will write a new one for you.

// If you find any exploits in the code, please bring them to my attention immediately!
// Thank you and enjoy!


// NETWORK SETTINGS
// NETWORK SETTINGS

// This is the IP Address of the server. Do not include the port number!
// By default, and for security, this value is empty. If ParaTracker is launched without a value here,
// it will display a message telling the user to check config.php before running.
$serverIPAddress = "";


// Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
// The default port number for Jedi Outcast is 28070.
// If an invalid entry is given, this value will default to 29070.
$serverPort = "";

// This variable limits how many seconds are required between each snapshot of the server.
// This prevents high traffic on the tracker from bogging down the game server it is tracking.
// ParaTracker forces a minimum value of 5 seconds between snapshots. Maximum is 1200 seconds.
// This value cannot be lower than the value of $connectionTimeout (below).
// Default is 15 seconds.
$floodProtectTimeout = "15";

// This value is the number of seconds ParaTracker will wait for a response from the game server
// before timing out. Note that, every time the tracker gets data from the server, it will ALWAYS
// wait the full delay time. Server connections are UDP, so the tracker cannot tell when the data
// stream is complete. After this time elapses, ParaTracker will assume it has all the data and
// parse it. If your web server has a slow response time to the game server, set this value
// higher. ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
// Not recommended to go above 5 seconds, as people will get impatient and leave.
// This setting also affects RCon wait times.
// Default is 2.5 seconds.
$connectionTimeout = "2.5";

// This value, given in seconds, determines how long ParaTracker will wait for a current refresh of
// the server info to complete, before giving up and forcing another one. Raise this value if your
// web server is busy or slow to reduce the load on the game server.
// Minimum is 1 second, maximum is 15 seconds.
// Default is 3 seconds.
$refreshTimeout = "3";


// VISUAL SETTINGS
// VISUAL SETTINGS

// This line specifies which skin file to load. Skins are found in the skins/ folder, and they are all
// simple CSS files. The name is case sensitive.
// ParaTracker will automatically search in the skins/ folder for the file specified, and it will automatically
// add the ".css" file extension. All you need to include here is the file name, minus the extension.
// You can make your own custom CSS skins, but if you want to use JSON to make a custom skin, then
// set this value to "JSON" and the tracker will send an unformatted JSON response.
// Default value is "Metallic Console"
$paraTrackerSkin = "Metallic Console";


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for on the web server in the images/levelshots folder.
// If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

// For levelshots to animate, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three animated levelshots for mp/ffa5, the files would have to be in
// the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
// and ffa5_3.jpg

// ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
// and GIFs third. If no images are found, a placeholder image will be displayed instead.

// The following value will enable or disable levelshot transitions. A value of 1 or "Yes" will allow them,
// and any other value with disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$levelshotTransitionsEnabled = "1";

// This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the amount of time, in second, each levelshot will take to fade into the next one.
// Note that fades do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is 1 seconds.
$levelshotTransitionTime = "1";

// This is the animation that will be used for fading levelshots.
// If you want to change the animations, they are found in "css/LevelshotAnimations.css"
// Valid values are whole numbers between 0 to 999 (No decimals).
// Setting this value to 0 will play a random animation.
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
// more levelshots is not always better. Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS

// This value is boolean. When this variable is set to Yes or 1, offending symbols will be
// filtered from the server name. Frequently, people will put unreadable symbols into their
// server names to get a higher alphabetical listing. This feature will remove the nonsense symbols.
// Default is 1.
$filterOffendingServerNameSymbols = "1";

// No Players Online Message
// This message displays in place of the player list when nobody is online.
// Default is "No players online."
$noPlayersOnlineMessage = "No players online.";

// ParaTracker can automatically refresh itself every so often.
// This will not cause any disruption to the game, because the flood protection
// limits how often ParaTracker will contact the server.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Enabled by default.
$enableAutoRefresh = "1";

// This value determines how many seconds ParaTracker waits between refreshes.
// This value cannot be lower than the value in $floodProtectTimeout, or 10 seconds, whichever is greater.
// Decimals are invalid and will be rounded.
// It also cannot be higher than 300 seconds.
// Default is 30 seconds.
$autoRefreshTimer = "30";

// This variable will set the maximum number of characters ParaTracker will accept from the server.
// This prevents pranksters from sending 50MB back, in the event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// If this limit is met, ParaTracker will terminate with an error.
// Default is 16384 characters (One packet).
$maximumServerInfoSize = "16384";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and give
// an IP address, port number and visual theme ID in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=ParaSkinA"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that I have not yet found!
// If you do not understand what this feature is, then DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "0";

// The following setting is a personal message that will be displayed on ParaTrackerDynamic.php when a user is setting
// up ParaTracker for their own use. By default, this is simply a link to our GitHub, where you can download the program
// for free. The point is to encourage as many people as possible to run the software themselves, and not to rely on Dynamic
// mode too much.
// Default is: "ParaTracker is free, open-source software! Download your own at http://github.com/ParabolicMinds/ParaTracker"
$personalDynamicTrackerMessage = "ParaTracker is free, open-source software! Download your own at http://github.com/ParabolicMinds/ParaTracker";


// RCON SETTINGS
// RCON SETTINGS

// This value will enable or disable RCon.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default for security.
$RConEnable = "0";

// This value sets the maximum number of characters ParaTracker will send to the server.
// If the command or password is any larger than this, the command will not be sent.
// Minimum is 20 characters, maximum is 10000 characters.
// Default is 100 characters.
$RConMaximumMessageSize = "100";

// RCon flood protection forces the user to wait a certain number of seconds before sending another command.
// Note that this is not user-specific; if someone else is using your RCon, you may have to wait a bit to
// send the command. Minimum is 10 seconds, maximum is 3600.
// Cannot be lower than the value of $connectionTimeout.
// Default is 20 seconds.
$RConFloodProtect = "20";

// RCon events are logged in RConLog.php for security. This variable will determine
// the maximum number of lines that will be stored in the log file before the old
// entries are truncated. Minimum is 100 lines. Maximum is 100000.
// Default is 1000 lines.
$RConLogSize = "1000";


// POPUP WINDOW SETTINGS
// POPUP WINDOW SETTINGS

// This value is boolean. When the RCon and PARAM buttons are clicked, the popup
// window will snap to the top left corner of the screen by default. When this
// variable is set to any value other than Yes or 1, the behavior is disabled.
// Does not appear to work correctly in Google Chrome.
// Default is 0.
$newWindowSnapToCorner = "0";


// GEOIP SETTINGS
// GEOIP SETTINGS

// This value is boolean. When this variable is set to Yes or 1, GeoIP will be enabled, which
// allows a country flag icon to be displayed on the tracker.
// GEOIP MUST BE INSTALLED ON THE SERVER FOR THIS TO WORK.
// If ParaTracker does not find GeoIP, it will ignore this setting and give an error message.
// Default is 0.
$enableGeoIP = "0";

// For GeoIP to work, ParaTracker needs to know where to find it. This path needs to point to
// the GeoIP PHP file, as ParaTracker will load it on startup.
// Since typically GeoIP will be in the same directory as ParaTracker, the
// default value is ""
$geoIPPath = "";



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