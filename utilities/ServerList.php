<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//This variable prevents a skin from being assigned to the output page
$calledFromElsewhere = "1";

//Check to see if ParaFunc was already executed
if(!isset($utilitiesPath))
{
    //We are in the utilities folder, so we have to back out one
    chdir("../");

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
}

if(!dynamicTrackerEnabled)
{
	displayError('--><h3>ParaTracker Dynamic must be enabled to use the server list!</h3>', "", "");
	exit();
}

// Specify the skin to use here
// The resolution of the skin will automatically be parsed and used
$paraTrackerSkin = 'Banner Ad - Dark';

$skinDimensions = getSkinFileDimensions($paraTrackerSkin);

$serverData = renderAllTrackedServers();

$output = htmlDeclarations('Server List', '../');
$output .= '<link rel="stylesheet" href="../css/ServerList.css" type="text/css" />';
$output .= '<script>trackerDimensions = { width:' . $skinDimensions[0] . ', height: ' . $skinDimensions[1] . '}</script>';
$output .= '<script>gameList = ' . JSONArray("", detectGameName()[0], 3) . '</script>';
$output .= '<script>data = ' . $serverData . '</script>
<script src="../js/ParaScript.js"></script></head>
<script src="../js/ServerList.js"></script></head>
<body>
<!--

<!--  WARNING: DO NOT USE THIS PAGE\'S JSON OUTPUT TO INTERFACE WITH PARATRACKER  -->
<!--            THIS PAGE DELIVERS CACHED DATA ONLY, FOR SPEED REASONS           -->
<!--           FOR UP-TO-DATE INFO, DIRECTLY TRACK A SINGLE GAME SERVER          -->
<!--



-->
';

$output .= '<h1><a href="https://' . webServerName . '/ParaTrackerDynamic.php" target="_blank" title="Click here to track your own game server"><span class="paraTrackerColor">' . trackerName() . '</span> <span class="paraTrackerVersionColor">' . strval(trackerVersion()) . '</span> Server List</a></h1>
<div id="gameSelectDiv"></div>
<div id="filterDiv"></div>
<div id="headerDiv"></div>
<div id="serverDiv"></div>
<div id="footerDiv"></div>';

$output .= '</body>
</html>
';

echo "-->" . $output;

?> 
