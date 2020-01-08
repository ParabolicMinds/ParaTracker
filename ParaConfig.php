<?php

///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the configuration file for ParaTracker.
// If you want to edit fonts and colors, you should edit them
// in the css files found in the /skins folder.
// The visual settings found here are overrides only, and should be used with caution!

// ONLY modify the variables defined below, between the double quotes!
// Changing anything else can break the tracker!

// If this file ever breaks and you have no idea what is wrong, just delete it.
// The next time ParaTracker is run, it will create a new one for you.

// If you find any exploits in the code, please bring them to our attention immediately!
// Thank you and enjoy!


/*==================================================================================================*/
// TRACKER NAME
// TRACKER NAME

//This value appears in the title bar of the browser. It will show up in Google search results - choose wisely!
//Default value is "ParaTracker - The Ultimate Quake III Server Tracker"
$trackerName = "ParaTracker - The Ultimate Quake III Server Tracker";


/*==================================================================================================*/
// NETWORK SETTINGS
// NETWORK SETTINGS

// This is the IP Address of the server. Do not include the port number!
// By default, and for security, this value is empty. If ParaTracker is launched without a value here,
// it will display a message telling the user to check ParaConfig.php before running.
$serverIPAddress = "";

// Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
// The default port number for Jedi Outcast is 28070.
// If an invalid entry is given, this value will default to 29070.
//$serverPort = "";
$serverPort = "";

// This variable limits how many seconds are required between each snapshot of the server.
// This prevents high traffic on the tracker from bogging down the game server it is tracking.
// ParaTracker forces a minimum value of 5 seconds between snapshots. Maximum is 1200 seconds.
// This value cannot be lower than the value of $connectionTimeout (below).
// Default is 15 seconds.
$floodProtectTimeout = "15";

// This value is the number of seconds ParaTracker will wait for a response from the game server
// before timing out. If the first attempt fails, a second attempt will be made.
// ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
// Not recommended to go above 5 seconds, as people will get impatient and leave.
// This setting also affects RCon wait times.
// Default is 2.5 seconds.
$connectionTimeout = "2.5";

// This value determines how many seconds ParaTracker will wait for a current refresh of
// the server info to complete, before giving up and forcing another one. Raise this value if your
// web server is busy or slow to reduce the load on the game server.
// Minimum is 1 second, maximum is 15 seconds.
// Default is 3 seconds.
$refreshTimeout = "3";


/*==================================================================================================*/
// VISUAL SETTINGS
// VISUAL SETTINGS

// This line specifies which skin file to load. Skins are found in the $skinsPath folder (Found near the bottom
// of this file), and they are all simple CSS files. The file name is case sensitive.
// ParaTracker will automatically search in the $skinsPath folder for the file specified, and it will automatically
// add the ".css" file extension. All you need to include here is the file name, minus the extension.
// You can make your own custom CSS skins. If you want to use JSON to make a fully custom skin, then
// set this value to "JSON" and the tracker will send an unformatted JSON response.
// Default value is "Metallic Console"
$paraTrackerSkin = "Metallic Console";

// The following visual settings are OVERRIDES ONLY. Use with caution!
// The following visual settings are OVERRIDES ONLY. Use with caution!

// This is a 6 character hexadecimal value that specifies the background color to be used.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$backgroundColor = "";

// This value is a percentage, from 0 to 100, of how opaque the background color will be.
// Default value is "100".
$backgroundOpacity = "100";

// This is a 6 character hexadecimal value that specifies the color of the odd rows on the player list.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$playerListColor1 = "";

// This value is a percentage, from 0 to 100, of how opaque the color of the odd rows on the player list will be.
// Default value is "100".
$playerListColor1Opacity = "100";

// This is a 6 character hexadecimal value that specifies the color of the even rows on the player list.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$playerListColor2 = "";

// This value is a percentage, from 0 to 100, of how opaque the color of the even rows on the player list will be.
// Default value is "100".
$playerListColor2Opacity = "100";

// This is a 6 character hexadecimal value that specifies the text color of all non-colorized text.
// It will not change the color of server names, mod names, map names, or player names.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$textColor = "";

// This value specifies a font to be used on the tracker page. Font families are also accepted.
// Make sure you use a common font so everyone can see it!
// Default value is "".
$customFont = "";


/*==================================================================================================*/
// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for in the images/levelshots folder.
// For instance, if the game is Jedi Academy and the map is mp/ffa5, ParaTracker will search for
// images named "ffa5" in the "images/levelshots/jedi academy/mp/" folder.

