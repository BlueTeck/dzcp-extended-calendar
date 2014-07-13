<?php
if(_adminMenu != 'true') exit;

    $where = $where.': '._config_ev_kats_edit_head;
    if(!permission("ev_kat"))
    {
      $show = error(_error_wrong_permissions, 1);
    } else {
      $qry = db("SELECT * FROM ".$sql_prefix."events_kat 
                 ORDER BY `name`");
      while($get = _fetch($qry))
      {
        $edit = show("page/button_edit_single", array("id" => $get['id'],
                                                      "action" => "admin=ev_kat&amp;do=edit",
                                                      "title" => _button_title_edit));
        $delete = show("page/button_delete_single", array("id" => $get['id'],
                                                          "action" => "admin=ev_kat&amp;do=delete",
                                                          "title" => _button_title_del,
                                                          "del" => convSpace(_confirm_del_kat)));
        $img = show(_config_ev_kats_img, array("img" => re($get['katimg'])));

        $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst"; $color++;
        $kats .= show($dir."/eventkats_show", array("mainkat" => re($get['name']),
                                                   "class" => $class,
                                                   "img" => $img,
                                                   "delete" => $delete,
                                                   "edit" => $edit));
      }

      $show = show($dir."/eventkats", array("head" => _config_ev_kats_head,
                                           "kats" => $kats,
                                           "add" => _config_ev_kats_add_head,
                                           "img" => _config_ev_kats_katbild,
                                           "delete" => _deleteicon_blank,
                                           "edit" => _editicon_blank,
                                           "mainkat" => _config_ev_kats_kat));
//#############################################################################											   
      if($_GET['do'] == "delete")
      {
        $qry = db("SELECT COUNT(id) AS num FROM ".$sql_prefix."events_info
                   WHERE kat = '".intval($_GET['id'])."'");
		$get = _fetch($qry);
	
if($get['num'] == 0) {
		
	    @unlink(basePath."/inc/images/eventkat/".$get['id'].'.gif');
		@unlink(basePath."/inc/images/eventkat/".$get['id'].'.png');
		@unlink(basePath."/inc/images/eventkat/".$get['id'].'.jpg');

        $del = db("DELETE FROM ".$sql_prefix."events_kat
                  WHERE id = '".intval($_GET['id'])."'");

          $show = info(_config_ev_kat_deleted, "?admin=ev_kat");
		  } else {
		  $show = info(_config_ev_kat_deleted_error, "../event/?action=list&kat=".$_GET['id']."");
		  }
//#############################################################################			
      } elseif($_GET['do'] == "add") {
        $files = get_files('../inc/images/eventkat/');
        for($i=0; $i<count($files); $i++)
        {
          $img .= show(_select_field, array("value" => $files[$i],
                                            "sel" => "",
                                            "what" => $files[$i]));
        }

        $show = show($dir."/eventkatform", array("head" => _config_ev_kats_add_head,
                                                "nkat" => _config_katname,
                                                "kat" => "",
                                                "value" => _button_value_add,
                                                "nothing" => "",
                                                "do" => "addeventkat",
                                                "nimg" => _config_ev_kats_katbild,
                                                "upload" => "",
                                                "img" => $img));

//#############################################################################													
      } elseif($_GET['do'] == "addeventkat") {
        if(empty($_POST['kat']))
        {
          $show = error(_config_empty_katname,1);
        } else {
			
		  
	  $tmpname = $_FILES['file']['tmp_name'];
      $name = $_FILES['file']['name'];
      $type = $_FILES['file']['type'];
      $size = $_FILES['file']['size'];
      $imageinfo = @getimagesize($tmpname);

	if(!$tmpname)
      {
          $qry = db("INSERT INTO ".$sql_prefix."events_kat
                     SET `katimg`     = '".up($_POST['img'])."',
                         `name`  = '".up($_POST['kat'])."'");
		//bild kopieren						 
			$getrennt = explode ('.',$_POST['img']);
			$getrennt = array_reverse ($getrennt);
			$neuername = mysql_insert_id().'.'.$getrennt[0];
			copy(basePath."/inc/images/eventkat/".$_POST['img']."", basePath."/inc/images/eventkat/".$neuername."");

          $show = info(_config_newskats_added, "?admin=ev_kat");
	  } else {  
					$qry = db("INSERT INTO ".$sql_prefix."events_kat
                     SET `katimg`     = '".$name."',
                         `name`  = '".up($_POST['kat'])."'");
//bild hochladen und nach id bennen
			$getrennt = explode ('.',$_FILES['file']['name']);
			$getrennt = array_reverse ($getrennt);
			$neuername = mysql_insert_id().'.'.$getrennt[0];
        copy($tmpname, basePath."/inc/images/eventkat/".$neuername."");
        @unlink($_FILES['file']['tmp_name']);
		$show = info(_config_newskats_added, "?admin=ev_kat");
        }
		}
		
//#############################################################################		
      } elseif($_GET['do'] == "edit") {
        $qry = db("SELECT * FROM ".$sql_prefix."events_kat
                   WHERE id = '".intval($_GET['id'])."'");
        $get = _fetch($qry);

        $files = get_files('../inc/images/eventkat/');
        for($i=0; $i<count($files); $i++)
        {
          if($get['katimg'] == $files[$i]) $sel = "selected=\"selected\"";
          else $sel = '';

          $img .= show(_select_field, array("value" => $files[$i],
                                            "sel" => $sel,
                                            "what" => $files[$i]));
        }

        $upload = show(_config_ev_kats_katbild_upload_edit, array("id" => $_GET['id']));
        $do = show(_config_ev_kats_editid, array("id" => $_GET['id']));

        $show = show($dir."/eventkatform", array("head" => _config_newskats_edit_head,
                                                "nkat" => _config_katname,
                                                "kat" => re($get['name']),
                                                "value" => _button_value_edit,
                                                "id" => $_GET['id'],
                                                "nothing" => _nothing,
                                                "do" => $do,
                                                "nimg" => _config_newskats_katbild,
                                                "upload" => $upload,
                                                "img" => $img));
//#############################################################################													
      } elseif($_GET['do'] == "editeventkat") {
        if(empty($_POST['kat']))
        {
          $show = error(_config_empty_katname,1);
        } else {
          if($_POST['img'] == "lazy") $katimg = "";
          else $katimg = "`katimg` = '".up($_POST['img'])."',";


$tmpname = $_FILES['file']['tmp_name'];
      $name = $_FILES['file']['name'];
      $type = $_FILES['file']['type'];
      $size = $_FILES['file']['size'];
      $imageinfo = @getimagesize($tmpname);

	if(!$tmpname)
      {
          $qry = db("UPDATE ".$sql_prefix."events_kat
                     SET ".$katimg."
                         `name` = '".up($_POST['kat'])."'
                     WHERE id = '".intval($_GET['id'])."'");
					 
			//bild kopieren						 
			$getrennt = explode ('.',$_POST['img']);
			$getrennt = array_reverse ($getrennt);
			$neuername = $_GET['id'].'.'.$getrennt[0];
			copy(basePath."/inc/images/eventkat/".$_POST['img']."", basePath."/inc/images/eventkat/".$neuername."");

          $show = info(_config_newskats_edited, "?admin=ev_kat");
    } else {  
	$katimg = "`katimg` = '".$name."',";
					 $qry = db("UPDATE ".$sql_prefix."events_kat
                     SET ".$katimg."
                         `name` = '".up($_POST['kat'])."'
                     WHERE id = '".intval($_GET['id'])."'");

//bild hochladen und nach id bennen
			$getrennt = explode ('.',$_FILES['file']['name']);
			$getrennt = array_reverse ($getrennt);
			$neuername = $_GET['id'].'.'.$getrennt[0];
        copy($tmpname, basePath."/inc/images/eventkat/".$neuername."");
        @unlink($_FILES['file']['tmp_name']);
		$show = info(_config_newskats_edited, "?admin=ev_kat");
        }
        }
      }
    }
?>