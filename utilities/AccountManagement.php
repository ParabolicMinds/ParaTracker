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

$output = htmlDeclarations("ParaTracker - Account Management", "../") . '</head><body class="accountManagement">';

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

    $output .= '<p><strong>' . implode($emailAdministrators, '<br>') . '</strong></p>';

    $output .= '<a class="testMessage" href="AccountManagement.php?sendTestEmail=1">Send test message to administrators</a>';
}


$output .= '</body></html>';

echo "-->" . $output;

?>