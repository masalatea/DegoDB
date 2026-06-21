<?PHP

function BuildHtmlFromTemplate($BuildToken, $project, $ProjectSourceOutput, $html, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	$htmlfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$htmlfilename = $html->name . "_include.php";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			PrintOutMtoolBuildResultMessage();
			die("Not Supported: Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	$ReplaceParameterList = array(
		array("KEY"=>"__NAME__", "VALUE"=>$html->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		);
	GetReplaceParameterListFromTemplate($BuildToken, $ReplaceParameterList, $project, $ProjectSourceOutput, $html, $html->htmlTemplatePID);
	
	$source_template = GetMtoolHtmlTemplateFile($BuildToken, $project, $ProjectSourceOutput, $html->htmlTemplateFileName);
	$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $htmlfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	if ($result->Success) {
		// OK
	} else {
		AddMtoolErrorBuildMessage(" -> Error! Failed to update");
	}
}

?>
