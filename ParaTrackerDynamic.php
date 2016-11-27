<?php
echo "<!--";

//This variable allows ParaFunc.php to see that we are running in dynamic mode
$dynamicTrackerCalledFromCorrectFile = "1";

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//ParaFunc.php MUST exist, or the page must terminate!
if (file_exists("ParaFunc.php"))
{
    if (!isset($_GET["ip"]))
    {
        echo "--><h3>No IP address given! In dynamic mode, the IP address must be given in the URL. For example:<br /></h3><p>" . $_SERVER['SERVER_NAME'] . "/ParaTrackerDynamic.php?<strong>ip=127.143.12.88</strong>&port=29070&skin=A&game=Jedi Academy</p>";
        exit();
    }
    include 'ParaFunc.php';
}
else
{
    echo "--> <h3>ParaFunc.php not found - cannot continue!</h3> <!--";
    exit();
}

if($dynamicTrackerEnabled != "1")
{
    echo "--><h3>Dynamic ParaTracker is disabled! If you wish to enable it,<br />first read the warnings in ParaConfig.php then PROCEED WITH CAUTION!</h3>";
    exit();
}


include("ParaTracker" . $paraTrackerSkin . ".php");

?>