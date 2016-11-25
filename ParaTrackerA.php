<?php
echo "<!--";

//Prevent users from aborting the page! This will reduce load on both the game server, and the web server.
ignore_user_abort(true);

//ParaFunc.php MUST exist, or the page must terminate!
if (file_exists("ParaFunc.php"))
{
    include 'ParaFunc.php';
}
else
{
    echo "--> <h3>ParaFunc.php not found - cannot continue!</h3> <!--";
    exit();
}


//Check the time delay between refreshes
checkTimeDelay($connectionTimeout);

checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $floodProtectTimeout, $connectionTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dmflags, $forcePowerFlags, $weaponFlags);


$output = htmlDeclarations("ParaTracker", "");
$output .= file_get_contents("info/refreshCode.txt");
$output .= file_get_contents("info/levelshotJavascriptAndCSS.txt");
$output .= file_get_contents("info/rconParamScript.txt");


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
' . file_get_contents("info/sv_hostname.txt") . '

</span>
<br />

<span class="gameTitle">&nbsp;' . $gameName . '</span>

</div>

<div class="nameScorePing nameScorePingSize">
<div class="playerName playerNameSize">Name</div><div class="playerScore playerScoreSize">&nbsp;Score</div><div class="playerPing playerPingSize">&nbsp;Ping</div>
</div>

<div class="playerList">' . file_get_contents("info/playerList.txt") . '

</div>

<div class="rconParamSpacer"></div>

<div class="playersRconParamFrame">

<div class="playerCountFrame">
<table class="playersAlign"><tr><td class="playerCount">
Players: ' . file_get_contents("info/playerCount.txt") . '/' . file_get_contents("info/sv_maxclients.txt") . '</td></tr></table>
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

<div class="matchData"><div class="mapName"><table class="noPadding1"><tr><td>&nbsp;Map: <span class="color7">' . file_get_contents("info/mapname.txt") . '</span></td></tr></table></div>
<div class="gametype"><table class="noPadding1"><tr><td>&nbsp;Gametype: ' . $gametypes[file_get_contents("info/g_gametype.txt")] . '</td></tr></table></div>
<br />
<div class="modName"><table class="noPadding1"><tr><td>&nbsp;Mod Name: ' . file_get_contents("info/gamename.txt") . '</td></tr></table></div>
<br />
<div class="IPAddress"><table class="noPadding2"><tr><td>&nbsp;Server IP: ' . $serverIPAddress . ':' . $serverPort . '</td><td class="blinkingCursor"></td></tr></table>


</div>


</div>

</div>
</div>
</div>

</body>
</html>';

echo "-->";

echo $output;

?>