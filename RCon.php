<?php
echo "<!-- ";

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

$output = htmlDeclarations("Rcon Page", "");

$output .= '</head><body class="RConPage">';

if ($RConEnable == "1")
{
if ($serverIPAddress == "Invalid")
{
    $output = "Invalid IP address detected! Cannot continue.<br />Check the IP address in ParaConfig.php.";
    exit();
}
else
{
    $RConCommand = stringValidator($_POST["command"], "", "");
    $RConPassword = stringValidator($_POST["password"], "", "");

    $output .= '<form action="RCon.php" method="post" onsubmit="disableRConForm()">
    <div class="RConPasswordCommand RConPasswordCommandSize">
    Command:<input id="commandTextField" class="RConInput" size="35" type="text" value="' . $RConCommand . '" name="command" />
    &nbsp;Password:<input id="passwordTextField" class="RConInput" type="password" name="password" value="" />
    <input id="submitButton" type="submit" value=" Send " />
    </div>
    </form>
    <div class="RConServerResponseFrame"><div class="RConServerAddressResponse"><br />Server Address: ' . $serverIPAddress . ":" . $serverPort . '<br /><br />Server Response:<br /><br /></div><div class="RConServerResponse">';

echo $RConMaximumMessageSize;

    if(strlen($RConCommand) > $RConMaximumMessageSize)
    {
        $output .= 'RCon command exceeds maximum size! Limit is ' . $RConMaximumMessageSize . ' characters.<br />If you need the limit raised, change it in ParaConfig.php.';
    }
    else
    {
        if(strlen($RConPassword) > $RConMaximumMessageSize)
        {
            $output .= 'RCon password exceeds maximum size! Limit is ' . $RConMaximumMessageSize . ' characters.<br />If you need the limit raised, change it in ParaConfig.php.';
        }
        else
        {

    if ($RConCommand != "" && $RConPassword != "")
    {
        $lastRefreshTime = "0";
        if (file_exists("info/RConTime.txt"))
        {
            $lastRefreshTime = numericValidator(file_get_contents("info/RConTime.txt"), "", "", 0);
        }

        if ($lastRefreshTime + $RConFloodProtect < time())
        {
            file_put_contents("info/RConTime.txt", time());
            $output .= sendRecieveRConCommand($serverIPAddress, $serverPort, $connectionTimeout, $RConEnable, $RConFloodProtect, $RConPassword, $RConCommand, $RConLogSize);
        }
        else
        {

        $timeRemaining = intval(abs(time() - $RConFloodProtect - $lastRefreshTime)) + 1;
        if ($timeRemaining > $RConFloodProtect)
        {
            $timeRemaining = intval($RConFloodProtect) + 1;
        }
        $javascriptTimeInterval = intval($RConFloodProtect);
        $output .= '<script type="text/javascript">
        var floodProtectTimer = ' . intval($javascriptTimeInterval) . ';
        var initialFloodProtectTimer = ' . floor($RConFloodProtect) . ';
        var timeRemaining = ' . intval($timeRemaining) .';
        RConTimer = setTimeout("RConFloodProtectTimer()", 50);
        function RConFloodProtectTimer()
	    {
            if (timeRemaining == 0)
            {
                document.getElementById("RConTimeoutTimer").innerHTML = "RCon is ";
                document.getElementById("RConTimeoutText").innerHTML = "ready!";
            }
            if (timeRemaining == 1)
            {
                timeRemaining--;
                document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
                document.getElementById("RConTimeoutText").innerHTML = "seconds remaining.";
                RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
            }
            if (timeRemaining == 2)
            {
                timeRemaining--;
                document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
                document.getElementById("RConTimeoutText").innerHTML = "second remaining.";
                RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
            }
            if (timeRemaining > 2)
            {
                timeRemaining--;
                document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
                document.getElementById("RConTimeoutText").innerHTML = "seconds remaining.";
                RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
            }
	    }

        </script>Please wait ' . $RConFloodProtect . ' seconds between commands.<br /><div id="RConTimeoutTimer" class="RConTimeoutTimer"></div> <div id="RConTimeoutText" class="RConTimeoutTimer"></div>';
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

//Done with RCon Stuff! Remove the HTML comment and give the output.
echo "-->";

$output .= '<div class="RConblinkingCursor">&nbsp;</div></div></div>';

echo $output;

?>		
</body></html>