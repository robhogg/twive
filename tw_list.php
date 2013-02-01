<?php
	require_once("twitlib.php");

	$params = parse_params();

	// If still not set, something is wrong
	if(! $params['archive']) {
		echo('<h1 class="error">No archive set</h1>');
		exit(0);
	}

	$tw_num = get_num_tweets($params['archive'],$params['crit']); 

	$pages = ceil($tw_num / $params['perpage']);
	$page = ($params['page'] <= $pages)?$params['page']:$pages;

   $tweets = get_tweets($params['archive'],$params['perpage']
		,($page - 1) * $params['perpage'],$params['sort'],$params['crit']);
	?>
	<div id="list-controls">
		<?php get_controls($page,$pages); ?>
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
