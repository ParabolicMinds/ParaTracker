<?php
echo "<!--";


//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

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
        echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
        exit();
    }
}


//Check to see if an update needs done, and do it
checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $refreshTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dynamicTrackerEnabled);


if (file_exists("info/" . $dynamicIPAddressPath . "serverDump.txt") && file_get_contents("info/" . $dynamicIPAddressPath . "serverDump.txt") != "")
{
//Server dump detected - connection assumed successful! Rendering a normal page.

//Insert tracker HTML here

$output = htmlDeclarations("ParaTracker - The Ultimate Quake III Server Tracker", "");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "levelshotJavascriptAndCSS.txt");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "rconParamScript.txt");


$output .= '</head>
<body class="ParaTrackerPage">
<div class="TrackerFrameCNoBG BackgroundColorImageC">
<div class="TrackerFrameC';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">';


$output .= '<div class="levelshotFrameWrapperC">
<div id="levelshotPreload" class="levelshotFrameC">


<div id="bottomLayerFade" class="levelshotFrameC">
<div id="ls" class="levelshotFrame';

if ($disableFrameBorder == 0)
{
$output .= ' levelshotCornerC';
}

$output .= '">
<div id="topLayerFade" class="levelshotFrameC levelshot1">';

if ($disableFrameBorder == 0)
{
    $output .= '<img src="images/tracker/Tracker_Skin_C_Side_Frame.png" width="300" height="225" alt="" />';
}

$output .= '</div>
</div>
</div>

</div>
</div>';

$output .= '

<div class="dataLevelshotSpacerC"></div>

';

$output .= '

<div class="dataFrameC">


<div class="serverFrameSpacerC"></div>
<div class="serverFrameC">
<span class="serverNameC">
' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_hostname.txt") . '
</span>
</div>


<div class="gameNameWrapperC"><div class="gamenameC gamenameSizeC">&nbsp;' . $gameName . '</div></div>


<div class="nameScorePingC">
<div class="playerNameC playerNameHeaderC">Name</div>
<div class="playerScoreC playerScoreHeaderC">Score</div>
<div class="playerPingC playerPingHeaderC">Ping</div>
</div>



<div class="playerListC">' . file_get_contents("info/" . $dynamicIPAddressPath . "playerList.txt") . '</div>

<div class="playerListDataSpacerC"></div>

<div class="bottomLeftDataWrapperC">

<div class="dataRowC">
<div class="mapnameC">Map: ' . file_get_contents("info/" . $dynamicIPAddressPath . "mapname.txt") . '</div>
</div>
<div class="dataRowC">
<div class="modnameC modnameGametypeCSize">Modname: ' . file_get_contents("info/" . $dynamicIPAddressPath . "modname.txt") . '</div>
<div class="gametypeC modnameGametypeCSize">Gametype: ' . file_get_contents("info/" . $dynamicIPAddressPath . "gametype.txt") . '</div>
</div>
<div class="dataRowC">
<div class="IPandPortC">IP: ' . $serverIPAddress . ':' . $serverPort . '<div class="blinkingCursorC">&nbsp;</div></div></div></div></div>
';

$output .= '<div class="trackerLogoRConParamTimerWrapperC">

<div class="trackerLogoSpacerC">

<div class="ParamRConSpacerC"></div>

<div class="ParamRConSpacer2C"></div>

<div class="RConParamC ';

if ($RConEnable == 1)
{
    $output .= 'RConC" onclick="rcon_window();';
}

$output .= '" ></div>


<div class="ParamC RConParamC" onclick="param_window();"></div>

</div>

<div class="paramTimerSpacerC"></div>

<div class="reloadTimerOpacityC reloadTimerC reloadTimerTextC hiddenTimer" title="Click to cancel auto-refresh" id="refreshTimerDiv" onclick="toggleReload()"></div>';
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");


$output .= '</div>
';


//Players: ' . file_get_contents("info/" . $dynamicIPAddressPath . "playerCount.txt") . '/' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_maxclients.txt") . '




$output .= '
</div>
</div>
</body>
</html>';


}
else
{
//Could not connect to the server! Display error page.
//Insert "Could not connect" HTML here.


$output =  htmlDeclarations("ParaTracker - Could Not Connect To Server", "");
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");
$output .= '<script type="text/javascript">
reconnectTimer = setTimeout("makeReconnectButtonVisible()", ' . ($floodProtectTimeout * 1000 + 100) . ');
</script>
</head><body class="ParaTrackerPage">


<div class="TrackerFrameCNoBG BackgroundColorImageC">
<div class="TrackerFrameC';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">';


$output .= '<div class="levelshotFrameWrapperC"></div>
<div class="dataLevelshotSpacerC"></div>';



$output .= '

';


$output .= '<div class="dataFrameC"><div class="noConnectionFrameC">

<br /><br />Could not connect<br />to server!<br /><br />' . file_get_contents("info/" . $dynamicIPAddressPath . "connectionErrorMessage.txt") . '<br /><br /><br />' . $serverIPAddress . ':' . $serverPort . '<div class="RConblinkingCursor">&nbsp;</div></div>

<div class="reconnectButtonWrapperC">
<div class="reconnectSpacerC">&nbsp;</div>
<div id="reconnectButton" class="reconnectButtonC hide" onclick="pageReload();"></div>
</div>
</div>';

$output .= '<div class="trackerLogoRConParamTimerWrapperC">

<div class="trackerLogoSpacerC">

<div class="ParamRConSpacerC"></div>

<div class="ParamRConSpacer2C"></div>

<div class="RConParamC"></div>


<div class="RConParamC" onclick="param_window();"></div>

</div>

<div class="paramTimerSpacerC"></div>

<div class="reloadTimerOpacityC reloadTimerC reloadTimerTextC hiddenTimer" title="Click to cancel auto-refresh" id="refreshTimerDiv" onclick="toggleReload()"></div>';
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");


$output .= '</div>
';




$output .= '
</div>
</div>
</body>
</html>';

}

echo "-->";

echo $output;

?>