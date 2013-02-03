<?php
	require_once("twitlib.php");

	if(! isset($params['archive'])) {
		$params = parse_params();
	}

	$params['chartfrom'] = date("Y-m-d H:i:s", time() - (156 * 3600)); 
	$params['chartto'] = date("Y-m-d H:i:s");

	if(isset($params['chart']) && $params['chart'] != "") {
		$data = get_chart_data($params['archive'],$params['chart'],
			$params['chartfrom'],$params['chartto'],$params['crit']);

		echo draw_chart($data);
	}
?>
