<?php
echo "<!--";

//This variable allows ParaFunc.php to see that we are running in dynamic mode
$dynamicTrackerCalledFromCorrectFile = "1";

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

if($dynamicTrackerEnabled != "1")
{
    echo "--><h3>Dynamic ParaTracker is disabled! If you wish to enable it,<br />first read the warnings in ParaConfig.php then PROCEED WITH CAUTION!</h3>";
    exit();
}

if($paraTrackerSkin != "A" && $paraTrackerSkin != "B" && $paraTrackerSkin != "C" && $paraTrackerSkin != "D" && $paraTrackerSkin != "E" && $paraTrackerSkin != "F" && $paraTrackerSkin != "G" && $paraTrackerSkin != "H")
{
    $paraTrackerSkin = "A";
}

include("ParaTracker" . $paraTrackerSkin . ".php");

?>