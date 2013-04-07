<html><head><link rel="stylesheet" href="style.css" type="text/css" /><title>lame!</title></head><body bgcolor="#DDDDDD">
			<script language="javascript" type="text/javascript"><!--
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
					shot++;
					if(shot > 3) shot = 1;
				}
			}
			opac -= 0.1;
			document.getElementById("hs").style.opacity = opac;
			setTimeout("fadels()", 100);
		}

		function param_window()
		{
			mywindow = window.open("param.php", "mywindow", "location=1,status=1,scrollbars=1,  width=540,height=600");
			mywindow.moveTo(0, 0);
		}
		function rcon_window()
		{
			mywindow = window.open("rcon.php", "mywindow", "location=1,status=1,scrollbars=1,  width=600,height=300");
			mywindow.moveTo(0, 0);
		}
		//--></script>
		<table border="0" cellspacing="0" cellpadding="0" width="450" height="200" bgcolor="#000000" background="ui/full.jpg" class="text">
		<tr height="200"><td valign="top" width="244"><div class="urcorner"></div>
		<table border="0" cellspacing="0" cellpadding="0" class="text"><tr height="164"><td><table cellspacing="0" cellpadding="0" class="text" border="0">
		<tr height="22"><td width="30"></td>
		<td colspan="3" valign="bottom" class="serverName"><span class="color7">*Jedi*</span></td></tr>

		<tr height="19"><td></td><td colspan="3" class="gameTitle" valign="middle">&nbsp;Jedi Academy</td></tr>
		
		<tr height="14" class="nameScorePing"><td></td><td valign="bottom" width="138">Name</td><td valign="bottom" width="38">Score</td><td valign="bottom" width="38">Ping</td></tr>

		<tr height="105"><td valign="top"></td><td colspan="3">
		<iframe src="playerlist2.php" width="211" height="105" scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" allowtransparency="true" class="text">
		</iframe></td></tr>


		<tr height="36"><td></td><td colspan="3"><table border="0" cellspacing="0" cellpadding="0"><tr>
		<td width="116"><table cellspacing="0" cellpadding="0" class="players" border="0"><tr height="5"><td></td></tr><tr><td>Players: 2/8</td></tr></table></td>
		<td><table border="0" cellspacing="0" cellpadding="0" class="players"><tr height="4"><td colspan="3"></td></tr>
		<tr height="33"><td width="2"><img src="ui/leftborderline.gif" border="0" height="33" width="2" /></td><td width="51" valign="top"><a href="javascript:rcon_window()" onmouseover="rcon.src='ui/rcon_l.gif';" onmousemove="rcon.src='ui/rcon_l.gif';" onmouseout="rcon.src='ui/rcon.gif';" onmousedown="rcon.src='ui/rcon_c.gif';"><img name="rcon" src="ui/rcon.gif" border="0" /></a></td>
		<td width="51" valign="top"><a href="javascript:param_window()" onmouseover="param.src='ui/param_l.gif';" onmousemove="param.src='ui/param_l.gif';" onmouseout="param.src='ui/param.gif';" onmousedown="param.src='ui/param_c.gif';"><img name="param" src="ui/param.gif" border="0" /></a></td></tr>
		</table></td></tr></table></td></tr></table></td></tr></table></td><td valign="top" width="6"></td>
		<td valign="top" width="200">
		<style>.ls1{background: url("levelshots/mp/ffa5_1.jpg");}
.ls2{background: url("levelshots/mp/ffa5_2.jpg");}
.ls3{background: url("levelshots/mp/ffa5_3.jpg");}

		.corner{height:150px;absolute;top:0;right:0;}
		.urcorner{background:url("ui/corner-tr.gif");height:150px;position:absolute;top:0;right:0;}
		</style>
		<table border="0" cellspacing="0" cellpadding="0" class="text"><tr height="150">
		<td valign="top" id="ls" class="ls1"><table border="0" cellspacing="0" cellpadding="0"><tr height="150"><td width="200"><table border="0" cellspacing="0" cellpadding="0" background="ui/corner-tr.gif"><tr height="150"><td width="200" id="hs" class="ls1"><img src="ui/corner-tr.gif" height="150" width="200" /></td></tr></table></td></tr></table></td>
		</tr><tr height="50"><td valign="top">
		<table height="50" border="0" cellspacing="0" cellpadding="0" class="info"><tr height="3"><td valign="top" colspan="2"></td></tr>
		<tr height="15"><td width="88">Gametype: FFA</td>
		<td width="106">Map: <span class="color7">mp/ffa5</span></td></tr>
		<tr height="15"><td colspan="2">Mod Name: basejka</td></tr>
		<tr height="15"><td colspan="2"><table class="info" cellpadding="0" cellspacing="0" border="0"><tr><td>Server IP: 98.192.82.137:29070</td><td width="6" background="ui/blinker.gif"></td></tr></table></td></tr>
</table></td></tr></table></td></tr></table></body></html>
		<script language="javascript" type="text/javascript"><!-- 
		fadels();
		//--></script><img src="levelshots/mp/ffa5_1.jpg" class="hiddenPic"><img src="levelshots/mp/ffa5_2.jpg" class="hiddenPic"><img src="levelshots/mp/ffa5_3.jpg" class="hiddenPic"><img src="ui/param.gif" class="hiddenPic" /><img src="ui/param_c.gif" class="hiddenPic" /><img src="ui/param_l.gif" class="hiddenPic" /><img src="ui/rcon.gif" class="hiddenPic" /><img src="ui/rcon_c.gif" class="hiddenPic" /><img src="ui/rcon_l.gif" class="hiddenPic" />