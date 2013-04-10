$(document).ready(function() {
	$('#loading').hide()
	$('#scrollable').slimScroll({	
    	color: "goldenrod",
        height: '147px'
    });
	do_update();
});

function do_update() {
	$('#loading').show()
	$.getJSON('serverstatus.php', function(data) {
		$("#addr").text("(" + data["addr"] + ")");
		$("#hostname").text(data["cvars"]["sv_hostname"]);
		$("#map").text(data["cvars"]["mapname"]);
		$("#gamename span").text(data["cvars"]["gamename"]);
		$("#gamemode span").text((new Array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF"))[data["cvars"]["g_gametype"]]);
		$.each(data["players"], function(index, value) {
			$("#scrollable ul").append("<li>" + value["name"] + "</li>");
		});
		$('#loading').hide();
	})
}

function rcon() {
	console.log("rcon");
}

function cvars() {
	console.log("cvars");
}