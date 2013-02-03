<?php
   */
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

	require("config.php");

	$conn = @new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

	$cfg = array(
		'sorting' => array("date-" => "Newest first","date+" => "Oldest first",
			"username+" => "Sender - A-Z", "username-" => "Sender - Z-A"),
		'params' => array("archive" => array("[-_a-zA-Z0-9]+",null),
			"page" => array("[0-9]+",1),"perpage" => array("[1-9][0-9]*",25),
			"q" => array(".+",null),"sort" => array("(date|username)[-+]","date-"),			  "chart" => array("week|day",null))
	);

	$params = parse_params();
	
	function get_header($search) {
		global $params;
		echo("<h1><a href=\"/twive/".$params['archive']."\">Twitter archive for "
			."<span class=\"archname\">$search</span></a></h1>\n");
	}

	function get_controls($page,$pages) {
		global $cfg;

		$params = get_params();

		$paging = "<span id=\"paging\">";

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

		if($page < $pages - 1) {
			$qs = qs_set_params(array("page" => $pages));
			$paging .= " <a href=\"$uri?$qs\" id=\"last_page\">&gt;|</a>";
		} else {
			$paging .= " &gt;|";
		}

		$paging .= "</span>\n\n";

		$numbering = "<span id=\"pagenum\">Page $page of $pages</span>\n\n";

		$search = "<form method=\"get\" action=\"$uri\" id=\"searchbar\">\n";
		
		$sval = (isset($params['q']))?$params['q']:"";
		
		$search .= "<input type=\"text\" size=\"20\" name=\"q\" "
			."id=\"tsearch\" value=\"$sval\" />\n";

		$search .= "<select name=\"sort\" id=\"tsort\">\n";
		foreach ($cfg['sorting'] as $val => $title) {
			$sel = (isset($params['sort']) && urldecode($params['sort']) == $val)?
				' selected="selected"':"";
			$search .= "<option value=\"$val\"$sel>$title</option>\n";
		}
		$search .= "</select>\n";
		
		$search .= "<input type=\"submit\" value=\"Go\" />\n";
		
		if(isset($params['page']) && $params['page'] <= $pages) {
			$search .= "<input type=\"hidden\" name=\"page\" "
				."id=\"tpage\" value=\"".$params['page']."\" />\n";
		} elseif(isset($params['page'])) {
			$search .= "<input type=\"hidden\" name=\"page\" "
				."id=\"tpage\" value=\"$pages\" />\n";
		}

		if(isset($params['perpage'])) {
			$search .= "<input type=\"hidden\" name=\"perpage\" "
				."id=\"tperpage\" value=\"".$params['perpage']."\" />\n";
		}

		foreach(array("page","perpage") as $retain) {
			if(isset($params[$retain])) {
				$search .= "<input type=\"hidden\" name=\"$retain\" "
					."id=\"t$retain\" value=\"$params[$retain]\" />\n";
			}
		}


		$search .= "</form>\n";

		$unset = "";

		if(isset($params['q']) && $params['q'] != "") {
			$unset = "<a href=\"$uri?".qs_set_params(array("q" => ""))
				."\">Clear search</a>";
		}

		if(isset($params["chart"])) {
			$qs = qs_set_params(array("chart" => ""));
			$chart = "<a href=\"$uri?$qs\">Hide&nbsp;Chart</a>";
		} else {
			$qs = qs_set_params(array("chart" => "week"));
			$chart = "<a href=\"$uri?$qs\">Show&nbsp;Chart</a>";
		}

		$sp = "&nbsp;&nbsp;&nbsp;";
		echo "$paging $sp $numbering $sp $unset $sp $search $sp $chart";

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
		$criteria = "") {
		global $conn;

		$sql = "select * from tw_tweets tw, tw_users us "
			."where archive = '$archive' and tw.uid = us.uid ";
			
		$sql .= ($criteria == "")?"":"and $criteria ";

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

	function get_num_tweets($archive,$criteria = "") {
		global $conn;
		$sql = "select tw.tid "
			."from tw_tweets tw, tw_users us "
			."where archive = '$archive' "
			."and tw.uid = us.uid";

		$sql .= ($criteria == "")?"":" and $criteria";

		$conn->query($sql);

		return $conn->affected_rows;
	}

	// TODO: add different chart options
	function get_chart_data($archive,$type,$from,$to,$crit) {
		global $conn;

		$sql = "select date_format(date,'%Y-%m-%d %p') as label, count(*) as num "
			."from tw_tweets tw, tw_users us where tw.uid = us.uid and "
			."date between '$from' and '$to' ";
		
		$sql .= ($crit != "")?" and $crit ":" ";
			
		$sql .= "group by label order by label";

		$res = $conn->query($sql);

		$data = array();
		$max = 0;
		while($row = $res->fetch_assoc()) {
			array_push($data,$row);
			$max = ($max < $row['num'])?$row['num']:$max;
		}

		array_push($data,array("max" => $max));

		return $data;
	}

	// TODO: investigate proper graphic library
	function draw_chart($data) {
		$params = array_pop($data);
		$col_width = 100 / count($data);

		$values = "";
		$labels = "";
		$chart = "";
		foreach($data as $datum) {
			$values .= "<div class=\"chart_values\" style=\"width: "
				."$col_width%\">".$datum['num']."</div>\n";
			$labl = preg_replace("/([0-9]{4})-([0-9]{2})-([0-9]{2}) (AM|PM)/",
				'$3/$2<br />$4',$datum['label']);
			$labels .= "<div class=\"chart_label\" style=\"width: "
				."$col_width%\">$labl</div>\n";
			// "out of 101" because a div with height 0 has width 0
			$col_height = 101 - (100 / $params['max']) * $datum['num'];
			$chart .= "<div id=\"bar-".$datum['label']."-".$datum['num']."\" "
				."class=\"chart_bar\" style=\"width: "
				."$col_width%; height: $col_height%\">&nbsp;</div>\n";
		}

		return "<div id=\"chart_values\">\n$values\n</div>\n"
			."<div id=\"chart_pane\">\n$chart\n</div>\n"
			."<div id=\"chart_labels\">\n$labels\n</div>\n";
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
			
			$qs = "$qs&amp;$param=$val";
		}

		return preg_replace('/^&amp;/','',$qs);
	}

	function qs_set_params($new_params) {
		$params = get_params();

		foreach($new_params as $param => $val) {
			if($val == "") {
				unset($params[$param]);
			} else {
				$params[$param] = $val;
			}

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

	function parse_params() {
		global $conn,$cfg;
		$p = get_params();
		$pout = array();

		foreach($cfg['params'] as $param => $pset) {
			if(isset($p[$param]) && 
				preg_match('/'.$pset[0].'/',urldecode($p[$param]))) {
				$pout[$param] = $conn->real_escape_string(urldecode($p[$param]));
			} elseif(! is_null($pset[1])) {
				$pout[$param] = $pset[1];
			}
		}

		$pout['crit'] = ($pout['q'])?"(tw.text like '%".$pout['q']."%' "
				."or us.username like '%".$pout['q']."%') ":"";

		return $pout;
	}
?>
