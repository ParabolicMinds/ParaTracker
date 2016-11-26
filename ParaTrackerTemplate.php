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
<body class="ParaTrackerPage">';

/*
if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}
*/


$output .= '</body>
</html>';


}
else
{
//Could not connect to the server! Display error page.
$output =  htmlDeclarations("ParaTracker - Could Not Connect To Server", "");
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

/*
if ($disableFrameBorder == 1)
{
$output .= 'NoBG';
}
*/

$output .= '<div id="reconnectButton" class="reconnectButton hide" onclick="pageReload();"></div>';

$output .= '</body></html>';

file_put_contents('info/' . $dynamicIPAddressPath . 'param.html', "");
}

echo "-->";

echo $output;

?>