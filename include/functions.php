<?php
//  -----------------------------------------------------------------------  //
//                           Jobs for Xoops 2.4.x                            //
//                  By John Mordo from the myAds 2.04 Module                 //
//                    All Original credits left below this                   //
//                                                                           //
//                                                                           //
//                                                                           //
//                                                                           //
// ------------------------------------------------------------------------- //
//               E-Xoops: Content Management for the Masses                  //
//                       < http://www.e-xoops.com >                          //
// ------------------------------------------------------------------------- //
// Original Author: Pascal Le Boustouller                                    //
// Author Website : pascal.e-xoops@perso-search.com                          //
// Licence Type   : GPL                                                      //
// ------------------------------------------------------------------------- //

$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;

require_once( XOOPS_ROOT_PATH."/modules/$mydirname/include/gtickets.php" ) ;

function ExpireJob()
{
	global $xoopsDB, $xoopsConfig, $xoopsModuleConfig, $myts, $meta, $mydirname;

	$datenow = time();

	$result5 = $xoopsDB->query("select lid, title, expire, type, company, desctext, requirements, contactinfo, date, email, submitter, usid, photo, view FROM ".$xoopsDB->prefix("jobs_listing")." WHERE valid='Yes'");

	while(list($lids, $title, $expire, $type, $company, $desctext, $requirements, $contactinfo, $dateann, $email, $submitter, $usid, $photo, $lu) = $xoopsDB->fetchRow($result5)) {
		$title = $myts->addSlashes($title);
		$expire = $myts->addSlashes($expire);
		$type = $myts->addSlashes($type);
		$company = $myts->addSlashes($company);
		$desctext = $myts->displayTarea($desctext,1,1,1,1,1);
		$requirements = $myts->displayTarea($requirements,1,1,1,1,1);
		$contactinfo = $myts->addSlashes($contactinfo);
		$submitter = $myts->addSlashes($submitter);
		$usid = intval($usid);

		$supprdate = $dateann + ($expire*86400);
		if ($supprdate < $datenow) {
			$xoopsDB->queryF("delete from ".$xoopsDB->prefix("jobs_listing")." where lid=".mysql_real_escape_string($lids)."");

		$destination = XOOPS_ROOT_PATH."/modules/$mydirname/logo_images";

			if($photo) {
				if (file_exists("$destination/$photo")) {
					unlink("$destination/$photo");
				}
			}

	$comp_id = jobs_getCompIdFromName($company);
	$extra_users = jobs_getCompany($comp_id, $usid);

	$extra_user1 = $extra_users['comp_user1'];
	$extra_user2 = $extra_users['comp_user2'];

	if ($extra_user1) {
	$result = $xoopsDB->query("select email from ".$xoopsDB->prefix("users")." where uid=$extra_user1");
	list($extra_user1_email) = $xoopsDB->fetchRow($result);
	$extra_user1_email = $extra_user1_email;
	} else {
		$extra_user1_email = "";
	}

	if ($extra_user2) {
	$result = $xoopsDB->query("select email from ".$xoopsDB->prefix("users")." where uid=$extra_user2");
	list($extra_user2_email) = $xoopsDB->fetchRow($result);
	$extra_user2_email = $extra_user2_email;
	} else {
		$extra_user2_email = "";
	}

	if ($email) {
	$tags=array();
	$tags['TITLE'] = $title;
	$tags['TYPE'] = $type;
	$tags['COMPANY'] = $company;
	$tags['DESCTEXT'] = $desctext;
	$tags['MY_SITENAME'] = $xoopsConfig['sitename'];
	$tags['REPLY_ON'] = _JOBS_REMINDANN;
	$tags['DESCRIPT'] = _JOBS_DESC;
	$tags['TO'] = _JOBS_TO;
	$tags['SUBMITTER'] = $submitter;
	$tags['EMAIL'] = _JOBS_EMAIL;
	$tags['HELLO'] = _JOBS_HELLO;
	$tags['YOUR_JOB'] = _JOBS_YOUR_JOB;
	$tags['THANKS'] = _JOBS_THANK;
	$tags['WEBMASTER'] = _JOBS_WEBMASTER;
	$tags['AT'] = _JOBS_AT;
	$tags['SENDER_IP'] = $_SERVER['REMOTE_ADDR'];
	$tags['HITS'] = $lu;
	$tags['TIMES'] = _JOBS_TIMES;
	$tags['VIEWED'] = _JOBS_VIEWED;
	$tags['ON'] = _JOBS_ON;
	$tags['EXPIRED'] = _JOBS_EXPIRED;

	$subject = ""._JOBS_STOP2.""._JOBS_STOP3."";
	$mail =& xoops_getMailer();

	if (is_dir("language/".$xoopsConfig['language']."/mail_template/") ) {
	$mail->setTemplateDir(XOOPS_ROOT_PATH."/modules/$mydirname/language/".$xoopsConfig['language']."/mail_template/");
	} else {
	$mail->setTemplateDir(XOOPS_ROOT_PATH."/modules/$mydirname/language/english/mail_template/");
	}

	$mail->setTemplate("jobs_listing_expired.tpl");
	$mail->useMail();
	$mail->setFromEmail($xoopsConfig['adminmail']);
	$mail->setToEmails(array($email,$extra_user1_email,$extra_user2_email));
	$mail->setSubject($subject);
	$mail->multimailer->isHTML(true);
	$mail->assign($tags);
	$mail->send();
	echo $mail->getErrors();
			}
		}
	}
}

