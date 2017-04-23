//This function opens the param window
function param_window()
{
	if (newWindowSnapToCorner == "1")
	{
	    paramWindow = window.open("Param.php?ip=" + serverIPAddress + "&port=" + serverPort + "&skin=" + paraTrackerSkin + "&customSkin=" + customSkin + "&filterOffendingServerNameSymbols=" + filterOffendingServerNameSymbols, "paramWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=600,height=700,left=0,top=0");
	}
	else
	{
	    paramWindow = window.open("Param.php?ip=" + serverIPAddress + "&port=" + serverPort + "&skin=" + paraTrackerSkin + "&customSkin=" + customSkin + "&filterOffendingServerNameSymbols=" + filterOffendingServerNameSymbols, "paramWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=600,height=700");
	}
}

//This function opens the RCon window
function rcon_window()
{
	if (newWindowSnapToCorner == "1")
	{
		rconWindow = window.open("RCon.php?ip=" + serverIPAddress + "&port=" + serverPort + "&skin=" + paraTrackerSkin + "&customSkin=" + customSkin, "rconWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=780,height=375,left=0,top=0");
	}
	else
	{
		rconWindow = window.open("RCon.php?ip=" + serverIPAddress + "&port=" + serverPort + "&skin=" + paraTrackerSkin + "&customSkin=" + customSkin, "rconWindow" ,"resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=780,height=375");
	}
}

//This function handles the animated levelshots
function animateLevelshot()
{
    if(maxLevelshots > 1 && allowTransitions == 1)
    {
        //Set mode to 0 to prevent further triggering
        mode = 0;
        //Clear any timers that shouldn't be active, just in case
        clearTimeout(timer);
        originalStyleData = document.getElementById("topLayerFade").style.cssText;

        if(levelshotTransitionAnimation == "0")
        {
            document.getElementById("topLayerFade").style.cssText = originalStyleData + " animation-duration: " + levelshotTransitionTime + "s; animation-fill-mode: forwards; animation-name: " + animationList[Math.floor((Math.random() * animationList.length))] + ";";
        }
        else
        {
            document.getElementById("topLayerFade").style.cssText = originalStyleData + " animation-duration: " + levelshotTransitionTime + "s; animation-fill-mode: forwards; animation-name: levelshotTransition" + levelshotTransitionAnimation + ";";
        }
        timer = setTimeout("swapLevelshots()", levelshotTransitionTime * 1000);
    }
}

function swapLevelshots()
{
    if (maxLevelshots > 1 && allowTransitions == 1)
    {
        //Clear any timers that shouldn't be active, just in case
        clearTimeout(timer);
        //A levelshot has finished it's transition, so reset everything
        count = 0;
        shot++;

        if(shot > maxLevelshots) shot = 1;
        {
            document.getElementById("topLayerFade").style.cssText = document.getElementById("bottomLayerFade").style.cssText;
            document.getElementById("bottomLayerFade").style.cssText = document.getElementById("levelshotPreload1").style.cssText;
            document.getElementById("levelshotPreload1").style.cssText = document.getElementById("levelshotPreload2").style.cssText;
            document.getElementById("levelshotPreload2").style.cssText = 'background: url("' + levelshots[shot - 1] + '"); background-size: 100% 100%; background-repeat: no-repeat;';

            originalStyleData = "";
            opac = 1;
            count = 0;
            mode = 1;

            //Clear the levelshot timer, and force an immediate levelshot change
            clearTimeout(timer);

            timer = setTimeout("animateLevelshot()", 1000 * levelshotDisplayTime);
        }
    }
}

function detectLevelshotClasses()
{
//Let's detect all levelshot transition animation classes currently in memory.
//If levelshot transitions are set to random, we'll pick one at random from this list.

    var sheet = "";

    for (sheeti = 0; sheeti < document.styleSheets.length; sheeti++)
    {
      sheet = document.styleSheets[sheeti]

      if(sheet.cssRules)
      {
        for (rulei = 0; rulei < sheet.cssRules.length; rulei++)
            {
                rule = sheet.cssRules[rulei]
                if (rule.cssText.substring(0, 10) == "@keyframes" && rule.name.substring(0, 19) == "levelshotTransition")
                {
                    animationList.push(rule.name)
                }
            }
        }
    }
}

