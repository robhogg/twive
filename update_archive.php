<?php
	require("twitlib.php");

	$opts = getopt("m:n:s:h");

	if(isset($opts[h])) {
		usage($argv[0]);
		exit(0);
	}

	$since = (isset($opts[s]) && is_numeric($opts[s]))?$opts[s]:0;
	$max = (isset($opts[m]) && is_numeric($opts[m]))?$opts[m]:0;
	$num = (isset($opts[n]) && is_numeric($opts[n]))?$opts[n]:15;

	$archname = array_pop($argv);

	$archive = get_archive_details($archname);

	if(! isset($archive['name'])) {
		$stderr = fopen("php://stderr","w");
		fwrite($stderr,"Error: Unable to retrieve details for $archname\n\n");
		usage($argv[0]);
		exit(1);
	}

	echo("=== Starting at ".date("Y-m-d H:i:s")."\n");
	echo("Retrieving tweets for $archname\n");
	$tweets = harvest_tweets($archive['search'],$num,$since,$max);
	echo(count($tweets->results)." retrieved. Updating archive\n\n");
	
	$archived = 0;
	$already = 0;
	$fails = 0;
	foreach($tweets->results as $i => $tweet) {
		$res = save_tweet($tweet,$archname);
		
		switch($res) {
			case 0:
				$fails += 1;
				break;
			case 1:
				$archived += 1;
				break;
			case -1:
				$already += 1;
				break;
		}

	}

	echo("Added to $archname: $archived\n"
		."Already in $archname: $already\n"
		."Failures: $fails\n");

	if($archived + $already > 0) {
		archive_updated($archname);
	}

	echo("=== Finished at ".date("Y-m-d H:i:s")."\n\n");

	function usage($scriptname) {
		echo("Usage: $scriptname [-s id] [-m id] [-num n] [-h] archive\n"
			."\tarchive - mandatory (name of an existing archive)\n"
			."\t-s id - since_id parameter for the twitter search\n"
			."\t-m id - max_id parameter for the twitter search\n"
			."\t-n num - number of tweets to fetch (default 15)\n"
			."\t-h - output this help information\n\n");
	}
?>