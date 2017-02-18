<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

// This is the config file for ParaTracker.
// The only visual setting found here is the frame border.
// If you want to edit fonts and colors, they are found
// in ParaStyle.css and the ParaSkin.css files, not here.

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
$serverIPAddress = "212.224.101.83";


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
// before timing out. Note that, every time the tracker gets data from the server, it will ALWAYS
// wait the full delay time. Server connections are UDP, so the tracker cannot tell when the data
// stream is complete. After this time elapses, ParaTracker will assume it has all the data and
// parse it. If your web server has a slow response time to the game server, set this value
// higher. ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
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


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

// Levelshots will be searched for on the web server in the images/levelshots folder.
// If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

// For levelshots to fade, they will have to be named with _1, _2, and _3 at the end of the file name.
// For instance, to have three fading levelshots for mp/ffa5, the files would have to be in
// the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
// and ffa5_3.jpg

// ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
// and GIFs third. If no images are found, a placeholder image will be displayed instead.

// The following value will enable or disable fading levelshots. A value of 1 or "Yes" will allow them,
// and any other value with disable them. If this is disabled, only the first levelshot will show.
// Default value is 1.
$fadeLevelshots = "1";

// This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
// Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
// Default is 3 seconds.
$levelshotDisplayTime = "3";

// This is the amount of time, in second, each levelshot will take to fade into the next one.
// Note that fades do not work in some browsers, like Internet Explorer 8.
// Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
// Default is .5 seconds.
$levelshotTransitionTime = ".5";

// This is the frame rate at which levelshots will transition. Higher values are smoother,
// and lower values are choppier. Values between 10 and 30 are good. A value of 1 will
// disable the fading and give a "slide show" feel.
// Any value below 1 is forbidden. Values above 60 are also forbidden.
// Default is 30 FPS.
$levelshotFPS = "30";

// The following value is the maximum number of levelshots that can be used. Keep in mind that
// more levelshots is not always better. Minimum is 1, maximum is 99.
// Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS

// This value is boolean. When this variable is set to Yes or 1, offending symbols will be
// filtered from the server name. Currently the only affected symbol is the Euro symbol, €.
// Default is 1.
$filterOffendingServerNameSymbols = "1";

// This is the name of the game being tracked; I.E. Jedi Academy, Jedi Outcast, Call Of Duty 4, etc.
// It is displayed underneath the server name in the top left corner of the tracker.
// For future-proofing, this value is left to you, the user.
// The levelshots derive their directory from this value, so make sure it is correct! For instance,
// a value of "Jedi Academy" means ParaTracker will look for levelshots in "images/levelshots/jedi academy"
// Default is "Jedi Academy"
$gameName = "Jedi Academy";

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
// This prevents pranksters from sending 50MB back, in the unlikely event that you connect to
// the wrong server. Minimum is 2000 characters, maximum is 50000 characters.
// Default is 4000 characters.
$maximumServerInfoSize = "4000";

// This next setting enables "Dynamic" ParaTracker. Clients can load "ParaTrackerDynamic.php" and give
// an IP address, port number and visual theme ID in the URL, and ParaTracker will connect to that server.
// For instance, "YourWebsiteNameHere.com/ParaTrackerDynamic.php?ip=192.168.1.100&port=29070&skin=ParaTrackerA&game=Jedi%20Academy"
// DO *NOT*, I REPEAT, DO *NOT* ENABLE THIS FEATURE UNLESS YOU WANT PEOPLE USING YOUR WEBSITE TO TRACK THEIR SERVERS.
// Also, DO NOT run ParaTracker in this mode without isolating it in its own webroot first - the consequences
// can be grave if there is a security hole that I have not yet found!
// If you do not understand what this feature is, DO NOT enable it.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default.
$dynamicTrackerEnabled = "1";

// The following setting is a personal message that will be displayed on ParaTrackerDynamic.php when a user is setting
// up ParaTracker for their own use. By default, this is simply a link to our GitHub, where you can download the program
// for free. The point is to encourage as many people as possible to run the software themselves, and not to rely on Dynamic
// mode too much.
// Default is: "ParaTracker is free, open-source software! Download your own at http://github.com/ParabolicMinds/ParaTracker"
$personalDynamicTrackerMessage = "ParaTracker is free, open-source software! Download your own at http://github.com/ParabolicMinds/ParaTracker";


// RCON SETTINGS
// RCON SETTINGS

// This value will enable or disable RCon.
// A value of Yes or 1 will enable it, and any other value will disable it.
// Disabled by default for security.
$RConEnable = "1";

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


// End of config file

/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

?>