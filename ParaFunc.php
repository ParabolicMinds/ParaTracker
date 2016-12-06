<?php

function versionNumber()
{
    //Return a string of the version number
    //If you modify this project, PLEASE change this value to something of your own, as a courtesy to your users
    Return("1.1.1");
}

//This is here to suppress error messages
$dynamicIPAddressPath = "";
$lastRefreshTime = "";
$floodProtectTimeout = "";

//If this file is executed directly, then echoing this value here will display the version number before exiting.
//If the file is executed from one of the skin files, then this will end up in an HTML comment and will not be visible.
//Either way, the version number will be visible.
echo " ParaTracker " . versionNumber() . " ";
 
if (!isset($safeToExecuteParaFunc))
{
    displayError("ParaFunc.php is a library file and can not be run directly!<br />Try running ParaTrackerA.php or ParaTrackerDynamic.php instead.", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
}

if (file_exists("ParaConfig.php"))
{
    include 'ParaConfig.php';
}
else
{
    writeNewConfigFile();
    if (file_exists("ParaConfig.php"))
    {
        displayError("ParaConfig.php not found! A default config file has been written to disk.<br />Please add an IP Address and port to it.", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
    }
    else
    {
        displayError("ParaConfig.php not found! Attempted to write a default config file, but failed!<br />Make sure ParaTracker has file system access, and that the disk is not full!", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
    }
}

//This IF statement will avoid warning messages during validation
if (!isset($dynamicTrackerCalledFromCorrectFile))
{
    $dynamicTrackerCalledFromCorrectFile = "0";
}

//Before we go any further, let's validate ALL input from the config file!
//To validate booleans:
//$variableName = booleanValidator($variableName, defaultValue);

//To evaluate numeric values:
//$variableName = numericValidator($variableName, minValue, maxValue, defaultValue);

//To evaluate strings:
//$variableName = stringValidator($variableName, maxLength, defaultValue);

//These two values MUST be evaluated first, because they are used in the IP address validation.
//ParaTrackerDynamic.php calls this same file, so we need to be sure which file is calling,
//and what to do about it.
$dynamicTrackerCalledFromCorrectFile = booleanValidator($dynamicTrackerCalledFromCorrectFile, 0);
$dynamicTrackerEnabled = booleanValidator($dynamicTrackerEnabled, 0);
$personalDynamicTrackerMessage = stringValidator($personalDynamicTrackerMessage, "", "");

if($dynamicTrackerEnabled == "1" && $dynamicTrackerCalledFromCorrectFile == "1")
{
    //Terminate the script with an instruction page if no IP address was given!
    if (!isset($_GET["ip"]))
    {
        dynamicInstructionsPage($personalDynamicTrackerMessage);
    }
    $serverIPAddress = ipAddressValidator($_GET["ip"], "", $dynamicTrackerEnabled);
    $serverPort = numericValidator($_GET["port"], 1, 65535, 29070);
    //We need to make sure the skin given is a valid value. If not, we just default to A.
    $paraTrackerSkin = skinValidator($_GET["skin"]);
}
else
{
    $serverIPAddress = ipAddressValidator($serverIPAddress, $serverPort, $dynamicTrackerEnabled);
    $serverPort = numericValidator($serverPort, 1, 65535, 29070);
    $paraTrackerSkin = "";
}

$connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2);
$floodProtectTimeout = numericValidator($floodProtectTimeout, 5, 1200, 15);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$floodProtectTimeout = numericValidator($floodProtectTimeout, $connectionTimeout, 1200, 10);
$refreshTimeout = numericValidator($refreshTimeout, 1, 15, 2);

$disableFrameBorder = booleanValidator($disableFrameBorder, 0);

$fadeLevelshots = booleanValidator($fadeLevelshots, 1);
$levelshotDisplayTime = numericValidator($levelshotDisplayTime, 1, 15, 3);
$levelshotTransitionTime = numericValidator($levelshotTransitionTime, 0.1, 5, 0.5);
$levelshotFPS = numericValidator($levelshotFPS, 1, 60, 30);
$maximumLevelshots = numericValidator($maximumLevelshots, 1, 99, 20);

//Gamename can also be given dynamically, so let's check for that too.
if($dynamicTrackerEnabled == "1" && $dynamicTrackerCalledFromCorrectFile == "1")
{
    $gameName = stringValidator(rawurldecode($_GET["game"]), "", "Jedi Academy");
}
else
{
    $gameName = stringValidator($gameName, "", "Jedi Academy");
}
//Generate a levelshot path
$levelshotFolder = $gameName;
//Check to make sure the folder exists, and convert the string and directory name to lowercase
$levelshotFolder = checkLevelshotDirectoriesAndConvertToLowercase($levelshotFolder);

$noPlayersOnlineMessage = stringValidator($noPlayersOnlineMessage, "", "No players online.");

$enableAutoRefresh = booleanValidator($enableAutoRefresh, 1);
//Have to validate this one twice to make sure it isn't lower than the floodprotect limit
$autoRefreshTimer = numericValidator($autoRefreshTimer, 10, 300, 30);
$autoRefreshTimer = intval(numericValidator($autoRefreshTimer, $floodProtectTimeout, 300, 30));
$maximumServerInfoSize = numericValidator($maximumServerInfoSize, 2000, 50000, 4000);

$RConEnable = booleanValidator($RConEnable, 0);
$RConMaximumMessageSize = numericValidator($RConMaximumMessageSize, 20, 10000, 100);

$RConFloodProtect = numericValidator($RConFloodProtect, 10, 3600, 20);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$RConFloodProtect = numericValidator($RConFloodProtect, $connectionTimeout, 3600, 20);
$RConLogSize = numericValidator($RConLogSize, 100, 100000, 1000);

$newWindowSnapToCorner = booleanValidator($newWindowSnapToCorner, 0);

//The IP address has already been validated, so we can use it for a directory name
//Make sure we convert the path to lowercase when creating folders for it, or else the flood protection could be bypassed!
$dynamicIPAddressPath = strtolower($serverIPAddress . "-" . $serverPort . "/");

//Add some checks to make sure we have directories for the stuff
checkDirectoryExistence("info/", "");
checkDirectoryExistence($dynamicIPAddressPath, "info/");

checkDirectoryExistence("images/levelshots", "");

checkDirectoryExistence("logs/", "");
checkDirectoryExistence($dynamicIPAddressPath, "logs/");

//And now let's check to make sure we have access to the file system to write all the files we need. 
checkForMissingFiles($dynamicIPAddressPath);

//This needs to run every time the tracker is run. Otherwise the "No connection" pages will be missing the counter
autoRefreshScript($dynamicIPAddressPath, $enableAutoRefresh, $autoRefreshTimer);

function checkForMissingFiles($dynamicIPAddressPath)
{
    checkFileExistence("BitFlags.php", "");
    checkFileExistence("connectionErrorMessage.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("errorMessage.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("gametype.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("gamename.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("levelshotJavascriptAndCSS.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("mapname.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("mapname_raw.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("modname.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("param.html", "info/" . $dynamicIPAddressPath);
    checkFileExistence("playerCount.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("playerList.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("rconParamScript.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("RConTime.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("RConLog.php", "logs/" . $dynamicIPAddressPath);
    checkFileExistence("refreshCode.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("serverDump.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("sv_hostname.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("sv_maxclients.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("time.txt", "info/" . $dynamicIPAddressPath);
}

function checkFileExistence($filename, $dynamicIPAddressPath)
{
    if (!file_exists($dynamicIPAddressPath . $filename))
    {
        file_put_contents($dynamicIPAddressPath . $filename, "");
        if (!file_exists($dynamicIPAddressPath . $filename))
        {
            displayError("Failed to create file " . $dynamicIPAddressPath . $filename . "!<br />Make sure ParaTracker has file system access, and that the disk is not full!", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
        }
    }
}

function checkDirectoryExistence($dirname, $dynamicIPAddressPath)
{
    if (!file_exists($dynamicIPAddressPath . $dirname))
    {
        mkdir($dynamicIPAddressPath . $dirname);
    }
    if (!file_exists($dynamicIPAddressPath . $dirname))
    {
        displayError("Failed to create directory " . $dynamicIPAddressPath . $dirname . " in ParaTracker folder!<br />Cannot continue without file system access!", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
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
                if(rename("images/levelshots/" . $directoryList[$i], "images/levelshots/" . strtolower($directoryList[$i])) == false)
                {
                    echo ' Could not rename directory ' . $directoryList[$i] . '! Levelshots will not work. Continuing without them.';
                }
                $exit = "1";
            }
        }
    }


    //If we cannot find a match, then we will just leave it be, and the levelshot code will fall back on the placeholder image

    $levelshotFolder = "images/levelshots/" . $levelshotFolder . "/";
    return $levelshotFolder;
}

function checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $refreshTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner)
{

    //Check the time delay between refreshes. Make sure we wait if need be
    checkTimeDelay($connectionTimeout, $refreshTimeout, $dynamicIPAddressPath);

    $lastRefreshTime = numericValidator(file_get_contents("info/" . $dynamicIPAddressPath . "time.txt"), "", "", "0");


        if ($lastRefreshTime + $floodProtectTimeout < time())
        {

            //Prevent users from aborting the page! This will reduce load on both the game server and the web server
            //by forcing the refresh to finish.
            ignore_user_abort(true);

            file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", "wait");

            //Remove any lingering error messages. We will write a new one later if we encounter another error.
            file_put_contents("info/" . $dynamicIPAddressPath . "errorMessage.txt", "");

            doUpdate("0", $serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner);

            file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", time());

		    //If someone loads a dynamic tracker with the same IP and port number, and the wrong
            //game name, we don't want it to look wrong to other users. So we'll write the game name to a special file,
            //and if the tracker is re-run with the wrong one, we'll re-parse the server dump accordingly.
            file_put_contents("info/" . $dynamicIPAddressPath . "gamename.txt", $gameName);

            //Allow users to abort the page again.
            ignore_user_abort(false);

        }
        else
        {
            //Even if it isn't time to update, we have a little more work to do.
            //First, did ParaTracker terminate with an error last time?
            $printErrorMessage = trim(file_get_contents("info/" . $dynamicIPAddressPath . "errorMessage.txt"));
            if($printErrorMessage != "")
            {
                //ParaTracker terminated with an error last time. Display it and the remaining time before the next refresh!
                echo $printErrorMessage . "<br />" . ($lastRefreshTime + $floodProtectTimeout - time()) . " seconds before next attempt.";
            }

            //Next we need to check if the gameName has changed from what is in the text file.
            //If it has, we must re-parse the serverDump accordingly.
            if (file_get_contents("info/" . $dynamicIPAddressPath . "gamename.txt") != $gameName)
            {
                //Save the old refresh time so we can put it back afterward
                $oldRefreshTime = file_get_contents("info/" . $dynamicIPAddressPath . "time.txt");
                file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", "wait");

                doUpdate("1", $serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner);

                //Put the old refresh time back into time.txt
                file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", $oldRefreshTime);

            }
        }
}

function doUpdate($useOldServerDump, $serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner)
{
	//Before we start, wipe out the parameter list. That way, if we encounter an error later, the list does not remain
    file_put_contents('info/' . $dynamicIPAddressPath . 'param.html', "");

    if($useOldServerDump != "1")
    {
        //On with the good stuff!
        $fp = fsockopen("udp://" . $serverIPAddress, $serverPort, $errno, $errstr, $connectionTimeout);
        fwrite($fp, str_repeat(chr(255),4) . "getstatus\n");
        $s='';
        stream_set_timeout($fp, $connectionTimeout);

        while (false !== ($char = fgetc($fp)))
        {
		    $s .= $char;
		}
        fclose($fp);


        if(strlen($s) > $maximumServerInfoSize)
        {
	        displayError('Received too much data!<br />' . strlen($s) . ' characters received, the limit is ' . $maximumServerInfoSize . '<br />Check to see if you are connected to the correct server or increase $maximumServerInfoSize in ParaConfig.php.', $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
	        }

	//This file is used for determining if the server connection was successful and regenerating dynamic content, plus it's good for debugging
	file_put_contents("info/" . $dynamicIPAddressPath . "serverDump.txt", $s);

	if($errstr == "" && $s == "")
	{
	    $errstr = "No response in " . $connectionTimeout . " seconds.";
	    echo "No response in " . $connectionTimeout . " seconds.";
	}
	file_put_contents("info/" . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));

    }
    else
    {
        $s = file_get_contents("info/" . $dynamicIPAddressPath . "serverDump.txt");
	}

	if(strlen($s))
	{
	    //Server responded!
	    
	    //Mark the time in microseconds so we can see how long this takes.
	    $parseTimer = microtime(true);

	    //Now, we call a function to parse the data
	    $dataParserReturn = dataParser($s, $dynamicIPAddressPath);
	    //Organize the data that came back in the array
		$cvar_array_single = $dataParserReturn[0];
		$cvars_hash = $dataParserReturn[1];
		$player_array = $dataParserReturn[2];
		$playerParseCount = $dataParserReturn[3];


		$player_count = playerList($dynamicIPAddressPath, $player_array, $playerParseCount, $noPlayersOnlineMessage);
		file_put_contents("info/" . $dynamicIPAddressPath . "playerCount.txt", $player_count);

		//The following function detects how many levelshots exist on the server, and passes a buffer of information back, the final count of levelshots, and whether they fade or not
		$levelshotBufferArray = levelshotfinder($cvars_hash["mapname"], $maximumLevelshots, $fadeLevelshots, $levelshotFolder);
		$levelshotBuffer = $levelshotBufferArray[0];
		$levelshotCount = $levelshotBufferArray[1];

		levelshotJavascriptAndCSS($dynamicIPAddressPath, $levelshotBuffer, $enableAutoRefresh, $autoRefreshTimer, $fadeLevelshots, $levelshotCount, $levelshotTransitionTime, $levelshotFPS, $levelshotDisplayTime, $levelshotFolder);

		paramRConJavascript($dynamicIPAddressPath, $RConEnable, $newWindowSnapToCorner);

		//This has to be last, because the timer will output on this page
		cvarList($serverIPAddress, $serverPort, $dynamicIPAddressPath, $gameName, $cvar_array_single, $parseTimer);

	}
}

function dataParser($s, $dynamicIPaddressPath)
{
$player_array = "";
//Split the info first, then we'll loop through and remove any dangerous characters
		$sections = preg_split('_[' . chr(0x0A) . ']_', $s);
		$cvars_array = preg_split('_[\\\]_', $sections[1]);

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
		for($i = 2; $i < count($sections)-1; $i++)
		{
			$player_data_split = preg_split('_["]_', $sections[$i]);
			$player_numbers_split = preg_split('_[ ]_', $player_data_split[0]);
			//As we put them into the new array, let's validate them as well
			$player_array[$i] = array("score" => stringValidator($player_numbers_split[0], "", ""), "ping" => stringValidator($player_numbers_split[1], "", ""), "name" => stringValidator($player_data_split[1], "", ""));
			$playerParseCount++;
		}


		if(isset($cvars_hash["sv_hostname"]))
		{
		    file_put_contents('info/' . $dynamicIPaddressPath . 'sv_hostname.txt', colorize($cvars_hash["sv_hostname"]));
		}
		else
		{
		    file_put_contents('info/' . $dynamicIPaddressPath . 'sv_hostname.txt', colorize($cvars_hash["hostname"]));
		}
		file_put_contents('info/' . $dynamicIPaddressPath . 'sv_maxclients.txt', $cvars_hash["sv_maxclients"]);
		file_put_contents('info/' . $dynamicIPaddressPath . 'modname.txt', colorize($cvars_hash["gamename"]));
		file_put_contents('info/' . $dynamicIPaddressPath . 'mapname.txt', colorize($cvars_hash["mapname"]));
		//This line is needed for Dynamic Paratracker to use levelshots correctly
		file_put_contents('info/' . $dynamicIPaddressPath . 'mapname_raw.txt', $cvars_hash["mapname"]);

		return(array($cvar_array_single, $cvars_hash, $player_array, $playerParseCount));
}

function cvarList($serverIPAddress, $serverPort, $dynamicIPAddressPath, $gameName, $cvar_array_single, $parseTimer)
{
		$buf = '</head>
		<body class="cvars_page">
		<span class="CVarHeading">Server Cvars</span><br />
		<span class="CVarServerAddress">' . $serverIPAddress . ":" . $serverPort . '</span><br /><br />
		<table class="FullSizeCenter"><tr><td><table><tr class="cvars_titleRow cvars_titleRowSize"><td class="nameColumnWidth">Name</td><td class="valueColumnWidth">Value</td></tr>' . "\n";
		$c = 1;

		//BitFlags.php must be included here.
		if(file_exists("BitFlags.php"))
		{
		$safeToExecuteBitFlags = "1";
		include('BitFlags.php');
		}
		else
		{
		    //Lack of BitFlags.php is not a fatal error. Just echo a warning and ignore.
		    echo " Could not find BitFlags.php! This error is not fatal, but ParaTracker cannot parse gametypes or bitflags. ";
		}

		//We'll need a new copy of game name to toy with for this part
		//Pull out invalid characters and make it lowercase
		$bitFlagGameName = preg_replace("/[^a-z0-9]/", "", strtolower($gameName));

		if(function_exists($bitFlagGameName) && is_callable($bitFlagGameName))
		{
		    //Remove the index
		    $bitFlagsData = $bitFlagGameName();
		    $bitFlagsIndex = array_shift($bitFlagsData);
		    
		    //Remove the gametype array, as it is not a bitflag
		    $gametypeCVarName = strtolower(array_shift($bitFlagsIndex));
		    $gametypeArray = array_shift($bitFlagsData);
		    
		    //Parse the arrays into variables named after the CVars
		    for($i = 0; $i < count($bitFlagsData); $i++)
		    {
		        $$bitFlagsIndex[$i] = $bitFlagsData[$i];
		    }
		}
		else
		{
		    if(!is_callable($bitFlagGameName))
		    {
		        echo " Could not load bit flag data for " . $gameName . " due to an invalid function name! This error is not fatal, but ParaTracker cannot parse gametypes or bitflags. Contact the ParaTracker team with the game name, as this is a bug that must be fixed. ";
		    }
		    else
		    {
		        echo " Could not find bit flag data for " . $gameName . "! This error is not fatal, but ParaTracker cannot parse gametypes or bitflags. ";
		    }
        }

		foreach($cvar_array_single as $cvar)
		{
			$buf .= '<tr class="cvars_row' . $c . '"><td class="nameColumnWidth">' . $cvar['name'] . '</td><td class="valueColumnWidth">';

			if ((($cvar['name'] == 'sv_hostname') || ($cvar['name'] == 'hostname') || ($cvar['name'] == 'gamename') || ($cvar['name'] == 'mapname')) && ((strpos(colorize($cvar['value']), $cvar['value'])) == FALSE))
			{
				$buf .= '<b>' . colorize($cvar['value']) . "</b><br />" . $cvar['value'];
			}
			else
			{
			    //We need to check for the bitflag variables here, and calculate them if there is a match
			    $foundMatch = 0;
			    for($i = 0; $i < count($bitFlagsIndex) && $foundMatch == 0; $i++)
			    {
			        $cvar['name'] = strtolower($cvar['name']);

			        if($cvar['name'] == strtolower($bitFlagsIndex[$i]))
			        {
			            $foundMatch = 1;
			            $buf .= bitvalueCalculator($cvar['name'], $cvar['value'], $$bitFlagsIndex[$i]);
			        }
			        else
			        {
			            if($cvar['name'] == $gametypeCVarName)
			            {
			                $buf .= $cvar['value'] . ' (<b>' . $gametypeArray[$cvar['value']] . '</b>)';
			                file_put_contents('info/' . $dynamicIPAddressPath . 'gametype.txt', $gametypeArray[$cvar['value']]);
			                $foundMatch = 1;
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
		$buf .= '</table></td></tr></table><h4 class="center">ParaTracker version ' . versionNumber() . ' - Server info parsed in ' . number_format(((microtime(true) - $parseTimer) * 1000), 3) . ' milliseconds.</h4><h5>Copyright &copy; 1837 Rick Astley. No rights reserved. Void where prohibited.<br />Your mileage may vary. Please drink and drive responsibly.</h5></body></html>';
		$buf = htmlDeclarations($cvar['name'] . "CVars", "../../") . $buf;
		file_put_contents('info/' . $dynamicIPAddressPath . 'param.html', $buf);
}

function playerList($dynamicIPAddressPath, $player_array, $playerParseCount, $noPlayersOnlineMessage)
{

		$playerListbuffer = '';
		$player_count = 0;

		if($playerParseCount > 0)
		{
		    //Sort by ping first in descending order, to move bots to the bottom
			$player_array = array_sort($player_array, "ping", false);
			//Now, sort by score. If a bot has a higher score than a player, they will be on top. But at least real players are more visible this way
			$player_array = array_sort($player_array, "score", true);

			$c = 1;
			foreach($player_array as &$player)
			{
				$player_name = str_replace(array("\n", "\r"), '', $player["name"]);
				$player_count++;
				$playerListbuffer .= "\n" . '
<div class="playerRow' . $c . ' playerRowSize"><div class="playerName playerNameSize">'. colorize($player_name);
				$playerListbuffer .= '</div>' . "\n" . '
<div class="playerScore playerScoreSize">' . $player["score"] . '</div><div class="playerPing playerPingSize">' . $player["ping"] . '</div></div>';
				$c++;
				if($c > 2) $c = 1;
			}
			$playerListbuffer .= "\n";
		}
		else
		{
			$playerListbuffer .= '<div class="noPlayersOnline">&nbsp;' . $noPlayersOnlineMessage . '</div>';
		}
		$playerListbuffer .= '<div></div>';
		$buf3='';
		file_put_contents('info/' . $dynamicIPAddressPath . 'playerList.txt', $playerListbuffer);

		return $player_count;
}

function levelshotFinder($mapName, $maximumLevelshots, $fadeLevelshots, $levelshotFolder)
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
		        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '_' . $levelshotIndex . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        		$foundLevelshot = 1;
		    }
		    else
		    {
		    //Failed to find a PNG, so let's check for a JPG
		        if(file_exists($levelshotFolder . $mapName . '_' . $levelshotIndex . '.jpg'))
		        {
		            $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '_' . $levelshotIndex . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		            $foundLevelshot = 1;
		        }
		        else
		        {
		            //Also failed to find a JPG, so let's check for a GIF
		            if(file_exists($levelshotFolder . $mapName . '_' . $levelshotIndex . '.gif'))
		            {
		                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '_' . $levelshotIndex . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
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
		                        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        				    }
        				    else
        				    {
        				        //And checking for a JPG again:
		                	    if(file_exists($levelshotFolder . $mapName . $levelshotIndex . '.jpg'))
		                	    {
		                	        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	    }
		                	    else
		                	    {
		                	        //Lastly...checking for a GIF.
		                	        if(file_exists($levelshotFolder . $mapName . $levelshotIndex . '.gif'))
		                	        {
		                                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("' . $levelshotFolder . $mapName . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	        }
		                	        else
		                	        {
		                	            //Could not find a levelshot! Use the default 'missing picture' image and close out
		                	            $levelshotBuffer .= '.levelshot1{background: url("images/missing.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	            $levelshotBuffer .= '.levelshot2{background: url("images/missing.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	        }
		                	    }
        				    }
		                }
		            }
		        }
		    }

   		$levelshotBuffer .= "\n";

	        if ($foundLevelshot == 1)
	        {
	            $levelshotCount++;
	            $levelshotIndex++;
	        }

		} While ($foundLevelshot == 1 && $levelshotCount < $maximumLevelshots && $fadeLevelshots == 1);

//This code prevents the Javascript that follows from seeing a value of 0 levelshots when none are found.
//There will always be a minimum of one levelshot. A placeholder is used if none is found.
if ($levelshotCount == 0)
{
    $levelshotCount = 1;
}

return array($levelshotBuffer, $levelshotCount);
}

function autoRefreshScript($dynamicIPAddressPath, $enableAutoRefresh, $autoRefreshTimer)
{
$output = "";
    if($enableAutoRefresh == "1")
    {
        $output .= '<script type="text/javascript">

 		var refreshTimer = "' . $autoRefreshTimer . '";
 		var refreshCancelled = "0";
 		var replaceStuff = "";

 		replaceStuff = document.getElementById("refreshTimerDiv").className;
 		document.getElementById("refreshTimerDiv").className = replaceStuff.replace("hiddenTimer", "");
		document.getElementById("refreshTimerDiv").innerHTML = refreshTimer;

		pageReloadTimer = setTimeout("refreshTick()", 1000);

 		function refreshTick()
 		{

 		    if(refreshCancelled == "0")
 		    {
 		        if(refreshTimer > 0)
 		        {
 		            refreshTimer--;
					document.getElementById("refreshTimerDiv").innerHTML = refreshTimer;
 		            pageReloadTimer = setTimeout("refreshTick()", 1000);
 		        }
 		        else
 		        {
					document.getElementById("refreshTimerDiv").innerHTML = "...";
 		            pageReload();
 		        }
 		    }
 		}

        </script>
';
    }
file_put_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt", $output);
}

function levelshotJavascriptAndCSS($dynamicIPAddressPath, $levelshotBuffer, $enableAutoRefresh, $autoRefreshTimer, $fadeLevelshots, $levelshotCount, $levelshotTransitionTime, $levelshotFPS, $levelshotDisplayTime, $levelshotFolder)
{
$javascriptFunctions = "";

        $javascriptFunctions .= '<script type="text/javascript"><!--
		var timer = 0;  //Used for setting re-execution timeout
		var allowFading = ' . $fadeLevelshots . ';   //Used to test whether fading levelshots is disabled
		var opac = 1;   //Opacity level for the top layer.
		var shot = 1;   //Levelshot number
		var mode = 1;   //0 means we are delaying between fades. 1 means a fade is in progress.
		var maxLevelshots = ' . $levelshotCount . ';   //The maximum number of levelshots detected by the PHP code goes here.
		var count = -1; //Counter used for checking the number of executions.

		function fadelevelshot()
		{

			count++;
			if (mode == 0 && maxLevelshots > 1 && allowFading == 1)
			{
				if (count >= ' . $levelshotFPS * $levelshotTransitionTime . ' || opac < 0)
				{
					document.getElementById("topLayerFade").className = document.getElementById("bottomLayerFade").className;
					document.getElementById("bottomLayerFade").className = document.getElementById("levelshotPreload").className;
	                document.getElementById("levelshotPreload").className = "levelshotFrame levelshot" + shot;
					document.getElementById("topLayerFade").style.opacity = 1;
					opac = 1;
					count = 0;
					mode = 1;
					timer = setTimeout("fadelevelshot()", ' . 1000 * $levelshotDisplayTime . ');
				}
				else
				{
					opac -= 1 / (' . $levelshotTransitionTime * $levelshotFPS . ');
					document.getElementById("topLayerFade").style.opacity = opac;
					timer = setTimeout("fadelevelshot()", ' . $levelshotTransitionTime * 1000 / ($levelshotFPS * $levelshotTransitionTime) . ');
				}
			}
			else
			{
			//A levelshot has finished its transition, so reset everything
				count = 0;
				mode = 0;
				shot++;
				if(shot > maxLevelshots) shot = 1;
				//Now, re-execute the script to start fading a new levelshot!
				timer = setTimeout("fadelevelshot()", 10);
			}
		}

		    //This little bit of code pre-loads the second and third levelshots, and terminates the script if only one levelshot is available.
	    function firstexecution()
	    {
	        if (maxLevelshots > 1 && allowFading == 1);
	            {
	                shot++;
	                document.getElementById("topLayerFade").className = "levelshotFrame levelshot1";
	                document.getElementById("bottomLayerFade").className = "levelshotFrame levelshot" + shot;

	                //lets set up a pre-loader in case there are more than 2 levelshots
	                shot++;
	                //In case there are only two levelshots, then we will just go back to shot 1
	                if(shot > maxLevelshots) shot = 1;
	                document.getElementById("levelshotPreload").className = "levelshotFrame levelshot" + shot;
	                document.getElementById("topLayerFade").style.opacity = 1;

	                opac = 1;
	                count = 0;
	                mode = 1;
	                timer = setTimeout("fadelevelshot()", ' . 1000 * $levelshotDisplayTime . ');
	            }
	    }

timer = setTimeout("firstexecution()", 100);';

$javascriptFunctions .= '//--></script>

<style>
' . $levelshotBuffer . '
</style>';

file_put_contents("info/" . $dynamicIPAddressPath . "levelshotJavascriptAndCSS.txt", $javascriptFunctions);
}

function paramRConJavascript($dynamicIPAddressPath, $RConEnable, $newWindowSnapToCorner)
{
		$output = '<script type="text/javascript">

		function param_window()
		{
		paramWindow = window.open("info/' . $dynamicIPAddressPath . 'param.html", "paramWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=600,height=700';

		if ($newWindowSnapToCorner == "1")
			{
			$output .= ',left=0,top=0';
			}

		$output .= '");
}
';

		if ($RConEnable == 1)
		{
		$output .= 'function rcon_window()
		{
		rconWindow = window.open("RCon.php", "rconWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=780,height=375';

		if ($newWindowSnapToCorner == "1")
		{
		    $output .= ',left=0,top=0';
		}

		$output .= '");
		}';

		}
$output .= '</script>';

file_put_contents("info/" . $dynamicIPAddressPath . "rconParamScript.txt", $output);
}

function checkTimeDelay($connectionTimeout, $refreshTimeout, $dynamicIPAddressPath)
{
$lastRefreshTime = "0";
if (file_exists("info/" . $dynamicIPAddressPath . "time.txt"))
{
    $lastRefreshTime = numericValidator(file_get_contents("info/" . $dynamicIPAddressPath . "time.txt"), "", "", "wait");
}
else
{
  	file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", "wait");
    $lastRefreshTime = "0";
}

$i = 0;
$sleepTimer = "0.15"; //This variable sets the number of seconds PHP will wait before checking to see if anything has changed.

while ($lastRefreshTime == "wait" && $i < ($connectionTimeout + $refreshTimeout))
{
    //info/Time.txt indicated that a refresh is in progress. Wait a little bit so it can finish. If it goes too long, we'll continue on, and force a refresh.
    usleep($sleepTimer * 1000000);
    $lastRefreshTime = numericValidator(file_get_contents("info/" . $dynamicIPAddressPath . "time.txt"), "", "", "wait");
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

    if ($input == 1 || strtolower($input) == "yes")
        {
            $input = 1;
        }
        else
        {
            if($defaultValue == "1" && strtolower($defaultValue) == "yes")
            {
                //Not $input = $defaultValue - I want to force it to boolean, even if I make a programming error
                $input = 1;
            }
            else
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
            $input = substr($input,0,$maxLength);
        }
        //Trim whitespace from the end of the string. There's no reason to leave it there.
        //I will leave whitespace at the beginning, though, because people might use spaces
        //or tabs to align things. So, rtrim instead of trim.
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
            //We are in static mode, so ParaConfic.php is the problem
            displayError("No server address specified in ParaConfig.php!", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
        }
        else
        {
            //We are in Dynamic mode, so the user did not give an address
            displayError('Invalid IP address! ' . stringValidator($input) . '<br />Please add an IP Address and port to ParaConfig.php</h3>', $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
        }
    }

    //Use a PHP function to check validity
    if (!filter_var($input, FILTER_VALIDATE_IP) && $input != "localhost")
    {
        //getbyhostname returns the input string on failure. So, to test if this is a failure, we test it against itself
        if(gethostbyname($input) == $input)
        {
            //DNS test failed. Just error out.
            displayError('Invalid domain name! ' . stringValidator($input, "", "") . '<br />Check the address and try again.</h3>', $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
        }
        else
        {
            if(gethostbyname($input) == $input)
            {
                If($dynamicTrackerEnabled == "1")
                {
                    displayError('Invalid IP address! ' . stringValidator($input, "", "") . '<br />Check the IP address and try again.</h3>', $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
                }
                else
                {
                    displayError('Invalid IP address! ' . stringValidator($input, "", "") . '<br />Check the IP address in ParaConfig.php</h3>', $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
                }
            }
        }
    }

return $input;
}

function skinValidator($paraTrackerSkin)
{
    $paraTrackerSkin = stringValidator($paraTrackerSkin, "", "A");

    if(strlen($paraTrackerSkin) == 1)
    {
        $paraTrackerSkin = strtoupper($paraTrackerSkin);
    }

    if(strtolower($paraTrackerSkin) == "dynamic" || strtolower($paraTrackerSkin) == "template")
    {
        $paraTrackerSkin = "A";
        echo "Invalid skin specified! Assuming default skin.";
    }

    if(!file_exists("ParaTracker" . $paraTrackerSkin . ".php"))
    {
        if(!file_exists("ParaTrackerA.php"))
        {
            displayError("Invalid skin specified!<br />Default skin could not be found!", $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout);
        }
        else
        {
        $paraTrackerSkin = "A";
        echo "Invalid skin specified! Assuming default skin.";
        }
    }
    return $paraTrackerSkin;
}

function displayError($errorMessage, $dynamicIPAddressPath, $lastRefreshTime, $floodProtectTimeout)
{
    if(trim($errorMessage) == "")
    {
        $errorMessage = "An unknown error has occurred!<br />ParaTracker must terminate.";
    }
    $errorMessage = '<!-- --><h3 class="errorMessage">' . $errorMessage . '</h3>';

    //Error detected and ParaTracker is terminating. Check to see if we have a file path and refresh time data.
    if($dynamicIPAddressPath != "" && $lastRefreshTime != "" && $floodProtectTimeout != "")
    {
        //We have a file path! Write the error message to a file, update both of the refresh timers, and terminate!
        file_put_contents("info/" . $dynamicIPAddressPath . "errorMessage.txt", $errorMessage);
        file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", time());
        file_put_contents("info/" . $dynamicIPAddressPath . "RConTime.txt", time());
    }
    //If no file path was given, flood protection will not be necessary, as ParaTracker never had a chance to contact the server.
    //so it is safe to terminate regardless of whether there was a file path or not.
    echo $errorMessage;
    exit();
}

function bitvalueCalculator($cvarName, $cvarValue, $arrayList)
{

            $toBeExploded = "";
             $output = '<div class="CVarExpandList" onclick="bitValueClick(' . "'" . $cvarName . "'" .  ')"><b>' . $cvarValue . '</b><br /><i class="expandCollapse">(Click to expand/collapse)</i>';

            $index = count($arrayList);

            if ($index < 1)
            {
                $output .= '<div id="' . $cvarName . '" class="collapsedList"><br /><i>None</i></div>';
            }
            elseif ($cvarValue >= pow(2, $index))
            {
                //Miscount detected! Array does not have enough values
                $output .= "<br />Miscount detected! Not enough values in the array for " . $cvarName . ". Check BitFlags.php and add the missing values!";
            }
            else
            {
                //Sort through the bits in the value given, and for every 1, output the matching array value
                for ($i = 0; $i < $index; $i++)
                {
                    if ($cvarValue & (1 << $i))
                    {
                        $toBeExploded = "\n" . $arrayList[$i] . $toBeExploded;
                    }
                }
            }


    $iBlewItUp = explode("\n", $toBeExploded);
    sort($iBlewItUp);
    $iBlewItUp = implode("<br />", $iBlewItUp);

    $output .= '<div id="' . $cvarName .  '" class="collapsedList"><i>' . $iBlewItUp . '</i></div></div>';

    return $output;
}

function htmlDeclarations($pageTitle, $filePath)
{
    $pageTitle = stringValidator($pageTitle, "", "ParaTracker - The Ultimate Quake III Server Tracker");
    $output = '<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="Windows-1252">
    <link rel="stylesheet" href="' . $filePath . 'Config-DoNotEdit.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaStyle.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinA.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinB.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinC.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinD.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinE.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinF.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinG.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaSkinH.css" type="text/css" />
    <title>' . $pageTitle . '</title>
    <script src="' . $filePath . 'ParaScript.js"></script>';
    return $output;
}

function colorize($string)
{
        $characters = preg_split('//', $string);
        $colorized_string = '<span class="color7">';
        
        for($i = 0; $i < count($characters)-1; $i++)
        {
                if(($characters[$i] == '^') && ($i < count($characters)-2) && (strpos(" 0123456789?", $characters[$i+1]) != FALSE))
                {
                        $colorized_string = $colorized_string . '</span><span class="color' . $characters[$i+1] . '">';
                        $i ++;
                }
                else
                {
                        $colorized_string = $colorized_string . $characters[$i];
                }
        }
        return $colorized_string . "</span>";
}

function dynamicInstructionsPage($personalDynamicTrackerMessage)
{
    $urlWithoutParameters = explode('?', $_SERVER["REQUEST_URI"], 2);
    $currentURL = $_SERVER['HTTP_HOST'] . $urlWithoutParameters[0];

    $output = htmlDeclarations("", "");
    
    echo '-->' . $output . '</head><body class="dynamicConfigPage dynamicConfigPageStyle">
';

    echo '<div class="dynamicPageContainer"><div class="dynamicPageWidth"><h1>ParaTracker ' . versionNumber() . ' - Dynamic Mode</h1>
    <i>' . $personalDynamicTrackerMessage . '</i>
    <h3>ParaConfig.php settings still apply!</h3><h6>So, for instance, if you want to enable RCon, or change levelshot options, it must be changed in ParaConfig.php, by the server owner.<br />For a full set of options, you can and should host your own ParaTracker.</h6>

    <form>
    <p>Current page URL:<br /><input type="text" size="80" id="currentURL" value="' . $currentURL . '" readonly /></p>
    <br />
    <h3>Enter the appropriate data below to get a URL you can use for ParaTracker Dynamic:</h3>

    <p>Server IP Address: <input type="text" size="46" onclick="clearOutputFields()" onchange="clearOutputFields()" id="IPAddress" value="" /></p>
    <p>Server port number: <input type="text" size="15" onclick="clearOutputFields()" onchange="clearOutputFields()" id="PortNumber" value="" /></p>';


echo 'Game name: <select id="GameNameDropdown" name="Game" onclick="clearOutputFields()" onchange="checkForOtherValue()">';

    $directoryList = scandir("images/levelshots/");

    //Loop through the array of stuff listed, and see if there's anything that matches the given game name
    //We start counting at 2 because the first two values are ".." and "..."
    for($i = 2; $i < count($directoryList); $i++)
    {
        //DO NOT make the directory list value lowercase here! It will make all dynamic game names appear in lowercase on the tracker
        echo '<option value="' . ucwords(strtolower($directoryList[$i])) . '" ';
        //If the levelshots for JA are present, let's set it as the default game
        if(strtolower($directoryList[$i]) == "jedi academy")
        {
            echo 'selected="selected"' ;
        }
        echo '>' . ucwords(strtolower($directoryList[$i])) . '</option>' . "\n";
    }
    
    echo '<option value="other">Other... (Levelshots unavailable on this server)</option>
    </select>';


echo '<div class="gameNameContainer"><div id="hideGameNameWhenUnnecessary" ';
if(count($directoryList) > 2)
{
    echo 'class="collapsedFrame"';
}
else
{
    echo 'class="expandedFrame"';
}

echo '>
<br />Enter game name: <input type="text" size="40" onclick="clearOutputFields()" onchange="clearOutputFields()" id="GameName" value="" />
</div></div>';




echo '<p>Skin:<br />
    <input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-A" value="A" checked>A (675 x 300)<br />';

    if(file_exists("ParaTrackerB.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-B" value="B">B (600 x 225)<br />';
    }
    if(file_exists("ParaTrackerC.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-C" value="C">C (600 x 225)<br />';
    }
    if(file_exists("ParaTrackerD.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-D" value="D">D (100 x 100)<br />';
    }
    if(file_exists("ParaTrackerE.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onclick="clearOutputFields()" id="SkinID-E" value="E">E (100 x 100)<br />';
    }
    if(file_exists("ParaTrackerF.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-F" value="F">F (100 x 100)<br />';
    }
    if(file_exists("ParaTrackerG.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-G" value="G">G (100 x 100)<br />';
    }
    if(file_exists("ParaTrackerH.php"))
    {
        echo '<input type="radio" name="skinID" onclick="clearOutputFields()" onchange="clearOutputFields()" id="SkinID-H" value="H">H (100 x 100)</p>';
    }
    echo '<p><button type="button" class="dynamicFormButtons dynamicFormButtonsStyle" onclick="createURL()"> Generate! </button></p>
    <p>Direct link:<br /><textarea rows="3" cols="120" id="finalURL" readonly></textarea></p>
    <p>HTML code to insert on a web page:<br /><textarea rows="4" cols="120" id="finalURLHTML" readonly></textarea></p>

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
    exit();
}

function sendRecieveRConCommand($serverIPAddress, $serverPort, $dynamicIPAddressPath, $connectionTimeout, $RConEnable, $RConFloodProtect, $RConPassword, $RConCommand, $RConLogSize)
{
$serverResponse = "";
$output = "";
$s = "";
$RConLog = "";
$RConLog2 = "";

if ($RConPassword != "" && $RConCommand != "")
	{
		$output .= '';

		$fp = fsockopen("udp://" . $serverIPAddress, $serverPort, $errno, $errstr, 30);
		fwrite($fp, str_repeat(chr(255),4) . 'RCon ' . $RConPassword . ' ' . $RConCommand);
		$s='';
		stream_set_timeout($fp, $connectionTimeout);
		while (false !== ($char = fgetc($fp))) {
			$s .= $char;
		}
		fclose($fp);

		if($s != "")
		{
		    $serverResponse = $s;
		    //Check for exploits in the response that might trigger some PHP code
			$serverResponse = str_replace("<?", 'EXPLOIT REMOVED ', $serverResponse);
			$serverResponse = str_replace("?>", 'EXPLOIT REMOVED ', $serverResponse);
			$serverResponse = str_replace("*/", 'EXPLOIT REMOVED ', $serverResponse);

			//Replace line breaks with spaces for the RCon log only
			$newRConLogEntry = str_replace(chr(0x0A), '\n', $serverResponse);

		    //Validate the rest!
		    $serverResponse = stringValidator($serverResponse, "", "");

		    //Now we format the remaining data in a readable fashion
			$serverResponse = str_replace(str_repeat(chr(255),4) . 'print', '', $serverResponse);
			$serverResponse = str_replace(chr(0x0A), '<br />', $serverResponse);
			//This next line apparently replaces spaces with....spaces? Not sure who added that but I'm commenting it out
			//$serverResponse = str_replace(chr(0x20), ' ', $serverResponse);

		}
		else
		{
		    $serverResponse = 'No response from server at ' . $serverIPAddress . ':' . $serverPort . '!';
		    $newRConLogEntry = $serverResponse;
		}

		$output .= $serverResponse;
	}


	//Log time!
    $RConLog2 = file_get_contents("logs/" . $dynamicIPAddressPath . "RConLog.php");

    //Trim off the PHP tags and comment markers at the beginning and end of the file
    $RConLog2 = substr($RConLog2, 183, count($RConLog2) - 8);

    //If there are too many lines, truncate them
    $RConLogArray = explode("\n", $RConLog2);
    $RConLogArray = array_slice($RConLogArray, 0, $RConLogSize);

    $RConLog2 = implode("\n", $RConLogArray);

    //Assemble the new log entry.
    $RConLog = date(DATE_RFC2822) . "  Client IP Address: " . $_SERVER['REMOTE_ADDR'] . "  Command: " . $_POST["command"] . "  Response: " . $newRConLogEntry . $RConLog2;

    //Check for exploits before writing the new entry to the log. The command hasn't been validated yet, so this *must* happen a second time
	$RConLog = str_replace("<?", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("?>", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("*/", 'EXPLOIT REMOVED ', $RConLog);

    //Assemble the new log entry. This is the log, so validating anything other than what was already validated is a bad idea.
    $RConLog = RConLogHeader() . "\n" . $RConLog . "\n*/ ?> ";

    //Write the newly appended RCon log to a file
    file_put_contents("logs/" . $dynamicIPAddressPath . "RConLog.php", $RConLog);

    return $output;
}

function RConLogHeader()
{
    $output = "<?php \n echo '<h3 class=" . '"errorMessage"' . ">RConLog.php can only be viewed with direct file system access.<br />Download it and open it in a text editor.</h3>';\n exit(); \n/*  LOG ENTRIES:\n";
    return $output;
}

function writeNewConfigFile()
{
$configBuffer = '<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the config file for ParaTracker.
// The only visual setting found here is the frame border.
// If you want to edit fonts and colors, they are found
// in ParaStyle.css and the ParaSkin.css files, not here.

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
// Default is 2 seconds.
$refreshTimeout = "2";


// VISUAL SETTINGS
// VISUAL SETTINGS

// This value is boolean. When this variable is set to any value other than Yes or 1, the
// frame image that overlays the tracker is disabled.
// Default is 0.
$disableFrameBorder = "0";


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for on the web server in the images/levelshots folder.
// If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

// For levelshots to fade, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three fading levelshots for mp/ffa5, the files would have to be in
// the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
// and ffa5_3.jpg

// ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
// and GIFs third. If no images are found, a placeholder image will be displayed instead.

// The following value will enable or disable fading levelshots. A value of 1 or "Yes" will allow them,
// and any other value with disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$fadeLevelshots = "1";

// This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the amount of time, in second, each levelshot will take to fade into the next one.
// Note that fades do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is .5 seconds.
$levelshotTransitionTime = ".5";

// This is the frame rate at which levelshots will transition. Higher values are smoother,
// and lower values are choppier. Values between 10 and 30 are good. A value of 1 will
// disable the fading and give a "slide show" feel.
// Any value below 1 is forbidden. Values above 60 are also forbidden.
// Default is 30 FPS.
$levelshotFPS = "30";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better. Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS

// This is the name of the game being tracked; I.E. Jedi Academy, Jedi Outcast, Call Of Duty 4, etc.
// It is displayed underneath the server name in the top left corner of the tracker.
// For future-proofing, this value is left to you, the user.
// The levelshots derive their directory from this value, so make sure it is correct! For instance,
// a value of "Jedi Academy" means ParaTracker will look for levelshots in "images/levelshots/jedi academy"
// Default is "Jedi Academy"
$gameName = "Jedi Academy";

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
// This prevents pranksters from sending 50MB back, in the unlikely event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// Default is 4000 characters.
$maximumServerInfoSize = "4000";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and give
// an IP address, port number and visual theme ID in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=A&game=Jedi%20Academy"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that I have not yet found!
// If you do not understand what this feature is, DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "1";

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


// End of config file
?>';
file_put_contents('ParaConfig.php', $configBuffer);
}

?>