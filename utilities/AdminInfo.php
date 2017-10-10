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
    displayError("You must be logged in as an administrator!", "", "");
    exit();
}

$output = htmlDeclarations("ParaTracker - Admin Info", "../") . '</head><body class="adminInfo">';

if(isset($_GET['showServerList']) && booleanValidator($_GET['showServerList'], 0))
{
	if(analyticsEnabled) displayServerList($output);
	else displayError('Analytics is not enabled on this server, so there is no server list to get!', '', '');
	exit();
}

$output .= '</head><body style="background-color: #000; color: #FFF; font-family: monospace; text-align: center;">';


if(emailEnabled)
{
    include utilitiesPath . 'SendEmails.php';

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

    $output .= "<h4>Email is enabled.</h4>";
    if(emailAdminReports)
    {
        $output .= '<p>Admin reports are enabled.</p>';
    }
	if(analyticsEnabled) $output .= '<p>Analytics is enabled.<br><strong><a class="testMessage" href="AdminInfo.php?showServerList=1">Display active server and address list</a></strong></p>';
	else $output .= 'Analytics is disabled.';

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

    $output .= '<strong><a class="testMessage" href="AdminInfo.php?sendTestEmail=1">Send test message to administrators</a></strong>';

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
	for($i = 0; $i < $count; $i++)
	{
		if($currentGame != $databaseInfo[$i]['name'])
		{
			//We've changed games
			if($currentGame != '')
			{
				//This is not our first execution
				$count2 = count($outputArray);
				$output .= '<strong>' . $count2 . checkPlural('</strong> server', $count2) . '<br><div id="' . makeFunctionSafeName($databaseInfo[$i-1]['name']) . '">' . padOutputAndImplode($outputArray, '<br>') . '<br></div><br>';
				$outputArray = array();
			}
			$output .= '<div class="cursorPointer" title="Click to expand/collapse" onclick="expandContractDiv(' . "'" . makeFunctionSafeName($databaseInfo[$i]['name']) . "'" . ')"><div class="serverListHeading ' . makeFunctionSafeName($databaseInfo[$i]['name']) . '">' . $databaseInfo[$i]['name'] . '</div></div>';
			$currentGame = $databaseInfo[$i]['name'];
		}
		array_push($outputArray, $databaseInfo[$i]['location'] . ':' . $databaseInfo[$i]['port']);
	}
	$count2 = count($outputArray);
	$output .= '<strong class="' . '' . '">' . $count2 . checkPlural('</strong> server', $count2) . '<br><div id="' . makeFunctionSafeName($databaseInfo[$i-1]['name']) . '">' . padOutputAndImplode($outputArray, '<br>') . '<br></div><br>';


	$output .= '<br>' . adminInfoGoBackLink() . '<br></body></html>';

	echo "-->" . $output;
	exit();
}

$output .= '</body></html>';

echo "-->" . $output;

?>
