<?php
echo "<!--";

$refresh = 0;
if ((file_get_contents("time.txt") + 5) < time())
{
	file_put_contents("time.txt", time());
	do_update();
}
echo "-->";
include 'tracker_page.php';

function array_sort($a, $subkey, $direction) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	$direction == true ? arsort($b) : asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}

function colorize($string)
{
	$characters = preg_split('//', $string);
	$colorized_string = '<span class="color7">';
	
	for($i = 0; $i < count($characters)-1; $i++)
	{
		if(($characters[$i] == '^') && ($i < count($characters)-2) && (strpos(" 0123456789", $characters[$i+1]) != FALSE))
		{
			$colorized_string = $colorized_string . '</span><span class="color' . $characters[$i+1] . '">';
			$i ++;
		} else {
			$colorized_string = $colorized_string . $characters[$i];
		}
	}
	return $colorized_string . "</span>";
}

function do_update()
{
	$server_data = preg_split('_[' . chr(0x3A) . ']_', trim(file_get_contents("server_addr.txt")));
	$server_addr = $server_data[0];
	$server_port = $server_data[1];
	$fp = fsockopen("udp://" . $server_addr, $server_port, $errno, $errstr, 30);
	fwrite($fp, str_repeat(chr(255),4) . "getstatus\n");
	$s='';
	stream_set_timeout($fp, 2);
	while (false !== ($char = fgetc($fp))) {
		$s .= $char;
	}
	fclose($fp);
	if(strlen($s))
	{
		$sections = preg_split('_[' . chr(0x0A) . ']_', $s);
		$cvars_array = preg_split('_[\\\]_', $sections[1]);
		$j=0;
		for($i = 1; $i < count($cvars_array)-1; $i += 2)
		{
			$cvar_name = str_replace(array("\n", "\r"), '', $cvars_array[$i]);
			$cvar_value = str_replace(array("\n", "\r"), '', $cvars_array[$i+1]);
			$cvar_array_single[$j++] = array("name" => $cvar_name, "value" => $cvar_value);
			$cvars_hash[$cvar_name] = $cvar_value; 
		}
		$cvar_array_single = array_sort($cvar_array_single, "name", false);
		$buf='<html><head><link rel="stylesheet" href="style.css" type="text/css" /><title>Server Cvars</title></head>
		<body class="cvars_page">
		<span class="heading">&nbsp;Server Cvars</span><hr><br /><center><table border=1><tr class="cvars_titleRow"><td>Name</td><td>Value</td></tr>';
		$c=1;
		foreach($cvar_array_single as $cvar) {
			$buf .= '<tr height="20" class="cvars_row' . $c . '"><td>' . $cvar['name'] . '</td><td>' . $cvar['value'];
			if ((($cvar['name'] == 'sv_hostname') || ($cvar['name'] == 'gamename') || ($cvar['name'] == 'mapname')) && ((strpos(colorize($cvar['value']), $cvar['value'])) == FALSE)) {
				$buf .= ' (' . colorize($cvar['value']) . ')';
			}
			$buf .= '</td></tr>';
			$c++;
			if($c > 2) $c = 1;
		}
		$buf .= '</table></center><hr><h6>Copyright &copy 1837 Rick Astley. No rights reserved. Void where prohibited. Your mileage may vary. Please drink and drive responsibly.</h6></body></html>';
		file_put_contents('param.php', $buf);
		$j=0;
		for($i = 2; $i < count($sections)-1; $i++)
		{
			$player_data_split = preg_split('_["]_', $sections[$i]);
			$player_numbers_split = preg_split('_[ ]_', $player_data_split[0]);
			$player_array[$i] = array("score" => $player_numbers_split[0], "ping" => $player_numbers_split[1], "name" => $player_data_split[1]);
			$j++;
		}
		$buf = '<html><head><link rel="stylesheet" href="style.css" type="text/css" />

<script language="JavaScript"> 
<!--
image1 = new Image();
image1.src = "images/tracker/param.gif";
image2 = new Image();
image2.src = "images/tracker/param_c.gif";
image3 = new Image();
image3.src = "images/tracker/param_l.gif";
image4 = new Image();
image4.src = "images/tracker/rcon.gif";
image5 = new Image();
image5.src = "images/tracker/rcon_c.gif";
image6 = new Image();
image6.src = "images/tracker/rcon_l.gif";
-->
</script>
		</head><body bgcolor="black"><table border="0" cellspacing="0" cellpadding="0" class="text">';
		$player_count = 0;
		if($j>0)
		{
			$player_array = array_sort($player_array, "score", true);
			$c = 1;
			foreach($player_array as &$player)
			{
				$player_name = str_replace(array("\n", "\r"), '', $player["name"]);
				if (strlen($player_name) > 20)
				{
					$l=0;
					for($k=0; ($l < 20) && ($k < strlen($player_name)); $k++)
					{
						if(($player_name[$k] == '^') && (strpos("0123456789", $player_name[$k+1]) != FALSE)) {
							$k++;
						} else {
							$l++;
						}
					}
				} else {
					$k=20;
				}
				$player_count++;
				$buf .= '<tr height="20" class="playerList' . $c . '"><td width="2"></td><td valign="middle" width="154">'. colorize(substr($player_name,0,$k));
				$buf .= '</td><td valign="middle" align="center" width="46">' . $player["score"] . '</td><td valign="middle" align="right" width="30">' . $player["ping"] . '</td><td width="1"></td></tr>';
				$c++;
				if($c > 2) $c = 1;
			}
			$buf .= '<tr height="';
			if($player_count < 6)
			{
			$buf .= (6-$player_count) * 20;
			}
			$buf .= '"><td colspan="4"></td></tr>';
		} else {
			$buf .= '<tr><td colspan="4">&nbsp;No players online.</td>';
		}
		$buf .= '</table></body></html>';
		file_put_contents('playerlist2.php', $buf);
		$gametypes = array("FFA", "", "", "Duel", "Power Duel", "", "Team FFA", "Siege", "CTF");
		$buf='';
		
		if(file_exists('images/levelshots/' . str_replace('/','_',$cvars_hash["mapname"]) . '_' . $i . '.jpg'))
		{
		for($i=1;file_exists('images/levelshots/' . str_replace('/','_',$cvars_hash["mapname"]) . '_' . $i . '.jpg');$i++)
			{
				$buf .= '.ls' . $i . '{background: url("images/levelshots/' . str_replace('/',"_",$cvars_hash["mapname"]) . '_' . $i . '.jpg");}' . chr(0x0A);
			}
		}
		else
		{
			$i = 1;
			$buf .= '.ls1{background: url("images/levelshots/missing.gif");}' . chr(0x0A);
		}
			$buf2 = '<html><head><link rel="stylesheet" href="style.css" type="text/css" /><title>lame!</title></head><body bgcolor="#DDDDDD">
			<script language="javascript" type="text/javascript"><!--
		
		
		function FadeInImage(foregroundID,newImage,backgroundID)
		{
			var foreground=document.getElementById(foregroundID);
  			if (backgroundID)
  			{
				var background=document.getElementById(backgroundID);
				if (background)
				{
					background.style.backgroundImage = "url(" + foreground.src + ")";
				}
 			}
 			SetOpacity(foreground,0);
			foreground.src = newImage;
			if (foreground.timer) window.clearTimeout(foreground.timer); 
			var startMS = (new Date()).getTime();
			foreground.timer = window.setTimeout("ChangeOpacity(' . chr(39) . '" + foregroundID + "' . chr(39) . ',1000," + startMS + ",0,100)",10);
		}

		var shot = 1;
		var opac=1;
		var mode=0;
		var count=0;
		function fadels()
		{
			count++;
			if (mode == 0) {
				if (count > 10) {
					document.getElementById("hs").className = document.getElementById("ls").className;
					document.getElementById("ls").className = "ls" + shot;
					document.getElementById("hs").style.opacity = 1;
					opac = 1;
					count=0;
					mode=1;
				} else {
					opac -= 0.1;
				}
			} else {
				if (count > 20) {
					count=0;
					mode=0;
				}
			}
			opac -= 0.1;
			document.getElementById("hs").style.opacity = opac;
			shot++;
			if(shot > ' . ($i-1) . ') shot = 1;
			setTimeout("fadels()", 100);
		}
		function param_window()
		{
			mywindow = window.open("param.php", "mywindow", "location=1,status=1,scrollbars=1,  width=500,height=600");
			mywindow.moveTo(0, 0);
		}
		function rcon_window()
		{
			mywindow = window.open("rcon.php", "mywindow", "location=1,status=1,scrollbars=1,  width=300,height=300");
			mywindow.moveTo(0, 0);
		}
		//--></script>
		<table border="0" cellspacing="0" cellpadding="0" width="450" height="200" bgcolor="#000000" class="text">
		<tr height="200"><td valign="top" width="244" background="images/tracker/leftside.jpg"><div class="urcorner"></div>
		<table border="0" cellspacing="0" cellpadding="0" class="text"><tr height="22"><td width="30"></td>
		<td colspan="3" valign="bottom" class="serverName">' . colorize($cvars_hash["sv_hostname"]) . '</td></tr>
		<tr height="18"><td></td><td valign="bottom" width="138">&nbsp;Name</td><td valign="bottom" width="38">Score</td>
		<td valign="bottom" width="38">Ping</td></tr><tr height="120"><td valign="top"></td><td colspan="3">
		<iframe src="playerlist2.php" width="211" height="115" scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" allowtransparency="true" class="text">
		</iframe></td></tr><tr height="36"><td></td><td colspan="3"><table border="0" cellspacing="0" cellpadding="0"><tr>
		<td width="116"><table cellspacing="0" cellpadding="0" class="players" border="0"><tr height="5"><td></td></tr><tr><td>Players: ' . $player_count . '/' . $cvars_hash["sv_maxclients"] . '</td></tr></table></td>
		<td><table border="0" cellspacing="0" cellpadding="0" class="players"><tr height="5"><td colspan="3"></td></tr>
		<tr><td width="1"><img src="images/tracker/leftborderline.gif" border="0" /></td><td width="51" valign="top"><a href="javascript:rcon_window()" onmouseover="rcon.src=\'images/tracker/rcon_l.gif\';" onmousemove="rcon.src=\'images/tracker/rcon_l.gif\';" onmouseout="rcon.src=\'images/tracker/rcon.gif\';" onmousedown="rcon.src=\'images/tracker/rcon_c.gif\';"><img name="rcon" src="images/tracker/rcon.gif" border="0" /></a></td>
		<td width="51" valign="top"><a href="javascript:param_window()" onmouseover="param.src=\'images/tracker/param_l.gif\';" onmousemove="param.src=\'images/tracker/param_l.gif\';" onmouseout="param.src=\'images/tracker/param.gif\';" onmousedown="param.src=\'images/tracker/param_c.gif\';"><img name="param" src="images/tracker/param.gif" border="0" /></a></td></tr>
		</table></td></tr></table></td></tr></table></td><td valign="top" width="6" background="images/tracker/center.jpg"></td>
		<td valign="top" width="200" background="images/tracker/rightside.jpg">
		<style>' . $buf . '
		.corner{height:150px;absolute;top:0;right:0;}
		.urcorner{background:url("images/tracker/corner-tr.gif");height:150px;position:absolute;top:0;right:0;}
		</style>
		<table border="0" cellspacing="0" cellpadding="0" class="text"><tr height="150">
		<td valign="top" id="ls" class="ls1"><table border="0" cellspacing="0" cellpadding="0"><tr height="150"><td width="200"><table border="0" cellspacing="0" cellpadding="0" background="images/tracker/corner-tr.gif"><tr height="150"><td width="200" id="hs" class="ls1"><img src="images/tracker/corner-tr.gif" height="150" width="200" /></td></tr></table></td></tr></table></td>
		</tr><tr height="50"><td valign="top">
		<table height="50" border="0" cellspacing="0" cellpadding="0" class="info"><tr height="3"><td valign="top" colspan="2"></td></tr>
		<tr height="14"><td width="88">Gametype: ' . $gametypes[$cvars_hash["g_gametype"]] . '</td>
		<td width="106">Map: ' . colorize($cvars_hash["mapname"]) . '</td></tr>
		<tr height="14"><td colspan="2">Mod Name: ' . $cvars_hash["gamename"] . '</td></tr>
		<tr height="14"><td colspan="2"><table class="info" cellpadding="0" cellspacing="0" border="0"><tr><td>Server IP: ' . $server_addr . ':' . $server_port . '</td><td width="6" background="images/tracker/blinker.gif"></td></tr></table></td></tr>
		<tr height="5"><td colspan="3"></td></tr></table></td></tr></table></td></tr></table></body></html>
		<script language="javascript" type="text/javascript"><!-- 
		fadels();
		//--></script>';
		file_put_contents('tracker_page.php', $buf2);
	} else {
		$buf =  '<html><head><link rel="stylesheet" href="style.css" type="text/css" /></head><body><table cellspacing="0" cellpadding="0" border="0" width="450" height="200" background="images/tracker/blank.jpg"><tr><td valign="top">
<table class="noConnection" cellspacing="0" cellpadding="0" border="0" width="244"><tr><td width="7"></td><td valign="top" width="237" align="center"><br><br><br>Could not connect<br />to server!<br /><br /><table class="noConnection" cellspacing="0" cellpadding="0" border="0"><tr><td>' . $server_addr . ":" . $server_port . '</td><td width="9" background="images/tracker/blinker2.gif"></td></tr></table></td></tr></table></td></tr></table></body></html>';

		file_put_contents('tracker_page.php', $buf);
	}
}

?>
