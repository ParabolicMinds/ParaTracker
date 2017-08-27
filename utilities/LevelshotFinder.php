<?php

//We are in the utilities folder, so we have to back out one
chdir("../");

$safeToExecuteGameInfo = 1;

//GameInfo.php MUST exist, or the page must terminate!
if (file_exists("GameInfo.php"))
{
    include_once 'GameInfo.php';
}
else
{
    exit();
}

if(!isset($_GET["gameName"]) || !isset($_GET["mapName"]))
{
    exit();
}

//$gameName must be parsed from the URL here
$gameName = rawurldecode($_GET["gameName"]);
$gameName = makeFunctionSafeName($gameName);

//$mapName must be parsed from the URL here
$mapName = rawurldecode($_GET["mapName"]);


if(function_exists($gameName) && is_callable($gameName))
{
    //Call the function
    $GameInfoData = $gameName(array(), array());
    $levelshotFolder = $GameInfoData[1];
    echo ':#:' . levelshotFinder($mapName, $levelshotFolder) . ':#:';
}

function makeFunctionSafeName($input)
{
    $input = preg_replace("/[^a-z0-9]/", "", strtolower($input));
    return $input;
}

function levelshotFinder($mapName, $levelshotFolder)
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