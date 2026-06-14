<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LiveCheckResultSummaryForEachHourDBAccess
{
	public function __construct() {
	}
	
	public function InsertLiveCheckResultSummaryForEachHour($LiveCheckResultSummaryForEachHourObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLiveCheckResultSummaryForEachHour ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLiveCheckResultSummaryForEachHour ==
		
		$last_sql_command_for_mtooldb = "insert into LiveCheckResultSummaryForEachHour (CheckStartDT, CheckEndDT, CheckTargetURL, CheckOriginServerName, LiveCheckType, LiveCheckResult, SumCount) values('" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->CheckStartDT) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->CheckEndDT) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->CheckTargetURL) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->CheckOriginServerName) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->LiveCheckType) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->LiveCheckResult) . "', '" . $mtooldb->real_escape_string($LiveCheckResultSummaryForEachHourObj->SumCount) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function GetLiveCheckResultSummaryForEachHourList($param_LiveCheckResultSummaryForEachHour_CheckStartDT_where, $param_LiveCheckResultSummaryForEachHour_CheckEndDT_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultSummaryForEachHourList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultSummaryForEachHourList ==
		
		$last_sql_command_for_mtooldb = "select LiveCheckResultSummaryForEachHour.PID, LiveCheckResultSummaryForEachHour.CheckStartDT, LiveCheckResultSummaryForEachHour.CheckEndDT, LiveCheckResultSummaryForEachHour.CheckTargetURL, LiveCheckResultSummaryForEachHour.CheckOriginServerName, LiveCheckResultSummaryForEachHour.LiveCheckType, LiveCheckResultSummaryForEachHour.LiveCheckResult, LiveCheckResultSummaryForEachHour.SumCount from LiveCheckResultSummaryForEachHour where LiveCheckResultSummaryForEachHour.CheckStartDT = '" . $mtooldb->real_escape_string($param_LiveCheckResultSummaryForEachHour_CheckStartDT_where) . "' and LiveCheckResultSummaryForEachHour.CheckEndDT = '" . $mtooldb->real_escape_string($param_LiveCheckResultSummaryForEachHour_CheckEndDT_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LiveCheckResultSummaryForEachHourData();
			$thisresult->PID = $thisline[0];
			$thisresult->CheckStartDT = $thisline[1];
			$thisresult->CheckEndDT = $thisline[2];
			$thisresult->CheckTargetURL = $thisline[3];
			$thisresult->CheckOriginServerName = $thisline[4];
			$thisresult->LiveCheckType = $thisline[5];
			$thisresult->LiveCheckResult = $thisline[6];
			$thisresult->SumCount = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>