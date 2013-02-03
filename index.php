<?php
	require("twitlib.php");
   $Index = true;	

	$params = parse_params();
	$archdet = get_archive_details($params['archive']);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Twitter archive for <?php echo $params['archive'] ?></title>

<base href="/twive">

<link rel="stylesheet" href="/twive/tw_style.css">
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
</body>
</html>
