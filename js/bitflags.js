var bitflags_tabdisplay
var bitflags_bitselect
var bitflags_tabregion
var sortAlphabetically = false

var bitflags_html = []

function clear_element(e) {
    while (e.hasChildNodes()) {
      e.removeChild(e.lastChild);
    }
}

function setup() {
	let index = 0
	bitflags_raw.forEach( game => {
		let game_html = {}
		let game_tab = document.createElement('span')
		game_tab.gameClassName = game.gameClassName
		game_tab.appendChild(document.createTextNode(game.gamename))
		game_tab.index = index++
		game_tab.onclick = ()=>{bitflags_gamechange(game_tab.index)}
		bitflags_tabdisplay.appendChild(game_tab)
		game_html.tab = game_tab
		game_html.sets = []
		
		game.bitflags.forEach( bitset => {
			let set_opt = document.createElement('option')
			set_opt.className = 'bitselect_background'
			set_opt.appendChild(document.createTextNode(bitset.setname))
			
			bitset_html = {}
			bitset_html.flags = []
			
			// ================
			let set_content = document.createElement('div')
			set_content.className = 'bitflags_container'
			// ================
			let set_content_top = document.createElement('div')
			set_content_top.className = 'bitflags_container_top'
			set_content_top.id = 'bitflags_container_top'
			let set_content_bottom = document.createElement('div')
			set_content_bottom.className = 'bitflags_container_bottom'
			set_content.appendChild(set_content_top)
			set_content.appendChild(set_content_bottom)
			// ================

			i = 1
			bitset.flags.forEach((flag)=>{
			    let flagobj = {}
			    flagobj.name = flag
		        flagobj.value = i
			    let flagcont = document.createElement('div')
			    let span = document.createElement('span')
			    span.className = 'bitflags_container_flag'
			    span.appendChild(document.createTextNode(flagobj.name))
			    flagcont.appendChild(span)
			    let cb = document.createElement('input')
			    cb.onclick = ()=>{cb.checked = !cb.checked; bitflags_recalculate_from_cbs()}
			    cb.type = 'checkbox'
			    cb.className = 'bitflags_container_cb'
			    flagcont.appendChild(cb)
			    set_content_top.appendChild(flagcont)
				
			    flagcont.onclick = cb.onclick
				
			    flagobj.cb = cb
			    flagobj.cont = flagcont
			    bitset_html.flags.push(flagobj)
			    i = i * 2
			})

			// ================
			let bitvalue_label = document.createElement('span')
			bitvalue_label.appendChild(document.createTextNode(bitset.setname + ' '))
			bitvalue_label.className = 'bitflags_container_bitvalue_label'
			bitvalue_label.onclick = ()=>{window.prompt('Copy/Paste', bitset.setname + ' ' + cur_bitset.bitvalue.value)}
			let bitvalue = document.createElement('input')
			bitvalue.type = 'text'
			bitvalue.className = 'bitflags_container_bitvalue'
			bitvalue.oninput = ()=>{bitflags_recalculate_from_input()}

			let alphabetize_label = document.createElement('div')
			alphabetize_label.appendChild(document.createTextNode('Alphabetize List'))
			alphabetize_label.className = 'alphabetizeLabel'

			let alphabetize_checkbox = document.createElement('input')
			alphabetize_checkbox.type = "checkbox"
			alphabetize_checkbox.className = 'alphabetizeCheckbox'
			alphabetize_checkbox.onchange = ()=>{sortFlags()}

			let leftSide = document.createElement('div')
			leftSide.className = 'leftContainer'
			leftSide.appendChild(alphabetize_label)
			leftSide.appendChild(alphabetize_checkbox)

			let invertSelection_button = document.createElement('span')
			invertSelection_button.appendChild(document.createTextNode('Invert Selection'))
			invertSelection_button.className = 'invertSelectionButton'
			invertSelection_button.onclick = ()=>{invertSelection()}

			let selectAll_button = document.createElement('span')
			selectAll_button.appendChild(document.createTextNode('Select All'))
			selectAll_button.className = 'selectAllButton'
			selectAll_button.onclick = ()=>{setMaxValue()}

			let clear_button = document.createElement('span')
			clear_button.appendChild(document.createTextNode('Clear All'))
			clear_button.className = 'clearButton'
			clear_button.onclick = ()=>{bitvalue.value = '0';bitflags_recalculate_from_input()}

			let rightSide = document.createElement('div')
			rightSide.className = 'rightContainer'
			rightSide.appendChild(invertSelection_button)
			rightSide.appendChild(selectAll_button)
			rightSide.appendChild(clear_button)

			let bottom_container = document.createElement('div')
			bottom_container.className = 'bottomContainer'

			bottom_container.appendChild(leftSide)
			bottom_container.appendChild(bitvalue)
			bottom_container.appendChild(rightSide)
			set_content_bottom.appendChild(bitvalue_label)
			set_content_bottom.appendChild(bottom_container)
			// ================
			
			
			bitset_html.name = bitset.setname
			bitset_html.opt = set_opt
			bitset_html.flags_content = set_content_top
			bitset_html.content = set_content
			bitset_html.bitvalue = bitvalue
			bitset_html.maxvalue = Math.pow(2, bitset.flags.length) - 1
			bitset_html.alphacb = alphabetize_checkbox
			game_html.sets.push(bitset_html)
		})
		bitflags_html.push(game_html)
	})
	bitflags_gamechange(0)
}

