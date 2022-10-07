<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


$calledFromElsewhere = 1;

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

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

if(!analyticsEnabled)
{
	//If analytics is disabled, exit now.
	exit();
}

if(!isset($_GET["gameName"]) || !isset($_GET["mapName"]))
{
    exit();
}

//$gameName must be parsed from the URL here
$inputGameName = rawurldecode($_GET["gameName"]);
$inputGameName = makeFunctionSafeName($inputGameName);

$gameName = "";

//$gameName must match a game from the known game list, or we are being exploited. Terminate.
$gameList = detectGameName("");
$count = count($gameList[0]);
for($i = 0; $i < $count; $i++)
{
	if(makeFunctionSafeName($gameList[0][$i]) == $inputGameName)
	{
		$gameName = $inputGameName;
	}
}

if($gameName == "")
{
	exit();
}

//$mapName must be parsed from the URL here
$mapName = rawurldecode($_GET["mapName"]);


if(function_exists($gameName) && is_callable($gameName))
{
    //Call the function
    $GameInfoData = $gameName(array(), array());
    $levelshotFolder = $GameInfoData[1];
    $defaultLevelshot = $GameInfoData[2];
    
    //levelshotfinder returns an array, so we need to get index 0
	$output = levelshotFinder("", $mapName, $levelshotFolder, $gameName, $defaultLevelshot, 1);

	//Strip this down to just the first entry
	if(is_array($output)) $output = $output[0];

	if(strtolower($levelshotFolder) == "unknown" || $output == "")
    {
		//Invalid game or no levelshots found - see if there's a default levelshot
		if($defaultLevelshot != "")
		{
			$output = $defaultLevelshot;
		}
		else
		{
			//Nothing at all...we'll just have to use the placeholder
			$output = levelshotPlaceholder;
		}
    }

	$output = '-->:#:' . $output . ':#:';
	echo $output;
}

?>
