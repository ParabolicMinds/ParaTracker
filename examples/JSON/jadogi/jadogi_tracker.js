var json_host = 'dogi.us'
var json_port = '29070'

var tracker_header
var tracker_players
var tracker_info
var tracker_lv
var tracker_lv_i
var tracker_plyhead_count

var data
var lvindex = 0

var lvTimeout

function cur_lvs() {
	if (lvindex >= data.levelshotsArray.length) lvindex = 0
	return 'http://pt.dogi.us/' + data.levelshotsArray[lvindex]
}

function next_lvs() {
	lvindex += 1
	if (lvindex >= data.levelshotsArray.length) lvindex = 0
	return 'http://pt.dogi.us/' + data.levelshotsArray[lvindex]
}

function peek_lvs() {
	let nlv = lvindex + 1
	if (nlv >= data.levelshotsArray.length) nlv = 0
	return 'http://pt.dogi.us/' + data.levelshotsArray[nlv]
}

var is_in_transition = false

function lvtrans() {
	clearTimeout(lvTimeout)
	if (is_in_transition) return
	is_in_transition = true
	let lvto = next_lvs()
	tracker_lv.style.backgroundImage = 'url("' + lvto + '")'
	tracker_lv_i.style.animation = '1s ease 0s 1 forwards tracker_transition'
	setTimeout(()=>{
		tracker_lv_i.style.animation = null
		tracker_lv_i.src = lvto
		tracker_lv.style.backgroundImage = 'url("' + peek_lvs() + '")'
		is_in_transition = false
		lvTimeout = setTimeout(()=>{
			lvtrans()
		}, 3000)
	}, 1000)
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

function generate_colored_span(text) {
	let cont = document.createElement('span')
	
	let txtary = []
	let curtxt = ''
	let curcol = '7'
	
	for (let i = 0; i < text.length; i++) {
		if (text[i] == '^') {
			curobj = {}
			curobj.txt = curtxt
			curobj.col = curcol
			txtary.push(curobj)
			curtxt = ''
			i += 1
			curcol = text[i]
		} else {
			curtxt += text[i]
		}
	}
	
	curobj = {}
	curobj.txt = curtxt
	curobj.col = curcol
	txtary.push(curobj)
	
	txtary.forEach((txt)=>{
		let txtsp = document.createElement('span')
		txtsp.className = 'tracker_col' + txt.col
		txtsp.appendChild(document.createTextNode(txt.txt))
		cont.appendChild(txtsp)
	})
	
	return cont
}

var paramdiv

function showparams(evt) {
	if (paramdiv) {
		paramdiv.remove()
	}
	paramdiv = document.createElement('div')
	paramdiv.className = 'tracker_param_div'
	paramdiv.style.left = evt.pageX + 'px'
	paramdiv.style.top = evt.pageY + 'px'
	
	let flag = 0
	paramdiv.addEventListener('mousedown',()=>{ flag = 1 })
	paramdiv.addEventListener('mousemove',()=>{ flag = 0 })
	paramdiv.addEventListener('mouseup',()=>{ if (flag) paramdiv.remove() })
	document.body.appendChild(paramdiv)
	
	let alternator = false
	for (let infokey in data.info) {
		let infoval = data.info[infokey]
		let kvtr = document.createElement('div')
		kvtr.className = 'tracker_param_row ' + (alternator ? 'tracker_param_rowa' : 'tracker_param_rowb')
		alternator = !alternator
		let keytd = document.createElement('div')
		keytd.className = 'tracker_param_keyd'
		keytd.appendChild(wrap_span(infokey, 'tracker_param_key'))
		let valtd = document.createElement('div')
		valtd.className = 'tracker_param_vald'
		valtd.appendChild(wrap_span(infoval, 'tracker_param_val'))
		kvtr.appendChild(keytd)
		kvtr.appendChild(valtd)
		paramdiv.appendChild(kvtr)
	}
}

function timer_loop(cur_tim, reftim) {
	cur_tim -= 1
	if (cur_tim == 0) {
		reftim.innerHTML = '...'
		setup_tracker()
		return
	} else {
		reftim.innerHTML = cur_tim
		setTimeout(()=>{timer_loop(cur_tim, reftim)}, 1000)
	}
}

function setup_tracker() {
	
	json_info = {}
	let hashsplit = window.location.hash.substring(1).split(':')
	json_info.host = hashsplit[0] || 'dogi.us'
	json_info.port = hashsplit[1] || '29070'
	
	let svjsonreq = new XMLHttpRequest()
	svjsonreq.open('GET', 'http://pt.dogi.us/?ip=' + json_info.host + '&port=' + json_info.port + '&skin=JSON', true);
	svjsonreq.onload = ()=>{
		data = JSON.parse(svjsonreq.responseText)
		data.players.sort((a, b)=>{
			let ascore = parseInt(a.score)
			let bscore = parseInt(b.score)
			if (a.ping == '0' && b.ping != '0') return 1
			if (b.ping == '0' && a.ping != '0') return -1
			if (ascore > bscore) return -1
			if (ascore < bscore) return 1
			return 0
		})
		clear_element(tracker_header)
		clear_element(tracker_players)
		clear_element(tracker_info)
		// ================
		let svname = generate_colored_span(data.serverInfo.servername)
		svname.className = 'tracker_svname'
		tracker_header.appendChild(svname)
		let svspan = wrap_span(data.serverIPAddress + ':' + data.serverPort, 'tracker_svinfo')
		tracker_header.appendChild(svspan)
		// ================
		if (!tracker_lv_i) {
			tracker_lv_i = document.createElement('img')
			tracker_lv.appendChild(tracker_lv_i)
			tracker_lv_i.onclick = ()=>{lvtrans()}
			tracker_lv_i.src = cur_lvs()
			tracker_lv_i.height = 180
			tracker_lv.style.backgroundImage = 'url("' + peek_lvs() + '")'
		}
		// ================
		let plyconta = true
		tracker_plyhead_count.innerHTML = '(' + data.players.length + '/' + data.info.sv_maxclients + ')'
		if (data.players.length) {data.players.forEach((ply)=>{
			tracker_players.style.textAlign = null
			tracker_players.style.justifyContent = null
			let plycont = document.createElement('div')
			plycont.className = 'tracker_plycont ' + (plyconta ? 'tracker_plycontaa' : 'tracker_plycontab')
			plyconta = !plyconta
			let plyspan = generate_colored_span(ply.name)
			plyspan.className = 'tracker_plyname'
			plycont.appendChild(plyspan)
			plycont.appendChild(wrap_span(ply.score, 'tracker_plyscore'))
			plycont.appendChild(wrap_span(ply.ping == '0' ? 'BOT' : ply.ping, 'tracker_plyping'))
			tracker_players.appendChild(plycont)
		})} else {
			tracker_players.style.justifyContent = 'center'
			tracker_players.style.textAlign = 'center'
			tracker_players.appendChild(wrap_span('NO PLAYERS ONLINE', 'tracker_noplayers'))
		}
		// ================
		let cimg = document.createElement('div')
		cimg.className = 'tracker_countryimg'
		cimg.style.backgroundImage = 'url(http://pt.dogi.us/vendor/components/flag-icon-css/flags/4x3/' + data.geoipCountryCode.toLowerCase() + '.svg)'
		let locspan = wrap_span('LOC: ', 'tracker_info_item_desc')
		locspan.marginLeft = 0
		tracker_info.appendChild(locspan)
		tracker_info.appendChild(cimg)
		
		tracker_info.appendChild(wrap_span('GAME: ', 'tracker_info_item_desc'))
		tracker_info.appendChild(wrap_span(data.info.gamename, 'tracker_info_item'))
		
		tracker_info.appendChild(wrap_span('MODE: ', 'tracker_info_item_desc'))
		tracker_info.appendChild(wrap_span(data.serverInfo.gametype, 'tracker_info_item'))
		
		tracker_info.appendChild(wrap_span('MAP: ', 'tracker_info_item_desc'))
		tracker_info.appendChild(wrap_span(data.info.mapname, 'tracker_info_item'))
		
		let param = document.createElement('a')
		param.className = 'tracker_info_item_desc tracker_info_param'
		param.appendChild(document.createTextNode('PARAM'))
		param.onclick = (evt)=>{showparams(evt)}
		tracker_info.appendChild(param)
		
		// ================
		if (!data.enableAutoRefresh) return
		let reftim = document.createElement('span')
		reftim.className = 'tracker_refresh_timer'
		let tim = parseInt(data.autoRefreshTimer)
		tracker_info.appendChild(reftim)
		timer_loop(tim + 1, reftim)
	}
	svjsonreq.send()
}

window.onhashchange = function(){
	setup_tracker()
}

document.addEventListener("DOMContentLoaded", function(event) {
	tracker_header = document.getElementById('tracker_header')
	tracker_players = document.getElementById('tracker_players')
	tracker_info = document.getElementById('tracker_info')
	tracker_lv = document.getElementById('tracker_lv')
	tracker_plyhead_count = document.getElementById('tracker_plyhead_count')
	lvTimeout = setTimeout(()=>{
		lvtrans()
	}, 3000)
	setup_tracker()
})