//This little bit of code pre-loads the second and third levelshots, and terminates the script if only one levelshot is available.
function firstExecution()
{
   if (maxLevelshots > 1 && allowTransitions == 1);
        {
            detectLevelshotClasses();
            document.getElementById("topLayerFade").style.cssText = 'background: url("' + levelshots[shot - 1] + '"); background-size: 100% 100%; background-repeat: no-repeat;';
            shot++;
            document.getElementById("bottomLayerFade").style.cssText = 'background: url("' + levelshots[shot - 1] + '"); background-size: 100% 100%; background-repeat: no-repeat;';

            //let's set up a pre-loader in case there are more than 2 levelshots
            shot++;
            //In case there are only two levelshots, then we will just go back to shot 1
            if(shot > maxLevelshots) shot = 1;
            document.getElementById("levelshotPreload1").style.cssText = 'background: url("' + levelshots[shot - 1] + '"); background-size: 100% 100%; background-repeat: no-repeat;';

            shot++;
            //In case there are only three levelshots, then we will just go back to shot 1
            if(shot > maxLevelshots) shot = 1;
            document.getElementById("levelshotPreload2").style.cssText = 'background: url("' + levelshots[shot - 1] + '"); background-size: 100% 100%; background-repeat: no-repeat;';

            opac = 1;
            count = 0;
            mode = 1;
            timer = setTimeout("animateLevelshot()", 1000 * levelshotDisplayTime);
        }
}

function levelshotClick()
{
    if (maxLevelshots > 1 && mode == "1" && allowTransitions == "1")
    {
        //Clear the levelshot timer, and force an immediate levelshot change
        clearTimeout(timer);
        animateLevelshot()
    }
}

function bitValueClick(cvarName)
{
    if (document.getElementById(cvarName).className == "collapsedList")
    {
        document.getElementById(cvarName).className = "expandedList"
    }
    else
    {
        document.getElementById(cvarName).className = "collapsedList"
    }
}

function expandContractDiv(divID)
{
    var test = document.getElementById(divID).className
        test = test.replace("  ", " ")

    if (test.search("collapsedFrame") > -1)
    {
        test = test.replace("collapsedFrame", "")
        test = test.replace("expandedFrame", "")
        test += " expandedFrame"
    }
    else
    {
        test = test.replace("collapsedFrame", "")
        test = test.replace("expandedFrame", "")
        test += " collapsedFrame"
    }
    test = test.replace("  ", " ")
    document.getElementById(divID).className = test
}

function expandBitValueCalculator()
{
    if (document.getElementById(cvarName).className == "collapsedFrame")
    {
        document.getElementById(cvarName).className = "expandedFrame"
    }
    else
    {
        document.getElementById(cvarName).className = "collapsedFrame"
    }
}

function makeReconnectButtonVisible()
{
    var replaceReconnectClass = "";
    replaceReconnectClass = document.getElementById("reconnectButton").className;
 	document.getElementById("reconnectButton").className = replaceReconnectClass.replace("hide", "");
}

function initializeTimer()
{
    replaceStuff = document.getElementById("refreshTimerDiv").className;
    document.getElementById("refreshTimerDiv").className = replaceStuff.replace("hiddenTimer", "");
    document.getElementById("refreshTimerDiv").innerHTML = refreshTimer;

    pageReloadTimer = setTimeout("refreshTick()", 1000);
}

//This function counts down each second until the tracker's auto-refresh
function refreshTick()
{
    if(refreshCancelled == "0")
    {
        if(refreshTimer > 0)
        {
            refreshTimer--;
            document.getElementById("refreshTimerDiv").innerHTML = refreshTimer;
            pageReloadTimer = setTimeout("refreshTick()", 1000);
        }
        else
        {
            pageReload();
        }
    }
}

function pageReload()
{
    document.getElementById("refreshTimerDiv").innerHTML = "...";
    clearTimeout(pageReloadTimer);
    window.location.reload(true);
}

function toggleReload()
{
    if(refreshCancelled == "1")
    {
        refreshCancelled = "0";
        pageReloadTimer = setTimeout("refreshTick()", 1000);
    }
    else
    {
        refreshCancelled = "1";
        if(refreshTimer == "0")
        {
            window.stop();
        }
    }
    document.getElementById("refreshTimerDiv").className = "hiddenTimer";
}

function disableRConForm()
{
    document.getElementById("commandTextField").readOnly = true;
    document.getElementById("passwordTextField").readOnly = true;
    document.getElementById("submitButton").disabled = true;
    return true;
}

