<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LiveCheckResultDBAccess
{
	public function __construct() {
	}
	
	public function GetLiveCheckResultList($param_LiveCheckResult_CheckTargetServerName_where, $param_LiveCheckResult_LiveCheckType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultList ==
		
		$last_sql_command_for_mtooldb = "select LiveCheckResult.PID, LiveCheckResult.CheckDT, LiveCheckResult.CheckTargetURL, LiveCheckResult.CheckOriginServerName, LiveCheckResult.LiveCheckType, LiveCheckResult.LiveCheckResult from LiveCheckResult where LiveCheckResult.CheckTargetServerName = '" . $mtooldb->real_escape_string($param_LiveCheckResult_CheckTargetServerName_where) . "' and LiveCheckResult.LiveCheckType = '" . $mtooldb->real_escape_string($param_LiveCheckResult_LiveCheckType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LiveCheckResultData();
			$thisresult->PID = $thisline[0];
			$thisresult->CheckDT = $thisline[1];
			$thisresult->CheckTargetURL = $thisline[2];
			$thisresult->CheckOriginServerName = $thisline[3];
			$thisresult->LiveCheckType = $thisline[4];
			$thisresult->LiveCheckResult = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLiveCheckResultSummaryList($param_LiveCheckResult_CheckDT_where, $param_LiveCheckResult_CheckDT_where2)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultSummaryList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLiveCheckResultSummaryList ==
		
		$last_sql_command_for_mtooldb = "select count(LiveCheckResult.PID), LiveCheckResult.CheckTargetURL, LiveCheckResult.CheckOriginServerName, LiveCheckResult.LiveCheckType, LiveCheckResult.LiveCheckResult from LiveCheckResult where LiveCheckResult.CheckDT >= '" . $mtooldb->real_escape_string($param_LiveCheckResult_CheckDT_where) . "' and LiveCheckResult.CheckDT < '" . $mtooldb->real_escape_string($param_LiveCheckResult_CheckDT_where2) . "' Group By LiveCheckResult.CheckTargetURL, LiveCheckResult.CheckOriginServerName, LiveCheckResult.LiveCheckType, LiveCheckResult.LiveCheckResult";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LiveCheckResultData();
			$thisresult->PID = $thisline[0];
			$thisresult->CheckTargetURL = $thisline[1];
			$thisresult->CheckOriginServerName = $thisline[2];
			$thisresult->LiveCheckType = $thisline[3];
			$thisresult->LiveCheckResult = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLatestSuccessedLiveCheckResult()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLatestSuccessedLiveCheckResult ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLatestSuccessedLiveCheckResult ==
		
		$last_sql_command_for_mtooldb = "select min(LiveCheckResult.CheckDT) from LiveCheckResult where LiveCheckResult.LiveCheckResult = '" . $mtooldb->real_escape_string("OK") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LiveCheckResultData();
			$thisresult->CheckDT = $thisline[0];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLiveCheckResult($LiveCheckResultObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLiveCheckResult ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLiveCheckResult ==
		
		$last_sql_command_for_mtooldb = "insert into LiveCheckResult (CheckTargetURL, CheckOriginServerName, LiveCheckType, LiveCheckResult) values('" . $mtooldb->real_escape_string($LiveCheckResultObj->CheckTargetURL) . "', '" . $mtooldb->real_escape_string($LiveCheckResultObj->CheckOriginServerName) . "', '" . $mtooldb->real_escape_string($LiveCheckResultObj->LiveCheckType) . "', '" . $mtooldb->real_escape_string($LiveCheckResultObj->LiveCheckResult) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSuccessedLiveCheckResult($param_LiveCheckResult_CheckDT_where, $param_LiveCheckResult_CheckDT_where2)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSuccessedLiveCheckResult ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSuccessedLiveCheckResult ==
		
		$last_sql_command_for_mtooldb = "delete from LiveCheckResult where LiveCheckResult.CheckDT >= '" . $mtooldb->real_escape_string($param_LiveCheckResult_CheckDT_where) . "' and LiveCheckResult.CheckDT < '" . $mtooldb->real_escape_string($param_LiveCheckResult_CheckDT_where2) . "' and LiveCheckResult.LiveCheckResult = '" . $mtooldb->real_escape_string("OK") . "'";
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