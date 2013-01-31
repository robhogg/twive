<?php
	require("twitlib.php");
   $Index = true;	
	$archname = (isset($_GET['archive']))?$_GET['archive']:"";

	$page = (isset($_GET['page']))?$_GET['page']:1;
	$per_page = (isset($_GET['perpage']))?$_GET['perpage']:25;
	if(isset($_GET['order']) && isset($_GET['dir'])
		&& $_GET['dir'] = "desc") {
		$order = $_GET['order'].'-';
	} elseif(isset($_GET['order'])) {
		$order = $_GET['order']."+";
	} else {
		$order = "date-";
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>EDMOOC</title>

<base href="/twive">

<link rel="stylesheet" href="/twive/tw_style.css">
</head>
<body>
	<div id="header">
	<?php get_header($archname); ?>
	</div>
	<div id="content">
		<?php
			include("tw_list.php"); ?>
	</div>
</body>
</html>
