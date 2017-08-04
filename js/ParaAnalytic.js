weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
weekdays_short = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
months_short = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]

//This value is boolean. true means 12 hour clock, false means 24 hour clock (Military time).
twelveHourClockMode = true
//This value is boolean. true means to display times in UTC, false means local time.
displayTimesInUTC = false

//This value is boolean. true means to display block names seeded by color. false means to show them all as the same color.
colorizeBlocks = true

//This value is boolean. true means to display the server's offline times. false means to hide it.
showOfflineTimes = true
//This value is boolean. true means to display the game name. false means to hide it.
showGameName = true
//This value is boolean. true means to display the server name. false means to hide it.
showServerName = true
//This value is boolean. true means to display the name of the mod being run. false means to hide it.
showModName = true
//This value is boolean. true means to display the game type being played. false means to hide it.
showGametype = true

//This value determines how maps are sorted.
//false = sort by players per hour
//true = sort by time played
sortMapsBy = false

//This value is boolean. false means to hide grid lines, true means to show them.
showHorizontalGridLines = true
//This value is boolean. false means to hide grid lines, true means to show them.
showVerticalGridLines = true
//This value is boolean. false means to hide grid labels, true means to show them.
showGridLabels = true

//ParaTracker will retain data between queries from the web server. This value determines how many
//seconds a new request must be from the currently loaded data, to cause the old data to be wiped
maxTimeDifference = 604800

//ParaTracker will only request a certain amount of data from the web server at once.
//Default value is 32 weeks.
maxTimeRequest = 19353600

//This variable is the number of milliseconds Analytics will wait when someone changes the server address, port, start time, or end time, before acting on the new value
changeTimeTimeout = 2000


loadingDiv = document.createElement("div")

offset = 0
startTime = 0
endTime = 0
URLStartTime = 0
URLEndTime= 0
timeDifference = 0

currentTimeList = []

onlineStatusArray = []
refreshTimesArray = []
gameNameArray = []
sv_hostnameArray = []
mapnameArray = []
modNameArray = []
gametypeArray = []
playerCountArray = []

mapList = []
mapListByTime = []
playerCountByTime = []
playersPerHour = []
mapTime = []
mapName = []
levelshotsArray = []

startTimeTimer = 0
endTimeTimer = 0
serverAddressTimer = 0

serverAddress = ""
serverPort = ""

loadingPage = 1

document.addEventListener("DOMContentLoaded", function(event)
{
    if(typeof(runOnStartup) === 'undefined' || runOnStartup != 0)
    {
        //Start the page with the controls frozen!
        freezeControls()

        analyticsDataFrame = document.getElementById("analyticsDataFrame")

        checkStartAndEndTimesForZero()

        parseHash()

        updateControlsFromHash()
        initializeTimeFields()

        rasterizeData()
        loadAnalyticsData()
        loadingPage = 0
    }
})

function checkStartAndEndTimesForZero()
{
    if(startTime == 0 || !checkValidInput(startTime))
    {
        startTime = Math.floor(Date.now() / 1000) - 604800
    }
    if(endTime == 0 || !checkValidInput(endTime))
    {
        endTime = Math.floor(Date.now() / 1000)
    }
}

function freezeControls()
{
    showLoadingMessage()
    startTimeField.disabled = true
    endTimeField.disabled = true
    resetTimeFields.disabled = true
    displayTimesInUTCTrue.disabled = true
    displayTimesInUTCFalse.disabled = true
    twelveHourClockSettingTrue.disabled = true
    twelveHourClockSettingFalse.disabled = true
    colorizeBlocksTrue.disabled = true
    colorizeBlocksFalse.disabled = true
    sortMapsByFalse.disabled = true
    sortMapsByTrue.disabled = true
    showOfflineTimesCheckbox.disabled = true
    showGameNameCheckbox.disabled = true
    showServerNameCheckbox.disabled = true
    showModNameCheckbox.disabled = true
    showGametypeCheckbox.disabled = true
    showHorizontalGridLinesCheckbox.disabled = true
    showVerticalGridLinesCheckbox.disabled = true
    showGridLabelsCheckbox.disabled = true
}

function unFreezeControls()
{
    startTimeField.disabled = false
    endTimeField.disabled = false
    resetTimeFields.disabled = false
    displayTimesInUTCTrue.disabled = false
    displayTimesInUTCFalse.disabled = false
    twelveHourClockSettingTrue.disabled = false
    twelveHourClockSettingFalse.disabled = false
    colorizeBlocksTrue.disabled = false
    colorizeBlocksFalse.disabled = false
    sortMapsByFalse.disabled = false
    sortMapsByTrue.disabled = false
    showOfflineTimesCheckbox.disabled = false
    showGameNameCheckbox.disabled = false
    showServerNameCheckbox.disabled = false
    showModNameCheckbox.disabled = false
    showGametypeCheckbox.disabled = false
    showHorizontalGridLinesCheckbox.disabled = false
    showVerticalGridLinesCheckbox.disabled = false
    showGridLabelsCheckbox.disabled = false
    hideLoadingMessage()
}

function showHideStuff(elementName)
{
    let test = elementName.className

    if (test.search("hiddenStuff") > -1)
    {
        test = test.replace("hiddenStuff", "")
        test += " "
    }
    else
    {
        test = test.replace("hiddenStuff", "")
        test += " hiddenStuff"
    }
    test = test.replace("  ", " ")
    elementName.className = test

}

function addTimezoneOffset(input)
{
    if(typeof(input) === 'undefined') input = 0
    if(displayTimesInUTC) return input
    return input + new Date().getTimezoneOffset() * 60
}

function subtractTimezoneOffset(input)
{
    if(typeof(input) === 'undefined') input = 0
    if(displayTimesInUTC) return input
    return input - new Date().getTimezoneOffset() * 60 * 2
}

function updateControlsFromHash()
{
    let ipAndPort = window.location.href.split("#")[0]
    ipAndPort = ipAndPort.split("?")[1]

    ipAndPort = ipAndPort.split("&")

    for(i = 0; i < ipAndPort.length; i++)
    {
        ipPortSplit = ipAndPort[i].split("=")
        if(ipPortSplit[0] == "ip")
        {
            serverAddress = ipPortSplit[1].trim()
        }
        if(ipPortSplit[0] == "port")
        {
            serverPort = ipPortSplit[1].trim()
        }
    }
    serverAddressField.value = serverAddress
    serverPortField.value = serverPort

    if(URLStartTime)
    {
        setStartTime(URLStartTime)
    }
    else
    {
        setStartTime(Date.now() / 1000 - 604800)
    }

    if(URLEndTime)
    {
        setEndTime(URLEndTime)
    }
    else
    {
        setEndTime(Date.now() / 1000)
    }

    if(displayTimesInUTC)
    {
        displayTimesInUTCTrue.checked = true
        displayTimesInUTCFalse.checked = false
    }
    else
    {
        displayTimesInUTCTrue.checked = false
        displayTimesInUTCFalse.checked = true
    }

    if(twelveHourClockMode)
    {
        twelveHourClockSettingTrue.checked = true
        twelveHourClockSettingFalse.checked = false
    }
    else
    {
        twelveHourClockSettingTrue.checked = false
        twelveHourClockSettingFalse.checked = true
    }

    if(colorizeBlocks)
    {
        colorizeBlocksTrue.checked = true
        colorizeBlocksFalse.checked = false
    }
    else
    {
        colorizeBlocksTrue.checked = false
        colorizeBlocksFalse.checked = true
    }

    if(sortMapsBy)
    {
        sortMapsByFalse.checked = false
        sortMapsByTrue.checked = true
    }
    else
    {
        sortMapsByFalse.checked = true
        sortMapsByTrue.checked = false
    }

    if(showOfflineTimes)
    {
        showOfflineTimesCheckbox.checked = true
    }
    else
    {
        showOfflineTimesCheckbox.checked = false
    }

    if(showGameName)
    {
        showGameNameCheckbox.checked = true
    }
    else
    {
        showGameNameCheckbox.checked = false
    }

    if(showServerName)
    {
        showServerNameCheckbox.checked = true
    }
    else
    {
        showServerNameCheckbox.checked = false
    }

    if(showModName)
    {
        showModNameCheckbox.checked = true
    }
    else
    {
        showModNameCheckbox.checked = false
    }

    if(showGametype)
    {
        showGametypeCheckbox.checked = true
    }
    else
    {
        showGametypeCheckbox.checked = false
    }

    if(showHorizontalGridLines)
    {
        showHorizontalGridLinesCheckbox.checked = true
    }
    else
    {
        showHorizontalGridLinesCheckbox.checked = false
    }

    if(showVerticalGridLines)
    {
        showVerticalGridLinesCheckbox.checked = true
    }
    else
    {
        showVerticalGridLinesCheckbox.checked = false
    }

    if(showGridLabels)
    {
        showGridLabelsCheckbox.checked = true
    }
    else
    {
        showGridLabelsCheckbox.checked = false
    }

}

