<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplateParameterDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlTemplateParameter($param_htmlTemplateParameter_PID_where, $param_htmlTemplateParameter_htmlTemplatePID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplateParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplateParameter ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplateParameter.htmlTemplatePID, htmlTemplateParameter.PID, htmlTemplateParameter.ParameterName, htmlTemplateParameter.TargetValueType, htmlTemplateParameter.TargetVariableOrClassObject, htmlTemplateParameter.TargetPropertyOfClassObject, htmlTemplateParameter.AnotherTemplatePID, htmlTemplateParameter.TrimLastSpace, htmlTemplateParameter.TrimLastReturn, htmlTemplateParameter.DataType from htmlTemplateParameter where htmlTemplateParameter.PID = '" . $mtooldb->real_escape_string($param_htmlTemplateParameter_PID_where) . "' and htmlTemplateParameter.htmlTemplatePID = '" . $mtooldb->real_escape_string($param_htmlTemplateParameter_htmlTemplatePID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplateParameterData();
			$thisresult->htmlTemplatePID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ParameterName = $thisline[2];
			$thisresult->TargetValueType = $thisline[3];
			$thisresult->TargetVariableOrClassObject = $thisline[4];
			$thisresult->TargetPropertyOfClassObject = $thisline[5];
			$thisresult->AnotherTemplatePID = $thisline[6];
			$thisresult->TrimLastSpace = $thisline[7];
			$thisresult->TrimLastReturn = $thisline[8];
			$thisresult->DataType = $thisline[9];
			return $thisresult;
		}
		return NULL;
	}
	public function InserthtmlTemplateParameter($htmlTemplateParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InserthtmlTemplateParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION InserthtmlTemplateParameter ==
		
		$last_sql_command_for_mtooldb = "insert into htmlTemplateParameter (htmlTemplatePID, ParameterName, TargetValueType, TargetVariableOrClassObject, TargetPropertyOfClassObject, AnotherTemplatePID, TrimLastSpace, TrimLastReturn, DataType) values('" . $mtooldb->real_escape_string($htmlTemplateParameterObj->htmlTemplatePID) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->ParameterName) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetValueType) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetVariableOrClassObject) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetPropertyOfClassObject) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->AnotherTemplatePID) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TrimLastSpace) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TrimLastReturn) . "', '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->DataType) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatehtmlTemplateParameter($htmlTemplateParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatehtmlTemplateParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatehtmlTemplateParameter ==
		
		$last_sql_command_for_mtooldb = "update htmlTemplateParameter SET ParameterName = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->ParameterName) . "', TargetValueType = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetValueType) . "', TargetVariableOrClassObject = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetVariableOrClassObject) . "', TargetPropertyOfClassObject = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TargetPropertyOfClassObject) . "', AnotherTemplatePID = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->AnotherTemplatePID) . "', TrimLastSpace = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TrimLastSpace) . "', TrimLastReturn = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->TrimLastReturn) . "', DataType = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->DataType) . "' where htmlTemplateParameter.PID = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->PID) . "' and htmlTemplateParameter.htmlTemplatePID = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->htmlTemplatePID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletehtmlTemplateParameter($htmlTemplateParameterObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletehtmlTemplateParameter ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletehtmlTemplateParameter ==
		
		$last_sql_command_for_mtooldb = "delete from htmlTemplateParameter where htmlTemplateParameter.PID = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->PID) . "' and htmlTemplateParameter.htmlTemplatePID = '" . $mtooldb->real_escape_string($htmlTemplateParameterObj->htmlTemplatePID) . "'";
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