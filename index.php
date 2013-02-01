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
