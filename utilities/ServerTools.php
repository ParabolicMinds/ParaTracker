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


if(!isset($doNotExecuteServerTools)) $doNotExecuteServerTools = false;

//Check to see if ParaFunc was already executed
if(!defined('utilitiesPath'))
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
        exit;
    }
}

if(serverToolsEnabled == false)
{
	displayError('Server tools is disabled.', '', '');
	exit;
}

define('serverToolsSessionTimeout', 900);		// Number of seconds before a session times out
define('emailFloodProtect', 60);
define('toolsHr1', '<hr width="70%" style="opacity: .225">');
define('maxEmailAddressLength', 200);
define('maxEmailMessageLength', 2000);

// Declaring these to be used in the global scope
$messages = array();
$warnings = array();
$postData = array();
$formCount = 0;


if(emailEnabled)
{
	include_once utilitiesPath . "SendEmails.php";
}



// For getting info from the URL:
//	if(isset($_GET['variableName']) && booleanValidator($_GET['variableName'], 0))

// For getting stuff from a form:
//	if(isset($_POST['variableName']) && booleanValidator($_POST['variableName'], 0))


/*

address=	urlencode(serverIPAddress)
port=		urlencode(serverPort)
action=		unsubscribe
token=		$emailAccountData['unsubscribeToken']

*/


/*
if( isset($_GET['debug']) && ($_GET['debug'] == true || $_GET['debug'] == '1' || $_GET['debug'] == 1) )
{
	// Insert debug actions here
}
*/


