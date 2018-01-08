<?php

if (php_sapi_name() != "cli" && !isset($forceAnalyticsBackgroundRun))
{
    echo "<!-- --><h3>AnalyticsBackground.php cannot be run from the web!!</h3><p>Use a cron job to run this file.</p>";
    exit();
}

$analyticsBackground = 1;
$calledFromElsewhere = 1;
$safeToExecuteParaFunc = "1";

if(!isset($utilitiesPath))
{
	//We are in the utilities folder, so we have to back out one
	chdir("../");
}

if (file_exists("ParaFunc.php"))
{
    include_once 'ParaFunc.php';
}
else
{
    echo "Unable to open ParaFunc.php\n";
    exit();
}

if(!analyticsEnabled)
{
    displayError("AnalyticsBackground.php cannot run because analytics is disabled in ParaConfig.php!", "", "");
}
else
{
    set_time_limit(300);
    cleanupInfoFolder($cleanupInterval, $deleteInterval, $loadLimit, $cleanupLogSize);
}

define("analyticsStartTime", date('Y-m-d H:i', time()));

set_time_limit(30);
//Lower the process priority of this script so it doesn't destroy the CPU
proc_nice(9);


$servers = pg_fetch_all(pg_query($pgCon, 'SELECT id, location, port, active FROM tracker.server'));
if (empty($servers)) exit();

//Check to see if threads are present. If not, let's do this the brute force way
if(class_exists('Thread'))
{
    //This is the number of attempts Analytics will make to connect to the server
    //Threads are present, so higher numbers are possible
    $attempts = 4;

    //Defining this here for later use
    $serverArray = array();

    class serverCheckThread extends Thread {
        function __construct($var) {
            $this->serv = $var;
        }
        public function run() {
            //Lower the process priority of this script so it doesn't destroy the CPU
            proc_nice(9);
            set_time_limit(30);
            $serv_str = makeDynamicAddressPath($serv['location'], $serv['port']);
            for($i = 0; $i < $attempts; $i++)
            {
                if(checkForAndDoUpdateIfNecessary($serv_str)) break;
            }
        }
    }

    foreach($servers as $serv) {
        if(!booleanValidator($serv['active'], 1)) continue;
        $svthread = new serverCheckThread($serv);
        $svthread->start();
        // $svthread goes in an array for later
        array_push($serverArray, $svthread);
    }

    foreach($serverArray as $serv) {
        //iterate over array of threads, calling $svthread->join()
        $serv->join();
    }

    //Now, put everything into the database
    foreach($servers as $serv) {
        if(!booleanValidator($serv['active'], 1)) continue;
        putInfoIntoDatabase($serv);
    }
}
else
{
    //This is the number of attempts Analytics will make to connect to the server
    //Threads are not present, so don't go crazy!
    $attempts = 2;

    foreach($servers as $serv) {
        if(!booleanValidator($serv['active'], 1)) continue;
        //This line will prevent analytics from timing out
        set_time_limit(30);
        $serv_str = makeDynamicAddressPath($serv['location'], $serv['port']);
        for($i = 0; $i < $attempts; $i++)
        {
            if(checkForAndDoUpdateIfNecessary($serv_str))
            {
                putInfoIntoDatabase($serv);
                break;
            }
        }
    }
}

pg_query_params($pgCon, 'INSERT INTO tracker.cpuload (load) VALUES ($1)', array(getSystemLoadAverage()));
pg_query_params($pgCon, 'INSERT INTO analytics.runtimes (startdate) VALUES ($1)', array(analyticsStartTime));



function putInfoIntoDatabase($serv)
{
	global $pgCon;
	$server_id = $serv['id'];
	$serv_str = makeDynamicAddressPath($serv['location'], $serv['port']);
	$data = readPostgresDataFile($serv_str);
	/* [0] => Online Status
	 * [1] => Game Name
	 * [2] => Hostname
	 * [3] => Map Name
	 * [4] => Mod Name
	 * [5] => Gametype
	 * [6] => Player Count
	 */

	 echo '
' . $serv_str . '
';

	// STATUS 
	$status = $data[0];
	$frame_id = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.frame (server_id) VALUES ($1) RETURNING id', array($server_id)))[0];
	if (!$status) return;

	// GAMENAME
	$gamename_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'SELECT id FROM analytics.gamename WHERE name = $1', array($data[1])));
	if (empty($gamename_id_fetch)) {
		$gamename_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.gamename (name) VALUES ($1) RETURNING id', array($data[1])));
	}
	$gamename_id = $gamename_id_fetch[0];

	// HOSTNAME
	$hostname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'SELECT id FROM analytics.hostname WHERE name = $1', array($data[2])));
	if (empty($hostname_id_fetch)) {
		$hostname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.hostname (name) VALUES ($1) RETURNING id', array($data[2])));
	}
	$hostname_id = $hostname_id_fetch[0];

	// MAP NAME
	$mapname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'SELECT id FROM analytics.mapname WHERE name = $1', array($data[3])));
	if (empty($mapname_id_fetch)) {
		$mapname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.mapname (name) VALUES ($1) RETURNING id', array($data[3])));
	}
	$mapname_id = $mapname_id_fetch[0];

	// MOD NAME
	$modname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'SELECT id FROM analytics.modname WHERE name = $1', array($data[4])));
	if (empty($modname_id_fetch)) {
		$modname_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.modname (name) VALUES ($1) RETURNING id', array($data[4])));
	}
	$modname_id = $modname_id_fetch[0];

	// GAMETYPE
	$gametype_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'SELECT id FROM analytics.gametype WHERE name = $1', array($data[5])));
	if (empty($gametype_id_fetch)) {
		$gametype_id_fetch = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.gametype (name) VALUES ($1) RETURNING id', array($data[5])));
	}
	$gametype_id = $gametype_id_fetch[0];


	$record_id = pg_fetch_row(pg_query_params($pgCon, 'INSERT INTO analytics.record (gamename_id, hostname_id, mapname_id, modname_id, gametype_id, player_count) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id',
		array($gamename_id, $hostname_id, $mapname_id, $modname_id, $gametype_id, $data[6])))[0];

	pg_query_params($pgCon, 'UPDATE analytics.frame SET record_id = $1 WHERE id = $2', array($record_id, $frame_id));
}

?>
