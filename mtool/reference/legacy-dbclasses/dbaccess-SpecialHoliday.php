<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class SpecialHolidayDBAccess
{
	public function __construct() {
	}
	
	public function GetAllList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAllList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAllList ==
		
		$last_sql_command_for_mtooldb = "select SpecialHoliday.PID, SpecialHoliday.year, SpecialHoliday.month, SpecialHoliday.day, SpecialHoliday.is_confirmed from SpecialHoliday";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecialHolidayData();
			$thisresult->PID = $thisline[0];
			$thisresult->year = $thisline[1];
			$thisresult->month = $thisline[2];
			$thisresult->day = $thisline[3];
			$thisresult->is_confirmed = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetYearListList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetYearListList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetYearListList ==
		
		$last_sql_command_for_mtooldb = "select distinct SpecialHoliday.year from SpecialHoliday order by SpecialHoliday.year";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecialHolidayData();
			$thisresult->year = $thisline[0];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDaysInYearList($param_SpecialHoliday_year_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDaysInYearList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDaysInYearList ==
		
		$last_sql_command_for_mtooldb = "select SpecialHoliday.PID, SpecialHoliday.year, SpecialHoliday.month, SpecialHoliday.day, SpecialHoliday.is_confirmed from SpecialHoliday where SpecialHoliday.year = '" . $mtooldb->real_escape_string($param_SpecialHoliday_year_where) . "' order by SpecialHoliday.year,SpecialHoliday.month,SpecialHoliday.day";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecialHolidayData();
			$thisresult->PID = $thisline[0];
			$thisresult->year = $thisline[1];
			$thisresult->month = $thisline[2];
			$thisresult->day = $thisline[3];
			$thisresult->is_confirmed = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecialHoliday($param_SpecialHoliday_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecialHoliday ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecialHoliday ==
		
		$last_sql_command_for_mtooldb = "select SpecialHoliday.PID, SpecialHoliday.year, SpecialHoliday.month, SpecialHoliday.day, SpecialHoliday.is_confirmed from SpecialHoliday where SpecialHoliday.PID = '" . $mtooldb->real_escape_string($param_SpecialHoliday_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecialHolidayData();
			$thisresult->PID = $thisline[0];
			$thisresult->year = $thisline[1];
			$thisresult->month = $thisline[2];
			$thisresult->day = $thisline[3];
			$thisresult->is_confirmed = $thisline[4];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertSpecialHoliday($SpecialHolidayObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertSpecialHoliday ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertSpecialHoliday ==
		
		$last_sql_command_for_mtooldb = "insert into SpecialHoliday (year, month, day) values('" . $mtooldb->real_escape_string($SpecialHolidayObj->year) . "', '" . $mtooldb->real_escape_string($SpecialHolidayObj->month) . "', '" . $mtooldb->real_escape_string($SpecialHolidayObj->day) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSpecialHoliday($SpecialHolidayObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSpecialHoliday ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSpecialHoliday ==
		
		$last_sql_command_for_mtooldb = "update SpecialHoliday SET year = '" . $mtooldb->real_escape_string($SpecialHolidayObj->year) . "', month = '" . $mtooldb->real_escape_string($SpecialHolidayObj->month) . "', day = '" . $mtooldb->real_escape_string($SpecialHolidayObj->day) . "', is_confirmed = '" . $mtooldb->real_escape_string("0") . "' where SpecialHoliday.PID = '" . $mtooldb->real_escape_string($SpecialHolidayObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateConfirmFlag($SpecialHolidayObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateConfirmFlag ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateConfirmFlag ==
		
		$last_sql_command_for_mtooldb = "update SpecialHoliday SET is_confirmed = '" . $mtooldb->real_escape_string($SpecialHolidayObj->is_confirmed) . "' where SpecialHoliday.PID = '" . $mtooldb->real_escape_string($SpecialHolidayObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSpecialHoliday($SpecialHolidayObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSpecialHoliday ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSpecialHoliday ==
		
		$last_sql_command_for_mtooldb = "delete from SpecialHoliday where SpecialHoliday.PID = '" . $mtooldb->real_escape_string($SpecialHolidayObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>