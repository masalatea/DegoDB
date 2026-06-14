<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dbtablecolumnsDBAccess
{
	public function __construct() {
	}
	
	public function GetdbtablecolumnsList($param_dbtablecolumns_ProjectPID_where, $param_dbtablecolumns_dbtablePID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetdbtablecolumnsList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetdbtablecolumnsList ==
		
		$last_sql_command_for_mtooldb = "select dbtablecolumns.ProjectPID, dbtablecolumns.dbtablePID, dbtablecolumns.PID, dbtablecolumns.name, dbtablecolumns.datatype, dbtablecolumns.IsNull, dbtablecolumns.IsKey, dbtablecolumns.IsDefault, dbtablecolumns.Extra, dbtablecolumns.ColumnListOrder, dbtablecolumns.memo from dbtablecolumns where dbtablecolumns.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_ProjectPID_where) . "' and dbtablecolumns.dbtablePID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_dbtablePID_where) . "' order by dbtablecolumns.ColumnListOrder,dbtablecolumns.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dbtablecolumnsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->dbtablePID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->datatype = $thisline[4];
			$thisresult->IsNull = $thisline[5];
			$thisresult->IsKey = $thisline[6];
			$thisresult->IsDefault = $thisline[7];
			$thisresult->Extra = $thisline[8];
			$thisresult->ColumnListOrder = $thisline[9];
			$thisresult->memo = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getdbtablecolumns($param_dbtablecolumns_PID_where, $param_dbtablecolumns_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getdbtablecolumns ==
		// == END OF EDITABLE AREA FOR FUNCTION Getdbtablecolumns ==
		
		$last_sql_command_for_mtooldb = "select dbtablecolumns.ProjectPID, dbtablecolumns.dbtablePID, dbtablecolumns.PID, dbtablecolumns.name, dbtablecolumns.datatype, dbtablecolumns.IsNull, dbtablecolumns.IsKey, dbtablecolumns.IsDefault, dbtablecolumns.Extra, dbtablecolumns.ColumnListOrder, dbtablecolumns.memo from dbtablecolumns where dbtablecolumns.PID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_PID_where) . "' and dbtablecolumns.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new dbtablecolumnsData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->dbtablePID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->datatype = $thisline[4];
			$thisresult->IsNull = $thisline[5];
			$thisresult->IsKey = $thisline[6];
			$thisresult->IsDefault = $thisline[7];
			$thisresult->Extra = $thisline[8];
			$thisresult->ColumnListOrder = $thisline[9];
			$thisresult->memo = $thisline[10];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertdbtablecolumns($dbtablecolumnsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertdbtablecolumns ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertdbtablecolumns ==
		
		$last_sql_command_for_mtooldb = "insert into dbtablecolumns (ProjectPID, dbtablePID, name, datatype, IsNull, IsKey, IsDefault, Extra, memo) values('" . $mtooldb->real_escape_string($dbtablecolumnsObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->dbtablePID) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->name) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->datatype) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsNull) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsKey) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsDefault) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->Extra) . "', '" . $mtooldb->real_escape_string($dbtablecolumnsObj->memo) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedbtablecolumnsExcludeColumnListOrder($dbtablecolumnsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedbtablecolumnsExcludeColumnListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedbtablecolumnsExcludeColumnListOrder ==
		
		$last_sql_command_for_mtooldb = "update dbtablecolumns SET name = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->name) . "', datatype = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->datatype) . "', IsNull = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsNull) . "', IsKey = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsKey) . "', IsDefault = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsDefault) . "', Extra = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->Extra) . "', memo = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->memo) . "' where dbtablecolumns.PID = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->PID) . "' and dbtablecolumns.ProjectPID = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deletedbtablecolumns($param_dbtablecolumns_PID_where, $param_dbtablecolumns_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deletedbtablecolumns ==
		// == END OF EDITABLE AREA FOR FUNCTION Deletedbtablecolumns ==
		
		$last_sql_command_for_mtooldb = "delete from dbtablecolumns where dbtablecolumns.PID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_PID_where) . "' and dbtablecolumns.ProjectPID = '" . $mtooldb->real_escape_string($param_dbtablecolumns_ProjectPID_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatedbtablecolumnsIncludeColumnListOrder($dbtablecolumnsObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatedbtablecolumnsIncludeColumnListOrder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatedbtablecolumnsIncludeColumnListOrder ==
		
		$last_sql_command_for_mtooldb = "update dbtablecolumns SET name = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->name) . "', datatype = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->datatype) . "', IsNull = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsNull) . "', IsKey = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsKey) . "', IsDefault = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->IsDefault) . "', Extra = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->Extra) . "', ColumnListOrder = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->ColumnListOrder) . "', memo = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->memo) . "' where dbtablecolumns.PID = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->PID) . "' and dbtablecolumns.ProjectPID = '" . $mtooldb->real_escape_string($dbtablecolumnsObj->ProjectPID) . "'";
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

function UpdatedbtablecolumnsColumnListOrderSupposedToBe($dbtablecolumnList)
{
	for($i = 0 ; $i < count($dbtablecolumnList); $i++) {
		$dbtablecolumnList[$i]->ColumnListOrderSupposedToBe = ($i + 1);
	}
}

// == END OF EDITABLE AREA FOR BOTTOM ==

?>