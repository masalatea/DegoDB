<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dataclassfieldsDBAccess
{
	public function __construct() {
	}
	
	public function GetdataclassfieldsList($param_dataclassfields_ProjectPID_where, $param_dataclassfields_dataclassPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdataclassfieldsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdataclassfieldsList ==
		
		$last_sql_command_for_mtooldb = "select dataclassfields.ProjectPID, dataclassfields.dataclassPID, dataclassfields.PID, dataclassfields.name, dataclassfields.datatype, dataclassfields.FieldListOrder, dataclassfields.RefDataClassName, dataclassfields.RefDataClassFieldName from dataclassfields where dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclassfields_ProjectPID_where) . "' and dataclassfields.dataclassPID = '" . $mtooldb->real_escape_string($param_dataclassfields_dataclassPID_where) . "' order by dataclassfields.FieldListOrder,dataclassfields.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dataclassfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->dataclassPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->datatype = $thisline[4];
			$thisresult->FieldListOrder = $thisline[5];
			$thisresult->RefDataClassName = $thisline[6];
			$thisresult->RefDataClassFieldName = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdataclassfields($param_dataclassfields_PID_where, $param_dataclassfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdataclassfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdataclassfields ==
		
		$last_sql_command_for_mtooldb = "select dataclassfields.ProjectPID, dataclassfields.dataclassPID, dataclassfields.PID, dataclassfields.name, dataclassfields.datatype, dataclassfields.FieldListOrder, dataclassfields.RefDataClassName, dataclassfields.RefDataClassFieldName from dataclassfields where dataclassfields.PID = '" . $mtooldb->real_escape_string($param_dataclassfields_PID_where) . "' and dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclassfields_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dataclassfieldsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->dataclassPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->datatype = $thisline[4];
			$thisresult->FieldListOrder = $thisline[5];
			$thisresult->RefDataClassName = $thisline[6];
			$thisresult->RefDataClassFieldName = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdataclassfields($dataclassfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdataclassfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdataclassfields ==
		
		$last_sql_command_for_mtooldb = "insert into dataclassfields (ProjectPID, dataclassPID, name, datatype, RefDataClassName, RefDataClassFieldName) values('" . $mtooldb->real_escape_string($dataclassfieldsObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dataclassfieldsObj->dataclassPID) . "', '" . $mtooldb->real_escape_string($dataclassfieldsObj->name) . "', '" . $mtooldb->real_escape_string($dataclassfieldsObj->datatype) . "', '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassName) . "', '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassFieldName) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedataclassfieldsExcludeFieldListOrder($dataclassfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsExcludeFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsExcludeFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dataclassfields SET name = '" . $mtooldb->real_escape_string($dataclassfieldsObj->name) . "', datatype = '" . $mtooldb->real_escape_string($dataclassfieldsObj->datatype) . "', RefDataClassName = '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassName) . "', RefDataClassFieldName = '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassFieldName) . "' where dataclassfields.PID = '" . $mtooldb->real_escape_string($dataclassfieldsObj->PID) . "' and dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($dataclassfieldsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedataclassfieldsIncludeFieldListOrder($dataclassfieldsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsIncludeFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsIncludeFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dataclassfields SET name = '" . $mtooldb->real_escape_string($dataclassfieldsObj->name) . "', datatype = '" . $mtooldb->real_escape_string($dataclassfieldsObj->datatype) . "', FieldListOrder = '" . $mtooldb->real_escape_string($dataclassfieldsObj->FieldListOrder) . "', RefDataClassName = '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassName) . "', RefDataClassFieldName = '" . $mtooldb->real_escape_string($dataclassfieldsObj->RefDataClassFieldName) . "' where dataclassfields.PID = '" . $mtooldb->real_escape_string($dataclassfieldsObj->PID) . "' and dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($dataclassfieldsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedataclassfieldsFieldListOrder($param_dataclassfields_FieldListOrder_update, $param_dataclassfields_PID_where, $param_dataclassfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsFieldListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedataclassfieldsFieldListOrder ==
		
		$last_sql_command_for_mtooldb = "update dataclassfields SET FieldListOrder = " . $param_dataclassfields_FieldListOrder_update . " where dataclassfields.PID = '" . $mtooldb->real_escape_string($param_dataclassfields_PID_where) . "' and dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclassfields_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedataclassfields($param_dataclassfields_PID_where, $param_dataclassfields_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedataclassfields ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedataclassfields ==
		
		$last_sql_command_for_mtooldb = "delete from dataclassfields where dataclassfields.PID = '" . $mtooldb->real_escape_string($param_dataclassfields_PID_where) . "' and dataclassfields.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclassfields_ProjectPID_where) . "'";
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