<?php
	require_once("twitlib.php");

	if(! isset($archname)) {
		/*
		* If not set in context, may be being called via AJAX
		*/
		$archname = $_GET['archive'];
		
		$num = (isset($_GET['num']))?$_GET['num']:15;
		$to = (isset($_GET['to']))?$_GET['to']:0;
		$from = (isset($_GET['from']))?$_GET['from']:0;
		if(isset($_GET['order']) && isset($_GET['dir']) 
			&& $_GET['dir'] = "desc") {
			$order = $_GET['order'].'-';
		} elseif(isset($_GET['order'])) {
			$order = $_GET['order']."+";
		} else {
			$order = "date-";
		}
	}

	// If still not set, something is wrong
	if(! $archname) {
		echo('<h1 class="error">No archive set</h1>');
		exit(0);
	}

echo("$archname, $to, $from, $order<br />");
	// TODO: search criteria

   $tweets = get_tweets($archname,$to,$from,$order);
	
	$count = 0;
	foreach($tweets as $tweet) {
		echo("<div id=\"tweet-$count\" class=\"tweet\">\n");
			echo(format_tweet($tweet));
		echo("</div>");
		$count += 1;
	}
?>
