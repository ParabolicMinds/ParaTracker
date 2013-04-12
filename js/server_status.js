var current_map = "";
var slideshow_started = false;

$(document).ready(function() {
	$('#scrollable').slimScroll({ color: "#DAA520", height: '147px' });
	do_update();
	setInterval(do_update, 60000); // Get an update every 60 seconds
});

function do_update() {
	$.getJSON('serverstatus.php', function(data) {
		$("#addr").text("(" + data["addr"] + ")");
		$("#hostname").html(colorize(data["cvars"]["sv_hostname"]));
		$("#map").html(colorize(data["cvars"]["mapname"]));
		$("#gamename").find("span").text(data["cvars"]["gamename"]);
		$("#gamemode").find("span").text((new Array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF"))[data["cvars"]["g_gametype"]]);
		$("#scrollable ul").html(data["players"].map(function(a) { return "<li>" + colorize(a["name"]) + "</li>"; }).join(""));
		$("#cvars_popup table").html(
			Object.keys(data["cvars"]).map(
				function(a) {
					return "<tr><td>" + a + "</td><td>" + data["cvars"][a] + "</td></tr>";
				}).join(""));
		
		if (data["cvars"]["mapname"] != current_map) {
			current_map = data["cvars"]["mapname"];
			$(".slideshow").html(data["levelshots"].map(function(a) { return "<img src='" + a + "'>"}).join(""));
		}
		if (slideshow_started == false) {
			$('.slideshow').cycle({ fx: 'fade' /*, speed: 300, timeout: 2000 */ });
			slideshow_started = true;	
		}
	});
}

function colorize(str) {
	return "<span class='color7'>" + str.replace(/\^(\d)/g, "</span><span class='color$1'>") + "</span>";
}

function rcon() {
	$("#rcon_popup").show();
}

function rcon_close() {
	$("#rcon_popup").hide();
}

function cvars() {
	$("#cvars_popup").show();
}

function cvars_close() {
	$("#cvars_popup").hide();
}