//This function toggles the gradient behind the dynamic tracker on the setup page
function toggleGradientBackground()
{
    if(document.getElementById("backgroundToggle").checked == true)
    {
        document.getElementById("paraTrackerTestFrameContent").className = "paraTrackerTestFrameGradient";
    }
    else
    {
        document.getElementById("paraTrackerTestFrameContent").className = "";
    }
}

function toggleTextureBackground()
{
    if(document.getElementById("textureToggle").checked == true)
    {
        document.getElementById("paraTrackerTestFrameContent2").className = "paraTrackerTestFrame paraTrackerTestFrameTexture";
    }
    else
    {
        document.getElementById("paraTrackerTestFrameContent2").className = "paraTrackerTestFrame";
    }
}

function createURL()
{
    if(document.getElementById("skinID").value == "Custom")
    {
        document.getElementById("externalSkinFile").className = "customSkinSelections expandedFrame";
    }
    else
    {
        document.getElementById("externalSkinFile").className = "customSkinSelections collapsedFrame";
    }


    if(document.getElementById("IPAddress").value == "")
    {
        document.getElementById("finalURL").value = "Please enter an IP address!";
        document.getElementById("finalURLHTML").value = "Please enter an IP address!";
        return;
    }
    else
    {
        if(document.getElementById("PortNumber").value == "")
        {
            document.getElementById("finalURL").value = "Please enter a port number!";
            document.getElementById("finalURLHTML").value = "Please enter a port number!";
            return;
        }
        else
        {
    var outputURL = "http://";
    var width = "";
    var height = "";
//    var skinWidth = "";
    var skinHeight = "";
    var skinFile = "";
    var skinFieldValue = "";
    var backgroundColor = "";
    var backgroundOpacity = "";
    var textColor = "";
    var playerListColor1 = "";
    var playerListColor1Opacity = "";
    var playerListColor2 = "";
    var playerListColor2Opacity = "";
    var levelshotsEnabled = "";
    var font = "";
    var enableAutoRefresh = "";
    var levelshotTransitionTime = "";
    var levelshotDisplayTime = "";


    outputURL += document.getElementById("currentURL").value;
    outputURL += "?ip=";
    outputURL += document.getElementById("IPAddress").value;
    outputURL += "&port=";

    if(document.getElementById("PortNumber").value == "")
    {
        outputURL += "29070";
    }
    else
    {
        outputURL += document.getElementById("PortNumber").value;
    }

    outputURL += "&skin=";

    if(document.getElementById("skinID").value == "Custom")
    {
        if(document.getElementById("customSkin").value == "")
        {
            document.getElementById("finalURL").value = "Please enter a custom skin file!";
            document.getElementById("finalURLHTML").value = "Please enter a custom skin file!";
            return;
        }

        skinFile = document.getElementById("skinID").value + "&customSkin=" + document.getElementById("customSkin").value;

        if(document.getElementById("customWidth").value != "")
        {
            if(isNaN(document.getElementById("customWidth").value))
            {
                document.getElementById("finalURL").value = "Invalid custom skin width!";
                document.getElementById("finalURLHTML").value = "Invalid custom skin width!";
                return;
            }
            else
            {
                skinWidth = document.getElementById("customWidth").value;
            }

            if(document.getElementById("customHeight").value != "")
            {
                if(isNaN(document.getElementById("customHeight").value))
                {
                    document.getElementById("finalURL").value = "Invalid custom skin height!";
                    document.getElementById("finalURLHTML").value = "Invalid custom skin height!";
                    return;
                }
                else
                {
                    skinHeight = document.getElementById("customHeight").value;
                }
            }
            else
            {
                document.getElementById("finalURL").value = "Please enter a custom height!";
                document.getElementById("finalURLHTML").value = "Please enter a custom height!";
                return;
            }

        }
        else
        {
            document.getElementById("finalURL").value = "Please enter a custom width!";
            document.getElementById("finalURLHTML").value = "Please enter a custom width!";
            return;
        }
    }
    else
    {
        skinFieldValue = document.getElementById("skinID").value;
        skinFieldValue = skinFieldValue.split(":#:");
        skinFile = skinFieldValue[0];

        skinWidth = skinFieldValue[1];
        skinHeight = skinFieldValue[2];
    }

    outputURL += skinFile;


    outputURL += "&filterOffendingServerNameSymbols=" + document.getElementById("filterOffendingServerNameSymbols").checked;
    outputURL += "&displayGameName=" + document.getElementById("displayGameName").checked;
    outputURL += "&enableAutoRefresh=" + document.getElementById("enableAutoRefresh").checked;
    outputURL += "&levelshotsEnabled=" + document.getElementById("levelshotsEnabled").checked;

    if(document.getElementById("enableGeoIP"))
    {
        outputURL += "&enableGeoIP=" + document.getElementById("enableGeoIP").checked;
    }

    if(document.getElementById("levelshotDisplayTime").value != "")
    {
        outputURL += "&levelshotDisplayTime=" + document.getElementById("levelshotDisplayTime").value;
    }

    if(document.getElementById("levelshotTransitionTime").value != "")
    {
        outputURL += "&levelshotTransitionTime=" + document.getElementById("levelshotTransitionTime").value;
    }

    if(document.getElementById("backgroundColor").value != "")
    {
        outputURL += "&backgroundColor=" + document.getElementById("backgroundColor").value;
        if(document.getElementById("backgroundOpacity").value != "")
        {
            outputURL += "&backgroundOpacity=" + document.getElementById("backgroundOpacity").value;
        }
    }

    if(document.getElementById("playerListColor1").value != "")
    {
        outputURL += "&playerListColor1=" + document.getElementById("playerListColor1").value;
        if(document.getElementById("playerListColor1Opacity").value != "")
        {
            outputURL += "&playerListColor1Opacity=" + document.getElementById("playerListColor1Opacity").value;
        }
    }

    if(document.getElementById("playerListColor2").value != "")
    {
        outputURL += "&playerListColor2=" + document.getElementById("playerListColor2").value;
        if(document.getElementById("playerListColor2Opacity").value != "")
        {
            outputURL += "&playerListColor2Opacity=" + document.getElementById("playerListColor2Opacity").value;
        }
    }

    if(document.getElementById("textColor").value != "")
    {
        outputURL += "&textColor=" + document.getElementById("textColor").value;
    }

    if(document.getElementById("font").value != "")
    {
        outputURL += "&font=" + document.getElementById("font").value;
    }

    outputURL= encodeURI(outputURL);

    document.getElementById("finalURL").value = outputURL;

    outputURL = '<iframe id="ParaTracker" src="' + outputURL + '" width="' + skinWidth + '" height="' + skinHeight + '" sandbox="allow-forms allow-popups allow-scripts allow-same-origin" style="border:none;background:none transparent;" allowtransparency="true" scrolling="no"></iframe>';
    document.getElementById("finalURLHTML").value = outputURL;

    document.getElementById("paraTrackerTestFrameContent").innerHTML = "<br />\n" + outputURL + "\n<br /><br />";
    document.getElementById("paraTrackerTestFrame").className = "expandedFrame";
    document.getElementById("paraTrackerTestFrameContent").width = skinWidth + 100;
    document.getElementById("paraTrackerTestFrameContent").height = skinHeight + 100;
    }
}
return false;
}

