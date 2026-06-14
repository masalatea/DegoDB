<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceCaptionDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceCaptionList($param_LanguageResourceCaption_ProjectPID_where, $param_LanguageResourceCaption_LanguageResourcePID_where, $param_LanguageResourceCaption_LanguageResourceGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceCaptionList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceCaptionList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceCaption.PID, LanguageResourceCaption.ProjectPID, LanguageResourceCaption.LanguageResourcePID, LanguageResourceCaption.LanguageResourceGroupPID, LanguageResourceCaption.LanguageResourceLangPID, LanguageResourceCaption.Caption, LanguageResourceCaption.CaptionAutoTranslated, LanguageResourceLang.TemplateKey from LanguageResourceCaption join LanguageResourceLang where LanguageResourceCaption.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_ProjectPID_where) . "' and LanguageResourceCaption.LanguageResourcePID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_LanguageResourcePID_where) . "' and LanguageResourceCaption.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_LanguageResourceGroupPID_where) . "' and LanguageResourceCaption.LanguageResourceLangPID = LanguageResourceLang.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceCaptionData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourcePID = $thisline[2];
			$thisresult->LanguageResourceGroupPID = $thisline[3];
			$thisresult->LanguageResourceLangPID = $thisline[4];
			$thisresult->Caption = $thisline[5];
			$thisresult->CaptionAutoTranslated = $thisline[6];
			$thisresult->LanguageResourceLangTemplateKey = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResourceCaption($param_LanguageResourceCaption_ProjectPID_where, $param_LanguageResourceCaption_LanguageResourcePID_where, $param_LanguageResourceCaption_LanguageResourceGroupPID_where, $param_LanguageResourceCaption_LanguageResourceLangPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceCaption ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceCaption ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceCaption.PID, LanguageResourceCaption.ProjectPID, LanguageResourceCaption.LanguageResourcePID, LanguageResourceCaption.LanguageResourceGroupPID, LanguageResourceCaption.LanguageResourceLangPID, LanguageResourceCaption.Caption, LanguageResourceCaption.CaptionAutoTranslated, LanguageResourceLang.TemplateKey from LanguageResourceCaption join LanguageResourceLang where LanguageResourceCaption.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_ProjectPID_where) . "' and LanguageResourceCaption.LanguageResourcePID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_LanguageResourcePID_where) . "' and LanguageResourceCaption.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_LanguageResourceGroupPID_where) . "' and LanguageResourceCaption.LanguageResourceLangPID = '" . $mtooldb->real_escape_string($param_LanguageResourceCaption_LanguageResourceLangPID_where) . "' and LanguageResourceCaption.LanguageResourceLangPID = LanguageResourceLang.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceCaptionData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourcePID = $thisline[2];
			$thisresult->LanguageResourceGroupPID = $thisline[3];
			$thisresult->LanguageResourceLangPID = $thisline[4];
			$thisresult->Caption = $thisline[5];
			$thisresult->CaptionAutoTranslated = $thisline[6];
			$thisresult->LanguageResourceLangTemplateKey = $thisline[7];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLanguageResourceCaption($LanguageResourceCaptionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceCaption ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResourceCaption ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResourceCaption (ProjectPID, LanguageResourcePID, LanguageResourceGroupPID, LanguageResourceLangPID, Caption, CaptionAutoTranslated) values('" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourcePID) . "', '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourceGroupPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourceLangPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->Caption) . "', '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->CaptionAutoTranslated) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLanguageResourceCaption($LanguageResourceCaptionObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceCaption ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLanguageResourceCaption ==
		
		$last_sql_command_for_mtooldb = "update LanguageResourceCaption SET Caption = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->Caption) . "', CaptionAutoTranslated = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->CaptionAutoTranslated) . "' where LanguageResourceCaption.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->ProjectPID) . "' and LanguageResourceCaption.LanguageResourcePID = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourcePID) . "' and LanguageResourceCaption.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourceGroupPID) . "' and LanguageResourceCaption.LanguageResourceLangPID = '" . $mtooldb->real_escape_string($LanguageResourceCaptionObj->LanguageResourceLangPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	public function GetCaptionBasedOnResouceKey($ProjectPID, $LanguageResourceLangPID, $ResourceKey)
	{
		$DALanguageResource = new LanguageResourceDBAccess();
		$LanguageResource = $DALanguageResource->GetLanguageResourceByKeyName($ProjectPID, $ResourceKey);
		if ($LanguageResource) {
			$LanguageResourceCaption = $this->GetLanguageResourceCaption($LanguageResource->ProjectPID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $LanguageResourceLangPID);
			if ($LanguageResourceCaption) {
				return $LanguageResourceCaption->Caption;
			}
		}
		return "";
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==


// == END OF EDITABLE AREA FOR BOTTOM ==

?>