if($doNotExecuteServerTools === false)
{

	if(	isset($_GET['action']) &&
		isset($_GET['address']) &&
		isset($_GET['port']) &&
		isset($_GET['email']) &&
		isset($_GET['token'])
		)
	{

		// We are either here to verify an email address, or unsubscribe an email address.
		// Don't log in for this.

		$serverAddress = urldecode($_GET['address']);
		$serverPort = urldecode($_GET['port']);
		$emailAddress = strtolower(urldecode($_GET['email']));
		$token = $_GET['token'];
		$dynamicIPAddressPath = makeDynamicAddressPath($serverAddress, $serverPort);
		$serverSettings = readSettingsFile($dynamicIPAddressPath);

		$match = false;
		$outputMessage = '';
		for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
		{
			if($emailAddress == $serverSettings['emailAlerts']['addresses'][$i]['address'])
			{
				if($_GET['action'] == 'verify')
				{
					if(!$serverSettings['emailAlerts']['addresses'][$i]['verified'])
					{
						if($token == $serverSettings['emailAlerts']['addresses'][$i]['verifyToken'])
						{
							$serverSettings['emailAlerts']['addresses'][$i]['verified'] = true;
							writeSecurityLogEntry($dynamicIPAddressPath, 'Server Tools: Email address \'' . $emailAddress . '\' is now verified.');
							writeSettingsFile($dynamicIPAddressPath, $serverSettings);
							$outputMessage = 'Email address verified!';
						} else {
							$outputMessage = 'Invalid token!';
						}
					} else {
						$outputMessage = 'Email address has already been verified!';
					}
				} else if($_GET['action'] == 'unsubscribe') {
					$output = serverToolsHTMLDeclarations('Unsubscribe from email alerts');
					$output .= '<div class="serverToolsCategory">';
					$output .= '<h4 class="serverToolsCategoryHeading">Are you sure you want to unsubscribe<br><span class="gameColor2">' . $emailAddress . '</span><br>from email alerts for the game server at<br><span class="paraTrackerColor">' . $serverAddress . '</span><span class="gameColor7">:</span><span class="paraTrackerVersionColor">' . $serverPort . '</span>?</h4>';
					$output .= '<br><br>';
					$output .= '<div style="display: flex; flex-direction: row;">';
					$output .= '<div style="flex: 1;"></div>';
					$output .= '<div style="flex: 1;"><a href="' . basename($_SERVER['PHP_SELF']) . '" class="dynamicFormButtons dynamicFormButtonsStyle redButton">&nbsp;&nbsp;No&nbsp;&nbsp;</a></div>';
					$output .= '<div style="flex: 1;"><a href="' . basename($_SERVER['PHP_SELF']) . '?action=unsubscribeConfirmed&address=' . urlencode($serverAddress) . '&port=' . urlencode($serverPort) . '&email=' . urlencode(strtolower($emailAddress)) . '&token=' . $token . '" class="dynamicFormButtons dynamicFormButtonsStyle greenButton">&nbsp;&nbsp;Yes&nbsp;&nbsp;</a></div>';
					$output .= '<div style="flex: 1;"></div>';
					$output .= '</div>';
					$output .= '<br><br>';
					$output .= '</body></html>';
					echo $output;
					exit;
				} else if($_GET['action'] == 'unsubscribeConfirmed') {
					$attempts = 3;
					while(time() - $serverSettings['emailAlerts']['addresses'][$i]['emailFloodProtect'] < 2 && $attempts > 0)
					{
						sleep(2.1);
						$attempts--;
					}
					if(time() - $serverSettings['emailAlerts']['addresses'][$i]['emailFloodProtect'] > 2)
					{
						if($token == $serverSettings['emailAlerts']['addresses'][$i]['unsubscribeToken'])
						{
							$serverSettings['emailAlerts']['addresses'] = array_removeIndex($serverSettings['emailAlerts']['addresses'], $i, 1);
							writeSettingsFile($dynamicIPAddressPath, $serverSettings);
							writeSecurityLogEntry($dynamicIPAddressPath, 'Server Tools: Email address \'' . $emailAddress . '\' has been removed.');
							$outputMessage = 'Email address:<br><span class="gameColor5">' . $emailAddress . '</span><br>has been removed from the game server at:<br><span class="paraTrackerColor">' . $serverAddress . '</span><span class="gameColor7">:</span><span class="paraTrackerVersionColor">' . $serverPort . '</span>!';
						} else {
							$outputMessage = 'Invalid token!';
							$serverSettings['emailAlerts']['addresses'][$i]['emailFloodProtect'] = time();
							writeSettingsFile($dynamicIPAddressPath, $serverSettings);
						}
					} else {
						$outputMessage = 'Too many requests made in a short time! Try again.';
					}
				}
				$match = true;
				break;	// We found a match. Break here.
			}
		}
		if(!$match) $outputMessage = 'Email address not found!';

		// Render a page here with what we need for output
		$output = serverToolsHTMLDeclarations('Server Tools');

		$output .= '<div class="serverToolsCategory">';
		$output .= '<h2 class="gameColor2">' . $outputMessage . '</h2>';
	//	$output .= renderButton('emailAlertsMenu', 'Continue', true, 'greenButton');
		$output .= '<br></div>';
		$output .= '</body></html>';

		echo $output;
		exit;
	}



	// These need to be globals
	if(getPostData('serverToolsAddress') !== false)
	{
		define('serverIPAddress', ipAddressValidator(getPostData('serverToolsAddress')));
	} elseif(getCookieData('serverToolsAddress') !== false) {
		define('serverIPAddress', ipAddressValidator(getCookieData('serverToolsAddress')));
	}
	if(getPostData('serverToolsPort') !== false)
	{
		define('serverPort', numericValidator(getPostData('serverToolsPort'), 0, 99999, 29070));
	} elseif(getCookieData('serverToolsPort') !== false) {
		define('serverPort', numericValidator(getCookieData('serverToolsPort'), 0, 99999, 29070));
	}
	if(defined('serverIPAddress') && defined('serverPort'))
	{
		define('dynamicServerToolsAddressPath', makeDynamicAddressPath(serverIPAddress, serverPort));

		// Prevent path exploits...this is especially important since we allow log viewing below
		if(str_contains(dynamicServerToolsAddressPath, '..'))
		{
			displayError('Invalid server: ' . serverIPAddress . ':' . serverPort . '<br>Nice try.<br>This event has been logged.', '', '');
			exit;
		}

	}

	if(isset($_GET['data']))
	{
		$getData = json_decode(urldecode($_GET['data']), true);
		if(isset($getData['warnings'])) $warnings = array_merge($warnings, $getData['warnings']);
		if(isset($getData['messages'])) $messages = array_merge($messages, $getData['messages']);
		if(isset($getData['action'])) $postAction = $getData['action'];
		if(isset($getData['postData'])) $postData = $getData['postData'];
	}


	if(defined('dynamicServerToolsAddressPath'))
	{
		$whatToDo = getPostData('action');

		// This has to be checked before we check for a valid session
		if($whatToDo == 'login')
		{
			$RConPassword = getPostData('serverToolsPassword');

			if(serverIPAddress !== false && serverPort !== false && $RConPassword !== false)
			{
				$result = logInOverRCON($RConPassword);
				if($result) {
					redirectPageAndGoto('login', false);
				} else {
					redirectPageAndGoto('mainMenu', true);
				}
				exit;
			}
			else
			{
				// Failed for an unknown reason. Terminate here. The warning we need was probably generated elsewhere
				renderServerToolsLoginPage();
				exit;
			}
		}

		$tokenTimeout = serverToolsCheckTokenFromFile(dynamicServerToolsAddressPath, getCookieData('serverToolsToken'));
		if($tokenTimeout !== false)
		{
			// If we're here from a redirect, get the appropriate action to perform
			if(isset($postAction))
			{
				$whatToDo = $postAction;
			}

			// Set the session timeout to the remaining time in the session
			define('sessionTimeout', $tokenTimeout);

			// Since we have a valid session, show sensitive data here
			switch($whatToDo)
			{
				case 'logOut':
					serverToolsLogOut(dynamicServerToolsAddressPath, getCookieData('serverToolsToken'));
					redirectPageAndGoto();
					break;


				case 'visibilityMenuMakePublic':
					makeServerPublic();
					redirectPageAndGoto('visibilityMenu');
					exit;
					break;


				case 'visibilityMenuMakePrivate':
					makeServerPrivate();
					redirectPageAndGoto('visibilityMenu');
					exit;
					break;


				case 'visibilityMenu':
					renderVisibilityMenu();
					exit;
					break;


				case 'removeDosKey':
					renderConfirmation('remove your server\'s DoS key', '', 'removeDosKeyConfirmed', 'dosMenu');
					exit;
					break;


				case 'removeDosKeyConfirmed':
					removeServerDoSKey();
					redirectPageAndGoto('dosMenu');
					exit;
					break;


				case 'generateDosKey':
					renderConfirmation('overwrite the old DoS key', 'You will have to update this value with your server provider/firewall for the service to work again.', 'generateDosKeyConfirmed', 'dosMenu');
					exit;
					break;


				case 'generateDosKeyConfirmed':
					createServerDoSKey();
					redirectPageAndGoto('dosMenu');
					exit;
					break;


				case 'dosMenu':
					renderDosMenu();
					exit;
					break;


				case 'contactUsSendEmail':
					// If emails aren't enabled or there are no administrators, go back to the menu
					if(emailEnabled && count(emailAdministrators) > 0)
					{
						contactUsSendEmail();
					}
					redirectPageAndGoto('mainMenu');
					exit;
					break;


				case 'contactUs':
					if(emailEnabled && serverToolsAllowEmailAdministrators)
					{
						if(count(emailAdministrators) <= 0)
						{
							array_push($messages, 'No administrator email addresses configured!');
							redirectPageAndGoto('mainMenu');
							exit;
							break;
						}
						renderContactUsForm();
						exit;
						break;
					}
					renderServerToolsMenu();
					exit;
					break;


				case 'removeEmailAddressConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}

					$address = getPostData('emailAddress');

					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$match = false;
					for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
					{
						if(strtolower($serverSettings['emailAlerts']['addresses'][$i]['address']) == strtolower($address))
						{
							$serverSettings['emailAlerts']['addresses'] = array_removeIndex($serverSettings['emailAlerts']['addresses'], $i, 1);
							$match = true;
							break;
						}
					}
					if($match)
					{
						writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

						$subject = trackerName() . ' ALERT: Email address removed!';
						$message = '<h4>This message was sent to inform you that this email address, <span style="' . emailHeadingColor3 . '">' . fixHyperlinksForGmail($address) . '</span>, has been removed from the game server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span>.</h4>';
						sendEmail($address, $subject, $message);

						array_push($messages, 'Email address "' . $address . '" removed!');
					} else {
						array_push($warnings, 'Error: Could not remove email address "' . $address . '"!');
					}
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'removeEmailAddress':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}

					$emailAddress = getPostData('emailAddress');

					$output = serverToolsHTMLDeclarations('Unsubscribe from email alerts');
					$output .= '<div class="serverToolsCategory">';
					$output .= '<h4 class="serverToolsCategoryHeading">Are you sure you want to remove <br><span class="gameColor1">' . $emailAddress . '</span>?</h4>';
					$output .= '<div style="display: flex; flex-direction: row;">';
					$output .= '<div style="flex: 1;"></div>';
					$output .= '<div style="flex: 1;">' . renderButton('emailAlertsMenu', '&nbsp;&nbsp;No&nbsp;&nbsp;', true, 'redButton') . '</div>';
					$output .= '<div style="flex: 1;">' . startServerToolsForm('removeEmailAddressConfirmed', 'removeEmailAddressConfirmed', false) . '
					<input type="hidden" name="emailAddress" value="' . $emailAddress . '">
					' . endServerToolsForm('&nbsp;&nbsp;Yes&nbsp;&nbsp;', 'removeEmailAddressConfirmed', 'greenButton') . '
					</div>';
					$output .= '<div style="flex: 1;"></div>';
					$output .= '</div>';
					$output .= '</body></html>';
					echo $output;
					exit;
					break;


				case 'resendEmailVerification':
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					// Find the correct email on the list
					$email = strtolower(getPostData('emailAddress'));

					for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
					{
						if($email == $serverSettings['emailAlerts']['addresses'][$i]['address'])
						{
							if($serverSettings['emailAlerts']['addresses'][$i]['verified'])
							{
								//We're already verified.
								array_push($messages, 'This email address is already verified!');
								redirectPageAndGoto('emailAlertsMenu');
								exit;
							}
							sendVerificationEmail($serverSettings['emailAlerts']['addresses'][$i]);
							array_push($messages, 'Email verification message sent!');
							redirectPageAndGoto('emailAlertsMenu');
							exit;
						}
					}
					array_push($warnings, 'Email address not found on this server:<br>' . $email);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'addEmailAddressToServerConfirmation':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}

					$email = getPostData('serverToolsAddEmail');
					if($email == '')
					{
						array_push($warnings, 'Enter a valid email address!');
						redirectPageAndGoto('addEmailAddressToServer', true);
						exit;
						break;
					}

					if(!filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						array_push($warnings, 'Invalid email address!');
						redirectPageAndGoto('addEmailAddressToServer', true);
						exit;
						break;
					}

					if(addEmailAddressToServer($email)) array_push($messages, 'Email address added, verification pending!');
					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Email address \'' . $email . '\' has been added, pending verification.');
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'addEmailAddressToServer':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					renderAddEmailAddress();
					exit;
					break;


	//	*****************************************************************


				case 'enableserverOfflineAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['serverOffline']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server offline email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">server offline</span>
					alerts for your game server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'enableserverOfflineAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">server offline</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


				case 'disableserverOfflineAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['serverOffline']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server offline email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">server offline</span>
					alerts for your server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'disableserverOfflineAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">server offline</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


	//	*****************************************************************


				case 'enableserverToolsAccessedAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['serverToolsAccessed']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server tools access email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">server tools access</span>
					alerts for your server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'enableserverToolsAccessedAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">server tools access</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


				case 'disableserverToolsAccessedAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['serverToolsAccessed']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server tools access email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">server tools access</span>
					alerts for your server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'disableserverToolsAccessedAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">server tools access</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


	//	*****************************************************************


				case 'enablerconCommandSentAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['rconCommandSent']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: RCON email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">RCON usage</span>
					alerts for your server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'enablerconCommandSentAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = true;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">RCON usage</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


				case 'disablerconCommandSentAlertsConfirmed':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
					$serverSettings['emailAlerts']['reasons']['rconCommandSent']['active'] = $enable;
					writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

					writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: RCON email alerts ' . ($enable ? 'enabled' : 'disabled') . '.');

					// Send an email
					$subject = 'Email alerts changed!';
					$message = '<p>This message was sent to inform you that <span style="' . emailHeadingColor4 . '">RCON usage</span>
					alerts for your server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been <span style="' . emailHeadingColor3 . '">' . ($enable ? 'enabled' : 'disabled') . '</span>.</p>';
					sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $serverSettings, $subject, $message);
					redirectPageAndGoto('emailAlertsMenu');
					exit;
					break;


				case 'disablerconCommandSentAlerts':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					$enable = false;
					renderConfirmation(($enable ? 'enable' : 'disable') . ' <span class="gameColor1">RCON usage</span> email alerts', '', $whatToDo . 'Confirmed', 'emailAlertsMenu');
					exit;
					break;


	//	*****************************************************************


				case 'emailAlertsMenu':
					// If emails aren't enabled, go back to the menu
					if(!emailEnabled)
					{
						redirectPageAndGoto('mainMenu');
						exit;
						break;
					}
					renderemailAlertsMenu();
					exit;
					break;


				case 'securityLog':
					renderServerToolsServerLog();
					exit;
					break;


				default:
					renderServerToolsMenu();
					exit;
					break;
			}
		}
	}

	if(getCookieData('serverToolsToken') !== false)
	{
		// Invalid token.
		array_push($warnings, 'Token invalid or expired! Log in again.');
		clearServerToolsCookies();
	}

	renderServerToolsLoginPage();
	exit;

}