document.addEventListener("DOMContentLoaded", function(event) {
	bitflags_tabdisplay = document.getElementById('bitflags_tabdisplay')
	bitflags_bitselect = document.getElementById('bitflags_bitselect')
	bitflags_tabregion = document.getElementById('bitflags_tabregion')
	setup()
})

var cur_game
var cur_bitset

function sortEntriesByName(input)
{
    input.sort((a, b)=>{
    let aname = a.name
    let bname = b.name
    aname = aname.toLowerCase()
    bname = bname.toLowerCase()
    if (aname == '(unused)') return 1
    if (bname == '(unused)') return -1
    if (aname < bname) return -1
    if (aname > bname) return 1
    return a.value - b.value
    })
}

function sortEntriesByValue(input)
{
    input.sort((a, b)=>{
    return a.value - b.value
    })
}

function sortFlags() {
	sortAlphabetically = cur_bitset.alphacb.checked
    if(sortAlphabetically) sortEntriesByName(cur_bitset.flags)
    else sortEntriesByValue(cur_bitset.flags)
    
    clear_element(cur_bitset.flags_content)
    cur_bitset.flags.forEach(flag=>{
		cur_bitset.flags_content.appendChild(flag.cont)
	})
}

function bitflags_gamechange(index) {
	bitflags_html.forEach(game=>{game.tab.className = 'game_tab ' + game.tab.gameClassName})
	cur_game = bitflags_html[index]
	cur_game.tab.className = 'game_tab game_tab_selected ' + cur_game.tab.gameClassName
	clear_element(bitflags_bitselect)
	cur_game.sets.forEach( bitset => {
		bitflags_bitselect.appendChild(bitset.opt)
	})
	bitflags_setchange(bitflags_bitselect.selectedIndex)
}

function bitflags_setchange(index) {
	clear_element(bitflags_tabregion)
	cur_bitset = cur_game.sets[index]
	bitflags_tabregion.appendChild(cur_bitset.content)
	cur_bitset.alphacb.checked = sortAlphabetically
	sortFlags()
	bitflags_recalculate_from_cbs()
}

function set_flag(flag, bool) {
	if (bool) {
		flag.cb.checked = true
		flag.cont.className = 'bitflags_container_flagcont bitflags_container_flagcont_selected'
	} else {
		flag.cb.checked = false
		flag.cont.className = 'bitflags_container_flagcont'
	}
}

function invertSelection()
{
    let testCount = cur_bitset.flags.length
    for(f = 0; f < testCount; f++)
    {
        if(cur_bitset.flags[f].cb.checked == true)
        {
            cur_bitset.flags[f].cb.checked = false
        }
        else
        {
            cur_bitset.flags[f].cb.checked = true
        }
    }
    bitflags_recalculate_from_cbs()
}

function bitflags_recalculate_from_cbs() {
	let sum = 0
	for (let i = 0; i < cur_bitset.flags.length; i++) {
		if (cur_bitset.flags[i].cb.checked) {
			sum += parseInt(cur_bitset.flags[i].value)
			set_flag(cur_bitset.flags[i], true)
		} else {
			set_flag(cur_bitset.flags[i], false)
		}
	}
	cur_bitset.bitvalue.value = sum
}

function setMaxValue()
{
    cur_bitset.bitvalue.value = cur_bitset.maxvalue
    bitflags_recalculate_from_input()
}

function bitflags_recalculate_from_input() {
	let value = parseInt(cur_bitset.bitvalue.value)
	if (isNaN(value) || value < 0) value = 0
	if (!isFinite(value) || value > cur_bitset.maxvalue) value = cur_bitset.maxvalue

	count = cur_bitset.flags.length
    for (let i = 0; i < count; i++)
    {
        set_flag(cur_bitset.flags[i], (cur_bitset.flags[i].value & value) ? true : false)
    }
    
    cur_bitset.bitvalue.value = value
}
