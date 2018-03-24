﻿refreshCancelled = 0
animationList = []
teamCount = 0
team1score = 0
team2score = 0
team3score = 0
team4score = 0
team1count = 0
team2count = 0
team3count = 0
team4count = 0

document.addEventListener("DOMContentLoaded", function(event)
{
    changeSetupPageFunction()
})

//This function opens the param window
function param_window()
{
	    paramWindow = window.open("Param.php?" + window.location.href.split("?")[1], "paramWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=600,height=700");
}

//This function opens the RCon window
function rcon_window()
{
	if (data.RConEnable === true)
	{
		rconWindow = window.open("RCon.php?" + window.location.href.split("?")[1], "rconWindow", "resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=780,height=375");
	}
}

//This function opens the Analytics window
function analytics_window()
{
		analyticsWindow = window.open(data.utilitiesPath + "Analytics.php?ip=" + data.serverIPAddress + "&port=" + data.serverPort, "analyticsWindow" ,"resizable=no,titlebar=no,menubar=no,status=no,scrollbars=yes,width=1100,height=800");
}

//This function opens the Analytics window
function setup_window()
{
		setupWindow = window.open("ParaTrackerDynamic.php", "ParaTrackerSetupWindow" ,"width=1100,height=800");
}

//This function handles the animated levelshots
function animateLevelshot()
{
    if(maxLevelshots > 1)
    {
        originalStyleData = document.getElementById("topLayerFade").style.cssText;
        //Set mode to 0 to prevent further triggering
        mode = 0;
        //Clear any timers that shouldn't be active, just in case
        clearTimeout(timer);

        if(allowTransitions == 1)
        {
            if(data.levelshotTransitionAnimation == "0")
            {
                document.getElementById("topLayerFade").style.cssText = originalStyleData + " animation-duration: " + levelshotTransitionTime + "s; animation-fill-mode: forwards; animation-name: " + animationList[Math.floor((Math.random() * animationList.length))] + ";";
            }
            else
            {
                document.getElementById("topLayerFade").style.cssText = originalStyleData + " animation-duration: " + levelshotTransitionTime + "s; animation-fill-mode: forwards; animation-name: levelshotTransition" + data.levelshotTransitionAnimation + ";";
            }
            timer = setTimeout("swapLevelshots()", levelshotTransitionTime * 1000);
        }
        else
        {
            //Transitions are disabled, but we were clicked. We should still transition to the next levelshot, but with transition 1 only.
            document.getElementById("topLayerFade").style.cssText = originalStyleData + " animation-duration: " + levelshotTransitionTime + "s; animation-fill-mode: forwards; animation-name: levelshotTransition1;";
            timer = setTimeout("swapLevelshots()", levelshotTransitionTime * 1000);
        }
    }
}

function swapLevelshots()
{
    if (maxLevelshots > 1)
    {
        //Clear any timers that shouldn't be active, just in case
        clearTimeout(timer);
        //A levelshot has finished it's transition, so reset everything
        count = 0;
        shot++;

        if(shot > maxLevelshots)
        {
            shot = 1;
        }

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

        if(allowTransitions == 1)
        {
            timer = setTimeout("animateLevelshot()", 1000 * levelshotDisplayTime)
        }
    }
}

function detectLevelshotClasses()
{
    //Let's detect all levelshot transition animation classes currently in memory.
    //If levelshot transitions are set to random, we'll pick one at random from this list.

    //Browsers are now blocking Javascript access to the style sheets, so this method is invalid.
    /*
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
    */

	//We will now have to assume the original 15 levelshots, as given in the original animation stylesheet.
    animationList = []
    for(iLev = 1; iLev <= 15; iLev++)
	{
        animationList.push("levelshotTransition".concat(iLev))
	}
}