function jobs_getTotalItems($sel_id, $status="")
{
	global $xoopsDB, $mytree, $mydirname;
	$categories = jobs_MygetItemIds("".$mydirname."_view");
	$count = 0;
	$arr = array();
	if(in_array($sel_id, $categories)) {
		$query = "select count(*) from ".$xoopsDB->prefix("".$mydirname."_listing")." where cid=".intval($sel_id)." and valid='Yes' and status!='1'";
		
		$result = $xoopsDB->query($query);
		list($thing) = $xoopsDB->fetchRow($result);
		$count = $thing;
		$arr = $mytree->getAllChildId($sel_id);
		$size = count($arr);
		for($i=0;$i<$size;$i++){
			if(in_array($arr[$i], $categories)) {
				$query2 = "select count(*) from ".$xoopsDB->prefix("".$mydirname."_listing")." where cid=".intval($arr[$i])." and valid='Yes' and status!='1'";
				
				$result2 = $xoopsDB->query($query2);
				list($thing) = $xoopsDB->fetchRow($result2);
				$count += $thing;
			}
		}
	}
	return $count;
}

function JobsShowImg()
{
	global $mydirname;
	
	echo "<script type=\"text/javascript\">\n";
	echo "<!--\n\n";
	echo "function showimage() {\n";
	echo "if (!document.images)\n";
	echo "return\n";
	echo "document.images.avatar.src=\n";
	echo "'".XOOPS_URL."/modules/$mydirname/images/cat/' + document.imcat.img.options[document.imcat.img.selectedIndex].value\n";
	echo "}\n\n";
	echo "//-->\n";
	echo "</script>\n";
}

//Reusable Link Sorting Functions
function jobs_convertorderbyin($orderby) {
	switch (trim($orderby)) {
	case "titleA":
		$orderby = "title ASC";
		break;
	case "dateA":
		$orderby = "date ASC";
		break;
	case "viewA":
		$orderby = "view ASC";
		break;
	case "companyA":
		$orderby = "company ASC";
		break;
	case "townA":
		$orderby = "town ASC";
		break;
	case "stateA":
		$orderby = "state ASC";
		break;
	case "titleD":
		$orderby = "title DESC";
		break;
	case "viewD":
		$orderby = "view DESC";
		break;
	case "companyD":
		$orderby = "company DESC";
		break;
	case "townD":
		$orderby = "town DESC";
		break;
	case "stateD":
		$orderby = "state DESC";
		break;
	case "dateD":
	default:
		$orderby = "date DESC";
		break;
	}
	return $orderby;
}

