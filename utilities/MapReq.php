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

//Set the default game to select from the list later on
$gameReq = "jedi academy";
$bspReq = "";

//If another game is specified, then we'll use it instead of the one above
if (isset($_GET["gameReq"]))
{
	$gameReq = stringValidator($_GET["gameReq"], "", "");
}
if (isset($_GET["bspReq"]))
{
	$bspReq = strtolower(stringValidator($_GET["bspReq"], "", ""));
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
            if (!empty($_POST['mapreq_bsp_game']) || !empty($_POST['mapreq_bsp_name']) || !empty($_POST['mapreq_bsp_link'])) { // Map Request Submission
                if (!enablePGSQL) {
                    $output .= pageNotificationFailure("Cannot submit map request, PGSQL is NOT enabled!");
                 } else if (!empty($_POST['mapreq_bsp_game']) && !empty($_POST['mapreq_bsp_name']) && !empty($_POST['mapreq_bsp_link'])) {
                    $mapreq_bsp_game = trim($_POST['mapreq_bsp_game']);
                    $mapreq_bsp_name = strtolower($_POST['mapreq_bsp_name']);
                    if(substr($mapreq_bsp_name, -4) == '.bsp') $mapreq_bsp_name = substr($mapreq_bsp_name, 0, strlen($mapreq_bsp_name) - 4);
                    $mapreq_bsp_link = trim($_POST['mapreq_bsp_link']);
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
                        $message .= '<p><span style="font-size: 12pt;">A levelshot request has been received for <strong>' . stringValidator($mapreq_bsp_game, "", "") . '</strong>.</span></p>';
                        $message .= '<p>BSP name: <strong>' . stringValidator($mapreq_bsp_name, "", "") . '</strong></p>';
                        if(!empty($mapreq_bsp_link))
                        {
							if(strtolower(trim($mapreq_bsp_link)) == "base game") $message .= '<p style=""><strong>Base Game</strong></a></p>';
							else if(strtolower(trim($mapreq_bsp_link)) == "none") $message .= '<p>No link provided</p>';
							else $message .= '<p>Link: <span style="font-size: 9pt; font-weight: bold;"><a href="' . stringValidator($mapreq_bsp_link, "", "") . '"><strong>' . stringValidator($mapreq_bsp_link, "", "") . '</strong></a></span></p>';
                        }
                        else
                        {
                            $message .= '<p><b>No link provided</b></p>';
                        }
                        $message .= '<p>Client IP Address: <b>' . $_SERVER['REMOTE_ADDR'] . '</b></p>';
                        $message .= '<p>Time: <b>' . date('Y-m-d H:i', time()) . '</b></p>';
                        $message .= '</td></tr></table>';
                        sendEmail($emailAdministrators, 'ParaTracker - New Levelshot Request Received!', $message);
                    }

                } else { // Required field not filled out
                    $output .= pageNotificationFailure("You must fill out ALL fields for levelshot requests!");
                }
            }

				$output .='<div class="reqformrow">
                <span class="reqformlabel">Game Name:</span>';

                $gameListArray = detectGameName("");
                //The output returned will be an array. Position 0 is a full game list, position 1 is a filtered game list (Useful for hiding duplicate game entries)
                $gameList = $gameListArray[0];

                $output .= '<select class="reqformtextentry" name="mapreq_bsp_game">';

                $count = count($gameList);
                for($i = 0; $i < $count; $i++)
                {
					$output .= '<option ';
                    if(strtolower(trim($gameList[$i])) == strtolower(trim($gameReq)))
                    {
                        $output .= 'selected="selected" ';
                    }
                    $output .= 'value="' . $gameList[$i] . '" ';

                    $output .= '>' . $gameList[$i] . '</option>';
                }
                
                $output .= '</select>';
echo '>> ' . $bspReq . ' <<
';
            $output .= '</div>
            <div class="reqformrow">
                <span class="reqformlabel">BSP Name:</span>
                <input class="reqformtextentry" type="text" name="mapreq_bsp_name" value="' . $bspReq . '">
            </div>
            <div class="reqformrow">
                <span class="reqformlabel">Map or Image(s) Link:</span>
			</div>
			<div class="reqformrow">
                <input class="reqformtextentry" type="text" name="mapreq_bsp_link" placeholder="Use \'\'Base Game\'\' for maps included with the game">
            </div>
            <input class="reqformsubmit" type="submit" value="SUBMIT">
        </div>
    </form>
';

$output .= '';

if (!empty($mapreqs_user)) {
    if(admin)
    {
        $output .= addmapreqtable('User Added (High Priority)', $mapreqs_user, true);
    }
    else
    {
        $output .= addmapreqtable('Levelshot Requests', $mapreqs_user, false);
    }
}

if (admin && !empty($mapreqs_auto)) {
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
				if ($with_dl && admin) $output .= '<span class="entryfield entrylinkfield entryheaderfield">Download</span>';
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
					if ($with_dl && !empty($entry["dl_link"]))
					{
						if(strtolower(trim($entry["dl_link"])) == "base game") $output .= "<p class=\"entryfield entrylinkfield\">Base Game</p>";
						else if(strtolower(trim($entry["dl_link"])) == "none") $output .= "<p class=\"entryfield entrylinkfield\">No link provided</p>";
						else $output .= "<a class=\"entryfield entrylinkfield\" title=\"$entry[dl_link]\" href=\"$link\">Link</a>";
					}
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