//This function sets up the tracker to run for it's first time
function firstExecution()
{
    if(typeof(data) === 'undefined')
    {
        return
    }
    paraTrackerVersionObject = document.getElementById("paraTrackerVersion")
    serverNameObject = document.getElementById("serverName")
    geoIPFlagObject = document.getElementById("geoIPFlag")
    gameTitleObject = document.getElementById("gameTitle")
    nameScorePingObject = document.getElementById("nameScorePing")
    playerListObject = document.getElementById("playerList")
    playerCountObject = document.getElementById("playerCount")
    mapNameObject = document.getElementById("mapName")
    modNameObject = document.getElementById("modName")
    gametypeObject = document.getElementById("gametype")
    IPAddressObject = document.getElementById("IPAddress")
    serverPingObject = document.getElementById("serverPing")
    errorMessageObject = document.getElementById("errorMessage")
    errorAddressObject = document.getElementById("errorAddress")
    refreshTimerObject = document.getElementById("refreshTimer")

    nameHeader = document.getElementById("nameHeader")
    scorePing = document.getElementById("scoreHeader")
    pingHeader = document.getElementById("pingHeader")
    if(document.getElementById("teamHeader"))
    {
        teamHeader = document.getElementById("teamHeader")
    }
    sortByName = 0
    sortByScore = 1
    sortByPing = 0
    sortByTeam = 1
    
    allowTransitions = data.allowTransitions;
    levelshotDisplayTime = data.levelshotDisplayTime
    levelshotTransitionTime = data.levelshotTransitionTime

    if(typeof data.serverInfo !== 'undefined' && typeof data.serverInfo.mapname !== 'undefined')
    {
        mapname = data.serverInfo.mapname
    }
    else
    {
        mapname = ""
    }

    if(document.getElementById('blinker'))
    {
        blinker = document.getElementById("blinker")
    }

    levelshots = data.levelshotsArray
    maxLevelshots = levelshots.length
    setUpLevelshots()
    inputData()
}

function setUpLevelshots()
{
    shot = 1;   //Levelshot number.
    opac = 1;   //Opacity level for the top layer.
    mode = 1;   //0 means we are delaying between fades. 1 means a fade is in progress.
    count = 0;

    if (maxLevelshots > 1 && typeof topLayerFade !== 'undefined');
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

            if(allowTransitions == 1)
            {
                timer = setTimeout("animateLevelshot()", 1000 * levelshotDisplayTime);
            }
        }
}