function jobs_convertorderbytrans($orderby) {
            if ($orderby == "view ASC")     $orderbyTrans = ""._JOBS_POPULARITYLTOM."";
            if ($orderby == "view DESC")    $orderbyTrans = ""._JOBS_POPULARITYMTOL."";
            if ($orderby == "title ASC")    $orderbyTrans = ""._JOBS_TITLEATOZ."";
           if ($orderby == "title DESC")    $orderbyTrans = ""._JOBS_TITLEZTOA."";
            if ($orderby == "date ASC")     $orderbyTrans = ""._JOBS_DATEOLD."";
            if ($orderby == "date DESC")    $orderbyTrans = ""._JOBS_DATENEW."";
            if ($orderby == "company ASC")  $orderbyTrans = ""._JOBS_COMPANYATOZ."";
            if ($orderby == "company DESC") $orderbyTrans = ""._JOBS_COMPANYZTOA."";
	    if ($orderby == "town ASC")      $orderbyTrans = ""._JOBS_LOCALATOZ."";
            if ($orderby == "town DESC")     $orderbyTrans = ""._JOBS_LOCALZTOA."";
	    if ($orderby == "state ASC")      $orderbyTrans = ""._JOBS_STATEATOZ."";
            if ($orderby == "state DESC")     $orderbyTrans = ""._JOBS_STATEZTOA."";
            return $orderbyTrans;
}


function jobs_convertorderby($orderby) {
            if ($orderby == "title ASC")         $orderby = "titleA";
            if ($orderby == "date ASC")          $orderby = "dateA";
            if ($orderby == "company ASC")       $orderby = "companyA"; 
            if ($orderby == "town ASC")          $orderby = "townA";
            if ($orderby == "state ASC")         $orderby = "stateA";
            if ($orderby == "view ASC")          $orderby = "viewA";
            if ($orderby == "title DESC")        $orderby = "titleD";
            if ($orderby == "date DESC")         $orderby = "dateD";
            if ($orderby == "company DESC")      $orderby = "companyD";
            if ($orderby == "town DESC")         $orderby = "townD";
            if ($orderby == "state DESC")        $orderby = "stateD";
            if ($orderby == "view DESC")         $orderby = "viewD";
            return $orderby;
}

function JobTableExists($tablename)
	{
		global $xoopsDB;
		$result=$xoopsDB->queryF("SHOW TABLES LIKE '$tablename'");
		return($xoopsDB->getRowsNum($result) > 0);
	}

function JobFieldExists($fieldname,$table)
	{
		global $xoopsDB;
		$result=$xoopsDB->queryF("SHOW COLUMNS FROM $table LIKE '$fieldname'");
		return($xoopsDB->getRowsNum($result) > 0);
	}

function JobAddField($field, $table)
	{
		global $xoopsDB;
		$result=$xoopsDB->queryF("ALTER TABLE " . $table . " ADD $field");
		return $result;
	}

function jobs_getEditor($caption, $name, $value = "", $width = '99%', $height ='200px', $supplemental=''){

    global $xoopsModuleConfig;

if ($xoopsModuleConfig['jobs_form_options'] == 'dhtmltextarea') {
$nohtml = "1";
} else {
$nohtml = "0";
}

$editor_configs=array();
$editor_configs["name"] = $name;
$editor_configs["value"] = $value;
$editor_configs["rows"] = 25;
$editor_configs["cols"] = 70;
$editor_configs["width"] = "95%";
$editor_configs["height"] = "12%";
$editor_configs["editor"] = strtolower($xoopsModuleConfig['jobs_form_options']);
if ( is_readable(XOOPS_ROOT_PATH . '/class/xoopseditor/xoopseditor.php')) {
	require_once(XOOPS_ROOT_PATH . '/class/xoopseditor/xoopseditor.php');
	$editor = new XoopsFormEditor($caption, $name, $editor_configs, $nohtml, $onfailure = 'textarea');
		return $editor;
	}
}

function jobs_getIdFromUname($uname) {
           global $xoopsDB, $xoopsConfig, $myts, $xoopsUser;

           $sql = "SELECT uid FROM ".$xoopsDB->prefix("users")." WHERE uname = '$uname'";

           if ( !$result = $xoopsDB->query($sql) ) {
               return false;
           }

           if ( !$arr = $xoopsDB->fetchArray($result) ) {
               return false;
           }

           $uid = $arr['uid'];

           return $uid;
       }

function jobs_getCompCount($usid) {
	global $xoopsDB, $xoopsUser;

	$sql = "SELECT count(*) as count FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE ".$usid." IN (comp_usid, comp_user1, comp_user2)";
		 $result = $xoopsDB->query($sql);
         if (!$result) {
         return 0;;
		} else {
	list($count) = $xoopsDB->fetchRow($result);
         return $count;
     }
}

function jobs_getCompany($usid=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_address, comp_address2, comp_city, comp_state, comp_zip, comp_phone, comp_fax, comp_url, comp_img, comp_usid, comp_user1, comp_user2, comp_contact, comp_user1_contact, comp_user2_contact FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE ".$usid." IN (comp_usid, comp_user1, comp_user2)";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$company = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$company = $row;
		}
	return $company;
}

