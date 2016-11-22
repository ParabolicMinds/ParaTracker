<?php
///////////////////////////////
// ParaTracker Configuration //
///////////////////////////////

//ParaTracker version 1.0
//This version only has one layout. To use it, invoke ParaTracker-A.php on your web page.
//Future versions will have more layouts.

//This is the config file for ParaTracker.
//If you want to edit fonts and colors,
//they are found in ParaStyle.css, not here.

//ONLY modify the variables defined below, between the double quotes!
//Changing anything else can break the tracker!

//If you are not sure what you are doing, just change the IP address and port to match
//your game server, and let the default settings take care of the rest.

//If you find any exploits in the code, please bring them to my attention immediately!
//Thank you and enjoy!



// NETWORK SETTINGS
// NETWORK SETTINGS


//This is the IP Address of the server. Do not include the port number!
//By default, and for security, this value is empty. If ParaTracker is launched without a value here,
//it will display a message telling the user to check config.php before running.
$serverIPAddress = "212.224.101.83";

//Port number of the server. The default port for Jedi Academy is 29070. Another common port is 21000.
//The default port number for Jedi Outcast is 28070.
//If an invalid entry is given, this value will default to 29070.
$serverPort = "29070";


//This variable limits how many seconds are required between each snapshot of the server.
//This prevents high traffic on the tracker from bogging down the game server it is tracking.
//ParaTracker forces a minimum value of 5 seconds between snapshots. Maximum is 1200 seconds.
//This value cannot be lower than the value of $connectionTimeout below.
//Default is 10 seconds.
$floodProtectTimeout = "10";

//This value is the number of seconds ParaTracker will wait for a response from the game server
//before timing out. Note that, every time the tracker gets data from the server, it will ALWAYS
//wait the full delay time. Server connections are UDP, so the tracker cannot tell when the data
//stream is complete. After this time elapses, ParaTracker will assume it has all the data and
//parse the data. If your web page has a slow response time to the game server, set this value
//higher. ParaTracker forces a minimum value of 1 second, and will not allow values over 15 seconds.
//Not recommended to go above 5 seconds, as people will get impatient and leave.
//This setting also affects RCon wait times.
//Default is 2.5 seconds.
$connectionTimeout = "2.5";


// VISUAL SETTINGS
// VISUAL SETTINGS

//This value is boolean. When this variable is set to any value other than Yes or 1, the
//frame image that overlays the tracker is disabled.
//Default is 0.
$disableFrameBorder = "0";


// LEVELSHOT SETTINGS
// LEVELSHOT SETTINGS

//Levelshots will be searched for on the web server in the images/levelshots folder.
//If the map is mp/ffa5, ParaTracker will search for images in images/levelshots/mp/ffa5.

//For levelshots to fade, they will have to be named with _1, _2, and _3 at the end of the file name.
//For instance, to have three fading levelshots for mp/ffa5, the files would have to be in
//the images/levelshots/mp folder, and they would need to be named ffa5_1.jpg, ffa5_2.jpg,
//and ffa5_3.jpg

//ParaTracker will use any combination of PNG, JPG, and GIF images. PNGs will be used first, JPGs second,
//and GIFs third. If no images are found, a placeholder image will be displayed instead.


//The following value will enable or disable fading levelshots. A value of 1 or "Yes" will allow them,
//and any other value with disable them. If this is disabled, only the first levelshot will show.
//Default value is 1.
$fadeLevelshots = "1";

//This is the amount of time, in seconds, each levelshot will be displayed before moving on to the next.
//Decimals are acceptable. Minimum is 1 second. Maximum is 15 seconds.
//Default is 3 seconds.
$levelshotDisplayTime = "3";

//This is the amount of time, in second, each levelshot will take to fade into the next one.
//Note that fades do not work in some browsers, like Internet Explorer 8.
//Decimals are acceptable. Minimum is 0.1 seconds. Maximum is 5 seconds.
//Default is .5 seconds.
$levelshotTransitionTime = ".5";

//This is the frame rate at which levelshots will transition. Higher values are smoother,
//and lower values are choppier. Values between 10 and 30 are good. A value of 1 will
//disable the fading and give a "slide show" feel.
//Any value below 1 is forbidden. Values above 60 are also forbidden.
//Default is 20 FPS.
$levelshotFPS = "20";

//The following value is the maximum number of levelshots that can be used. Keep in mind that
//more levelshots is not always better. Minimum is 1, maximum is 99.
//Default is 20 levelshots.
$maximumLevelshots = "20";


// TRACKER SETTINGS
// TRACKER SETTINGS


//This is the name of the game being tracked; I.E. Jedi Academy, Jedi Outcast, Call Of Duty 4, etc.
//It is displayed underneath the server name in the top left corner of the tracker.
//For future-proofing, this value is left to you, the user.
//Default is "Jedi Academy."
$gameName = "Jedi Academy";


