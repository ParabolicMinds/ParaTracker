<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the configuration file for ParaTracker.
// If you want to edit fonts and colors, they are found
// in the css files found in the /skins folder.
// You can change the skin used in static mode here and override a few
// colors, but there are no other visual settings.

// ONLY modify the variables defined below, between the double quotes!
// Changing anything else can break the tracker!

// If this file ever breaks and you have no idea what is wrong, just delete it.
// When ParaTracker is run, it will write a new one for you.

// If you find any exploits in the code, please bring them to my attention immediately!
// Thank you and enjoy!


// NETWORK SETTINGS
// NETWORK SETTINGS

// This is the IP Address of the server. Do not include the port number!
// By default, and for security, this value is empty. If ParaTracker is launched without a value here,
// it will display a message telling the user to check config.php before running.
$serverIPAddress = "";


// Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
// The default port number for Jedi Outcast is 28070.
// If an invalid entry is given, this value will default to 29070.
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

// This value, given in seconds, determines how long ParaTracker will wait for a current refresh of
// the server info to complete, before giving up and forcing another one. Raise this value if your
// web server is busy or slow to reduce the load on the game server.
// Minimum is 1 second, maximum is 15 seconds.
// Default is 3 seconds.
$refreshTimeout = "3";


// VISUAL SETTINGS
// VISUAL SETTINGS

// This line specifies which skin file to load. Skins are found in the skins/ folder, and they are all
// simple CSS files. The name is case sensitive.
// ParaTracker will automatically search in the skins/ folder for the file specified, and it will automatically
// add the ".css" file extension. All you need to include here is the file name, minus the extension.
// You can make your own custom CSS skins, but if you want to use JSON to make a custom skin, then
// set this value to "JSON" and the tracker will send an unformatted JSON response.
// Default value is "Metallic Console"
$paraTrackerSkin = "Metallic Console";

// This is a 6 character hexadecimal value that specifies the background color to be used.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$backgroundColor = "";

// This value is a percentage, from 0 to 100, of how opaque the background color will be.
// Default value is "100".
$backgroundOpacity = "100";

// This is a 6 character hexadecimal value that specifies the text color of all non-colorized text.
// It will not change the color of server names, mod names, map names, or player names.
// The skin chosen will already have it's own color; this value will override it, if desired.
// Default value is "".
$textColor = "";

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


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for on the web server in the images/levelshots folder.
// If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

// For levelshots to animate, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three animated levelshots for mp/ffa5, the files would have to be in
// the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
// and ffa5_3.jpg

// ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
// and GIFs third. If no images are found, a placeholder image will be displayed instead.

// The following value will enable or disable levelshot transitions. A value of 1 or "Yes" will allow them,
// and any other value with disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$levelshotTransitionsEnabled = "1";

// This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the amount of time, in second, each levelshot will take to fade into the next one.
// Note that fades do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is 1 seconds.
$levelshotTransitionTime = "1";

// This is the animation that will be used for fading levelshots.
// If you want to change the animations, they are found in "css/LevelshotAnimations.css"
// Valid values are whole numbers between 0 to 999 (No decimals).
// Setting this value to 0 will play a random animation.
// Default value is 0
// The default transitions are as follows:
// Transition 1: Fade
// Transition 2: Fade to black
// Transition 3: Hue shift
// Transition 4: Skew
// Transition 5: Horizontal Stretch
// Transition 6: Stretch and rebound
// Transition 7: Slide to left
// Transition 8: Slide to right
// Transition 9: Slide to top
// Transition 10: Slide to bottom
// Transition 11: Spin and fly to top left
// Transition 12: Spin and fly to top right
// Transition 13: Fall away and spin
// Transition 14: Zoom in
// Transition 15: Blur
$levelshotTransitionAnimation = "0";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better. Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS

// This value is boolean. When this variable is set to Yes or 1, offending symbols will be
// filtered from the server name. Frequently, people will put unreadable symbols into their
// server names to get a higher alphabetical listing. This feature will remove the nonsense symbols.
// Default is 1.
$filterOffendingServerNameSymbols = "1";

// No Players Online Message
// This message displays in place of the player list when nobody is online.
// Default is "No players online."
$noPlayersOnlineMessage = "No players online.";

// ParaTracker can automatically refresh itself every so often.
// This will not cause any disruption to the game, because the flood protection
// limits how often ParaTracker will contact the server.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Enabled by default.
$enableAutoRefresh = "1";

// This value determines how many seconds ParaTracker waits between refreshes.
// This value cannot be lower than the value in $floodProtectTimeout, or 10 seconds, whichever is greater.
// Decimals are invalid and will be rounded.
// It also cannot be higher than 300 seconds.
// Default is 30 seconds.
$autoRefreshTimer = "30";

// This variable will set the maximum number of characters ParaTracker will accept from the server.
// This prevents pranksters from sending 50MB back, in the event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// If this limit is met, ParaTracker will terminate with an error.
// Default is 16384 characters (One packet).
$maximumServerInfoSize = "16384";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and give
// an IP address, port number and visual theme ID in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=ParaSkinA"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that I have not yet found!
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


// RCON SETTINGS
// RCON SETTINGS

// This value will enable or disable RCon.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default for security.
$RConEnable = "0";

// This value sets the maximum number of characters ParaTracker will send to the server.
// If the command or password is any larger than this, the command will not be sent.
// Minimum is 20 characters, maximum is 10000 characters.
// Default is 100 characters.
$RConMaximumMessageSize = "100";

// RCon flood protection forces the user to wait a certain number of seconds before sending another command.
// Note that this is not user-specific; if someone else is using your RCon, you may have to wait a bit to
// send the command. Minimum is 10 seconds, maximum is 3600.
// Cannot be lower than the value of $connectionTimeout.
// Default is 20 seconds.
$RConFloodProtect = "20";

// RCon events are logged in RConLog.php for security. This variable will determine
// the maximum number of lines that will be stored in the log file before the old
// entries are truncated. Minimum is 100 lines. Maximum is 100000.
// Default is 1000 lines.
$RConLogSize = "1000";


// POPUP WINDOW SETTINGS
// POPUP WINDOW SETTINGS

// This value is boolean. When the RCon and PARAM buttons are clicked, the popup
// window will snap to the top left corner of the screen by default. When this
// variable is set to any value other than Yes or 1, the behavior is disabled.
// Does not appear to work correctly in Google Chrome.
// Default is 0.
$newWindowSnapToCorner = "0";


// GEOIP SETTINGS
// GEOIP SETTINGS

// This value is boolean. When this variable is set to Yes or 1, GeoIP will be enabled, which
// allows a country flag icon to be displayed on the tracker.
// GEOIP MUST BE INSTALLED ON THE SERVER FOR THIS TO WORK.
// If ParaTracker does not find GeoIP, it will ignore this setting and give a debug message.
// Default is 0.
$enableGeoIP = "1";

// For GeoIP to work, ParaTracker needs to know where to find the country database. This path
// needs to point to the GeoIP database file. Include the file name and extension.
// default value is ""
$geoIPPath = "/srv/GeoLite2-Country.mmdb";



// End of config file

/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

?>
