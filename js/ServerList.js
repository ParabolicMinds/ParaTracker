/*
Existing bugs:

On a page re-render, the radio buttons should be changed to reflect the new setting. I'm pretty sure the only way
to fix this is to add them to the render function and re-render them every time, because changing them will trigger
another page render because of the onchange attribute

Sometimes, when you click a page to navigate to, the page kicks you right back to page 1...not sure what's up
with that

If you set the servers-per-page count to 10, go to the last page in the list, then increase the servers-per-page
count, the entire page list disappears. It's supposed to jump you back to the last available page....

There are several off-by-one errors in the page count, I"m positive...most notably, when going forward a page, the
last server on the previous page is the first server on the next, and it shouldn't be

*/ 



// This is the game that will be selected by default if the page is opened with none specified
// This is the game that will be selected by default if the page is opened with none specified
// If this is blank, all games will be shown
defaultGame = "Jedi Academy"
maxServersPerPage = 100
minServersPerPage = 10
defaultMaxCount = 40


// Declaring these here so they are in the global scope
filterList = []
currentGame = ""
trackerURL = ""
timer = ""


function changeHashData(inputObject)
{
	//Make sure there's no surprise timers running
	clearTimeout(timer);

	hashProperties = decodeURIComponent(document.location.hash)
	hashProperties = hashProperties.substr(1, hashProperties.length)
	hashProperties = hashProperties.split(";")
	if(!Array.isArray(hashProperties))
	{
		hashProperties = [hashProperties]
	}

	if(inputObject.game && inputObject.game.toLowerCase() != currentGame.toLowerCase())
	{
		currentGame = inputObject.game.toLowerCase()
		inputObject.startPage = 1
	}

	if(!inputObject.filter)
	{
		//The above can also evaluate to true if the string is "", so we have to check!
		if(inputObject.filter != "") inputObject.filter = document.getElementById('filterInput').value
	}

	inputValues = Object.entries(inputObject)
	for(let h = 0; h < inputValues.length; h++)
	{
		let property = inputValues[h][0]
		let value = inputValues[h][1]
		let match = false
		let temp = []
		let newHash = {}
		let test = ""

		if(typeof(property) === 'string')
		{
			property = property.toLowerCase()
		}
		else
		{
			property = property.toString()
		}
/*
		if(typeof(value) == 'string')
		{
			value = value.toLowerCase()
		}
		else
		{
			value = value.toString()
		}
*/
		for(let i = 0; i < hashProperties.length; i++)
		{
			temp = hashProperties[i].split("=")
			test = temp[0].toLowerCase()
			if(test == property && property !== "")
			{
				if(!match)
				{
					newHash[property] = value
					match = true
				}
			}
			else
			{
				newHash[test] = temp[1]
			}
		}

		if(!match) newHash[property] = value
		hashProperties = Object.entries(newHash)
		for(let i = 0; i < hashProperties.length; i++)
		{
			hashProperties[i] = hashProperties[i].join("=")
		}
	}
	window.location.hash = encodeURIComponent(hashProperties.join(";"))
}

function getHashData(property)
{
	let temp = []

	if(typeof(property) == 'string')
	{
		property = property.toLowerCase()
	}
	else
	{
		property = property.toString()
	}

	hashProperties = decodeURIComponent(document.location.hash)
	hashProperties = hashProperties.substr(1, hashProperties.length)
	hashProperties = hashProperties.split(";")
	if(!Array.isArray(hashProperties))
	{
		hashProperties = [hashProperties]
	}

	for(let i = 0; i < hashProperties.length; i++)
	{
		temp = hashProperties[i].split("=")
		if(temp[0].toLowerCase() == property)
		{
			return temp[1]
		}
	}
	return false
}

function changeServerListGame(input)
{
	changeHashData({"game": input})
}

