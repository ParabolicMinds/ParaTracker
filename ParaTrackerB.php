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
checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $refreshTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $filterOffendingServerNameSymbols, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dynamicTrackerEnabled);


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
<div class="TrackerFrameBNoBG BackgroundColorImageB">
<div class="TrackerFrameB';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">';


$output .= '<div class="trackerLogoRConParamTimerWrapperB">

<div class="trackerLogoSpacerB">

<div class="ParamRConSpacerB"></div>

<div class="ParamRConSpacer2B"></div>

<div class="RConParamB ';

if ($RConEnable == 1)
{
    $output .= 'RConB" onclick="rcon_window();';
}

$output .= '" ></div>


<div class="ParamB RConParamB" onclick="param_window();"></div>

</div>

<div class="paramTimerSpacerB"></div>

<div class="reloadTimerOpacityB reloadTimerB reloadTimerTextB hiddenTimer" title="Click to cancel auto-refresh" id="refreshTimerDiv" onclick="toggleReload()"></div>';
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");

$output .= '

</div>

<div class="dataFrameB">


<div class="serverFrameSpacerB"></div>
<div class="serverFrameB">
<span class="serverNameB">
' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_hostname.txt") . '
</span>
</div>


<div class="gameNameWrapperB"><div class="gamenameB gamenameSizeB">&nbsp;' . $gameName . '</div></div>


<div class="nameScorePingB">
<div class="playerNameB playerNameHeaderB">Name</div>
<div class="playerScoreB playerScoreHeaderB">Score</div>
<div class="playerPingB playerPingHeaderB">Ping</div>
</div>



<div class="playerListB">' . file_get_contents("info/" . $dynamicIPAddressPath . "playerList.txt") . '</div>

<div class="playerListDataSpacerB"></div>

<div class="bottomLeftDataWrapperB">

<div class="dataRowB">
<div class="mapnameB">Map: ' . file_get_contents("info/" . $dynamicIPAddressPath . "mapname.txt") . '</div>
</div>
<div class="dataRowB">
<div class="modnameB modnameGametypeBSize">Modname: ' . file_get_contents("info/" . $dynamicIPAddressPath . "modname.txt") . '</div>
<div class="gametypeB modnameGametypeBSize">Gametype: ' . file_get_contents("info/" . $dynamicIPAddressPath . "gametype.txt") . '</div>
</div>
<div class="dataRowB">
<div class="IPandPortB">IP: ' . $serverIPAddress . ':' . $serverPort . '<div class="blinkingCursorB">&nbsp;</div>
</div>
</div>
</div>


</div>

';


//Players: ' . file_get_contents("info/" . $dynamicIPAddressPath . "playerCount.txt") . '/' . file_get_contents("info/" . $dynamicIPAddressPath . "sv_maxclients.txt") . '

$output .= '

<div class="dataLevelshotSpacerB"></div>

';

$output .= '<div class="levelshotFrameWrapperB">
<div id="levelshotPreload" class="levelshotFrameB">


<div id="bottomLayerFade" class="levelshotFrameB">
<div id="ls" class="levelshotFrame';

if ($disableFrameBorder == 0)
{
$output .= ' levelshotCornerB';
}

$output .= '">
<div id="topLayerFade" class="levelshotFrameB levelshot1" onclick="levelshotClick()">';

if ($disableFrameBorder == 0)
{
    $output .= '<img src="images/tracker/Tracker_Skin_B_Side_Frame.png" width="300" height="225" alt="" />';
}

$output .= '</div>
</div>
</div>

</div>
';



$output .= '</body>
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


<div class="TrackerFrameBNoBG BackgroundColorImageB">
<div class="TrackerFrameB';

if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}

$output .= '">';
$output .= '<div class="trackerLogoRConParamTimerWrapperB">

<div class="trackerLogoSpacerB">

<div class="ParamRConSpacerB"></div>

<div class="ParamRConSpacer2B"></div>

<div class="RConParamB" ></div>

</div>

<div class="paramTimerSpacerB"></div>

<div class="reloadTimerOpacityB reloadTimerB reloadTimerTextB hiddenTimer" title="Click to cancel auto-refresh" id="refreshTimerDiv" onclick="toggleReload()"></div>';
$output .= file_get_contents("info/" . $dynamicIPAddressPath . "refreshCode.txt");

$output .= '

</div>';


$output .= '<div class="dataFrameB"><div class="noConnectionFrameB">

<br /><br />Could not connect<br />to server!<br /><br />' . file_get_contents("info/" . $dynamicIPAddressPath . "connectionErrorMessage.txt") . '<br /><br /><br />' . $serverIPAddress . ':' . $serverPort . '<div class="RConblinkingCursor">&nbsp;</div></div>

<div class="reconnectButtonWrapperB">
<div class="reconnectSpacerB">&nbsp;</div>
<div id="reconnectButton" class="reconnectButtonB hide" onclick="pageReload();"></div>
</div>

</div></div>';

$output .= '</body></html>';

}

echo "-->";

echo $output;

?>