function redirectPageAndGoto($action = 'mainMenu', $keepPostData = false)
{
	global $warnings;
	global $messages;

	$output = array();
	$output['action'] = $action;

	if(count($warnings) > 0)
	{
		$output['warnings'] = $warnings;
	}

	if(count($messages) > 0)
	{
		$output['messages'] = $messages;
	}

	if($keepPostData && count($_POST) > 0)
	{
		$output['postData'] = array();
		foreach($_POST as $key => $value)
		{
			if(stripos($key, 'action') !== false)
			{
				$output['action'] = $value;		// If there's an action, put it where it belongs. It doesn't go here
				continue;
			}

			if(stripos($key, 'pass') === false)	// Make sure we don't send any passwords in the page URL!!
			{
				$output['postData'][$key] = $value;
			}
		}
	}

	http_response_code( 303 );
	header("Location: ". $_SERVER['REQUEST_URI'] . '?data=' . urlencode(json_encode($output)));
	exit;
}

function clearServerToolsCookies()
{
	clearCookie('serverToolsToken');
	clearCookie('serverToolsAddress');
	clearCookie('serverToolsPort');
}

function makeServerPrivate()
{
	global $messages;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	array_push($messages, 'Server visibility updated!');
	writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server is now private.');

	$serverSettings['serverIsPrivate'] = true;
	writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);
}

function makeServerPublic()
{
	global $messages;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	array_push($messages, 'Server visibility updated!');
	writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Server is now public.');

	$serverSettings['serverIsPrivate'] = false;
	writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);
}

function createServerDoSKey()
{
	global $messages;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	if(array_key_exists('serverDDOSKey', $serverSettings))
	{
		array_push($messages, 'DoS key changed!');
		writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: DoS key changed.');
	} else {
		array_push($messages, 'DoS key added!');
		writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: DoS key added.');
	}
	$newDoSKey = generateToken();
	$serverSettings['serverDDOSKey'] = $newDoSKey;
	writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);
}

function removeServerDoSKey()
{
	global $messages;
	global $warnings;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	if(array_key_exists('serverDDOSKey', $serverSettings))
	{
		unset($serverSettings['serverDDOSKey']);
		array_push($messages, 'DoS key removed!');
	} else {
		array_push($warnings, 'Server does not have a DoS key to remove!');
	}
	writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: DoS key removed.');
	writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);
}

function addEmailAddressToServer($newAddress)
{
	global $warnings;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	if(!isset($serverSettings['emailAlerts']['addresses']))
	{
		$serverSettings['emailAlerts'] = generateBlankEmailAlertsArray();
	}
	for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
	{
		if(strtolower($serverSettings['emailAlerts']['addresses'][$i]['address']) == strtolower($newAddress))
		{
			//This email has already been added!
			array_push($warnings, 'This email address has already been added!');
			return false;
		}
	}

	$newEmailData = generateEmailAccountData($newAddress);
	array_push($serverSettings['emailAlerts']['addresses'], $newEmailData);

	writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);

	sendVerificationEmail($newEmailData);

	return true;
}

function sendVerificationEmail($emailAccountData)
{
	if($emailAccountData['verified']) return true;	// We shouldn't be here if the address is already verified

	$subject = trackerName() . ': Verify email address for game server at ' . serverIPAddress . ':' . serverPort;
	// serverIPAddress . ':' . serverPort
	$message = '<p>Your email address has been added as an administrator for the game server at: <a style="font-weight: bold;" href="https://' . webServerName . '/' . utilitiesPath . basename($_SERVER['PHP_SELF']) . '?" target="_blank">' . serverIPAddress . ':' . serverPort . '</a></p>
	<p>To confirm and receive email alerts, <a href="https://' . webServerName . '/' . utilitiesPath . basename($_SERVER['PHP_SELF']) . '?action=verify&token=' . $emailAccountData['verifyToken'] . '&address=' . urlencode(serverIPAddress) . '&port=' . urlencode(serverPort) . '&email=' . urlencode($emailAccountData['address']) . '" style="color: #EC1; font-weight: bold;" target="_blank">click here</a>.</p>';

	$success = true;
	$success = sendEmail($emailAccountData['address'], $subject, $message, 'address=' . urlencode(serverIPAddress) . '&port=' . urlencode(serverPort) . '&email=' . urlencode($emailAccountData['address']) . '&action=unsubscribe&token=' . $emailAccountData['unsubscribeToken']);

	return $success;
}

function generateEmailAccountData($newAddress)
{
	$token1 = generateToken();
	$token2 = generateToken();

	// Make sure these aren't the same
	while($token1 == $token2)
	{
		$token2 = generateToken();
	}

	return json_decode('{
		"verified": false,
		"address": ' . json_encode($newAddress) . ',
		"unsubscribeToken": ' . json_encode($token1) . ',
		"verifyToken": ' . json_encode($token2) . ',
		"emailFloodProtect"	:	0
	}', true);
}

function generateBlankEmailAlertsArray()
{
/*
	{
		"unsubscribeFloodProtect"	:	time(),
		"lastOfflineMessage"	:	time(),
		"reasons"	:	{
			"serverOffline"	:	{
				"active"	:	true,
				"time"	:	time,
			},
			"serverToolsAccessed"	:	{
				"active"	:	"true"
			},
			"serverToolsChanged"	:	{
				"active"	:	"true"
			},
			"rconCommandSent"	:	{
				"active"	:	"true"
			}
		},
		"addresses"	:	[
			{
				"verified"	:	true/false,
				"address"	:	test@test.net,
				"unsubscribeToken"	:	"token",
				"verifyToken"	:	"token",
			}
		]
	}
*/
	return json_decode('{
		"unsubscribeFloodProtect"	:	0,
		"lastOfflineMessage"	:	0,
		"reasons"	:	{
			"serverOffline"	:	{
				"active"	:	false,
				"offlineTime"	:	30,
				"lastEmailTime"	:	0
			},
			"serverToolsAccessed"	:	{
				"active"	:	false
			},
			"serverToolsChanged"	:	{
				"active"	:	false
			},
			"rconCommandSent"	:	{
				"active"	:	false
			}
		},
		"addresses"	:	[]
	}', true);
}

function sendEmailAlertsWithUnsubscribeTokens($serverIPAddress, $serverPort, $serverSettings, $subject, $message, $ignoreToken = false)
{
	if(!isset($serverSettings['emailAlerts']['addresses'])) return;

	$subject = trackerName() . ' ALERT: ' . $subject;
	$token = '';

	for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
	{
		if(!$serverSettings['emailAlerts']['addresses'][$i]['verified']) continue;

		if(!$ignoreToken)
		{
			$token = 'address=' . urlencode($serverIPAddress) . '&port=' . urlencode($serverPort) . '&email=' . urlencode($serverSettings['emailAlerts']['addresses'][$i]['address']) . '&action=unsubscribe&token=' . $serverSettings['emailAlerts']['addresses'][$i]['unsubscribeToken'];
		}

		sendEmail($serverSettings['emailAlerts']['addresses'][$i]['address'], $subject, $message, $token);
	}
}

function renderConfirmation($text, $subtext, $actionOnYes, $actionOnNo)
{
	$output = serverToolsHTMLDeclarations($text);

	$output .= '<div class="serverToolsCategory">';
	$output .= '<h4 class="serverToolsCategoryHeading">Are you sure you want to ' . $text . '?</h4>';
	if($subtext != '') $output .= '<p>' . $subtext . '</p>';
	$output .= '<div style="display: flex; flex-direction: row;">';
	$output .= '<div style="flex: 1;"></div>';
	$output .= '<div style="flex: 1;">' . renderButton($actionOnNo, '&nbsp;&nbsp;No&nbsp;&nbsp;', false, 'redButton') . '</div>';
	$output .= '<div style="flex: 1;">' . renderButton($actionOnYes, '&nbsp;&nbsp;Yes&nbsp;&nbsp;', false, 'greenButton') . '</div>';
	$output .= '<div style="flex: 1;"></div>';
	$output .= '</div>';
	$output .= '</body></html>';

	echo $output;
}

