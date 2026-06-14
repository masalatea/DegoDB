<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceLangDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceLangList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceLangList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceLangList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceLang.PID, LanguageResourceLang.FilenameSuffix, LanguageResourceLang.TemplateKey, LanguageResourceLang.IsDefault, LanguageResourceLang.Caption, LanguageResourceLang.LangForCS, LanguageResourceLang.LangForAndroid, LanguageResourceLang.LangForiOS, LanguageResourceLang.LangForGoogle from LanguageResourceLang order by LanguageResourceLang.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceLangData();
			$thisresult->PID = $thisline[0];
			$thisresult->FilenameSuffix = $thisline[1];
			$thisresult->TemplateKey = $thisline[2];
			$thisresult->IsDefault = $thisline[3];
			$thisresult->Caption = $thisline[4];
			$thisresult->LangForCS = $thisline[5];
			$thisresult->LangForAndroid = $thisline[6];
			$thisresult->LangForiOS = $thisline[7];
			$thisresult->LangForGoogle = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDefault()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDefault ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDefault ==
		
		$last_sql_command_for_mtooldb = "select LanguageResourceLang.PID, LanguageResourceLang.FilenameSuffix, LanguageResourceLang.TemplateKey, LanguageResourceLang.IsDefault, LanguageResourceLang.Caption, LanguageResourceLang.LangForCS, LanguageResourceLang.LangForAndroid, LanguageResourceLang.LangForiOS, LanguageResourceLang.LangForGoogle from LanguageResourceLang where LanguageResourceLang.IsDefault = '" . $mtooldb->real_escape_string("1") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceLangData();
			$thisresult->PID = $thisline[0];
			$thisresult->FilenameSuffix = $thisline[1];
			$thisresult->TemplateKey = $thisline[2];
			$thisresult->IsDefault = $thisline[3];
			$thisresult->Caption = $thisline[4];
			$thisresult->LangForCS = $thisline[5];
			$thisresult->LangForAndroid = $thisline[6];
			$thisresult->LangForiOS = $thisline[7];
			$thisresult->LangForGoogle = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>