function renderServerList()
{
	// Clear the page
	clear_element(headerDiv)
	clear_element(serverDiv)
	clear_element(footerDiv)

	//Any discrepancies here should have already been caught by changeHashData
	let filterInputText = getHashData("filter")
	if(filterInputText === false)
	{
		filterInputText = ""
	}
	else
	{
		document.getElementById('filterInput').value = filterInputText
	}

	// Before we render a server list, let's set the combo box to the right entry
	let matchValue = getHashData("game")
	if(matchValue === false) matchValue = defaultGame	//If there's no hash data, use the default game
	let test = getHashData("startPage")
	if(currentGame != matchValue && test !== false && test > 1)
	{
		//If we changed games and are not on page 1, force us back to page 1 so we don't risk getting an empty list
		currentGame = matchValue
		changeHashData({ "startPage": 1 })
		return
	}

	matchValue = matchValue.toLowerCase()
	let valueList = gameDropdown.children
	for(let i = 0; i < valueList.length; i++)
	{
		if(valueList[i].value.toLowerCase() == matchValue)
		{
			gameDropdown.children[i].selected = true
			break
		}
	}


	filterList = data.serverList
	let renderList = []
	let cullList = []


	// Sorting
	sortType = getHashData("sortType")
	if(sortType === false) sortType = "players"
	sortServerList(sortType)
	arrow = { elementType: "span", className: "serverListSortArrows" }
	if(parseBoolean(getHashData("sortAscending")))
	{
		arrow.text = " ↑"
	}
	else
	{
		arrow.text = " ↓" 
		filterList.reverse()
	}


	// Filtering
	temp = getHashData("startPage")
	if(temp === false) temp = 0
	//startPage is a 1-based index, so we have to decrement it for the stuff below
	startPage = parseInt(temp) - 1
	if(startPage < 0) startPage = 0
	temp = getHashData("maxCount")

	if(temp === false || isNaN(temp))
	{
		maxCount = defaultMaxCount
	}
	else
	{
		maxCount = parseInt(temp)
	}
	if(maxCount < minServersPerPage) maxCount = minServersPerPage
	if(maxCount > maxServersPerPage) maxCount = maxServersPerPage

	maxCount--	//Since everything below is a 0-based index, decrement

	// We need to pre-parse the duplicates, because I want the domains to be preferred over the numeric IP addresses
	let serverDuplicatesList = []
	if(1) //Add the variable for the setting for checking duplicates here
	{
		for(let i = 0; i < data.serverList.length; i++)
		{
			if(filterList[i].serverInfo.serverIPAddress != filterList[i].serverInfo.serverNumericAddress)
			{
				//Before we can add this to the array, we need to be sure we didn't already add it from somewhere else (Some servers have more than one domain!)
				let match = false
				let test = filterList[i].serverInfo.serverNumericAddress + ":" + filterList[i].serverInfo.serverPort
				for(let j = 0; j < serverDuplicatesList.length; j++)
				{
					if(serverDuplicatesList[j] == test)
					{
						match = true
						//Also check for multiple domain names...
						if(filterList[i].serverInfo.serverIPAddress != filterList[i].serverInfo.serverNumericAddress)
						{
							serverDuplicatesList.push(filterList[i].serverInfo.serverIPAddress + ":" + filterList[i].serverInfo.serverPort)
						}
						break
					}
				}
				if(!match)
				{
					serverDuplicatesList.push(test)
				}
			}
		}
	}

	let filterType = getFilterType()
	filterInputText = filterInputText.toLowerCase()	//This has to be a case-insensitive search

	totalCount = 0
	for(let i = 0; i < filterList.length; i++)
	{
		//If servers are offline, do not show them
		if(!filterList[i].serverInfo || filterList[i].serverInfo.serverOnline === false) continue

		//If this server isn't of the game we are trying to show, ignore it
		if(matchValue != "all supported games" && filterList[i].serverInfo.gamename.toLowerCase() != matchValue) continue

		//If the server has a duplicate listing from a domain name and an IP address, ignore the IP address listing
		let match = false
		let test = filterList[i].serverInfo.serverIPAddress + ":" + filterList[i].serverInfo.serverPort
		for(let h = 0; h < serverDuplicatesList.length && !match; h++)
		{
			if(test == serverDuplicatesList[h])
			{
				match = true
			}
		}
		if(match == true) continue

		//Check against the filter text
		if(filterInputText !== false && filterInputText != "")
		{
			switch(decolorize(filterType))
			{
				case "server name":
					test = decolorize(filterList[i].serverInfo.servername.toLowerCase())
					if(test.search(filterInputText) < 1) continue
				break
				case "server address":
					test = decolorize(filterList[i].serverInfo.serverIPAddress.toLowerCase())
					if(test.search(filterInputText) < 1) continue
				break
				case "map name":
					test = decolorize(filterList[i].serverInfo.mapname.toLowerCase())
					if(test.search(filterInputText) < 1) continue
				break
			}
		}
		cullList.push(filterList[i])
		totalCount++
	}

	// Culling (For pagination)
	let testCount2 = Math.ceil(cullList.length / maxCount)
	if(startPage > testCount2) startPage = testCount2
	for(let i = startPage * maxCount; i < cullList.length && (startPage + 1) * maxCount; i++)
	{
		//If we are inside the specified range, push
		renderList.push(cullList[i])
	}

	maxCount++	//Since everything below is a 1-based index, increment again

	let testCount = Math.floor(totalCount / maxCount)
	if(startPage > testCount) startPage = testCount

	// Rendering
	let serverListElements = { elementType: "table", children: [
		{ elementType: "tr", className: "serverListHeader", children: [
			]}
		] }

	let countHeader = { elementType: "td", className: "topleft serverListCount serverListHeaderText", text: "", children: [] }
	let serverNameHeader = { elementType: "td", onclick: ()=>{ changeSort("servername") }, className: "serverListServerName serverListHeaderText", text: "Server Name", children: [] }
	let gameHeader = { elementType: "td", onclick: ()=>{ changeSort("game") }, className: "serverListGame serverListHeaderText", text: "Game", children: [] }
	let serverAddressHeader = { elementType: "td", onclick: ()=>{ changeSort("serveraddress") }, className: "serverListAddress serverListHeaderText", text: "Server Address", children: [] }
	let mapNameHeader = { elementType: "td", onclick: ()=>{ changeSort("map") }, className: "serverListMap serverListHeaderText", text: "Map", children: [] }
	let playersHeader = { elementType: "td", onclick: ()=>{ changeSort("players") }, className: "serverListPlayers serverListHeaderText", text: "Players", children: [] }
	let trackServerHeader = { elementType: "td", className: "topright serverListTrack serverListHeaderText", text: "" }

	switch(sortType)
	{
		case "servername":
			serverNameHeader.children.push(arrow)
		break
		case "game":
			gameHeader.children.push(arrow)
		break
		case "serveraddress":
			serverAddressHeader.children.push(arrow)
		break
		case "map":
			mapNameHeader.children.push(arrow)
		break
		case "players":
			playersHeader.children.push(arrow)
		break
	}

	serverListElements.children.push(countHeader)
	serverListElements.children.push(serverNameHeader)
	serverListElements.children.push(gameHeader)
	serverListElements.children.push(serverAddressHeader)
	serverListElements.children.push(mapNameHeader)
	serverListElements.children.push(playersHeader)
	serverListElements.children.push(trackServerHeader)

	for(let i = 0; i < renderList.length; i++)
	{
		let newServer = { elementType: "tr", title: "Last refreshed " + new Date(renderList[i].serverInfo.lastServerRefreshTime * 1000), className: "serverListLine " + (((i + 1) % 2) ? "serverListLineOdd" : "serverListLineEven"), children: [
			{ elementType: "td", className: "serverListCount", text: i + 1 },
			{ elementType: "td", className: "serverListServerName", text: renderList[i].serverInfo.servername },
			{ elementType: "td", className: "serverListGame " + makeCSSName(renderList[i].serverInfo.gamename), text: renderList[i].serverInfo.gamename },
			{ elementType: "td", className: "serverListAddress", text: renderList[i].serverInfo.serverIPAddress + ":" + renderList[i].serverInfo.serverPort },
			{ elementType: "td", className: "serverListMap", text: renderList[i].serverInfo.mapname },
			{ elementType: "td", className: "serverListPlayers", text: renderList[i].players.length + "/" + renderList[i].serverInfo.maxPlayers },
			{ elementType: "td", className: "serverListTrack", children: [
				{ elementType: "a", className: "serverListTrackButton", href: trackerURL + "?ip=" + renderList[i].serverInfo.serverIPAddress + "&port=" + renderList[i].serverInfo.serverPort, target: "_blank", text: "Track" }
				]}
			]}

		serverListElements.children.push(newServer)
	}


	//Since this is a 0 based index in rendering, but a 1 based index for pages, we need to increment it
	startPage++

	// Calculate out the number of pages this will be
	let totalPages = 1
	if(renderList.length > 0)
	{
		totalPages = Math.ceil(totalCount / maxCount)
	}
	else
	{
		serverListElements = { elementType: "h3", text: "No servers found!" }
	}


	let startColor = 0
	let endColor = 1.0 / 5
	let sColor = 1
	let lColor = 0.5

	let pageNumbersAndArrows = { elementType: "div", className: "serverListArrowsDiv", children: [] }

	let pageHeader = { elementType: "div", children: [
		]}
	startArrow = { elementType: "div", className: "serverListArrows serverListBeginningArrow", text: "|<<" }
	backArrow = { elementType: "div", className: "serverListArrows serverListBackArrow", text: "<<" }
	if(startPage > 1)
	{
		startArrow.onclick = ()=>{ changeHashData({ "startPage": 1 })}
		startArrow.className += " serverListHover"
		startArrow.title = "Page 1"
		startArrow.style = { "color": hslToRgb(startColor, sColor, lColor) }
		backArrow.onclick = ()=>{ changeHashData({ "startPage": startPage - 1 })}
		backArrow.className += " serverListHover"
		backArrow.title = "Page " + (startPage - 1)
		backArrow.style = { "color": hslToRgb(startColor, sColor, lColor) }
	}
	else
	{
		startArrow.className += " serverListCurrentNumber"
		backArrow.className += " serverListCurrentNumber"
	}
	pageNumbersAndArrows.children.push(startArrow)
	pageNumbersAndArrows.children.push(backArrow)

	let maxPagesVisible = 15	//Make sure this is an odd number, it looks better that way
	let taperCount = 3			//The number of entries to taper from the ends of the page list for easier navigation

	let pagesArray = []
	if(totalPages < maxPagesVisible)
	{
		//The number of visible pages is less than the max allowed, so just show them all
		for(let i = 1; i < totalPages + 1; i++)
		{
			pagesArray.push(i)
		}
	}
	else
	{
		let start = startPage - Math.floor(maxPagesVisible / 2)
		if(start < 1) start = 1
		if(start + maxPagesVisible > totalPages) start = totalPages - maxPagesVisible + 1	//FIXME - I'm pretty sure that + 1 at the end creates an off-by-one error???
		let stop = start + maxPagesVisible
		for(let i = start; i < stop; i++)
		{
			pagesArray.push(i)
		}

		// Let's taper the ends of this list...
		let taperCount = 3	//The number of entries to taper from the ends of the page list for easier navigation
		let taperMultiplier = 1 / (taperCount + 1)
		for(let i = 0; i < taperCount; i++)
		{
			if(pagesArray[i] > i + 1) pagesArray[i] = Math.ceil(pagesArray[i] * taperMultiplier * (i + 1))
		}

		taperIncrement = taperCount
		for(let i = maxPagesVisible - 1; i >= maxPagesVisible - taperCount; i--)
		{
			let tempPageNum = (totalPages - pagesArray[i]) * taperMultiplier * taperIncrement + pagesArray[i]
			if(i == maxPagesVisible - 1)
			{
				pagesArray[i] = Math.ceil(tempPageNum)
			}
			else
			{
				pagesArray[i] = Math.floor(tempPageNum)
			}
			taperIncrement--
		}
	}

	for(let i = 0; i < pagesArray.length; i++)
	{
		let newPageNumber = { elementType: "div", className: "serverListNumbers", text: pagesArray[i] }
		if(pagesArray[i] != startPage) newPageNumber.style = { "color": hslToRgb(((endColor - startColor) / (pagesArray[pagesArray.length - 1] - pagesArray[0]) ) * pagesArray[i], sColor, lColor) }
		if(pagesArray[i] != startPage)
		{
			newPageNumber.onclick = ()=>{ changeHashData({ "startPage": pagesArray[i] }) }
			newPageNumber.className += " serverListHover"
		}
		else
		{
			newPageNumber.className += " serverListCurrentNumber"
		}
		pageNumbersAndArrows.children.push(newPageNumber)
	}


	forwardArrow = { elementType: "div", className: "serverListArrows serverListForwardArrow", text: ">>" }
	endArrow = { elementType: "div", className: "serverListArrows serverListEndArrow", text: ">>|" }
	if(startPage < totalPages)
	{
		forwardArrow.onclick = ()=>{ changeHashData({ "startPage": startPage + 1 }) }
		forwardArrow.className += " serverListHover"
		forwardArrow.title = "Page " + (startPage + 1)
		forwardArrow.style = { "color": hslToRgb(endColor, sColor, lColor) }
		endArrow.onclick = ()=>{ changeHashData({ "startPage": totalPages })}
		endArrow.className += " serverListHover"
		endArrow.title = "Page " + totalPages
		endArrow.style = { "color": hslToRgb(endColor, sColor, lColor) }
	}
	else
	{
		forwardArrow.className += " serverListCurrentNumber"
		endArrow.className += " serverListCurrentNumber"
	}
	pageNumbersAndArrows.children.push(forwardArrow)
	pageNumbersAndArrows.children.push(endArrow)

	pageHeader.children.push(pageNumbersAndArrows)

	let pageFooter = pageNumbersAndArrows


	let pageDropdown = { elementType: "div", className: "pageCountDropdownDiv", children: [
		{ elementType: "span", className: "", text: "Servers" },
		{ elementType: "br" },
		{ elementType: "span", className: "", text: "Per Page:" },
		{ elementType: "br" },
		{ elementType: "select", id: "pageCountDropdown", onchange: ()=>{ changeHashData({ "maxcount": getSelectedValue("pageCountDropdown") }) }, children: [] }
		]}

	let pageCountIncrement = 10
	for(let i = 1; i * pageCountIncrement < maxServersPerPage + 1; i++)
	{
		if(i * pageCountIncrement >= minServersPerPage)
		{
			let newEntry = { elementType: "option", value: i * pageCountIncrement, text: i * pageCountIncrement }
			if(maxCount == i * pageCountIncrement) newEntry.selected = "selected"
			pageDropdown.children[pageDropdown.children.length - 1].children.push(newEntry)
		}
	}
	pageHeader.children.push(pageDropdown)
	pageHeader.children.unshift({ elementType: "div", className: "pageCountDropdownDiv" })


	// Assemble the page
	headerDiv.appendChild(createElement(pageHeader))
	serverDiv.appendChild(createElement(serverListElements))
	footerDiv.appendChild(createElement(pageFooter))

}