function inputData()
{
    contractDiv('reconnectButton')
    initializeTimer()

     if(data.serverInfo)
     {
        if(mapname != data.serverInfo.mapname || maxLevelshots != data.levelshotsArray.length)
        {
            //Set mode to 0 to prevent further triggering
            mode = 0;
            clearTimeout(timer)

            //Either the map has changed, or the levelshots have changed.
            //Load new levelshots and restart the script.

            expandDiv('loading')
            document.getElementById('loading').style.cssText = "animation-duration: .5s; animation-fill-mode: forwards; animation-name: loadingLevelshotAnimationOn;"
            mapname = data.serverInfo.mapname
            levelshots = data.levelshotsArray
            maxLevelshots = levelshots.length
            setupTimer = setTimeout("setUpLevelshots()", 550)
            loadTimer = setTimeout("document.getElementById('loading').style.cssText = 'animation-duration: .5s; animation-fill-mode: forwards; animation-name: loadingLevelshotAnimationOff;'", 1550)
            loadTimer2 = setTimeout("contractDiv('loading')", 2100)
        }
    }

    if(data.serverOnline == true)
    {
        contractDiv('errorMessage')
        contractDiv('errorAddress')

        expandDiv('serverName')
        expandDiv('geoIPFlag')
        expandDiv('playerList')
        expandDiv('serverPing')
        expandDiv('gameTitle')
        expandDiv('playerList')
        expandDiv('rconButton')
        expandDiv('paramButton')
        expandDiv('nameScorePing')
        expandDiv('playerCount')
        expandDiv('mapName')
        expandDiv('modName')
        expandDiv('IPAddress')
        expandDiv('gametype')

        if(data.filterOffendingServerNameSymbols == false)
        {
            clear_element(serverNameObject)
            serverNameObject.appendChild(colorize(data.serverInfo.servernameUnfiltered))
        }
        else
        {
            clear_element(serverNameObject)
            serverNameObject.appendChild(colorize(data.serverInfo.servername))
        }

        changeHTMLData(paraTrackerVersionObject, data.version)

        if(data.displayGameName)
        {
            changeHTMLData(gameTitleObject, data.serverInfo.gamename)
        }
        else
        {
            clear_element(gameTitleObject)
        }

        changeHTMLData(mapNameObject, data.info.mapname)
        changeHTMLData(modNameObject, data.info.gamename)
        changeHTMLData(gametypeObject, data.serverInfo.gametype)
        changeHTMLData(serverPingObject, data.serverInfo.serverPing)
        changeHTMLData(IPAddressObject, data.serverIPAddress + ":" + data.serverPort)
        changeHTMLData(playerCountObject, data.players.length + "/" + data.serverInfo.maxPlayers)

        if(blinker)
        {
            IPAddressObject.appendChild(blinker)
        }

        clear_element(geoIPFlagObject)
        if(data.enableGeoIP && typeof(data.serverInfo.geoIPcountryCode) !== 'undefined')
        {
            flag = document.createElement('div')
            flag.style.width = "100%"
            flag.style.height = "100%"
            flag.style.backgroundImage = "url('flags/" + data.serverInfo.geoIPcountryCode + ".svg')"
            flag.style.backgroundSize = "100% 100%"
            if(typeof(data.serverInfo.geoIPcountryName) !== 'undefined')
            {
                flag.title = data.serverInfo.geoIPcountryName
            }
            geoIPFlagObject.appendChild(flag)
        }
    parsePlayerProperties()
    sortPlayerData(data.players)
    }
    else
    {
        clear_element(errorMessageObject)
        let errorText = colorize(data.connectionErrorMessage)
        errorMessageObject.appendChild(document.createElement('br'))
        errorMessageObject.appendChild(document.createElement('br'))
        errorMessageObject.appendChild(errorText)


        clear_element(errorAddressObject)
        let errorAddressText = colorize(" " + data.serverIPAddress + ":" + data.serverPort)
        errorAddressObject.appendChild(document.createElement('br'))
        errorAddressObject.appendChild(document.createElement('br'))
        errorAddressObject.appendChild(errorAddressText)
        if(blinker)
        {
            errorAddressObject.appendChild(blinker)
        }

        expandDiv('errorMessage')
        expandDiv('errorAddress')

        contractDiv('serverName')
        contractDiv('geoIPFlag')
        contractDiv('playerList')
        contractDiv('serverPing')
        contractDiv('gameTitle')
        contractDiv('playerList')
        contractDiv('rconButton')
        contractDiv('paramButton')
        contractDiv('nameScorePing')
        contractDiv('playerCount')
        contractDiv('mapName')
        contractDiv('modName')
        contractDiv('IPAddress')
        contractDiv('gametype')

        reconnectTimer = setTimeout("expandDiv('reconnectButton')", data.reconnectTimeout * 1000)
    }

}

function adjustPlayerTableWidth()
{
    //Browsers are now blocking Javascript access to the style sheets, so this function is invalid.
    /*
    //We have to get the padding values for namescoreping to set the value correctly
    rules = document.styleSheets[2].cssRules;
    if (!rules) return;
    paddingWidth = 0
    for (let i = 0; i < rules.length; i++)
    {
        let rule = rules[i]
        if (rule.selectorText == '.nameScorePing')
        {
            paddingWidth = parseInt(rule.style.paddingLeft) + parseInt(rule.style.paddingRight)
        }
    }
    newWidth = playerListObject.scrollWidth - paddingWidth
    nameScorePingObject.style.width = newWidth + "px"
    */
}

