<!DOCTYPE html><html lang="en">
<head>
<meta charset="utf-8"/>
<link rel="stylesheet" href="ParaStyle.css" type="text/css" />
<link rel="stylesheet" href="Config-DoNotEdit.css" type="text/css" />
<title>RCon Console</title>
<script src="ParaScript.js"></script>
</head>
<body class="RConPage">

<?php
echo "<!-- ";

//Prevent users from aborting the page! This will reduce load on both the game server, and the web server.
ignore_user_abort(true);

//Declaring these variables before loading the config file will avoid error messages
$RConEnable = "";

//Check to see if the library file exists, and load it in.
if (file_exists("ParaFunc.php"))
{
    include 'ParaFunc.php';
}
else
{
    echo "--> <h3>ParaFunc.php not found - cannot continue!</h3> <!--";
    exit();
}

if (file_exists("ParaConfig.php"))
{
    include 'ParaConfig.php';
}
else
{
    echo "--> <h3>ParaConfig.php not found!</h3><br />Writing default config file...<!--";
    writeNewConfigFile();
    if (file_exists("ParaConfig.php"))
    {
        echo "<!-- <h4>Default ParaConfig.php successfully written!<br />Please add an IP Address and port to it.</h4> <!--";
    }
    else
    {
        echo "<!-- <h4>Failed to write new config file!</h4> <!--";
    }
    
    exit();
}



//IMMEDIATELY validate the necessary input from the config file.
$serverIPAddress = ipAddressValidator($serverIPAddress);
$serverPort = numericValidator($serverPort, 1, 65535, 29070);
$connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2.5);
$RConEnable = booleanValidator($RConEnable, 0);
$RConFloodProtect = numericValidator($RConFloodProtect, 10, 3600, 20);
$RConLogSize = numericValidator($RConLogSize, 100, 100000, 1000);

//If someone was silly and made the connection timeout longer than the floodprotect, we should force them to be equal
if ($connectionTimeout > $RConFloodProtect)
{
    $RConFloodProtect = $connectionTimeout;
}

$output = "";

if ($RConEnable == "1")
{
if ($serverIPAddress == "Invalid")
{
    $output = "Invalid IP address detected! Cannot continue.<br />Check the IP address in ParaConfig.php.";
}
else
{
    $RConCommand = stringValidator($_POST["command"], "", "");
    $RConPassword = stringValidator($_POST["password"], "", "");


    $output .= '<form action="RCon.php" method="post"><div class="RConPasswordCommand RConPasswordCommandSize">Password:<input class="RConInput" type="password" name="password" value="" />
    &nbsp;Command:<input class="RConInput" size="35" type="text" value="' . $RConCommand . '" name="command" />
    <input type="submit" value=" Send " />
    </div>
    </form>
    <div class="RConServerResponseFrame"><div class="RConServerAddressResponse"><br />Server Address: ' . $serverIPAddress . ":" . $serverPort . '<br /><br />Server Response:<br /><br /></div><div class="RConServerResponse">';

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
        echo " " . $javascriptTimeInterval . "  " . $timeRemaining . " " . intval($timeRemaining) . "\n"; //Debug line
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