function jobs_getPriceType() {
	global $xoopsDB;
	$sql = "SELECT nom_type FROM " . $xoopsDB->prefix("jobs_price") . " ORDER BY nom_type";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		} else {
			$rows = array();
			while($row = $xoopsDB->fetchArray($result)) {
				$rows[] = $row;
			}
			return ($rows);
		}
}

function jobs_MygetItemIds($permtype)
{
	global $xoopsUser, $mydirname;
	static $permissions = array();
	if(is_array($permissions) && array_key_exists($permtype, $permissions)) {
		return $permissions[$permtype];
	}

   	$module_handler =& xoops_gethandler('module');
   	$myModule =& $module_handler->getByDirname("jobs");
   	$groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
   	$gperm_handler =& xoops_gethandler('groupperm');
   	$categories = $gperm_handler->getItemIds($permtype, $groups, $myModule->getVar('mid'));
   	$permissions[$permtype] = $categories;
    return $categories;
}

function jobs_getCatNameFromId($cid) {
           global $xoopsDB, $xoopsConfig, $myts, $xoopsUser, $mydirname;

           $sql = "SELECT title FROM ".$xoopsDB->prefix("jobs_categories")." WHERE cid = '$cid'";

           if ( !$result = $xoopsDB->query($sql) ) {
               return false;
           }

           if ( !$arr = $xoopsDB->fetchArray($result) ) {
               return false;
           }

           $title = $arr['title'];

           return $title;
       }

function jobs_getStateNameFromId($rid) {
           global $xoopsDB, $xoopsConfig, $myts, $xoopsUser, $mydirname;

           $sql = "SELECT name FROM ".$xoopsDB->prefix("jobs_region")." WHERE rid = '$rid'";

           if ( !$result = $xoopsDB->query($sql) ) {
               return false;
           }

           if ( !$arr = $xoopsDB->fetchArray($result) ) {
               return false;
           }

           $name = $arr['name'];

           return $name;
       }

function jobs_getCompIdFromName($name) {
           global $xoopsDB, $xoopsConfig, $myts, $xoopsUser;

           $sql = "SELECT comp_id FROM ".$xoopsDB->prefix("jobs_companies")." WHERE comp_name = '$name'";

           if ( !$result = $xoopsDB->query($sql) ) {
               return false;
           }
	   if ( !$arr = $xoopsDB->fetchArray($result) ) {
               return false;
           }

           $comp_id = $arr['comp_id'];

           return $comp_id;
       }


function jobs_getCompanyWithListing($usid) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_address, comp_address2, comp_city, comp_state, comp_zip, comp_phone, comp_fax, comp_url, comp_img, comp_usid, comp_user1, comp_user2, comp_contact, comp_user1_contact, comp_user2_contact FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE comp_usid = '$usid' OR comp_user1 = '$usid' OR  comp_user2 = '$usid' order by comp_id";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$companies = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$companies = $row;
		}
	return $companies;
}

function jobs_getPremiumListings($cid) {
	global $xoopsDB, $xoopsUser;

	$sql = "SELECT lid, cid, title, status, expire, type, company, price, typeprice, date, town, state, valid, premium, photo, view from ".$xoopsDB->prefix("jobs_listing")." WHERE cid=".mysql_real_escape_string($cid)." AND valid='yes' AND premium='1' AND status!='1' order by date";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$premium_listings = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$premium_listings = $row;
		}
	return $premium_listings;
}

function jobs_getAllCompanies() {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_address, comp_address2, comp_city, comp_state, comp_zip, comp_phone, comp_fax, comp_url, comp_img, comp_usid, comp_user1, comp_user2, comp_contact, comp_user1_contact, comp_user2_contact FROM " . $xoopsDB->prefix("jobs_companies") . "";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$companies = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$companies = $row;
		}
	return $companies;
}