function getSelectedValue(input)
{
	input = document.getElementById(input)
	for(let i = 0; i < input.length; i++)
	{
		if(input[i].selected) return input[i].value
	}
	return false
}

function hslToRgb(h, s, l) {
	var r, g, b;

	if (s == 0) {
		r = g = b = l; // achromatic
	} else {
		function hue2rgb(p, q, t) {
			if (t < 0) t += 1;
			if (t > 1) t -= 1;
			if (t < 1/6) return p + (q - p) * 6 * t;
			if (t < 1/2) return q;
			if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
			return p;
		}

		var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
		var p = 2 * l - q;

		r = hue2rgb(p, q, h + 1/3);
		g = hue2rgb(p, q, h);
		b = hue2rgb(p, q, h - 1/3);
	}

	return 'rgb(' + r * 255 + ',' + g * 255 + ',' + b * 255 + ')';
}

function getFilterType()
{
	for(let i = 0; i < filterRadioButtons.length; i++)
	{
		if(filterRadioButtons[i].checked) return filterRadioButtons[i].value.toLowerCase()
	}
	return "server address"
}

function sortServerList(sortType)
{
	switch(sortType)
	{
		case "servername":
			sortServerListByProperty("serverNumericAddress")
			sortServerListByProperty("gamename")
			sortServerListByProperty("mapname")
			sortServerListByProperty("players")
			sortServerListByProperty("servername")
		break
		case "game":
			sortServerListByProperty("serverNumericAddress")
			sortServerListByProperty("servername")
			sortServerListByProperty("mapname")
			sortServerListByProperty("players")
			sortServerListByProperty("gamename")
		break
		case "serveraddress":
			sortServerListByProperty("servername")
			sortServerListByProperty("mapname")
			sortServerListByProperty("gamename")
			sortServerListByProperty("players")
			sortServerListByProperty("serverNumericAddress")
		break
		case "map":
			sortServerListByProperty("serverNumericAddress")
			sortServerListByProperty("servername")
			sortServerListByProperty("players")
			sortServerListByProperty("gamename")
			sortServerListByProperty("mapname")
		break
		case "players":
			sortServerListByProperty("serverNumericAddress")
			sortServerListByProperty("gamename")
			sortServerListByProperty("servername")
			sortServerListByProperty("mapname")
			sortServerListByProperty("players")
		break
	}
}

