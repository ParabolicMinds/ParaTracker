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
    echo '-->:#:' . levelshotFinderLite($mapName, $levelshotFolder) . ':#:';
}

function levelshotFinderLite($mapName, $levelshotFolder)
{
    if(strtolower($levelshotFolder) == "unknown")
    {
        //Invalid game. Terminate.
        return "images/missing.gif";
    }

                $levelshotFolder = "images/levelshots/" . strtolower($levelshotFolder) . "/";
                $levelshotCheckName = strtolower($mapName);

                $levelshotBuffer = '';

                $levelshotCount = 0;
                $levelshotIndex = 1;

                    //Check for a PNG first
                    if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.png'))
                    {
                        $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.png';
                    }
                    else
                    {
                    //Failed to find a PNG, so let's check for a JPG
                        if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.jpg'))
                        {
                            $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.jpg';
                        }
                        else
                        {
                            //Also failed to find a JPG, so let's check for a GIF
                            if(file_exists($levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.gif'))
                            {
                                $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '_' . $levelshotIndex . '.gif';
                            }
                            else
                            {
                                //Checking for a PNG again:
                                        if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.png'))
                                        {
                                        $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '.png';
                                            }
                                            else
                                            {
                                                //And checking for a JPG again:
                                            if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.jpg'))
                                            {
                                                $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '.jpg';
                                            }
                                            else
                                            {
                                                //Lastly...checking for a GIF.
                                                if(file_exists($levelshotFolder . $levelshotCheckName . $levelshotIndex . '.gif'))
                                                {
                                                $levelshotBuffer = $levelshotFolder . $levelshotCheckName . '.gif';
                                                }
                                                else
                                                {
                                                    //Could not find a levelshot! Use the default 'missing picture' image and close out
                                                    $levelshotBuffer = "images/missing.gif";
                                                }
                                            }
                                        }
                            }
                        }
                    }

    return $levelshotBuffer;
}

?>