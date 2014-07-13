<?php
function events()
{
  global $db,$sql_prefix;

 $qry = db("SELECT id, name, start FROM ".$sql_prefix."events_info
              WHERE (`start` >= ".time()." OR (`start` <= ".time()." AND `ende` >= ".time().")) AND `show` = 1
              ORDER BY `start`
              LIMIT ".config('m_events')."");
   while($get = _fetch($qry))
   {
     $events = show(_ev_next_event_link, array("datum" => date("d.m.",$get['start']),
                                            "id" => $get['id'],
                                            "event" => $get['name']));

  	 $eventbox .= show("menu/event", array("events" => $events));
   }

   return empty($eventbox) ? '<center style="margin:2px 0">'._no_events.'</center>' : '<table class="navContent" cellspacing="0">'.$eventbox.'</table>';;
}
?>