function renderAddEmailAddress()
{
	global $postData;

	$output = serverToolsHTMLDeclarations('Add Email Address');

	$output .= '<div class="serverToolsCategory">';
	$output .= '<h4 class="serverToolsCategoryHeading">Add Email Address</h4>';

	$formName = 'addEmailAddressToServerConfirmation';
	$output .= startServerToolsForm($formName, $formName);

	$output .= '<p>Type your email address here to associate it with this game server.</p>';
	$output .= '<p>Once your game server has at least one verified email address, you can configure alert settings and will receive emails.</p>';
	$output .= '<br>';
	$email = getPostData('serverToolsAddEmail');
	if($email == '' && isset($postData['serverToolsAddEmail']))
	{
		$email = $postData['serverToolsAddEmail'];
	}
	$email = stringValidator($email, maxEmailAddressLength, '');
	$output .='<div><input id="serverToolsAddEmail" name="serverToolsAddEmail" class="" size="50" type="text" value="' . $email . '" placeholder="" />
	</div><br>';

	$output .= endServerToolsForm('Add Email Address', $formName, 'greenButton');

	$output .= renderButton('emailAlertsMenu', 'Go Back', false, '');

	$output .= '</body></html>';

	echo $output;
}

function renderemailAlertsMenu()
{
	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	$output = serverToolsHTMLDeclarations('Email Alerts');

	$output .= '<div class="serverToolsCategory">';
	$output .= '<h4 class="serverToolsCategoryHeading">Email Alerts</h4>';
	$output .= '<p>These settings will allow you to configure ' . trackerName() . ' to send you an email when certain conditions are met.</p>';

	$output .= emailHr1;

	if(isset($serverSettings['emailAlerts']['addresses']) && count($serverSettings['emailAlerts']['addresses']) > 0)
	{
		for($i = 0; $i < count($serverSettings['emailAlerts']['addresses']); $i++)
		{
			$output .= '<div class="serverToolsKeysAndValues noBottomMargin">';
			$output .= '<div class="serverToolsKey"><span class="gameColor8">' . ($i + 1) . '. </span>' . $serverSettings['emailAlerts']['addresses'][$i]['address'] . '</div>
			</div><div class="serverToolsKeysAndValues noBottomMargin noTopMargin">
			<div style="flex: 1;"></div>
			<div style="flex: 2; text-align: center;" class="serverToolsKey gameColor3">Status:</div>
			<div style="flex: 2; text-align: center;" class="serverToolsValue ' . ($serverSettings['emailAlerts']['addresses'][$i]['verified'] ? 'gameColor2' : 'gameColor1') . '">' . ($serverSettings['emailAlerts']['addresses'][$i]['verified'] ? 'Verified' : 'Not verified') . '</div>
			<div style="flex: 1;"></div>
			</div><div class="serverToolsKeysAndValues noTopMargin">';

			if(!$serverSettings['emailAlerts']['addresses'][$i]['verified'])
			{
				// If we aren't verified, display a button to perform the verification
				$formName = 'emailAddress' . strval($i);
				$output .= '<div style="flex: 1;">';
				$output .= startServerToolsForm('resendEmailVerification', $formName);
				$output .= '<input type="hidden" name="emailAddress" value="' . $serverSettings['emailAlerts']['addresses'][$i]['address'] . '" />';
				$output .= endServerToolsForm('Resend Verification Email', $formName, 'purpleButton');
				$output .= '</div>
				<div style="flex: 1;"></div>';			
			}
			$formName = 'removeEmailAddress' . $i;
			$output .= '<div style="flex: 1;" class="serverToolsValue">
			' . startServerToolsForm('removeEmailAddress', $formName) . '
			<input type="hidden" name="emailAddress" value="' . $serverSettings['emailAlerts']['addresses'][$i]['address'] . '" />
			' . endServerToolsForm('Remove Address', $formName, 'redButton') . '</div>';

			$output .= '</div>' . emailHr2;
		}
	} else {
		$output .= '<h3 class="gameColor1">No email addresses configured!</h3>';
	}

	$output .= '<br>' . renderButton('addEmailAddressToServer', 'Add Email Address', false, 'greenButton') . '<br>';


	$output .= emailHr1;
	$output .= '<h2 class="gameColor5">Active alerts</h2>';


	$output .= '<div class="serverToolsKeysAndValues serverToolsEmailReasons"><div class="serverToolsKey">Server Being Removed</div> <div class="serverToolsValue">Always Enabled</div></div>';
	$output .= '<p class="noTopMargin">Send an email alert when the game server and all tracked data is being deleted from ' . trackerName() . '. This will take place after <span class="gameColor1">' . strval(deleteInterval) . '</span> days of no contact with the game server.</p><br>';

	if(analyticsEnabled)	// Analytics is required for this because we need the database
	{
		$reason = 'serverOffline';
		$output .= '<div class="serverToolsKeysAndValues noBottomMargin noBottomPadding"><div class="serverToolsKey">Server Is Offline</div><div class="serverToolsValue">';
		if(isset($serverSettings['emailAlerts']['reasons'][$reason]['active']) && $serverSettings['emailAlerts']['reasons'][$reason]['active'])
		{
			$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor2">Enabled</span>';
			$output .= renderButton('disable' . $reason . 'Alerts', 'Disable', false, 'redButton');
			$output .= '</div>';
		} else {
			$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor1">Disabled</span>';
			$output .= renderButton('enable' . $reason . 'Alerts', 'Enable', false, 'greenButton');
			$output .= '</div>';
		}
		$output .= '</div></div>
		<p class="noTopMargin noTopPadding">Send an email alert when the game server is offline for 30 minutes, and once every day after that</p><br>';
	}

	$reason = 'serverToolsAccessed';
	$output .= '<div class="serverToolsKeysAndValues noBottomMargin noBottomPadding"><div class="serverToolsKey">Server Tools Accessed</div><div class="serverToolsValue">';
	if(isset($serverSettings['emailAlerts']['reasons'][$reason]['active']) && $serverSettings['emailAlerts']['reasons'][$reason]['active'])
	{
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor2">Enabled</span>';
		$output .= renderButton('disable' . $reason . 'Alerts', 'Disable', false, 'redButton');
		$output .= '</div>';
	} else {
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor1">Disabled</span>';
		$output .= renderButton('enable' . $reason . 'Alerts', 'Enable', false, 'greenButton');
		$output .= '</div>';
	}
	$output .= '</div></div>
	<p class="noTopMargin noTopPadding">Send an email alert when someone logs into server tools</p><br>';

/*
	$reason = 'serverToolsChanged';
	$output .= '<div class="serverToolsKeysAndValues noBottomMargin noBottomPadding"><div class="serverToolsKey">Server Tools Changes</div><div class="serverToolsValue">';
	if(isset($serverSettings['emailAlerts']['reasons'][$reason]['active']) && $serverSettings['emailAlerts']['reasons'][$reason]['active'])
	{
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor2">Enabled</span>';
		$output .= renderButton('disable' . $reason . 'Alerts', 'Disable', false, 'redButton');
		$output .= '</div>';
	} else {
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor1">Disabled</span>';
		$output .= renderButton('enable' . $reason . 'Alerts', 'Enable', false, 'greenButton');
		$output .= '</div>';
	}
	$output .= '</div></div>
	<p class="noTopMargin noTopPadding">Send an email alert when changes are made in server tools</p><br>';
*/

	$reason = 'rconCommandSent';
	$output .= '<div class="serverToolsKeysAndValues noBottomMargin noBottomPadding"><div class="serverToolsKey">RCON Usage</div><div class="serverToolsValue">';
	if(isset($serverSettings['emailAlerts']['reasons'][$reason]['active']) && $serverSettings['emailAlerts']['reasons'][$reason]['active'])
	{
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor2">Enabled</span>';
		$output .= renderButton('disable' . $reason . 'Alerts', 'Disable', false, 'redButton');
		$output .= '</div>';
	} else {
		$output .= '<div class="serverToolsKeysAndValues noTopMargin noTopPadding noBottomMargin noBottomPadding"><span class="serverToolsValue gameColor1">Disabled</span>';
		$output .= renderButton('enable' . $reason . 'Alerts', 'Enable', false, 'greenButton');
		$output .= '</div>';
	}
	$output .= '</div></div>
	<p class="noTopMargin noTopPadding">Send an email alert when someone sends an RCON command to<br>your game server through ' . trackerName() . '</p><br>';


//	$output .= emailHr1;
	$output .= '</div><br><br></body></html>';

	echo $output;
}
	