function updateInfoFromHash()
{
    freezeControls()
    parseHash()
    loadAnalyticsData()
}

function resetTimeFieldValues()
{
    freezeControls()
    loadingPage = 1

    initializeTimeFields()

    loadingPage = 0
    startTime = 0
    endTime = 0
    URLstartTime = 0
    URLendTime = 0

    updateHash('', '')
    loadAnalyticsData()
}

function initializeTimeFields()
{
    if(displayTimesInUTC)
    {
        setStartTime(Date.now() / 1000 - 604800 + subtractTimezoneOffset())
        setEndTime(Date.now() / 1000)
    }
    else
    {
//        setStartTime(Date.now() / 1000 - 604800 + subtractTimezoneOffset())
//        setEndTime(Date.now() / 1000 + subtractTimezoneOffset())
    }
}

function parseStartTime(value)
{
    let startValue = new Date(startTimeField.value)
    if(displayTimesInUTC)
    {
        startValue = Math.floor(startValue.valueOf() / 1000 + subtractTimezoneOffset())
//        startValue = addTimezoneOffset(startValue)
    }
    else
    {
        startValue = Math.floor(startValue.getTime() / 1000)
//        startValue = addTimezoneOffset(startValue)
    }
    if((typeof(value) !== 'undefined' && !value) || isNaN(startValue)) startValue = 0
    return startValue
}

function parseEndTime(value)
{
    let endValue = new Date(endTimeField.value);
    if(displayTimesInUTC)
    {
        endValue = Math.floor(endValue.valueOf() / 1000)
//        endValue = addTimezoneOffset(endValue)
    }
    else
    {
        endValue = Math.floor(endValue.getTime() / 1000)
//        endValue = addTimezoneOffset(endValue)
    }
    if((typeof(value) !== 'undefined' && !value) || isNaN(endValue)) startValue = 0
    return endValue
}

function setStartTime(input)
{
    startTimeField.value = createISOString(new Date(input * 1000))
//    startTimeField.value = new Date(input * 1000 + subtractTimezoneOffset()).toISOString().slice(0, 19)
//    startTimeField.value = new Date(input * 1000).toISOString().slice(0, 19)
}

function setEndTime(input)
{
    endTimeField.value = createISOString(new Date(input * 1000))
//    endTimeField.value = new Date(input * 1000 + subtractTimezoneOffset()).toISOString().slice(0, 19)
//    endTimeField.value = new Date(input * 1000).toISOString().slice(0, 19)
}

function createISOString(input)
{
    let yearValue = "";
    let monthValue = "";
    let dayValue = "";
    let hourValue = "";
    let minutesValue = "";
    let secondsValue = "";

/*
    if(displayTimesInUTC)
    {
        yearValue = ("0000" + input.getUTCFullYear().toString()).slice(-4)
        monthValue = ("0" + (parseInt(input.getUTCMonth().toString()) + 1).toString()).slice(-2)
        dayValue = ("0" + input.getUTCDate().toString()).slice(-2)
        hourValue = ("0" + input.getUTCHours().toString()).slice(-2)
        minutesValue = ("0" + input.getUTCMinutes().toString()).slice(-2)
        secondsValue = ("0" + input.getUTCSeconds().toString()).slice(-2)
    }
    else
    {
*/
        yearValue = ("0000" + input.getFullYear().toString()).slice(-4)
        monthValue = ("0" + (parseInt(input.getMonth().toString()) + 1).toString()).slice(-2)
        dayValue = ("0" + input.getDate().toString()).slice(-2)
        hourValue = ("0" + input.getHours().toString()).slice(-2)
        minutesValue = ("0" + input.getMinutes().toString()).slice(-2)
        secondsValue = ("0" + input.getSeconds().toString()).slice(-2)
//    }

    output = yearValue + "-" + monthValue + "-" + dayValue + "T" + hourValue + ":" + minutesValue + ":" + secondsValue
    return output
}

function updateHash(variableName, value)
{
    //Clear these so we don't execute twice by mistake
    clearTimeouts()

    let hashValue = document.location.hash
    hashValue = hashValue.replace("#", "")

    let hashArray = []
    let outputArray = []
    let output = []

    if(hashValue.length > 0) hashArray = hashValue.split("&")

    //Add the new value to the array before processing
    hashArray.push(variableName + "=" + value)

    for(i = 0; i < hashArray.length; i++)
    {
        hashVariable = hashArray[i].split("=")
        if(!checkValidInput(hashVariable[0])) continue
        if(!checkValidInput(hashVariable[1])) continue

        if(hashVariable[0] == "startTime") outputArray[0] = hashArray[i]
        if(hashVariable[0] == "endTime") outputArray[1] = hashArray[i]
        if(hashVariable[0] == "twelveHourClockMode") outputArray[2] = hashArray[i]
        if(hashVariable[0] == "displayTimesInUTC") outputArray[3] = hashArray[i]
        if(hashVariable[0] == "colorizeBlocks") outputArray[4] = hashArray[i]
        if(hashVariable[0] == "showHorizontalGridLines") outputArray[5] = hashArray[i]
        if(hashVariable[0] == "showVerticalGridLines") outputArray[6] = hashArray[i]
        if(hashVariable[0] == "sortMapsBy") outputArray[7] = hashArray[i]
        if(hashVariable[0] == "showOfflineTimes") outputArray[8] = hashArray[i]
        if(hashVariable[0] == "showGameName") outputArray[9] = hashArray[i]
        if(hashVariable[0] == "showServerName") outputArray[10] = hashArray[i]
        if(hashVariable[0] == "showModName") outputArray[11] = hashArray[i]
        if(hashVariable[0] == "showGametype") outputArray[12] = hashArray[i]
        if(hashVariable[0] == "showGridLabels") outputArray[13] = hashArray[i]
    }

    for(i = 0; i < outputArray.length; i++)
    {
        if(checkValidInput(outputArray[i])) output.push(outputArray[i])
    }

    if(variableName == "URLStartTime")
    {
        outputArray[0] = "startTime=" + parseStartTime(value)
    }

    if(variableName == "URLEndTime")
    {
        outputArray[1] = "endTime=" + parseEndTime(value)
    }


    //Now, let's check to see if the server address/port is still the same
    newAddress = serverAddressField.value.trim()
    newPort = serverPortField.value.trim()

    let ipAndPort = window.location.href.split("#")[0]
    ipAndPort = ipAndPort.split("?")[1]

    ipAndPort = ipAndPort.split("&")

    for(i = 0; i < ipAndPort.length; i++)
    {
        ipPortSplit = ipAndPort[i].split("=")
        if(ipPortSplit[0] == "ip")
        {
            serverAddress = ipPortSplit[1].trim()
        }
        if(ipPortSplit[0] == "port")
        {
            serverPort = ipPortSplit[1].trim()
        }
    }

    if((newAddress != serverAddress || newPort != serverPort) && checkValidInput(newAddress) && checkValidInput(newPort))
    {
        //If this is different, we need to clear all the data arrays and ask for new data from the server
        clearDataArrays()

        serverAddress = newAddress
        serverPort = newPort
    }

    let webServerAddress = window.location.href.split("?")[0]

    document.location.href = webServerAddress + "?ip=" + serverAddress + "&port=" + serverPort + "#" + output.join("&")
}

function clearTimeouts()
{
    clearTimeout(startTimeTimer)
    clearTimeout(endTimeTimer)
    clearTimeout(serverAddressTimer)
}

function updateServerAddress()
{
    clearTimeouts()
    if(!loadingPage) serverAddressTimer = setTimeout("updateHash('', '')", changeTimeTimeout)
}

function updateStartTime(input1, input2)
{
    clearTimeouts()
    if(!loadingPage) startTimeTimer = setTimeout("updateHashFromControls(\"" + input1 + "\",\"" + input2 + "\")", changeTimeTimeout);
}

function updateEndTime(input1, input2)
{
    clearTimeouts()
    if(!loadingPage) endTimeTimer = setTimeout("updateHashFromControls(\"" + input1 + "\",\"" + input2 + "\")", changeTimeTimeout);
}

function updateHashFromControls(variableName, value)
{
    //If the page is still loading, do not continue.
    if(loadingPage == 1) return

    updateHash(variableName, value)
}

