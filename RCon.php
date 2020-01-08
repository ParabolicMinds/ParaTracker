<?php
echo "<!-- ";

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

$calledFromRCon = "1";

//ParaFunc.php MUST exist, or the page must terminate!
if (file_exists("ParaFunc.php"))
{
    include_once 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

//Make sure the rcon log file exists
if(!checkFileExistence("RConLog.php", logPath . makeDynamicAddressPath($serverIPAddress, $serverPort))) return 'Could not create RCon log file!';

if (trim(file_get_contents(logPath . $dynamicIPAddressPath . "RConLog.php")) == "")
{
    file_put_contents(logPath . $dynamicIPAddressPath . "RConLog.php", logHeader("RConLog.php") . logFooter());
}

$output = htmlDeclarations("Rcon - " . $serverIPAddress . " - ParaTracker", "");

if(scrollShaftColor != "")
{
    $output .= '<style>::-webkit-scrollbar-track{background-color: rgba(' . convertToRGBA(scrollShaftColor) . ', 100);}</style>';
}
if(scrollThumbColor != "")
{
    $output .= '<style>::-webkit-scrollbar-thumb{background-color: rgba(' . convertToRGBA(scrollThumbColor) . ', 100);}</style>';
}

$output .= '<script src="js/ParaScript.js">var runSetup = 0;</script></head><body class="RConPage">';

if (RConEnable == "1")
{
if ($serverIPAddress == "Invalid")
{
    $output = "Invalid IP address detected! Cannot continue.<br />Check the IP address in ParaConfig.php.";
    exit();
}
else
{
    if(isset($_POST["command"]))
    {
        $RConCommand = $_POST["command"];
    }
    else
    {
        $RConCommand = "";
    }
    if(isset($_POST["rconPassword"]))
    {
        $RConPassword = $_POST["rconPassword"];
    }
    else
    {
        $RConPassword = "";
    }

    $output .= '<form action="RCon.php';
    $output .= '?' . $_SERVER['QUERY_STRING'];

    if(isset($_GET["customSkin"]))
    {
        $output .= '&customSkin=' . customSkin;
    }

    $output .= '" method="post" onsubmit="disableRConForm()">
    <div class="RConPasswordCommand RConPasswordCommandSize">
    Command:<input id="commandTextField" class="RConInput" size="35" type="text" value="' . stringValidator($RConCommand, "", "") . '" name="command" />
    &nbsp;Password:<input id="passwordTextField" class="RConInput" type="password" name="rconPassword" value="" />
    <input id="submitButton" type="submit" value=" Send " />
    </div>
    </form>
    <script>commandTextField.focus()</script>
    <div class="RConServerResponseFrame"><div class="RConServerAddressResponse"><br />Server Address: ' . $serverIPAddress . ":" . $serverPort . '<br /><br />Server Response:<br /><br /></div><div class="RConServerResponse RConServerResponseScroll">';

    if(strlen($RConCommand) > RConMaximumMessageSize)
    {
        $output .= 'RCon command exceeds maximum size! Limit is ' . RConMaximumMessageSize . ' characters.<br />If you need the limit raised, change it in ParaConfig.php.';
    }
    else
    {
        if(strlen($RConPassword) > RConMaximumMessageSize)
        {
            $output .= 'RCon password exceeds maximum size! Limit is ' . RConMaximumMessageSize . ' characters.<br />If you need the limit raised, change it in ParaConfig.php.';
        }
        else
        {

    if ($RConCommand != "" && $RConPassword != "")
    {
        $lastRefreshTime = "0";
        if (file_exists(infoPath . $dynamicIPAddressPath . "RConTime.txt"))
        {
            $lastRefreshTime = numericValidator(file_get_contents(infoPath . $dynamicIPAddressPath . "RConTime.txt"), "", "", 0);
        }

        if ($lastRefreshTime + RConFloodProtect < time())
        {
            file_put_contents(infoPath . $dynamicIPAddressPath . "RConTime.txt", time());
            $output .= sendReceiveRConCommand($serverIPAddress, $serverPort, $lastRefreshTime, $RConPassword, $RConCommand);
        }
        else
        {

        $timeRemaining = intval(abs(time() - RConFloodProtect - $lastRefreshTime)) + 1;
        if ($timeRemaining > RConFloodProtect)
        {
            $timeRemaining = intval(RConFloodProtect) + 1;
        }
        $javascriptTimeInterval = intval(RConFloodProtect);
        $output .= '<script type="text/javascript">
        var floodProtectTimer = ' . intval($javascriptTimeInterval) . ';
        var initialFloodProtectTimer = ' . floor(RConFloodProtect) . ';
        var timeRemaining = ' . intval($timeRemaining) .';
        RConTimer = setTimeout("RConFloodProtectTimer()", 50);
        </script>Please wait ' . RConFloodProtect . ' seconds between commands.<br /><div id="RConTimeoutTimer" class="RConTimeoutTimer"></div> <div id="RConTimeoutText" class="RConTimeoutTimer"></div>';
        }
    }
    else
    {
        if ($RConPassword == "" && $RConCommand != "")
        {
            $output .= "Enter a password!";
        }
        if ($RConPassword != "" && $RConCommand == "")
        {
            $output .= "Enter a command!";
        }
    }

        }
    }

}

}
else
{
$output .= '<h2>RCon is disabled on this tracker!</h2><br />RCon must be enabled in ParaConfig.php.';
}

$output .= '<span class="blinkingCursor" style="animation-duration: 1s; animation-fill-mode: forwards; animation-iteration-count: infinite; animation-name: blink;">_</span></div></div>';

//Done with RCon Stuff! Remove the HTML comment and give the output.
echo "-->";

echo $output;

?>		
</body></html>
