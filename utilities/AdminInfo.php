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
    include_once 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

if(!admin)
{
    displayError("You must be logged in as an administrator!", "", "");
    exit();
}

$output = htmlDeclarations("ParaTracker - Admin Info", "../");

//Include ParaScript. We need it for the confirmation dialogs
$output .= '<script src="../js/ParaScript.js"></script>';

$output .= '</head><body class="adminInfo" style="background-color: #000; color: #FFF; font-family: monospace; text-align: center;">';

if(isset($_GET['showServerList']) && booleanValidator($_GET['showServerList'], 0))
{
	if(analyticsEnabled) displayServerList($output);
	else displayError('Analytics is not enabled on this server, so there is no server list to get!', '', '');
	exit();
}

if(isset($_GET['forceCleanup']))
{
	if(numericValidator($_GET['forceCleanup'], 0, 2, 0) == 1)
	{
		$output .= '<h3>Forcing info folder cleanup to run could cause a high load on the server.<br>This should only be done if absolutely necessary.<br>Do you wish to continue?<br><br><strong id="yesNoDialog"><a class="testMessage" href="AdminInfo.php?forceCleanup=2" onclick="adminConfirmationClicked(\'yesNoDialog\')">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="testMessage" href="AdminInfo.php">No</a></strong></h3></body></html>';
		echo '-->' . $output;
		exit();
	}
	if (numericValidator($_GET['forceCleanup'], 0, 2, 0) == 2)
	{
		$forceCleanup = 1;
		cleanupInfoFolder($cleanupInterval, $deleteInterval, $loadLimit, $cleanupLogSize, 1);
		$output .= '<h4 class="messageSuccess">Info folder and levelshot request cleanup complete!</h4>';
		if(emailEnabled)
		{
			include_once utilitiesPath . 'SendEmails.php';
			if(sendEmail($emailAdministrators, 'ParaTracker - info/levelshot cleanup forced', '<h3>This message was sent to notify you that ParaTracker was forced to run an info folder and levelshot request cleanup.</h4><br><br>' . 	date('Y-m-d H:i', time()))) $output .= '<h4 class="messageSuccess">Email alert sent!</h4>';
			else $output .= '<h4 class="messageFailed">Email alert failed to send!</h4>';
		}
	}
}

if(analyticsEnabled && isset($_GET['forceAnalyticsBackground']))
{
	if(numericValidator($_GET['forceAnalyticsBackground'], 0, 2, 0) == 1)
	{
		$output .= '<h3>Forcing analyticsBackground to run could cause a high load on the server.<br>This should only be done if absolutely necessary.<br>Do you wish to continue?<br><br><strong id="yesNoDialog"><a class="testMessage" href="AdminInfo.php?forceAnalyticsBackground=2" onclick="adminConfirmationClicked(\'yesNoDialog\')">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="testMessage" href="AdminInfo.php">No</a></strong></h3></body></html>';
		echo '-->' . $output;
		exit();
	}
	if (numericValidator($_GET['forceAnalyticsBackground'], 0, 2, 0) == 2)
	{
		$forceAnalyticsBackgroundRun = 1;
		include_once utilitiesPath . 'AnalyticsBackground.php';
		$output .= '<h4 class="messageSuccess">AnalyticsBackground complete!</h4>';
		if(emailEnabled)
		{
			include_once utilitiesPath . 'SendEmails.php';
			if(sendEmail($emailAdministrators, 'ParaTracker - AnalyticsBackground forced', '<h3>This message was sent to notify you that AnalyticsBackground was forced to run.</h4><br><br>' . 	date('Y-m-d H:i', time()))) $output .= '<h4 class="messageSuccess">Email alert sent!</h4>';
			else $output .= '<h4 class="messageFailed">Email alert failed to send!</h4>';
		}
	}
}


