<?php

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
	echo '-->:#:' . levelshotFinder("", $mapName, $levelshotFolder, $gameName, 1) . ':#:';

    if(strtolower($levelshotFolder) == "unknown")
    {
        //Invalid game. Terminate.
        return levelshotPlaceholder;
    }
}

?>
