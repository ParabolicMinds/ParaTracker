var bitflags_tabdisplay
var bitflags_bitselect
var bitflags_tabregion

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
			
			let bitset_html = {}
			bitset_html.flags = []
			
			// ================
			let set_content = document.createElement('div')
			set_content.className = 'bitflags_container'
			// ================
			let set_content_top = document.createElement('div')
			set_content_top.className = 'bitflags_container_top'
			let set_content_bottom = document.createElement('div')
			set_content_bottom.className = 'bitflags_container_bottom'
			set_content.appendChild(set_content_top)
			set_content.appendChild(set_content_bottom)
			// ================
			bitset.flags.forEach((flag)=>{
				let flagobj = {}
				flagobj.name = flag
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

			let selectAll_button = document.createElement('div')
			selectAll_button.appendChild(document.createTextNode(' Select All '))
			selectAll_button.className = 'selectAllButton'
			selectAll_button.onclick = ()=>{bitvalue.value = '9999999999999999999999';bitflags_recalculate_from_input()}

			let clear_button = document.createElement('div')
			clear_button.appendChild(document.createTextNode(' Clear All '))
			clear_button.className = 'clearButton'
			clear_button.onclick = ()=>{bitvalue.value = '0';bitflags_recalculate_from_input()}

			set_content_bottom.appendChild(bitvalue_label)
			set_content_bottom.appendChild(bitvalue)
			set_content_bottom.appendChild(selectAll_button)
			set_content_bottom.appendChild(clear_button)
			// ================
			
			
			bitset_html.name = bitset.setname
			bitset_html.opt = set_opt
			bitset_html.content = set_content
			bitset_html.bitvalue = bitvalue
			bitset_html.maxvalue = Math.pow(2, bitset.flags.length) - 1
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

function bitflags_recalculate_from_cbs() {
	let sum = 0
	for (let i = 0; i < cur_bitset.flags.length; i++) {
		if (cur_bitset.flags[i].cb.checked) {
			sum += Math.pow(2, i)
			set_flag(cur_bitset.flags[i], true)
		} else {
			set_flag(cur_bitset.flags[i], false)
		}
	}
	cur_bitset.bitvalue.value = sum
}

function bitflags_recalculate_from_input() {
	let value = parseInt(cur_bitset.bitvalue.value)
	if (isNaN(value) || value < 0) value = 0
	if (!isFinite(value) || value > cur_bitset.maxvalue) value = cur_bitset.maxvalue
	for (let i = 0; i < cur_bitset.flags.length; i++) {
		let bit = 1 << i
		set_flag(cur_bitset.flags[i], (value & bit))
	}
	cur_bitset.bitvalue.value = value
}



