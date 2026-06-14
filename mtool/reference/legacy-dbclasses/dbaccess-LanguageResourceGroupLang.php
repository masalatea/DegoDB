<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceGroupLangDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceGroupLangList($param_LanguageResourceGroupLang_ProjectPID_where, $param_LanguageResourceGroupLang_LanguageResourceGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupLangList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceGroupLangList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceLang.LangForGoogle, LanguageResourceGroupLang.PID, LanguageResourceGroupLang.ProjectPID, LanguageResourceGroupLang.LanguageResourceGroupPID, LanguageResourceGroupLang.LanguageResourceLangPID, LanguageResourceLang.Caption, LanguageResourceLang.IsDefault, LanguageResourceLang.LangForCS, LanguageResourceLang.LangForAndroid, LanguageResourceLang.LangForiOS, LanguageResourceLang.TemplateKey from LanguageResourceLang join LanguageResourceGroupLang where LanguageResourceGroupLang.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupLang_ProjectPID_where) . "' and LanguageResourceGroupLang.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResourceGroupLang_LanguageResourceGroupPID_where) . "' and LanguageResourceGroupLang.LanguageResourceLangPID = LanguageResourceLang.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceGroupLangData();
			$thisresult->LanguageResourceLangLangForGoogle = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ProjectPID = $thisline[2];
			$thisresult->LanguageResourceGroupPID = $thisline[3];
			$thisresult->LanguageResourceLangPID = $thisline[4];
			$thisresult->LanguageResourceLangCaption = $thisline[5];
			$thisresult->LanguageResourceLangIsDefault = $thisline[6];
			$thisresult->LanguageResourceLangLangForCS = $thisline[7];
			$thisresult->LanguageResourceLangLangForAndroid = $thisline[8];
			$thisresult->LanguageResourceLangLangForiOS = $thisline[9];
			$thisresult->LanguageResourceLangTemplateKey = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertLanguageResourceGroupLang($LanguageResourceGroupLangObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroupLang ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceGroupLang ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResourceGroupLang (ProjectPID, LanguageResourceGroupPID, LanguageResourceLangPID) values('" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->LanguageResourceGroupPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->LanguageResourceLangPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLanguageResourceGroupLang($LanguageResourceGroupLangObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroupLang ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLanguageResourceGroupLang ==
		
		$last_sql_command_for_mtooldb = "delete from LanguageResourceGroupLang where LanguageResourceGroupLang.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->ProjectPID) . "' and LanguageResourceGroupLang.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->LanguageResourceGroupPID) . "' and LanguageResourceGroupLang.LanguageResourceLangPID = '" . $mtooldb->real_escape_string($LanguageResourceGroupLangObj->LanguageResourceLangPID) . "'";
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