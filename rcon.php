<html><head><link rel="stylesheet" href="style.css" type="text/css" /><title>RCON Console</title></head><body class="rcon_page"><center>
<?php
echo '<form action="rcon.php" method="post"><table><tr><td>Password: <input type="password" name="password"';
if (isset($_POST["password"])) echo ' value="' . $_POST["password"] . '" ';
echo '/></td><td>Command: <input type="text" ';

if (isset($_POST["command"])) echo ' value="' . $_POST["command"] . '" ';

echo ' name="command" /></td><td><input type="submit" value="Submit" /></form></td><td width="8"></td></tr><tr height="7"><td colspan="4"></td></tr></table>';
if (isset($_POST["password"]) && isset($_POST["command"])) {
	if (($_POST["password"] != "") && ($_POST["command"] != "")) {
		echo '<table border="1" width="570" ><tr><td><p class="serverResponse1">';
		$server_data = preg_split('_[' . chr(0x3A) . ']_', trim(file_get_contents("server_addr.txt")));
		$server_addr = $server_data[0];
		$server_port = $server_data[1];
		$fp = fsockopen("udp://" . $server_addr, $server_port, $errno, $errstr, 30);
		fwrite($fp, str_repeat(chr(255),4) . 'rcon ' . $_POST["password"] . ' ' . $_POST["command"]);
		$s='';
		stream_set_timeout($fp, 2);
		while (false !== ($char = fgetc($fp))) {
			$s .= $char;
		}
		fclose($fp);

		if(strlen($s))
		{
			$s= str_replace('ÿÿÿÿprint' . chr(0x0A), '', $s);
			$s = str_replace(chr(0x20), '&nbsp;', $s);
			$s = str_replace(chr(0x0A), '<br>', $s);
			echo $s;
		}
		echo '</p></td></tr></table>';
	}
}
?>		
</center></body></html>