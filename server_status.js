$(document).ready(function() {
	$('#loading').hide()
	$('#scrollable').slimScroll({	
    	color: "goldenrod",
        height: '147px'
    });
	do_update();
	
	/*$.getJSON('serverstatus.php', function(data) {
		var items = [];
		$.each(data, function(key, val) {
			items.push('<li id="' + key + '">' + val + '</li>');
		});
		$('<ul/>', {
			'class': 'my-new-list',
			html: items.join('')
		}).appendTo('body');
		});*/
});

function do_update() {
	$('#loading').show()
	$.getJSON('serverstatus.php', function(data) {
		console.log(data);
		$('#loading').hide();
	})
}

function rcon() {
	console.log("rcon");
}

function param() {
	console.log("param");
}