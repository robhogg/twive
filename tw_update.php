<?php
   /*
   * Copyright Rob Hardy 2013 (https://github.com/robhogg/twive)
   *
   * This file is part of Twive
   * 
   * Twive is free software: you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation, either version 3 of the License, or
   * (at your option) any later version.
   * 
   * Twive is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   * 
   * You should have received a copy of the GNU General Public License
   * along with Twive.  If not, see <http://www.gnu.org/licenses/>.
   */

	require("tw_lib.php");

	$opts = getopt("b::m:n:s:h");

	if(isset($opts[h])) {
		usage($argv[0]);
		exit(0);
	}

	$since = (isset($opts[s]) && is_numeric($opts[s]))?$opts[s]:0;
	$max = (isset($opts[m]) && is_numeric($opts[m]))?$opts[m]:0;
	$num = (isset($opts[n]) && is_numeric($opts[n]))?$opts[n]:15;
	$bulk = "";
	if(isset($opts['b']) && $opts['b'] == '+') {
		$bulk = '+';
	} elseif (isset($opts['b'])) {
		$bulk = '-';
	}

	$archname = array_pop($argv);

	$archive = get_archive_details($archname);

	if(! isset($archive['name'])) {
		$stderr = fopen("php://stderr","w");
		fwrite($stderr,"Error: Unable to retrieve details for $archname\n\n");
		usage($argv[0]);
		exit(1);
	}

	echo("=== Starting at ".date("Y-m-d H:i:s")."\n");
	if($bulk == "+") {
		echo("Operating in \"later tweets\" bulk update mode.\n");

		$archived = 1;
		$count = 0;
		while($archived > 0) {
			$stats = get_stats($archname);
			$since = $stats['latest']['tweet'];
			$results = retrieve_tweets($archive,$num,$since,$max);

			output_results($results,$archname);
			$archived = $results['archived'];
			$count += $archived;
			sleep(30);
		}
		
		echo("Archived total: $count");
	} elseif($bulk == "-") {
		echo("Operating in bulk update mode.\n");

		$archived = 1;
		$count = 0;
		while($archived > 0) {
			$stats = get_stats($archname);
			$max = ($stats['earliest']['tweet'])?$stats['earliest']['tweet']:0;
			$results = retrieve_tweets($archive,$num,$since,$max);

			output_results($results,$archname);
			$archived = $results['archived'];
			$count += $archived;
			sleep(30);
		}
		
		echo("Archived total: $count");
	} else {
		echo("Operating in update mode.\n");
		$results = retrieve_tweets($archive,$num,$since,$max);

		output_results($results,$archname);
	}
	echo("=== Finished at ".date("Y-m-d H:i:s")."\n\n");

	exit(0);

	function retrieve_tweets($archive,$num,$since,$max) {
		echo("Retrieving tweets for ".$archive['name']."\n");
		$tweets = harvest_tweets($archive['search'],$num,$since,$max);
		echo(count($tweets->results)." retrieved. Updating archive\n\n");
		
		$results = array('archived' => 0, 'already' => 0, 'fails' => 0);
		foreach($tweets->results as $i => $tweet) {
			$res = save_tweet($tweet,$archive['name']);

			switch($res) {
				case 0:
					$results['fails'] += 1;
					break;
				case 1:
					$results['archived'] += 1;
					break;
				case -1:
					$results['already'] += 1;
					break;
			}

		}

		return $results;
	}

	function output_results($results,$archname) {
		echo("Added to $archname: ".$results['archived']."\n"
			."Already in $archname: ".$results['already']."\n"
			."Failures: ".$results['fails']."\n");

		if($archived + $already > 0) {
			archive_updated($archname);
		}
	}

	function usage($scriptname) {
		echo("Usage: $scriptname [-s id] [-m id] [-num n] [-h] archive\n"
			."\tarchive - mandatory (name of an existing archive)\n"
			."\t-s id - since_id parameter for the twitter search\n"
			."\t-m id - max_id parameter for the twitter search\n"
			."\t-n num - number of tweets to fetch (default 15)\n"
			."\t-b [+] - bulk update mode - get all tweets available\n"
			."\t\tfor search. With + works up from latest tweet in\n"
			."\t\tarchive\n"
			."\t-h - output this help information\n\n");
	}
?>
