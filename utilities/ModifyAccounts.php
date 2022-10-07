<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


$analyticsBackground = 1;
$calledFromElsewhere = 1;
$safeToExecuteParaFunc = "1";

//We are in the utilities folder, so we have to back out one
chdir("../");

if (file_exists("ParaFunc.php"))
{
	ob_start();
    include 'ParaFunc.php';
    ob_end_clean();
}
else
{
    echo "Unable to open ParaFunc.php\n";
    exit();
}

if (php_sapi_name() != "cli")
{
    if(!enablePGSQL)
    {
        displayError("Postgres must be enabled to add an account!", "", "");
        exit();
    }
    else
    {
        echo "<!-- -->";
        echo renderInstructions();
        exit();
    }
}

//If we're here, then we must have been executed from the command line and have a command to perform.

if(!isset($argv[1]))
{
    echo "Error: command required!\n";
    exit();
}
if(!isset($argv[2]))
{
    echo "Error: username required!\n";
    exit();
}

$command = strtolower($argv[1]);
$username = $argv[2];


if($command == "add")
{
    addAccount($username, getPassword());
}
else if($command == "remove")
{
    removeAccount($username);
}
else if($command == "modify")
{
    modifyAccount($username, getPassword());
}
else
{
    echo "Unrecognized command: '" . $command . "'\n";
}


//We are done. Terminate.
exit();


function getPassword()
{
	global $argv;
	
    if(!isset($argv[3]))
    {
        echo "Error: password required!\n";
        exit();
    }
    else
    {
        return $argv[3];
    }
}


function renderInstructions()
{
    $output = '<html><body style="font-family: monospace; font-size: 12pt;"><h3>This file does nothing unless it is run from the command line.</h3>';

    $output .= "<h3>Instructions:</h3>";
    $output .= "<p>To add an account, run this file from the command line with the following parameters:<br>";
    $output .= "<strong>add &lt;username&gt; &lt;password&gt;</strong><br>";
    $output .= "The password will be hashed and salted, and stored in the database.</p>";

    $output .= "<p>To modify an account (Change the password), run this file from the command line with the following parameters:<br>";
    $output .= "<strong>modify &lt;username&gt; &lt;password&gt;</strong><br>";
    $output .= "The new password will be hashed and salted, and stored in the database.</p>";

    $output .= "<p>To delete an account, run this file from the command line with the following parameters:<br>";
    $output .= "<strong>delete &lt;username></strong><br>";
    $output .= "The user will be removed from the database.</p>";

    $output .= '</body></html>';

    echo $output;
    exit();
}


function addAccount($username, $password)
{
    global $pgCon;
	$salt = bin2hex(random_bytes(8));
    $hash = hash("sha512", $password . $salt);

    if(pg_query_params($pgCon, "INSERT INTO account.user (username, passhash, salt) VALUES ($1, $2, $3)", array($username, $hash, $salt)) !== false)
    {
        echo "Successfully added '" . $username . "' to the database!";
    }
    else
    {
        echo "Error! '" . $username . "' not added to the database.";
    }
}

function removeAccount($username)
{
    global $pgCon;

    if(pg_query_params($pgCon, "DELETE FROM account.user WHERE username = $1", array($username)) !== false)
    {
        echo "Successfully removed '" . $username . "' from the database!";
    }
    else
    {
        echo "Error! '" . $username . "' not removed from the database.";
    }
}

function modifyAccount($username, $password)
{
    global $pgCon;
	$salt = bin2hex(random_bytes(8));
    $hash = hash("sha512", $password . $salt);

    if(pg_query_params($pgCon, "UPDATE account.user SET passhash = $2, salt = $3 WHERE username = $1", array($username, $hash, $salt)) !== false)
    {
        echo "Successfully change password for '" . $username . "'.";
    }
    else
    {
        echo "Error! '" . $username . "' password not changed.";
    }
}



?>