function sortServerListByProperty(property)
{
	filterList.sort((a, b)=>{
	if(!a.serverInfo && !b.serverInfo) return 0
	if(!a.serverInfo) return -1
	if(!b.serverInfo) return 1
	if(a.serverInfo.serverOnline == false && b.serverInfo.serverOnline == false) return 0
	if(a.serverInfo.serverOnline == false) return -1
	if(b.serverInfo.serverOnline == false) return 1

	let aProperty = ""
	let bProperty = ""

	if(property == "players")
	{
		aProperty = a.players.length
		bProperty = b.players.length
	}
	else if(property == "serverNumericAddress")
	{
		aProperty = a.serverInfo.serverIPAddress.split(".")
		bProperty = b.serverInfo.serverIPAddress.split(".")
		let iterationLength = findGreater(aProperty.length, bProperty.length)

		for(let i = 0; i < iterationLength; i++)
		{
			if(!isNaN(parseInt(aProperty[0])) && !isNaN(parseInt(bProperty[0])))
			{
				let test1 = parseInt(aProperty[i])
				let test2 = parseInt(bProperty[i])
				//Both inputs are numeric
				if(test1 == test2) continue
				return test1 - test2
				if(length != 0) return length
			}

			if(isNaN(parseInt(aProperty[0])) && isNaN(parseInt(bProperty[0])))
			{
				//Both addresses are strings. Evaluate as such
				for(let i = iterationLength; i >= 0 ; i--)
				{
					//If the strings are the same, just move to the next iteration
					if(aProperty[i] == bProperty[i]) continue
					stringLength = findGreater(aProperty[i], bProperty[i])
					for(let h = 0; h < stringLength; h--)
					{
						//The strings have to be different if we got here
						if(h >= aProperty.length) return -1
						if(h >= bProperty.length) return 1
						if(aProperty[i][h] > bProperty[i][h]) return 1
						if(aProperty[i][h] < bProperty[i][h]) return -1
					}
				}
				//If we got here, then the addresses are the same. Use the port to differentiate
				return a.serverInfo.serverPort - b.serverInfo.serverPort
			}

			// At least one input is numeric
			if(i > aProperty.length) return -1
			if(i > bProperty.length) return 1
			let test1 = parseInt(aProperty[i])
			let test2 = parseInt(bProperty[i])
			if(isNaN(test1))
			{
				return 1
			}
			if(isNaN(test2))
			{
				return -1
			}
		}
		//If we got here, then the server addresses are the same. Use the port to differentiate
		return a.serverInfo.serverPort - b.serverInfo.serverPort
	}
	else
	{
		aProperty = decolorize(a.serverInfo[property].toLowerCase())
		bProperty = decolorize(b.serverInfo[property].toLowerCase())
		aProperty = aProperty.trim()
		bProperty = bProperty.trim()
	}
	if (aProperty < bProperty) return -1
	if (aProperty > bProperty) return 1
	return 0
	})
}