//No Players Online Message
//This message displays in place of the player list when nobody is online.
//Default is "No players online."
$noPlayersOnlineMessage = "No players online.";


//Auto-Refresh
//ParaTracker can automatically refresh the server page every so often.
//This will not cause any disruption to the game, because the flood protection
//limits how often ParaTracker will contact the server.
//A value of Yes or 1 will enable it, and any other value will disable it.
//Enabled by default.
$enableAutoRefresh = "1";

//This value determines how many seconds ParaTracker waits between refreshes.
//This value cannot be lower than the value in $floodProtectTimeout, or 5 seconds, whichever is greater.
//It also cannot be higher than 180 seconds.
//Default is 30 seconds.
$autoRefreshTimer = "30";


// RCON SETTINGS
// RCON SETTINGS

//This value will enable or disable RCon.
//A value of Yes or 1 will enable it, and any other value will disable it.
//Disabled by default for security.
$RConEnable = "1";

//RCon flood protection forces the user to wait a certain number of seconds before sending another command.
//Note that this is not user-specific; if someone else is using your RCon, you may have to wait a bit to
//send the command. Minimum is 10 seconds, maximum is 3600.
//Default is 20 seconds.
$RConFloodProtect = "20";


//RCon events are logged in RConLog.php for security. This variable will determine
//the maximum number of lines that will be stored in the log file before the old
//entries are truncated. Minimum is 100 lines. Maximum is 100000.
//Default is 1000 lines.
$RConLogSize = 1000;


// POPUP WINDOW SETTINGS
// POPUP WINDOW SETTINGS

//This value is boolean. When the RCon and PARAM buttons are clicked, the popup
//window will snap to the top left corner of the screen by default. When this
//variable is set to any value other than Yes or 1, the behavior is disabled.
//Default is 0.
$newWindowSnapToCorner = "0";


// GAMETYPE NAMES
// GAMETYPE NAMES

//The following is an array of gametypes. These are used when ParaTracker
//tries to identify a gametype. The array is listed with gametype 1 in the
//first value, gametype 2 in the second value, and so on. If you do not know
//what this is, do not change it, as ParaTracker cannot correct this if it
//is broken. The default value is:
//$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");

$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");


// DMFLAGS
// DMFLAGS

//The following is an array used to determine what the dmflags parameter controls.
//dmflags is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for dmflags 1, the second value is for dmflags 2, the third is for
//dmflags 4, the fourth is dmflags 8, the fifth is dmflags 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");

$dmflags = array("", "", "", "No Fall Damage", "Fixed cg_fov", "No footsteps", "No drown damage", "Fixed CL_Yawspeed", "No fixed anims", "No realistic hook");


// WEAPON FLAGS
// WEAPON FLAGS

//The following is an array used to determine what the g_weaponDisable parameter controls.
//g_weaponDisable is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for g_weaponDisable 1, the second value is for g_weaponDisable 2, the
//third is for g_weaponDisable 4, the fourth is g_weaponDisable 8, the fifth is
//g_weaponDisable 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "FC1 Flechette", "Rocket Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");

$weaponFlags = array("", "Stun Baton", "Melee", "Lightsaber", "Bryar Blaster Pistol", "E-11 Blaster", "Tenloss Disruptor Rifle", "Wookiee Bowcaster", "Imperial Heavy Repeater", "DEMP 2", "Golan Arms FC1 Flechette", "Merr-Sonn Portable Missile Launcher", "Thermal Detonator", "Trip Mine", "Detonation Pack", "Stouker Concussion Rifle", "Bryar Blaster Pistol (Old)", "Emplaced Gun", "Turret");


// FORCE POWER FLAGS
// FORCE POWER FLAGS

//The following is an array used to determine what the g_forcePowerDisable parameter controls.
//g_forcePowerDisable is a bit value, and each value in this array is entered in numeric order,
//from the lowest value to the highest. The only reason it is in the config file is
//in case ParaTracker is being used by some other game than Jedi Academy, so that you,
//the user, can change it to match whatever game you like.
//The first value is for g_forcePowerDisable 1, the second value is for g_forcePowerDisable 2, the
//third is for g_forcePowerDisable 4, the fourth is g_forcePowerDisable 8, the fifth is
//g_forcePowerDisable 16, and so on.
//Blank values should be indicated by two double-quotes. ParaTracker will ignore them.
//If you do not know what this is, do not change it, as ParaTracker cannot correct this
//if it is broken. The default value is:
//$forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");

$forcePowerFlags = array("Heal", "Jump", "Speed", "Push", "Pull", "Mind Trick", "Grip", "Lightning", "Rage", "Protect", "Absorb", "Team Heal", "Team Force", "Drain", "Sight", "Saber Offense", "Saber Defense", "Saber Throw");


//End config file
?>