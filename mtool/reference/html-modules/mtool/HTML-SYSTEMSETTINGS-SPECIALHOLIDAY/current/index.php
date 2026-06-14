<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_EMAIL_VERIFY_AFTER_LOGIN = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_SPECIAL_HOLIDAY_LIST"); ?>-<?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php

include_once("/srv/legacy/www/mtool_work_lib/dbclasses/autoload_mtooldb_work.php");
include_once("/srv/legacy/www/mtool_work_lib/lib_mtool_work_db_datetime.php");
include_once("lib_update_special_holiday_snapshot.php");

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

printPathOnTopForSpecialHolidaySetting("Special Holiday Setting", "");

$DoConfirm = trim(GetParam("DoConfirm"));
$SpecialHolidayPID = trim(GetParam("SpecialHolidayPID"));
$DoUpdateSnapshot = trim(GetParam("DoUpdateSnapshot"));

$DASpecialHoliday = new SpecialHolidayDBAccess();

if ($DoUpdateSnapshot != "") {
	if (update_special_holiday_snapshot()) {
		?>
        <h3><font color="red">Snapshot was updated</font></h3>
        <?php
	}
}
if ($DoConfirm != "" && is_numeric($SpecialHolidayPID)) {
	$thisSpecialHoliday = new SpecialHolidayData();
	$thisSpecialHoliday->PID = $SpecialHolidayPID;
	$thisSpecialHoliday->is_confirmed =1;
	
	if ($DASpecialHoliday->UpdateConfirmFlag($thisSpecialHoliday)) {
		if (mysqli_affected_rows($mtooldb) > 0) {
			?>
			<h3><font color="red">Confirmed</font></h3>
			<?php
		} else {
			?>
			<h3><font color="red">No Change</font></h3>
			<?php
		}
	} else {
		?>
		<h3><font color="red">Failed to update confirm flog. Please contact to administrator if this continues.</font></h3>
		<?php
	}
}

if (check_special_holiday_snapshot()) {
	?>
    <h2><font color="red">Snapshot needs to be updated. <a href="./?DoUpdateSnapshot=y">Please Click here to update</a></font></h2>
    <?php
}

$YearList = $DASpecialHoliday->GetYearListList();

if (count($YearList) > 0) {
	?>
<table class="table">
  <thead>
	<tr bgcolor="#ECECEC">
	  <th>Year</th>
	  <th>Month</th>
	  <th>Day</th>
	  <th></th>
	  <th></th>
	</tr>
  </thead>
  <tbody>
	<?php
	for($i = 0 ; $i < count($YearList); $i++) {
		$year = $YearList[$i]->year;
		
		$DaysInYearList = $DASpecialHoliday->GetDaysInYearList($year);
		for($j = 0 ; $j < count($DaysInYearList); $j++) {
			$DaysInYear = $DaysInYearList[$j];
		?>
	<tr>
	  <td><?php print htmlspecialchars($year); ?></td>
	  <td><?php print htmlspecialchars($DaysInYear->month); ?></td>
	  <td><?php print htmlspecialchars($DaysInYear->day); ?></td>
	  <td><?php
		if ($DaysInYear->is_confirmed == 0) {
			?>
			<font color="red">Not Confirmed</font> (<a href="./?DoConfirm=yes&SpecialHolidayPID=<?php print urlencode($DaysInYear->PID); ?>">Do Confirm</a>)
			<?php
		}
	  ?></td>
	  <td><a href="specialholiday_edit.php?PID=<?php print urlencode($DaysInYear->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
	</tr>
	<?php
		}
	}
	?>
  </tbody>
</table>
<?php
	
} else {
	?>
<p>No Setting</p>
<?php
}
?>
<p align="right"><a href="specialholiday_edit.php?<?php print makeRandStr(8); ?>">Add Special Holiday Setting</a></p>

<br>
<br>
<br>

<table class="table">
  <thead>
	<tr bgcolor="#ECECEC">
	  <th><?php print get_week_name_from_index(0); ?></th>
	  <th><?php print get_week_name_from_index(1); ?></th>
	  <th><?php print get_week_name_from_index(2); ?></th>
	  <th><?php print get_week_name_from_index(3); ?></th>
	  <th><?php print get_week_name_from_index(4); ?></th>
	  <th><?php print get_week_name_from_index(5); ?></th>
	  <th><?php print get_week_name_from_index(6); ?></th>
	</tr>
  </thead>
  <tbody>
<?php

$now_t = time();
$now_y = date("Y", $now_t);
$now_m = date("m", $now_t);
$now_d = date("j", $now_t);

$year = 2015;
$month = 12;
$day = 27;

$this_t = make_integer_ymdhm($year, $month, $day, 0, 0);

while(true) {
	?>
    <tr>
    <?php
	for($this_w = 0 ; $this_w <= 6 ; $this_w++) {
		$this_y = date("Y", $this_t);
		$this_m = date("n", $this_t);
		$this_d = date("j", $this_t);
		
		$is_holiday = CheckIfHolidayByMtoolWorkDB($this_y, $this_m, $this_d);
		$is_daytime = CheckIfDaytimeByMtoolWorkDB($this_y, $this_m, $this_d, 9, 30);
		?>
        <td <?php
		if ($is_holiday) {
			print ' bgcolor="#FFEAEB"';
		}
		if ($is_daytime) {
			print ' bgcolor="#E8F3FF"';
		}
		?>><?php print "$this_y/$this_m/$this_d";
		if ($is_holiday) {
			print "(H)";
		}
		if ($is_daytime) {
			print "(D)";
		}
		?></td>
        <?php
		
		$this_t += (60 * 60 * 24);
	}
	?>
    </tr>
    <?php
	if ($this_y > $now_y + 3) {
		break;
	}
}



?>
</tbody>
</table>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content
