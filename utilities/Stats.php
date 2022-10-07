<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


ob_start();

$calledFromAnalytics = 1;

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//We are in the utilities folder, so we have to back out one
chdir("../");

//ParaFunc.php MUST exist, or the page must terminate!
if (file_exists("ParaFunc.php"))
{
    include_once 'ParaFunc.php';
}
else
{
    echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
    exit();
}

if(analyticsFrontEndEnabled == "0")
{
    displayError("Analytics front end is disabled!<br>Analytics must be enabled in paraConfig.php.", $lastRefreshTime, "");
}

//If we're parsing analytics data, we need to validate the incoming info
$startTime = 0;
$endTime = 0;
$JSONReload = 0;
$mode = 0;

if(isset($_GET["startTime"]))
{
    $startTime = intval($_GET["startTime"]);
}
if(isset($_GET["endTime"]))
{
    $endTime = intval($_GET["endTime"]);
}

if(isset($_GET["JSONReload"]) && booleanValidator($_GET["JSONReload"], 0) == "1")
{
    $JSONReload = 1;
}

//$mode = round(numericValidator($mode, 0, 127, 0));
//Due to some changes, mode is now forced to 127
$mode = 127;

if($startTime == 0)
{
    $startTime = time() - 604800;
}

if($endTime == 0)
{
    $endTime = time();
}

$endTime = numericValidator($endTime, 0, time(), time());
$startTime = numericValidator($startTime, 0, $endTime, time() - 604800);
$endTime = numericValidator($endTime, $startTime, time(), time());

// We must prevent the users from requesting 10 years of data at a time.
// Not only will it be useless to view, but it will bog down the web server.
// This MUST match the value in ParaUtil.js
// 4838400 seconds is 8 weeks.
// 9676800 seconds is 16 weeks.
// 19353600 seconds is 32 weeks.
$maxTime = 19353600;

$startTime = numericValidator($startTime, $startTime, $startTime + $maxTime, $endTime - $maxTime);

define("startTime", $startTime);
define("endTime", $endTime);
define("JSONReload", $JSONReload);
define("mode", $mode);

if(JSONReload != 1 && !isset($_GET["ip"]) && !isset($_GET["port"]))
{
    renderServerAddressPage();
    exit();
}

function renderServerAddressPage()
{
    $output = htmlDeclarations('Analytics', '../');
    $output .= '<script>runOnStartup = 0</script>';
    $output .= '<script src="../js/ParaUtil.js"></script>';
    $output .= '</head><body class="analyticsAddressPage">';
    $output .= '
    <br>
    <form class="centerElement" action="' . basename($_SERVER['PHP_SELF']) . '" method="get">
    <input id="ip" class="analyticsAddressForm" size="45" type="text" name="ip" placeholder="Server Address" /><br>
    <input id="port" class="analyticsAddressForm" size="45" type="text" name="port" placeholder="Server Port" /><br>
    <input type="submit" class="analyticsAddressForm" />
    </form>
    ';

    echo "-->" . $output;
}