function findGreater(input1, input2)
{
	if(input1 > input2) return input1
	return input2
}

function changeSort(input)
{
	let oldSort = getHashData("sorttype")
	if(oldSort == input)
	{
		let direction = parseBoolean(getHashData("sortAscending"))
		direction = !direction
		changeHashData({"sortAscending": direction})
	}
	else
	{
		if(oldSort === false && getHashData("sortAscending") === false && input.toLowerCase() == "players")
		{
			//Nothing was in the hash at all.
			changeHashData({"sortType": input, "sortAscending": true})
		}
		else
		{
			changeHashData({"sortType": input, "sortAscending": false})
		}
	}
}

function parseBoolean(input)
{
	switch (typeof(input))
	{
		case "boolean":
			if(input == true) return input
			if(input == false) return input
		break
		case "number":
			if(input > 0) return true
			return false
		break
		case "string":
			input = input.toLowerCase()
			if(input == "t" || input == "true") return true
			if(input == "f" || input == "false") return false
		break
		case "object":
			return false
		break
	}
	return false
}

function makeCSSName(input)
{
	input = input.replace(/\W/g, "")
	return input.toLowerCase()
}

function clear_element(e) {
	for (let i = e.children.length - 1; i >= 0; i--)
	{
		let c = e.children[i]
		if (c.id == 'errorDiv') continue;
		e.removeChild(c)
	}
}