function parseHash()
{
    let hashValue = document.location.hash
    hashValue = hashValue.replace("#", "")

    hashArray = []
    if(hashValue.length > 0) hashArray = hashValue.split("&")
    for(i = 0; i < hashArray.length; i++)
    {

        hashVariable = hashArray[i].split("=")
        if(typeof(hashVariable[0]) === 'undefined' || hashVariable[0] == "" || hashVariable[0] == null) continue
        if(typeof(hashVariable[1]) === 'undefined' || hashVariable[1] == "" || hashVariable[1] == null) continue

        hashVariable[0] = hashVariable[0].trim()
        hashVariable[1] = hashVariable[1].trim()

        if(hashVariable[0] == "startTime")
        {
            startTime = parseInt(hashVariable[1])
            URLstartTime = parseInt(hashVariable[1])
        }
        if(hashVariable[0] == "endTime")
        {
            endTime = parseInt(hashVariable[1])
            URLendTime = parseInt(hashVariable[1])
        }
        if(hashVariable[0] == "twelveHourClockMode") twelveHourClockMode = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "displayTimesInUTC") displayTimesInUTC = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "colorizeBlocks") colorizeBlocks = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showHorizontalGridLines") showHorizontalGridLines = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showVerticalGridLines") showVerticalGridLines = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "sortMapsBy") sortMapsBy = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showOfflineTimes") showOfflineTimes = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showGameName") showGameName = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showServerName") showServerName = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showModName") showModName = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showGametype") showGametype = parseBoolean(hashVariable[1])
        if(hashVariable[0] == "showGridLabels") showGridLabels = parseBoolean(hashVariable[1])
    }

    checkStartAndEndTimes(startTime, endTime)
    
    loadingPage = 1
    updateHashFromControls()

    setStartTime(startTime)
    setEndTime(endTime)


    loadingPage = 0

}

function parseBoolean(input)
{
    if(typeof(input) === 'undefined' || input == null || input == "0" || input == 0 || input == false || input == "false")
    {
        return false
    }
    return true
}

function checkStartAndEndTimes(startInput, endInput)
{
    let currentDate = Math.floor(Date.now() / 1000)

    if(startInput > currentDate)
    {
        startInput = currentDate - 604800
    }
    if(endInput == 0)
    {
        endInput = currentDate
    }
    if(endInput > currentDate)
    {
        endInput = currentDate
    }
    if(startInput == 0)
    {
        startInput = endInput - 604800
    }
    startTime = startInput
    endTime = endInput

    timeDifference = endTime - startTime
}

function clearDataArrays()
{
    onlineStatusArray = []
    refreshTimesArray = []
    gameNameArray = []
    sv_hostnameArray = []
    mapnameArray = []
    modNameArray = []
    gametypeArray = []
    playerCountArray = []
    mapList = []
    mapListByTime = []
    playerCountByTime = []
    playersPerHour = []
    mapTime = []
    mapName = []
}

function displayErrorMessage(input)
{
    clear_element(analyticsDataFrame)

    addGridBackground()

    errorMessage = document.createElement('h3')
    errorMessage.className = "analyticsErrorMessage"
    errorMessage.appendChild(document.createTextNode(input))
    gridBackground.appendChild(errorMessage)

    unFreezeControls()
}

function getStartAndEndTimes()
{
    let output = [startTime, endTime]
    let currentDate = Math.floor(Date.now() / 1000)

    if(refreshTimesArray[0] - maxTimeDifference > endTime || refreshTimesArray[refreshTimesArray.length - 1] + maxTimeDifference < startTime)
    {
        //We're getting data from far away. Clear and start over.
        clearDataArrays()
        return output
    }
    else
    {
        //The time range checks out. One last check should ensure that the data stays contiguous.
        if(output[1] > refreshTimesArray[refreshTimesArray.length - 1] && output[0] >= refreshTimesArray[0] && output[0] < refreshTimesArray[refreshTimesArray.length - 1] + maxTimeDifference)
        {
            output[0] = refreshTimesArray[refreshTimesArray.length - 1]
        }
        if(output[0] < refreshTimesArray[0] && output[1] < refreshTimesArray[refreshTimesArray.length - 1] && output[1] >= refreshTimesArray[0] - maxTimeDifference)
        {
            output[1] = refreshTimesArray[0]
        }
    }

    if(output[1] > currentDate) output[1] = currentDate
    if(output[1] > endTime) output[0] = output[1]

    return output
}

function loadAnalyticsData()
{
    //Set up the start and end times
    parseHash()

    let timesReturn = getStartAndEndTimes()

    let loadStartTime = timesReturn[0]
    let loadEndTime = timesReturn[1]

    //Check to see if the end time is greater than the current time, and correct it
    if(loadEndTime > Math.floor(Date.now() / 1000))
    {
        if(loadStartTime > Math.floor(Date.now() / 1000))
        {
            //Check to see if both times are in the future, and display a message
            displayErrorMessage("Start and end dates cannot both be in the future!")
            return
        }
        else
        {
            loadEndTime = Math.floor(Date.now() / 1000)
        }
    }

    //If we already have the info needed, input the info and terminate
    if(loadStartTime >= refreshTimesArray[0] && loadEndTime <= refreshTimesArray[refreshTimesArray.length - 1])
    {
        rasterizeData()
        inputAnalyticsData()
        return
    }

    //Empty this object, just in case things fail down the line
    analyticsData = {}

    //Restrict the start and end times to only the parts we are missing
    if(loadEndTime > refreshTimesArray[refreshTimesArray.length - 1] && loadStartTime >= refreshTimesArray[0]) loadStartTime = refreshTimesArray[refreshTimesArray.length - 1]
    if(loadStartTime < refreshTimesArray[0] && loadEndTime < refreshTimesArray[refreshTimesArray.length - 1]) loadEndTime = refreshTimesArray[0]

    //If too much data is requested, cancel and display an error message
    if(timeDifference > maxTimeRequest)
    {
        displayErrorMessage("Too much time requested! Reduce request size and try again.")
        return
    }

    //Check to see if the start time is greater than the end time
    if(loadStartTime > loadEndTime)
    {
        displayErrorMessage("End time must be greater than start time!")
        return
    }

    showLoadingMessage()

    let reloadURL = window.location.href.split("?")[0]
    let ipAndPort = window.location.href.split("#")[0]
    ipAndPort = ipAndPort.split("?")[1]
    let ipPortSplit = []
    let ipPortReturn = []
    ipAndPort = ipAndPort.split("&")

    for(i = 0; i < ipAndPort.length; i++)
    {
        ipPortSplit = ipAndPort[i].split("=")
        if(ipPortSplit[0] == "ip")
        {
            ipPortReturn.push(ipAndPort[i])
        }
        if(ipPortSplit[0] == "port")
        {
            ipPortReturn.push(ipAndPort[i])
        }
    }

    reloadURL += "?" + ipPortReturn[0] + "&" + ipPortReturn[1] + "&"

    if(loadStartTime != 0)
    {
        reloadURL += "startTime=" + loadStartTime + "&"
    }
    if(loadEndTime != 0)
    {
        reloadURL += "endTime=" + loadEndTime + "&"
    }

    reloadURL += "JSONReload=1"

    let svjsonreq = new XMLHttpRequest()
	svjsonreq.open('GET', reloadURL, true);
	svjsonreq.onerror = ()=>{
	    clearTimeout(pageReloadTimer)
	    pageReloadTimer = setTimeout("loadAnalyticsData()", 4000);
	}

	svjsonreq.ontimeout = svjsonreq.onerror
	svjsonreq.onexception = svjsonreq.onerror
	svjsonreq.onload = ()=>{

        analyticsData = JSON.parse(svjsonreq.responseText)
        if(analyticsData == null && refreshTimesArray.length > 0 && (refreshTimesArray[0] - maxTimeDifference > endTime || refreshTimesArray[refreshTimesArray.length - 1] + maxTimeDifference < startTime))
        {
            displayErrorMessage("No data available!")
        }
        else
        {
            rasterizeData()
            inputAnalyticsData()
        }

	}

	svjsonreq.send()
}

function clear_element(e) {
    while (e.hasChildNodes()) {
      e.removeChild(e.lastChild);
    }
}

function updateDataArray(inputArray, inputDeltas)
{
    for(i = 0; i < refreshTimesArray.length; i++)
    {
        inputArray[refreshTimesArray[i]] = getDeltaState(inputDeltas, refreshTimesArray[i])
    }

    return inputArray
}

function getDeltaState(inputDeltas, time)
{
    var deltaValue = inputDeltas[0].value
    for(iDelta = 0; iDelta < inputDeltas.length; iDelta++)
    {
        if(typeof inputDeltas[iDelta] !== 'undefined' && inputDeltas[iDelta].date <= time) deltaValue = inputDeltas[iDelta].value
        if(typeof inputDeltas[iDelta + 1] !== 'undefined')
        {
            if(inputDeltas[iDelta + 1].date >= time) return deltaValue
        }
        else
        {
            return deltaValue
        }
    }
    return ""
}