// For levelshots to animate, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three animated levelshots for mp/ffa5, the files would have to be
// named ffa5_1.jpg, ffa5_2.jpg, and ffa5_3.jpg

// The following array is a list of levelshot file extensions supported by the tracker.
// You can add as many extensions as you like. Add each file extension on a new line, between the double
// quotes, and make sure there is a comma at the end of each line, with the exception of the last line.
// Do not inclue the "." - just the letters of the extension. "jpg", "png", etc. When the levelshot finder
// searches for images, it will search in the order of this array and stop when it finds a match.
// Prioritize the extensions carefully!
// Example:
// $fileExtList = array(
// "webp",
// "png",
// "jpg",
// "gif"
// );
$fileExtList = array(
"webp",
"png",
"jpg",
"gif"
);

// The following value will enable or disable levelshot transitions. A value of 1 or "Yes" will allow them,
// and any other value will disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$levelshotTransitionsEnabled = "1";

// This is the number of seconds each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the number of seconds, each levelshot will take to transition into the next one.
// Note that transitions do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is 1 second.
$levelshotTransitionTime = "1";

// This is the animation that will be used for fading levelshots.
// If you want to change the animations, they are found in "css/LevelshotAnimations.css"
// Each transition is listed here. The user can pick which transitions are enabled
// by adding the values of the desired transitions together, and putting them
// into the variable below.
// Dynamic mode will allow users to override this setting.
// Minimum is 0 (No transitions). maximum is 32767.
// Default value is 3.
// The default transitions are as follows:
// Transition 1: Fade                   ( Value: 1 )
// Transition 2: Smooth Fade            ( Value: 2 )
// Transition 3: Hue Shift              ( Value: 4 )
// Transition 4: Skew                   ( Value: 8 )
// Transition 5: Horizontal Stretch     ( Value: 16 )
// Transition 6: Stretch and rebound    ( Value: 32 )
// Transition 7: Slide Left             ( Value: 64 )
// Transition 8: Slide Right            ( Value: 128 )
// Transition 9: Slide Top              ( Value: 256 )
// Transition 10: Slide Bottom          ( Value: 512 )
// Transition 11: Spin, Fly Left        ( Value: 1024 )
// Transition 12: Spin, Fly Right       ( Value: 2048 )
// Transition 13: Fall Away             ( Value: 4096 )
// Transition 14: Zoom                  ( Value: 8192 )
// Transition 15: Blur                  ( Value: 16384 )
$levelshotTransitionAnimation = "3";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better.
// Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


/*==================================================================================================*/
// TRACKER SETTINGS
// TRACKER SETTINGS

// This value is boolean. When this variable is set to Yes or 1, the game name will be displayed
// in the tracker. Otherwise, it will be hidden.
// Default is 1.
$displayGameName = "1";

// This value is boolean. When this variable is set to Yes or 1, offending symbols will be
// filtered from the server name. Frequently, people will put unreadable symbols into their
// server names to get a higher alphabetical listing. This feature will remove the nonsense symbols.
// Default is 1.
$filterOffendingServerNameSymbols = "1";

// This message displays in place of the player list when nobody is online.
// Default is "No players online."
$noPlayersOnlineMessage = "No players online.";

// This value is boolean. When it is enabled, ParaTracker will automatically reload info from the
// game server, keeping the tracker up to date.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Enabled by default.
$enableAutoRefresh = "1";

// This value determines how many seconds ParaTracker waits between refreshes.
// Decimals are invalid and will be rounded.
// Minimum is 10 seconds, maximum is 300 seconds.
// Cannot be lower than the value of $floodProtectTimeout.
// Default is 30 seconds.
$autoRefreshTimer = "30";

// This variable will set the maximum number of characters ParaTracker will accept from the server.
// This prevents pranksters from sending 50MB back, in the event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// If this limit is met, ParaTracker will terminate with an error.
// Default is 16384 characters (One packet).
$maximumServerInfoSize = "16384";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and specify
// an IP address, port number and skin in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=Metallic%20Console"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that has not been found!
// If you do not understand what this feature is, then DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "0";

// The following setting is a personal message that will be displayed on ParaTrackerDynamic.php when a user is setting
// up ParaTracker for their own use. By default, this is simply a link to our GitHub, where you can download the program
// for free. The point is to encourage as many people as possible to run the software themselves, and not to rely on Dynamic
// mode too much.
// Default is: "ParaTracker is a free, open-source server tracker for Quake 3 based games! Download your own at http://github.com/ParabolicMinds/ParaTracker"
$personalDynamicTrackerMessage = "ParaTracker is a free, open-source server tracker for Quake 3 based games! Download your own at http://github.com/ParabolicMinds/ParaTracker";


