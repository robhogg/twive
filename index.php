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
   $Index = true;	

	$params = parse_params();
	$archdet = get_archive_details($params['archive']);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="refresh" content="60">
<title>Twitter archive for <?php echo $params['archive'] ?></title>
<base href="<?php echo $params['dir']; ?>">

<link rel="stylesheet" href="<?php echo $params['dir']; ?>/tw_style.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js">
</script>
<script type="text/javascript">
/*$(document).ready(function () {
	$(".chart_bar").bind("click", function() {
		$(this).css("background-color","red");
		alert($(this).attr("id") + ": " +$(this).css("height"));
	});
});*/
</script>
</head>
<body>
	<div id="header">
		<?php get_header('"'.urldecode($archdet['search']).'"'); ?>
	</div>
	<div id="content">
		<?php include("tw_list.php"); ?>
	</div>
	<div id="footer">
		<p><a href="https://github.com/robhogg/twive">Twive</a> is free software,
		licensed under the GNU General Public Licence.</p>
	</div>
	<!--<div id="script-params"><?php echo json_encode($params); ?></div>-->
</body>
</html>
