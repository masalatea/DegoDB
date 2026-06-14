<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dataclassDBAccess
{
	public function __construct() {
	}
	
	public function GetdataclassList($param_dataclass_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdataclassList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdataclassList ==
		
		$last_sql_command_for_mtooldb = "select dataclass.ProjectPID, dataclass.PID, dataclass.name, dataclass.StoreBasePath, dataclass.IsAutoload, dataclass.InheritParentDataClassName, dataclass.LastModifiedDT from dataclass where dataclass.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclass_ProjectPID_where) . "' order by dataclass.name";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dataclassData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->InheritParentDataClassName = $thisline[5];
			$thisresult->LastModifiedDT = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdataclass($param_dataclass_PID_where, $param_dataclass_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdataclass ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdataclass ==
		
		$last_sql_command_for_mtooldb = "select dataclass.ProjectPID, dataclass.PID, dataclass.name, dataclass.StoreBasePath, dataclass.IsAutoload, dataclass.InheritParentDataClassName, dataclass.LastModifiedDT from dataclass where dataclass.PID = '" . $mtooldb->real_escape_string($param_dataclass_PID_where) . "' and dataclass.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclass_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dataclassData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->InheritParentDataClassName = $thisline[5];
			$thisresult->LastModifiedDT = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GetdataclassByName($param_dataclass_ProjectPID_where, $param_dataclass_name_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdataclassByName ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdataclassByName ==
		
		$last_sql_command_for_mtooldb = "select dataclass.ProjectPID, dataclass.PID, dataclass.name, dataclass.StoreBasePath, dataclass.IsAutoload, dataclass.InheritParentDataClassName, dataclass.LastModifiedDT from dataclass where dataclass.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclass_ProjectPID_where) . "' and dataclass.name = '" . $mtooldb->real_escape_string($param_dataclass_name_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dataclassData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->InheritParentDataClassName = $thisline[5];
			$thisresult->LastModifiedDT = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdataclass($dataclassObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdataclass ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdataclass ==
		
		$last_sql_command_for_mtooldb = "insert into dataclass (ProjectPID, name, StoreBasePath, IsAutoload, InheritParentDataClassName) values('" . $mtooldb->real_escape_string($dataclassObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dataclassObj->name) . "', '" . $mtooldb->real_escape_string($dataclassObj->StoreBasePath) . "', '" . $mtooldb->real_escape_string($dataclassObj->IsAutoload) . "', '" . $mtooldb->real_escape_string($dataclassObj->InheritParentDataClassName) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedataclass($dataclassObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedataclass ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedataclass ==
		
		$last_sql_command_for_mtooldb = "update dataclass SET name = '" . $mtooldb->real_escape_string($dataclassObj->name) . "', StoreBasePath = '" . $mtooldb->real_escape_string($dataclassObj->StoreBasePath) . "', IsAutoload = '" . $mtooldb->real_escape_string($dataclassObj->IsAutoload) . "', InheritParentDataClassName = '" . $mtooldb->real_escape_string($dataclassObj->InheritParentDataClassName) . "', LastModifiedDT = now() where dataclass.PID = '" . $mtooldb->real_escape_string($dataclassObj->PID) . "' and dataclass.ProjectPID = '" . $mtooldb->real_escape_string($dataclassObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLastModifiedDT($param_dataclass_PID_where, $param_dataclass_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		
		$last_sql_command_for_mtooldb = "update dataclass SET LastModifiedDT = now() where dataclass.PID = '" . $mtooldb->real_escape_string($param_dataclass_PID_where) . "' and dataclass.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclass_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedataclass($param_dataclass_PID_where, $param_dataclass_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedataclass ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedataclass ==
		
		$last_sql_command_for_mtooldb = "delete from dataclass where dataclass.PID = '" . $mtooldb->real_escape_string($param_dataclass_PID_where) . "' and dataclass.ProjectPID = '" . $mtooldb->real_escape_string($param_dataclass_ProjectPID_where) . "'";
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