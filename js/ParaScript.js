function levelshotClick()
{
    if (mode == 1)
    {
        //Clear the levelshot timer, and force an immediate levelshot change
        clearTimeout(timer);
        timer = setTimeout("fadelevelshot()", 10);
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

function makeReconnectButtonVisible()
{
    var replaceReconnectClass = "";
    replaceReconnectClass = document.getElementById("reconnectButton").className;
 	document.getElementById("reconnectButton").className = replaceReconnectClass.replace("hide", "");
}

function pageReload()
{
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

function createURL()
{
    if(document.getElementById("IPAddress").value == "")
    {
        document.getElementById("finalURL").value = "Please enter an IP address!";
        document.getElementById("finalURLHTML").value = "Please enter an IP address!";
    }
    else
    {
    var outputURL = "http://";
    var width = "";
    var height = "";
    var radioButton = "";

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
    if(document.getElementById("SkinID-JSON"))
    {
        if(document.getElementById("SkinID-JSON").checked)
        {
            radioButton += "ParaJSON";
            width = "675";
            height = "300";
        }
    }
    if(document.getElementById("SkinID-H"))
    {
        if(document.getElementById("SkinID-H").checked)
        {
            radioButton += "ParaTrackerH";
            width = "120";
            height = "600";
        }
    }
    if(document.getElementById("SkinID-G"))
    {
        if(document.getElementById("SkinID-G").checked)
        {
            radioButton += "ParaTrackerG";
            width = "120";
            height = "600";
        }
    }
    if(document.getElementById("SkinID-F"))
    {
        if(document.getElementById("SkinID-F").checked)
        {
            radioButton += "ParaTrackerF";
            width = "468";
            height = "60";
        }
    }
    if(document.getElementById("SkinID-E"))
    {
        if(document.getElementById("SkinID-E").checked)
        {
            radioButton += "ParaTrackerE";
            width = "468";
            height = "60";
        }
    }
    if(document.getElementById("SkinID-D"))
    {
        if(document.getElementById("SkinID-D").checked)
        {
            radioButton += "ParaTrackerD";
            width = "250";
            height = "250";
        }
    }
    if(document.getElementById("SkinID-C"))
    {
        if(document.getElementById("SkinID-C").checked)
        {
            radioButton += "ParaTrackerC";
            width = "600";
            height = "225";
        }
    }
    if(document.getElementById("SkinID-B"))
    {
        if(document.getElementById("SkinID-B").checked)
        {
            radioButton += "ParaTrackerB";
            width = "600";
            height = "225";
        }
    }
    if(document.getElementById("SkinID-A"))
    {
        if(document.getElementById("SkinID-A").checked)
        {
            radioButton += "ParaTrackerA";
            width = "675";
            height = "300";
        }
    }
    if(radioButton == "")
    {
        radioButton = "ParaTrackerA";
    }

    outputURL += radioButton;
    
    outputURL += "&game=";

    if(document.getElementById("GameNameDropdown").value == "other")
    {
        if(document.getElementById("GameName").value == "")
        {
            outputURL += "Jedi Academy";
        }
        else
        {
        outputURL += document.getElementById("GameName").value;
        }
    }
    else
    {
    outputURL += document.getElementById("GameNameDropdown").value;
    }


    outputURL= encodeURI(outputURL);

    document.getElementById("finalURL").value = outputURL;

    outputURL = '<object id="ParaTracker" type="text/html" data="' + outputURL + '" width="' + width + '" height="' + height + '" ></object>';
    document.getElementById("finalURLHTML").value = outputURL;

    document.getElementById("paraTrackerTestFrameContent").innerHTML = outputURL;
    document.getElementById("paraTrackerTestFrame").className = "expandedFrame";
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
//    document.getElementById("paraTrackerTestFrame").className = "collapsedFrame";  //I see no reason to remove the frame, it just scrolls the page up for no reason and is annoying.
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

