# extended Events AddOn- ReadMe 

Mit diesem AddOn bringt erweitert ihr euren Kalender, hier eine kleine Übersicht der Funktionen:
* Kategorien für Termine
* Detaillierte Angaben zu Terminen
* User können zusagen/absagen
* Termine können kommentiert werden

Bei Problemen, Fehlern, Anregungen oder Fragen, wendet euch gerne an mich.
 
## Copyright & Haftungsausschluss 
Wir übernehmen keinerlei Verantwortung, für Schäden die durch das einbinden der Mod/des Addons entstehen. Das einbinden und nutzen erfolgt demnach auf eigene Gefahr.

 Vor den einbinden der Mod/des Addons sollte eine Datenbanksicherung durchgeführt werden.  
   
## Installation 
Entpackt das Archiv in einen beliebigen Ordner. Wenn Ihr es entpackt habt, findet Ihr in den entpackten Ordner 4 weitere Ordner (vorhanden, _install, PHP und Template).

Damit es zu keinen Fehler kommt, müssen zuerst die Tabellen in der Datenbank angelegt werden. Hierfür habe ich einen kleinen Installer geschrieben, welchen sich im Ordner _install befindet. Ladet diesen Ordner in das Hauptverzeichnis eures deV!L'z Clanportals.

Ruft anschliesend eure Seite auf und fügt hinter die Adresse folgendes ein:

```
/_install/install.php
```

Wenn die Installation erfolgreich verlief löscht zur Sicherheit den Installer-Ordner von euren Webspace.


Nun müssen die restlichen Dateien hochgeladen werden. Den Inhalt aus dem "PHP Ordner"  müsst Ihr in das Hauptverzeichnis des deV!L'z Clanportal hochladen. Das Hauptverzeichnis ist das oberste Verzeichnis des deV!L'z Clanportals in welchen sich unter anderen die Dateien __readme.html, antispam.php, index.php, popup.html und die ganzen Ordner der einzelnen Bereiche befinden.

Den Inhalt des "Templates" Ordner müsst Ihr in das Verzeichnis eures Templates hochladen (Pfad: inc/_templates_/TEMPLATE).


In dem Ordner vorhanden, sind Dateien enthalten, die du schon besitzt, wenn du noch keine Änderungen in diesen Dateien gemacht hast, kannst du sie einfach ï¿½berschreiben. Andernsfalls musst du die wie folgt anpassen:
 
### inc/menu-functions/kalender.php 
Sucht nach: 
```
global $db;
```

und macht daraus: 
```
global $db, $sql_prefix;
```

Sucht nach: 
```
$qry = db("SELECT datum,title FROM ".$db['events']." WHERE DATE_FORMAT(FROM_UNIXTIME(datum), '%d.%m.%Y') = '".cal($i).".".$monat.".".$jahr."'");
```
 bis 
``` 
} else {
            $event = "";
            $titleev = "";
          }
```
 und ersetzt es durch: 
```
$qry = db("SELECT s1.*, s2.name as katname FROM ".$sql_prefix."events_info as s1 JOIN ".$sql_prefix."events_kat as s2
                   WHERE s1.kat = s2.id AND DATE_FORMAT(FROM_UNIXTIME(start), '%d.%m.%Y') <= '".cal($i).".".$monat.".".$jahr."' AND DATE_FORMAT(FROM_UNIXTIME(ende), '%d.%m.%Y') >= '".cal($i).".".$monat.".".$jahr."' AND s1.show = 1");
        
		  if(_rows($qry))
          {
            while($get = _fetch($qry))
            {
			
			 
			$pfadbild = "inc/images/eventkat/".$get['kat'];
			if(file_exists(basePath."/".$pfadbild.".gif"))     $bild = '<img src=../'.$pfadbild.'.gif class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".jpg")) $bild = '<img src=../'.$pfadbild.'.jpg class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".png")) $bild = '<img src=../'.$pfadbild.'.png class=icon  alt= />';	
			else $bild = "<img src=../inc/images/event.gif class=icon alt= />";  
			
              $event = "set";
              $titleev .= '<tr><td>'.$bild.jsconvert(re($get['katname']).': '.re($get['name'])).'</td></tr>';
            }
```
 
### inc/menu-functions/events.php 
Diese Datei ist so klein, die könnt ihr ersetzten, oder einmal die paar Zeilen vergleichen mit dem hier: 
```
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
```
### user/case_userlobby.php 
Sucht nach: 
```
/** Kalender Events anzeigen */
```
 löscht nun die folgenden Zeilen bis: 
