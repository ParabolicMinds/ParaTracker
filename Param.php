<?php
echo "<!-- ";
 
//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

$calledFromParam = "1";

//Check to see if Dynamic mode gave us an IP address. If it has, let's go into Dynamic mode.
if(isset($_GET["ip"]))
{
    $dynamicTrackerCalledFromCorrectFile = "1";
}


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

//ParaFunc already does the validation for everything, including the IP address. It should be fine to just check if a refresh is needed.
//If it isn't, the response will end up being parsed from the old data anyhow.
checkForAndDoUpdateIfNecessary();

if (file_exists("info/" . dynamicIPAddressPath . "serverDump.txt") && file_get_contents("info/" . dynamicIPAddressPath . "serverDump.txt") != "")
{
    echo "-->" . htmlDeclarations("", "") . file_get_contents("info/" . dynamicIPAddressPath . "param.txt");
}
else
{
    echo "-->" . htmlDeclarations("", "") . "</head><body><h3>Could not connect to game server!</h3><h4>" . $serverIPAddress . ":" . $serverPort . "</h4></body></html>";
}

?>