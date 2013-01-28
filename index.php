<?php
	require("twitlib.php");
   $Index = true;	
	$archname = (isset($_GET['archive']))?$_GET['archive']:"";

	$to = 50;
	$from = 0;
	$order = "date-";

?>
<!DOCTYPE html>
<html>
<head>
<title>EDMOOC</title>
</head>
<body>
	<div id="tweet-list">
		<?php include("tw_list.php"); ?>
	</div>
</body>
</html>
