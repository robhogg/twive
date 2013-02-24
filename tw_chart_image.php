<?php
   require_once("tw_lib.php");
   $params = parse_params();

   $period = date("j M Y a",strtotime($params['chartwe']));

   if(preg_match('/^week/',$params['chart'])) {
      $period = "w/e ".$period;
      $hoursminus = 168;
   } else {
      $hoursminus = 24;
   }

   $type = (preg_match('/user$/',$params['chart']))?"Users":"Tweets";

   $chart_title = "Tweets";
   $chartfrom =  date("Y-m-d H:i:s",
      strtotime($params['chartwe']) - $hoursminus * 3600 + 1);
   $chartto = $params['chartwe'];

   $data = get_chart_data($params['archive'],$params['chart'],
      $chartfrom,$chartto,$params['crit']);

   $url = "http:".google_chart_link($data,urlencode("$type $period"),
      "300x200");
   $ch = curl_init($url);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
   curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
   $img = curl_exec($ch);
   curl_close($ch);
   header("Content-Type: image/png");
   header("Content-Length: ".strlen($img));
   echo($img);
?>
