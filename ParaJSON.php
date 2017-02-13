﻿<?php

header("Content-Type: application/json");
ob_start();

$safeToExecuteParaFunc = "1";
include 'ParaFunc.php';

checkForAndDoUpdateIfNecessary($serverIPAddress, $serverPort, $dynamicIPAddressPath, $floodProtectTimeout, $connectionTimeout, $refreshTimeout, $disableFrameBorder, $fadeLevelshots, $levelshotDisplayTime, $levelshotTransitionTime, $levelshotFPS, $maximumLevelshots, $levelshotFolder, $filterOffendingServerNameSymbols, $gameName, $noPlayersOnlineMessage, $enableAutoRefresh, $autoRefreshTimer, $maximumServerInfoSize, $RConEnable, $RConMaximumMessageSize, $RConFloodProtect, $RConLogSize, $newWindowSnapToCorner, $dynamicTrackerEnabled);
$dump = file_get_contents("info/" . $dynamicIPAddressPath . "serverDump.txt");
$dump_split = explode("\n", $dump);
array_pop($dump_split);
$server_info = explode('\\', $dump_split[1]);
array_shift($server_info);
$json = "{\"version\":\"". versionNumber() . "\",\"info\":{";
for ($i = 0; $i < count($server_info); $i += 2) {
  if ($i != 0) { $json .= ",";}
  $json .= '"' . stringValidator($server_info[$i], "", "") . '":"' . stringValidator($server_info[$i+1], "", "") . '"';
}
$json .= "},\"players\":[";
for ($i = 2; $i < count($dump_split); $i ++) {
  $player_split = explode(' ', $dump_split[$i], 3);
  if ($i != 2) { $json .= ",";}
  $json .= "{\"name\":\"" . stringValidator(trim($player_split[2],"\""), "", "") . "\",";
  $json .= "\"score\":" . stringValidator($player_split[0], "", "") . ",";
  $json .= "\"ping\":" . stringValidator($player_split[1], "", "") . "}";
}
$json .= "]}";

ob_clean();
ob_end_flush();

echo $json

?>