/*==================================================================================================*/
// RCON SETTINGS
// RCON SETTINGS

// This value will enable or disable RCon.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default for security.
$RConEnable = "0";

// This value sets the maximum number of characters ParaTracker will send to the server.
// If the command or password is any larger than this, the command will not be sent.
// Minimum is 20 characters, maximum is 10000 characters.
// Default is 200 characters.
$RConMaximumMessageSize = "200";

// RCon flood protection forces the user to wait a certain number of seconds before sending another command.
// Note that this is not user-specific; if someone else is using RCon on your server, you will also have to wait
// to send the command.
// Minimum is 10 seconds, maximum is 3600.
// Cannot be lower than the value of $connectionTimeout.
// Default is 20 seconds.
$RConFloodProtect = "20";


/*==================================================================================================*/
// GEOIP SETTINGS
// GEOIP SETTINGS

// This value is boolean. When this variable is set to Yes or 1, GeoIP will be enabled, which
// allows a country flag icon to be displayed on the tracker.
// GEOIP MUST BE INSTALLED ON THE WEB SERVER FOR THIS TO WORK.
// If ParaTracker does not find GeoIP, it will ignore this setting, and give an error message in an
// HTML comment at the top of the page.
// Default is 0.
$enableGeoIP = "0";

// For GeoIP to work, ParaTracker needs to know where to find the country database. This path
// needs to point to the GeoIP database file. Include the file name and extension.
// default value is ""
$geoIPPath = "";


/*==================================================================================================*/
// POSTGRESQL SETTINGS
// POSTGRESQL SETTINGS

// This value is boolean. When set to 1, ParaTracker will attempt to find a postgres SQL database. ParaTracker
// will connect to this database and use it for things like analytics and levelshot requests for maps.
// Do not enable this if you do not have postgres installed.
// Default is 0.
$enablePGSQL = "0";

// This is the user name used for the postgres database.
// Default is "postgres"
$pgUser = "postgres";

// This is the password used for the postgres database.
// Default is ""
$pgPass = "";

// This is the name of the postgres database.
// Default is "paratracker"
$pgName = "paratracker";

// This is the URL of the postgres database.
// Default is "localhost"
$pgHost = "localhost";

// This is the port used to connect to the postgres database.
// Default is "" (uses the default port of pg_connect)
$pgPort = "";


/*==================================================================================================*/
// ANALYTICS SETTINGS
// ANALYTICS SETTINGS

// To run analytics, you must set up a cron job to run AnalyticsBackground.php (found in the $utilitiesPath folder)
// at specific intervals. This process will connect to every server that has recently been tracked and
// store info in the database.

// This value is boolean. When this variable is set to Yes or 1, the analytics back end will be enabled.
// Analytics will save server data to a database over time, and allow users to view this data.
// Analytics REQUIRES pgsql to be enabled. If pgsql is disabled, analytics will also be disabled.
// Default value is 0.
$analyticsEnabled = "0";

// This value is boolean. When this variable is set to Yes or 1, the analytics front end will be enabled.
// This will allow users to view the saved analytics data from the database.
// This feature is here to help prevent people from overloading the server with false analytics requests,
// while at the same time allowing the back end to run.
// If the analytics back end is disabled, the front end will be disabled as well.
// Default value is 1.
$analyticsFrontEndEnabled = "1";


/*==================================================================================================*/
// MAPREQ SETTINGS
// MAPREQ SETTINGS

// This value is boolean. When set to "Yes" or "1", mapreq will be used to allow users to request
// levelshots of maps they like.
// mapreq REQUIRES pgsql and dynamic mode to be enabled. If pgsql or dynamic mode is disabled, mapreq will disable itself.
// Default is 0
$mapreqEnabled = "0";

// If mapreq is enabled and the server being tracked does not have levelshots, ParaTracker will display this message to
//encourage users to submit their own levelshots.
//Default is "Click here to add levelshots"
$mapreqTextMessage = "Click here to add levelshots";


/*==================================================================================================*/
// EMAIL SETTINGS
// EMAIL SETTINGS

// These settings are for email functions.
// Emails are sent by SendEmails.php, which is found in the $utilitiesPath folder.
// SendEmails.php must be activated by a cron job, that runs at the frequency you want to receive administrative reports.

// This setting enables or disables e-mail functionality. For this to work, an administrator e-mail address must exist,
// and PHPMailer must be installed via composer.
// Default is 0
$emailEnabled = "0";

// This setting enables or disables the sending of admin reports.
// Default is 0
$emailAdminReports = "0";

