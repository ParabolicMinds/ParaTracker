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
    include 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

if($dynamicTrackerEnabled != "1")
{
    displayError('<h3 class="errorMessage">Dynamic ParaTracker is disabled! If you wish to enable it,<br />first read the warnings in ParaConfig.php then PROCEED WITH CAUTION!</h3>');
}


include("ParaTracker" . $paraTrackerSkin . ".php");

?>