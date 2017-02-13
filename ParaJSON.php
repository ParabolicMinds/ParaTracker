<?php

ob_start();

$safeToExecuteParaFunc = "1";
include 'ParaFunc.php';

checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $refreshTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $filterOffendingServerNameSymbols, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dynamicTrackerEnabled);
$dump = file_get_contents("info/" . $dynamicIPAddressPath . "serverDump.txt");
$dump = stringValidator( $dump, "", "");
$dump_split = explode("\n", $dump);
array_pop($dump_split);
$server_info = explode('\\', $dump_split[1]);
array_shift($server_info);
$json = "{\"version\":\"". versionNumber() . "\",\"info\":{";
for ($i = 0; $i < count($server_info); $i += 2) {
  if ($i != 0) { $json .= ",";}
  $json .= '"' . $server_info[$i] . '":"' . $server_info[$i+1] . '"';
}
$json .= "},\"players\":[";
for ($i = 2; $i < count($dump_split); $i ++) {
  $player_split = explode(' ', $dump_split[$i], 3);
  if ($i != 2) { $json .= ",";}
  $json .= "{\"name\":" . $player_split[2] . ",";
  $json .= "\"score\":" . $player_split[0] . ",";
  $json .= "\"ping\":" . $player_split[1] . "}";
}
$json .= "]}";

ob_clean();
ob_end_flush();

header("Content-Type: application/json");
echo $json

?>