function rasterizeTimeArrays()
{
    let averageTimeData = 0

    //We need to keep a running total of the refresh times as we go
    let runningTotal = analyticsData.refreshTimes[0]

    //We'll have to do the first one manually
    refreshTimesArray.push(runningTotal)
    onlineStatusArray[refreshTimesArray[0]] = getDeltaState(analyticsData.online, refreshTimesArray[0])

    for(i = 1; i < analyticsData.refreshTimes.length; i++)
    {
        if(!checkValidInput(analyticsData.refreshTimes[i])) continue

        //We have data, so let's add it.
        //First let's check to see if there's a chance of missing info.
        if(i > 2 && analyticsData.refreshTimes[i] > analyticsData.refreshTimes[i - 1] * 2.5)
        {
            //Possible missing info detected! Need to check and be sure.
            averageTimeData = (analyticsData.refreshTimes[i] + analyticsData.refreshTimes[i - 1] + analyticsData.refreshTimes[i - 2]) / 3
            if(averageTimeData * 2.5 < analyticsData.refreshTimes[i] && averageTimeData > 300)
            {
                //Definitely missing info here. ParaTracker must have been broken or offline.
                //Add an entry in onlineStatusArray for the offline time and continue.
                refreshTimesArray.push(runningTotal + averageTimeData)
                onlineStatusArray[runningTotal + averageTimeData] = false
            }
        }
        refreshTimesArray.push(runningTotal + analyticsData.refreshTimes[i])
        onlineStatusArray[refreshTimesArray[i]] = getDeltaState(analyticsData.online, refreshTimesArray[i])
        runningTotal += analyticsData.refreshTimes[i]
    }
    refreshTimesArray = sortAndRemoveDuplicates(refreshTimesArray)
}

function sortAndRemoveDuplicates(input)
{
    let output = []
    input.sort()

    output.push(input[0])

    for(g = 1; g < input.length; g++)
    {
        if(input[g - 1] != input[g])
        {
            output.push(input[g])
        }
    }

    return output
}

function rasterizeData()
{
    if(analyticsData == {} || analyticsData == null) return

    //This must be done BEFORE rasterization, or else it will need to use the times index.
    makeMapsLowerCase()

    //Due to the new way refresh times are sent, the online status array must be processed alongside the refresh times array
    rasterizeTimeArrays()

    //Update the arrays with the incoming info. All incoming data will be joined with existing data.
    gameNameArray = updateDataArray(gameNameArray, analyticsData.game)
    sv_hostnameArray = updateDataArray(sv_hostnameArray, analyticsData.hostname)
    mapnameArray = updateDataArray(mapnameArray, analyticsData.maps)
    modNameArray = updateDataArray(modNameArray, analyticsData.mod)
    gametypeArray = updateDataArray(gametypeArray, analyticsData.gametype)
    playerCountArray = updateDataArray(playerCountArray, analyticsData.playercount)

    //We need to create a new time array that consists of all refresh times between the start time and end time
    createCurrentTimeList()

    generateAdditionalMapInfo()

    if(!sortMapsBy)
    {
        sortMapsByPPMH()
    }
    else
    {
        sortMapsByTimePlayed()
    }
}

function findUniqueMapValues()
{
    newArray = []
    let timeKey = ""

    for(iCount = 0; iCount < currentTimeList.length; iCount++)
    {
        for(h = 0; h <= newArray.length; h++)
        {
            mapKey = mapnameKey(currentTimeList[iCount])
            if((h + 1 >= newArray.length && newArray[h] != mapKey) || newArray.length == 0)
            {
                newArray.push(mapKey)
                h = newArray.length
                continue
            }
            if(mapKey == newArray[h])
            {
                break
            }
        }
    }
    return newArray
}

function mapnameKey(input)
{
    //input must be the time value

    let output = ""

    output += gameNameArray[input]
    output += sv_hostnameArray[input]
    output += modNameArray[input]
    output += gametypeArray[input]

    output += mapnameArray[input]

    return output
}

function levelshotMapnameKey(input)
{
    //input must be the time value
    return gameNameArray[input] + mapnameArray[input]
}

function generateAdditionalMapInfo()
{
    //Empty this object so we can recalculate everything. 
    mapList = []
    mapListByTime = []
    mapTime = []
    playersPerHour = []

    mapList = findUniqueMapValues()

    for(j = 0; j < mapList.length; j++)
    {
        //This line initializes the array values to prevent NaN from occurring below
        mapTime[j] = 0
        playerCountByTime[j] = 0


        for(iSetup = 0; iSetup + 1 < currentTimeList.length; iSetup++)
        {
            if(mapnameKey([currentTimeList[iSetup]]) == mapList[j] && onlineStatusArray[currentTimeList[iSetup]])
            {
                mapTime[j] += currentTimeList[iSetup + 1] - currentTimeList[iSetup]
                playerCountByTime[j] += parseInt(playerCountArray[currentTimeList[iSetup]]) * (currentTimeList[iSetup + 1] - currentTimeList[iSetup])
            }

        }
    }

    mapListByTime = mapList

    for(j = 0; j < mapList.length; j++)
    {
        //Now we need to calculate the number of players per hour for each map
        //mapTime is a number of seconds, and playerCountByTime is the number of players multiplied by the number of seconds played
        playersPerHour[j] = 3600 / mapTime[j] * (playerCountByTime[j] / 3600)
        if(isNaN(playersPerHour[j])) playersPerHour[j] = 0
    }

}

function sortMapsByPPMH()
{
    let tempSwap = ""

    //I'm sure objects are a better way to do this but I'm a noob and I'm hungry and I'm tired and.....
    for(j = 0; j < mapListByTime.length - 1; j++)
    {
        for(k = 0; k < mapListByTime.length - 1; k++)
        {
            if(playersPerHour[k] > playersPerHour[k + 1])
            {
                tempSwap = mapListByTime[k]
                mapListByTime[k] = mapListByTime[k + 1]
                mapListByTime[k + 1] = tempSwap

                tempSwap = mapTime[k]
                mapTime[k] = mapTime[k + 1]
                mapTime[k + 1] = tempSwap

                tempSwap = playerCountByTime[k]
                playerCountByTime[k] = playerCountByTime[k + 1]
                playerCountByTime[k + 1] = tempSwap

                tempSwap = playersPerHour[k]
                playersPerHour[k] = playersPerHour[k + 1]
                playersPerHour[k + 1] = tempSwap
            }
        }
    }
}

function sortMapsByTimePlayed()
{
    let tempSwap = ""

    //I'm sure objects are a better way to do this but I'm a noob and I'm hungry and I'm tired and.....
    for(j = 0; j < mapListByTime.length - 1; j++)
    {
        for(k = 0; k < mapListByTime.length - 1; k++)
        {
            if(mapTime[k] > mapTime[k + 1])
            {
                tempSwap = mapListByTime[k]
                mapListByTime[k] = mapListByTime[k + 1]
                mapListByTime[k + 1] = tempSwap

                tempSwap = mapTime[k]
                mapTime[k] = mapTime[k + 1]
                mapTime[k + 1] = tempSwap

                tempSwap = playerCountByTime[k]
                playerCountByTime[k] = playerCountByTime[k + 1]
                playerCountByTime[k + 1] = tempSwap

                tempSwap = playersPerHour[k]
                playersPerHour[k] = playersPerHour[k + 1]
                playersPerHour[k + 1] = tempSwap
            }
        }
    }
}

function makeMapsLowerCase()
{
    for(i = 0; i < analyticsData.maps.length; i++)
    {
        if(typeof(analyticsData.maps[i].value) !== 'undefined' && analyticsData.maps[i].value != null) analyticsData.maps[i].value = analyticsData.maps[i].value.toLowerCase()
    }
}

function inputAnalyticsData()
{
    clear_element(analyticsDataFrame)

    if(startTime > endTime)
    {
        displayErrorMessage("Invalid start and end times!")
        return
    }

    if(analyticsData == null && refreshTimesArray.length > 0 && (refreshTimesArray[0] - maxTimeDifference > endTime || refreshTimesArray[refreshTimesArray.length - 1] + maxTimeDifference < startTime))
    {
        displayErrorMessage("No data available! Try again later.")
        return
    }
    
    populateAnalyticsField()
    unFreezeControls()
}

