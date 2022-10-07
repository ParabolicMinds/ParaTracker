<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


$sendAdminEmails = 0;
$calledFromElsewhere = 1;

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//Check to see if ParaFunc was already executed
if(!defined('utilitiesPath'))
{
    //If this file is called from the command line, it will send the admin reports.
    //This is intended to be done via a cron job.
    //If it is called by URL or included by another file, it will serve as a library file.
    if (php_sapi_name() == "cli") $sendAdminEmails = 1;

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


set_time_limit(300);

if(!emailEnabled)
{
    displayError("Email is disabled in ParaConfig.php! You must enable email to continue.", "", "");
}

//Make sure this can be run in the first place
if(!file_exists('vendor/autoload.php'))
{
    echo ' Composer does not appear to be installed. ' . trackerName() . ' expects PHPMailer to be installed via Composer. ';
    exit();
}
else
{
    //Composer appears to be installed. Load PHPMailer!
	require_once 'vendor/autoload.php';

	try {
		//Import PHPMailer classes into the global namespace
		$test = new PHPMailer\PHPMailer\PHPMailer;
	} catch (Exception $e) {
        echo ' PHPMailer does not appear to be installed. ' . trackerName() . ' expects this library to be installed via Composer. \n' . $e->getMessage();
        exit();
	}
}


//This variable sets how many displayError calls are considered acceptable per day before warnings start
//The value given here will turn the warning yellow, and 1.5 times that value will turn it red
define("callsPerDay", 10);

$emailPath = rtrim(trim($emailPath), '/') . '/';
$emailAdminReports = booleanValidator($emailAdminReports, 0);
$SMTPSecure = booleanValidator($SMTPSecure, 1);

define("emailFromAddress", $emailFromAddress);

define("smtpAddress", trim($smtpAddress));
define("smtpPort", numericValidator($smtpPort, 0, 65535, 25));
define("smtpUsername", $smtpUsername);
define("smtpPassword", $smtpPassword);
define("SMTPSecure", $SMTPSecure);


define("emailHr1", '<hr width="70%" style="opacity: .4">');
define("emailHr2", '<hr width="15%" style="opacity: .275">');
define("emailHeadingColor1", 'color: #c41414; text-shadow: -1px 1px #76767699;');
define("emailHeadingColor2", 'color: #767676; text-shadow: -1px 1px #c4141499;');
define("emailHeadingColor3", 'color: #d5a620; text-shadow: -1px 1px #c4141499;');
define("emailHeadingColor4", 'color: #69F; text-shadow: -1px 1px #3BF9;');


//If any other emails are to be sent, this is where to add them.

if($sendAdminEmails && emailAdminReports && count(emailAdministrators) > 0)
{
    prepareAndsendAdminReport(emailAdministrators);
}

//Below here is just functions

function getExecutionTimeArray()
{
	global $pgCon;
	$frames_fetch = pg_fetch_all(pg_query_params($pgCon, "SELECT startdate, enddate FROM analytics.runtimes WHERE startdate BETWEEN $1 AND $2", array(date('Y-m-d H:i', lastRefreshTime), date('Y-m-d H:i', currentTime))));

	//This next line prevents a boolean false from being counted as a 1
	$count = countDatabaseReturn($frames_fetch);
	for($i = 0; $i < $count; $i++)
	{
	$frames_fetch[$i]['timeDiff'] = strtotime($frames_fetch[$i]['enddate']) - strtotime($frames_fetch[$i]['startdate']);
	}
	return $frames_fetch;
}

function getDisplayErrorCalls()
{
    global $pgCon;
    $frames_fetch = pg_fetch_all(pg_query_params($pgCon, "SELECT entrydate FROM tracker.displayerror WHERE entrydate BETWEEN $1 AND $2", array(date('Y-m-d H:i', lastRefreshTime), date('Y-m-d H:i', currentTime))));

	return $frames_fetch;
}

function getCPULoadArray()
{
    global $pgCon;
    $frames_fetch = pg_fetch_all(pg_query_params($pgCon, "SELECT load FROM tracker.cpuload WHERE entrydate BETWEEN $1 AND $2", array(date('Y-m-d H:i', lastRefreshTime), date('Y-m-d H:i', currentTime))));
    return $frames_fetch;
}

function getValuesFromArray($input, $key)
{
    $max = "No data";
    $min = "No data";
    $average = "No data";
    $sum = 0;
    if(is_array($input))
    {
        $count = count($input);
        if($count > 0)
        {
            $min = $input[0];
            $max = 0;
            for($i = 0; $i < $count; $i++)
            {
                if(!empty($input[$i][$key]))
                {
                    $value = $input[$i][$key];
                    $sum = $sum + $value;
                    if($value > $max) $max = $value;
                    if($value < $min) $min = $value;
                }
            }
            $max = round($max, 2);
            $min = round($min, 2);
            $average = round($sum / $count, 2);
        }
    }
    return array($min, $max, $average);
}

function parseEmailDataIntoTable($data, $rowColors = array('191919', '262626'))
{
	// $data must be an array of info.
	// $data[$i][$j][0] must be the data to display. Optionally, if $data[$i][$j][1] is provided, it will be used to style that element

	$textStyle = ' white-space: nowrap; font-family: monospace; margin: 0px; border-style: none; padding: 3px; ';

	$output = '<table style="' . $textStyle . ' margin: 0 auto; border-spacing: 0px;">';

	for($i = 0; $i < count($data); $i++)
	{
		$output .= '<tr style="' . $textStyle . ' background-color: #' . $rowColors[$i % count($rowColors)] . ';">';
		for($j = 0; $j < count($data[$i]); $j++)
		{
			$style = $textStyle;
			if(is_array($data[$i][$j]))
			{
				if(count($data[$i][$j]) > 1) $style .= ' ' . $data[$i][$j][1];
				$output .= '<td style="' . $style . '">' . $data[$i][$j][0] . '</td>';
			}
			else
			{
				$output .= '<td style="' . $style . '">' . $data[$i][$j] . '</td>';
			}
		}
	}

	return $output . '</table>';
}

function prepareAndsendAdminReport($emailAdministrators)
{
    //Mark the time in microseconds so we can see how long this takes.
	$parseTimer = microtime(true);

	//Get the last refresh time
    define("lastRefreshTime", numericValidator(readFileIn(infoPath . 'emailTimer.txt'), 0, time(), 0));
    define("currentTime", time());

    $subject = trackerName() . ' - Admin Status Report';
    $message = '<table style="width: 100%; font-family: monospace; font-size: 10pt;"><tr><td style="text-align: center;"><h1 style="text-align: center; font-family: monospace; ' . emailHeadingColor3 . '">Admin Status Report</h1>' . emailHr2;
//    $message .= '<h3 style="text-align: center;">Server: ' . $_SERVER['REQUEST_URI'] . '</h3>';

    if(mapreqEnabled)
    {
		$mapreqArray = array();

        $message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . '">Pending Levelshot Requests:</h3>';

        global $pgCon;
        $mapreqs_user = pg_fetch_all(pg_query($pgCon, 'SELECT * FROM mapreq WHERE useradded = true ORDER BY  CASE WHEN dl_link IS NULL THEN 1 ELSE 0 END, game_name ASC, bsp_name ASC'));
        $mapreqs_auto = pg_fetch_all(pg_query($pgCon, 'SELECT * FROM mapreq WHERE useradded = false ORDER BY game_name ASC, bsp_name ASC'));

		//This next line prevents a boolean false from being counted as a 1
		$count1 = countDatabaseReturn($mapreqs_user);
        $unit1 = checkPlural("request", $count1);

		//This next line prevents a boolean false from being counted as a 1
		$count2 = countDatabaseReturn($mapreqs_auto);
        $unit2 = checkPlural("request", $count2);

		$padAmt = strlen(strval(max($count1, $count2)));
echo $padAmt;
        array_push($mapreqArray, array(array('Manual requests:', 'text-align: right;'), array(padStringBefore(strval($count1), $padAmt) . ' ' . $unit1, 'text-align: left;' . getBackgroundColorForDangerousValueHigher($count1, 1, 5, '') . ';')));
        array_push($mapreqArray, array(array('Automatic requests:', 'text-align: right;'), array(padStringBefore(strval($count2), $padAmt) . ' ' . $unit2, 'text-align: left;' . getBackgroundColorForDangerousValueHigher($count2, 2000, 2500, '') . ';')));

		$message .= '<p style="font-family: monospace; text-align: center;">' . parseEmailDataIntoTable($mapreqArray) . '</p>' . emailHr2;
    }

	if(enablePGSQL)
	{
		// START OF GAMES TABLE
		// START OF GAMES TABLE
		// START OF GAMES TABLE

		$gameListArray = array();

		$trackedCount = pg_fetch_all(pg_query($pgCon, "SELECT COUNT(*) FROM tracker.server WHERE active = true"));
		$message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . '">Currently tracking <strong>' . $trackedCount[0]['count'] . '</strong> servers</h3>';

		$databaseInfo = getServerListFromDatabase();

		$count = countDatabaseReturn($databaseInfo);
		$unrecognizedGames = array();

		$maxVal = 0;

		for($i = 0; $i < $count; $i++)
		{
			if(!isset($gameCountList[$databaseInfo[$i]["name"]]) || empty($gameCountList[$databaseInfo[$i]["name"]]))
			{
				$gameCountList[$databaseInfo[$i]["name"]] = 1;
				$maxVal = max($maxVal, 1);
			}
			else if($databaseInfo[$i]["name"] == 'Unrecognized Game')
			{
				array_push($unrecognizedGames, '<a href="https://' . webServerName . '/ParaTrackerDynamic.php?ip=' . $databaseInfo[$i]["location"] . '&port=' . $databaseInfo[$i]["port"] . '" style="color: 25F;">' . $databaseInfo[$i]["location"] . ':' . $databaseInfo[$i]["port"] . '</a>');
			}
			else
			{
				$gameCountList[$databaseInfo[$i]["name"]]++;
				$maxVal = max($maxVal, $gameCountList[$databaseInfo[$i]["name"]]);
			}
		}

		$padAmt = strlen(strval($maxVal));

		$gameList = detectGameName("")[0];
		$count = count($gameList);
		for($i = 0; $i < $count; $i++)
		{
			$gameEntry = array();
			array_push($gameEntry, array($gameList[$i] . ':', 'text-align: right;'));
			if(isset($gameCountList[$gameList[$i]]))
			{
				$unit = checkPlural("server", $gameCountList[$gameList[$i]]);
				array_push($gameEntry, array(padStringBefore(strval($gameCountList[$gameList[$i]]), $padAmt) . '&nbsp;' . $unit, 'text-align: left;' . getBackgroundColorForDangerousValueLower($gameCountList[$gameList[$i]], 4, 0)));
			}
			else
			{
				array_push($gameEntry, array(padStringBefore('0', $padAmt) . '&nbsp;servers', 'text-align: left;' . getBackgroundColorForDangerousValueLower(0, 0, 0)));
			}
			array_push($gameListArray, $gameEntry);
		}

		$count = count($unrecognizedGames);
		$unit = checkPlural("server", $count);

		$gameEntry = array();
		array_push($gameEntry, array('Unrecognized Games:', 'text-align: right;'));
		array_push($gameEntry, array(padStringBefore(strval($count), $padAmt) . '&nbsp;' . $unit, 'text-align: left;' . getBackgroundColorForDangerousValueHigher($count, 1, 1, '')));
		array_push($gameListArray, $gameEntry);

		if($count > 0)
		{
			$message .= emailHr2 . '<h3 style="' . emailHeadingColor1 . '">Unrecognized Games:</h3><p>' . implode('<br>', $unrecognizedGames[$i]) . '</p>';
		}

		$message .= '<p style="font-family: monospace; text-align: left;">' . parseEmailDataIntoTable($gameListArray) . '</p>';

	}

	$stuff = array();
	$stuff[0] = '<span style="' . emailHeadingColor1 . '"><strong>Start Time: </strong>' . date(DATE_RFC2822, lastRefreshTime) . '</span>';
	$stuff[1] = '<span style="' . emailHeadingColor2 . '"><strong>End Time: </strong>' . date(DATE_RFC2822, currentTime) . '</span>';
	
	$message .= '<p style="font-family: monospace; text-align: center;">' . padOutputAndImplode($stuff, '<br>') . '</p>' . emailHr2;


	// END OF GAMES TABLE
	// END OF GAMES TABLE
	// END OF GAMES TABLE


    if(enablePGSQL)
    {
		$displayErrorCount = numericValidator(countDatabaseReturn(getDisplayErrorCalls()), 0, "", 622);
        $message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . '">DisplayError() Calls:</h3>';

		//86400 is the number of seconds in a day
        $problemThreshold = ((currentTime - lastRefreshTime) / 86400) * callsPerDay;

        $unit = checkPlural("time", $displayErrorCount);
        $message .= '<p style="font-family: monospace; text-align: center;">DisplayError was called ' . colorizeDangerousValuesHigher($displayErrorCount, $unit, $problemThreshold, $problemThreshold * 1.5, '');

        //86400 is the number of seconds in 24 hours
        $calls = round($displayErrorCount / ((currentTime - lastRefreshTime) / 86400), 2);
        $unit = checkPlural("call", $calls);

        $message .= '<br><span style="font-size: 9pt;">At a rate of ' . colorizeDangerousValuesHigher($calls, $unit, $problemThreshold, $problemThreshold * 1.5, '') . ' per day</span><br>
        <span style="font-size: 8pt;">If there are excessive displayError calls, check ';
        if(webServerDomain != '') $message .= '<a href="https://' . webServerDomain . '/' . utilitiesPath . 'LogViewer.php?path=errorLog.php" style="' . emailHeadingColor3 . '">';
        $message .= logPath . 'errorLog.php';
        if(webServerDomain != '') $message .= '</a>';
        $message .= ' for details.</span></p>' . emailHr2;

        $executionTimeArray = getExecutionTimeArray();
        $temp = getValuesFromArray($executionTimeArray, 'timeDiff');
		$temp[0] = numericValidator($temp[0], 0, 999999999999999999999999, 0);
		$temp[1] = numericValidator($temp[1], 0, 999999999999999999999999, 0);
		$temp[2] = numericValidator($temp[2], 0, 999999999999999999999999, 0);
        $minExecutionTime = round($temp[0]);
        $maxExecutionTime = round($temp[1]);
        $averageExecutionTime = round($temp[2]);

        $message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . '">AnalyticsBackground.php Execution Time:</h3>';
		$stuff = array();
		$stuff[0] = 'Highest Execution Time: ' . colorizeDangerousValuesHigher($maxExecutionTime, 'seconds', 60, 120, '');
		$stuff[1] = 'Lowest Execution Time: ' . colorizeDangerousValuesHigher($minExecutionTime, 'seconds', 60, 120, '');
		$stuff[2] = 'Average Execution Time: ' . colorizeDangerousValuesHigher($averageExecutionTime, 'seconds', 60, 120, '');
		
		$message .= '<p style="font-family: monospace; text-align: center;"><span style="font-size: 9pt;">' . padOutputAndImplode($stuff, '<br>') . '</span></p>' . emailHr2;

        $cpuLoadArray = getCPULoadArray();
        $temp = getValuesFromArray($cpuLoadArray, 'load');
        $minCPULoad = $temp[0];
        $maxCPULoad = $temp[1];
        $averageCPULoad = $temp[2];

        $message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . ';">CPU Load:</h3>';

        if($maxCPULoad == "No data")
        {
			$maxCPULoad = colorizeDangerousValuesHigher($maxCPULoad, '', 75, 90, '');
        }
        else
        {
			$maxCPULoad = colorizeDangerousValuesHigher($maxCPULoad, '%', 75, 90, '');
        }
        
        if($minCPULoad == "No data")
        {
			$minCPULoad = colorizeDangerousValuesHigher($minCPULoad, '', 75, 90, '');
        }
        else
        {
			$minCPULoad = colorizeDangerousValuesHigher($minCPULoad, '%', 75, 90, '');
        }

        if($averageCPULoad == "No data")
        {
			$averageCPULoad = colorizeDangerousValuesHigher($averageCPULoad, '', 75, 90, '');
        }
        else
        {
			$averageCPULoad = colorizeDangerousValuesHigher($averageCPULoad, '%', 75, 90, '');
        }

		$stuff = array();
		$stuff[0] = 'Highest CPU Load: ' . $maxCPULoad;
		$stuff[1] = 'Lowest CPU Load: ' . $minCPULoad;
		$stuff[2] = 'Average CPU Load: ' . $averageCPULoad;
		$message .= '<p style="font-family: monospace; text-align: center;">' . padOutputAndImplode($stuff, '<br>') . '</p>' . emailHr2;
    }
    else
    {
        $message .= "<h3>Postgres is disabled!</h3>";
    }

	$message .= '<h3 style="text-align: center; font-family: monospace; ' . emailHeadingColor1 . '">Free Space:</h3>';
	$stuff = array();
	$stuff[0] = 'Free space in info folder: ' . getFreeSpace(infoPath);
	$stuff[1] = 'Free space in logs folder: ' . getFreeSpace(logPath);
	$message .= '<p style="font-family: monospace; text-align: center;"><span style="font-size: 8pt;">' . padOutputAndImplode($stuff, '<br>') . '</span></p>' . emailHr2;

	$parseTimer = number_format(((microtime(true) - $parseTimer) * 1000), 3);
	$message .= '<p style="text-align: center; font-family: monospace; '. emailHeadingColor2 . '">Email prepared in ' . $parseTimer . ' milliseconds.</p>';

    $message .= "</td></tr></table>";

    $finished = sendEmail($emailAdministrators, $subject, $message);
    if($finished)
    {
        writeFileOut(infoPath . 'emailTimer.txt', currentTime);
	}
	return $finished;
}

function fixHyperlinksForGmail($input)
{
	$input = str_replace("@", "&#8203;@&#8203;", $input);
	$input = str_replace(".", "&#8203;.&#8203;", $input);
	return $input;
}

function fixGmailQuotingBug($message)
{
	// This function will fix messages from being displayed incorrectly as quoted messages with hidden text
	$output = $message;

	$tagsToFix = array('span', 'td', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'br');

	for($i = 0; $i < count($tagsToFix); $i++)
	{
		$searchTerm = '</' . $tagsToFix[$i] . '>';
		$temp = explode($searchTerm, $output);
		$output = $temp[0];
		for($j = 1; $j < count($temp); $j++)
		{
			$output .= '<span style="display: none !important;">' . generateToken(20, 40) . '</span>' . $searchTerm . $temp[$j];
		}
	}

	return $output;
}

function emailHeader()
{
	return '<div style="width: 100%; height: 100%; margin: 0px; padding: 0px; text-align: center; background-color: #111; color: #EEE; font-size: 11pt; font-family: monospace;">
			<div style="margin: 0 auto; max-width: 700px;">
<h1 style="margin-bottom: 0px; margin-top: 0px; padding-top: 12px;"><span style="' . emailHeadingColor1 . '">' . trackerName() . '</span> <span style="' . emailHeadingColor2 . '">' . strval(trackerVersion()) . '</span></h1><h4 style="margin-top: 0px;">' . trackerTagline . '</h4><br>' . emailHr1 . '<br><div style="font-size: 11pt; text-align: center;">';
}

function emailFooter($unsubscribeInfo = '')
{
	$output = '</div><br>' . emailHr1 . '<br><div style="font-size: 9pt;"><p>This is an automated message from <span style="' . emailHeadingColor1 . '">' . trackerName() . '</span>';
	if(webServerName != '')
	{
		$output .= ' at <a style="font-weight: bold;' . emailHeadingColor2 . '" href="https://' . webServerName . '" target="_blank">' . webServerName . '</a>';
	}
	$output .= '.<br>Do not respond, as this mailbox is not ' . (useSMTP == true ? 'monitored.' : 'real.') . '</p><br>';
 
	if($unsubscribeInfo != '')
	{
		$output .= '<p>To disable these email alerts, <a style="font-weight: bold;" href="https://' . webServerName . '/' . utilitiesPath . 'ServerTools.php?' . $unsubscribeInfo . '" target="_blank">click here</a>.</p><br>';
	}

	$output .= '<br></div></div></div>';

	return $output;
}

function sendEmail($recipients, $subject, $messageBody, $unsubscribeInfo = '')
{
	if(empty($recipients)) return false;
	if(empty($messageBody)) return false;

	// Add a header and footer to make the emails have a distinct appearance
	$emailHeader = emailHeader();

	$emailFooter = emailFooter($unsubscribeInfo);

	// Put all the pieces of the email together
	$messageBody = fixGmailQuotingBug($emailHeader . $messageBody . $emailFooter);

	// DEBUG LINES
	//echo '-->' . $messageBody;
	//exit();
	// DEBUG LINES

	//Create a new PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

    if(useSMTP)
    {
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = smtpAddress;
        $mail->Port = smtpPort;                                    // TCP port to connect to
        $mail->SMTPAuth = false;                               // Enable SMTP authentication
        $mail->Username = smtpUsername;                 // SMTP username
        $mail->Password = smtpPassword;                           // SMTP password
		$mail->SMTPSecure = SMTPSecure;
		$mail->SMTPAutoTLS = true;
    }
    else
    {
        // Set PHPMailer to use the sendmail transport
        $mail->isSendmail();
    }

    $mail->setFrom(emailFromAddress, trackerName());

    if(is_array($recipients))
    {
        foreach($recipients as $address)
        {
            if(empty($address)) continue;
            $mail->addAddress($address);
        }
    }
    else
    {
        $mail->addAddress($recipients);
    }

    //Set the subject line
    $mail->Subject = $subject;
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML($messageBody);

    //send the message, check for errors

    if (!$mail->send())
    {
        echo "Mailer Error: " . $mail->ErrorInfo;
        if(useSMTP)
        {
            echo "\n<br>Attempting without SMTP...<br>";
            $mail->isSendmail();
            if (!$mail->send())
            {
                echo "Mailer Error: " . $mail->ErrorInfo;
                displayError("Failed to send email with SMTP! Attemped without SMTP, but also failed. <br>" . $mail->ErrorInfo, "", "");
                return 0;
            }
            else
            {
                sentConfirmation();
                displayError("Failed to send email with SMTP, but succeeded without it! <br>" . $mail->ErrorInfo, "", "");
                return 0;
            }
        }
        else
        {
            displayError("Failed to send email! Was not using SMTP. <br>" . $mail->ErrorInfo, "", "");
            return 0;
        }
    } else {
        sentConfirmation();
        return 1;
    }

}

function sentConfirmation()
{
//	echo "Message sent! ";
}

function getFreeSpace($path)
{
    $redThreshold = 314572800;
    $yellowThreshold = 1073741824;

    $val = disk_free_space($path);
    if ($val < 2048)
    {
        $reducedValue = $val;
        $units = 'Bytes';
        $output = colorizeDangerousValuesLower($val, $units, $yellowThreshold, $redThreshold, $reducedValue);
    }
    else if ($val < 2097152)
    {
        $reducedValue = round($val / 1024, 2);
        $units = 'KiB';
        $output = colorizeDangerousValuesLower($val, $units, $yellowThreshold, $redThreshold, $reducedValue);
    }
    else if ($val < 1073741824)
    {
        $reducedValue = round($val / 1048576, 2);
        $units = 'MiB';
        $output = colorizeDangerousValuesLower($val, $units, $yellowThreshold, $redThreshold, $reducedValue);
    }
    else
    {
        $reducedValue = round($val / 1073741824, 2);
        $units = 'GiB';
        $output = colorizeDangerousValuesLower($val, $units, $yellowThreshold, $redThreshold, $reducedValue);
    }

    return $output;
}

function getBackgroundColorForDangerousValueLower($input, $yellowThreshold, $redThreshold)
{
	$autoStyle = 'font-weight: bold;';
    if($input <= $redThreshold) return $autoStyle . 'background-color: ' . redColor() . ';';
    else if($input <= $yellowThreshold) return $autoStyle . 'background-color: ' . yellowColor() . ';';
    return $autoStyle . 'background-color: ' . greenColor() . ';';
}

function getBackgroundColorForDangerousValueHigher($input, $yellowThreshold, $redThreshold)
{
	$autoStyle = 'font-weight: bold;';
    if($input >= $redThreshold) return $autoStyle . 'background-color: ' . redColor() . ';';
    else if($input >= $yellowThreshold) return $autoStyle . 'background-color: ' . yellowColor() . ';';
    return $autoStyle . 'background-color: ' . greenColor() . ';';
}

function colorizeDangerousValuesLower($input, $units, $yellowThreshold, $redThreshold, $input2)
{
	if($units != '') $units = ' ' . $units;

	if(!isset($input2) || $input2 == "") $input2 = $input;
    if($input <= $redThreshold) $output = colorRed('&nbsp;' . $input2 . $units . '&nbsp;');
    else if($input <= $yellowThreshold) $output = colorYellow('&nbsp;' . $input2 . $units . '&nbsp;');
    else $output = colorGreen('&nbsp;' . $input2 . $units . '&nbsp;');

    return $output;
}

function colorizeDangerousValuesHigher($input, $units, $yellowThreshold, $redThreshold, $input2)
{
	if($units != '') $units = ' ' . $units;

    if(!isset($input2) || $input2 == "") $input2 = $input;
    if($input >= $redThreshold) $output = colorRed('&nbsp;' . $input2 . $units . '&nbsp;');
    else if($input >= $yellowThreshold) $output = colorYellow('&nbsp;' . $input2 . $units . '&nbsp;');
    else $output = colorGreen('&nbsp;' . $input2 . $units . '&nbsp;');

    return $output;
}

function colorGreen($input)
{
    return '<span style="background-color: ' . greenColor() . '; font-weight: bold;">' . $input . '</span>';
}

function colorYellow($input)
{
    return '<span style="background-color: ' . yellowColor() . '; font-weight: bold;">' . $input . '</span>';
}

function colorRed($input)
{
    return '<span style="background-color: ' . redColor() . '; font-weight: bold;">' . $input . '</span>';
}

function greenColor()
{
	return '#2A2';
}

function yellowColor()
{
	return '#AA2';
}

function redColor()
{
	return '#A22';
}

?>
