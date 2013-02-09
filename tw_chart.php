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

	if(! isset($params['archive'])) {
		$params = parse_params();
	}

	if(isset($params['chart']) && $params['chart'] != "") {
		$chartwc =  date("Y-m-d H:i:s",strtotime($params['chartwe']) - 168 * 3600 + 1);
		$chartprev = date("Y-m-d H:i:s",strtotime($params['chartwe']) - 168 * 3600);
		$chartnext = date("Y-m-d H:i:s",strtotime($params['chartwe']) + 168 * 3600);

		$prev_qs = qs_set_params(array("chartwe" => $chartprev));
		$next_qs = qs_set_params(array("chartwe" => $chartnext));
		$period = date("j M Y a",strtotime($chartwc))." - "
			.date("j M Y a",strtotime($params['chartwe']));
		$prev_link = "<a href=\"" .$params['uri']."?$prev_qs\">&lt; Prev</a>";
		$next_link = "<a href=\"" .$params['uri']."?$next_qs\">Next &gt;</a>";
		$controls = "<div id=\"chart-controls\" class=\"list-controls\">$prev_link "
			."&nbsp;&nbsp;$period&nbsp;&nbsp;$next_link</div>\n";

		echo $controls;

		$data = get_chart_data($params['archive'],$params['chart'],
			$chartwc,$params['chartwe'],$params['crit']);

		echo draw_chart($data);
	} elseif(isset($params['stats'])) {
		show_stats($params['archive'],$params['q']);
	} elseif(isset($params['cloud'])) {
		get_cloud($params['archive']);
	}

?>
