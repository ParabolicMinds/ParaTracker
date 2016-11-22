<?php
echo "<!--";

//Prevent users from aborting the page! This will reduce load on both the game server, and the web server.
ignore_user_abort(true);

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


//Check a few variables we need here, just to make sure the input is valid.
//The rest of them must be checked below, when a refresh is done.
//These ones will also need re-checked below.

$floodProtectTimeout = numericValidator($floodProtectTimeout, 5, 1200, 10);
$connectionTimeout = numericValidator($connectionTimeout, 1, 15, 2.5);

//Check the time delay
checkTimeDelay($connectionTimeout);

$lastRefreshTime = numericValidator(file_get_contents("info/time.txt"), "", "", "0");


//Check to see if we have a valid IP address before continuing
$serverIPAddress = ipAddressValidator($serverIPAddress);
if ($serverIPAddress == "Invalid")
{
    echo "-->Invalid IP address detected! Cannot continue.<br />Check the IP address in ParaConfig.php.";
}
else
{

if ($lastRefreshTime + $floodProtectTimeout < time())
{
  	file_put_contents("info/time.txt", "wait");
  	doUpdate();
   	file_put_contents("info/time.txt", time());
}


echo "-->";

//Remove this when all is done
include 'info/trackerPageA.txt';



}

?>