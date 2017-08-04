<?php

echo "<!--";

$calledFromElsewhere = 1;

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

if(mapreqEnabled == "0")
{
    displayError("Mapreq is disabled! Mapreq must be enabled in ParaConfig.php.", "", "");
}

$output = htmlDeclarations("", "../");

$output .= '<link rel="stylesheet" href="../css/ParaSetup.css" type="text/css" />';

if (admin && !empty($_POST['entryid'])) {
	pg_query_params($pgCon, 'DELETE FROM mapreq WHERE id = $1', array($_POST['entryid']));
}

$mapreqs_user = pg_fetch_all(pg_query($pgCon, 'SELECT * FROM mapreq WHERE useradded = true ORDER BY  CASE WHEN dl_link IS NULL THEN 1 ELSE 0 END, game_name ASC, bsp_name ASC'));
$mapreqs_auto = pg_fetch_all(pg_query($pgCon, 'SELECT * FROM mapreq WHERE useradded = false ORDER BY game_name ASC, bsp_name ASC'));

$output .= '<body class="mapReqPageStyle">
    <form method="POST">
        <div id="reqform">';

            // Process POST and print notifications
            if (!empty($_POST['mapreq_bsp_game']) || !empty($_POST['mapreq_bsp_name'])) { // Map Request Submission
                if (!enablePGSQL) {
                    $output .= pageNotificationFailure("Cannot submit map request, PGSQL is NOT enabled!");
                } else if (!empty($_POST['mapreq_bsp_game']) && !empty($_POST['mapreq_bsp_name'])) {
                    $mapreq_bsp_game = $_POST['mapreq_bsp_game'];
                    $mapreq_bsp_name = $_POST['mapreq_bsp_name'];
                    $mapreq_bsp_link = "";
                    if (!empty($_POST['mapreq_bsp_link'])) $mapreq_bsp_link = $_POST['mapreq_bsp_link'];
                    else $mapreq_bsp_link = NULL;
                    pg_query_params($pgCon, '
                        INSERT INTO mapreq (game_name, bsp_name, dl_link, useradded)
                        VALUES ($1, $2, $3, true)
                        ON CONFLICT (game_name, bsp_name) DO UPDATE
                        SET dl_link = $3, entry_date = NOW(), useradded = true', array($mapreq_bsp_game, $mapreq_bsp_name, $mapreq_bsp_link))
                        or displayError ('Could not insert data into map table', "", "");
                    $output .= pageNotificationSuccess("Submission received!");

                    //Submission added. Check to see if email is enabled and try the email file
                    if(emailEnabled)
                    {
                        include_once utilitiesPath . "SendEmails.php";
                        $message = '<table style="width: 100%; font-family: monospace; font-size: 11pt;"><tr><td style="text-align: center;">';
                        $message .= '<h3>A levelshot request has been received for <strong>' . stringValidator($mapreq_bsp_game, "", "") . '</strong>.</h3>';
                        $message .= '<p>BSP name: <strong>' . stringValidator($mapreq_bsp_name, "", "") . '</strong></p>';
                        if(!empty($mapreq_bsp_link))
                        {
                            $message .= '<p style="">Link: <a href="' . stringValidator($mapreq_bsp_link, "", "") . '"><strong>' . stringValidator($mapreq_bsp_link, "", "") . '</strong></a></p>';
                        }
                        else
                        {
                            $message .= '<p>No link provided.</p>';
                        }
                        $message .= '<p>Client IP Address: ' . $_SERVER['REMOTE_ADDR'] . '</p>';
                        $message .= '<p>Time: ' . date('Y-m-d H:i', time()) . '</p>';
                        $message .= '</td></tr></table>';
                        sendEmail($emailAdministrators, 'ParaTracker - New Levelshot Request Received!', $message);
                    }

                } else { // Required field not filled out
                    $output .= pageNotificationFailure("Must fill out BOTH REQUIRED fields for map request!");
                }
            }

            $output .='<div class="reqformrow">
                <span class="reqformlabel">Game Name:</span>';

                $gameListArray = detectGameName("");
                //The output returned will be an array. Position 0 is a full game list, position 1 is a filtered game list (Useful for hiding duplicate game entries)
                $gameList = $gameListArray[1];

                $output .= '<select class="reqformtextentry" name="mapreq_bsp_game">';

                $count = count($gameList);
                for($i = 0; $i < $count; $i++)
                {
					$output .= '<option ';
                    if(strtolower(trim($gameList[$i])) == "jedi academy")
                    {
                        $output .= 'selected="selected" ';
                    }
                    $output .= 'value="' . $gameList[$i] . '" ';

                    $output .= '>' . $gameList[$i] . '</option>';
                }
                
                $output .= '</select>';

            $output .= '</div>
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
';

$output .= '';

if (!empty($mapreqs_user)) {
	$output .= addmapreqtable('User Added (High Priority)', $mapreqs_user, true);
}

if (!empty($mapreqs_auto)) {
	$output .= addmapreqtable('Automatically Added (Low Priority)', $mapreqs_auto, false);
}

$output .= '</body></html>';

echo "-->" . $output;

function addmapreqtable($title, $query, $with_dl) {
	$output = "<h3>$title</h3>";
	$output .= '<div class="entrytop">
			<div class="entryheader">';
				if (admin) $output .= '<span class="entryfield entrydeletefield entryheaderfield">✖</span>';
				$output .= '<span class="entryfield entrygamefield entryheaderfield">Game</span>';
				$output .= '<span class="entryfield entrybspfield entryheaderfield">BSP</span>';
				$output .= '<span class="entryfield entrydatefield entryheaderfield">Date</span>';
				if ($with_dl) $output .= '<span class="entryfield entrylinkfield entryheaderfield">Download</span>';
				$output .= '</div><div class="entrybody">';

				$curbg = false;
				if (!empty($query)) foreach ($query as $entry) {
					$link = $entry['dl_link'];
					if (substr($link, 0, 7) !== 'http://' && substr($link, 0, 8) !== 'https://') $link = 'http://' . $link;

					$output .= "<div class=\"entrycont " . ($curbg ? "entrycont_dbg" : "entrycont_lbg") . "\">";
					if (admin) $output .= "<form class=\"entryfield entrydeletefield\" method=\"POST\"><input type=\"hidden\" name=\"entryid\" value=\"$entry[id]\"><input type=\"submit\" class=\"entrydeletebutton\" value=\"✖\"></form>";
					$output .= "<span class=\"entryfield entrygamefield\" title=\"$entry[game_name]\">" . htmlentities("$entry[game_name]") . "</span>";
					$output .= "<span class=\"entryfield entrybspfield\" title=\"$entry[bsp_name]\">" . htmlentities("$entry[bsp_name]") . "</span>";
					$output .= "<span class=\"entryfield entrydatefield\" title=\"$entry[bsp_name]\">" . date('Y-m-d', strtotime($entry['entry_date'])) . "</span>";
					if ($with_dl && !empty($entry["dl_link"])) $output .= "<a class=\"entryfield entrylinkfield\" title=\"$entry[dl_link]\" href=\"$link\">Link</a>";
					$curbg = !$curbg;
					$output .= "</div>";
				} else {
					$output .= '<span style="text-align:center;font-weight:bold;margin:10px;">NO ENTRIES</span>';
				}
			$output .= '</div>
		</div>';
		return $output;
}

?>
