<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class BuildTokenCompletedItemDBAccess
{
	public function __construct() {
	}
	
	public function GetBuildTokenCompletedItemList($param_BuildTokenCompletedItem_BuildTokenPID_where, $param_BuildTokenCompletedItem_ProjectPID_where, $param_BuildTokenCompletedItem_ProjectSourceOutputPID_where, $param_BuildTokenCompletedItem_BuildTargetType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetBuildTokenCompletedItemList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetBuildTokenCompletedItemList ==
		
		$last_sql_command_for_mtooldb = "select BuildTokenCompletedItem.PID, BuildTokenCompletedItem.ProjectPID, BuildTokenCompletedItem.BuildTokenPID, BuildTokenCompletedItem.ProjectSourceOutputPID, BuildTokenCompletedItem.BuildTargetType, BuildTokenCompletedItem.EachTargetPID, BuildTokenCompletedItem.option_anyOutput, BuildTokenCompletedItem.option_createdBaseClasses from BuildTokenCompletedItem where BuildTokenCompletedItem.BuildTokenPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_BuildTokenPID_where) . "' and BuildTokenCompletedItem.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_ProjectPID_where) . "' and BuildTokenCompletedItem.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_ProjectSourceOutputPID_where) . "' and BuildTokenCompletedItem.BuildTargetType = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_BuildTargetType_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildTokenCompletedItemData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->BuildTokenPID = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			$thisresult->BuildTargetType = $thisline[4];
			$thisresult->EachTargetPID = $thisline[5];
			$thisresult->option_anyOutput = $thisline[6];
			$thisresult->option_createdBaseClasses = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetCountForTokenAndProjectSourceOutput($param_BuildTokenCompletedItem_BuildTokenPID_where, $param_BuildTokenCompletedItem_ProjectPID_where, $param_BuildTokenCompletedItem_ProjectSourceOutputPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetCountForTokenAndProjectSourceOutput ==
		// == END OF EDITABLE AREA FOR FUNCTION GetCountForTokenAndProjectSourceOutput ==
		
		$last_sql_command_for_mtooldb = "select count(BuildTokenCompletedItem.PID) from BuildTokenCompletedItem where BuildTokenCompletedItem.BuildTokenPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_BuildTokenPID_where) . "' and BuildTokenCompletedItem.ProjectPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_ProjectPID_where) . "' and BuildTokenCompletedItem.ProjectSourceOutputPID = '" . $mtooldb->real_escape_string($param_BuildTokenCompletedItem_ProjectSourceOutputPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new BuildTokenCompletedItemData();
			$thisresult->PID = $thisline[0];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertBuildTokenCompletedItem($BuildTokenCompletedItemObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertBuildTokenCompletedItem ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertBuildTokenCompletedItem ==
		
		$last_sql_command_for_mtooldb = "insert into BuildTokenCompletedItem (ProjectPID, BuildTokenPID, ProjectSourceOutputPID, BuildTargetType, EachTargetPID, option_anyOutput, option_createdBaseClasses) values('" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->BuildTokenPID) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->ProjectSourceOutputPID) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->BuildTargetType) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->EachTargetPID) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->option_anyOutput) . "', '" . $mtooldb->real_escape_string($BuildTokenCompletedItemObj->option_createdBaseClasses) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteBuildTokenCompletedItem()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteBuildTokenCompletedItem ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteBuildTokenCompletedItem ==
		
		$last_sql_command_for_mtooldb = "delete from BuildTokenCompletedItem where BuildTokenCompletedItem.BuildTokenPID not in (select PID from BuildToken)";
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