function renderVisibilityMenu()
{
	global $messages;

	$output = serverToolsHTMLDeclarations('Server Visibility');

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
	if(array_key_exists('serverIsPrivate', $serverSettings) && $serverSettings['serverIsPrivate'] == true)
	{
		$status = true;
	} else {
		$status = false;
	}

	$output .= '<div class="serverToolsCategory">';
	$output .= '<h4 class="serverToolsCategoryHeading">Server Visibility</h4>';
	$output .= '<p>This setting controls whether your server is visible on ' . trackerName() . '\'s public list.</p>';
	$output .= '<p>' . trackerName() . ' will still treat your server exactly the same otherwise.</p>';

	$output .= toolsHr1;
	$output .= '<div class="serverToolsKeysAndValues"><div class="serverToolsKey">Server Visibility:</div> <div class="serverToolsValue ' . ($status ? 'gameColor3' : 'gameColor2') . '"><span class="' . (count($messages) > 0 ? ' flashRedBackground' : '') . '">' . ($status ? 'Private' : 'Public') . '</span></div></div>';
	$output .= toolsHr1;

	$output .= '<div style="display: flex; flex-direction: row;">';

	if($status)
	{
		$output .= '<div style="flex: 1;"><button class="dynamicFormButtons dynamicFormButtonsStyle disabledButton">Make Server Private</button></div>';
		$output .= '<div style="flex: 1;">' . renderButton('visibilityMenuMakePublic', 'Make Server Public', false, 'greenButton') . '</div>';
	} else {
		$output .= '<div style="flex: 1;">' . renderButton('visibilityMenuMakePrivate', 'Make Server Private', false, 'yellowButton') . '</div>';
		$output .= '<div style="flex: 1;"><button class="dynamicFormButtons dynamicFormButtonsStyle disabledButton">Make Server Public</button></div>';
	}

	$output .= '</div>';

	$output .= '</body></html>';

	echo $output;
}

function renderDosMenu()
{
	global $messages;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	$formName = 'dosMenu';
	$output = serverToolsHTMLDeclarations('DoS Filtering');

	$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">DoS Filtering</h4>';

	$output .= '<p>UDP connections allow IP addresses to be spoofed, so a malicious actor can spoof traffic from ' . trackerName() . ' to DoS attack a game server. This has caused ' . trackerName() . ' to be blocked by some game server hosts.</p>
	<p>Enabling DoS filtering will append a harmless string of text to everything ' . trackerName() . ' sends to your game server. With this enabled, your server host can differentiate between real traffic from ' . trackerName() . ' and malicious traffic.</p>
	<p class="gameColor8">You can only have one DoS key on your server at a time. Generating a new one will erase the old one.</p>' . toolsHr1;

	$output .= '<p class="serverToolsKeysAndValues"><span class="serverToolsKey">DoS Key:</span> <span class="serverToolsValue"><span class="' . (count($messages) > 0 ? ' flashRedBackground' : '') . '">' . (array_key_exists('serverDDOSKey', $serverSettings) ? $serverSettings['serverDDOSKey'] : 'Not Enabled') . '</span></span></p>';

	$output .= toolsHr1;

	if(array_key_exists('serverDDOSKey', $serverSettings))
	{
		$output .= renderButton('generateDosKey', 'Generate New DoS Key', false, 'yellowButton');
	} else {
		$output .= renderButton('generateDosKeyConfirmed', 'Generate New DoS Key', false, 'greenButton');
	}

	if(array_key_exists('serverDDOSKey', $serverSettings)) $output .= renderButton('removeDosKey', 'Remove DoS Key', false, 'redButton');

	$output .= '</body></html>';
	echo $output;
}

function contactUsSendEmail()
{
	global $messages;
	global $warnings;

	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);
	if(isset($serverSettings['emailTime']) && $serverSettings['emailTime'] + emailFloodProtect - time() > 0)
	{
		array_push($warnings, 'Wait ' . $serverSettings['emailTime'] + emailFloodProtect - time() . ' seconds to send another message.');
		redirectPageAndGoto('contactUs', true);
		exit;
	} else {
		$address = stringValidator(getPostDataForForm('emailContactAddress'), maxEmailAddressLength, '', false);
		$message = stringValidator(getPostDataForForm('emailMessage'), maxEmailMessageLength, '', false);

		if($address != '' && $message != '')
		{
			$subject = 'Message from server owner of: ' . serverIPAddress . ':' . serverPort;
			$sendMessage = serverToolsEmailMessage($address, $message);

			$success = true;
			$success = sendEmail(emailAdministrators, $subject, $sendMessage);
			if($success)
			{
				$serverSettings['emailTime'] = time();
				writeSettingsFile(dynamicServerToolsAddressPath, $serverSettings);
				array_push($messages, 'Message sent!');
				writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: Email sent to ' . trackerName() . ' administrators.  Response address:   ' . str_replace("\n", ' ', $address) . '   Message:   ' . str_replace("\n", ' ', $message));
				redirectPageAndGoto('mainMenu');
				exit;
			} else {
				array_push($warnings, 'Message failed to send!');
				renderContactUsForm();
				exit;
			}
		} else {
			if($address == '') array_push($warnings, 'No email address provided!');
			if($message == '') array_push($warnings, 'Message is blank!');
			renderContactUsForm();
			exit;
		}
	}
}

function renderContactUsForm()
{
	global $postData;

	$formName = 'contactUs';
	$output = serverToolsHTMLDeclarations('Contact Us');

	$address = stringValidator(getPostDataForForm('emailContactAddress'), maxEmailAddressLength, '', false);
	$message = stringValidator(getPostDataForForm('emailMessage'), maxEmailMessageLength, '', false);

	if(isset($postData['emailContactAddress'])) $address = stringValidator($postData['emailContactAddress'], maxEmailAddressLength, '', false);
	if(isset($postData['emailMessage'])) $message = stringValidator($postData['emailMessage'], maxEmailMessageLength, '', false);

	$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">Contact Us</h4>';

	if($address != '' || $message != '')
	{
		$form2Name = 'contactUsSendEmail';
		$output .= '<h2>Message Preview:</h2>
		<div class="serverToolsEmailPreview">' . emailHeader() . serverToolsEmailMessage($address, $message) . emailFooter() . '</div>';
		$output .= '<br>';

		if($address != '' && $message != '')
		{
			$output .= startServerToolsForm($form2Name, $form2Name);
			$output .= endServerToolsForm('Send Message', $form2Name, 'redButton');
		}
		$output .= '<br>' . emailHr1 . '<br>';
	} else {
		$output .= '<p class="serverToolsInfo">Use the form below to send an email to the administrator(s) of ' . trackerName() . '.</p>';

	}
	$output .= startServerToolsForm('contactUs', $formName);

	$output .= '<h2 class="noBottomMargin">Your email address:</h2><p class="noTopMargin noBottomMargin">(So we can respond)</p>
	<P class="noTopMargin"><span id="emailAddressCharacterCount" class="';
	if(strlen($address) >= maxEmailAddressLength * .75)
	{
		$output .= 'gameColor3';
	} else if(strlen($address) >= maxEmailAddressLength)
	{
		$output .= 'gameColor1';
	} else {
		$output .= 'gameColor2';
	}
	$output .= '">' . number_format(strlen($address), 0, '.', ',') . '</span> / ' . number_format(maxEmailAddressLength, 0, '.', ',') . ' characters</p>';
	$output .= '<input id="emailContactAddress" name="emailContactAddress" class="serverToolsLoginForm inputDarkColors serverToolsInput" oninput="disableFormButtons(contactUsSendEmail, \'Message changed! Preview again before sending.\'); updateCharCount(this, emailAddressCharacterCount, ' . maxEmailAddressLength . ');" maxlength="' . maxEmailAddressLength . '" size="40" type="text" value="' . $address . '" placeholder="Your email address" />';

	$output .='<br><br>';

	$output .= '<h2 class="noBottomMargin">Message:</h2><p class="noTopMargin"><span id="emailCharacterCount" class="';
	if(strlen($message) >= maxEmailMessageLength * .75)
	{
		$output .= 'gameColor3';
	} else if(strlen($message) >= maxEmailMessageLength)
	{
		$output .= 'gameColor1';
	} else {
		$output .= 'gameColor2';
	}
	$output .= '">' . number_format(strlen($message), 0, '.', ',') . '</span> / ' . number_format(maxEmailMessageLength, 0, '.', ',') . ' characters</p>';
	$output .= '<textarea id="emailMessage" type="text" name="emailMessage" class="inputDarkColors serverToolsInput" rows="12" cols="65" oninput="disableFormButtons(contactUsSendEmail, \'Message changed! Preview again before sending.\'); updateCharCount(this, emailCharacterCount, ' . maxEmailMessageLength . ');" maxlength="' . maxEmailMessageLength . '" placeholder="Your message here">' . $message . '</textarea>';

    $output .= endServerToolsForm('Preview', $formName, 'greenButton');

	$output .= '</div>';

	$output .= '</body></html>';
	echo $output;
}

