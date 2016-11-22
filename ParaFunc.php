<?php

function do_update()
{

    include 'ParaConfig.php';
    
    //First and firemost, let's see if the user put any invalid stuff in the config file.
    //After all, garbage in, garbage out.

    //To validate booleans:
    //$variableName = booleanValidator($variableName, defaultValue);

    //To evaluate numeric values:
    //$variableName = numericValidator($variableName, min, max, default);

    $serverIPAddress = ipAddressValidator($serverIPAddress);
    $serverPort = numericValidator($serverPort, 1, 65535, 29070);

    $floodProtectTimeout = numericValidator($floodProtectTimeout, 5, 1200, 10);
    $connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2);

    $disableFrameBorder = booleanValidator($disableFrameBorder, 0);

    $fadeLevelshots = booleanValidator($fadeLevelshots, 1);
    $levelshotDisplayTime = numericValidator($levelshotDisplayTime, 1, 15, 3);
    $levelshotTransitionTime = numericValidator($levelshotTransitionTime, 0.1, 5, 0.5);
    $levelshotFPS = numericValidator($levelshotFPS, 1, 60, 20);
    $maximumLevelshots = numericValidator($maximumLevelshots, 1, 99, 20);

    $RConEnable = booleanValidator($RConEnable, 0);
    $newWindowSnapToCorner = booleanValidator($newWindowSnapToCorner, 0);

    //Done validating. On with the good stuff!
	$fp = fsockopen("udp://" . $serverIPAddress, $serverPort, $errno, $errstr, $connectionTimeout);
	fwrite($fp, str_repeat(chr(255),4) . "getstatus\n");
	$s='';
	stream_set_timeout($fp, $connectionTimeout);

	while (false !== ($char = fgetc($fp)))
	{
		$s .= $char;
	}
	fclose($fp);