if(emailEnabled && emailAdminReports && isset($_GET['forceAdminEmails']))
{
	if(numericValidator($_GET['forceAdminEmails'], 0, 2, 0) == 1)
	{
		$output .= '<h3>Forcing the admin status report to send will interfere with the weekly information sent.<br>Do you wish to continue?<br><br><strong id="yesNoDialog"><a class="testMessage" href="AdminInfo.php?forceAdminEmails=2" onclick="adminConfirmationClicked(\'yesNoDialog\')">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="testMessage" href="AdminInfo.php">No</a></strong></h3></body></html>';
		echo '-->' . $output;
		exit();
	}
	if(numericValidator($_GET['forceAdminEmails'], 0, 2, 0) == 2)
	{
		include_once utilitiesPath . 'SendEmails.php';
		if (prepareAndsendAdminReport($emailAdministrators)) $output .= '<h4 class="messageSuccess">Admin status email sent successfully!</h4>';
		else $output .= '<h4 class="messageFailed">Admin status email failed to send!</h4>';
	}
}



if(emailEnabled)
{
    include_once utilitiesPath . 'SendEmails.php';

    if(isset($_GET['sendTestEmail']) && booleanValidator($_GET['sendTestEmail'], 0))
    {
        //Send a test message!
        if(sendEmail($emailAdministrators, 'ParaTracker - Test', 'This message was sent as a test of the email system.'))
        {
            $output .= '<h4 class="messageSuccess">Test email sent successfully!</h4>';
        }
        else
        {
            $output .= '<h4 class="messageFailed">Failed to send test email!</h4>';
        }
    }

    $output .= '<p>Email is enabled.</p>';
    if(emailAdminReports)
    {
        $output .= '<p>Admin reports are enabled.</p>';
    }
	if(analyticsEnabled) $output .= '<p>Analytics is enabled.</p><strong id="showServerList"><a onclick="adminConfirmationClicked(\'showServerList\')" class="testMessage" href="AdminInfo.php?showServerList=1">Display active server and address list</a></strong><br><br><strong><a class="testMessage" href="AdminInfo.php?forceAnalyticsBackground=1">Force AnalyticsBackground to run now</a></strong>';
	else $output .= '<p>Analytics is disabled.</p>';

	$output .= '<p><strong><a class="testMessage" href="AdminInfo.php?forceCleanup=1">Force info folder and levelshot request cleanup to run now</a></strong></p>';
	
	if(useSMTP)
    {
        $output .= '<p>Using SMTP server at:<br><strong>' . smtpAddress . ':' . smtpPort . '</strong></p>';
    }
    else
    {
        $output .= '<p>Not using an SMTP server.</p>';
    }

    $output .= '<p>Using "<strong>' . emailFromAddress . '</strong>" as the sender address.</p>';

    $output .= '<p>Administrator email addresses:</p>';

    $output .= '<h3>' . implode($emailAdministrators, '<br>') . '</h3>';

    $output .= '<strong id="sendTestEmail"><a onclick="adminConfirmationClicked(\'sendTestEmail\')" class="testMessage" href="AdminInfo.php?sendTestEmail=1">Send test message to administrators</a></strong>';

	if(emailAdminReports)
	{
		$output .= '<br><strong><a class="testMessage" href="AdminInfo.php?forceAdminEmails=1">Force Admin Status Report To Send Now</a></strong><br><br>';
	}

}

function sortByLocationAndPort($a, $b)
{
	if($a['location'] > $b['location']) return 1;
	if($a['location'] < $b['location']) return -1;

	if($a['location'] == $b['location'])
	{
		//If the addresses are the same, sort by port
		if($a['port'] > $b['port']) return 1;
		if($a['port'] < $b['port']) return -1;
	}
	return 0;
}

function sortServerInfo($a, $b)
{
	//We need to make sure that unrecognized games are bumped to the top of the list
	if($a['name'] == "Unrecognized Game" && $b['name'] == "Unrecognized Game")
	{
		return sortByLocationAndPort($a, $b);
	}
	else
	{
		if($a['name'] == "Unrecognized Game") return 1;
		if($b['name'] == "Unrecognized Game") return -1;
	}

	if($a['name'] == $b['name'])
	{
		return sortByLocationAndPort($a, $b);
	}
	else
	{
		if($a['name'] > $b['name']) return 1;
		if($a['name'] < $b['name']) return -1;
	}
	return 0;
}

