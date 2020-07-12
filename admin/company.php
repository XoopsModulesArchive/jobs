<?php
//                 Jobs for Xoops 2.3.3b and up  by John Mordo - jlm69 at Xoops              //
//                                                                                           //
include_once '../../../include/cp_header.php';
$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
include_once (XOOPS_ROOT_PATH."/modules/$mydirname/include/functions.php");

include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";

include_once(XOOPS_ROOT_PATH."/modules/$mydirname/class/jobtree.php");

$myts =& MyTextSanitizer::getInstance();

    include 'header.php';
    xoops_cp_header();
    loadModuleAdminMenu(3, "");

	include XOOPS_ROOT_PATH.'/class/pagenav.php';

	$countresult=$xoopsDB->query("select COUNT(*) FROM ".$xoopsDB->prefix("jobs_companies")."");
	list($crow) = $xoopsDB->fetchRow($countresult);
	$crows = $crow;

	$nav = '';
	if ($crows > "0") {
// shows number of companies per page default = 15
	$showonpage = 15;
	$show = "";
	$show = (intval($show) > 0 ) ? intval($show) : $showonpage ;

	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
	if (!isset($max)) {
        $max = $start + $show;
	}

	$sql = "select comp_id, comp_name, comp_date_added from ".$xoopsDB->prefix("jobs_companies")." ORDER BY comp_name";

	$result1=$xoopsDB->query($sql,$show,$start);
	echo "<table border=1 width=100% cellpadding=2 cellspacing=0 border=0><td><tr>";
	if ($crows>0) {
	$nav = new XoopsPageNav($crows, $showonpage, $start, 'start', 'op=Company');
	echo "<fieldset><legend style='font-weight: bold; color: #900;'>"._AM_JOBS_MAN_COMPANY."</legend>"; 
	echo "<br />"._AM_JOBS_THEREIS." <b>$crows</b> "._AM_JOBS_COMPANIES."<br /><br />";
	echo "<fieldset><legend style='font-weight: bold; color:#900;'>"._AM_JOBS_ADD_COMPANY."</legend>";
	echo "<a href=\"addcomp.php\">"._AM_JOBS_ADD_COMPANY."</a></fieldset>";
	echo "</td></tr></table>";
	echo $nav->renderNav();
	echo "<br /><br /><table width=100% cellpadding=2 cellspacing=0 border=0>";
	$rank = 1;
	}
	while(list($comp_id, $comp_name, $comp_date_added) = $xoopsDB->fetchRow($result1)) {

	$comp_name = $myts->htmlSpecialChars($comp_name);
	$date2 = formatTimestamp($comp_date_added,"s");

	if(is_integer($rank/2)) {
	$color="even";
	} else {
	$color="odd";
	}

	echo "<tr class='$color'><td><a href=\"modcomp.php?comp_id=$comp_id\">$comp_name</a></td><td><a href=\"../members.php?comp_id=$comp_id\">"._AM_JOBS_VIEW_LISTINGS."</a></td><td><a href=\"delcomp.php?comp_id=$comp_id&comp_name=$comp_name\">"._AM_JOBS_DEL." - ".$comp_name."</a></td><td align=right> $date2</td></tr>";
	$rank++;
	}
	echo "</table><br />";
	echo "</fieldset><br />";
	} else {
	echo "<fieldset><legend style='font-weight: bold; color: #900;'>". _AM_JOBS_MAN_COMPANY . "</legend>"; 
	echo "<br /> "._AM_JOBS_NOCOMPANY."<br /><br />";
	echo "</fieldset>

	<fieldset><legend style='font-weight: bold; color:#900;'>"._AM_JOBS_ADD_COMPANY."</legend>";
	echo "<a href=\"addcomp.php\">"._AM_JOBS_ADD_COMPANY."</a></fieldset>
	</table<br />"; 
	}
	xoops_cp_footer();

?>