<?php

 /**************************************************
  **                                              **
  **  YOUR PERSONAL                               **
  **  FOURSQUARE HISTORY                          **
  **  MAPPED                                      **
  **                                              **
  **  v2.0 (c) 2011 by David Schmidt              **
  **  http://individual8.com                      **
  **                                              **
  **  Email me with questions or any inquiries:   **
  **  david@individual8.com                       **
  **                                              **
  **************************************************/

  //Requires PHP 5

  //Please edit config.php,
  //to customize script for your needs!

  //NO NEED TO EDIT BELOW THIS LINE
  /////////////////////////////////////////////////

  require_once('config.php');

  if(isset($_GET['json'])) {
    $feed = simplexml_load_file($feed_url.'?count=5');
    if($feed->channel->item) {
      foreach($feed->channel->item as $k => $v) $items[] = array('title' => (string) $v->title,'link' => (string) $v->link,'pubdate' => strtotime((string) $v->pubDate),'guid' => (string) $v->guid,'georss' => explode(" ",$v->children('georss',TRUE)->point));
      $output = array((string) $feed->channel->title,(string) $feed->channel->link,array_reverse($items));
      header('Content-type: application/json; charset=utf-8');
      header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
      header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
      print json_encode($output);
    }
    exit;
  }

  //define map types
  $default_map_types = array('ROADMAP','HYBRID','TERRAIN');
  $custom_map_types = array('MONOTONE','LIGHT','CROSSOVER');
  $all_map_types = array_merge($default_map_types,$custom_map_types);
  //if($_SERVER["HTTP_HOST"] == 'individual8.com') $map_type = $all_map_types[array_rand($all_map_types)];

  //check for valid values
  if($refresh_every < 5) $refresh_every = 5;
  if($default_zoom < 13) $default_zoom = 13;
  if($default_zoom > 16) $default_zoom = 16;
  if($max_markers > 25) $max_markers = 25;
  if(!in_array($map_type,$all_map_types) || empty($map_type)) $map_type = 'ROADMAP';