function renderServerToolsServerLog()
{
	$logFilePath = dynamicServerToolsAddressPath . serverSecurityLogFilename;
	$count = count(permittedLogFiles);

	$output = '';

	//Extract the file name for matching
	$test = explode('/', $logFilePath);
	if(is_array($test) && count($test) > 0)
	{
		$test = $test[count($test) - 1];
	}

	for($i = 0; $i < $count; $i++)
	{
		if(permittedLogFiles[$i] == $test)
		{
			writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: User viewing security log.');
			$output .= serverToolsHTMLDeclarations('Log Viewer', '../') . '</head><body class="logViewerPage serverToolsPage logPageFlex"><h1>Security Log</h1>
			<h4 class="noTopMargin noBottomMargin noTopPadding noBottomPadding"><ul style="display:inline-block;">
			<li><span class="gameColor5">' . trackerName() . ' logs all RCON commands that are sent through it.</span></li>
			<li><span class="gameColor3">Any changes made to server settings are also logged.</span></li>
			<li><span  class="gameColor2">RCON passwords are <i>never</i> saved by ' . trackerName() . ', only commands.</li>
			</ul></h4>';

			$output .= renderLogFile($logFilePath);
			$output .= '</body></html>';
			echo $output;
			exit;
		}
	}

	global $warnings;
	array_push($warnings, 'Invalid log file specified!');
	redirectPageAndGoto('mainMenu');
	exit;
}

function serverToolsEmailMessage($address = '', $message = '')
{
	$address = stringValidator($address, maxEmailAddressLength, '', false);
	$message = stringValidator($message, maxEmailMessageLength, '', false);

	$output = '<div style="max-width: 650px; margin: 0 auto;"><br><h3 style="margin-bottom: 0px; margin-top: 0px; ' . emailHeadingColor3 . ' ">Message from server administrator of:</h3><h3 style="margin-bottom: 0px; margin-top: 0px; ' . emailHeadingColor3 . '"><span style="color: #c41414;">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="color: #767676;">' . fixHyperlinksForGmail(serverPort) . '</span></h3>';
	$output .= '<br>' . emailHr2 . '<br><h4 style="' . emailHeadingColor4 . ' margin-bottom: 0px; margin-top: 0px;">Respond to this message at:</h4><h4 style="margin-bottom: 0px; margin-top: 0px;">' . fixHyperlinksForGmail($address) . '</h4><br>' . emailHr1;
	$output .= '<pre style="white-space: break-spaces;">' . fixHyperlinksForGmail($message) . '</pre></div>';

	return fixGmailQuotingBug($output);
}

function logInOverRCON($RConPassword)
{
	global $messages;
	global $warnings;

	define('doNotEmailForRCON', true);	// This prevents the login commands from triggering an email to be sent

	clearServerToolsCookies();

	$success = false;

	$sleepTime = 0;
	$sleepInterval = 0.15;
	$lastAttempt = numericValidator(file_get_contents(infoPath . dynamicServerToolsAddressPath . "loginTime.txt"), "", "", 0);
	while(time() - $lastAttempt < loginFloodProtect)
	{
		sleep($sleepInterval);
		$lastAttempt = numericValidator(file_get_contents(infoPath . dynamicServerToolsAddressPath . "loginTime.txt"), "", "", 0);
		$sleepTime += $sleepInterval;
		if($sleepTime > loginFloodProtect * 2.1)
		{
			array_push($warnings, 'Too many login attempts in too short a time span.<br>Someone else may be attempting to use this tool on your server.<br>Wait a few seconds and try again.');
			return false;
		}
	}

	// Update the time file
	file_put_contents(infoPath . dynamicServerToolsAddressPath . "loginTime.txt", time());

	set_time_limit((floodProtectTimeout + RConFloodProtect) * 6);
	ignore_user_abort(true);

	$registrationVarName = str_replace(' ', '', trackerName()) . 'LoginKey';
	$registrationKey = generateToken();

	// Send the token to the server in an RCon command
	$response = sendReceiveRConCommand(serverIPAddress, serverPort, $RConPassword, 'sets ' . $registrationVarName . ' ' . $registrationKey);

	if(strpos(strtolower($response), 'password') !== false)
	{
		//Bad password! Terminate.
		writeFileOut(infoPath . dynamicServerToolsAddressPath . "loginTime.txt", time());
		array_push($warnings, 'Incorrect RCON Password!');
		return false;
	}

	//Now let's update!
	doUpdate(dynamicServerToolsAddressPath);

	//Remove the key from the game server NOW - that way, even if we fail below, this is still guaranteed to run
	$response = sendReceiveRConCommand(serverIPAddress, serverPort, $RConPassword, 'sets ' . $registrationVarName . ' ""');

	//Check the server files to see what's there
	$params = readFileIn(infoPath . dynamicServerToolsAddressPath . "JSONParams.txt");
	$dataStart = strpos($params, ':') + 1;
	$params = substr($params, $dataStart, strlen($params) - $dataStart);
	$jsonParams = json_decode($params, true);

	if($jsonParams === null)
	{
		array_push($warnings, 'Could not contact game server!');
		return false;
	}

	if(!array_key_exists($registrationVarName, $jsonParams))
	{
		array_push($warnings, 'Server login failed!');
		return false;
	}

	$serverKey = $jsonParams[$registrationVarName];

	if($serverKey == $registrationKey)
	{
		$settings = readSettingsFile(dynamicServerToolsAddressPath);
		$currentTokens = serverToolsGetTokens($settings);

		// Make sure we don't accidentally create a duplicate token!
		do {
			$sessionToken = generateToken(30, 40);
		} while (serverToolsCheckTokenFromSettings($settings, $sessionToken) !== false);

		$settings = serverToolsAddTokenToSettings($settings, $sessionToken);
		writeSettingsFile(dynamicServerToolsAddressPath, $settings);

		setcookie('serverToolsAddress', serverIPAddress, time() + serverToolsSessionTimeout + 1, '', '', false, true);
		setcookie('serverToolsPort', serverPort, time() + serverToolsSessionTimeout + 1, '', '', false, true);
		setcookie('serverToolsToken', $sessionToken, time() + serverToolsSessionTimeout + 1, '', '', false, true);

		array_push($messages, 'Successfully logged in to server at ' . serverIPAddress . ':' . serverPort);
		$success = true;

		if(emailEnabled && $settings['emailAlerts']['reasons']['serverToolsAccessed']['active']) {
			// If there is an email address attached to this server, send the login notice
			$subject = 'Server tools accessed';
			$message = '<p>This message was sent to inform you that the <a href="https://' . webServerName . '/' . utilitiesPath . basename($_SERVER['PHP_SELF']) . '" style="' . emailHeadingColor3 . '">server tools</a> for your game server at <span style="' . emailHeadingColor1 . '">' . fixHyperlinksForGmail(serverIPAddress) . '</span><span style="color: #FFF;">:</span><span style="' . emailHeadingColor2 . '">' . serverPort . '</span> have been accessed.</p>
			<p>If this access was not you or another server administrator, then your game server\'s RCON password is compromised and should be changed immediately.</p>';
			sendEmailAlertsWithUnsubscribeTokens(serverIPAddress, serverPort, $settings, $subject, $message);
		}
	}
	else
	{
		array_push($warnings, 'Server login failed!');
		$success = false;
	}

	set_time_limit(30);
	ignore_user_abort(false);
	return $success;
}

function serverToolsLogOut($dynamicIPAddressPath, $removeToken)
{
	$settings = readSettingsFile($dynamicIPAddressPath);
	$tokens = serverToolsGetTokens($settings);
	$settings['tokens'] = serverToolsRemoveToken($tokens, $removeToken);

	writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: User logged out.');

	writeSettingsFile($dynamicIPAddressPath, $settings);

	clearServerToolsCookies();
}

