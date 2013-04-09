$(document).ready(function() {
				
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
	$.getJSON('serverstatus.php', function(data) {
		console.log(data);
	})
}

function next_page() {
	console.log("next");
}

function previous_page() {
	console.log("prev");
}

function rcon() {
	console.log("rcon");
}

function param() {
	console.log("param");
}