function createCurrentTimeList()
{
    currentTimeList = []
    count = refreshTimesArray.length

    for(i = 0; refreshTimesArray[i] <= endTime && i <= count; i++)
    {
        if(refreshTimesArray[i] < startTime) continue
        currentTimeList.push(refreshTimesArray[i])
    }
}

function addPlayersToGrid()
{
    maxValue = findMaxValue(playerCountArray)
    createHorizontalGridLines(maxValue)
    for(iGrid = 0; iGrid < currentTimeList.length; iGrid++)
    {
        //If the server was offline, we need to skip the frame since there will be no data
        if(!onlineStatusArray[currentTimeList[iGrid]]) continue

        dataElement = document.createElement('div')
        dataElement.className = "gridNode analyticsColor7"
        //Add the value as a title so that a mouse over will show the data.
        dateValue = new Date(currentTimeList[iGrid] * 1000)

        let leftPosition = 100 / timeDifference * (currentTimeList[iGrid] - startTime)
        let topPosition = Math.abs(100 - (100 / maxValue * playerCountArray[currentTimeList[iGrid]]))

        dataElement.style.cssText = "top:" + topPosition  + "%;left:" + leftPosition  + "%;"

        let playerCountAtTime = playerCountArray[currentTimeList[iGrid]]
        let currentPlayerTime = currentTimeList[iGrid]
        let currentMap = mapnameArray[currentTimeList[iGrid]]
        let currentGametype = gametypeArray[currentTimeList[iGrid]]
        dataElement.onclick = ()=>{displayInfo('Players', playerCountAtTime, currentPlayerTime, '', currentMap, currentGametype, '', '', '')}

        analyticsDataFrame.appendChild(dataElement)
    }

}

function addBlocksToGrid(input, color, inputTitle, height)
{
    let styleText = ""
    let leftPosition = 0
    height = parseInt(height)

    for(iGrid = 0; iGrid < currentTimeList.length; iGrid++)
    {
        //If the server was offline, we need to skip the frame since there will be no data.
        if(!onlineStatusArray[currentTimeList[iGrid]]) continue

        dataElement = document.createElement('div')
        dataElement.className = "gridBlockPoint " + color
        //Add the value as a title so that a mouse over will show the data.
        dateValue = new Date(currentTimeList[iGrid] * 1000)

        leftPosition = 100 / timeDifference * (currentTimeList[iGrid] - startTime)

        let displayHeight = height
        let topPosition = 100 - displayHeight - offset
        let width = 0

        let blockStart = currentTimeList[iGrid]

        while(iGrid + 1 < currentTimeList.length && input[currentTimeList[iGrid]] == input[currentTimeList[iGrid + 1]] && onlineStatusArray[currentTimeList[iGrid]] == onlineStatusArray[currentTimeList[iGrid + 1]])
        {
            iGrid++
            width++
        }

        let blockEnd = 0
        if(checkValidInput(currentTimeList[iGrid + 1]))
        {
            width = 100 / timeDifference * (currentTimeList[iGrid + 1] - startTime) - leftPosition
            //The end of the block must be no more than 1 second before the next block
            blockEnd = currentTimeList[iGrid + 1] - 1
        }
        else
        {
            width = 100 - leftPosition
            //If we are at the end of the array, endTime is our number
    	    blockEnd = endTime
        }

        styleText = "top:" + topPosition  + "%; left:" + leftPosition  + "%; height: " + displayHeight + "%; width: " + width + "%;"

        if(colorizeBlocks)
        {
            styleText += generateHSLForBlock(input, currentTimeList[iGrid])
        }

        dataElement.style.cssText = styleText

        let blockValue = input[currentTimeList[iGrid]]
        dataElement.onclick = ()=>{displayInfo(inputTitle, '', blockStart, blockEnd, blockValue, '', '', '', '')}

        analyticsDataFrame.appendChild(dataElement)
    }

    incrementOffset(height)
}
function addOfflineStatusToGrid()
{
    let styleText = ""
    let leftPosition = 0
    let title = ""
    height = 100
    if(!showOfflineTimes) return

    for(iGrid = 0; iGrid < currentTimeList.length; iGrid++)
    {
        if(onlineStatusArray[currentTimeList[iGrid]]) continue
        if(!checkValidInput(currentTimeList[iGrid + 1])) break
        dataElement = document.createElement('div')
        dataElement.className = "gridBlockPoint analyticsColor1"
        if(showOfflineTimes) dataElement.className += " offlineBlinker"
        //Add the value as a title so that a mouse over will show the data.
        dateValue = new Date(currentTimeList[iGrid] * 1000)

        leftPosition = 100 / timeDifference * (currentTimeList[iGrid] - startTime)

        let displayHeight = height
        let topPosition = 100 - displayHeight - offset

        let blockStart = currentTimeList[iGrid]

        let width = 0
        while(iGrid + 1 < currentTimeList.length && onlineStatusArray[currentTimeList[iGrid]] == onlineStatusArray[currentTimeList[iGrid + 1]])
        {
            iGrid++
            width++
        }

        let blockEnd = 0
        if(checkValidInput(currentTimeList[iGrid + 1]))
        {
            width = 100 / timeDifference * (currentTimeList[iGrid + 1] - startTime) - leftPosition
            //The end of the block must be no more than 1 second before the next block
            blockEnd = currentTimeList[iGrid + 1] - 1
        }
        else
        {
            width = 100 - leftPosition
            //If we are at the end of the array, endTime is our number
    	    blockEnd = endTime
        }

        styleText = "top:" + topPosition  + "%; left:" + leftPosition  + "%; height: " + displayHeight + "%; width: " + width + "%;"
            
        dataElement.style.cssText = styleText

        dataElement.onclick = ()=>{displayInfo('Server status', 'Offline', blockStart, blockEnd, '', '', '', '', '')}

        analyticsDataFrame.appendChild(dataElement)
    }

}

function addMapsToGrid(height)
{
    let color = "analyticsColor5"
    let title = "Map"
    let styleText = ""
    let mapData = ""
    //minHeight is used to calculate the minimum allowed height, and compensate for it.
    minHeight = 7

    for(iGrid = 0; iGrid < currentTimeList.length; iGrid++)
    {
        let displayHeight = 0
        let leftPosition = 0

        //If the server was offline, we need to skip the frame since there will be no data
        if(!onlineStatusArray[currentTimeList[iGrid]]) continue

        dataElement = document.createElement('div')
        dataElement.className = "gridBlockPoint " + color
        //Add the value as a title so that a mouse over will show the data.
        dateValue = new Date(currentTimeList[iGrid] * 1000)

        let playersPerMapHour = playersPerHour[findValueInArray(mapnameKey(currentTimeList[iGrid]), mapListByTime)].toFixed(2) + " players per map-hour"
        let timePlayed = (mapTime[findValueInArray(mapnameKey(currentTimeList[iGrid]), mapListByTime)] / 3600).toFixed(2) + " total hours played"

        if(!sortMapsBy)
        {
            mapData = ": " + mapnameArray[currentTimeList[iGrid]] + " - " + gametypeArray[currentTimeList[iGrid]] + " - " + playersPerMapHour + timePlayed
        }
        else
        {
            mapData = ": " + mapnameArray[currentTimeList[iGrid]] + " - " + gametypeArray[currentTimeList[iGrid]] + " - " + timePlayed + playersPerMapHour
        }

        leftPosition = 100 / timeDifference * (currentTimeList[iGrid] - startTime)

        if(!sortMapsBy)
        {
            //Since playersPerHour is sorted, the highest value is at the last index value.
            //minHeight is used to calculate the minimum allowed height, and compensate for it.
            displayHeight = (100 - minHeight - offset) * (playersPerHour[findValueInArray(mapnameKey(currentTimeList[iGrid]), mapListByTime)] / playersPerHour[playersPerHour.length - 1]) + minHeight
        }
        else
        {
            //Since mapTime is sorted, the highest value is at the last index value.
            //minHeight is used to calculate the minimum allowed height, and compensate for it.
            displayHeight = (100 - minHeight - offset) * (mapTime[findValueInArray(mapnameKey(currentTimeList[iGrid]), mapListByTime)] / mapTime[mapTime.length - 1]) + minHeight
        }

        //If players are 0 or mapTime is 0, this ends up being NaN. Too tired to find and fix the cause...
        if(isNaN(displayHeight)) displayHeight = 100

        let topPosition = 100 - displayHeight - offset
        let width = 0

        let blockStart = currentTimeList[iGrid]

        while(iGrid + 1 < currentTimeList.length && mapnameKey(currentTimeList[iGrid]) == mapnameKey(currentTimeList[iGrid + 1]) && onlineStatusArray[currentTimeList[iGrid]] == onlineStatusArray[currentTimeList[iGrid + 1]])
        {
            iGrid++
            width++
        }

        let blockEnd = 0
        if(checkValidInput(currentTimeList[iGrid + 1]))
        {
            width = 100 / timeDifference * (currentTimeList[iGrid + 1] - startTime) - leftPosition
            //The end of the block must be no more than 1 second before the next block
            blockEnd = currentTimeList[iGrid + 1] - 1
        }
        else
        {
            width = 100 - leftPosition
            //If we are at the end of the array, endTime is our number
    	    blockEnd = endTime
        }

        styleText = "top:" + topPosition  + "%; left:" + leftPosition  + "%; height: " + displayHeight + "%; width: " + width + "%;"

        let colorGenerator = ""
        if(colorizeBlocks)
        {
            colorGenerator = generateHSLForBlock(mapnameArray, currentTimeList[iGrid])
        }

        styleText += colorGenerator

        dataElement.style.cssText = styleText


        let mapValue = mapnameArray[currentTimeList[iGrid]]
        let gametypeValue = "Gametype: " + gametypeArray[currentTimeList[iGrid]]
        let timeValue = currentTimeList[iGrid]
        dataElement.onclick = ()=>{displayInfo('Map', mapValue, blockStart, blockEnd, gametypeValue, timePlayed, playersPerMapHour, colorGenerator, timeValue)}
        analyticsDataFrame.appendChild(dataElement)
    }
}

