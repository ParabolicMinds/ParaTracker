document.addEventListener("DOMContentLoaded", function(event)
{
    renderParamPage()
})

function renderParamPage()
{
    if(data.serverOnline)
    {
        clear_element(paramDataTable)
//        sort_elements(data.info)
        renderElements(paramDataTable)
    }
    else
    {
        clear_element(bodyElement)
        errorMessage = document.createElement('span')
        errorMessage.appendChild(document.createTextNode(data.connectionErrorMessage))
        bodyElement.appendChild(document.createTextNode(errorMessage))
    }
}

function sort_elements(input)
{
    input.sort((a, b)=>{
    let ad = decolorize(a.toLowerCase())
    let bd = decolorize(b.toLowerCase())
    if (decolorize(ad.toLowerCase()) > decolorize(bd.toLowerCase())) return 1
    return -1
    })
}

function renderElements(paramDataTable)
{

    clear_element(CVarServerNumericAddress)
    if(data.serverNumericAddress != data.serverIPAddress)
    {
        CVarServerNumericAddress.appendChild(document.createTextNode('(' + data.serverNumericAddress + ')'))
        CVarServerNumericAddress.appendChild(document.createElement('br'))
    }


clear_element(parseTime)
parseTime.appendChild(document.createTextNode(data.serverInfo.parseTime))

clear_element(serverPing)
serverPing.appendChild(document.createTextNode(data.serverInfo.serverPing))

clear_element(serverName)
serverName.appendChild(colorize(data.serverInfo.servername))

clear_element(gameName)
gameName.appendChild(colorize(data.serverInfo.gamename))

table = ""

tr = document.createElement('tr')
td1 = document.createElement('td')
td2 = document.createElement('td')

tr.className = 'cvars_titleRow cvars_titleRowSize'
td1.className = 'nameColumnWidth'
td2.className = 'valueColumnWidth'

td1.appendChild(document.createTextNode('Name'))
td2.appendChild(document.createTextNode('Value'))
tr.appendChild(td1)
tr.appendChild(td2)
paramDataTable.appendChild(tr)

row = 1
count = data.parsedInfo.length
for (let infokey in data.info)
{
    let infoval = data.info[infokey]

    tr = document.createElement('tr')
    td1 = document.createElement('td')
    td2 = document.createElement('td')

    tr.className = 'cvars_row' + row
    td1.className = 'nameColumnWidth'
    td2.className = 'valueColumnWidth'

    td1.appendChild(colorize(infokey))

    match = 0
    if(infokey.toLowerCase() == "sv_hostname" || infokey.toLowerCase() == "hostname")
    {
        b = document.createElement('b')
        b.appendChild(colorize(data.serverInfo.servername))
        td2.appendChild(b)
        td2.appendChild(document.createElement('br'))
        td2.appendChild(document.createTextNode(data.serverInfo.servernameUnfiltered))
    }
    else
    {
        for(i = 0; i < count; i++)
        {
            if(infokey == data.parsedInfo[i].name)
            {
                let infoDiv = document.createElement('div')
                infoDiv.className = "CVarExpandList"
                infoDiv.onclick = ()=>{bitValueClick(infokey)}

                i2 = document.createElement('i')
                b = document.createElement('b')

                b.appendChild(colorize(infoval))
                i2.appendChild(b)
                infoDiv.appendChild(i2)
                infoDiv.appendChild(document.createElement('br'))

                i2 = document.createElement('i')
                i2.className = 'expandCollapse'
                i2.appendChild(document.createTextNode('(Click to expand/collapse)'))
                infoDiv.appendChild(i2)
                infoDiv.appendChild(document.createElement('br'))

                let innerDiv = document.createElement('div')
                innerDiv.id = infokey
                innerDiv.className = "collapsedList"
                i2 = document.createElement('i')
                count2 = data.parsedInfo[i].flags.length
                if(count2 > 0)
                {
                    innerDiv.id = infokey
                    for(j = 0; j < count2; j++)
                    {
                        i2.appendChild(document.createTextNode(data.parsedInfo[i].flags[j]))
                        i2.appendChild(document.createElement('br'))
                    }
                }
                else
                {
                    i2.appendChild(document.createTextNode('None'))
                }

                innerDiv.appendChild(i2)
                infoDiv.appendChild(innerDiv)

                td2.appendChild(infoDiv)
                i = count
                match = 1
            }
        }
        if(match == 0)
        {
            td2.appendChild(colorize(infoval))
        }
    }


    tr.appendChild(td1)
    tr.appendChild(td2)
    paramDataTable.appendChild(tr)
    row++
    if(row > 2)
    {
        row = 1
    }
}

}
