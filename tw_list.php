<?php
	require_once("twitlib.php");

	$params = parse_params();

	// If still not set, something is wrong
	if(! $params['archive']) {
		echo('<h1 class="error">No archive set</h1>');
		exit(0);
	}

   $tweets = get_tweets($params['archive'],$params['perpage']
		,($params['page'] - 1) * $params['perpage'],
		$params['order'],$params['crit']);
	$tw_num = get_num_tweets($params['archive'],$params['crit']); 

	?>
	<div id="list-controls">
		<?php get_controls($params['page'],ceil($tw_num / $params['perpage'])); ?>
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
