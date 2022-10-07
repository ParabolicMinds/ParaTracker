<?php

echo "<!--";

$calledFromElsewhere = 1;

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//Check to see if ParaFunc was already executed
if(!isset($utilitiesPath))
{
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
}


if(!admin)
{
	displayError("You must be logged in as an administrator to view log files!", "", "");
	exit();
}

//This is here to suppress errors
$filename = "";
$filepath = "";


$output = htmlDeclarations('Log Viewer', '../') . '</head><body class="logViewerPage logPageFlex">';

if(isset($_GET["path"]))
{
	//We were given a path. See if it leads to a file.
	$filepath = pathValidator($_GET["path"]);
}

//This is used in ParaFunc
$this_file = trim(trim(substr(__FILE__, strlen(__DIR__)), "/"), "\\");

define("this_file", $this_file);
define("filepath", $filepath);

if(filepath != "")
{
	$count = count(permittedLogFiles);
	$match = 0;
	//Extract the file name for matching
	$test = explode('/', filepath);
	if(is_array($test) && count($test) > 0)
	{
		$test = $test[count($test) - 1];
	}

	for($i = 0; $i < $count; $i++)
	{
		if(permittedLogFiles[$i] == $test)
		{
			//We're looking for a log file
			$output .= '<h1 class="logTitle">' . $input . '</h1>' . renderLogFile(filepath);
			$match = 1;
			break;
		}
	}

	if($match == 0)
	{
		//There is only one directory that can be asked for, so list the security log files
		$logFileList = scandir(logPath);

		$output .= logGoBackDirectoryLink() . '<table class="logPathTable">';

		$counted = count($logFileList);
		$j = 0;
		for($i = 2; $i < $counted; $i++)
		{
			//Make sure the file exists, make sure it is a PHP file, and make sure it is larger than 192 bytes (The size for an empty PHP log file)
			if(file_exists(logPath . $logFileList[$i] . '/' . serverSecurityLogFilename) && substr(strtolower(logPath . $logFileList[$i]), -4) != ".php" && filesize(logPath . $logFileList[$i] . '/' . serverSecurityLogFilename) > 192)
			{
				$serverName = breakDynamicAddressPath($logFileList[$i]);
				$output .= '<tr class="logViewerRow' . $j % 2 + 1 . '"><td class="logViewerPathTable">' . logViewerLink($logFileList[$i] . '/' . serverSecurityLogFilename, $serverName[0] . ':' . $serverName[1] . '</td><td>' . getHumanReadableFilesize(logPath . $logFileList[$i] . '/' . serverSecurityLogFilename)) . '</td></tr>';
				$j++;
			}
		}
		$output .= '</table>' . logGoBackDirectoryLink();
	}
}
else
{
	$output .= 	displayRootLogDirectory();
}
$output .= '</body></html>';

echo "-->" . $output;

?>
