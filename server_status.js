$(document).ready(function() {
	$('#scrollable').slimScroll({ color: "goldenrod", height: '147px' });
	do_update();
});

function do_update() {
	$.getJSON('serverstatus.php', function(data) {
		$("#addr").text("(" + data["addr"] + ")");
		$("#hostname").html(colorize(data["cvars"]["sv_hostname"]));
		$("#map").html(colorize(data["cvars"]["mapname"]));
		$("#gamename").find("span").text(data["cvars"]["gamename"]);
		$("#gamemode").find("span").text((new Array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF"))[data["cvars"]["g_gametype"]]);
		$.each(data["players"], function(index, value) {
			$("#scrollable ul").append("<li>" + colorize(value["name"]) + "</li>");
		});
	});
}

function colorize(str) {
	return "<span class='color7'>" + str.replace(/\^(\d)/g, "</span><span class='color$1'>") + "</span>";
}

function rcon() {
	console.log("rcon");
}

function cvars() {
	console.log("cvars");
}