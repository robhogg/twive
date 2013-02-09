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
	if(date("H") >= 12) {
		$params['chartfrom'] = date("Y-m-d 00:00:00", time() - (156 * 3600)); 
		$params['chartto'] = date("Y-m-d 23:59:59");
	} else {
		$params['chartfrom'] = date("Y-m-d 12:00:00", time() - (156 * 3600)); 
		$params['chartto'] = date("Y-m-d 11:59:59");
	}

	if(isset($params['chart']) && $params['chart'] != "") {
		$data = get_chart_data($params['archive'],$params['chart'],
			$params['chartfrom'],$params['chartto'],$params['crit']);

		echo draw_chart($data);
	} elseif(isset($params['stats'])) {
		show_stats($params['archive'],$params['q']);
	} elseif(isset($params['cloud'])) {
		get_cloud($params['archive']);
	}

?>