?>
<!DOCTYPE html>
<html>
<head>
  <!-- 
  (c) 2011 by David Schmidt, http://individual8.com
  Licensed under Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
  Version 2.0
  -->
  <meta charset="utf-8" />
  <title></title>
  <style type="text/css">
    html{height:100%;overflow:hidden;}
    body{height:100%;margin:0;padding:0;overflow:hidden;}
    #header{background:#4DB8E0;height:30px;padding-left:10px;font:normal 12px/30px sans-serif;}
    #header h1{margin:0;font-size:16px;color:#fff;line-height:inherit;}
    #header span{font-size:14px;color:#fff;line-height:inherit;font-weight:normal;display:inline-block;border-left:1px dotted #fff;margin-left:7px;padding-left:8px;}
    #header div{margin:0;line-height:inherit;float:right;padding-right:10px;}
    #header a{color:#fff;text-decoration:none;}
    #header a:hover{color:#000;background:rgb(255,255,255);background:rgba(255,255,255,0.4);border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:1px 3px;margin:-1px -3px;text-decoration:none;}
    #footer{position:absolute;bottom:0;width:100%;background:#999;height:30px;font:normal 12px/30px sans-serif;white-space:nowrap;overflow:hidden;}
    #footer div{font-size:12px;color:#fff;line-height:inherit;font-weight:normal;display:inline-block;padding-left:10px;}
    #footer div#time{border-right:1px dotted #eee;margin-right:0;padding-right:8px;color:#ddd;}
    #footer span:first-child{background:none;padding-left:0;}
    #footer span{padding-left:18px;background:url('arrow.png') center left no-repeat;}
    #footer a{color:#fff;text-decoration:none;}
    #footer a:hover{color:#000;background:rgb(255,255,255);background:rgba(255,255,255,0.4);border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:1px 3px;margin:-1px -3px;text-decoration:none;}
    #footer .fade-out{position:absolute;height:100%;top:0;right:0;width:15px;background:url('bar_fade_out.png') left top repeat-y;display:block;padding:0;}
    #about{display:none;position:absolute;top:0;bottom:0;right:-240px;width:200px;background:#ddd;-moz-box-shadow:-2px 0 4px rgba(0,0,0,0.2);-webkit-box-shadow:-2px 0 4px rgba(0,0,0,0.2);box-shadow:-2px 0 4px rgba(0,0,0,0.2);font:normal 12px/30px sans-serif;color:#444;}
    #about .close{margin:0;line-height:inherit;float:right;padding-right:10px;}
    #about .info{margin:0 15px;line-height:16px;position:absolute;top:45px;}
    #about .info img{border:0;}
    #about .info p{margin:0 0 0.75em;}
    #about .info p.sep{margin:0 0 1em;border-bottom:1px dotted #444;padding:0 15px 1em 0;text-align:center;}
    #about .info p.download{padding-right:15px;margin-top:30px;text-align:center;}
    #about .author{margin:0 15px;line-height:16px;position:absolute;bottom:15px;text-align:right;width:170px;}
    #about .author img{float:right;padding-left:8px;border:0;}
    #about a{color:inherit;text-decoration:none;}
    #about a:hover{color:#fff;background:rgb(0,0,0);background:rgba(0,0,0,0.4);border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:1px 3px;margin:-1px -3px;text-decoration:none;}
    #about a.default:hover{background:transparent;border-radius:0;-moz-border-radius:0;-webkit-border-radius:0;padding:0;margin:0;text-decoration:none;}
    #error{display:none;position:absolute;left:0;right:0;top:100px;margin:0 auto;text-align:center;font:14px sans-serif;color:#4DB8E0;background:#fff;border-radius:20px;-moz-border-radius:20px;-webkit-border-radius:20px;width:400px;padding:20px;border:1px solid #4DB8E0;}
    #error h1{margin:0;font-size:1.5em;}
    #error h2{margin:0;font-size:1em;}
    #map_canvas{position:absolute;top:30px;bottom:30px;left:0;right:0;}
    .infowindow{font:12px sans-serif;color:#000;}
    .infowindow b{display:block;margin-top:0.2em;font-size:14px;}
    .infowindow a{color:inherit;text-decoration:none;}
    .infowindow a:hover{text-decoration:underline;}
    .infowindow .ts{margin-top:0.5em;font-style:italic;color:#999;}
  </style>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
  <script type="text/javascript">
    var map, geocoder, poly;
    var locations = [], markers = [], coords = [], guids = [], infowindows = [];
    var userActivity = false;
    var iterator = 0;
    var refreshEvery = <?php echo $refresh_every; ?>;
    var zoomTo = <?php echo $default_zoom; ?>;
    var maxMarkers = <?php echo $max_markers; ?>;
    var mapOptions = {zoom: 2,center: new google.maps.LatLng(0,0),disableDefaultUI: true,mapTypeId: <?php if(in_array($map_type,$default_map_types)) { echo 'google.maps.MapTypeId.'.$map_type; } else { echo 'google.maps.MapTypeId.'.$default_map_types[0]; } ?>};
    var image = new google.maps.MarkerImage("4sq_icon.png",new google.maps.Size(29,33),new google.maps.Point(0,0),new google.maps.Point(15,33));
    var image_scaled = new google.maps.MarkerImage("4sq_icon.png",new google.maps.Size(29,33),new google.maps.Point(0,0),new google.maps.Point(10,22),new google.maps.Size(20,22.75));
    var shadow = new google.maps.MarkerImage("4sq_shadow.png",new google.maps.Size(46,33),new google.maps.Point(0,0),new google.maps.Point(15,33));
    var shadow_scaled = new google.maps.MarkerImage("4sq_shadow.png",new google.maps.Size(46,33),new google.maps.Point(0,0),new google.maps.Point(10,22),new google.maps.Size(32,22.75));
    function initialize() {
      map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);
<?php if(!in_array($map_type,$default_map_types)) { ?>
      var monotoneStyle = [{featureType:"all",elementType:"all",stylers:[{gamma:1.6},{saturation:-99}]}];
      var lightStyle = [{featureType:"all",elementType:"all",stylers:[{gamma:1.85}]}];
      var crossoverStyle = [{featureType:"water",elementType:"all",stylers:[{visibility:"simplified"},{saturation:97},{hue:"#ff0066"}]},{featureType:"transit",elementType:"all",stylers:[{visibility:"simplified"}]},{featureType:"road",elementType:"all",stylers:[{visibility:"on"},{hue:"#00ff5e"}]},{featureType:"poi",elementType:"all",stylers:[{visibility:"simplified"},{hue:"#0091ff"}]},{featureType:"landscape.man_made",elementType:"all",stylers:[{invert_lightness:true}]}];
      var customMapOptions = {name: "Custom"}
      var customMapType = new google.maps.StyledMapType(<?php echo strtolower($map_type); ?>Style, customMapOptions);
      map.mapTypes.set('custom', customMapType);
      map.setMapTypeId('custom');
<?php } ?>  
      poly = new google.maps.Polyline({path: coords,strokeColor: "#4DB8E0",strokeOpacity: 1.0,strokeWeight: 2,map: map,geodesic: true});
      geocoder = new google.maps.Geocoder();
      google.maps.event.addListener(map, 'drag', function() {userActivity = true;closeInfowindows();});
      google.maps.event.addListener(map, 'idle', function() {userActivity = false;});
    }
    function getLocations() {
      $.getJSON('?json', function(data) {
        if(data) {
          $('#error:visible').fadeOut();
          if(!document.title) {<?php if($alt_title) echo 'data[0] = \''.$alt_title.'\';'; ?>document.title = data[0];$('#header h1').html(data[0] + ' <span><a href="' + data[1] + '">My Foursquare profile</a></span>');}
          locations = data[2];
          for(var i = 0; i < data[2].length; i++) {setTimeout(function(){addMarker();},i*500);}
          $('#time').text(printTimeDiff(locations[locations.length - 1].pubdate));
          iterator = 0;
        } else $('#error').fadeIn();
      });
      window.setTimeout('getLocations()',refreshEvery*60*1000);
    }
    function addMarker() {
      if(guids.indexOf(locations[iterator].guid) == -1) {
        for(var i=0;i < markers.length;i++) {markers[i].setIcon(image_scaled);markers[i].setShadow(shadow_scaled);}
        markers.push(new google.maps.Marker({position: new google.maps.LatLng(locations[iterator].georss[0], locations[iterator].georss[1]),map: map,draggable: false,icon: image,shadow: shadow}));
        infowindows.push(new google.maps.InfoWindow({content: '<div class="infowindow">Check-in at <b><a href="' + locations[iterator].link + '" target="_blank">' + locations[iterator].title + '</a></b><div class="ts">' + printDate(locations[iterator].pubdate) + '</div></div>'}));
        google.maps.event.addListener(markers[markers.length - 1], 'click', function() {closeInfowindows();if(map.getZoom()<14){map.setZoom(zoomTo);map.setCenter(this.getPosition());}infowindows[markers.indexOf(this)].open(map,this);});
        guids.push(locations[iterator].guid);
        coords.push(markers[markers.length - 1].position);
        $('#log').prepend('<span><a href="#">' + locations[iterator].title + '</a></span>');
        var place = new google.maps.LatLng(locations[iterator].georss[0], locations[iterator].georss[1]);
        $('#log span:first-child a').bind('click',function(){centerMap(place);return false;});
        if(!userActivity) {closeInfowindows();map.panTo(markers[markers.length - 1].position);map.setZoom(zoomTo);
          if(locations[locations.length - 1].guid == locations[iterator].guid) infowindows[infowindows.length - 1].open(map,markers[markers.length - 1]);
        }
        if(markers.length > maxMarkers) {markers[0].setMap(null);markers.shift();coords.shift();$('#log span').last().remove();}
        poly.setPath(coords);
      }
      iterator++;
    }
    function centerMap(place) {
      closeInfowindows();
      map.setZoom(zoomTo);
      map.panTo(place);
    }
    function closeInfowindows() {
      for(var i=0;i < infowindows.length;i++) infowindows[i].close();
    }
    function printDate(date) {
      var org = new Date(date*1000);
      var now = new Date();
      var diff = (now - org) / 1000;
      var d_names = new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
      var m_names = new Array('January','February','March','April','May','June','July','August','September','October','November','December');
      var curr_day = org.getDay();
      var curr_date = org.getDate();
      var sup = "";
      if (curr_date == 1 || curr_date == 21 || curr_date ==31) sup = "st";
      else if (curr_date == 2 || curr_date == 22) sup = "nd";
      else if (curr_date == 3 || curr_date == 23) sup = "rd";
      else sup = "th";
      var curr_month = org.getMonth();
      var curr_year = org.getFullYear();
      var a_p = '';
      var curr_hour = org.getHours();
      if (curr_hour < 12) a_p = 'am';
      else a_p = 'pm';
      if (curr_hour == 0) curr_hour = 12;
      if (curr_hour > 12) curr_hour = curr_hour - 12;
      var curr_min = org.getMinutes();
      curr_min = curr_min + '';
      if (curr_min.length == 1) curr_min = '0' + curr_min;
      var str = (d_names[curr_day] + ', ' + m_names[curr_month] + ' ' + curr_date + sup + ', ' + curr_year + ' - ' + curr_hour + ':' + curr_min + a_p);
      return str;
    }
    function printTimeDiff(ts) {
      var mode = mode || 'short';
      var h,m;
      var diff = (new Date().getTime() / 1000) - ts;
      h = parseInt(diff / (60 * 60));
      diff -= (h * (60 * 60));
      m = parseInt(diff / 60);
      if(mode == 'long') {
        if(h > 0) return (h == 1)?h + ' hour ago':h + ' hours ago';
        if(m > 0) return (m == 1)?m + ' minute ago':m + ' minutes ago';
        else return 'just a moment ago';
      } else if(mode == 'short') {
        //return new Date(ts).toString();
        if(h > 0) return h + ' h ago';
        if(m > 0) return m + ' min ago';
        else return 'just now';
      }
    }
    function openAbout() {$('#about').animate({width: 'toggle',right: 0});}
    function closeAbout() {$('#about').animate({width: 'toggle',right: $('#about').width() * -1.2})}
    $(document).ready(function() {
      initialize();
      getLocations();
      <?php if($_SERVER["HTTP_HOST"] == 'individual8.com') echo 'openAbout();'; ?>
    });
  </script>
</head>
<body>
  <div id="header"><div><a href="#" onclick="openAbout();return false">About</a></div><h1>&nbsp;</h1></div>
  <div id="map_canvas"></div>
  <div id="footer"><div id="time">&nbsp;</div><div id="log"></div><div class="fade-out"></div></div>
  <div id="about"><div class="close"><a href="#" onclick="closeAbout();return false">Close</a></div><div class="info"><p>Actively using Foursquare yourself? Why not creating a map like this for your site? It's free and licensed under a Creative Commons license.</p><p class="sep"><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" class="default"><img alt="Creative Commons License" src="http://i.creativecommons.org/l/by-nc-sa/3.0/80x15.png" /></a></p><p>All you need is a server running PHP 5, which is probably the case with most servers. Download the sources below, add your Foursquare feed and you're done.</p><p>The map won't just show your most recent check-ins, it'll also silently update every few minutes and check for new. Your audience will always know where to meet you.</p><p>Enjoy and have fun...</p><p class="download"><a href="http://goo.gl/8NyzO" class="default"><img src="download.png" /></a><br /><a href="http://goo.gl/8NyzO">Download files</a></p></div><div class="author"><a href="http://individual8.com" target="_blank"><img src="i8.png" /></a>Created and<br />distributed by</div></div>
  <div id="error"><h1>Can't access feed.</h1><h2>Either it's invalid or Foursquare's<br />temporarily down. Will retry shortly.</h2></div>
  <?php if($_SERVER["HTTP_HOST"] == 'individual8.com') include('tracking.php'); ?>
</body>
</html>