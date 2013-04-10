<?php
$server_data = explode(chr(0x3A), trim(file_get_contents("server_addr.txt")));
	$fp = fsockopen("udp://" . $server_data[0], $server_data[1], $errno, $errstr, 30);
	fwrite($fp, str_repeat(chr(255),4) . "getstatus\n");
	stream_set_timeout($fp, 2);
	while (($char = fgetc($fp)) !== false) $s .= $char;
	fclose($fp);
echo $s;

?>
