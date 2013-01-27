<?php
	require("../../main_scripts/config.php");

	$conn = @new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	
	function display_tweets($min=0,$max=100) {

	}

	function harvest_tweets($search,$num=100,$since=0,$max=0,$type="mixed") {
		$adet = get_archive_details($archive);

		$base = "https://search.twitter.com/search.json";
		$url = $base."?q=".$search."&rpp=".$num
			."&result_type=".$type;

		if($min > 0) {
			$url .= "&since_id=".$since;	
		}

		if($max > 0) {
			$url .= "&max_id=".$max;
		}

		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		$tweets = json_decode(curl_exec($ch));

		return $tweets;
	}

	function list_archives() {
		global $conn;

		$res = $conn->query("select * from tw_archive");
		
		$archive_list = array();

		while($row = $res->fetch_assoc()) {
			array_push($archive_list,$row);
		}

		return $archive_list;
	}

	function get_archive_details($archive) {
		global $conn;
		$archive = $conn->real_escape_string($archive);

		$details = $conn->query("select * from tw_archive where name = "
			."'$archive'");

		return $details->fetch_assoc();
	}

	function save_tweet($tweet,$archive) {
		global $conn;

		$tid = $conn->real_escape_string($tweet->id_str);
		$uid = $conn->real_escape_string($tweet->from_user_id_str);

		$tcheck = $conn->query("select tid from tw_tweets where tid = '$tid'");

		if($conn->affected_rows == 0) {
			add_user($uid,$tweet->from_user,$tweet->from_user_name,
					$tweet->profile_image_url);
			$text = $conn->real_escape_string($tweet->text);
			$date = date('Y-m-d H:i:s',strtotime($tweet->created_at));
			$reply_tweet = "";
			if(isset($tweet->in_reply_to_status_id_str)) {
				$reply_tweet = $conn->real_escape_string(
					$tweet->in_reply_to_status_id_str);
			}

			$reply_user = $conn->real_escape_string(
				$tweet->to_user_id_str);
			
			$conn->query("insert into tw_tweets values ("
				."'$tid','$uid','$archive','$text','$date',"
				."'$reply_tweet','$reply_user')");
			
			return $conn->affected_rows;
		} else {
			return -1;
		}

	}

	function archive_updated($archive) {
		global $conn;

		$conn->query("update tw_archive set last_updated = current_timestamp() "
			."where name = '$archive'");
	}

	function add_user($uid,$username,$name,$image) {
		global $conn;
		$ucheck = $conn->query("select uid from tw_users where uid = '$uid'");
		if($conn->affected_rows == 0) {
			$uid = $conn->real_escape_string($uid);
			$username = $conn->real_escape_string($username);
			$name = $conn->real_escape_string($name);
			$image = $conn->real_escape_string($image);
			
			$conn->query("insert into tw_users values ('$uid','$username',"
				."'$name','$image')");
		}
	}

	function tweet_date_format($date) {
		$ts = strtotime($date);
		$ago = time() - $ts;
		switch(true) {
			case ($ago <= 60):
				return $ago."s";
				break;
			case ($ago <= 3600):
				break;
				return $ago."m";
			case ($ago <= 86400):
				return date("H:i",$ts);
				break;
			case (date("Y") != date("Y",$ts)):
				return date("j M Y");
				break;
			default:
				return date("j M",$ts);

		}

		return $now - $ts;
	}
?>