``` 
/** Neue Awards anzeigen */
```
 und fügt stattdessen dies ein: 
```
$qrykal = db("SELECT count(id) as num, start, ende FROM ".$sql_prefix."events_info
                  WHERE (`start` >= ".time()." OR (`start` <= ".time()." AND `ende` >= ".time().")) AND `show` = 1
                  GROUP BY id ORDER BY start LIMIT 1");
 
    $getkal = _fetch($qrykal);
if($getkal['num'] != 0) {
      if(date("d.m.Y H:i",$getkal['start']) <= date("d.m.Y H:i", time()) AND date("d.m.Y H:i",$getkal['ende']) >= date("d.m.Y H:i", time()))
      {
        $nextkal = show(_ev_userlobby_kal_now, array("time" => mktime(0,0,0,date("m",$getkal['start']),
                                                              date("d",$getkal['start']),date("Y",$getkal['start']))));
	  } 
	  elseif(date("d.m.Y",$getkal['start']) == date("d.m.Y", time()) AND date("d.m.Y H:i",$getkal['start']) >= date("d.m.Y H:i", time()))
	  {
              $nextkal = show(_ev_userlobby_kal_today, array("time" => mktime(0,0,0,date("m",$getkal['start']),
                                                              date("d",$getkal['start']),date("Y",$getkal['start']))));
	  }	  
       else {
        $nextkal = show(_ev_userlobby_kal_not_today, array("time" => mktime(0,0,0,date("m",$getkal['start']),
                                                                  date("d",$getkal['start']),date("Y",$getkal['start'])),
                                                        "date" => date("d.m.Y", $getkal['start'])));
      }
} else {
$nextkal = _no_events;
}
```
 
### kalender/index.php 
Sucht nach: 
```
        $qry = db("SELECT datum,title FROM ".$db['events']."
                   WHERE DATE_FORMAT(FROM_UNIXTIME(datum), '%d.%m.%Y') = '".cal($i).".".$monat.".".$jahr."'");
        if(_rows($qry))
        {
          while($get = _fetch($qry)) $infoEvent .= '<img src=../inc/images/event.gif class=icon alt= /> '.jsconvert(_kal_event.re($get['title']));
          
         $info = ' onmouseover="DZCP.showInfo(\'<tr><td>'.$infoEvent.'</td></tr>\')" onmouseout="DZCP.hideInfo()"';
          $event = '<a href="?action=show&time='.$datum.'"'.$info.'><img src="../inc/images/event.gif" alt="" /></a>';
        } else {
          $event = "";
        }
```
 und ersetzt es durch: 
``` 
$qry = db("SELECT s1.*, s2.name as katname FROM ".$sql_prefix."events_info as s1 JOIN ".$sql_prefix."events_kat as s2
                   WHERE s1.show = 1 AND s1.kat = s2.id AND DATE_FORMAT(FROM_UNIXTIME(start), '%d.%m.%Y') <= '".cal($i).".".$monat.".".$jahr."' AND DATE_FORMAT(FROM_UNIXTIME(ende), '%d.%m.%Y') >= '".cal($i).".".$monat.".".$jahr."'");
        if(_rows($qry))
        {
          while($get = _fetch($qry)) 
		  {
 
			$pfadbild = "inc/images/eventkat/".$get['kat'];
			if(file_exists(basePath."/".$pfadbild.".gif"))     $bild = '<img src=../'.$pfadbild.'.gif class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".jpg")) $bild = '<img src=../'.$pfadbild.'.jpg class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".png")) $bild = '<img src=../'.$pfadbild.'.png class=icon  alt= />';	
			else $bild = "<img src=../inc/images/event.gif class=icon alt= />";  

		  $infoEvent .= $bild.jsconvert(re($get['katname']).': '.re($get['name'])).'<br>';
		  }
          
          $info = ' onmouseover="DZCP.showInfo(\'<tr><td>'.$infoEvent.'</td></tr>\')" onmouseout="DZCP.hideInfo()"';
          $event = '<a href="../event/?action=list&time='.$datum.'"'.$info.'><img src="../inc/images/event.gif" alt="" /></a>';
        } else {
          $event = "";
        }
```
 
Nun löscht ihr noch /admin/menu/kalender.php und /admin/menu/kalender.gif. Und wenn ich jetzt nichts vergessen habe und ihr alles richtig gemacht habt, sollte alles funktionerien.  
