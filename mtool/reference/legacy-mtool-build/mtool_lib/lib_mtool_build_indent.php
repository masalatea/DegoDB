<?PHP

// =================================================================

function GetMtoolDefaultIndentForSource($ProjectSourceOutput)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			return "\t";
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
	}
	return "    ";
}

// =================================================================

?>
