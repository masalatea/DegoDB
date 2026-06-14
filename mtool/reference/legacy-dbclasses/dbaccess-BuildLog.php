<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildLogDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildLogList($param_BuildLog_BuildTokenPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildLogList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildLogList ==
		
		$last_sql_command_for_mtooldb = "select BuildLog.PID, BuildLog.BuildTokenPID, BuildLog.MessageType, BuildLog.Message, BuildLog.AddedDT from BuildLog where BuildLog.BuildTokenPID = '" . $mtooldb->real_escape_string($param_BuildLog_BuildTokenPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildLogData();
			$thisresult->PID = $thisline[0];
			$thisresult->BuildTokenPID = $thisline[1];
			$thisresult->MessageType = $thisline[2];
			$thisresult->Message = $thisline[3];
			$thisresult->AddedDT = $thisline[4];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertBuildLog($BuildLogObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildLog ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildLog ==
		
		$last_sql_command_for_mtooldb = "insert into BuildLog (BuildTokenPID, MessageType, Message) values('" . $mtooldb->real_escape_string($BuildLogObj->BuildTokenPID) . "', '" . $mtooldb->real_escape_string($BuildLogObj->MessageType) . "', '" . $mtooldb->real_escape_string($BuildLogObj->Message) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildLog()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildLog ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildLog ==
		
		$last_sql_command_for_mtooldb = "delete from BuildLog where BuildLog.BuildTokenPID not in (select PID from BuildToken)";
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