function createElement(inputValues)
{
	//This function was copied from another project to make this easier...
	let new_element = null
	if(inputValues && inputValues.elementType)
	{
		new_element = document.createElement(inputValues.elementType)
		for (var key in inputValues)
		{
			switch(key) {
				case 'elementType':
					continue
				case 'style':
					for(let styleKey in inputValues.style)
					{
							 new_element.style[styleKey] = inputValues.style[styleKey]
					}
					break
				case 'text':
/*
					//This code is part of a test to have \n characters converted to <br> characters
					if(inputValues[key] !== '')
					{
						if(inputValues[key] === '\n')
						{
							new_element.appendChild(document.createElement('br'))
							break
						}
						else
						{
							let newText = inputValues[key].toString().split('\n')
							for(let i = 0; i < newText.length; i++)
							{
								if(i > 0) new_element.appendChild(document.createElement('br'))
								if(newText[i] === '') continue
								let text_element = document.createElement('span')
								text_element.appendChild(document.createTextNode(newText[i]))
								new_element.appendChild(text_element)
							}
						}
					}
*/
					if(inputValues.elementType == 'div')
					{
						let text_element = colorize(inputValues[key])
						new_element.appendChild(text_element)
					}
					else
					{
						let text_element = colorize(inputValues[key])
						new_element.appendChild(text_element)
					}
					break
				case 'children':
					for (let i = 0; i < inputValues.children.length; i++) {
						new_element.appendChild(createElement(inputValues.children[i]))
					}
					break
				default:
					new_element[key] = inputValues[key]
			}
		}
	}
	return new_element
}

