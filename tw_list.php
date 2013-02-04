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

	require_once("tw_lib.php");

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
	<div id="chart">
		<?php include("tw_chart.php"); ?>
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
