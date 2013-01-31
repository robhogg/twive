<?php
	require_once("twitlib.php");

	if(! isset($archname)) {
		/*
		* If not set in context, may be being called via AJAX
		*/
		$archname = $_GET['archive'];
		
		$per_page = (isset($_GET['perpage']))?$_GET['perpage']:25;
		$page = (isset($_GET['page']))?$_GET['page']:1;
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

	// TODO: search criteria
	$crit = array();

   $tweets = get_tweets($archname,$per_page,($page - 1) * $per_page,
		$order,$crit);
	$tw_num = get_num_tweets($archname,$crit);

	?>
	<div id="list-controls">
		<?php get_controls($page,ceil($tw_num / $per_page)); ?>
	</div>
	<div id="tweet-list">
		<?php
		$count = 0;
		foreach($tweets as $tweet) {
			echo("<div id=\"tweet-$count\" class=\"tweet\">\n");
				echo(format_tweet($tweet));
			echo("</div>");
			$count += 1;
		}
		?>
	</div>
?>