function updateFilterHashAfterDelay()
{
	clearTimeout(timer);
	timer = setTimeout("changeHashData({ filter: document.getElementById('filterInput').value })", 2000);
}

document.addEventListener("DOMContentLoaded", function(event)
{
	console.log("%c WARNING: DO NOT USE THIS PAGE\'S JSON OUTPUT TO INTERFACE WITH PARATRACKER", 'font-size: 14pt; font-weight: bold; color: #F00;')
	console.log("%c THIS PAGE DELIVERS CACHED DATA ONLY, FOR SPEED REASONS", 'font-size: 14pt; font-weight: bold; color: #F00;')
	console.log("%c FOR UP-TO-DATE INFO, DIRECTLY TRACK A SINGLE GAME SERVER", 'font-size: 14pt; font-weight: bold; color: #F00;')

	trackerURL = window.location.href.split(data.utilitiesPath)[0]

	//Have to add these, since PHP won't be sending it (Obviously)
	gameList.unshift("All Supported Games")
//	gameList.push("Unrecognized Games")

	//This has to be rendered here, or else it gets re-rendered with every settings change, which deselects the combo box
	gameListComboBox = { elementType: "select", id: "gameDropdown", onchange: ()=>{ changeServerListGame(gameDropdown.value) }, children: []}
	let match = false
	URLGameName = getHashData("game")
	for(let i = 0; i < gameList.length; i++)
	{
		newOption = { elementType: "option", className: "", text: gameList[i], value: gameList[i] }
		gameListComboBox.children.push(newOption)
	}
	if(!match) gameListComboBox.children[0].selected = "selected"

	gameSelectDiv.appendChild(createElement(gameListComboBox))

	//Add this here as well
	filterBox = { elementType: "div", className: "filterBoxDiv", children: [
		{ elementType: "span", className: "filterLabel", text: "Filter: " },
		{ elementType: "input", type: "text", size: 30, id: "filterInput", oninput: ()=>{ updateFilterHashAfterDelay() }, placeholder: "Filter text" },
		{ elementType: "button", id: "clearFilter", text: "X", onclick: ()=>{ changeHashData({ filter: "" }) } },
		{ elementType: "label", children: [
			{ elementType: "input", type: "radio", id: "filterRadioButtons", name: "filter", value: "Server Name", onchange: ()=>{ changeHashData({ filterDestination: "server name" }) } },
			{ elementType: "span", text: "Server Name" },
			]},
		{ elementType: "label", children: [
			{ elementType: "input", type: "radio", id: "filterRadioButtons", name: "filter", value: "Server Address", onchange: ()=>{ changeHashData({ filterDestination: "server address" }) } },
			{ elementType: "span", for: "filter", text: "Server Address" },
		]},
		{ elementType: "label", children: [
			{ elementType: "input", type: "radio", id: "filterRadioButtons", name: "filter", value: "Map Name", onchange: ()=>{ changeHashData({ filterDestination: "map name" }) } },
			{ elementType: "span", for: "filter", text: "Map Name" },
			]},
		]}


	let test = getHashData("filterdestination")
	match = false
	if(test === false) test = "server name"
	test = test.toLowerCase()
	for(let i = 0; i < filterBox.children.length; i++)
	{
		if(!filterBox.children[i].children) continue
		if(filterBox.children[i].children[0].name == "filter")
		{
			if(filterBox.children[i].children[0].value.toLowerCase() == test)
			{
				filterBox.children[i].children[0].checked = true
				match = true
				break
			}
		}
	}

	filterDiv.appendChild(createElement(filterBox))

	currentGame = getHashData("game")
	if(currentGame === false) currentGame = defaultGame

	window.onhashchange = renderServerList
	renderServerList()

	document.getElementById("filterInput").focus();
})
