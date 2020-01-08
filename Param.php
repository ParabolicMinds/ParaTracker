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
    include_once 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

//Check to see if an update needs done, and do it
checkForAndDoUpdateIfNecessary($dynamicIPAddressPath);

//Render the param page
$output = renderParamPage($serverIPAddress, $serverPort);

//Echo $output and terminate
echo "-->" . $output;
?>