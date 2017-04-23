<?php

$calledFromElsewhere = 1;
$safeToExecuteParaFunc = 1;
echo "<!--";
include 'ParaFunc.php';
echo "-->";
echo htmlDeclarations("", "");

?>

<body class="dynamicConfigPageStyle" style="text-align:center;padding-top:10px;">
    <form method="POST">
        <div id="reqform">
            <?php
            // Process POST and print notifications
            if (!empty($_POST['mapreq_bsp_game']) || !empty($_POST['mapreq_bsp_name'])) { // Map Request Submission
                if (!enablePGSQL) {
                    $output .= pageNotificationFailure("Cannot submit map request, PGSQL is NOT enabled!");
                } else if (!empty($_POST['mapreq_bsp_game']) && !empty($_POST['mapreq_bsp_name'])) {
                    $mapreq_bsp_game = $_POST['mapreq_bsp_game'];
                    $mapreq_bsp_name = $_POST['mapreq_bsp_name'];
                    $mapreq_bsp_link = "";
                    if (!empty($_POST['mapreq_bsp_link'])) $mapreq_bsp_link = $_POST['mapreq_bsp_link'];
                    pg_query_params($pgCon, '
                        INSERT INTO mapreq (game_name, bsp_name, dl_link)
                        VALUES ($1, $2, $3)
                        ON CONFLICT (game_name, bsp_name) DO UPDATE
                        SET dl_link = $3, entry_date = NOW()', array($mapreq_bsp_game, $mapreq_bsp_name, $mapreq_bsp_link))
                        or displayError ('could not insert data into map table', $lastRefreshTime);
                    echo pageNotificationSuccess("Submission received!");
                } else { // Required field not filled out
                    echo pageNotificationFailure("Must fill out BOTH REQUIRED fields for map request!");
                }
            }
            ?>
            <div class="reqformrow">
                <span class="reqformlabel">Game Name:</span>
                <input class="reqformtextentry" type="text" name="mapreq_bsp_game" placeholder="REQUIRED (e.g. Jedi Academy)">
            </div>
            <div class="reqformrow">
                <span class="reqformlabel">BSP Name:</span>
                <input class="reqformtextentry" type="text" name="mapreq_bsp_name" placeholder="REQUIRED (excluding .bsp)">
            </div>
            <div class="reqformrow">
                <span class="reqformlabel">BSP Download:</span>
                <input class="reqformtextentry" type="text" name="mapreq_bsp_link" placeholder="OPTIONAL (but greatly appreciated)">
            </div>
            <input class="reqformsubmit" type="submit" value="SUBMIT">
        </div>
    </form>
</body>