function displayServerList($output)
{
	$output .= '<style>p{margin-top:0px;padding-top:0px;}</style>';
	$output .= '<style>.cursorPointer{cursor:pointer;}</style>';
	$output .= '<script src="../js/ParaScript.js"></script>';
	$output .= '</head><body style="background-color: #000; color: #FFF; font-family: monospace; text-align: center;">' . adminInfoGoBackLink();

	$databaseInfo = getServerListFromDatabase();

	usort($databaseInfo, 'sortServerInfo');

	$count = count($databaseInfo);
	$i = 0;
	$output .= '<h2>Tracking <strong>' . $count . checkPlural('</strong> Active Server', $count) . '</h2>';

	$currentGame = '';
	$outputArray = array();
	$trackedGamesArray = array();
	for($i = 0; $i < $count; $i++)
	{
		if($currentGame != $databaseInfo[$i]['name'])
		{
			//We've changed games
			array_push($trackedGamesArray, $databaseInfo[$i]['name']);
			if($currentGame != '')
			{
				//This is not our first execution
				$count2 = count($outputArray);
				$output .= '<strong>' . $count2 . checkPlural('</strong> server', $count2) . '<br><div id="' . makeFunctionSafeName($databaseInfo[$i-1]['name']) . '">' . padOutputAddHyperlinksAndImplode($outputArray, '<br>') . '<br></div><br>';
				$outputArray = array();
			}
			$output .= '<div class="cursorPointer" title="Click to expand/collapse" onclick="expandContractDiv(' . "'" . makeFunctionSafeName($databaseInfo[$i]['name']) . "'" . ')"><div class="serverListHeading ' . makeFunctionSafeName($databaseInfo[$i]['name']) . '">' . $databaseInfo[$i]['name'] . '</div></div>';
			$currentGame = $databaseInfo[$i]['name'];
		}
		array_push($outputArray, $databaseInfo[$i]['location'] . ':' . $databaseInfo[$i]['port']);
	}
	$count2 = count($outputArray);

	$output .= '<strong>' . $count2 . checkPlural('</strong> server', $count2) . '<br><div id="' . makeFunctionSafeName($databaseInfo[$i-1]['name']) . '">' . padOutputAddHyperlinksAndImplode($outputArray, '<br>') . '<br></div><br>';

	//Now we need to add the untracked games to the list
	$gameList = detectGameName('')[1];
	$count = count($trackedGamesArray);
	$untrackedGamesArray = array();
	while(count($gameList) > 0)
	{
		$foundMatch = 0;
		$test = array_shift($gameList);
		for($i = 0; $i < $count; $i++)
		{
			$foundMatch = 0;
			if($test == $trackedGamesArray[$i])
			{
				$foundMatch = 1;
				break;
			}
		}
		if(!$foundMatch)
		{
			array_push($untrackedGamesArray, $test);
		}
	}

	$output .= '<div class="cursorPointer" title="Click to expand/collapse" onclick="expandContractDiv(\'untrackedGames\')">
				<br><div class="serverListHeading">Untracked Games</div>
				</div>
				<div id="untrackedGames">';
				$count = count($untrackedGamesArray);
				for($i = 0; $i < $count; $i++)
				{
					$untrackedGamesArray[$i] = '<strong class="' . makeFunctionSafeName($untrackedGamesArray[$i]) . '">' . $untrackedGamesArray[$i] . '</strong>';
				}
				if($count > 0) $output .= '<br>' . implode('<br>', $untrackedGamesArray) . '</div>';
				else $output .= 'None<br>';

	$output .= '<br>' . adminInfoGoBackLink() . '<br></body></html>';

	echo "-->" . $output;
	exit();
}

$output .= '</body></html>';

echo "-->" . $output;

?>
