<?php

echo "<!--";

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
    include 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

if(!admin)
{
    displayError("You must be logged in as an administrator to view log files!", "", "");
}

//This is here to suppress errors
$filename = "";
$filepath = "";


$output = htmlDeclarations("ParaTracker - Log Viewer", "../") . '</head><body class="logViewerPage">';

//This is an array of log file names that are permitted to be displayed.
$permittedFiles = array("cleanupLog.php", "errorLog.php", "RConLog.php");

if(isset($_GET["path"]))
{
    //We were given a path. See if it leads to a file.
    $filepath = pathValidator($_GET["path"]);
}
$this_file = trim(trim(substr(__FILE__, strlen(__DIR__)), "/"), "\\");

define("this_file", $this_file);
define("filepath", $filepath);
define("permittedFiles", $permittedFiles);

if(filepath != "")
{
    if(is_file(logPath . filepath) && substr(strtolower(filepath), strlen(filepath) - 4) == ".php")
    {
        $output .= renderLogFile(filepath);
    }
    else
    {
        //List the RConlog files
        $logFileList = scandir(logPath);

        $output .= logGoBackDirectoryLink() . '<table class="logPathTable">';

        $counted = count($logFileList);
        for($i = 2; $i < $counted; $i++)
        {
            if(substr(strtolower(logPath . $logFileList[$i]), -4) != ".php" && filesize(logPath . $logFileList[$i] . '/RConLog.php') > 0)
            {
                $serverName = breakDynamicAddressPath($logFileList[$i]);
                $output .= '<tr><td class="logViewerPathTable">' . logViewerLink($logFileList[$i] . '/RConLog.php', $serverName[0] . ':' . $serverName[1] . '</td><td>' . getHumanReadableFilesize(logPath . $logFileList[$i] . '/RConLog.php')) . '</td></tr>';
            }
        }
        $output .= '</table>' . logGoBackDirectoryLink();
    }
}
else
{
    //List the files in the root folder.
    $logFileList = scandir(logPath);
    $counted = count($logFileList);

    $output .= '<table class="logPathTable">';
    for($i = 2; $i < $counted; $i++)
    {
        if(substr(strtolower(logPath . $logFileList[$i]), -4) == ".php"&& filesize(logPath . $logFileList[$i]) > 0)
        {
            $logFileList[$i] = stringValidator($logFileList[$i], "", "", "");

            $output .= '<tr><td class="logViewerPathTable">' . logViewerLink($logFileList[$i], $logFileList[$i] . '</td><td>' . getHumanReadableFilesize(logPath . $logFileList[$i])) . '</td></tr>';
        }
    }
    $output .= '<tr><td class="logViewerPathTable">' . logViewerLink("RConLogs", "RCon Logs") . '</td><td></td></tr></table>';
}

$output .= '</body></html>';

echo "-->" . $output;

?>
