<?php

$calledFromElsewhere = 1;
$safeToExecuteParaFunc = 1;
echo "<!--";
include 'ParaFunc.php';
echo "-->";
echo htmlDeclarations("", "");

?>

<body class="mapReqListPageStyle">
    <div id="entrytop">
        <div id="entryheader">
            <?php
                if ($admin) echo '<span class="entryfield entrydeletefield entryheaderfield"></span>';
            ?>
            <span class="entryfield entrygamefield entryheaderfield">Game</span>
            <span class="entryfield entrybspfield entryheaderfield">BSP</span>
            <span class="entryfield entrylinkfield entryheaderfield">Download</span>
        </div>
        <div id="entrybody">
        <?php
            $mapreqs = pg_fetch_all(pg_query($pgCon, 'SELECT * FROM mapreq ORDER BY game_name ASC, bsp_name ASC'));
            $curbg = false;
            if (!empty($mapreqs)) foreach ($mapreqs as $entry) {
                $link = $entry['dl_link'];
                if (substr($link, 0, 7) !== 'http://' && substr($link, 0, 8) !== 'https://') $link = 'http://' . $link;

                $output = "";
                $output .= "<div class=\"entrycont " . ($curbg ? "entrycont_dbg" : "entrycont_lbg") . "\">";
                if ($admin) $output .= "<form class=\"entryfield entrydeletefield\" method=\"POST\"><input type=\"hidden\" name=\"entryid\" value=\"$entry[id]\"><input type=\"submit\" class=\"entrydeletebutton\" value=\"X\"></form>";
                $output .= "<span class=\"entryfield entrygamefield\" title=\"$entry[game_name]\">" . htmlentities("$entry[game_name]") . "</span>";
                $output .= "<span class=\"entryfield entrybspfield\" title=\"$entry[bsp_name]\">" . htmlentities("$entry[bsp_name]") . "</span>";
                if (!empty($entry["dl_link"])) $output .= "<a class=\"entryfield entrylinkfield\" title=\"$entry[dl_link]\" href=\"$link\">Link</a>";
                $curbg = !$curbg;
                $output .= "</div>";
                echo $output;
            } else {
                echo "<span style=\"text-align:center;font-weight:bold;margin:10px;\">NO ENTRIES</span>";
            }
        ?>
        </div>
    </div>
</body>