function generateHSLForLevelshot(inputArray, inputTime)
{
    //input must be the time value we're checking

    //Use Cagelight's color generator function for this
    let hue = inputArray[inputTime].hashCode(inputArray[inputTime]) % 360
    let saturation = inputArray[inputTime].hashCode(inputArray[inputTime]) % 50
    let lightness = inputArray[inputTime].hashCode(inputArray[inputTime]) % 40
    return " background-color: hsl(" + hue + ", " + .45 * (50 + saturation) + "%, " + .45 * (40 + lightness) + "%) "
}

function generateHSLForBlock(inputArray, inputTime)
{
    //input must be the time value we're checking

    //Use Cagelight's color generator function for this
    let hue = inputArray[inputTime].hashCode(inputArray[inputTime]) % 360
    let saturation = inputArray[inputTime].hashCode(inputArray[inputTime]) % 50
    let lightness = inputArray[inputTime].hashCode(inputArray[inputTime]) % 30
    return " background-color: hsl(" + hue + ", " + (50 + saturation) + "%, " + (40 + lightness) + "%) "
}

function showLoadingMessage()
{
    loadingDiv.className = "loadingDiv"
}

function hideLoadingMessage()
{
    loadingDiv.className = "loadingDiv hiddenStuff"
}

function addLoadingMessage()
{
    clear_element(loadingDiv)
    loadingDiv.appendChild(document.createTextNode("Loading..."))
    loadingDiv.className = "loadingDiv hiddenStuff"
    loadingDiv.id = "loadingDiv"
    analyticsDataFrame.appendChild(loadingDiv)
}

function findLevelshot(input, element)
{
    //Input needs to be the time value of the point in question
    let output = ""

    let mapKey = levelshotMapnameKey(input)

    let levelshotTest = ""

    //I'm a noob with objects.....here we go with more arrays
    for(c = 0; c < levelshotsArray.length; c++)
    {
        levelshotTest = levelshotsArray[c].split("url(\"")[0]
        if(levelshotTest == mapKey)
        {
            return levelshotsArray[c].substring(mapKey.length)
        }
    }

    //Couldn't find the levelshot, so load a new one.
    loadLevelshot(input, element)
}

function loadLevelshot(input, element)
{
    let output = ""
    let gameNameAtTime = gameNameArray[input]
    let mapnameAtTime = mapnameArray[input]

    let currentURL = window.location.href.split("Analytics.php")[0]
    
    let levelshotURL = encodeURI(currentURL + "LevelshotFinder.php?gameName=" + gameNameAtTime + "&mapName=" + mapnameAtTime)

    let svjsonreq = new XMLHttpRequest()
	svjsonreq.open('GET', levelshotURL, true);
	svjsonreq.onerror = ()=>{
	}

	svjsonreq.ontimeout = svjsonreq.onerror
	svjsonreq.onexception = svjsonreq.onerror
	svjsonreq.onload = ()=>{

    newLevelshot = 'url("../' + svjsonreq.responseText.split(':#:')[1] + '")'
    let mapKey = levelshotMapnameKey(input)
    levelshotsArray.push(mapKey + newLevelshot)
    element.style.backgroundImage = newLevelshot
	}
	svjsonreq.send()
}

function addInfoDiv()
{
    let infoDiv = document.createElement('div')
    infoDiv.id = "infoDiv"
    infoDiv.className = "hiddenStuff"
    analyticsDataFrame.appendChild(infoDiv)
}

function displayInfo(propertyName, propertyValue, blockStart, blockEnd, info1, info2, info3, colorGenerator, timeValue)
{
    //If we are here, forcefully hide the checkboxes
    hiddenCheckboxes.className = "hiddenStuff"

    let tempStartDate = new Date(blockStart * 1000)
    let tempEndDate = new Date(blockEnd * 1000)

    if(displayTimesInUTC)
    {
        if(checkValidInput(blockStart)) startDate = tempStartDate.toUTCString()
        if(checkValidInput(blockEnd)) endDate = tempEndDate.toUTCString()
    }
    else
    {
        if(checkValidInput(blockStart)) startDate = tempStartDate.toString()
        if(checkValidInput(blockEnd)) endDate = tempEndDate.toString()
    }

    clear_element(infoDiv)

    if(checkValidInput(propertyName) || checkValidInput(propertyValue))
    {
        let headerStuff = document.createElement("span")
        headerStuff.appendChild(document.createTextNode(propertyName + ": "))
        headerStuff.appendChild(colorize(propertyValue))
        headerStuff.className = "infoDivHeader"
        infoDiv.appendChild(headerStuff)
        infoDiv.appendChild(document.createElement("br"))
    }

    if(propertyName == "Map")
    {
        //If we are showing a map, we need to show a levelshot and the background color

        infoDiv.appendChild(document.createElement("br"))

        let levelshotContainer = document.createElement("div")
        levelshotContainer.className = "infoDivLevelshotContainer"
        if(colorizeBlocks)
        {
            levelshotContainer.style.cssText = generateHSLForLevelshot(mapnameArray, timeValue)
        }
        else
        {
            levelshotContainer.style.cssText = "background-color: #555"
        }

        let levelshotImage = document.createElement("div")
        levelshotImage.className = "infoDivLevelshot"
        levelshotImage.style.backgroundImage = findLevelshot(timeValue, levelshotImage)

        levelshotContainer.appendChild(levelshotImage)
        infoDiv.appendChild(levelshotContainer)
    }


    if(checkValidInput(info1))
    {
        infoDiv.appendChild(document.createElement("br"))
        let info1Element = document.createElement("span")
        info1Element.className = "infoDivInfo"
        info1Element.appendChild(colorize(info1))
        infoDiv.appendChild(info1Element)
    }

    if(checkValidInput(info2))
    {
        infoDiv.appendChild(document.createElement("br"))
        let info2Element = document.createElement("span")
        info2Element.className = "infoDivInfo"
        info2Element.appendChild(colorize(info2))
        infoDiv.appendChild(info2Element)
    }

    if(checkValidInput(info3))
    {
        infoDiv.appendChild(document.createElement("br"))
        let info3Element = document.createElement("span")
        info3Element.className = "infoDivInfo"
        info3Element.appendChild(colorize(info3))
        infoDiv.appendChild(info3Element)
    }

    if(checkValidInput(blockStart))
    {
        infoDiv.appendChild(document.createElement("br"))

        //Before adding the start and end times, let's add a count of the total number of hours
        if(checkValidInput(blockEnd) && blockEnd != blockStart)
        {
            infoDiv.appendChild(document.createElement("br"))
            let timeLapseElement = document.createElement("span")
            timeLapseElement.className = "infoDivInfo"
            timeLapseElement.appendChild(document.createTextNode(((blockEnd - blockStart) / 3600).toFixed(2) + " hours"))
            infoDiv.appendChild(timeLapseElement)
        }

        infoDiv.appendChild(document.createElement("br"))
        let startDateBlock = document.createElement("span")
        startDateBlock.className = "infoDivDate"
        startDateBlock.appendChild(document.createTextNode(startDate))
        infoDiv.appendChild(startDateBlock)

        if(checkValidInput(blockEnd) && blockEnd != blockStart)
        {
            infoDiv.appendChild(document.createElement("br"))
            infoDiv.appendChild(document.createTextNode("Through"))
            infoDiv.appendChild(document.createElement("br"))
            let startDateBlock = document.createElement("span")
            startDateBlock.className = "infoDivDate"
            startDateBlock.appendChild(document.createTextNode(endDate))
            infoDiv.appendChild(startDateBlock)
        }
    }

    infoDiv.appendChild(document.createElement("br"))
    infoDiv.appendChild(document.createElement("br"))

    let closeButton = document.createElement("div")
    closeButton.className = "infoDivCloseButton"
    closeButton.onclick = ()=>{hideInfoDiv()}
    closeButton.appendChild(document.createTextNode("Close"))

    let closeButtonX = document.createElement("span")
    closeButtonX.className = "infoDivCloseButtonX"
    closeButtonX.appendChild(document.createTextNode("X"))
    closeButtonX.onclick = ()=>{hideInfoDiv()}
    closeButton.appendChild(closeButtonX)

    infoDiv.appendChild(closeButton)


    infoDiv.className = ""

    //Now that the info has been added, position the element where it needs to be

    //Get the click coordinates
    let x = event.pageX
    let y = event.pageY

    //Remember: xOffset is doubled when dealing with the right side of the screen
    let xOffset = 20
    let yOffset = 40

    //Start with the dimensions of the grid
    let widthValue = analyticsDataFrame.scrollWidth
    let heightValue = analyticsDataFrame.scrollHeight

    let infoDivWidth = infoDiv.scrollWidth
    let infoDivHeight = infoDiv.scrollHeight

    //Set X coordinates to the center of the div
    let halfWidth = infoDiv.scrollWidth / 2

    if(x + halfWidth > widthValue - 2 * xOffset)
    {
        //We're off the right side of the screen. Calculate to 2 * xOffset pixels from the right.
        x = widthValue - infoDivWidth - 2 * xOffset
    }
    else if(x - halfWidth - xOffset < 0)
    {
        //We're off the left side of the screen. Set the X point to the offset value.
        x = xOffset
    }
    else
    {
        //We're in the clear. Center the element.
        x = x - halfWidth
    }

    if(y + infoDivHeight + yOffset * 2 > heightValue)
    {
            if(y - infoDivHeight - yOffset * 2 > 0)
            {
                //We're off the bottom of the screen, and there's room up above. Display there.
                y = y - infoDivHeight - yOffset
            }
            else
            {
                //We're off the bottom, but there is no room up above. Display at the top.
                y = yOffset
            }
    }
    else
    {
        //There is room below the mouse for the info div. Add the offset value.
        y = y + yOffset
    }

    //Convert these to a percentage so the DIV will move when the window is resized
    x = 100 / widthValue * x
    y = 100 / heightValue * y

    infoDiv.style.left = x + "%"
    infoDiv.style.top = y + "%"
}

