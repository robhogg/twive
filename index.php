<?php
	require("tweets.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>EDMOOC</title>
</head>
<body>
<?php
	$json = file_get_contents("search4.json");
	$json = json_decode("$json");
	foreach($json->results as $key => $result) {
		echo "<h2>".$key."</h1>\n";
		echo '<li><a href="http://twitter.com/'.$result->from_user
			.'">'.$result->from_user."</a></li>";
		echo '<ul><li>'.$result->text.'</li>';
		echo '<li><a href="http://twitter.com/'.$result->from_user
			.'/status/'.$result->id_str.'">'.$result->created_at
			.'</a></li>';
		echo '<li>'.tweet_date_format($result->created_at).'</li></ul>';
	   echo '</ul>';
		var_dump($result);
		echo "<br /><br />";
	}
?>
</body>
</html>
