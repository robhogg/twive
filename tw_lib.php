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

	require("config.php");

	$conn = @new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

	$cfg = array(
		'tables' => array("tw_tweets" => "tw", "tw_users" => us,
			"tw_archive" => "ar"),
		'sorting' => array("date-" => "Newest first","date+" => "Oldest first",
			"username+" => "Sender - A-Z", "username-" => "Sender - Z-A"),
		'params' => array("archive" => array("[-_a-zA-Z0-9]+",null),
			"page" => array("[0-9]+",1),"perpage" => array("[1-9][0-9]*",25),
			"q" => array(".+",null),"sort" => array("(date|username)[-+]","date-"),
			"chart" => array("week|day",null),"stats" => array("show",null),
			"cloud" => array("keyword|hash",null))
	);

	$params = parse_params();
	
	function get_header($search) {
		global $params;
		echo("<h1><a href=\"/twive/".$params['archive']."\">Twitter archive for "
			."<span class=\"archname\">$search</span></a></h1>\n");
	}

	/*
	* Optional argument $full - if set to 0 only returns paging controls
	*/
	function get_controls($page,$pages,$full=1) {
		global $cfg;
		$sp = "&nbsp;&nbsp;&nbsp;";

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

		if($full == 0) {
			echo "<div class=\"controls1 list-foot\">$paging $sp $numbering</div>";
			return;
		}

		$search = "<form method=\"get\" action=\"$uri\" id=\"searchbar\">\n";
		
		$sval = (isset($params['q']))?$params['q']:"";
		$sval = preg_replace('/"/','&quot;',$sval);
		$search .= "<input type=\"text\" size=\"20\" name=\"q\" "
			."id=\"tsearch\" value=\"$sval\" />\n";

		$search .= "<select name=\"sort\" id=\"tsort\">\n";
		foreach ($cfg['sorting'] as $val => $title) {
			$sel = (isset($params['sort']) && $params['sort'] == $val)?
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
			$qs = qs_set_params(array("chart" => "week","stats" => "",
				"cloud" => ""));
			$chart = "<a href=\"$uri?$qs\">Show&nbsp;Chart</a>";
		}

		if(isset($params["stats"])) {
			$qs = qs_set_params(array("stats" => ""));
			$stats = "<a href=\"$uri?$qs\">Hide&nbsp;Stats</a>";
		} else {
			$qs = qs_set_params(array("stats" => "show","chart" => "",
				"cloud" => ""));
			$stats = "<a href=\"$uri?$qs\">Show&nbsp;Stats</a>";
		}

		if(isset($params["cloud"])) {
			$qs = qs_set_params(array("cloud" => ""));
			$cloud = "<a href=\"$uri?$qs\">Hide&nbsp;Cloud</a>";
		} else {
			$qs = qs_set_params(array("stats" => "","chart" => "",
				"cloud" => "keyword"));
			$cloud = "<a href=\"$uri?$qs\">Keyword&nbsp;Cloud</a>";
		}

		echo "<div class=\"controls1\">$paging $sp $numbering $sp $search</div>"
			."<div class=\"controls2\">$chart $sp $stats $sp $cloud $sp $unset</div>";

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

		$sql = "select tw.*,us.username from tw_tweets tw, tw_users us, "
			."tw_archive_link al where tw.uid = us.uid and tw.tid = al.tid "
			."and al.archive = '$archive' ";

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
			."from tw_tweets tw, tw_users us, tw_archive_link al "
			."where tw.uid = us.uid and tw.tid = al.tid "
			."and al.archive = '$archive' ";

		$sql .= ($criteria == "")?"":" and $criteria";

		$conn->query($sql);

		return $conn->affected_rows;
	}

	/*
	* Returns basic stats for archive. One mandatory parameter (archive name)
	* (and one optional - the query string, though stats for a particular
	* search is still TODO);
	*/
	function get_stats($archive,$q = "") {
		global $conn;

		if($q == "") {
			$stat['title'] = "Summary stats for $archive";
		} else {
			$stat['title'] = "Stats for current search in $archive";
			$t_crit = "and ".parse_search($q,"tw_tweets");
			$u_crit = "and ".parse_search($q,"tw_users");
			$crit = "and ".parse_search($q);
		}

		$total_sql = "select count(*) as num from tw_tweets tw, tw_archive_link al where "
			."tw.tid = al.tid and al.archive = '$archive' $t_crit";
		$perday_sql = "select count(*) as num from tw_tweets tw, tw_archive_link al where "
			."tw.tid = al.tid and al.archive = '$archive' group by date_format(date,'%Y-%m-%d') "
			."order by count(*)";
		$users_sql = "select count(distinct tw.uid) as num "
			."from tw_tweets tw, tw_users us, tw_archive_link al where tw.uid = us.uid " 
			."and tw.tid = al.tid and al.archive = '$archive' $t_crit $u_crit";
		$peruser_sql = "select count(*) as num from tw_tweets tw, tw_archive_link al where "
			."tw.tid = al.tid and al.archive = '$archive' group by uid "
			."order by count(*)";
		$top10_sql = "select us.username as name, tw.uid as id, "
			."count(*) as num from tw_tweets tw, tw_users us, tw_archive_link al "
			."where tw.uid = us.uid and tw.tid = al.tid and al.archive = '$archive' $crit "
			."group by name order by num desc limit 10";
		$earliest_sql = "select date,tid as tweet from tw_tweets tw where "
			."date = (select min(date) from tw_tweets sub, tw_archive_link al where "
			."sub.tid = al.tid and al.archive = '$archive' $t_crit) order by tid";
		$latest_sql = "select date,tid as tweet from tw_tweets tw where "
			."date = (select max(date) from tw_tweets sub, tw_archive_link al where "
			."sub.tid = al.tid and al.archive = '$archive' $t_crit) order by tid desc";
		
		$res = $conn->query($total_sql);
		$row = $res->fetch_assoc();
		$stat["num_tweets"] = $row['num'];

		$res = $conn->query($perday_sql);
		$num_days = $conn->affected_rows;
		if($num_days % 2 == 0) {
			$res->data_seek($num_days / 2 - 1);
			$r1 = $res->fetch_assoc();
			$r2 = $res->fetch_assoc();

			$stat['median_tweets'] = ($r1["num"] + $r2["num"]) / 2;
		} else {
			$res->data_seek(floor($num_days / 2));
			$r = $res->fetch_assoc();
			$stat['median_tweets'] = $r["num"]; 
		}

		$res = $conn->query($users_sql);
		$row = $res->fetch_assoc();
		$stat["num_users"] = $row['num'];

		$res = $conn->query($peruser_sql);
		if($stat['num_users'] % 2 == 0) {
			$res->data_seek($stat['num_users'] / 2 - 1);
			$r1 = $res->fetch_assoc();
			$r2 = $res->fetch_assoc();

			$stat['median_users'] = ($r1["num"] + $r2["num"]) / 2;
		} else {
			$res->data_seek(ceil($stat['num_users'] / 2));
			$r = $res->fetch_assoc();
			$stat['median_users'] = $r["num"];
		}

		$res = $conn->query($top10_sql);
		$stat["top10"] = array();
		while($row = $res->fetch_assoc()) {
			array_push($stat["top10"],$row);
		}

		$res = $conn->query($earliest_sql);
		$stat["earliest"] = $res->fetch_assoc();
		$res = $conn->query($latest_sql);
		$stat["latest"] = $res->fetch_assoc();
		
		$stat["period"] = strtotime($stat['latest']['date']) - 
			strtotime($stat['earliest']['date']);

		return $stat;
	}

	function show_stats($archive,$q = "") {
		$stats = get_stats($archive);

		$uri = preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']);
		?>
			<h2><?php echo $stats['title']; ?></h2>
			<ul id="archive_stats">
				<li><strong>Total tweets: </strong><?php 
					echo $stats['num_tweets']; ?></li>
				<li><strong>Users in archive: </strong><?php 
					echo $stats['num_users']; ?></li>
				<li><strong>Max tweets by user: </strong><?php 
					echo $stats['top10'][0]["num"]; ?></li>
				<li><strong>Median tweets per user: </strong><?php 
					echo $stats['median_users']; ?></li>
				<li><strong>First tweet: </strong><?php 
					echo  date("j M, Y H:i:s",
						strtotime($stats['earliest']['date']));?></li>
				<li><strong>Latest tweet: </strong><?php 
					echo date("j M, Y H:i:s",
						strtotime($stats['latest']['date']));?></li>
				<li><strong>Total period: </strong><?php 
						echo round($stats['period'] / 86400, 1); ?> days</li>
				<li><strong>Mean tweets per day: </strong><?php 
					echo round($stats['num_tweets'] * 86400 / $stats['period'],
						1); ?></li>
				<li><strong>Median tweets per day: </strong><?php 
					echo $stats['median_tweets']; ?></li>
			</ul>
		<?php

	}

	// TODO: add different chart options
	function get_chart_data($archive,$type,$from,$to,$crit) {
		global $conn;

		$sql = "select date_format(date,'%Y-%m-%d %p') as label, count(*) as num "
			."from tw_tweets tw, tw_users us, tw_archive_link al "
			."where tw.tid = al.tid and al.archive = '$archive' and "
			."tw.uid = us.uid and date between '$from' and '$to' ";
		
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

		if(count($data) == 0) {
			return "<h2>No chart data for period</h2>";
		}
		
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
		$authlink = '<a href="http://twitter.com/'.$tweet['username'].'" '
			.'class="tweet-auth">'.$tweet['username'].':</a>';
		
		$text = preg_replace('/(https?:\/\/[^ ]*)/',
			"<a href=\"$1\" class=\"tweet-link\">$1</a>",
			$tweet['text']);
		$text = preg_replace('/@([a-zA-Z0-9_]+)/',
			"<a href=\"http://twitter.com/$1\" class=\"tweet-to\">@$1</a>",
			$text);
		$text = preg_replace('/#([a-zA-Z0-9_]+)/',
			"<a href=\"http://twitter.com/search?q=%23$1\" class=\"tweet-hash\">"
			."#$1</a>",$text);

		$tweet_link = '<a href="http://twitter.com/'.$tweet['username'].'/status/'
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

	/*
	* Insert tweet data into tw_tweets and/or link into tw_archive_link
	*
	* $tweet is Twitter result object, $archive is string.
	*
	* Returns 1 on success, -1 if tweet and link already stored, 0 on error.
	*/
	function save_tweet($tweet,$archive) {
		global $conn;

		$tid = $conn->real_escape_string($tweet->id_str);
		$uid = $conn->real_escape_string($tweet->from_user_id_str);

		$rval = -1;

		$tcheck_sql = "select tid from tw_tweets where tid = '$tid'";

		$tcheck = $conn->query($tcheck_sql);

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
				."'$tid','$uid','$text','$date',"
				."'$reply_tweet','$reply_user')");
			
			if($conn->error == "") {
				$rval = 1;
			} else {
				error_log("Error writing $tid to tw_tweets: ".$conn->error);
				return 0;
			}

			extract_keywords($archive,$text);
		}

		$lcheck_sql = "select tid from tw_archive_link where tid = '$tid' "
			."and archive = '$archive'";

		$lcheck = $conn->query($lcheck_sql);

		if($conn->affected_rows == 0) {
			$conn->query("insert into tw_archive_link values ('$archive','$tid')");

			if($conn->error == "") {
				$rval = 1;
			} else {
				error_log("Error writing $archive, $tid to tw_archive_link: ".$conn->error);
				return 0;
			}
		}

		return $rval;

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
			
			$conn->query("insert into tw_users (uid,username,name,image) "
				."values ('$uid','$username','$name','$image')");
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
			
			$qs = "$qs&amp;$param=".urlencode($val);
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

			$pout[$parts[0]] = urldecode($parts[1]);
		}

		return $pout;
	}

	/*
	* parse query and return SQL
	*
	* if parameter $table is set, returns only criteria for that table,
	* otherwise, returns search for terms in tw_users or tw_tweets
	*
	* TODO: search options/intelligence
	*/
	function parse_search($search,$table="") {
		global $cfg, $conn;
		$t_search = "";
		$u_search = "";

		if(preg_match('/^(user:)([^ ]*)/',$search,$matches)) {
			$u_search .= 'us.username = "'.$matches[2].'" ';
			$search = preg_replace('/user:'.$matches[2].'/','',$search);
		}
		preg_match_all('/"[^"]*"/',$search,$quoted);

		// quoted terms matched exactly at word boundaries
		// TODO: escape regex special characters?
		foreach($quoted[0] as $term) {
			$term = trim(stripslashes($term),'"');
			$term = $conn->real_escape_string($term);
			$t_search .= 'text regexp \'[[:<:]]'.$term.'[[:>:]]\' and ';
			$search = preg_replace('/\\\"'.$term.'\\\"/','',$search);

		}
		// allow use of * and ? as wildcards 
		$search = preg_replace('/\*/','%',$search);
		$search = preg_replace('/\?/','_',$search);
		
		$terms = preg_split('/[ ,]/',trim($search));

		foreach($terms as $term) {
			// boolean options, get rid of empty terms and any explicit "and"
			if($term == "") {
				continue;
			} elseif(strtolower($term) == "or" && $t_search) {
				$t_search .= preg_replace('/and $/','or ',$t_search);
				continue;
			} elseif(strtolower($search[$i]) == "and") {
				continue;
			}
			$t_search .= "tw.text like '%$term%' and ";
		}

		$t_search = "(".preg_replace('/(and|or) $/','',$t_search).")"; 
		if($table == "" && $t_search != "" && $u_search != "") {
			return "($t_search and $u_search)";
		} elseif($table == "tw_users" || $u_search) {
			return $u_search;
		} else {
			return $t_search;
		}
	}

	function parse_params() {
		global $conn,$cfg;
		$p = get_params();
		$pout = array();

		foreach($cfg['params'] as $param => $pset) {
			if(isset($p[$param]) && 
				preg_match('/'.$pset[0].'/',$p[$param])) {
				$pout[$param] = $conn->real_escape_string($p[$param]);
			} elseif(! is_null($pset[1])) {
				$pout[$param] = $pset[1];
			}
		}

		$pout['crit'] = ($pout['q'] == "")?"":parse_search($pout['q']);

		return $pout;
	}

	function extract_keywords($archive,$text) {
		global $conn;

		$text = preg_replace('!https?://[^ ]*!','',$text);
		preg_match_all("/[@#]?[-'a-zA-Z]{3,}\b/",$text,$keywords);


		foreach($keywords[0] as $keyword) {
			$keyword = $conn->real_escape_string(trim($keyword,"\"'"));
			if(substr($keyword,0,1) == '@') {
				continue;
			}

			$test_sql = "select keyword,occurrences from tw_keywords "
				."where keyword = '$keyword' and archive = '$archive'";

			$res = $conn->query($test_sql);

			if($conn->affected_rows == 0) {
				$ins_sql = "insert into tw_keywords values ('$archive','$keyword',1)";

				$conn->query($ins_sql);
			} else {
				$row = $res->fetch_assoc();

				$occ = $row['occurrences'] + 1;

				$upd_sql = "update tw_keywords set occurrences = $occ "
					."where archive = '$archive' and keyword = '$keyword'";
			
				$conn->query($upd_sql);
			}
		}
	}

	/*
	* optional parameter $type can be "hash" or "keyword"
	* optional parameter $crit - get cloud for search (not implemented yet)
	*/
	function get_cloud($archive,$type="keyword",$crit="") {
		global $conn;
		$params = get_params();

		$rev = ($type == "hash")?"":" not";
		$where = "archive = '$archive' and keyword$rev like '#%' "
			."and keyword not in (select stop_word from tw_stop_words) "
			."order by occurrences desc limit 100";
		$kw_sql = "select keyword,occurrences from tw_keywords where $where";

		$res = $conn->query($kw_sql);

		$max = 0;
		$keywords = array();
		while($row = $res->fetch_assoc()) {
			$keywords[$row['keyword']] = $row['occurrences'];
			$max = ($row['occurrences'] > $max)?$row['occurrences']:$max;
		}

		ksort($keywords);

		$cloud = '<div class="tag_cloud">';

		foreach($keywords as $keyword => $occ) {
			$uri = preg_split('/\?/',$_SERVER['REQUEST_URI']);
			$cloud .= '<span class="tag" style="font-size: '
				.($occ / $max * 100 + 100).'%"><a href="'.$uri[0]
				.'?q=%22'.$keyword.'%22&amp;cloud='.$params['cloud'].'">'
				.$keyword.'</a> </span>';
		}
		$cloud .= '</div>';

		echo $cloud;
	}
?>