function hideInfoDiv()
{
    //If we are here, forcefully hide the checkboxes
    hiddenCheckboxes.className = "hiddenStuff"
    infoDiv.className = "hiddenStuff"
}

function checkValidInput(input)
{
    if(typeof(input) === 'undefined' || input == null || input == "") return false
    return true
}

function findUniqueValues(input)
{
    var newArray = []
    for(iCount = 0; iCount < currentTimeList.length; iCount++)
    {
        for(h = 0; h <= newArray.length; h++)
        {
            if((h + 1 >= newArray.length && newArray[h] != input[currentTimeList[iCount]]) || newArray.length == 0)
            {
                newArray.push(input[currentTimeList[iCount]])
                h = newArray.length
                continue
            }
            if(input[currentTimeList[iCount]] == newArray[h])
            {
                break
            }
        }
    }
    return newArray
}

function findValueInArray(input, arrayInput)
{
    for(k = 0; k < arrayInput.length; k++)
    {
        if(arrayInput[k] == input) return k
    }
    return false
}

function findMaxValue(input)
{
    var maxValue = 0
    for(iMax = 0; iMax < currentTimeList.length; iMax++)
    {
        if(input[currentTimeList[iMax]] > maxValue)
        {
            maxValue = parseInt(input[currentTimeList[iMax]])
        }
    }
    if(isNaN(maxValue))
    {
        maxValue = 0
    }
    return maxValue
}

function getTimeStepValue(input)
{
    // Step is the value skipped between each vertical grid line
    // 3600 means one line per hour of data
    // 7200 means one line per two hours of data
    // 43200 means one line per 12 hours of data
    // 86400 means one line per day of data
    // 1209600 means one line per 1 week of data
    // 2419200 means one line per 2 weeks of data
    // 4838400 means one line per 4 weeks of data
    // 9676800 means one line per 8 weeks of data
    // 19353600 means one line per 16 weeks of data
    // 38707200 means one line per 32 weeks of data
    // 31557600 means one line per 1 astronomical year of data (365.25 days)
    // 63115200 means one line per 2 astronomical years of data (365.25 days)
    // 157788000 means one line per 5 astronomical years of data (365.25 days)

    let stepTest = 3600

    if(input / stepTest < 12) return stepTest
    stepTest = 7200
    if(input / stepTest < 12) return stepTest
    stepTest = 43200
    if(input / stepTest < 20) return stepTest
    stepTest = 86400
    if(input / stepTest < 20) return stepTest
    stepTest = 1209600
    if(input / stepTest < 20) return stepTest
    stepTest = 2419200
    if(input / stepTest < 20) return stepTest
    stepTest = 4838400
    if(input / stepTest < 20) return stepTest
    stepTest = 9676800
    if(input / stepTest < 20) return stepTest
    stepTest = 19353600
    if(input / stepTest < 20) return stepTest
    stepTest = 38707200
    if(input / stepTest < 20) return stepTest
    stepTest = 38707200
    if(input / stepTest < 20) return stepTest
    stepTest = 63115200
    if(input / stepTest < 20) return stepTest
    stepTest = 157788000
    return stepTest
}

function createVerticalGridLines(timeDifference)
{
    verticalLineCounter = 1

    let step = getTimeStepValue(timeDifference)

    //If we are set to use local time, adjust the lines to match
    timezoneOffset = subtractTimezoneOffset()

    //This line sets the start point of the vertical lines to align with the step value
    let roundedStartTime = Math.floor(startTime / step) * step - step + timezoneOffset

    //Turn this info into grid lines, spaced "step" apart
    for(i = roundedStartTime; i <= endTime; i += step)
    {
            gridElement = document.createElement('div')
            gridElement.className = "gridLineVertical"

            let dateValue = new Date(i * 1000 + addTimezoneOffset())
            let dateValueString = dateValue.toString()

            gridElement.onclick = ()=>{displayInfo('', '', '', '', dateValueString, '', '', '', '')}

            leftPosition = (100 / timeDifference) * (i - startTime) + "%;"
            gridElement.style.cssText = "top: 0%; left: " + leftPosition
        if(showVerticalGridLines)
        {
            gridElement.onclick = ()=>{displayInfo('', '', '', '', dateValue.toString(), '', '', '', '')}
            if(displayTimesInUTC) gridElement.onclick = ()=>{displayInfo('', '', '',  '', dateValue.toUTCString(), '', '', '', '')}
            analyticsDataFrame.appendChild(gridElement)
            if(showGridLabels) addVerticalGridLabel(dateValue, leftPosition, step)
        }
    }
}

function createHorizontalGridLines(maxValue)
{
    //Step is the percentage skipped between each horizontal grid line
    let step = 1
    if(maxValue / step > 15) step = 2
    if(maxValue / step > 15) step = 5
    if(maxValue / step > 15) step = 10
    if(maxValue / step > 15) step = 15
    if(maxValue / step > 15) step = 20
    if(maxValue / step > 15) step = 30
    if(maxValue / step > 15) step = 40
    if(maxValue / step > 15) step = 50
    if(maxValue / step > 15) step = 75
    if(maxValue / step > 15) step = 100

    //Find out how many horizontal lines we'll have total
    maxLines = Math.floor(maxValue / step)

    for(i = 0; i <= maxValue; i += step)
    {
            gridElement = document.createElement('div')
            gridElement.className = "gridLineHorizontal"
            let elementTitle = i + " Player"
            if(i != 1) elementTitle += "s"
            positionText = "left: 0%; top: " + Math.abs(100 - (100 / maxValue * i)) + "%;"
            gridElement.style.cssText = positionText

        if(showHorizontalGridLines)
        {
            gridElement.onclick = ()=>{displayInfo('', '', '', '', elementTitle, '', '', '', '')}
            analyticsDataFrame.appendChild(gridElement)
            if(showGridLabels) addHorizontalGridLabel(i)
        }

    }
    //Force the final value to the top for readability
    positionText = "left: 0%; top: 0%;"
    if(showGridLabels && showHorizontalGridLines) addHorizontalGridLabel(maxValue)
}

