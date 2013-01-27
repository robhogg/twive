<?php
	require("/kunden/homepages/15/d342865908/htdocs/main_scripts/config.php");

	$conn = @new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	
	function display_tweets($min=0,$max=100) {

	}

	function harvest_tweets() {
		$base = "https://api.twitter.com/1.1/search/tweets.json";

		$q = urlencode("#edcmooc OR #edmooc");
	}

	function tweet_date_format($date) {
		$ts = strtotime($date);
		$ago = time() - $ts;
		switch(true) {
			case ($ago <= 60):
				return $ago."s";
				break;
			case ($ago <= 3600):
				break;
				return $ago."m";
			case ($ago <= 86400):
				return date("H:i",$ts);
				break;
			case (date("Y") != date("Y",$ts)):
				return date("j M Y");
				break;
			default:
				return date("j M",$ts);

		}

		return $now - $ts;
	}
?>