function checkForOtherValue()
{
    if(document.getElementById("GameNameDropdown").value == "other")
    {
        document.getElementById("hideGameNameWhenUnnecessary").className = "expandedFrame";
    }
    else
    {
        document.getElementById("hideGameNameWhenUnnecessary").className = "collapsedFrame";
    }
}

function testURL()
{
    window.open(outputURL);
}

function clearOutputFields()
{
    document.getElementById("finalURL").value = "";
    document.getElementById("finalURLHTML").value = "";
    document.getElementById("paraTrackerTestFrameContent").innerHTML = "";
}

function RConFloodProtectTimer()
{
    if (timeRemaining == 0)
    {
        document.getElementById("RConTimeoutTimer").innerHTML = "RCon is ";
        document.getElementById("RConTimeoutText").innerHTML = "ready!";
    }
    if (timeRemaining == 1)
    {
        timeRemaining--;
        document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
        document.getElementById("RConTimeoutText").innerHTML = "seconds remaining.";
        RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
    }
    if (timeRemaining == 2)
    {
        timeRemaining--;
        document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
        document.getElementById("RConTimeoutText").innerHTML = "second remaining.";
        RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
    }
    if (timeRemaining > 2)
    {
        timeRemaining--;
        document.getElementById("RConTimeoutTimer").innerHTML = timeRemaining;
        document.getElementById("RConTimeoutText").innerHTML = "seconds remaining.";
        RConTimer = setTimeout("RConFloodProtectTimer()", 1000);
    }
}