function serverToolsCheckTokenFromFile($dynamicIPAddressPath, $token)
{
	$tokenList = serverToolsGetTokensFromFile($dynamicIPAddressPath);
	return serverToolsCheckToken($tokenList, $token);
}

function serverToolsCheckTokenFromSettings($settings, $token)
{
	if(!array_key_exists('tokens', $settings))
	{
		return false;
	}

	return serverToolsCheckToken($settings['tokens'], $token);
}

function serverToolsCheckToken($currentTokens, $newToken)
{
	for($i = 0; $i < count($currentTokens); $i++)
	{
		// We've already checked and removed timed-out tokens in serverToolsGetTokens(), so just check to make sure there's a token by this name
		if($currentTokens[$i]['key'] == $newToken)
		{
			// If token is valid, return the amount of time remaining in the session
			return $currentTokens[$i]['time'] + serverToolsSessionTimeout - time() - 1;
		}
	}

	// Token is not valid, return false
	return false;
}

function serverToolsGetTokensFromFile($dynamicIPAddressPath)
{
	return serverToolsGetTokens(readSettingsFile($dynamicIPAddressPath));
}

function serverToolsGetTokens($settings)
{
	if(!array_key_exists('tokens', $settings))
	{
		return array();
	}

	$currentTokens = $settings['tokens'];
	$outputTokens = array();

	for($i = 0; $i < count($currentTokens); $i++)
	{
		if($currentTokens[$i]['time'] + serverToolsSessionTimeout >= time())
		{
			// Token has not timed out. Keep it
			array_push($outputTokens, $currentTokens[$i]);
		}
	}

	return $outputTokens;
}

function serverToolsAddTokenToFile($dynamicIPAddressPath, $newToken)
{
	$tokens = array();
	$settings = readSettingsFile($dynamicIPAddressPath);
	$settings = serverToolsAddTokenToSettings($settings, $newToken);
	writeSettingsFile($dynamicIPAddressPath, $settings);
}

function serverToolsAddTokenToSettings($settings, $newToken)
{
	writeSecurityLogEntry(dynamicServerToolsAddressPath, 'Server Tools: User logged in.');
	$settings['tokens'] = serverToolsAddToken(serverToolsGetTokens($settings), $newToken);
	return $settings;
}

function serverToolsAddToken($currentTokens, $newToken)
{
	for($i = 0; $i < count($currentTokens); $i++)
	{
		if($currentTokens[$i]['key'] == $newToken)
		{
			// If the token already exists, do not change it or add another
			return $currentTokens;
		}
	}

	// Token does not already exist. Add it.
	array_push($currentTokens, array('key'=>$newToken, 'time'=>time()));
	return $currentTokens;
}

function serverToolsRemoveToken($settings, $removeToken)
{
	$output = array();
	$tokens = array();

	if(array_key_exists('tokens', $settings))
	{
		$tokens = $settings['tokens'];
	}

	for($i = 0; $i < count($tokens); $i++)
	{
		if($tokens[$i]['key'] == $removeToken)
		{
			continue;
		}
		array_push($output, $tokens[$i]);
	}

	return $output;
}

function renderServerToolsMessages()
{
	global $messages;
	$output = "";
	$count = count($messages);
	for($i = 0; $i < $count; $i++)
	{
		$output .= str_replace("\n", '<br>', '<div id="message' . $i . '" class="serverToolsMessages"><h2 class="serverToolsWarningsText">' . $messages[$i] . '</h2><div class="closeServerToolsMessageButton" onclick="document.getElementById(\'message' . $i . '\').className = \'hiddenStuff\'">X</div></div>');
	}
	
	return $output;
}

function renderServerToolsWarnings()
{
	global $warnings;
	$output = "";
	$count = count($warnings);
	for($i = 0; $i < $count; $i++)
	{
		$output .= str_replace("\n", '<br>', '<div id="warning' . $i . '" class="serverToolsWarnings flashRedBackground"><h2 class="serverToolsWarningsText">' . $warnings[$i] . '</h2><div class="closeServerToolsMessageButton" onclick="document.getElementById(\'warning' . $i . '\').className = \'hiddenStuff\'">X</div></div>');
	}
	
	return $output;
}

function serverToolsHTMLDeclarations($pageTitle, $filePath = '../')
{
	if($pageTitle == '')
	{
		// For some reason this doesn't work in the function declaration...
		$pageTitle = trackerTagline;
	}
	$pageTitle = versionNumber() . ' - ' . $pageTitle;
	$pageTitle = stringValidator($pageTitle, '', '');
	$output = '--><!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="' . $filePath . 'css/ParaStyle.css" type="text/css" />
	<link rel="stylesheet" href="' . $filePath . 'css/LevelshotAnimations.css" type="text/css" />';

	$output .= '<title>' . $pageTitle . '</title>';

	$output .= passConfigValuesToJavascript();


	$output .= '<script src="../js/ParaScript.js"></script>';
	$output .= '<script>
	RConFloodProtect = ' . RConFloodProtect . '
	floodProtectTimeout = ' . floodProtectTimeout . '
	';
	if(defined('sessionTimeout'))
	{
		$output .='serverToolsSessionTimer = ' . sessionTimeout . '
		';
	}
	$output .= '</script>';
	$output .= '</head><body class="serverToolsPage">';

	if(defined('sessionTimeout'))
	{
		$color = '';
		if(sessionTimeout >= 120)
		{
			$color .= 'gameColor2';
		} else if(sessionTimeout >= 60)
		{
			$color .= 'gameColor3';
		} else if(	sessionTimeout == 30 ||
					sessionTimeout == 15 ||
					sessionTimeout == 10 ||
					sessionTimeout <= 5
					)
		{
			$color .= 'gameColor1 flashRedBackground';
		} else
		{
			$color .= 'gameColor1';
		}

		$output .= '<div id="serverToolsTimeoutDiv" class="serverToolsTimeoutDiv"><p class="noTopMargin noBottomMargin gameColor2">Time remaining:</p><p id="serverToolsSessionTimerContainer" class="serverToolsSessionTimer noTopMargin noBottomMargin"><span class="' . $color . '">' . intToTime(sessionTimeout) . '</span></p>
		';
		$output .= renderButton('mainMenu', 'Main Menu', true, 'blueButton');
		$output .= renderButton('logOut', 'Log Out', true, 'purpleButton');
		$output .= '
		</div>';
	}

	$output .= '<h1 class="noBottomMargin">' . versionNumber() . ' - Server Tools</h1>';

	$output .= renderServerHeader('', '', true);

	$output .= '<div class="serverToolsmessageContainer">';

	$output .= renderServerToolsWarnings();
	if(defined('sessionTimeout'))
	{
		$output .= renderServerToolsMessages();
	}
	$output .= '</div>';
	return $output;
}

function intToTime($input)
{
	$output = array();

	if($input < 0) $input = 0;
	$hours = floor($input / 3600);
	if($hours > 0) array_push($output, $hours);

	$minutes = floor(($input % 3600) / 60);
	if($minutes > 0) array_push($output, ($hours > 0 ? str_pad(strval($minutes), 2, '00', STR_PAD_LEFT) : strval($minutes)));

	$seconds = $input % 60;
	array_push($output, str_pad(strval($seconds), 2, '00', STR_PAD_LEFT));

	return implode(':', $output);
}

function startServerToolsForm($action = "", $formName = "", $resetFormData = false)
{
	global $postData;

	if($action != "") $action = '<input name="action" type="hidden" value="' . $action . '"></input>';
	$output = '<form id="' . $formName . '" action="' . basename($_SERVER['PHP_SELF']) . '" class="centerElement" method="post">' . "\n";

	if($resetFormData)
	{
		// This is kind of hackish, but we need all of this in each form...
		$i = 0;
		foreach($_POST as $key => $value)
		{
			$output .= '<input type="hidden" id="' . $key . '" name="' . stringValidator($key, 100, 'postInputKey' . $i) . '" value="'. stringValidator($value, maxEmailMessageLength, 'postInputValue' . $i) . '"></input>' . "\n";
			$i++;
		}

		foreach($postData as $key => $value)
		{
			$output .= '<input type="hidden" id="' . $key . '" name="' . stringValidator($key, 100, 'postInputKey' . $i) . '" value="'. stringValidator($value, maxEmailMessageLength, 'postInputValue' . $i) . '"></input>' . "\n";
			$i++;
		}
	}

	$output .= $action . "\n";
	return $output;
}