function addHorizontalGridLabel(verticalValue)
{
    gridLabel = document.createElement('div')
    if(verticalValue == 0)
    {
        gridLabel.className = "gridLabelHorizontal gridLabelZero"
    }
    else if(verticalValue == maxValue)
    {
        gridLabel.className = "gridLabelHorizontal gridLabelTop"
    }
    else
    {
        gridLabel.className = "gridLabelHorizontal"
    }

    let elementTitle = verticalValue + " Player"
    if(verticalValue != 1) elementTitle += "s"
    gridLabel.onclick = ()=>{displayInfo('', '', '', '', elementTitle, '', '', '', '')}

    gridLabel.style.cssText = positionText
    gridLabel.appendChild(document.createTextNode(verticalValue))
    analyticsDataFrame.appendChild(gridLabel)
}

function addVerticalGridLabel(dateValue, leftPosition, step)
{
    gridLabel = document.createElement('div')
    gridLabel.className = "gridLabelVertical gridLabelVerticalOffset" + verticalLineCounter

    if(verticalLineCounter == 1)
    {
        verticalLineCounter = 2
    }
    else
    {
        verticalLineCounter = 1
    }

    let dateValueString = dateValue.toString()
    if(displayTimesInUTC) dateValueString = dateValue.toUTCString()
    gridLabel.onclick = ()=>{displayInfo('', '', '', '', dateValueString, '', '', '', '')}
    gridLabel.style.cssText = "top: 100%; left: " + leftPosition

    if(step <= 43200)
    {
        gridLabel.appendChild(document.createTextNode(getDayAndHours(dateValue, 1)))
    }
    else if(step <= 2419200)
    {
        gridLabel.appendChild(document.createTextNode(getWeekday(dateValue)))
    }
    else if(step <= 38707200)
    {
        gridLabel.appendChild(document.createTextNode(getMonth(dateValue)))
    }
    else
    {
        gridLabel.appendChild(document.createTextNode(getYear(dateValue)))
    }
    analyticsDataFrame.appendChild(gridLabel)
}

function getDayAndHours(input, shortMonth)
{
    let hourValue = input.getHours()
    if(displayTimesInUTC) hourValue = input.getUTCHours()

    if(typeof(shortMonth) == 'undefined' || shortMonth == "" || parseInt(shortMonth) < 0 || parseInt(shortMonth) > 1)
    {
        shortMonth = 0
    }
    if(twelveHourClockMode)
    {
        let PMorAM = ""
        if(hourValue < 12 || hourValue == 24)
        {
            PMorAM = "AM"
        }
        else
        {
            PMorAM = "PM"
        }

        if(hourValue > 12)
        {
            hourValue = hourValue - 12 + ":00 " + PMorAM
        }
        else if(hourValue == 0)
        {
            hourValue = "12:00 " + PMorAM
        }
        else
        {
            hourValue = hourValue + ":00 " + PMorAM
        }
    }
    else
    {
        hourValue = hourValue + ":00"
    }

    let monthValue = ""
    if(shortMonth)
    {
        monthValue = months_short[input.getMonth()]
    }
    else
    {
        monthValue = months[input.getMonth()]
    }
    let dateNumber = input.getDate()

    if(displayTimesInUTC)
    {
        if(shortMonth)
        {
            monthValue = months_short[input.getUTCMonth()]
        }
        else
        {
            monthValue = months[input.getUTCMonth()]
        }
        dateNumber = input.getUTCDate()
    }

    let output = hourValue + ", " + monthValue + " " + dateNumber
    return output
}

function getWeekday(input, mode)
{
    let output = ""
    if(typeof(mode) == 'undefined' || mode == "" || parseInt(mode) < 0 || parseInt(mode) > 1)
    {
        mode = 0
    }
    if(mode)
    {
        weekdayValue = weekdays[input.getDay()]
        if(displayTimesInUTC)
        {
            weekdayValue = weekdays[input.getUTCDay()]
        }
    }
    else
    {
        weekdayValue = weekdays_short[input.getDay()]
        if(displayTimesInUTC)
        {
            weekdayValue = weekdays_short[input.getUTCDay()]
        }
    }

    let monthValue = months[input.getMonth()]
    let dateNumber = input.getDate()

    if(displayTimesInUTC)
    {
        monthValue = months[input.getUTCMonth()]
        dateNumber = input.getUTCDate()
    }

    output = weekdayValue + ", " + monthValue + " " + dateNumber
    return output
}

function getMonth(input)
{
    let output = ""

    let monthValue = months[input.getMonth()]

    yearValue = getYear(input)    

    output = monthValue + " " + yearValue

    return output
}

function getYear(input)
{
    let output = ""

    //dateValue must be increased here, or the astronomical year calculation will cause half of the year labels to be off by one
    input.setUTCDate(32)

    output = input.getFullYear()
    if(displayTimesInUTC) output = input.getUTCFullYear()

    return output
}

function addTimezoneDisclaimer()
{
    let timezoneDisclaimer = document.createElement('div')
    timezoneDisclaimer.className = "timezoneDisclaimer"

    if(displayTimesInUTC)
    {
        timezoneDisclaimer.appendChild(document.createTextNode("Times shown are in UTC."))
    }
    else
    {
        timezoneDisclaimer.appendChild(document.createTextNode("Times shown are in local time."))
    }
    analyticsDataFrame.appendChild(timezoneDisclaimer)
}

function addGridBackground(input)
{
    let gridBackground = document.createElement("div")
    gridBackground.id = "gridBackground"
    if(input == 1) gridBackground.onclick = ()=>{hideInfoDiv()}
    analyticsDataFrame.appendChild(gridBackground)
}

function populateAnalyticsField()
{
    //This value is the starting offset value
    offset = 1

    addLoadingMessage()

    addGridBackground(1)

    if(showOfflineTimes) addOfflineStatusToGrid()

    //The number at the end of each of the following function calls is the percentage height it will be
    //The offset value is used to determine the spacing between items

    if(showGameName) addBlocksToGrid(gameNameArray, "analyticsColor2", "Game Name", 7)
    if(showServerName) addBlocksToGrid(sv_hostnameArray, "analyticsColor4", "Server Name", 7)
    if(showModName) addBlocksToGrid(modNameArray, "analyticsColor3", "Mod Name", 7)
    if(showGametype) addBlocksToGrid(gametypeArray, "analyticsColor6", "Gametype", 7)

    addMapsToGrid(100)

//    addTimezoneDisclaimer()

    createVerticalGridLines(timeDifference)

    //The players function also adds the horizontal grid lines, because the spacing is based on the number of players
    addPlayersToGrid()

    addInfoDiv()
}

function incrementOffset(input)
{
    //This will add the current value to the offset, plus a 1% space for readability
    offset += input + 1
}

String.prototype.hashCode = function(seed = 42){
  var remainder, bytes, h1, h1b, c1, c1b, c2, c2b, k1, i;

	remainder = this.length & 3; // this.length % 4
	bytes = this.length - remainder;
	h1 = seed;
	c1 = 0xcc9e2d51;
	c2 = 0x1b873593;
	i = 0;

	while (i < bytes) {
	  	k1 =
	  	  ((this.charCodeAt(i) & 0xff)) |
	  	  ((this.charCodeAt(++i) & 0xff) << 8) |
	  	  ((this.charCodeAt(++i) & 0xff) << 16) |
	  	  ((this.charCodeAt(++i) & 0xff) << 24);
		++i;

		k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff;
		k1 = (k1 << 15) | (k1 >>> 17);
		k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff;

		h1 ^= k1;
        h1 = (h1 << 13) | (h1 >>> 19);
		h1b = ((((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16))) & 0xffffffff;
		h1 = (((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16));
	}

	k1 = 0;

	switch (remainder) {
		case 3: k1 ^= (this.charCodeAt(i + 2) & 0xff) << 16;
		case 2: k1 ^= (this.charCodeAt(i + 1) & 0xff) << 8;
		case 1: k1 ^= (this.charCodeAt(i) & 0xff);

		k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
		k1 = (k1 << 15) | (k1 >>> 17);
		k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
		h1 ^= k1;
	}

	h1 ^= this.length;

	h1 ^= h1 >>> 16;
	h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
	h1 ^= h1 >>> 13;
	h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
	h1 ^= h1 >>> 16;

	return h1 >>> 0;
}

function colorize(text, className = null, no_color_if_uncolored = false) {
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
      txtsp.className = 'infoDivColor' + txt.col
      txtsp.appendChild(document.createTextNode(txt.txt))
      cont.appendChild(txtsp)
    })
  }
	
	if (className) cont.className = className
	
	return cont
}
