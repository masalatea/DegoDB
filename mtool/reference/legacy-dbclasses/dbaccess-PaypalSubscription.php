<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class PaypalSubscriptionDBAccess
{
	public function __construct() {
	}
	
	public function GetActiveEikaiwaSubscriptionList($param_PaypalSubscription_STUDENT_ID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetActiveEikaiwaSubscriptionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetActiveEikaiwaSubscriptionList ==
		
		$last_sql_command_for_mtooldb = "select PaypalSubscription.PID, PaypalSubscription.STUDENT_ID, PaypalSubscription.Enabled, PaypalSubscription.CreatedTimestamp, PaypalSubscription.itemname, PaypalSubscription.is_eikaiwa, PaypalSubscription.is_cloud from PaypalSubscription where PaypalSubscription.STUDENT_ID = '" . $mtooldb->real_escape_string($param_PaypalSubscription_STUDENT_ID_where) . "' and PaypalSubscription.Enabled = '" . $mtooldb->real_escape_string("1") . "' and PaypalSubscription.is_eikaiwa = '" . $mtooldb->real_escape_string("1") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new PaypalSubscriptionData();
			$thisresult->PID = $thisline[0];
			$thisresult->STUDENT_ID = $thisline[1];
			$thisresult->Enabled = $thisline[2];
			$thisresult->CreatedTimestamp = $thisline[3];
			$thisresult->itemname = $thisline[4];
			$thisresult->is_eikaiwa = $thisline[5];
			$thisresult->is_cloud = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetActiveCloudSubscriptionList($param_PaypalSubscription_STUDENT_ID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetActiveCloudSubscriptionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetActiveCloudSubscriptionList ==
		
		$last_sql_command_for_mtooldb = "select PaypalSubscription.PID, PaypalSubscription.STUDENT_ID, PaypalSubscription.Enabled, PaypalSubscription.CreatedTimestamp, PaypalSubscription.itemname, PaypalSubscription.is_eikaiwa, PaypalSubscription.is_cloud from PaypalSubscription where PaypalSubscription.STUDENT_ID = '" . $mtooldb->real_escape_string($param_PaypalSubscription_STUDENT_ID_where) . "' and PaypalSubscription.Enabled = '" . $mtooldb->real_escape_string("1") . "' and PaypalSubscription.is_cloud = '" . $mtooldb->real_escape_string("1") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new PaypalSubscriptionData();
			$thisresult->PID = $thisline[0];
			$thisresult->STUDENT_ID = $thisline[1];
			$thisresult->Enabled = $thisline[2];
			$thisresult->CreatedTimestamp = $thisline[3];
			$thisresult->itemname = $thisline[4];
			$thisresult->is_eikaiwa = $thisline[5];
			$thisresult->is_cloud = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertPaypalSubscription($PaypalSubscriptionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertPaypalSubscription ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertPaypalSubscription ==
		
		$last_sql_command_for_mtooldb = "insert into PaypalSubscription (STUDENT_ID, Enabled, itemname, is_eikaiwa, is_cloud) values('" . $mtooldb->real_escape_string($PaypalSubscriptionObj->STUDENT_ID) . "', '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->Enabled) . "', '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->itemname) . "', '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->is_eikaiwa) . "', '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->is_cloud) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateEnabledEikaiwaSubscription($PaypalSubscriptionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateEnabledEikaiwaSubscription ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateEnabledEikaiwaSubscription ==
		
		$last_sql_command_for_mtooldb = "update PaypalSubscription SET Enabled = '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->Enabled) . "' where PaypalSubscription.STUDENT_ID = '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->STUDENT_ID) . "' and PaypalSubscription.is_eikaiwa = '" . $mtooldb->real_escape_string("1") . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateEnabledCloudSubscription($PaypalSubscriptionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateEnabledCloudSubscription ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateEnabledCloudSubscription ==
		
		$last_sql_command_for_mtooldb = "update PaypalSubscription SET Enabled = '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->Enabled) . "' where PaypalSubscription.STUDENT_ID = '" . $mtooldb->real_escape_string($PaypalSubscriptionObj->STUDENT_ID) . "' and PaypalSubscription.is_cloud = '" . $mtooldb->real_escape_string("1") . "'";
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