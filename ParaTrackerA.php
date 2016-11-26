<?php
echo "<!--";

//Check to see if we're running in Dynamic mode. If we are, DO NOT load ParaFunc.php, as it
//has already been loaded.
if(!isset($dynamicTrackerCalledFromCorrectFile))
{
    //We are not running in dynamic mode, so load ParaFunc.php
    //ParaFunc.php MUST exist, or we must terminate!
    if (file_exists("ParaFunc.php"))
    {
        include 'ParaFunc.php';
    }
    else
    {
        echo "--> <h3>ParaFunc.php not found - cannot continue!</h3> <!--";
        exit();
    }
}

//Check the time delay between refreshes. Make sure we wait if need be
checkTimeDelay($connectionTimeout, $refreshTimeout, $dynamicIPAddressPath);

//Do an update
checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dmflags, $forcePowerFlags, $weaponFlags);


if (file_exists("info/" . $dynamicIPAddressPath . "serverDump.txt") && file_get_contents("info/" . $dynamicIPAddressPath . "serverDump.txt") != "")
{
//Connection was successful! Rendering a normal page.

$output = htmlDeclarations("ParaTracker - The Ultimate Quake 3 Server Tracker", "");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "levelshotJavascriptAndCSS.txt");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "rconParamScript.txt");


$output .= '</head>
<body class="ParaTrackerPage">
<div class="TrackerFrameNoBG BackgroundColorImage">
<div class="TrackerFrame';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">


<div class="trackerLogoSpacer">
&nbsp;
</div>

<div class="dataFrame">

<div class="serverFrameSpacer"></div>
<div class="serverFrame">
<span class="serverName">
' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_hostname.txt") . '

</span>
<br />

<span class="gameTitle">&nbsp;' . $gameName . '</span>

</div>

<div class="nameScorePing nameScorePingSize">
<div class="playerName playerNameSize">Name</div><div class="playerScore playerScoreSize">&nbsp;Score</div><div class="playerPing playerPingSize">&nbsp;Ping</div>
</div>

<div class="playerList">' . file_get_contents("info/" . $dynamicIPAddressPath . "playerList.txt") . '

</div>

<div class="rconParamSpacer"></div>

<div class="playersRconParamFrame">

<div class="playerCountFrame">
<table class="playersAlign"><tr><td class="playerCount">
Players: ' . file_get_contents("info/" . $dynamicIPAddressPath . "playerCount.txt") . '/' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_maxclients.txt") . '</td></tr></table>
</div>


<div class="rconParamFrame">';

		if ($RConEnable == 1)
		{
		    $output .= '<div class="rconButton" onclick="rcon_window();"></div>';
		}
        $output .= '
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
$output .= ' levelshotCorner';
}

$output .= '">
<div id="topLayerFade" class="levelshotFrame levelshot1">';

if ($disableFrameBorder == 0)
{
    $output .= '<img src="images/tracker/corner-tr.gif" width="300" height="225" alt="" />';
}

$output .= '</div>
</div>
</div>

</div>

<div class="levelshotSpacer"></div>

<div class="matchData"><div class="mapName"><table class="noPadding1"><tr><td>&nbsp;Map: <span class="color7">' . file_get_contents("info/" . $dynamicIPAddressPath . "mapname.txt") . '</span></td></tr></table></div>
<div class="gametype"><table class="noPadding1"><tr><td>&nbsp;Gametype: ' . $gametypes[file_get_contents("info/" . $dynamicIPAddressPath . "g_gametype.txt")] . '</td></tr></table></div>
<br />
<div class="modName"><table class="noPadding1"><tr><td>&nbsp;Mod Name: ' . file_get_contents("info/" . $dynamicIPAddressPath . "gamename.txt") . '</td></tr></table></div>
<br />
<div class="IPAddress"><table class="noPadding2"><tr><td>&nbsp;Server IP: ' . $serverIPAddress . ':' . $serverPort . '</td><td class="blinkingCursor"></td></tr></table>


</div>


</div>

</div>
</div>
</div>

</body>
</html>';


}
else
{

//Could not connect to the server! Display error page.
$output = htmlDeclarations("ParaTracker - Could Not Connect To Server", "");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");
$output .= '<script type="text/javascript">
function makeReconnectButtonVisible()
{
	document.getElementById("reconnectButton").className = "reconnectButton";
}
reconnectTimer = setTimeout("makeReconnectButtonVisible()", ' . ($floodProtectTimeout * 1000 + 100) . ');
</script>
</head><body class="ParaTrackerPage">


<div class="TrackerFrameNoBG BackgroundColorImage">
<div class="TrackerFrame';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">


<div class="trackerLogoSpacer">
&nbsp;
</div>

<div class="dataFrame">
<div class="serverFrameSpacer"></div>
<div class="couldNotConnectFrame">
<div class="couldNotConnectText">

<br /><br /><br />Could not connect<br />to server!<br /><br />' . file_get_contents("info/" . $dynamicIPAddressPath . "connectionErrorMessage.txt") . '<br /><br /><br />' . $serverIPAddress . ':' . $serverPort . '<div class="RConblinkingCursor">&nbsp;</div></div></div>
<div class="rconParamSpacer"></div>
<div class="playersRconParamFrame">
<div class="playerCountFrame">

</div>

<div class="reconnectFrame">
<div id="reconnectButton" class="reconnectButton hide" onclick="pageReload();"></div></div></div></div></div></div>';


$output .= '
</body></html>';

file_put_contents('info/' . $dynamicIPAddressPath . 'param.html', "");
}

echo "-->";

echo $output;

?>