function endServerToolsForm($buttonText, $formName = "", $customStyle = '')
{
	if($formName == "")
	{
		$onclickEvent = '';
	}
	else
	{
		$onclickEvent = 'onclick="submitFormButRequireAllFields(' . $formName . ')"';
	}
    return  '<button type="button" class="dynamicFormButtons dynamicFormButtonsStyle ' . ($customStyle == '' ? '' : $customStyle) . '" ' . $onclickEvent . '>' . $buttonText . '</button></form>';
}

function renderServerHeader($before = '', $after = '', $noMargins = false)
{
	if(!defined('serverIPAddress') || !defined('serverPort')) return '';
	$output = $before . ' <span class="paraTrackerColor">' . serverIPAddress . '</span><span class="infoDivColor7">:</span><span class="paraTrackerVersionColor">' . serverPort . '</span> ' . $after;
	return '<h1 ' . ($noMargins ? 'class="noTopMargin noBottomMargin"' : '') . '>' . trim($output) . '</h1>';
}

function renderServerToolsMenu()
{
	$serverSettings = readSettingsFile(dynamicServerToolsAddressPath);

	$output = serverToolsHTMLDeclarations('Menu');

	$output .= '<h2 class="gameColor2 noBottomMargin">IMPORTANT:</h2>';
	$output .= '<h3 class="gameColor8 serverToolsRoundedCorners noTopMargin flashRedBackground inlineBlock noTopPadding">If ' . trackerName() . ' loses contact with your game server for <span class="serverToolsDayCount">' . strval(deleteInterval) . '</span> days,<br>all settings, logs and saved data will be erased!</h3>';


	$formName = 'visibilityMenu';
	$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">Server Visibility</h4>';
	$output .= '<p>This controls whether your game server appears on ' . trackerName() . '\'s public list.</p>' . toolsHr1;
	$output .= startServerToolsForm('visibilityMenu', $formName, true);
	if(array_key_exists('serverIsPrivate', $serverSettings) && $serverSettings['serverIsPrivate'] == true)
	{
		$status = true;
	} else {
		$status = false;
	}
	$output .= '<div class="serverToolsKeysAndValues"><div class="serverToolsKey">Server Visibility:</div> <div class="serverToolsValue  ' . ($status ? 'gameColor3' : 'gameColor2') . '">' . ($status ? 'Private' : 'Public') . '</div></div>';
    $output .= toolsHr1 . endServerToolsForm('Configure Visibility', $formName);
	$output .= '</div>';



	if(emailEnabled && enablePGSQL)
	{
		$formName = 'emailAlertsMenu';
		$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">Email Alerts</h4>';
		$output .= '<p>' . trackerName() . ' can send emails to you for various things.';
		if(useSMTP == false)
		{
			$output .= '<br><span class="gameColor1 noSelect noBottomMargin">WARNING: All emails from ' .trackerName() . ' will likely be marked spam, so be sure to<br>set up a filter with your provider so you can see them!</span>';
		}
		if(defined('emailFromAddress'))
		{
			$output .= '<span class="gameColor5 noSelect noTopMargin"><br>Emails will be sent from <span class="gameColor2 yesSelect">' . emailFromAddress . '</span>';
		}
		$output .= '</p>' . toolsHr1;
		$output .= startServerToolsForm('emailAlertsMenu', $formName, true);

		$emailList = getServerEmailList($serverSettings);
		if(count($emailList) > 0)
		{
			$output .= '<ol>';
			for($i = 0; $i < count($emailList); $i++)
			{
				$output .= '<li><p class="serverToolsKeysAndValues"><span class="serverToolsKey">' . $emailList[$i] . '</span></p></li>';
			}
			$output .= '</ol>';
		} else {
			$output .= '<p class="serverToolsKeysAndValues"><span class="serverToolsKey centerPage">No email addresses added.</span></p>';
		}

		if(array_key_exists('emailAlerts', $serverSettings))
		{
			$output .= '';
		} else {
			$output .= '<p class="serverToolsKeysAndValues"><span class="serverToolsKey centerPage">No email alerts configured.</span></p>';
		}

		$output .= toolsHr1 . endServerToolsForm('Configure Email Alerts', $formName);
		$output .= '</div>';
	}


	$formName = 'dosMenu';
	$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">DoS Filtering</h4>';
	$output .= '<p>DoS filtering appends a harmless string of text to everything ' . trackerName() . ' sends to your game server, so your game server host can differentiate between real traffic from ' . trackerName() . ' and malicious traffic.</p>' . toolsHr1;
	$output .= startServerToolsForm('dosMenu', $formName, true);

	$dosEnabled = false;
	if(array_key_exists('serverDDOSKey', $serverSettings)) $dosEnabled = true;
	$output .= '<p class="serverToolsKeysAndValues"><span class="serverToolsKey">Filtering:</span> <span class="serverToolsValue ' . ($dosEnabled ? 'gameColor2' : '') . '">' . ($dosEnabled ? 'Enabled' : 'Not Enabled') . '</span></p>';

    $output .= toolsHr1 . endServerToolsForm('Configure DoS Filtering', $formName);
	$output .= '</div>';


	$formName = 'securityLog';
	$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">Security Log</h4>';
	$output .= '<p class="serverToolsKeysAndValues serverToolsInfo">' . trackerName() . ' logs all RCON commands sent through it, as well as every time a user logs in to server tools or makes a change to them.</p>';
	$output .= startServerToolsForm('securityLog', $formName, true);

    $output .= endServerToolsForm('View Security Log', $formName);
	$output .= '</div>';


	if(emailEnabled)
	{
		if(serverToolsAllowEmailAdministrators && count(emailAdministrators) > 0)
		{
			$formName = 'contactUs';
			$output .= startServerToolsForm('contactUs', $formName, true);
			$output .= '<div class="serverToolsCategory"><h4 class="serverToolsCategoryHeading">Contact Us</h4>';
			$output .= '<p>Send an email to the administrator(s) of ' . trackerName() . '.</p>';
			$output .= endServerToolsForm('Contact Us', $formName) . '</div>';
		}
	}


	$output .= '<br><br>';

	echo $output;
}

function renderButton($function, $text, $resetFormData = false, $customStyle = '')
{
	global $formCount;
	$formCount++;
	return startServerToolsForm($function, $function . $formCount, $resetFormData) . endServerToolsForm($text, $function . $formCount, $customStyle);
}

function renderServerToolsLoginPage()
{
	global $messages;
	global $warnings;
	global $postData;

	$output = serverToolsHTMLDeclarations('Login');

    $output .= '<h3>This utility will allow you to:</h3>
    <ol class="inlineBlock">
		<li>Set your server to private, which will remove it from ' . trackerName() . '\'s public list.</li>
		<li>Set up ' . trackerName() . ' with a unique key to be used when contacting your game server.<br>This can be used by your server host as a means of filtering DoS attacks.</li>';
		if(emailEnabled)
		{
			if(enablePGSQL)
			{
				$output .= '<li>Add email addresses to ' . trackerName() . ' that can be used for alerts regarding your game server.</li>';
			}
			if(serverToolsAllowEmailAdministrators)
			{
				$output .= '<li>Send a message to the administrator(s) of ' . trackerName() . '.</li>';
			}
		}
		$output .= '<li>Access ' . trackerName() . '\'s security log for your server, which includes RCON commands sent<br>through ' . trackerName() . ', and every time this utility was used.</li>
    </ol>';

	$output .= '<h1>Server Tools Login</h1>';

	$formName = 'serverLoginForm';
	$output .= startServerToolsForm('login', $formName);
	$output .='<input id="serverToolsAddress" name="serverToolsAddress" class="serverToolsLoginForm" size="40" type="text" value="' . (isset($postData['serverToolsAddress']) ? $postData['serverToolsAddress'] : '') . '" placeholder="Server Address" />
	 <strong>:</strong>
	<input id="serverToolsPort" name="serverToolsPort" class="serverToolsLoginForm" size="15" type="text" value="' . (isset($postData['serverToolsPort']) ? $postData['serverToolsPort'] : '') . '" placeholder="Server Port" /><br><br>
	<input id="serverToolsPassword" name="serverToolsPassword" onenter="submitFormButRequireAllFields(' . $formName . ')" class="serverToolsLoginForm" size="45" type="password" placeholder="Server RCON Password" /><br>
	<input name="action" type="hidden" value="login"></input>
	<p class="noBottomMargin">Your RCON password is <i>never</i> saved - we just need proof that you administer the server.
	<br>
	Your game server must be online for you to log in to this utility.
	</p>';
	$output .= endServerToolsForm('Log in', $formName, 'purpleButton');

	$output .= '</body></html>';
	echo $output;

	// If we are rendering the login page we should always be exiting anyway...
	exit;
}

?> 