// This setting is the path to PHPMailerAutoload.php.
// Default is "vendor/phpmailer/phpmailer/"
$emailPath = "vendor/phpmailer/phpmailer/";

// This variable controls whether emails will be sent with SMTP or not.
// Default is 0
$useSMTP = "0";

    // If using SMTP, this variable is the address of the SMTP server we will use
    // Default is ""
    $smtpAddress = "";

    // If using SMTP, this variable is the port of the SMTP server we will use
    // Default is ""
    $smtpPort = "";

    // If using SMTP, this variable is the username we will use for the SMTP server
    // Default is ""
    $smtpUsername = "";

    // If using SMTP, this variable is the password we will use for the SMTP server
    // Default is ""
    $smtpPassword = "";

// This is the address that will be used to send the email.
// Default is ""
$emailFromAddress = "";

// This is an array of administrator e-mail addresses. You can add as many as you like. Add each e-mail address
// on a new line, between the double quotes, and make sure there is a comma at the end of each line, with the
// exception of the last line.
// Example:
// $emailAdministrators = array(
// "adminNumberOne@nowhere.com",
// "adminNumberTwo@nowhere.com",
// "adminNumberThree@nowhere.com"
// );
$emailAdministrators = array(
"",
"",
"",
"",
""
);


/*==================================================================================================*/
// MAINTENANCE SETTINGS
// MAINTENANCE SETTINGS

// Every so often, ParaTracker will clean up the $infoPath folder and the levelshots request database
// by checking for outdated data and deleting it.
// This variable controls how many minutes ParaTracker will wait between cleanups.
// Note that if analytics is enabled, cleanup will only be run by AnalyticsBackground.php.
// Minimum is 10 minutes, maximum is 1440 minutes.
// Default is 60 minutes.
$cleanupInterval = "60";

// This variable is how many days old the server info must be to be deleted.
// If analytics are enabled, this value will instead determine how many days
// a server must be offline before it is no longer tracked.
// This variable cannot be lower than 2 times the amount of time specified in $cleanupInterval above.
// Minimum is 1 day, maximum is 30 days.
// Decimals are accepted.
// Default is 7 days.
$deleteInterval = "7";

// This variable sets what percentage of server load will cause the cleanup to be skipped.
// If ParaTracker is running on Windows, PHP cannot measure CPU load, so this setting will be ignored
// and cleanup will run regardless of the load.
// Minimum is 50, maximum is 100
// Default is 90
$loadLimit = "90";

// This variable is boolean. When this variable is set to Yes or 1, ParaTracker will remove old entries
// from the analytics database as part of the cleanup process.
// Default is 1.
$cullDatabase = "1";

// This variable determines how many days entries will be left in the analytics database before removal.
// This will have no effect if $cullDatabase (Above) is disabled.
// Minimum is 90 days, maximum is 2000 days.
// Default is 370 days.
$databaseCullTime = 370;


/*==================================================================================================*/
// PATH SETTINGS
// PATH SETTINGS

// This setting controls the temporary folder where server info will be stored between refreshes.
// Changing this while ParaTracker is live will result in all game servers being reloaded. Change with caution.
// Default is "info"
$infoPath = "info";

// This setting controls the folder where logs will be stored.
// Changing this while ParaTracker is live will result in all logs being reset. Change with caution.
// Default is "logs"
$logPath = "logs";

// This setting controls the path to the skins folder.
// Default is "skins"
$skinsPath = "skins";

// This setting controls the path to the utilities folder. Do not change this unless you are rearranging
// the internals of ParaTracker to match.
// Default is "utilities"
$utilitiesPath = "utilities";


/*==================================================================================================*/
// LOG SETTINGS
// LOG SETTINGS

// Errors are logged in errorLog.php, which is found in the $logPath folder.
// This setting will determine the maximum number of lines that will be stored in the log file
// before the old entries are truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 10000 lines.
$errorLogSize = "10000";

// Cleanup events are logged in cleanupLog.php, which is found in the $logPath folder.
// This is the maximum of lines the cleanup log can have before the excess is truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 10000 lines.
$cleanupLogSize = "10000";

// RCon events are logged in RConLog.php. Each server has it's own unique RCon log, which is found in the
// $logPath folder, inside a folder named for the server's IP address and port.
// This setting will determine the maximum number of lines that will be stored in the log file
// before the old entries are truncated.
// Minimum is 100 lines. Maximum is 100000.
// Default is 1000 lines.
$RConLogSize = "1000";


/*==================================================================================================*/
// End of config file

/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

?>
