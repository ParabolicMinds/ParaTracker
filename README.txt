*******************************************************

  ParaTracker - The Ultimate Quake III Server Tracker

*******************************************************

Title: ParaTracker 1.0 RC

Authors: Parabolic Mind - NAB622, Phazmatis, Cagelight

Date released: 11/22/2016

__________________________________________

DESCRIPTION:

ParaTracker is a server tracker created for Jedi Academy, but it can also track most other Quake III games.

__________________________________________

INSTALLATION INSTRUCTIONS:

You must have PHP on your web server to use ParaTracker.

__________________________________________

HOW TO USE PARATRACKER:

First, open ParaConfig.php in a text editor. Set the IP address and port of your server, as well
as any other parameters that you feel like changing.

There are comments throughout the file that explain each feature. RCon is available, but is disabled
by default for security. There is also a Dynamic mode, which enables the user to input their own IP
address and port, but it is also disabled by default because it enables other users to use your
website's bandwidth, as well as being a security risk. DO NOT enable dynamic mode unless ParaTracker
is isolated in it's own webroot, as any security holes in ParaTracker could give someone root access
to your web server.


To use ParaTracker in static mode (Running directly from ParaConfig.php), load one of the skin files
on your web browser, like:
ParaTrackerA.php, ParaTrackerB.php, ParaTrackerC.php, ParaTrackerD.php, and so on.

To insert ParaTracker onto a web page in static mode, add an <object> tag somewhere on your website that
references the skin file you want to use, with the appropriate size and width settings. For instance:

<object id="ParaTracker" type="text/html" data="http://www.nab622.com/ParaTracker/ParaTrackerA.php" width="675" height="300" ></object>


To use ParaTracker in dynamic mode, you first have to enable it in ParaConfig.php. Then, load up
ParaTrackerDynamic.php in your web browser. An instruction page will appear that will help you get started.
Remember: other people can use ParaTrackerDynamic.php to track their servers, using your server as the medium.


All RCon usage is logged in the logs/ folder. Inside the logs folder will be a folder named after the server
IP address and port number of the server, and inside that folder will be RConLog.php. For security reasons,
you cannot load RConLog.php from a web browser - you must download it to your computer, and open it in a
text editor.

__________________________________________

HOUSEKEEPING:

ParaTracker writes the server data it receives to several files and folders, and if it is loaded before the
floodprotect time has elapsed, it will serve up the old data again. As long as the IP address and port number
of the server do not change, the files will be overwritten with every refresh, and everything should be
dandy. However, when ParaTracker runs in dynamic mode, it will make a new folder for every IP address
the users attempt to use. For flood protection reasons, this has to exist, even though it causes a lot of
extra files to be written.

These files are very small; however, if they take up too much space, you can simply delete the entire info/
folder. ParaTracker will re-generate the necessary info the next time it runs.

RCon logs are the same way. They shouldn't get very large, and you shouldn't delete them, but if the need
should arise, just delete the entire logs/ folder, and new information will be generated.


__________________________________________


BUGS:

Please find them for me. I've found and fixed a ton already, but I'm sure I'm missing many more.

__________________________________________


CREDITS:

Good coding: Phazmatis and Cagelight
Bad coding: NAB622

__________________________________________

THIS MODIFICATION IS NOT MADE, DISTRIBUTED, OR SUPPORTED BY ACTIVISION, RAVEN, OR LUCASARTS ENTERTAINMENT COMPANY LLC. ELEMENTS TM & © LUCASARTS ENTERTAINMENT COMPANY LLC AND/OR ITS LISCENSORS. I THINK SHIFT LOCK IS STUCK ON. WHO CARES. NOBODY READS THESE THINGS ANYWAY, ESPECIALLY THE PART IN CAPITALS AT THE END. SO I DON'T KNOW WHY IT'S THERE. SO I'LL MAKE SURE IT'S NICE AND LONG. THAT WAY YOU ALL KEEP READING IT BECAUSE YOU ARE INTERESTED IN WHAT IT MIGHT CONTAIN. BUT HAHA! YOU'VE BEEN FOOLED! IT DOESN'T CONTAIN ANYTHING WORTHWHILE! SO THERE! HAHA! AND YOU'VE WASTED YOUR TIME READING THIS! :P