function jobs_categorynewgraphic ($cat)
{
    global $xoopsDB, $mydirname, $xoopsUser, $xoopsModuleConfig;
	
    $newresult = $xoopsDB->query("select date from ".$xoopsDB->prefix("jobs_listing")." where cid=".mysql_real_escape_string($cat)." and valid = 'Yes' order by date desc limit 1");
    list($date)= $xoopsDB->fetchRow($newresult);

	$useroffset = "";
	if($xoopsUser) {
	$timezone = $xoopsUser->timezone();
		if(isset($timezone)) {
		$useroffset = $xoopsUser->timezone();
		} else {
		$useroffset = $xoopsConfig['default_TZ'];
		}
	}
	$date = ($useroffset*3600) + $date;

	$days_new = $xoopsModuleConfig['jobs_countday'];
	$startdate = (time()-(86400 * $days_new));

	if ($startdate < $date) {
		return "<img src=\"".XOOPS_URL."/modules/$mydirname/images/newred.gif\" />";
	}
}

function jobs_subcatnew($cid)
{
    global $xoopsDB, $mydirname;
	
    $newresult = $xoopsDB->query("select date from ".$xoopsDB->prefix("jobs_listing")." where cid=".mysql_real_escape_string($cid)." and valid = 'Yes' order by date desc limit 1");
    list($timeann)= $xoopsDB->fetchRow($newresult);
	
    $count = 1;
	$startdate = (time()-(86400 * $count));

	if ($startdate < $timeann) {
		return true;
	}
}

function jobs_listingnewgraphic($date)
{
    global $xoopsDB, $mydirname, $xoopsModuleConfig;
	
    $days_new = $xoopsModuleConfig['jobs_countday'];
	$startdate = (intval(time())-(86400 * intval($days_new)));

	if ($startdate < $date) {
		return "<img src=\"".XOOPS_URL."/modules/$mydirname/images/newred.gif\" />";
	}
}

function jobs_getCompNameFromId($comp_id) 
{
           global $xoopsDB, $xoopsConfig, $myts, $xoopsUser;

           $sql = "SELECT comp_name FROM ".$xoopsDB->prefix("jobs_companies")." WHERE comp_id = '$comp_id'";

           if ( !$result = $xoopsDB->query($sql) ) {
               return false;
           }

           if ( !$arr = $xoopsDB->fetchArray($result) ) {
               return false;
           }

           $comp_name = $arr['comp_name'];

           return $comp_name;
       }


function jobs_getCompanyUsers($comp_id=0,$usid=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_usid, comp_user1, comp_user2 FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE comp_id = '$comp_id' AND  ".$usid." IN (comp_usid, comp_user1, comp_user2)";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$their_comp = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$their_comp = $row;
		}
	return $their_comp;
}

function jobs_getXtraUsers($comp_id=0, $member_usid=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_user1, comp_user2 FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE comp_id = '$comp_id' AND ".$member_usid." IN (comp_user1, comp_user2)";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$xtra_users = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$xtra_users = $row;
		}
	return $xtra_users;
}

function jobs_getAllUserCompanies($member_usid=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_usid, comp_user1, comp_user2 FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE ".$member_usid." IN (comp_usid, comp_user1, comp_user2)";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$xtra_users = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$xtra_users = $row;
		}
	return $xtra_users;
}

function jobs_getThisCompany($comp_id, $usid=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_address, comp_address2, comp_city, comp_state, comp_zip, comp_phone, comp_fax, comp_url, comp_img, comp_usid, comp_user1, comp_user2, comp_contact, comp_user1_contact, comp_user2_contact FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE comp_id = '$comp_id' AND ".$usid." IN ( comp_usid, comp_user1, comp_user2)";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$thiscompany = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$thiscompany = $row;
		}
	return $thiscompany;
}

function jobs_getACompany($comp_id=0) {
	global $xoopsDB, $xoopsUser;
	$sql = "SELECT comp_id, comp_name, comp_address, comp_address2, comp_city, comp_state, comp_zip, comp_phone, comp_fax, comp_url, comp_img, comp_usid, comp_user1, comp_user2, comp_contact, comp_user1_contact, comp_user2_contact FROM " . $xoopsDB->prefix("jobs_companies") . " WHERE comp_id='$comp_id'";
		if ( !$result = $xoopsDB->query($sql) ) {
			return 0;
		}
		$company = array();
		while ( $row = $xoopsDB->fetchArray($result) ) {
			$company = $row;
		}
	return $company;
}

function jobs_isX24plus()
{
    $x24plus = false;
    $xv = str_replace('XOOPS ','',XOOPS_VERSION);
    if(substr($xv,2,1) >= '4') {
        $x24plus = true;
    }
    return $x24plus;
}

?>