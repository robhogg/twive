<?php
      require_once("tw_lib.php");

      if(! isset($paramms['archive'])) {
         $params = parse_params();
      }
      
      $pinlink = urlencode("http://".$_SERVER['HTTP_HOST']
         .$_SERVER['REQUEST_URI']);
      $chttype = (isset($params['chart']))?$params['chart']:"week";
      $pinimg = urlencode("http://".$_SERVER['HTTP_HOST']
         .dirname($_SERVER['PHP_SELF'])
         ."/tw_chart_image.php?chart=$chttype"
         ."&chartwe=".urlencode($params['chartwe'])
         ."&archive=".$params['archive']);
      $audience = (isset($params['chart']) && preg_match('/user$/',
         $params['chart']))?"Active users":"Tweets";
      $date = date('j M Y',strtotime($params['chartwe']));
      if(! isset($params['chart']) || preg_match('/^week/',$params['chart'])) {
         $date = "w/e $date";
      }
      $pindesc = urlencode("$audience for ".$params['archive'].", $date");
      echo("<a data-pin-config=\"above\" href=\""
         ."//pinterest.com/pin/create/button/?url=$pinlink"
         ."&amp;media=$pinimg&amp;description=$pindesc\" "
         ."data-pin-do=\"buttonPin\" ><img "
         ."src=\"//assets.pinterest.com/images/pidgets/pin_it_button.png\" "
         ."alt=\"Pinterest button\"></a>");
?>
