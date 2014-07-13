<?php
if(_adminMenu != 'true') exit;

  $where = $where.': '._kalender_head;
  if(!permission("editkalender"))
  {
    $show = error(_error_wrong_permissions, 1);
  } else {
    if($_GET['do'] == "add")
    {
      $dropdown_date_start = show(_dropdown_date_ts, array("nr" => '1',
	  														"day" => dropdown("day",date("d",time())),
				      	                                  "month" => dropdown("month",date("m",time())),
                                      	                  "year" => dropdown("year",date("Y",time()))));
      $dropdown_time_start = show(_dropdown_time_ts, array("nr" => '1',
	  													   "hour" => dropdown("hour",date("H",time())),
														  "minute" => dropdown("minute",date("i",time())),
														  "uhr" => _uhr));
												  
      $dropdown_date_ende = show(_dropdown_date_ts, array("nr" => '2',
	  													  "day" => dropdown("day",date("d",time())),
				      	                                  "month" => dropdown("month",date("m",time())),
                                      	          			"year" => dropdown("year",date("Y",time()))));
      $dropdown_time_ende = show(_dropdown_time_ts, array("nr" => '2',
	  													  "hour" => dropdown("hour",date("H",time())),
														  "minute" => dropdown("minute",date("i",time())),
														  "uhr" => _uhr));
	
	  $qryk = db("SELECT * FROM ".$sql_prefix."events_kat");
        while($getk = _fetch($qryk))
        {
		  $kat .= show(_select_field, array("value" => $getk['id'],
                                            "sel" => "",
                                            "what" => re($getk['name'])));
        }
														  
      $show = show($dir."/form_event", array("datum_start" =>_ev_l_start,
	  											"datum_ende" => _ev_l_ende,
                                                "event" => _kalender_event,
                                                "dropdown_time_start" => $dropdown_time_start,
                                                "dropdown_date_start" => $dropdown_date_start,
                                                "dropdown_time_ende" => $dropdown_time_ende,
                                                "dropdown_date_ende" => $dropdown_date_ende,												
                                                "beschreibung" => _beschreibung,
                                                "what" => _button_value_edit,
                                                "do" => "addevent",
                                                "k_event" => re($get['name']),
                                                "k_beschreibung" => re_bbcode($get['beschreibung']),
												"veranstalter" => _ev_veranstalter,
												"k_veranstalter" => re($get['veranstalter']),
												"ort" => _ev_l_ort,
												"k_ort" => re($get['ort']),
												"kat" => _ev_l_kat,
												"k_kat" => $kat,
												"gmaps" => _ev_gmaps,
												"gmaps_info" => _ev_gmaps_info,
												"checked" => $checked,
												"autor" => _autor,
												"bild" => _ev_bild,
												"k_autor" => autor($userid),
                                                "head" => _kalender_admin_head));
    } elseif($_GET['do'] == "addevent") {
	
	$start_time = mktime($_POST['h_1'],$_POST['min_1'],0,$_POST['m_1'],$_POST['t_1'],$_POST['j_1']);
	$ende_time = mktime($_POST['h_2'],$_POST['min_2'],0,$_POST['m_2'],$_POST['t_2'],$_POST['j_2']);
    
	if(empty($_POST['event']))
      {
		if(empty($_POST['event']))     $show = error(_kalender_error_no_title,1);
      } elseif($start_time >= $ende_time) { $show = error(_kalender_error_start_ende,1); 
	  } else {
        

		
		
        $insert = db("INSERT INTO ".$sql_prefix."events_info
                      SET `start` = '".((int)$start_time)."',
					  	  `ende` = '".((int)$ende_time)."',
						  `kat` = '".((int)$_POST['kat'])."',
						  `autor_id` = '".((int)$userid)."',
						  `gmaps` = '".((int)$_POST['gmaps'])."',
                          `name` = '".up($_POST['event'])."',
						  `ort` = '".up($_POST['ort'])."',
						  `veranstalter` = '".up($_POST['veranstalter'])."',
                          `beschreibung` = '".up($_POST['beschreibung'],1)."'");
		  
		  $tmp1 = $_FILES['bild']['tmp_name'];
          $type1 = $_FILES['bild']['type'];
          $end1 = explode(".", $_FILES['bild']['name']);
          $end1 = strtolower($end1[count($end1)-1]);
          if(!empty($tmp1))
          {
              @copy($tmp1, basePath."/event/img/".mysql_insert_id().".".strtolower($end1));
              @unlink($tmp1);
          }		

        $show = info(_kalender_successful_added,"?admin=event");
      }
    } elseif($_GET['do'] == "edit") {
      $qry = db("SELECT * FROM ".$sql_prefix."events_info
                 WHERE id = '".intval($_GET['id'])."'");
      $get = _fetch($qry);

      $dropdown_date_start = show(_dropdown_date_ts, array("nr" => '1',
	  														"day" => dropdown("day",date("d",$get['start'])),
				      	                                  "month" => dropdown("month",date("m",$get['start'])),
                                      	                  "year" => dropdown("year",date("Y",$get['start']))));
      $dropdown_time_start = show(_dropdown_time_ts, array("nr" => '1',
	  													   "hour" => dropdown("hour",date("H",$get['start'])),
														  "minute" => dropdown("minute",date("i",$get['start'])),
														  "uhr" => _uhr));
												  
      $dropdown_date_ende = show(_dropdown_date_ts, array("nr" => '2',
	  													  "day" => dropdown("day",date("d",$get['ende'])),
				      	                                  "month" => dropdown("month",date("m",$get['ende'])),
                                      	          			"year" => dropdown("year",date("Y",$get['ende']))));
      $dropdown_time_ende = show(_dropdown_time_ts, array("nr" => '2',
	  													  "hour" => dropdown("hour",date("H",$get['ende'])),
														  "minute" => dropdown("minute",date("i",$get['ende'])),
														  "uhr" => _uhr));
	
	  $qryk = db("SELECT * FROM ".$sql_prefix."events_kat");
        while($getk = _fetch($qryk))
        {
		  if($get['kat'] == $getk['id']) $sel = "selected=\"selected\"";
          else $sel = "";
          $kat .= show(_select_field, array("value" => $getk['id'],
                                            "sel" => $sel,
                                            "what" => re($getk['name'])));
        }
	   if($get['gmaps'] == '1') $checked = "checked=\"checked\"";
	   else $checked = "";
												  
      $show = show($dir."/form_event", array("datum_start" =>_ev_l_start,
	  											"datum_ende" => _ev_l_ende,
                                                "event" => _kalender_event,
                                                "dropdown_time_start" => $dropdown_time_start,
                                                "dropdown_date_start" => $dropdown_date_start,
                                                "dropdown_time_ende" => $dropdown_time_ende,
                                                "dropdown_date_ende" => $dropdown_date_ende,												
                                                "beschreibung" => _beschreibung,
                                                "what" => _button_value_edit,
                                                "do" => "editevent&amp;id=".$_GET['id'],
                                                "k_event" => re($get['name']),
                                                "k_beschreibung" => re_bbcode($get['beschreibung']),
												"veranstalter" => _ev_veranstalter,
												"k_veranstalter" => re($get['veranstalter']),
												"ort" => _ev_l_ort,
												"k_ort" => re($get['ort']),
												"kat" => _ev_l_kat,
												"k_kat" => $kat,
												"gmaps" => _ev_gmaps,
												"gmaps_info" => _ev_gmaps_info,
												"checked" => $checked,
												"autor" => _autor,
												"bild" => _ev_bild,
												"k_autor" => autor($get['autor_id']),
                                                "head" => _kalender_admin_head_edit));
    } elseif($_GET['do'] == "editevent") {
	        $start_time = mktime($_POST['h_1'],$_POST['min_1'],0,$_POST['m_1'],$_POST['t_1'],$_POST['j_1']);
		$ende_time = mktime($_POST['h_2'],$_POST['min_2'],0,$_POST['m_2'],$_POST['t_2'],$_POST['j_2']);
      if(empty($_POST['event']))
      {
        if(empty($_POST['event']))     $show = error(_kalender_error_no_title,1);
      } elseif($start_time >= $ende_time) { $show = error(_kalender_error_start_ende,1); }
		else {


		if($start_time != $ende_time) { $show = error(_kalender_error_start_ende,1); }
        $update = db("UPDATE ".$sql_prefix."events_info
                      SET `start` = '".((int)$start_time)."',
					  	  `ende` = '".((int)$ende_time)."',
						  `kat` = '".((int)$_POST['kat'])."',
						  `gmaps` = '".((int)$_POST['gmaps'])."',
                          `name` = '".up($_POST['event'])."',
						  `ort` = '".up($_POST['ort'])."',
						  `veranstalter` = '".up($_POST['veranstalter'])."',
                          `beschreibung` = '".up($_POST['beschreibung'],1)."'
                      WHERE id = '".intval($_GET['id'])."'");
					  
		  $tmp1 = $_FILES['bild']['tmp_name'];
          $type1 = $_FILES['bild']['type'];
          $end1 = explode(".", $_FILES['bild']['name']);
          $end1 = strtolower($end1[count($end1)-1]);
          if(!empty($tmp1))
          {
            $img1 = @getimagesize($tmp1);
						foreach($picformat AS $endun1)
            {
              if(file_exists(basePath.'/event/img/'.intval($_GET['id']).'.'.$endun1))
              {
                @unlink(basePath.'/event/img/'.intval($_GET['id']).'.'.$endun1);
                break;
              }
            }
						
            if($img1[0])
            {
              copy($tmp1, basePath."/event/img/".intval($_GET['id']).".".strtolower($end1));
              @unlink($tmp1);
            }
          }			  

        $show = info(_kalender_successful_edited,"?admin=event");
      }
	} elseif($_GET['do'] == 'public') {
        if($_GET['what'] == 'set')
        {
          $upd = db("UPDATE ".$sql_prefix."events_info
                     SET `show` = '1'
                     WHERE id = '".intval($_GET['id'])."'");
        } elseif($_GET['what'] == 'unset') {
          $upd = db("UPDATE ".$sql_prefix."events_info
                     SET `show` = '0'
                     WHERE id = '".intval($_GET['id'])."'");
        }
        header("Location: ?admin=event");
    } elseif($_GET['do'] == "delete") {
      $del = db("DELETE FROM ".$sql_prefix."events_info
                 WHERE id = '".intval($_GET['id'])."'");
	  $del = db("DELETE FROM ".$sql_prefix."events_comments
                 WHERE eid = '".intval($_GET['id'])."'");
	  $del = db("DELETE FROM ".$sql_prefix."events_user
                 WHERE eid = '".intval($_GET['id'])."'");
				 
	  		foreach($picformat AS $endun1)
            {
              if(file_exists(basePath.'/event/img/'.intval($_GET['id']).'.'.$endun1))
              {
                @unlink(basePath.'/event/img/'.intval($_GET['id']).'.'.$endun1);
                break;
              }
            }

      $show = info(_kalender_deleted,"?admin=event");
    } else {
      $qry = db("SELECT * FROM ".$sql_prefix."events_info
                 ORDER BY start DESC");
        while($get = _fetch($qry))
        {
			$edit = show("page/button_edit_single", array("id" => $get['id'],
														"action" => "admin=event&amp;do=edit",
                                                        "title" => _button_title_edit));
			$delete = show("page/button_delete_single", array("id" => $get['id'],
                                                            "action" => "admin=event&amp;do=delete",
                                                            "title" => _button_title_del,
                                                            "del" => convSpace(_confirm_del_kalender)));
			$show = ($get['show'] == 1)
               ? '<a href="?admin=event&amp;do=public&amp;id='.$get['id'].'&amp;what=unset"><img src="../inc/images/public.gif" alt="" title="'._non_public.'" /></a>'
               : '<a href="?admin=event&amp;do=public&amp;id='.$get['id'].'&amp;what=set"><img src="../inc/images/nonpublic.gif" alt="" title="'._public.'" /></a>';													

          $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst"; $color++;

          $show_ .= show($dir."/event_show", array("datum" => date("d.m.y H:i", $get['start'])._uhr,
                                                      "event" => re($get['name']),
                                                      "id" => $get['id'],
                                                      "class" => $class,
													  "show" => $show,
                                                      "edit" => $edit,
                                                      "delete" => $delete));
        }

        $show = show($dir."/event", array("head" => _kalender_admin_head.' <a href="http://www.modsbar.de/1269/blueteck/">v1.0</a>',
                                             "date" => _ev_start_datum,
                                             "titel" => _kalender_event,
                                             "show" => $show_,
                                             "add" => _kalender_admin_head_add
                                             ));
    }
  }
?>