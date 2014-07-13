<?php
## OUTPUT BUFFER START ##
include("../inc/buffer.php");
## INCLUDES ##
include(basePath."/inc/debugger.php");
include(basePath."/inc/config.php");
include(basePath."/inc/bbcode.php");

## SETTINGS ##
$time_start = generatetime();
lang($language);
$where = "Installer";
$title = $pagetitle." - ".$where."";
## INSTALLER ##
if(isset($_POST['submit'])) {	

// alte Tabellen/Spalten l�schen
		db("DROP TABLE IF EXISTS ".$sql_prefix."events_info");
		db("DROP TABLE IF EXISTS ".$sql_prefix."events_user");
		db("DROP TABLE IF EXISTS ".$sql_prefix."events_comments");
		db("DROP TABLE IF EXISTS ".$sql_prefix."events_kat");
				
		db("ALTER TABLE ".$sql_prefix."permissions ADD `ev_kat` INT NOT NULL");
				
// neue Tabellen/Spalten anlegen
		db("CREATE TABLE ".$sql_prefix."events_info (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(100) NOT NULL,
			`autor_id` INT(10) NOT NULL,
			`veranstalter` VARCHAR(100) NOT NULL,
			`start` VARCHAR(12) NOT NULL,
			`ende`VARCHAR(12) NOT NULL,
			`beschreibung` LONGTEXT NOT NULL,
			`kat` INT(10) NOT NULL,
			`ort` VARCHAR(100),
			`gmaps` INT(1) DEFAULT '0',
			`show` INT(1) DEFAULT '0',
			`aufrufe` INT(10),
			PRIMARY KEY  (`id`)) ");	
	
		db("INSERT INTO ".$sql_prefix."events_info (`id`, `name`, `autor_id`, `start`, `ende`, `beschreibung`, `kat`, `ort`) 
			VALUES (1, 'Events Mod installiert', 1, 	'".time()."', '".time()."', 'Dies ist nur ein Test Eintrag', 1, 'Diese Seite hier (".$_SERVER['HTTP_HOST'].")')");	
			
		db("CREATE TABLE ".$sql_prefix."events_user (
			`eid` INT(10) NULL,
			`uid` INT(10) NULL,
			`status` INT(1) NULL,
			`time` VARCHAR(12),
			PRIMARY KEY (`eid`, `uid`)) ");
			
		db("INSERT INTO ".$sql_prefix."events_user (`eid`, `uid`, `status`, `time`) VALUES (1, 1, 0, '".time().")')");
	
			
		db("CREATE TABLE ".$sql_prefix."events_kat (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(32) NOT NULL,
			`katimg` VARCHAR(32),
			PRIMARY KEY (`id`)) ");
			
		db("INSERT INTO ".$sql_prefix."events_kat (`id`, `name`, `katimg`) VALUES (1, 'Events Testkategorie', NULL)");
		db("INSERT INTO ".$sql_prefix."events_kat (`id`, `name`, `katimg`) VALUES (2, '�bernommene Events', NULL)");
		
		db("CREATE TABLE ".$sql_prefix."events_comments (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`eid` INT(10) NOT NULL,
			`nick` VARCHAR(20) NOT NULL DEFAULT '',
			`datum` INT(20) NOT NULL,
			`email` VARCHAR(130) NOT NULL DEFAULT '',
			`hp` VARCHAR(50) NOT NULL DEFAULT '',
			`reg` INT(5) NOT NULL DEFAULT '0',
			`comment` TEXT NOT NULL,
			`ip` VARCHAR(50) NOT NULL DEFAULT '',
			`editby` TEXT NOT NULL,
			PRIMARY KEY (`id`))");
	
$qry = db("SELECT * FROM ".$sql_prefix."events");
        while($get = mysql_fetch_array($qry))
        {
		db("INSERT INTO ".$sql_prefix."events_info (start,ende,name,beschreibung,autor_id,kat) VALUES 
		('".$get["datum"]."',
		'".$get["datum"]."',
		'".$get["title"]."',
		'".$get["event"]."',
		'".$userid."',
		'2')");
		}
				
// Check ob Install i.O. velief		
		if(cnt($sql_prefix."events_info") > '0') {
    $show = '<tr>
               <td class="contentHead" align="center"><span class="fontGreen"><b>Installation erfolgreich!</b></span></td>
             </tr>
             <tr>
               <td class="contentMainFirst"  align="center">
                 Die ben&ouml;tigten Tabellen konnten erfolgreich erstellt werden.<br>
                 <br>
                 <b>L&ouml;sche unbedingt den installer-Ordner!</b>
               </td>
             </tr>
             <tr>
               <td class="contentBottom"></td>
             </tr>';
  } else {
    $show = '<tr>
               <td class="contentHead" align="center"><span class="fontWichtig"><b>FEHLER</b></span></td>
             </tr>
             <tr>
               <td class="contentMainFirst" align="center">
                 Bei der Installation des Mods ist ein Fehler aufgetreten. Bitte &uuml;berpr&uuml;fe deine Datenbank auf Sch&auml;den und versuche die Installation erneut.
               </td>
             </tr>
             <tr>
               <td class="contentBottom"></td>
             </tr>';
  }
} else {
  $show = '<tr>
             <td class="contentHead" align="center"><b>Events - Installation</b></td>
           </tr>
           <tr>
             <td class="contentMainFirst" align="center">
               Hallo und herzlichen Dank, dass du diese Modifikation runtergeladen hast. Dieser Installer soll dir die Arbeit abnehmen, die ben&ouml;tigten Tabellen in der Datenbank manuell erstellen zu m&uuml;ssen.<b>
               <br /><br />
               <b><span style="text-align:center"><u>!!!! WICHTIG !!!!</u></span><br />Erstell vor dem ausf&uuml;hren des Installers ein Datenbank BackUp. Wir haften f&uuml;r keine Sch&auml;den!</b><br />
               <br />
             </td>
           </tr>
           <tr>
             <td class="contentBottom" align="center">
               <form action="?action=install" method="POST">
                 <input class="submit" type="submit" name="submit" value="Tabellen anlegen">
               </form>
             </td>
           </tr>';
}
## SETTINGS ##
$time_end = generatetime();
$time = round($time_end - $time_start,4);
page($show, $title, $where,$time);
?>
