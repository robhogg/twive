<?php
	require("../../main_scripts/config.php");

	$conn = @new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	
	function get_header($archname) {
		echo("<h1>Twitter archive for <span class=\"archname\">"
			."$archname</span></h1>\n");
	}

	function get_controls($page,$pages) {
		$paging = "<span id=\"paging\">";
		$params = get_params();

		$uri = preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']);

		if($page > 2) {
			$qs = qs_set_params(array("page" => 1));
			$paging .= "<a href=\"$uri?$qs\" id=\"first_page\">|&lt;</a>";
		} else {
			$paging .= "|&lt;";
		}


		if($page > 1) {
			$qs = qs_set_params(array("page" => $page - 1));
			$paging .= " <a href=\"$uri?$qs\" id=\"prev_page\">&lt;</a>";
		} else {
			$paging .= " &lt;";
		}

		if($page < $pages) {
		   $qs = qs_set_params(array("page" => $page + 1));	
			$paging .= " <a href=\"$uri?$qs\" id=\"prev_page\">&gt;</a>";
		} else {
			$paging .= " &gt;";
		}

		if($page < $pages - 2) {
			$qs = qs_set_params(array("page" => $pages));
			$paging .= " <a href=\"$uri?$qs\" id=\"last_page\">&gt;|</a>";
		} else {
			$paging .= " &gt;|";
		}

		$paging .= "</span>";

		$numbering = "<span id=\"pagenum\">Page $page of $pages</span>";

		$search = "<form method=\"get\" action=\"$uri\">\n"
			."<input type=\"text\" size=\"20\" name=\"q\" "
			."id=\"tsearch\" />\n<input type=\"submit\" value=\"Go\" />";



		echo "$paging $numbering";
	}
	/**
	* Mandataory argument $archive must be name of an existing archive.
	*
	* Optional arg. $ord is column name followed by '+' (asc) or '-' (desc)
	*
	* Optional arg. $criteria is array of form 'col' => array('op','val')...,
	*   recognised op values are =,<,>,like
	*
	* returns array of tweet arrays
	*/
	function get_tweets($archive,$to = 0,$from = 0,$ord = "date-",
		$criteria = array()) {
		global $conn;

		$sql = "select * from tw_tweets where archive = '$archive' ";

		foreach(array_keys($criteria) as $col) {
			$op = $criteria[$col][0];
			$val = $criteria[$col][1];
			if($op == "like") {
				$sql .= "and $col like '%$val%' ";
			} else {
				$sql .= "and $col $op '$val' ";
			}
		}

		preg_match('/([a-zA-Z]+)([+-])/',$ord,$m);
		$sql .= "order by ".$m[1]." ";
		if($m[2] == '-') {
			$sql .= "desc ";
		}

		if($from > 0) {
			$sql .= "limit $from, $to";
		} elseif ($to > 0) {
			$sql .= "limit $to";
		}

		$res = $conn->query($sql);

		$tweets = array();

		while($row = $res->fetch_assoc()) {
			array_push($tweets,$row);
		}

		return $tweets;
	}

	function get_num_tweets($archive,$criteria = array()) {
		global $conn;
		$sql = "select tid from tw_tweets where archive = '$archive' ";


		foreach(array_keys($criteria) as $col) {
			$op = $criteria[$col][0];
			$val = $criteria[$col][1];
			if($op == "like") {
				$sql .= "and $col like '%$val%' ";
			} else {
				$sql .= "and $col $op '$val' ";
			}
		}

		$conn->query($sql);

		return $conn->affected_rows;
	}

	function format_tweet($tweet) {
		$auth = get_author($tweet['uid']);


		$authlink = '<a href="http://twitter.com/'.$auth['username'].'" '
			.'class="tweet-auth">'.$auth['username'].':</a>';
		
		$text = preg_replace('/(https?:\/\/[^ ]*)/',
			"<a href=\"$1\" class=\"tweet-link\">$1</a>",
			$tweet['text']);
		$text = preg_replace('/@([a-zA-Z0-9_]+)/',
			"<a href=\"http://twitter.com/$1\" class=\"tweet-to\">@$1</a>",
			$text);
		$text = preg_replace('/#([a-zA-Z0-9_]+)/',
			"<a href=\"http://twitter.com/search?q=%23$1\" class=\"tweet-hash\">"
			."#$1</a>",$text);

		$tweet_link = '<a href="http://twitter.com/'.$auth['username'].'/status/'
			.$tweet['tid'].'" class="tweet-permalink">'
			.tweet_date_format($tweet['date']).'</a>';

		return "$authlink $text $tweet_link";
	}

	function get_author($uid) {
		global $conn;

		$res = $conn->query("select * from tw_users where uid = '$uid'");

		return $res->fetch_assoc();
	}

	function harvest_tweets($search,$num=100,$since=0,$max=0,$type="recent") {
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
		// TODO: fix issue with (v. recent) negative times!!!
		$ts = strtotime($date);
		$ago = time() - $ts; 

		if($ago <= 60) {
			return $ago."s";
		} elseif($ago <= 3600) {
			return floor($ago / 60)."m";
		} elseif($ago <= 86400) {
			return date("H:i",$ts);
		} elseif(date("Y") != date("Y",$ts)) {
			return date("j M Y",$ts);
		}

		return date("j M",$ts);
	}

	function qs_get($params) {
		$qs = "";

		foreach($params as $param => $val) {
			if($param == "archive") {
				continue;
			}
			
			$qs = "$qs&$param=$val";
		}

		return ltrim($qs,"&");
	}

	function qs_set_params($new_params) {
		$params = get_params();

		foreach($new_params as $param => $val) {
			$params[$param] = $val;
		}

		return qs_get($params);
	}

	function get_params() {
		$params = preg_split("/&/",$_SERVER['QUERY_STRING']);

		$pout = array();

		foreach($params as $param) {
			$parts = preg_split('/=/',$param);

			$pout[$parts[0]] = $parts[1];
		}

		return $pout;
	}
?>
