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
    include 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

//ParaFunc.php checks for the existence of the logs folder and for RConLog.php, otherwise we would check for it here.
if (trim(file_get_contents("logs/" . dynamicIPAddressPath . "RConLog.php")) == "")
{
    file_put_contents("logs/" . dynamicIPAddressPath . "RConLog.php", RConLogHeader() . "*/\n?>");
}

$output = htmlDeclarations("Rcon - " . serverIPAddress . " - ParaTracker", "");

$output .= '</head><body class="RConPage">';

if (RConEnable == "1")
{
if (serverIPAddress == "Invalid")
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
    if(isset($_POST["password"]))
    {
        $RConPassword = $_POST["password"];
    }
    else
    {
        $RConPassword = "";
    }

    $output .= '<form action="RCon.php';

    if(isset($_GET["ip"]))
    {
        $output .= '?ip=' . ipAddressValidator($_GET["ip"], "", $dynamicTrackerEnabled);

        if(isset($_GET["port"]))
        {
            $output .= '&port=' . numericValidator($_GET["port"], 1, 65535, 29070);
        }

        if(isset($_GET["skin"]))
        {
            $output .= '&skin=' . skinValidator($_GET["skin"]);
        }
    }

    $output .= '" method="post" onsubmit="disableRConForm()">
    <div class="RConPasswordCommand RConPasswordCommandSize">
    Command:<input id="commandTextField" class="RConInput" size="35" type="text" value="' . stringValidator($RConCommand, "", "") . '" name="command" />
    &nbsp;Password:<input id="passwordTextField" class="RConInput" type="password" name="password" value="" />
    <input id="submitButton" type="submit" value=" Send " />
    </div>
    </form>
    <div class="RConServerResponseFrame"><div class="RConServerAddressResponse"><br />Server Address: ' . serverIPAddress . ":" . serverPort . '<br /><br />Server Response:<br /><br /></div><div class="RConServerResponse RConServerResponseScroll">';

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
        if (file_exists("info/" . dynamicIPAddressPath . "RConTime.txt"))
        {
            $lastRefreshTime = numericValidator(file_get_contents("info/" . dynamicIPAddressPath . "RConTime.txt"), "", "", 0);
        }

        if ($lastRefreshTime + RConFloodProtect < time())
        {
            file_put_contents("info/" . dynamicIPAddressPath . "RConTime.txt", time());
            $output .= sendRecieveRConCommand($lastRefreshTime, $RConPassword, $RConCommand);
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

$output .= '<div class="RConblinkingCursor">&nbsp;</div></div></div>';

//Done with RCon Stuff! Remove the HTML comment and give the output.
echo "-->";

echo $output;

?>		
</body></html>