function renderAnalyticsPage($serverIPAddress, $serverPort)
{
    $output = htmlDeclarations('Analytics', '../');
    $output .= '<script src="../js/ParaUtil.js"></script>';
    $output .= '<script>analyticsData = ' . json_encode(getData($serverIPAddress, $serverPort)) . '</script>';
    $output .= '</head><body onhashchange="updateInfoFromHash()" class="centerPage fullHeightAndWidth">';
    $output .= '<div id="analyticsContainingFrame" class="">

<div id="analyticsWrapper">
<div id="analyticsDataFrame"></div>

<div id="analyticsControlsFrame">

<div class="topRowFlex">
<div class="topControlBarRow">
Server Address:<br><input type="text" size="25" class="serverAddressFields" name="serverAddressField" id="serverAddressField" onchange="updateServerAddress()">
</div>
<div class="topControlBarRow">
Server Port:<br><input type="text" size="9" maxlength="5" class="serverAddressFields" name="serverPortField" id="serverPortField" onchange="updateServerAddress()">
</div>

<div class="topControlBarRow">
Start time (Local time only):<br><input type="datetime-local" size="32" class="timeField" name="startTimeField" id="startTimeField" onchange="updateStartTime(\'URLStartTime\', this.value)">
</div>
<div class="topControlBarRow">
End time (Local time only):<br><input type="datetime-local" size="32" class="timeField" name="endTimeField" id="endTimeField" onchange="updateEndTime(\'URLEndTime\', this.value)">
</div>
<div class="topControlBarRow resetTimeFieldSize">
<br>
<button type="button" class="resetTimeFields" id="resetTimeFields" onclick="resetTimeFieldValues()">Reset Times</button>
</div>
</div>



<div class="bottomRowFlex">
<div class="bottomControlBarRow">
<p><input type="radio" class="timezoneSetting radioButton" name="displayTimesInUTC" id="displayTimesInUTCFalse" onchange="updateHashFromControls(\'displayTimesInUTC\', false)" checked>
Display times in local time</p>

<p><input type="radio" class="timezoneSetting radioButton" name="displayTimesInUTC" id="displayTimesInUTCTrue" onchange="updateHashFromControls(\'displayTimesInUTC\', true)">
Display times in UTC</p>
</div>

<div class="bottomControlBarRow">
<p><input type="radio" class="twelveHourClockSetting radioButton" name="twelveHourClockSetting" id="twelveHourClockSettingTrue" onchange="updateHashFromControls(\'twelveHourClockMode\', true)" checked>
Display times in 12-hour format</p>

<p><input type="radio" class="twelveHourClockSetting radioButton" name="twelveHourClockSetting" id="twelveHourClockSettingFalse" onchange="updateHashFromControls(\'twelveHourClockMode\', false)">
Display times in 24-hour format</p>
</div>

<div class="bottomControlBarRow">
<p><input type="radio" class="colorizeBlocks radioButton" name="colorizeBlocks" id="colorizeBlocksTrue" onchange="updateHashFromControls(\'colorizeBlocks\', true)" checked>
Color blocks by data values</p>

<p><input type="radio" class="colorizeBlocks radioButton" name="colorizeBlocks" id="colorizeBlocksFalse" onchange="updateHashFromControls(\'colorizeBlocks\', false)">
Color blocks by data type</p>
</div>

<div class="bottomControlBarRow">
<p><input type="radio" class="sortMapsBy radioButton" name="sortMapsBy" id="sortMapsByFalse" onchange="updateHashFromControls(\'sortMapsBy\', false)" checked>
Sort maps by players per map-hour</p>

<p><input type="radio" class="sortMapsBy radioButton" name="sortMapsBy" id="sortMapsByTrue" onchange="updateHashFromControls(\'sortMapsBy\', true)">
Sort maps by time played</p>
</div>
</div>

<div class="buttonRow">

<div class="hiddenStuff" id="hiddenCheckboxes">
<p><input type="checkbox" class="analyticsCheckbox" name="showOfflineTimes" id="showOfflineTimesCheckbox" onchange="updateHashFromControls(\'showOfflineTimes\', this.checked)" checked>
Show offline status</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showGameName" id="showGameNameCheckbox" onchange="updateHashFromControls(\'showGameName\', this.checked)" checked>
Show game name</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showServerName" id="showServerNameCheckbox" onchange="updateHashFromControls(\'showServerName\', this.checked)" checked>
Show server name</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showModName" id="showModNameCheckbox" onchange="updateHashFromControls(\'showModName\', this.checked)" checked>
Show mod name</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showGametype" id="showGametypeCheckbox" onchange="updateHashFromControls(\'showGametype\', this.checked)" checked>
Show gametype</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showHorizontalGridLines" id="showHorizontalGridLinesCheckbox" onchange="updateHashFromControls(\'showHorizontalGridLines\', this.checked)" checked>
Show horizontal grid lines</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showVerticalGridLines" id="showVerticalGridLinesCheckbox" onchange="updateHashFromControls(\'showVerticalGridLines\', this.checked)" checked>
Show vertical grid lines</p>

<p><input type="checkbox" class="analyticsCheckbox" name="showGridLabels" id="showGridLabelsCheckbox" onchange="updateHashFromControls(\'showGridLabels\', this.checked)" checked>
Show grid labels</p>
</div>

<div class="analyticsLogo"><span class="infoDivColor1">Para</span><span class="infoDivColor9">Tracker</span> <span class="infoDivColor4">Analytics</span></div>

<div id="showHideButton" onclick="showHideStuff(hiddenCheckboxes)">More Options
</div></div>

</div></div>

</div>
';


    $output .= '</body></html>';
    echo '-->' . $output;
    exit();
}

function exit_error($code) {
	http_response_code($code);
	exit();
}

function get_analytics_data($modeflags, $server_id, $start, $end) {

    if($modeflags == 0)
    {
        return null;
    }

	$do_uptime = $modeflags & (1 << 0);
	$do_gamename = $modeflags & (1 << 1);
	$do_hostname = $modeflags & (1 << 2);
	$do_mapname = $modeflags & (1 << 3);
	$do_modname = $modeflags & (1 << 4);
	$do_gametype = $modeflags & (1 << 5);
	$do_playercount = $modeflags & (1 << 6);

	global $pgCon;

	$query = "SELECT frame.entrydate AS entrydate";

	if ($do_uptime) $query .= ", CASE WHEN record_id IS NULL THEN FALSE ELSE TRUE END AS online";
	if ($do_gamename) $query .= ", analytics.gamename.name AS gamename";
	if ($do_hostname) $query .= ", analytics.hostname.name AS hostname";
	if ($do_mapname) $query .= ", analytics.mapname.name AS mapname";
	if ($do_modname) $query .= ", analytics.modname.name AS modname";
	if ($do_gametype) $query .= ", analytics.gametype.name AS gametype";
	if ($do_playercount) $query .= ", record.player_count";

	$query .= " FROM analytics.frame LEFT OUTER JOIN analytics.record ON record_id = record.id";
		
	if ($do_gamename) $query .= " LEFT OUTER JOIN analytics.gamename ON record.gamename_id = gamename.id";
	if ($do_hostname) $query .= " LEFT OUTER JOIN analytics.hostname ON record.hostname_id = hostname.id";
	if ($do_mapname) $query .= " LEFT OUTER JOIN analytics.mapname ON record.mapname_id = mapname.id";
	if ($do_modname) $query .= " LEFT OUTER JOIN analytics.modname ON record.modname_id = modname.id";
	if ($do_gametype) $query .= " LEFT OUTER JOIN analytics.gametype ON record.gametype_id = gametype.id";

	$query .= " WHERE server_id = $1 AND entrydate BETWEEN $2 AND $3 ORDER BY entrydate ASC";

	$record_fetch = pg_fetch_all(pg_query_params($pgCon, $query, array($server_id, date('Y-m-d H:i', $start), date('Y-m-d H:i', $end))));
	return $record_fetch;
}

function getData($serverIPAddress, $serverPort)
{
	$output = get_analytics_data(mode, server_id, startTime, endTime);

	if($output == null)
	{
	    return null;
	}

	$lasttime = NULL;
	$lastvalue = NULL;

	$times = array();
	$diff = 0;
	foreach ($output as &$row) {
		$curtime = strtotime($row['entrydate']);
		$timediff = $curtime - $diff;
		$diff = $curtime;
		array_push($times, $timediff);
	}

	$deltauptime = array();
	foreach ($output as &$row) {
		if (empty($deltauptime) || ($row['online'] != $lastvalue && !is_null($row['online']))) {
			$lastvalue = $row['online'];
			$lasttime = $row['entrydate'];
			array_push($deltauptime, array('date'=>strtotime($lasttime),'value'=>($lastvalue=='t')));
		}
	}

	$deltagame = array();
	foreach ($output as &$row) {
		if (empty($deltagame) || ($row['gamename'] != $lastvalue && !is_null($row['gamename']))) {
			$lastvalue = $row['gamename'];
			$lasttime = $row['entrydate'];
			array_push($deltagame, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}

	$deltahost = array();
	foreach ($output as &$row) {
		if (empty($deltahost) || ($row['hostname'] != $lastvalue && !is_null($row['hostname']))) {
			$lastvalue = $row['hostname'];
			$lasttime = $row['entrydate'];
			array_push($deltahost, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}

	$deltamap = array();
	foreach ($output as &$row) {
		if (empty($deltamap) || ($row['mapname'] != $lastvalue && !is_null($row['mapname']))) {
			$lastvalue = $row['mapname'];
			$lasttime = $row['entrydate'];
			array_push($deltamap, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}

	$deltamod = array();
	foreach ($output as &$row) {
		if (empty($deltamod) || ($row['modname'] != $lastvalue && !is_null($row['modname']))) {
			$lastvalue = $row['modname'];
			$lasttime = $row['entrydate'];
			array_push($deltamod, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}

	$deltagametype = array();
	foreach ($output as &$row) {
		if (empty($deltagametype) || ($row['gametype'] != $lastvalue && !is_null($row['gametype']))) {
			$lastvalue = $row['gametype'];
			$lasttime = $row['entrydate'];
			array_push($deltagametype, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}

	$deltaplayercount = array();
	foreach ($output as &$row) {
		if (empty($deltaplayercount) || ($row['player_count'] != $lastvalue && !is_null($row['player_count']))) {
			$lastvalue = $row['player_count'];
			$lasttime = $row['entrydate'];
			array_push($deltaplayercount, array('date'=>strtotime($lasttime),'value'=>$lastvalue));
		}
	}
    return array('refreshTimes'=>$times,'online'=>$deltauptime,'game'=>$deltagame,'hostname'=>$deltahost,'maps'=>$deltamap,'mod'=>$deltamod,'gametype'=>$deltagametype,'playercount'=>$deltaplayercount);
}

$server_id_fetch = pg_fetch_row(pg_query_params($pgCon, "SELECT id FROM tracker.server WHERE location = $1 AND port = $2", array(strtolower($serverIPAddress), $serverPort)));

if (empty($server_id_fetch))
{
    //No response was detected. Server must not have any info in the database.
    echo "--><h3>No data found for server";

    if(!empty($serverIPAddress) && !empty($serverPort))
    {
        echo " at '" . $serverIPAddress . ":" . $serverPort . "'";
    }
    echo "!<br>Check back later.</h3>";
    checkForAndDoUpdateIfNecessary($dynamicIPAddressPath);
    exit();
}
define("server_id", $server_id_fetch[0]);

if(JSONReload == 0)
{
    renderAnalyticsPage($serverIPAddress, $serverPort);
}

//Since we're giving a JSON response, we have to give the page a JSON header
header("Content-Type: application/json");

$jsondat = json_encode(getData($serverIPAddress, $serverPort));

ob_end_clean();

print_r($jsondat);

?>
