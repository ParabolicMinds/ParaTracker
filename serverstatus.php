<?php
// Load the existing server snapshot, update it if it is too old.
$server_status = file_get_contents("server_status.json");
$parsed = json_decode($server_status, true);
if (($parsed["updated"] + 5) < time()) do_update();

// Send the snapshot to the browser.
header('Content-Type: application/json');
echo $server_status;

function do_update() {

	// Send the getstatus command to the server and awat a response.
	// If the server
	$server_data = explode(chr(0x3A), trim(file_get_contents("server_addr.txt")));
	$fp = fsockopen("udp://" . $server_data[0], $server_data[1], $errno, $errstr, 30);
	fwrite($fp, str_repeat(chr(255),4) . "getstatus\n");
	stream_set_timeout($fp, 2);
	while (($char = fgetc($fp)) !== false) $s .= $char;
	fclose($fp);

	if(strlen($s)) {

		// Start splitting up the server response into data structures
		$sections = explode(chr(0x0A), $s);
		foreach(array_chunk(array_slice(preg_split('_[\\\]_', $sections[1]), 1), 2) as $cvar) {
			$result["cvars"][$cvar[0]] = $cvar[1]; 
		}
		array_shift($sections, 4);
		foreach(array_filter(array_slice($sections, 2)) as $index=>$player) {
			$player_data = preg_split('_["]_', $player);
			$player_numbers = explode(" ", $player_data[0]);
			$result["players"][$index] = array("score" => $player_numbers[0], "ping" => $player_numbers[1], "name" => $player_data[1]);
		}

		// Add the timestamp for this new data.
		$result["addr"] = $server_data[0] . ":" . $server_data[1];
		$result["updated"] = time();

		// Write out to the json file
		file_put_contents("server_status.json", json_encode($result, JSON_PRETTY_PRINT));
	}
}
?>