function levelshotClick()
{
    if (maxLevelshots > 1 && mode == "1")
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

function expandDiv(divID)
{
    var test = ""
    if(divID)
    {
        test = document.getElementById(divID).className
        test = test.replace("collapsedFrame", "")
        test = test.replace("expandedFrame", "")
        test += " expandedFrame"
        test = test.replace("  ", " ")
        document.getElementById(divID).className = test
    }
}

function contractDiv(divID)
{
    var test = ""
    if(divID)
    {
        test = document.getElementById(divID).className
        test = test.replace("collapsedFrame", "")
        test = test.replace("expandedFrame", "")
        test += " collapsedFrame"
        test = test.replace("  ", " ")
        document.getElementById(divID).className = test
    }
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

function decolorize(input)
{
    if(input != "")
    {
        input = input.replace(/\^\d/g, "")
    }
    return input;
}

function initializeTimer()
{
    if(!data.enableAutoRefresh)
    {
        refreshTimer.className = "hiddenTimer";
        return
    }
    autoRefreshTimer = data.autoRefreshTimer;
    replaceStuff = refreshTimerObject.className;
    refreshTimerObject.className = replaceStuff.replace("hiddenTimer", "");

    changeHTMLData(refreshTimerObject, autoRefreshTimer);

    pageReloadTimer = setTimeout("refreshTick()", 1000);
}

//This function counts down each second until the tracker's auto-refresh
function refreshTick()
{
    if(refreshCancelled == "0")
    {
        if(autoRefreshTimer > 0)
        {
            autoRefreshTimer--;
            changeHTMLData(refreshTimerObject, autoRefreshTimer);
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
    clearTimeout(pageReloadTimer);
    changeHTMLData(refreshTimerObject, "...")
    contractDiv('reconnectButton')

    var reloadURL = window.location.href

    //Check to see if there's already a JSONReload attribute in the URL
    if(reloadURL.indexOf("?") != -1)
    {
        if(reloadURL.indexOf("&JSONReload=1") == -1 && reloadURL.indexOf("?JSONReload=1") == -1)
        {
            //There's already a question mark in the URL. Just add JSONReload to the end of it.
            reloadURL = reloadURL + "&JSONReload=1"
        }
    }
    else
    {
        //No question mark in the URL. Add one, and then JSONReload.
        reloadURL = reloadURL + "?JSONReload=1"
    }

    let svjsonreq = new XMLHttpRequest()
	svjsonreq.open('GET', reloadURL, true);
	svjsonreq.onerror = ()=>{
	    pageReloadTimer = setTimeout("pageReload()", 2000);
	}

	svjsonreq.ontimeout = svjsonreq.onerror
	svjsonreq.onexception = svjsonreq.onerror
	svjsonreq.onload = ()=>{

	    data = JSON.parse(svjsonreq.responseText)
	    inputData()

	}

	svjsonreq.send()
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
        if(autoRefreshTimer == "0")
        {
            window.stop();
        }
    }
    document.getElementById("refreshTimer").className = "hiddenTimer";
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
    var outputURL = "https://";
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
    var scrollShaftColor = "";
    var scrollThumbColor = "";
//    var scrollShaftOpacity = "";

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

        skinFile = document.getElementById("customSkin").value
        skinFile = skinFile.replace('https://', '')
        skinFile = skinFile.replace('http://', '')
        skinFile = '//' + skinFile
        skinFile = skinFile.replace('////', '//')
        skinFile = document.getElementById("skinID").value + "&customSkin=" + skinFile;

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

/*
	Scrollshaft and scrollthumb are disabled since modern browsers do not allow javascript access to CSS
    if(document.getElementById("scrollShaftColor").value != "")
    {
        outputURL += "&scrollShaftColor=" + document.getElementById("scrollShaftColor").value;
    }

    if(document.getElementById("scrollThumbColor").value != "")
    {
        outputURL += "&scrollThumbColor=" + document.getElementById("scrollThumbColor").value;
    }
*/


/*
    if(document.getElementById("scrollShaftOpacity").value != "")
    {
        outputURL += "&scrollShaftOpacity=" + document.getElementById("scrollShaftOpacity").value;
    }
*/

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

function utilitiesPage()
{
    document.location.hash='utilities'
}

function setupPage()
{
    document.location.hash=''
}

function activeButton(divID)
{
    var test = document.getElementById(divID).className
    test = test.replace("activeButton", "")
    test += " activeButton"
    test = test.replace("  ", " ")
    document.getElementById(divID).className = test
}

function inactiveButton(divID)
{
    var test = document.getElementById(divID).className
    test = test.replace("activeButton", "")
    test = test.replace("  ", " ")
    document.getElementById(divID).className = test
}

function openUtilitiesPage()
{
    document.getElementById("utilitiesButton").innerHTML = " Close ParaTracker Utilities "
    document.getElementById("utilities").className = "utilitiesDiv expandedFrame"
    document.getElementById("utilities").style.cssText = "animation-duration: .75s; animation-fill-mode: forwards; animation-name: openPage"
    document.getElementById("trackerSetup").className = "collapsedFrame"
    document.getElementById("body").className = "dynamicConfigPage dynamicConfigPageStyle utilitiesPageStyle"

    dynamicTrackerMessage.className = "collapsedFrame"
}

function closeUtilitiesPage()
{
        document.getElementById("utilitiesButton").innerHTML = " Open ParaTracker Utilities "
        document.getElementById("utilitiesButton").onclick = utilitiesPage
        document.getElementById("trackerSetup").className = "expandedFrame"
        contractDiv("utilities")
        document.getElementById("utilities").style.cssText = "animation-duration: .75s; animation-fill-mode: forwards; animation-name: closePage"
        document.getElementById("body").className = "dynamicConfigPage dynamicConfigPageStyle"

        dynamicTrackerMessage.className = ""
}

function changeSetupPageFunction()
{
    //Let's set everything back at the start
    if(typeof utilitiesButton === 'undefined')
    {
        //Terminate the script if we're running on the wrong page
        return
    }

    document.getElementById("utilitiesButton").onclick = setupPage

    contractDiv("bitValueCalculatorDiv")
    inactiveButton("bitValueCalculatorButton")

    if(typeof analyticsDiv !== 'undefined')
    {
        contractDiv("analyticsDiv")
        inactiveButton("analyticsButton")
    }
    if(typeof mapreqDiv !== 'undefined')
    {
        contractDiv("mapreqDiv")
        inactiveButton("mapreqButton")
    }
    if(typeof logViewerDiv !== 'undefined')
    {
        contractDiv("logViewerDiv")
        inactiveButton("logViewerButton")
    }
    if(typeof adminInfoDiv !== 'undefined')
    {
        contractDiv("adminInfoDiv")
        inactiveButton("adminInfoButton")
    }


    if(document.location.hash == "" || document.location.hash == "#")
    {
        closeUtilitiesPage()
//        document.getElementById("IPAddress").focus()
    }
    else if(document.location.hash == "#bitValueCalculator" && typeof bitValueCalculatorDiv !== 'undefined')
    {
        openUtilitiesPage()

        activeButton("bitValueCalculatorButton")
        expandDiv("bitValueCalculatorDiv")
    }
    else if(document.location.hash == "#analytics" && typeof analyticsDiv !== 'undefined')
    {
        openUtilitiesPage()

        activeButton("analyticsButton")
        expandDiv("analyticsDiv")
    }
    else if(document.location.hash == "#mapreq" && typeof mapreqDiv !== 'undefined')
    {
        openUtilitiesPage()

        activeButton("mapreqButton")
        expandDiv("mapreqDiv")
    }
    else if(document.location.hash == "#logViewer" && typeof logViewerDiv !== 'undefined')
    {
        openUtilitiesPage()

        activeButton("logViewerButton")
        expandDiv("logViewerDiv")
    }
    else if(document.location.hash == "#adminInfo" && typeof adminInfoDiv !== 'undefined')
    {
        openUtilitiesPage()

        activeButton("adminInfoButton")
        expandDiv("adminInfoDiv")
    }
    else
    {
        openUtilitiesPage()
    }
}

//=============== JSON Stuff ===============

function colorize(text, className = null, no_color_if_uncolored = true) {
	let cont = document.createElement('span')
	
	let txtary = []
	let curtxt = ''
	let curcol = '7'
	
	if(text != null)
	{
		for (let i = 0; i < text.length; i++) {
		    if (text[i] == '^') {
			    if (curtxt) {
				    curobj = {}
				    curobj.txt = curtxt
				    curobj.col = curcol
				    txtary.push(curobj)
			    }
			    curtxt = ''
			    i += 1
			    curcol = text[i]
		    } else {
			    curtxt += text[i]
		    }
		}
	}
	
  if (no_color_if_uncolored && txtary.length == 0 && curcol == '7') {
    cont.appendChild(document.createTextNode(text))
  } else {
    curobj = {}
    curobj.txt = curtxt
    curobj.col = curcol
    txtary.push(curobj)
    txtary.forEach((txt)=>{
      let txtsp = document.createElement('span')
      txtsp.className = 'color' + txt.col
      txtsp.appendChild(document.createTextNode(txt.txt))
      cont.appendChild(txtsp)
    })
  }
	
	if (className) cont.className = className
	
	return cont
}

function changeHTMLData(object, text)
{
    if(object)
    {
        clear_element(object)
        text = colorize(text)
        object.appendChild(text)
    }
}

function clear_element(e) {
    while (e.hasChildNodes()) {
      e.removeChild(e.lastChild);
    }
}

function wrap_span(txt, className = null) {
	let span = document.createElement('span')
	span.className = className
	span.appendChild(document.createTextNode(txt))
	return span
}

function parsePlayerProperties()
{
    if(data.players.length > 0)
    {
        count = data.players.length

        teamCount = 0
        team1score = 0
        team2score = 0
        team3score = 0
        team4score = 0
        team1count = 0
        team2count = 0
        team3count = 0
        team4count = 0
        check = 0

        for(i = 0; i < count; i++)
        {
            if(data.players[i].team > check)
            {
                teamCount = data.players[i].team
//                team1count++
            }
            if(data.players[i].team == 1)
            {
                team1score += data.players[i].score
                team1count++
            }
            if(data.players[i].team == 2)
            {
                team2score += data.players[i].score
                team2count++
            }
            if(data.players[i].team == 3)
            {
                team3score += data.players[i].score
                team3count++
            }
            if(data.players[i].team == 4)
            {
                team4score += data.players[i].score
                team4count++
            }
        }

        //Assign the team scores for team sorting
        for(i = 0; i < count; i++)
        {
            if(data.players[i].team == 1)
            {
                data.players[i].teamScore = team1score
            }
            else if(data.players[i].team == 2)
            {
                data.players[i].teamScore = team2score
            }
            else if(data.players[i].team == 3)
            {
                data.players[i].teamScore = team3score
            }
            else if(data.players[i].team == 4)
            {
                data.players[i].teamScore = team4score
            }
            else
            {
                data.players[i].teamScore = 0
            }
            
        }
    }
}

function populatePlayerList(input)
{
    playerListArray = input
    playerListLength = playerListArray.length

    clear_element(playerListObject)

    if(data.players.length > 0)
    {
        for(i = 0; i < playerListLength; i++)
        {
            let row = document.createElement('div')

            if (i % 2)
            {
                row.className = "playerRow1"
            }
            else
            {
                row.className = "playerRow2"
            }

            let playerName = document.createElement('div')
            playerName.className = "playerName playerNameSize"

            let playerScore = document.createElement('div')
            playerScore.className = "playerScore playerScoreSize"

            let playerPing = document.createElement('div')
            playerPing.className = "playerPing playerPingSize"
        
            if(teamCount > 0)
            {
                //Teams detected! Parse team data.
                let playerTeam = document.createElement('span')
                playerTeam.onclick = sortPlayersByTeamClick
                if(input[i].internalInfo == 1)
                {
                    row.className += " teamBackground" + input[i].team
                    row.title = "Click to toggle sorting by team"
                    row.onclick = sortPlayersByTeamClick
                    playerTeam.onclick = ""
                }
                playerTeam.className = "playerTeam playerTeamSize team" + input[i].team
                playerTeam.title = "Click to toggle sorting by team"
                if(input[i].team == "0")
                {
                    playerTeam.appendChild(document.createTextNode("  "))
                }
                else
                {
                    playerTeam.appendChild(document.createTextNode("► "))
                }
                playerName.appendChild(playerTeam)
            }

            playerName.appendChild(colorize(input[i].name, "", false), "")
            playerScore.appendChild(document.createTextNode(input[i].score))
            playerPing.appendChild(document.createTextNode(input[i].ping))
            row.appendChild(playerName)
            row.appendChild(playerScore)
            row.appendChild(playerPing)
            playerListObject.appendChild(row)
        }
    }
    else
    {
        let row = document.createElement('div')
        row.className = "noPlayersOnline textColor"
        row.appendChild(document.createTextNode(data.noPlayersOnlineMessage))
        playerListObject.appendChild(row)
    }
    adjustPlayerTableWidth()
}

function sortPlayersByTeamClick()
{
    sortByTeam++
    if(sortByTeam > 1)
    {
        sortByTeam = 0;
    }
    sortPlayerData(data.players)
}

function sortPlayersByNameClick()
{
    sortByScore = 0
    sortByPing = 0
    sortByName++
    if(sortByName > 2)
    {
        sortByName = 1;
    }
    sortPlayerData(data.players)
}

function sortPlayersByScoreClick()
{
    sortByName = 0
    sortByPing = 0
    sortByScore++
    if(sortByScore > 2)
    {
        sortByScore = 1;
    }
    sortPlayerData(data.players)
}

function sortPlayersByPingClick()
{
    sortByName = 0
    sortByScore = 0
    sortByPing++
    if(sortByPing > 2)
    {
        sortByPing = 1;
    }
    sortPlayerData(data.players)
}

function checkForNullPlayerNames(input)
{
    for(i = 0; i < input.length; i++)
    {
        if(input[i].name == null)
        {
            input[i].name = ""
        }
    }
    return(input)
}

function sortPlayerData(input)
{
    checkForNullPlayerNames(input)
    changeHTMLData(nameHeader, "")
    changeHTMLData(scoreHeader, "")
    changeHTMLData(pingHeader, "")
    changeHTMLData(teamHeader, "")

    if(sortByName == 1)
    {
        sortPlayersByPing(input)
        sortPlayersByScore(input)
        sortPlayersByName(input)
        changeHTMLData(nameHeader, " ↓")
    }
    else if(sortByName == 2)
    {
        sortPlayersByPing(input)
        sortPlayersByScore(input)
        sortPlayersByName(input)
        input.reverse()
        changeHTMLData(nameHeader, " ↑")
    }
    else if(sortByScore == 1)
    {
        sortPlayersByName(input)
        sortPlayersByPing(input)
        sortPlayersByScore(input)
        changeHTMLData(scoreHeader, " ↓")
    }
    else if(sortByScore == 2)
    {
        sortPlayersByName(input)
        sortPlayersByPing(input)
        sortPlayersByScore(input)
        changeHTMLData(scoreHeader, " ↑")
    }
    else if(sortByPing == 1)
    {
        sortPlayersByName(input)
        sortPlayersByScore(input)
        sortPlayersByPing(input)
        changeHTMLData(pingHeader, " ↓")
    }
    else if(sortByPing == 2)
    {
        sortPlayersByName(input)
        sortPlayersByScore(input)
        sortPlayersByPing(input)
        input.reverse()
        changeHTMLData(pingHeader, " ↑")
    }
    sortPlayersByTeam(input)

    if(teamCount > 0 && sortByTeam == 1)
    {
        //We have teams! Add total scores to the list
        teamScores = []
        if (team1count > 0)
        {
            teamScores.push({"team":1,"score":team1score,"ping":"","name":"Red score:",internalInfo:1})
        }
        if (team2count > 0)
        {
            teamScores.push({"team":2,"score":team2score,"ping":"","name":"Blue score:",internalInfo:1})
        }
        if (team3count > 0)
        {
            teamScores.push({"team":3,"score":team3score,"ping":"","name":"Yellow score:",internalInfo:1})
        }
        if (team4count > 0)
        {
            teamScores.push({"team":4,"score":team4score,"ping":"","name":"Green score:",internalInfo:1})
        }
        sortPlayersByScore(teamScores)
        if(sortByScore == 2)
        {
            teamScores.reverse()
        }
        populatePlayerList(teamScores.concat(data.players))
    }
    else
    {
        //No teams. Just use the sorted server info
        populatePlayerList(data.players)
    }
}

function tagArrayByPosition(array)
{
    count = array.length;
    for(i = 0; i < count; i++)
    {
        array[i].index = i
    }
    return(array)
}

function sortPlayersByName(input)
{
    tagArrayByPosition(input)
    input.sort((a, b)=>{
    let aname = a.name
    let bname = b.name
    aname = decolorize(aname.toLowerCase())
    bname = decolorize(bname.toLowerCase())
    aname = aname.trim()
    bname = bname.trim()
    if (aname < bname) return -1
    if (aname > bname) return 1
    return a.index - b.index
    })
}

function sortPlayersByScore(input)
{
    tagArrayByPosition(input)
    input.sort((a, b)=>{
    let ascore = parseInt(a.score)
    let bscore = parseInt(b.score)
    if (ascore < bscore) return 1
    if (ascore > bscore) return -1
    return a.index - b.index
    })
}

function sortPlayersByPing(input)
{
    tagArrayByPosition(input)
    input.sort((a, b)=>{
    let aping = parseInt(a.ping)
    let bping = parseInt(b.ping)
    if (sortByPing == 0 && aping >= 999) aping = -aping
    if (sortByPing == 0 && bping >= 999) bping = -bping
    if (aping < bping) return 1
    if (aping > bping) return -1
    return a.index - b.index
    })
}

function sortPlayersByTeam(input)
{
    if(sortByTeam == 1)
    {
        tagArrayByPosition(input)
        input.sort((a, b)=>{
        if(a.team == 0 && b.team == 0) return a.index - b.index
        if(a.team == 0 || typeof(a.team) == 'undefined') return 1
        if(b.team == 0 || typeof(b.team) == 'undefined') return -1
            if (a.teamScore && b.teamScore)
            {
                let ateamScore = parseInt(a.teamScore)
                let bteamScore = parseInt(b.teamScore)
                if (ateamScore < bteamScore)
                {
                    return 1
                }
                if (ateamScore > bteamScore)
                {
                    return -1
                }
            }
            else if(a.teamScore && !b.teamScore)
            {
                return -1
            }
            else if(!a.teamScore && b.teamScore)
            {
                return 1
            }
            let teamDifference = 0
            if(sortByScore == 1)
            {
                teamDifference = parseInt(a.team) - parseInt(b.team)
                if(teamDifference != 0)
                {
                    return teamDifference
                }
                else
                {
                    return a.index - b.index
                }
            }
            if(sortByScore == 2)
            {
                teamDifference = parseInt(b.team) - parseInt(a.team)
                if(teamDifference != 0)
                {
                    return teamDifference
                }
                else
                {
                    return b.index - a.index
                }
            }
            return a.index - b.index
        })
    }
    if(sortByScore == 2)
    {
        input.reverse()
    }
}
