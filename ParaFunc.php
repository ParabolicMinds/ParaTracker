<?php

//Prevent users from aborting the page! This will reduce load on both the game server, and the web server.
ignore_user_abort(true);

function versionNumber()
{
    //Return a string of the version number
    return("1.0 RC");
}

if (file_exists("ParaConfig.php"))
{
    include 'ParaConfig.php';
}
else
{
    echo "--> <h3>ParaConfig.php not found!</h3>Writing default config file...<!-- ";
    writeNewConfigFile();
    if (file_exists("ParaConfig.php"))
    {
        echo "--> <h3>Default ParaConfig.php successfully written!<br />Please add an IP Address and port to it.</h3>";
    }
    else
    {
        echo "--> <h3>Failed to write new config file!</h3>";
    }
    exit();
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
if (!isset($dynamicTrackerCalledFromCorrectFile))
{
    $dynamicTrackerCalledFromCorrectFile = "0";
}
$dynamicTrackerCalledFromCorrectFile = booleanValidator($dynamicTrackerCalledFromCorrectFile, 0);
$dynamicTrackerEnabled = booleanValidator($dynamicTrackerEnabled, 0);


if($dynamicTrackerEnabled == "1" && $dynamicTrackerCalledFromCorrectFile == "1")
{
    $serverIPAddress = ipAddressValidator($_GET["ip"], "", $dynamicTrackerEnabled);
    $serverPort = numericValidator($_GET["port"], 1, 65535, 29070);
    $paraTrackerSkin = stringValidator(strtoupper($_GET["skin"]), "", "A");
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
$levelshotFPS = numericValidator($levelshotFPS, 1, 60, 20);
$maximumLevelshots = numericValidator($maximumLevelshots, 1, 99, 20);

$gameName = stringValidator($gameName, "", "Jedi Academy");
$noPlayersOnlineMessage = stringValidator($noPlayersOnlineMessage, "", "No players online.");

$enableAutoRefresh = booleanValidator($enableAutoRefresh, 1);
//Have to validate this one twice to make sure it isn't lower than the floodprotect limit
$autoRefreshTimer = numericValidator($autoRefreshTimer, 10, 300, 30);
$autoRefreshTimer = numericValidator($autoRefreshTimer, $floodProtectTimeout, 300, 30);
$maximumServerInfoSize = numericValidator($maximumServerInfoSize, 2000, 50000, 4000);

$RConEnable = booleanValidator($RConEnable, 0);
$RConMaximumMessageSize = numericValidator($RConMaximumMessageSize, 20, 10000, 100);

$RConFloodProtect = numericValidator($RConFloodProtect, 10, 3600, 20);
//Have to validate this one twice to make sure it isn't lower than connectionTimeout
$RConFloodProtect = numericValidator($RConFloodProtect, $connectionTimeout, 3600, 20);
$RConLogSize = numericValidator($RConLogSize, 100, 100000, 1000);

$newWindowSnapToCorner = booleanValidator($newWindowSnapToCorner, 0);

//The IP address has already been validated, so we can use it for a directory name
$dynamicIPAddressPath = $serverIPAddress . "-" . $serverPort . "/";

//Add some checks to make sure we have directories for the stuff
checkDirectoryExistence("info/", "");
checkDirectoryExistence($dynamicIPAddressPath, "info/");

checkDirectoryExistence("logs/", "");
checkDirectoryExistence($dynamicIPAddressPath, "logs/");

//And now let's check to make sure we have access to the file system to write all the files we need. 
checkForMissingFiles($dynamicIPAddressPath);

function checkForMissingFiles($dynamicIPAddressPath)
{
    $output = "0";
    checkFileExistence("connectionErrorMessage.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("g_gametype.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("gamename.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("levelshotJavascriptAndCSS.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("mapname.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("param.html", "info/" . $dynamicIPAddressPath);
    checkFileExistence("playerCount.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("playerList.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("rconParamScript.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("RConTime.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("refreshCode.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("serverDump.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("sv_hostname.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("sv_maxclients.txt", "info/" . $dynamicIPAddressPath);
    checkFileExistence("time.txt", "info/" . $dynamicIPAddressPath);
    return $output;
}

function checkFileExistence($filename, $dynamicIPAddressPath)
{
    if (!file_exists($dynamicIPAddressPath . $filename))
    {
        file_put_contents($dynamicIPAddressPath . $filename, "");
        if (!file_exists($dynamicIPAddressPath . $filename))
        {
            echo '--><h3>Failed to create file ' . $dynamicIPAddressPath . $filename . '!<br />Make sure ParaTracker has file system access, and that there is space to write files!</h3>';
            exit();
        }
    }
    return 1;
}

function checkDirectoryExistence($dirname, $dynamicIPAddressPath)
{
    if (!file_exists($dynamicIPAddressPath . $dirname))
    {
    echo "  " . $dynamicIPAddressPath . "  " . $dirname . "  ";
        mkdir($dynamicIPAddressPath . $dirname);
    }
    if (!file_exists($dynamicIPAddressPath . $dirname))
    {
        echo '--><h3>Failed to create directory ' . $dynamicIPAddressPath . $dirname .' in ParaTracker folder!<br />Cannot continue without file system access!</h3>';
    exit();
    }
}

function checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dmflags, $forcePowerFlags, $weaponFlags)
{

    $lastRefreshTime = numericValidator(file_get_contents("info/" . $dynamicIPAddressPath . "time.txt"), "", "", "0");


    if ($serverIPAddress == "Invalid")
    {
        echo "-->Invalid IP address detected! Cannot continue.<br />Check the IP address in ParaConfig.php.";
        exit();
    }
    else
    {

        if ($lastRefreshTime + $floodProtectTimeout < time())
        {
            file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", "wait");
            
            doUpdate($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dmflags, $forcePowerFlags, $weaponFlags);
            
            file_put_contents("info/" . $dynamicIPAddressPath . "time.txt", time());
        }

    }

}

function doUpdate($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dmflags, $forcePowerFlags, $weaponFlags)
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
	    echo '--> <h2>Received too much data!</h2><h4>' . strlen($s) . ' characters received, the limit is ' . $maximumServerInfoSize . '</h4><br />Check to see if you are connected to the correct server or increase $maximumServerInfoSize in ParaConfig.php.';
	    exit();
	}

//This file is used for determining if the server connection was successful
file_put_contents("info/" . $dynamicIPAddressPath . "serverDump.txt", $s);

	if($errstr == "")
	{
	    $errstr = "No response in " . $connectionTimeout . " seconds.";
	}
	file_put_contents("info/" . $dynamicIPAddressPath . "connectionErrorMessage.txt", stringValidator($errstr, "", ""));

	if(strlen($s))
	{
	    //Server responded!
	    //Now, we call a function to parse the data
	    $dataParserReturn = dataParser($s, $dynamicIPAddressPath);
	    //Organize the data that came back in the array
		$cvar_array_single = $dataParserReturn[0];
		$cvars_hash = $dataParserReturn[1];
		$player_array = $dataParserReturn[2];
		$playerParseCount = $dataParserReturn[3];

		cvarList($serverIPAddress, $serverPort, $dynamicIPAddressPath, $cvar_array_single, $dmflags, $forcePowerFlags, $weaponFlags);

		$player_count = playerList($dynamicIPAddressPath, $player_array, $playerParseCount, $noPlayersOnlineMessage);
		file_put_contents("info/" . $dynamicIPAddressPath . "playerCount.txt", $player_count);

		//The following function detects how many levelshots exist on the server, and passes a buffer of information back, the final count of levelshots, and whether they fade or not
		$levelshotBufferArray = levelshotfinder($cvars_hash["mapname"], $maximumLevelshots, $fadeLevelshots);
		$levelshotBuffer = $levelshotBufferArray[0];
		$levelshotCount = $levelshotBufferArray[1];

		autoRefreshScript($dynamicIPAddressPath, $enableAutoRefresh, $autoRefreshTimer);

		levelshotJavascriptAndCSS($dynamicIPAddressPath, $levelshotBuffer, $enableAutoRefresh, $autoRefreshTimer, $fadeLevelshots, $levelshotCount, $levelshotTransitionTime, $levelshotFPS, $levelshotDisplayTime);

		paramRConJavascript($dynamicIPAddressPath, $RConEnable, $newWindowSnapToCorner);

	}

}

function dataParser($s, $dynamicIPaddressPath)
{
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

		file_put_contents('info/' . $dynamicIPaddressPath . 'sv_hostname.txt', colorize($cvars_hash["sv_hostname"]));
		file_put_contents('info/' . $dynamicIPaddressPath . 'sv_maxclients.txt', $cvars_hash["sv_maxclients"]);
		file_put_contents('info/' . $dynamicIPaddressPath . 'g_gametype.txt', $cvars_hash["g_gametype"]);
		file_put_contents('info/' . $dynamicIPaddressPath . 'gamename.txt', colorize($cvars_hash["gamename"]));
		file_put_contents('info/' . $dynamicIPaddressPath . 'mapname.txt', colorize($cvars_hash["mapname"]));

		return(array($cvar_array_single, $cvars_hash, $player_array, $playerParseCount));
}

function cvarList($serverIPAddress, $serverPort, $dynamicIPAddressPath, $cvar_array_single, $dmflags, $forcePowerFlags, $weaponFlags)
{
		$buf = '</head>
		<body class="cvars_page">
		<span class="CVarHeading">Server Cvars</span><br />
		<span class="CVarServerAddress">' . $serverIPAddress . ":" . $serverPort . '</span><br /><br />
		<table class="FullSizeCenter"><tr><td><table><tr class="cvars_titleRow cvars_titleRowSize"><td class="Width270">Name</td><td class="Width270">Value</td></tr>' . "\n";
		$c = 1;
		
		

		foreach($cvar_array_single as $cvar)
		{
			$buf .= '<tr class="cvars_row' . $c . '"><td>' . $cvar['name'] . '</td><td>';
			if ((($cvar['name'] == 'sv_hostname') || ($cvar['name'] == 'gamename') || ($cvar['name'] == 'mapname')) && ((strpos(colorize($cvar['value']), $cvar['value'])) == FALSE))
			{
				$buf .= '<b>' . colorize($cvar['value']) . "</b><br />" . $cvar['value'];
			}
		    //Check for flags, and if they are present let's sort them into something useful...
			elseif ($cvar['name'] == 'dmflags')
			{
			    $buf .= bitvalueCalculator($cvar['name'], $cvar['value'], $dmflags);
			}
			elseif ($cvar['name'] == 'g_weaponDisable')
			{
			    $buf .= bitvalueCalculator($cvar['name'], $cvar['value'], $weaponFlags);
			}
			elseif ($cvar['name'] == 'g_forcePowerDisable')
			{
			    $buf .= bitvalueCalculator($cvar['name'], $cvar['value'], $forcePowerFlags);
			}
			else
			{
			$buf .= $cvar['value'];
			}
			$buf .= '</td></tr>' . "\n";
			$c++;
			if($c > 2) $c = 1;
		}
		$buf .= '</table></td></tr></table><h6 class="center">ParaTracker version ' . versionNumber() . ' - Copyright &copy; 1837 Rick Astley. No rights reserved. Void where prohibited. Your mileage may vary. Please drink and drive responsibly.</h6></body></html>';
		$buf = htmlDeclarations($cvar['name'] . "CVars", "../../") . $buf;
		file_put_contents('info/' . $dynamicIPAddressPath . 'param.html', $buf);
}

function playerList($dynamicIPAddressPath, $player_array, $playerParseCount, $noPlayersOnlineMessage)
{
		$playerListbuffer = '<div class="playerTable">';
		$player_count = 0;

		//FIXME: Doesn't work
		$playerNameCharacterLimit = 40;

		if($playerParseCount > 0)
		{
			$player_array = array_sort($player_array, "score", true);
			$c = 1;
			foreach($player_array as &$player)
			{
				$player_name = str_replace(array("\n", "\r"), '', $player["name"]);
				if (strlen($player_name) > $playerNameCharacterLimit)
				{
					$l = 0;
					for($k = 0; ($l < $playerNameCharacterLimit) && ($k < strlen($player_name)); $k++)
					{
						if(($player_name[$k] == '^') && (strpos("0123456789", $player_name[$k+1]) != FALSE))
						{
							$k++;
						}
						else
						{
							$l++;
						}
					}
				}
				else
				{
					$k = $playerNameCharacterLimit;
				}
				$player_count++;
				$playerListbuffer .= "\n" . '
<div class="playerRow' . $c . ' playerRowSize"><div class="playerName playerNameSize">'. colorize(substr($player_name,0,$k));
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
		$playerListbuffer .= '<div></div></div>';
		$buf3='';
		file_put_contents('info/' . $dynamicIPAddressPath . 'playerList.txt', $playerListbuffer);

		return $player_count;
}

function levelshotFinder($mapName, $maximumLevelshots, $fadeLevelshots)
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
		    if(file_exists('images/levelshots/' . $mapName . '_' . $levelshotIndex . '.png'))
		    {
		        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '_' . $levelshotIndex . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        		$foundLevelshot = 1;
		    }
		    else
		    {
		    //Failed to find a PNG, so let's check for a JPG
		        if(file_exists('images/levelshots/' . $mapName . '_' . $levelshotIndex . '.jpg'))
		        {
		            $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '_' . $levelshotIndex . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		            $foundLevelshot = 1;
		        }
		        else
		        {
		            //Also failed to find a JPG, so let's check for a GIF
		            if(file_exists('images/levelshots/' . $mapName . '_' . $levelshotIndex . '.gif'))
		            {
		                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '_' . $levelshotIndex . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
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
		                	if(file_exists('images/levelshots/' . $mapName . $levelshotIndex . '.png'))
		            		{
		                        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        				    }
        				    else
        				    {
        				        //And checking for a JPG again:
		                	    if(file_exists('images/levelshots/' . $mapName . $levelshotIndex . '.jpg'))
		                	    {
		                	        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	    }
		                	    else
		                	    {
		                	        //Lastly...checking for a GIF.
		                	        if(file_exists('images/levelshots/' . $mapName . $levelshotIndex . '.gif'))
		                	        {
		                                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $mapName . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
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
        $output .= '
<script type="text/javascript">
        pageReloadTimer = setTimeout("pageReload()", ' . ($autoRefreshTimer * 1000) . ');
        </script>
';
    }
file_put_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt", $output);
}

function levelshotJavascriptAndCSS($dynamicIPAddressPath, $levelshotBuffer, $enableAutoRefresh, $autoRefreshTimer, $fadeLevelshots, $levelshotCount, $levelshotTransitionTime, $levelshotFPS, $levelshotDisplayTime)
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
					count=0;
					mode=1;
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
				count=0;
				mode=0;
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
		$output = '<script type="text/javascript">function param_window()
		{
		paramWindow = window.open("info/' . $dynamicIPAddressPath . 'param.html", "paramWindow", "location=0,titlebar=0,menubar=0,status=0,titlebar=0,scrollbars=1,width=600,height=700");
';

			if ($newWindowSnapToCorner == "1")
			{
			$output .= 'paramWindow.moveTo(0, 0);';
			}

		$output .= '}
';

		if ($RConEnable == 1)
		{
		$output .= 'function rcon_window()
		{
		rconWindow = window.open("RCon.php", "rconWindow", "location=0,titlebar=0,menubar=0,status=0,titlebar=0,scrollbars=1,width=780,height=375");
';

			if ($newWindowSnapToCorner == "1")
			{
			$output .= 'rconWindow.moveTo(0, 0);';
			}
			
		$output .= '}
</script>';
		}
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
$sleepTimer = "0.15";

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
        foreach($a as $k=>$v)
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
        //Single quotes and double quotes are being removed as well.
        //Periods are being removed because they could be used along with slashes to
        //navigate away from the web page to other things.
        //Also removing colons to prevent http:// and stuff like that from getting through.
        //I know this is a bit over-protective, but safety first.
        $input = str_replace("<", "&lt;", $input);
        $input = str_replace(">", "&gt;", $input);
        $input = str_replace("{", "&#123;", $input);
        $input = str_replace("}", "&#125;", $input);
        $input = str_replace("=", "&#61;", $input);
        $input = str_replace("'", "&#39;", $input);
        $input = str_replace("\"", "&quot;", $input);
        $input = str_replace(".", "&#46;", $input);
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
    if($input == "" && $dynamicTrackerEnabled == "0")
    {
        echo '--><h3>Invalid IP address! ' . $input . '<br />Please add an IP Address and port to ParaConfig.php</h3>';
        exit();
    }

    //Remove whitespace
    $input = trim($input);
 
    //Use a PHP function to check validity
    if (!filter_var($input,FILTER_VALIDATE_IP) && $input != "localhost")
    {
        If($dynamicTrackerEnabled == “1”)
        {
            echo '--><h3>Invalid IP address! ' . $input . '<br />Check the IP address and try again.</h3>';
        }
        else
        {
            echo '--><h3>Invalid IP address! ' . $input . '<br />Check the IP address in ParaConfig.php</h3>';
        }
        exit();
    }
return $input;
}

function bitvalueCalculator($cvarName, $cvarValue, $arrayList)
{
            $output = '<script type="text/javascript">
            function ' . $cvarName . 'Click()
            {
                if (document.getElementById("' . $cvarName . '").className == "collapsedList")
                {
                    document.getElementById("' . $cvarName . '").className = "expandedList"
                }
                else
                {
                    document.getElementById("' . $cvarName . '").className = "collapsedList"
                }
            }
            </script>';

            $toBeExploded = "";
            $output .= '<div class="CVarExpandList" onclick="' . $cvarName . 'Click()"><b>' . $cvarValue . '</b><br /><i class="expandCollapse">(Click to expand/collapse)</i>';

            $index = count($arrayList);

            if ($index <= 0)
            {
                $output .= '<div id="' . $cvarName . '" class="collapsedList"><br /><i>None</i></div>';
            }
            elseif ($cvarValue >= pow(2, $index))
            {
                //Miscount detected! Array does not have enough values
                $output .= "<br />Miscount detected! Not enough values in the array for " . $cvarName . ". Check ParaConfig.php and add the missing values!";
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

    $output .= '<div id="' . $cvarName . '" class="collapsedList"><i>' . $iBlewItUp . '</i></div></div>';

    return $output;
}

function htmlDeclarations($pageTitle, $filePath)
{
    $pageTitle = stringValidator($pageTitle, "", "ParaTracker");
    $output = '<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="' . $filePath . 'Config-DoNotEdit.css" type="text/css" />
    <link rel="stylesheet" href="' . $filePath . 'ParaStyle.css" type="text/css" />
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
			$newRConLogEntry = str_replace("\n", ' ', $serverResponse);

			//Remove line breaks completely for the web page
			$serverResponse = str_replace("\n", '', $serverResponse);

		    //Validate the rest!
		    $serverResponse = stringValidator($serverResponse, "", "");
			$serverResponse = str_replace(chr(0x20), ' ', $serverResponse);
			$serverResponse = str_replace(chr(255) . chr(255) . chr(255) . chr(255) . 'print', '', $serverResponse);
			$serverResponse = str_replace(chr(0x0A), '', $serverResponse);
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
    $RConLog2 = substr($RConLog2, 8, count($RConLog2) - 7);

    //If there are too many lines, truncate them
    $RConLogArray = explode("\n", $RConLog2);
    $RConLogArray = array_slice($RConLogArray, 0, $RConLogSize);

    $RConLog2 = implode("\n", $RConLogArray);

    //Assemble the new log entry.
    $RConLog = date(DATE_RFC2822) . "  IP Address: " . $_SERVER['REMOTE_ADDR'] . "  Command: " . $_POST["command"] . "  Response: " . $newRConLogEntry . $RConLog2;

    //Check for exploits before writing the new entry to the log. The command hasn't been validated yet, so this *must* happen a second time
	$RConLog = str_replace("<?", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("?>", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("*/", 'EXPLOIT REMOVED ', $RConLog);

    //Assemble the new log entry. This is the log, so validating anything other than what was already validated is a bad idea.
    $RConLog = "<?php /*\n" . $RConLog . "\n*/ ?>";

    //Write the newly appended RCon log to a file
    file_put_contents("logs/" . $dynamicIPAddressPath . "RConLog.php", $RConLog);

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
// If you want to edit fonts and colors,
// they are found in ParaStyle.css, not here.

// ONLY modify the variables defined below, between the double quotes!
// Changing anything else can break the tracker!

// If you are not sure what you are doing, just change the IP address and port to match
// your game server, and let the default settings take care of the rest.

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
// Default is 20 FPS.
$levelshotFPS = "20";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better. Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS

// This is the name of the game being tracked; I.E. Jedi Academy, Jedi Outcast, Call Of Duty 4, etc.
// It is displayed underneath the server name in the top left corner of the tracker.
// For future-proofing, this value is left to you, the user.
// Default is "Jedi Academy."
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
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=A"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole!
// If you do not understand what this feature is, DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "0";


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
// Default is 0.
$newWindowSnapToCorner = "0";


// GAMETYPE NAMES
// GAMETYPE NAMES

// The following is an array of gametypes. These are used when ParaTracker
// tries to identify a gametype. The array is listed with gametype 1 in the
// first value, gametype 2 in the second value, and so on. If you do not know
// what this is, do not change it, as ParaTracker cannot correct this if it
// is broken. The default value is:
// $gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");

$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");


// DMFLAGS
// DMFLAGS

// The following is an array used to determine what the dmflags parameter controls.
// dmflags is a bit value, and each value in this array is entered in numeric order,
// from the lowest value to the highest. The only reason it is in the config file is
// in case ParaTracker is being used by some other game than Jedi Academy, so that you,
// the user, can change it to match whatever game you like.
// The first value is for dmflags 1, the second value is for dmflags 2, the third is for
// dmflags 4, the fourth is dmflags 8, the fifth is dmflags 16, and so on.
// Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
// If you do not know what this is, do not change it, as ParaTracker cannot correct this
// if it is broken. The default value is:
// $dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");

$dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");


// WEAPON FLAGS
// WEAPON FLAGS

// The following is an array used to determine what the g_weaponDisable parameter controls.
// g_weaponDisable is a bit value, and each value in this array is entered in numeric order,
// from the lowest value to the highest. The only reason it is in the config file is
// in case ParaTracker is being used by some other game than Jedi Academy, so that you,
// the user, can change it to match whatever game you like.
// The first value is for g_weaponDisable 1, the second value is for g_weaponDisable 2, the
// third is for g_weaponDisable 4, the fourth is g_weaponDisable 8, the fifth is
// g_weaponDisable 16, and so on.
// Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
// If you do not know what this is, do not change it, as ParaTracker cannot correct this
// if it is broken. The default value is:
// $weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "FC1 Flechette", "Rocket Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");

$weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "Golan Arms FC1 Flechette", "Merr-Sonn Portable Missile Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");


// FORCE POWER FLAGS
// FORCE POWER FLAGS

// The following is an array used to determine what the g_forcePowerDisable parameter controls.
// g_forcePowerDisable is a bit value, and each value in this array is entered in numeric order,
// from the lowest value to the highest. The only reason it is in the config file is
// in case ParaTracker is being used by some other game than Jedi Academy, so that you,
// the user, can change it to match whatever game you like.
// The first value is for g_forcePowerDisable 1, the second value is for g_forcePowerDisable 2, the
// third is for g_forcePowerDisable 4, the fourth is g_forcePowerDisable 8, the fifth is
// g_forcePowerDisable 16, and so on.
// Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
// If you do not know what this is, do not change it, as ParaTracker cannot correct this
// if it is broken. The default value is:
// $forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");

$forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");


// End of config file
?>';
file_put_contents('ParaConfig.php', $configBuffer);
}

?>