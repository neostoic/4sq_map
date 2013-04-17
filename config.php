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

  //Your personal Foursquare RSS feed
  //retrieve your own URL at https://foursquare.com/feeds
  //e.g. http://feeds.foursquare.com/history/8eb7a6350886ef4f4b680d89933fb4fc.rss
  $feed_url = '';

  $alt_title = ''; //alternative title, leave empty if not used
  $refresh_every = 5; //refresh feed every x minutes (5 minutes is more than enough in most cases)
  $default_zoom = 15; //default map zoom (optimal values are between 13 and 16)
  $max_markers = 10; //max number of visible markers (more markers will slow down the map and load time)
  $map_type = 'ROADMAP'; //valid values are ROADMAP, HYBRID, TERRAIN, MONOTONE, LIGHT, CROSSOVER

?>