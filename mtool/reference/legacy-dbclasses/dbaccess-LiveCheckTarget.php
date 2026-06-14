<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LiveCheckTargetDBAccess
{
	public function __construct() {
	}
	
	public function GetLiveCheckTargetList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLiveCheckTargetList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLiveCheckTargetList ==
		
		$last_sql_command_for_mtooldb = "select LiveCheckTarget.PID, LiveCheckTarget.CheckTargetBaseURL from LiveCheckTarget order by LiveCheckTarget.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LiveCheckTargetData();
			$thisresult->PID = $thisline[0];
			$thisresult->CheckTargetBaseURL = $thisline[1];
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