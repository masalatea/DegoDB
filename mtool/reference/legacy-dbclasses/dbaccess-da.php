<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class daDBAccess
{
	public function __construct() {
	}
	
	public function GetdaList($param_da_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaList ==
		
		$last_sql_command_for_mtooldb = "select da.ProjectPID, da.PID, da.name, da.StoreBasePath, da.IsAutoload, da.LastModifiedDT from da where da.ProjectPID = '" . $mtooldb->real_escape_string($param_da_ProjectPID_where) . "' order by da.name,da.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->LastModifiedDT = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getda($param_da_PID_where, $param_da_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getda ==
		// == END OF EDITABLE AREA FOR FUNCTION Getda ==
		
		$last_sql_command_for_mtooldb = "select da.ProjectPID, da.PID, da.name, da.StoreBasePath, da.IsAutoload, da.LastModifiedDT from da where da.PID = '" . $mtooldb->real_escape_string($param_da_PID_where) . "' and da.ProjectPID = '" . $mtooldb->real_escape_string($param_da_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->LastModifiedDT = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function GetdaByName($param_da_ProjectPID_where, $param_da_name_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdaByName ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdaByName ==
		
		$last_sql_command_for_mtooldb = "select da.ProjectPID, da.PID, da.name, da.StoreBasePath, da.IsAutoload, da.LastModifiedDT from da where da.ProjectPID = '" . $mtooldb->real_escape_string($param_da_ProjectPID_where) . "' and da.name = '" . $mtooldb->real_escape_string($param_da_name_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new daData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->StoreBasePath = $thisline[3];
			$thisresult->IsAutoload = $thisline[4];
			$thisresult->LastModifiedDT = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertda($daObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertda ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertda ==
		
		$last_sql_command_for_mtooldb = "insert into da (ProjectPID, name, StoreBasePath, IsAutoload) values('" . $mtooldb->real_escape_string($daObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($daObj->name) . "', '" . $mtooldb->real_escape_string($daObj->StoreBasePath) . "', '" . $mtooldb->real_escape_string($daObj->IsAutoload) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updateda($daObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updateda ==
		// == END OF EDITABLE AREA FOR FUNCTION Updateda ==
		
		$last_sql_command_for_mtooldb = "update da SET name = '" . $mtooldb->real_escape_string($daObj->name) . "', StoreBasePath = '" . $mtooldb->real_escape_string($daObj->StoreBasePath) . "', IsAutoload = '" . $mtooldb->real_escape_string($daObj->IsAutoload) . "', LastModifiedDT = now() where da.PID = '" . $mtooldb->real_escape_string($daObj->PID) . "' and da.ProjectPID = '" . $mtooldb->real_escape_string($daObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLastModifiedDT($param_da_PID_where, $param_da_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLastModifiedDT ==
		
		$last_sql_command_for_mtooldb = "update da SET LastModifiedDT = now() where da.PID = '" . $mtooldb->real_escape_string($param_da_PID_where) . "' and da.ProjectPID = '" . $mtooldb->real_escape_string($param_da_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deleteda($param_da_PID_where, $param_da_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deleteda ==
		// == END OF EDITABLE AREA FOR FUNCTION Deleteda ==
		
		$last_sql_command_for_mtooldb = "delete from da where da.PID = '" . $mtooldb->real_escape_string($param_da_PID_where) . "' and da.ProjectPID = '" . $mtooldb->real_escape_string($param_da_ProjectPID_where) . "'";
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