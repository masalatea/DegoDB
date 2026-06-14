<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dbtableDBAccess
{
	public function __construct() {
	}
	
	public function GetdbtableList($param_dbtable_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdbtableList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdbtableList ==
		
		$last_sql_command_for_mtooldb = "select dbtable.ProjectPID, dbtable.PID, dbtable.name from dbtable where dbtable.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtable_ProjectPID_where) . "' order by dbtable.name";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dbtableData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdbtable($param_dbtable_PID_where, $param_dbtable_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdbtable ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdbtable ==
		
		$last_sql_command_for_mtooldb = "select dbtable.ProjectPID, dbtable.PID, dbtable.name from dbtable where dbtable.PID = '" . $mtooldb->real_escape_string($param_dbtable_PID_where) . "' and dbtable.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtable_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dbtableData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function GetdbtableByName($param_dbtable_ProjectPID_where, $param_dbtable_name_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdbtableByName ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdbtableByName ==
		
		$last_sql_command_for_mtooldb = "select dbtable.ProjectPID, dbtable.PID, dbtable.name from dbtable where dbtable.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtable_ProjectPID_where) . "' and dbtable.name = '" . $mtooldb->real_escape_string($param_dbtable_name_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dbtableData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdbtable($dbtableObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdbtable ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdbtable ==
		
		$last_sql_command_for_mtooldb = "insert into dbtable (ProjectPID, name) values('" . $mtooldb->real_escape_string($dbtableObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dbtableObj->name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updatedbtable($dbtableObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updatedbtable ==
		// == END OF EDITABLE AREA FOR FUNCTION Updatedbtable ==
		
		$last_sql_command_for_mtooldb = "update dbtable SET name = '" . $mtooldb->real_escape_string($dbtableObj->name) . "' where dbtable.PID = '" . $mtooldb->real_escape_string($dbtableObj->PID) . "' and dbtable.ProjectPID = '" . $mtooldb->real_escape_string($dbtableObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedbtable($param_dbtable_PID_where, $param_dbtable_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedbtable ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedbtable ==
		
		$last_sql_command_for_mtooldb = "delete from dbtable where dbtable.PID = '" . $mtooldb->real_escape_string($param_dbtable_PID_where) . "' and dbtable.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtable_ProjectPID_where) . "'";
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