//	file_put_contents('info/serverdump.txt', $s); //Debug line

	if(strlen($s))
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
			$cvar_array_single[$cvarCount++] = array("name" => stringValidator($cvar_name, "", ""), "value" => stringValidator($cvar_value, "", ""));
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

		//This buffer holds the CVAR HTML code
		$buf='<!DOCTYPE html><html lang="en"><head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="../ParaStyle.css" type="text/css" />
		<link rel="stylesheet" href="../Config-DoNotEdit.css" type="text/css" />
		<title>Server Cvars</title>
		<script src="../ParaScript.js"></script>
		</head>
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
		$buf .= '</table></td></tr></table><h6 class="center">ParaTracker version 1.0 RC - Copyright &copy; 1837 Rick Astley. No rights reserved. Void where prohibited. Your mileage may vary. Please drink and drive responsibly.</h6></body></html>';
		file_put_contents('info/param.html', $buf);


		//This buffer holds the player list
		$playerListbuffer = '<table class="playerTable">';
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
				$playerListbuffer .= "\n" . '<tr class="playerRow' . $c . '"><td class="playerName">'. colorize(substr($player_name,0,$k));
				$playerListbuffer .= '</td>' . "\n" . '<td class="playerScore">' . $player["score"] . '</td><td class="playerPing">' . $player["ping"] . '</td></tr>';
				$c++;
				if($c > 2) $c = 1;
			}
			$playerListbuffer .= "\n";
		} else {
			$playerListbuffer .= '<tr><td class="noPlayersOnline">&nbsp;' . $noPlayersOnlineMessage . '</td></tr>';
		}
		$playerListbuffer .= '</table>';
		$buf3='';
		file_put_contents('info/playerlist2.html', $playerListbuffer);


		//The following chunk of code detects how many levelshots exist on the server, and passes them on to the stylesheet and JavaScript for fading.
		$levelshotBuffer = '';

		$levelshotCount = 0;
		$levelshotIndex = 1;
	    $foundLevelshot = 0;
		do
		{

		    //Reset this value every iteration so we can check to see if levelshots are being found
		    $foundLevelshot = 0;

		    //Check for a PNG first
		    if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.png'))
		    {
		        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        		$foundLevelshot = 1;
		    }
		    else
		    {
		    //Failed to find a PNG, so let's check for a JPG
		        if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.jpg'))
		        {
		            $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		            $foundLevelshot = 1;
		        }
		        else
		        {
		            //Also failed to find a JPG, so let's check for a GIF
		            if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.gif'))
		            {
		                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '_' . $levelshotIndex . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
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
		                	if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . $levelshotIndex . '.png'))
		            		{
		                        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '.png");background-size: 300px 225px;background-repeat: no-repeat;}';
        				    }
        				    else
        				    {
        				        //And checking for a JPG again:
		                	    if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . $levelshotIndex . '.jpg'))
		                	    {
		                	        $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '.jpg");background-size: 300px 225px;background-repeat: no-repeat;}';
		                	    }
		                	    else
		                	    {
		                	        //Lastly...checking for a GIF.
		                	        if(file_exists('images/levelshots/' . $cvars_hash["mapname"] . $levelshotIndex . '.gif'))
		                	        {
		                                $levelshotBuffer .= '.levelshot' . $levelshotIndex . '{background: url("images/levelshots/' . $cvars_hash["mapname"] . '.gif");background-size: 300px 225px;background-repeat: no-repeat;}';
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

//This code prevents the Javascript below from seeing a value of 0 levelshots when none are found.
//There will always be a minimum of one levelshot. A placeholder is used if none is found.
if ($levelshotCount == 0)
{
    $levelshotCount = 1;
}

       		//This buffer holds the main tracker page HTML and Javascript code
			$buf2 = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<link rel="stylesheet" href="Config-DoNotEdit.css" type="text/css" />
<link rel="stylesheet" href="ParaStyle.css" type="text/css" />
<title>ParaTracker</title>
<script src="ParaScript.js"></script>
        <script type="text/javascript"><!--
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
	                count=0;
	                mode=1;
	                timer = setTimeout("fadelevelshot()", ' . 1000 * $levelshotDisplayTime . ');
	            }
	    }

timer = setTimeout("firstexecution()", 100);

		function param_window()
		{
			mywindow = window.open("info/param.html", "mywindow", "location=0,titlebar=0,menubar=0,status=0,titlebar=0,scrollbars=1,width=600,height=700");';

			if ($newWindowSnapToCorner == "1")
			{
			$buf2 .= 'mywindow.moveTo(0, 0);';
			}

		$buf2 .= '}';
		
		if ($RConEnable == 1)
		{
		$buf2 .= 'function rcon_window()
		{
			myotherwindow = window.open("RCon.php", "myotherwindow", "location=0,titlebar=0,menubar=0,status=0,titlebar=0,scrollbars=1,width=780,height=375");
			';

			if ($newWindowSnapToCorner == "1")
			{
			$buf2 .= 'myotherwindow.moveTo(0, 0);';
			}
			
		$buf2 .= '}';
		}
		
		$buf2 .= '//-->
</script>
		

<style>
' . $levelshotBuffer . '
</style>
</head>

<div class="TrackerFrameNoBG BackgroundColorImage">
<div class="TrackerFrame';

if ($disableFrameBorder == 1)
{
$buf2 .= 'NoBG';
}

$buf2 .= '">


<div class="trackerLogoSpacer">
&nbsp;
</div>

<div class="dataFrame">

<div class="serverFrameSpacer"></div>
<div class="serverFrame">
<span class="serverName">
' . colorize($cvars_hash["sv_hostname"]) . '

</span>
<br />

<span class="gameTitle">&nbsp;' . $gameName . '</span>

</div>

<div class="nameScorePing">
<table><tr><td class="playerName">Name</td><td class="playerScore">&nbsp;Score</td><td class="playerPing">&nbsp;Ping</td><td></td></tr></table>
</div>

<div class="playerList">' . $playerListbuffer . '

</div>

<div class="rconParamSpacer"></div>

<div class="playersRconParamFrame">

<div class="playerCountFrame">
<table class="playersAlign"><tr><td class="playerCount">
Players: ' . $player_count . '/' . $cvars_hash["sv_maxclients"] . '</td></tr></table>
</div>


<div class="rconParamFrame">';

		if ($RConEnable == 1)
		{
		    $buf2 .= '<div class="rconButton" onclick="rcon_window();"></div>';
		}
        $buf2 .= '
</div>
<div class="rconParamFrame">

<div class="paramButton" onclick="param_window();"></div>" />


</div>
</div>
</div>


<div class="middleSpacer"></div>

<div class="levelshotFrameWrapper">
<div id="levelshotPreload" class="levelshotFrame">


<div id="bottomLayerFade" class="levelshotFrame">
<div id="ls" class="levelshotFrame';

if ($disableFrameBorder == 0)
{
$buf2 .= ' levelshotCorner';
}

$buf2 .= '">
<div id="topLayerFade" class="levelshotFrame levelshot1">';

if ($disableFrameBorder == 0)
{
    $buf2 .= '<img src="images/tracker/corner-tr.gif" width="300" height="225" alt="" />';
}

$buf2 .= '</div>
</div>
</div>

</div>

<div class="levelshotSpacer"></div>

<div class="matchData"><div class="mapName"><table class="noPadding1"><tr><td>&nbsp;Map: <span class="color7">' . colorize($cvars_hash["mapname"]) . '</span></td></tr></table></div>
<div class="gametype"><table class="noPadding1"><tr><td>&nbsp;Gametype: ' . $gametypes[$cvars_hash["g_gametype"]] . '</td></tr></table></div>
<br />
<div class="modName"><table class="noPadding1"><tr><td>&nbsp;Mod Name: ' . $cvars_hash["gamename"] . '</td></tr></table></div>
<br />
<div class="IPAddress"><table class="noPadding2"><tr><td>&nbsp;Server IP: ' . $serverIPAddress . ':' . $serverPort . '</td><td class="blinkingCursor"></td></tr></table>


</div>


</div>

</div>
</div>
</div>

</body>
</html>';
		file_put_contents('info/tracker_page.html', $buf2);

	}
	else
	{
		$buf =  '<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"/>
<link rel="stylesheet" href="ParaStyle.css" type="text/css" />
<link rel="stylesheet" href="Config-DoNotEdit.css" type="text/css" />
<script src="ParaScript.js"></script>
</head><body>

<div class="TrackerFrameNoBG BackgroundColorImage">
<div class="TrackerFrame';

if ($disableFrameBorder == 1)
{
$buf .= 'NoBG';
}

$buf .= '">


<div class="trackerLogoSpacer">
&nbsp;
</div>

<div class="dataFrame">
<div class="serverFrameSpacer"></div>
<div class="couldNotConnectFrame">
<div class="couldNotConnectText">

<script type="text/javascript">
function makeReconnectButtonVisible()
{
	document.getElementById("reconnectButton").className = "reconnectButton";
}
reconnectTimer = setTimeout("makeReconnectButtonVisible()", ' . ($floodProtectTimeout * 1000 + 100) . ');
</script>

<br /><br /><br />Could not connect<br />to server!<br /><br /><br />' . $serverIPAddress . ':' . $serverPort . '<div class="RConblinkingCursor">&nbsp;</div></div></div>
<div class="rconParamSpacer"></div>
<div class="playersRconParamFrame">
<div class="playerCountFrame">

</div>

<div class="reconnectFrame">
<div id="reconnectButton" class="reconnectButton hide" onclick="pageReload();"></div></div></div></div></div></div>';


$buf .= '
</body></html>';

		file_put_contents('info/tracker_page.html', $buf);
		file_put_contents('info/param.html', "");
	}

}

function checkTimeDelay($connectionTimeout)
{
$lastRefreshTime = "0";
if (file_exists("info/time.txt"))
{
    $lastRefreshTime = numericValidator(file_get_contents("info/time.txt"), "", "", "wait");
}
else
{
  	file_put_contents("info/time.txt", "wait");
    $lastRefreshTime = "0";
}

$i = 0;
$sleepTimer = "0.1";

while ($lastRefreshTime == "wait" && $i < $connectionTimeout + $sleepTimer * 3)
{
    //info/Time.txt indicated that a refresh is in progress. Wait a little bit so it can finish. If it goes too long, we'll continue on, and force a refresh.
    usleep($sleepTimer * 1000000);
    $lastRefreshTime = numericValidator(file_get_contents("info/time.txt"), "", "", "wait");
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

function ipAddressValidator($input)
{
    //Remove whitespace
    $input = trim($input);

    //Use a PHP function to check validity
    if (!filter_var($input,FILTER_VALIDATE_IP) && $input != "localhost")
    {
        $input = "Invalid";
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
                $output .= "None";
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

function sendRecieveRConCommand($serverIPAddress, $serverPort, $connectionTimeout, $RConEnable, $RConFloodProtect, $RConPassword, $RConCommand, $RConLogSize)
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


	//Log time! Let's see if a log file exists
    if (file_exists("info/RConLog.php"))
{
    //Houston, we have a log file. Read it in.
    $RConLog2 = file_get_contents("info/RConLog.php");

    //Trim off the PHP tags and comment markers at the beginning and end of the file
    $RConLog2 = substr($RConLog2, 8, count($RConLog2) - 7);

    //If there are too many lines, truncate them
    $RConLogArray = explode("\n", $RConLog2);
    $RConLogArray = array_slice($RConLogArray, 0, $RConLogSize);

    $RConLog2 = implode("\n", $RConLogArray);
}

    //Assemble the new log entry.
    $RConLog = date(DATE_RFC2822) . "  IP Address: " . $_SERVER['REMOTE_ADDR'] . "  Command: " . $_POST["command"] . "  Response: " . $newRConLogEntry . $RConLog2;

    //Check for exploits before writing the new entry to the log. The command hasn't been validated yet, so this *must* happen a second time
	$RConLog = str_replace("<?", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("?>", 'EXPLOIT REMOVED ', $RConLog);
	$RConLog = str_replace("*/", 'EXPLOIT REMOVED ', $RConLog);

    //Assemble the new log entry. This is the log, so validating anything other than what was already validated is a bad idea.
    $RConLog = "<?php /*\n" . $RConLog . "\n*/ ?>";

    //Write the newly appended RCon log to a file
    file_put_contents("info/RConLog.php", $RConLog);

    return $output;
}

function writeNewConfigFile()
{
$configBuffer = '<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

//This is the config file for ParaTracker.
//If you want to edit fonts and colors,
//they are found in ParaStyle.css, not here.

//ONLY modify the variables defined below, between the double quotes!
//Changing anything else can break the tracker!

//If you are not sure what you are doing, just change the IP address and port to match
//your game server, and let the default settings take care of the rest.

//If you find any exploits in the code, please bring them to my attention immediately!
//Thank you and enjoy!



// NETWORK SETTINGS
// NETWORK SETTINGS


//This is the IP Address of the server. Do not include the port number!
//By default, and for security, this value is empty. If ParaTracker is launched without a value here,
//it will display a message telling the user to check config.php before running.
$serverIPAddress = "";

//Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
//The default port number for Jedi Outcast is 28070.
//If an invalid entry is given, this value will default to 29070.
$serverPort = "29070";


//This variable limits how many seconds are required between each snapshot of the server.
//This prevents high traffic on the tracker from bogging down the game server it is tracking.
//ParaTracker forces a minimum value of 5 seconds between snapshots. Maximum is 1200 seconds.
//Default is 10 seconds.
$floodProtectTimeout = "10";

//This value is the number of seconds ParaTracker will wait for a response from the game server
//before timing out. Note that, every time the tracker gets data from the server, it will ALWAYS
//wait the full delay time. Server connections are UDP, so the tracker cannot tell when the data
//stream is complete. After this time elapses, ParaTracker will assume it has all the data and
//parse the data. If your web page has a slow response time to the game server, set this value
//higher. ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
//Not recommended to go above 5 seconds, as people will get impatient and leave.
//This setting also affects RCon wait times.
//Default is 2.5 seconds.
$connectionTimeout = "2.5";


// VISUAL SETTINGS
// VISUAL SETTINGS

//This value is boolean. When this variable is set to any value other than Yes or 1, the
//frame image that overlays the tracker is disabled.
//Default is 0.
$disableFrameBorder = "0";


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

//Levelshots will be searched for on the web server in the images/levelshots folder.
//If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

//For levelshots to fade, they will have to be named with _1, _2, and _3 at the end of the file name.
//For instance, to have three fading levelshots for mp/ffa5, the files would have to be in
//the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
//and ffa5_3.jpg

//ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
//and GIFs third. If no images are found, a placeholder image will be displayed instead.


//The following value will enable or disable fading levelshots. A value of 1 or "Yes" will allow them,
//and any other value with disable them. If this is disabled, only the first levelshot will show.
//Default value is 1.
$fadeLevelshots = "1";

//This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
//Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
//Default is 3 seconds.
$levelshotDisplayTime = "3";

//This is the amount of time, in second, each levelshot will take to fade into the next one.
//Note that fades do not work in some browsers, like Internet Explorer 8.
//Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
//Default is .5 seconds.
$levelshotTransitionTime = ".5";

//This is the frame rate at which levelshots will transition. Higher values are smoother,
//and lower values are choppier. Values between 10 and 30 are good. A value of 1 will
//disable the fading and give a "slide show" feel.
//Any value below 1 is forbidden. Values above 60 are also forbidden.
//Default is 20 FPS.
$levelshotFPS = "20";

//The following value is the maximum number of levelshots that can be used. Keep in mind that
//more levelshots is not always better. Minimum is 1, maximum is 99.
//Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS


//This is the name of the game being tracked; I.E. Jedi Academy, Jedi Outcast, Call Of Duty 4, etc.
//It is displayed underneath the server name in the top left corner of the tracker.
//For future-proofing, this value is left to you, the user.
//Default is "Jedi Academy."
$gameName = "Jedi Academy";


//No Players Online Message
//This message displays in place of the player list when nobody is online.
//Default is "No players online."
$noPlayersOnlineMessage = "No players online.";


// RCON SETTINGS
// RCON SETTINGS

//This value will enable or disable RCon.
//A value of Yes or 1 will enable it, and any other value will disable it.
//Disabled by default for security.
$RConEnable = "0";

//RCon flood protection forces the user to wait a certain number of seconds before sending another command.
//Note that this is not user-specific; if someone else is using your RCon, you may have to wait a bit to
//send the command. Minimum is 10 seconds, maximum is 3600.
//Default is 20 seconds.
$RConFloodProtect = "20";


//RCon events are logged in RConLog.php for security. This variable will determine
//the maximum number of lines that will be stored in the log file before the old
//entries are truncated. Minimum is 100 lines. Maximum is 100000.
//Default is 1000 lines.
$RConLogSize = 1000;


// POPUP WINDOW SETTINGS
// POPUP WINDOW SETTINGS

//This value is boolean. When the RCon and PARAM buttons are clicked, the popup
//window will snap to the top left corner of the screen by default. When this
//variable is set to any value other than Yes or 1, the behavior is disabled.
//Default is 0.
$newWindowSnapToCorner = "0";


// GAMETYPE NAMES
// GAMETYPE NAMES

//The following is an array of gametypes. These are used when ParaTracker
//tries to identify a gametype. The array is listed with gametype 1 in the
//first value, gametype 2 in the second value, and so on. If you do not know
//what this is, do not change it, as ParaTracker cannot correct this if it
//is broken. The default value is:
//$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");

$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");


// DMFLAGS
// DMFLAGS

//The following is an array used to determine what the dmflags parameter controls.
//dmflags is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for dmflags 1, the second value is for dmflags 2, the third is for
//dmflags 4, the fourth is dmflags 8, the fifth is dmflags 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");

$dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");


// WEAPON FLAGS
// WEAPON FLAGS

//The following is an array used to determine what the g_weaponDisable parameter controls.
//g_weaponDisable is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for g_weaponDisable 1, the second value is for g_weaponDisable 2, the
//third is for g_weaponDisable 4, the fourth is g_weaponDisable 8, the fifth is
//g_weaponDisable 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "FC1 Flechette", "Rocket Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");

$weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "Golan Arms FC1 Flechette", "Merr-Sonn Portable Missile Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");


// FORCE POWER FLAGS
// FORCE POWER FLAGS

//The following is an array used to determine what the g_forcePowerDisable parameter controls.
//g_forcePowerDisable is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for g_forcePowerDisable 1, the second value is for g_forcePowerDisable 2, the
//third is for g_forcePowerDisable 4, the fourth is g_forcePowerDisable 8, the fifth is
//g_forcePowerDisable 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");

$forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");


//End config file
?>';
file_put_contents('ParaConfig.php', $configBuffer); }

?>