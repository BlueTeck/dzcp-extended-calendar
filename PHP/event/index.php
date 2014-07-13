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
$where = _site_kalender;
$title = $pagetitle." - ".$where."";
$dir = "event";
## SECTIONS ##
if(!isset($_GET['action'])) $action = "";
else $action = $_GET['action'];

switch ($action):
default:
header("Location: ../kalender/index.php"); 
break;
//#####################################################################################################################;
case 'list';
if(is_numeric($_GET['time']) && isset($_GET['time'])){

	$tag = date("d",$_GET['time']);
	$monat = date("m",$_GET['time']);
	$jahr = date("Y",$_GET['time']);
			   
	 $qry = db("SELECT s1.*, s2.name as katname FROM ".$sql_prefix."events_info as s1 JOIN ".$sql_prefix."events_kat as s2
                WHERE s1.show = 1 AND s1.kat = s2.id AND DATE_FORMAT(FROM_UNIXTIME(start), '%d.%m.%Y') <= '".$tag.".".$monat.".".$jahr."' 
				AND DATE_FORMAT(FROM_UNIXTIME(ende), '%d.%m.%Y') >= '".$tag.".".$monat.".".$jahr."' ORDER BY start DESC");
	 $filter = _kalender_admin_head.'<i> - '._ev_filter.': '.$tag.'.'.$monat.'.'.$jahr.'</i>';		
	 
} elseif (is_numeric($_GET['kat']) && isset($_GET['kat'])){
	$qry = db("SELECT s1.*, s2.name as katname FROM ".$sql_prefix."events_info as s1 JOIN ".$sql_prefix."events_kat as s2
               WHERE s1.show = 1 AND s1.kat = s2.id AND kat = '".$_GET['kat']."' ORDER BY start DESC");	
	$filter = _kalender_admin_head.'<i> - '._ev_filter.': '._ev_l_kat.'</i>';
			   }
        if(_rows($qry))
        {
          while($get = _fetch($qry)) 
		  {

          $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst"; $color++;

			// BILD 
			$pfadbild = "inc/images/eventkat/".$get['kat'];
			if(file_exists(basePath."/".$pfadbild.".gif"))     $bild = '<img src=../'.$pfadbild.'.gif class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".jpg")) $bild = '<img src=../'.$pfadbild.'.jpg class=icon  alt= />';		
			elseif(file_exists(basePath."/".$pfadbild.".png")) $bild = '<img src=../'.$pfadbild.'.png class=icon  alt= />';	
			else $bild = "<img src=../inc/images/event.gif class=icon alt= />";  

			
			
          $show_ .= show($dir."/event_show_list", array("datum" => date("d.m.y H:i", $get['start'])._uhr,
		  											  "datum2" => date("d.m.y H:i", $get['ende'])._uhr,
                                                      "event" => re($get['name']),
                                                      "id" => $get['id'],
													  "kat_id" => $get['kat'],
													  "kat" => $bild.' '.$get['katname'],
                                                      "class" => $class,
                                                      "edit" => $edit,
                                                      "delete" => $delete));
        }

        $index = show($dir."/event_list", array("head" => $filter,
                                             "date" => _ev_start_datum,
											 "date2" => _ev_ende_datum,
											 "kat" => _ev_l_kat,
                                             "titel" => _kalender_event,
											 "foot" => _ev_foot,
                                             "show" => $show_
                                             ));
		}
break;
//#####################################################################################################################;
case 'admin';
  if($_GET['do'] == 'edit') header("Location: ../admin/?admin=event&do=edit&id=".$_GET['id']);
break;
//#####################################################################################################################;
case 'show';
if(!is_numeric($_GET['id'])) {
	exit;
	} else {
  $get = db("SELECT * FROM ".$sql_prefix."events_info
             WHERE id = '".$_GET['id']."'",false,true);
  
	if($get['show'] == '1') {
	
	$title = $pagetitle." - ".$where.": ".re($get['name'])."";
   
	if(permission("editkalender"))
	{
		$editev = show("page/button_edit_single", array("id" => $get['id'],
														"action" => "action=admin&amp;do=edit",
														"title" => _button_title_edit));
		$editev = '<tr><td class="contentBottom" colspan="3">'.$editev.'</td></tr>';												
	} else {
		$editev = "";
	}
  
	$katsql = db("SELECT * FROM ".$sql_prefix."events_kat WHERE id LIKE ".$get['kat']);
	$kat = _fetch($katsql); 
	if($kat['name'] =='') { $kat['name'] = 'error (deleted)'; }
	
	$kommentare = db("SELECT id AS num FROM ".$sql_prefix."events_comments WHERE eid = '".$get['id']."'");
	$kommentare = _rows($kommentare);
// # # # # # # #	
	if($_GET['w'] == 't') {
if($chkMe != "unlogged")
  {			
		if(isset($_POST['status'])) {
			$vorhanden = db("SELECT uid FROM ".$sql_prefix."events_user WHERE eid = '".$get['id']."' AND uid = '".$userid."'");
			if(!_rows($vorhanden)) {	
				db("INSERT INTO ".$sql_prefix."events_user (`eid`, `uid`, `status`, `time`) VALUES ('".$get['id']."', '".$userid."', '".$_POST['status']."', '".time()."')");
			} else {
				db("UPDATE `".$sql_prefix."events_user` SET `status`='".$_POST['status']."', `time` = '".time()."' WHERE `eid`='".$get['id']."' AND `uid`='".$userid."' LIMIT 1");
			}
		}}
	
		$userqry = db("SELECT * FROM ".$sql_prefix."events_user WHERE eid LIKE ".$get['id']);
		while($user = _fetch($userqry))
  		{
			if($user['status'] == 0) { //zusage
			$zusagen .= show(_ev_user_status, array("user" => autor($user['uid']),
												    "datum" => date("d.m.Y H:i", $user['time'])._uhr));
			} elseif ($user['status'] == 1) { //absage
			$absagen .= show(_ev_user_status, array("user" => autor($user['uid']),
												    "datum" => date("d.m.Y H:i", $user['time'])._uhr));
			} elseif ($user['status'] == 2) { //vllt
			$unsicher .= show(_ev_user_status, array("user" => autor($user['uid']),
												    "datum" => date("d.m.Y H:i", $user['time'])._uhr));
			}
				if($user['uid'] == $userid)
				{
				  if($user['status'] == "0") $sely = "checked=\"checked\"";
				  elseif($user['status'] == "1") $seln = "checked=\"checked\"";
				  elseif($user['status'] == "2") $selm = "checked=\"checked\"";
				} 

		}	
		if($chkMe != "unlogged")
  {	
		$formular .= show($dir."/players", array("id" => $get['id'],
												 "sely" => (empty($sely) && empty($seln) && empty($selm) ? 'checked="checked"' : $sely),
                                             	 "seln" => $seln,
                                            	 "selm" => $selm,
												 "yes" => _ev_zusagen,
												 "no" => _ev_absagen,
												 "maybe" => _ev_unsicher,
												 "value" => _ev_absenden));
  } else {
	  $formular = '';
  }
		$inhalt .= show($dir."/teilnehmer", array("zusagen_h" => _ev_zusagen,
												  "zusagen" => $zusagen,
												  "absagen" => $absagen,
												  "unsicher" => $unsicher,
												  "absagen_h" => _ev_absagen,
												  "unsicher_h" => _ev_unsicher,
												  "formular" => $formular));
// # # # # # # #
//############################################################################'	
//############################################################################'		
	} elseif ($_GET['w'] == 'k') {
		if(isset($_GET['page']))  $page = $_GET['page'];
				else $page = 1;
			
			   $max_comments = 100000;
			
				$qryc = db("SELECT * FROM ".$sql_prefix."events_comments 
				WHERE eid = '".$get['id']."' 
				ORDER BY 'datum' DESC LIMIT ".($page - 1)*$max_comments.",".$max_comments."
				");
		

	
				$entrys = $kommentare;
				$i = $entrys-($page - 1)*$max_comments;
		
	  while($getc = _fetch($qryc))
	  {
      if($getc['hp']) $hp = show(_hpicon, array("hp" => $getc['hp']));
      else $hp = "";

      if(($chkMe != 'unlogged' && $getc['reg'] == $userid) || permission("editkalender"))
      {
        $edit = show("page/button_edit_single", array("id" => $get['id'],
                                                      "action" => "action=show&amp;w=k&amp;do=edit&amp;cid=".$getc['id'],
                                                      "title" => _button_title_edit));  
        $delete = show("page/button_delete_single", array("id" => $_GET['id'],
                                                         "action" => "action=show&amp;w=k&amp;do=delete&amp;cid=".$getc['id'],
                                                         "title" => _button_title_del,
                                                         "del" => convSpace(_confirm_del_entry)));     
      } else {
        $edit = "";
        $delete = "";
      }

		  if($getc['reg'] == "0")
		  {
        if($getc['hp']) $hp = show(_hpicon_forum, array("hp" => $getc['hp']));
        else $hp = "";
        if($getc['email']) $email = '<br />'.show(_emailicon_forum, array("email" => eMailAddr($getc['email'])));
        else $email = "";
        $onoff = "";
        $avatar = "";
        $nick = show(_link_mailto, array("nick" =>re($getc['nick']),
                                         "email" => eMailAddr($getc['email'])));
		  } else {
        $email = "";
        $hp = "";
        $onoff = onlinecheck($getc['reg']);
        $nick = autor($getc['reg']);
		  }

      $titel = show(_eintrag_titel, array("postid" => $i,
												 				     			"datum" => date("d.m.Y", $getc['datum']),
													 		 			    	"zeit" => date("H:i", $getc['datum'])._uhr,
                                          "edit" => $edit,
                                          "delete" => $delete));

      if($chkMe == "4") $posted_ip = $getc['ip'];
      else $posted_ip = _logged;

		  $comments .= show("page/comments_show", array("titel" => $titel,
			  																			      "comment" => bbcode($getc['comment']),
                                                    "editby" => bbcode($getc['editby']),
                                                    "nick" => $nick,
                                                    "email" => $email,
                                                    "hp" => $hp,
                                                    "avatar" => useravatar($getc['reg']),
                                                    "onoff" => $onoff,
                                                    "rank" => getrank($getc['reg']),
                                                    "ip" => $posted_ip));
		  $i--;
	  }

    if(settings("reg_artikel") == "1" && $chkMe == "unlogged")
    {
      $add = _error_unregistered_nc;
    } else {
      if(isset($userid))
	    {
		    $form = show("page/editor_regged", array("nick" => autor($userid),
                                                 "von" => _autor));
	    } else {
        $form = show("page/editor_notregged", array("nickhead" => _nick,
                                                    "emailhead" => _email,
                                                    "hphead" => _hp));
      }

      //if(!ipcheck("evid(".$_GET['id'].")", $flood_newscom))
	  if(1==1)
      {
	      $add = show("page/comments_add", array("titel" => _ev_comments_write_head,
					      															 "bbcodehead" => _bbcode,
                                               "form" => $form,
                                               "show" => "none",
                                               "b1" => $u_b1,
                                               "b2" => $u_b2,
                                               "what" => _button_value_add,
                                               "ip" => _iplog_info,"sec" => $dir,
                                               "security" => _register_confirm,
                                               "preview" => _preview,
                                               "action" => '?action=show&amp;do=add&amp;w=k&amp;id='.$_GET['id'],
                                               "prevurl" => '../artikel/?action=compreview&id='.$_GET['id'],
                                               "lang" => $language,
										  					    					 "id" => $_GET['id'],
											  						    			 "postemail" => "",
												  							    	 "posthp" => "",
													  								   "postnick" => "",
			    									  								 "posteintrag" => "",
					    								  							 "error" => "",
											    					  				 "eintraghead" => _eintrag));
      } else {
        $add = "";
      }
    }
   $seiten = nav($entrys,$maxcomments,"?action=show&amp;w=k&amp;id=".$_GET['id']."");

//
 //   $showmore = show($dir."/comments",array("head" => _comments_head,
//	 	  									  "show" => $comments,
//                                            "seiten" => $seiten,
//                                            "icq" => "",
//                                            "add" => $add));
											
	$inhalt = show($dir."/comments",array("head" => _comments_head,
	 	  									"show" => $comments,
                                            "seiten" => $seiten,
                                            "add" => $add));

//    $inhalt = show($dir."/show_more", array("titel" => re($getc['titel']),
//                                           "id" => $getc['id'],
//                                           "comments" => "",
//                                           "display" => "inline",
//                                           "nautor" => _autor,
//                                           "kat" => re($getkat['katimg']),
//                                           "dir" => $designpath,
//                                           "ndatum" => _datum,
//                                           "showmore" => $showmore,
//                                           "icq" => "",
//                                           "text" => bbcode($getc['text']),
//                                           "datum" => date("j.m.y H:i", $getc['datum'])._uhr,
//                                           "links" => $links,
//                                           "autor" => autor($getc['autor'])));
  
  if($_GET['do'] == "add")
  {
	 
	  
	 $flood_evcom = 20;
    //if(!ipcheck("evid(".$_GET['id'].")", $flood_evcom))
	if(1 == 1) //der IP Check geht nicht deshalb hier der vorläufige Ersatz!!!
    {
		 

      if(isset($userid))
        $toCheck = empty($_POST['comment']);
      else
        $toCheck = empty($_POST['nick']) || empty($_POST['email']) || empty($_POST['comment']) || !check_email($_POST['email']) || $_POST['secure'] != $_SESSION['sec_'.$dir] || empty($_SESSION['sec_'.$dir]);



      if($toCheck)
		  {
        if(isset($userid))
        {
          if(empty($_POST['eintrag'])) $error = _empty_eintrag;
          $form = show("page/editor_regged", array("nick" => autor($userid),
                                                   "von" => _autor));
        } else {
          if(($_POST['secure'] != $_SESSION['sec_'.$dir])  || empty($_SESSION['sec_'.$dir])) $error = _error_invalid_regcode; 
          elseif(empty($_POST['nick'])) $error = _empty_nick;
		      elseif(empty($_POST['email'])) $error = _empty_email;
		      elseif(!check_email($_POST['email'])) $error = _error_invalid_email;
		      elseif(empty($_POST['eintrag'])) $error = _empty_eintrag;
          
          $form = show("page/editor_notregged", array("nickhead" => _nick,
                                                      "emailhead" => _email,
                                                      "hphead" => _hp));
        }



    		$error = show("errors/errortable", array("error" => $error));
		    $index = show("page/comments_add", array("titel" => _ev_comments_write_head,
				    																		 "nickhead" => _nick,
						    																 "bbcodehead" => _bbcode,
                                                 "sec" => $dir,
                                                 "security" => _register_confirm,
								    														 "emailhead" => _email,
                                                 "b1" => $u_b1,
                                                 "b2" => $u_b2,
                                                 "form" => $form,
										    												 "hphead" => _hp,
                                                 "preview" => _preview,
                                                 "action" => '?action=show&amp;do=add&amp;id='.$_GET['id'],
                                                 "prevurl" => '../artikel/?action=compreview&id='.$_GET['id'],
												    										 "id" => $_GET['id'],
                                                 "what" => _button_value_add,
														    								 "postemail" => $_POST['email'],
                                                 "ip" => _iplog_info,
																		    				 "posthp" => links($_POST['hp']),
																				    		 "postnick" => re($_POST['nick']),
                                                 "show" => "",
    																						 "posteintrag" => re_bbcode($_POST['comment']),
		    																				 "error" => $error,
								    														 "eintraghead" => _eintrag));
	    } else {
    	  $qry = db("INSERT INTO ".$sql_prefix."events_comments 
                   SET `eid`  = '".((int)$_GET['id'])."',
                       `datum`    = '".((int)time())."',
                       `nick`     = '".up($_POST['nick'])."',
                       `email`    = '".$_POST['email']."',
                       `hp`       = '".links($_POST['hp'])."',
                       `reg`      = '".((int)$userid)."',
                       `comment`  = '".up($_POST['comment'],1)."',
                       `ip`       = '".$userip."'");

        $ncid = "evid(".$_GET['id'].")";
        $qry = db("INSERT INTO ".$db['ipcheck']."
                   SET ip   = '".$userip."',
                       what = '".$ncid."',
                       time = '".((int)time())."'");

	      $index = info(_comment_added, "?action=show&amp;w=k&amp;id=".$_GET['id']."");
		}
   } else {
      $index = error(show(_error_flood_post, array("sek" => $flood_evcom)), 1);
   }
  } elseif($_GET['do'] == "delete") {
    $dqry = db("SELECT * FROM ".$sql_prefix."events_comments
               WHERE id = '".intval($_GET['cid'])."'");
    $getd = _fetch($dqry);
    
    if($getd['reg'] == $userid || permission('editkalendar'))
    {
      $qry = db("DELETE FROM ".$sql_prefix."events_comments
                 WHERE id = '".intval($_GET['cid'])."'");

      $index = info(_comment_deleted, "?action=show&amp;w=k&amp;id=".$_GET['id']."");
    } else {
      $index = error(_error_wrong_permissions, 1);
    }
  } elseif($_GET['do'] == "editcom") {
    $qry = db("SELECT * FROM ".$sql_prefix."events_comments
               WHERE id = '".intval($_GET['cid'])."'");
    $get = _fetch($qry);
    
    if($getc['reg'] == $userid || permission('editkalendar'))
    {
        $editedby = show(_edited_by, array("autor" => autor($userid),
                                           "time" => date("d.m.Y H:i", time())._uhr));
        $qry = db("UPDATE ".$sql_prefix."events_comments
                   SET `nick`     = '".up($_POST['nick'])."',
                       `email`    = '".up($_POST['email'])."',
                       `hp`       = '".links($_POST['hp'])."',
                       `comment`  = '".up($_POST['comment'],1)."',
                       `editby`   = '".addslashes($editedby)."'
                   WHERE id = '".intval($_GET['cid'])."'");
                   
        $index = info(_comment_edited, "?action=show&amp;w=k&amp;id=".$_GET['id']."");
      } else {
        $index = error(_error_edit_post,1);
      }
    } elseif($_GET['do'] == "edit") {
      $qryc = db("SELECT * FROM ".$sql_prefix."events_comments
                 WHERE id = '".intval($_GET['cid'])."'");
      $getc = _fetch($qryc);
      
      if($getc['reg'] == $userid || permission('editkalendar'))
      {
        if($getc['reg'] != 0)
  	    {
  		    $form = show("page/editor_regged", array("nick" => autor($getc['reg']),
                                                   "von" => _autor));
  	    } else {
          $form = show("page/editor_notregged", array("nickhead" => _nick,
                                                      "emailhead" => _email,
                                                      "hphead" => _hp,
                                                      "postemail" => $getc['email'],
        													  							    "posthp" => links($getc['hp']),
        														  								"postnick" => re($getc['nick']),
                                                      ));
        }
        
  		  $index = show("page/comments_add", array("titel" => _comments_edit,
  			    																		 "nickhead" => _nick,
  						      														 "bbcodehead" => _bbcode,
  							  	    												 "emailhead" => _email,
                                                 "sec" => $dir,
                                                 "security" => _register_confirm,
  								  		    										 "hphead" => _hp,
                                                 "b1" => $u_b1,
                                                 "b2" => $u_b2,
                                                 "form" => $form,
                                                 "preview" => _preview,
												"prevurl" => '../artikel/?action=compreview&id='.$_GET['id'],
												//"prevurl" => "",
                                                 "action" => '?action=show&amp;w=k&amp;do=editcom&amp;id='.$_GET['id'].'&amp;cid='.$_GET['cid'],
                                                 "ip" => _iplog_info,
                                                 "lang" => $language,
  										  				    						 "id" => $_GET['id'],
                                                 "what" => _button_value_edit,
                                                 "show" => "",
  										    			  							 "posteintrag" => re_bbcode($getc['comment']),
  												    		  						 "error" => "",
  																		      		 "eintraghead" => _eintrag));
      } else {
        $index = error(_error_edit_post,1);
     }}
     //ende
		
//$inhalt = "Kommentar-Baustelle".$entrys."";		
//#############################################################################
//############################################################################'	
// # # # # # # #		
	} else 	{
		$inhalt = bbcode($get['beschreibung']);
	}
// # # # # # # #
	$teilnehmer = db("SELECT uid FROM ".$sql_prefix."events_user WHERE eid = '".$get['id']."'");
	$teilnehmer = _rows($teilnehmer);
	
	

	if($get['veranstalter'] != '') {
		$veranstalter = $get['veranstalter']." (eingestellt von ".autor($get['autor_id']).")";
		}else{
			$veranstalter = autor($get['autor_id']);
			}
		
	if($get['gmaps'] == '1') {
			$ort = "<a target='_blank' href='http://maps.google.de/maps?q=".$get['ort']."'>".$get['ort']."</a>";
		} else {
			$ort = $get['ort'];
			}	
// BILD 
			$pfadbild = "event/img/".$_GET['id'];
			if(file_exists(basePath."/".$pfadbild.".gif"))     $bild = "<img src=\"../".$pfadbild.".gif\" style=\"max-width:350px;max-height:400px\" alt=\"\" />";		
			elseif(file_exists(basePath."/".$pfadbild.".jpg")) $bild = "<img src=\"../".$pfadbild.".jpg\" style=\"max-width:350px;max-height:400px\" alt=\"\" />";		
			elseif(file_exists(basePath."/".$pfadbild.".png")) $bild = "<img src=\"../".$pfadbild.".png\" style=\"max-width:350px;max-height:400px\" alt=\"\" />";	
			else $bild = "";

			// KATBILD 
			$katpfadbild = "inc/images/eventkat/".$get['kat'];
			if(file_exists(basePath."/".$katpfadbild.".gif"))     $katbild = '<img src=../'.$katpfadbild.'.gif class=icon  alt= />';		
			elseif(file_exists(basePath."/".$katpfadbild.".jpg")) $katbild = '<img src=../'.$katpfadbild.'.jpg class=icon  alt= />';		
			elseif(file_exists(basePath."/".$katpfadbild.".png")) $katbild = '<img src=../'.$katpfadbild.'.png class=icon  alt= />';	
			else $katbild = "<img src=../inc/images/event.gif class=icon alt= />"; 

	$details .= show($dir."/details", array("ort" => $ort,
											  "kat" => $katbild.' '.$kat['name'],
											  "kat_id" => $get['kat'],
											  "name" => $get['name'],
											  "autor" => $veranstalter,
                                              "start" => date("d.m.Y H:i", $get['start'])._uhr,
											  "ende" => date("d.m.Y H:i", $get['ende'])._uhr,
											  "l_kat" => _ev_l_kat,
											  "l_name" => _ev_l_name,
											  "l_autor" => _ev_l_autor,
											  "l_start" => _ev_l_start,
											  "l_ende" => _ev_l_ende,
											  "l_ort" => _ev_l_ort,
											  "edit" => $editev));
											  
	$oberer_teil .= show($dir."/oberer_teil", array( "details" => $details));
	
	if($_GET['w'] == 'b') {
		$b='<i><u>';
		$bb='</u></i>';
	} elseif ($_GET['w'] == 't') {
		$t='<i><u>';
		$tt='</u></i>';
	} elseif ($_GET['w'] == 'k') {
		$k='<i><u>';
		$kk='</u></i>';
	} else {
		$b='<i><u>';
		$bb='</u></i>';
	}
											  
    $events .= show($dir."/event_show", array("link_beschreibung" => "<a href='?action=show&w=b&id=".$_GET['id']."' target='_self'>".$b._ev_link_beschreibung.$bb."</a>",
											  "link_teilnehmer" => "<a href='?action=show&w=t&id=".$_GET['id']."' target='_self'>".$t._ev_link_teilnehmer." (".$teilnehmer.$tt.")</a>",
											  "link_kommentare" => "<a href='?action=show&w=k&id=".$_GET['id']."' target='_self'>".$k._ev_link_kommentare." (".$kommentare.$kk.")</a>",
											  "inhalt" => $inhalt,
											  "oberer_teil" => $oberer_teil,
											  "bild" => $bild));
  
  $get['aufrufe'] = $get['aufrufe'] + 1;											  
  db("UPDATE ".$sql_prefix."events_info SET `aufrufe`=".$get['aufrufe']." WHERE `id`=".$get['id']." LIMIT 1");											  
  $aufrufe = '('._ev_aufrufe.': '.$get['aufrufe'].')';	
} else {
  $index = error2(_ev_dont_show,1);
  
  }

  //$head = show(_kalender_events_head, array("datum" => date("d.m.Y",$_GET['time'])));
  if($index == "") {
  $index = show($dir."/event", array("head" => _events,
  									 "aufrufe" => $aufrufe,
                                     "events" => $events,
									 "foot" => _ev_foot));
  }}
break;
endswitch;
## SETTINGS ##
$time_end = generatetime();
$time = round($time_end - $time_start,4);
page($index, $title, $where,$time);
## OUTPUT BUFFER END ##
gz_output();
?>