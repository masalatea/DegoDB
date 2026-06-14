<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectUser.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectUser.php` and extend `ProjectUserDataBase` for project-specific customizations.

    class ProjectUserData extends ProjectUserDataBase
    {
    }
}
function GetProjectUserSerurityCaption($securitytype)
{
	switch($securitytype)
	{
		case ProjectUserSerurityEnum::$DBTOOLREAD:
			return "DB Tool: Read";
		case ProjectUserSerurityEnum::$DBTOOLWRITE:
			return "DB Tool: Write";
		case ProjectUserSerurityEnum::$HTMLREAD:
			return "Html: Read";
		case ProjectUserSerurityEnum::$HTMLWRITE:
			return "Html: Write";
		case ProjectUserSerurityEnum::$TESTTOOLREAD:
			return "Test Tool: Read";
		case ProjectUserSerurityEnum::$TESTTOOLWRITE:
			return "Test Tool: Write";
		case ProjectUserSerurityEnum::$SPECTOOLREAD:
			return "Spec Too: Read";
		case ProjectUserSerurityEnum::$SPECTOOLWRITE:
			return "Spec Too: Write";
		case ProjectUserSerurityEnum::$REQREAD:
			return "Requirement: Read";
		case ProjectUserSerurityEnum::$REQWRITE:
			return "Requirement: Write";
		case ProjectUserSerurityEnum::$CHATREAD:
			return "Chat: Read";
		case ProjectUserSerurityEnum::$CHATWRITE:
			return "Chat: Write";
		case ProjectUserSerurityEnum::$MINUTESREAD:
			return "Minutes: Read";
		case ProjectUserSerurityEnum::$MINUTESWRITE:
			return "Minutes: Write";
		case ProjectUserSerurityEnum::$UPLOADREAD:
			return "Upload: Read";
		case ProjectUserSerurityEnum::$UPLOADWRITE:
			return "Upload: Write";
	}
	return $securitytype;
}
function GetCategoryOfProjectUserSerurityCaption($securitytype)
{
	switch($securitytype)
	{
		case ProjectUserSerurityEnum::$DBTOOLREAD:
		case ProjectUserSerurityEnum::$DBTOOLWRITE:
			return "DB";
		case ProjectUserSerurityEnum::$HTMLREAD:
		case ProjectUserSerurityEnum::$HTMLWRITE:
			return "Html";
		case ProjectUserSerurityEnum::$TESTTOOLREAD:
		case ProjectUserSerurityEnum::$TESTTOOLWRITE:
			return "Test";
		case ProjectUserSerurityEnum::$SPECTOOLREAD:
		case ProjectUserSerurityEnum::$SPECTOOLWRITE:
			return "Spec";
		case ProjectUserSerurityEnum::$REQREAD:
		case ProjectUserSerurityEnum::$REQWRITE:
			return "Req.";
		case ProjectUserSerurityEnum::$CHATREAD:
		case ProjectUserSerurityEnum::$CHATWRITE:
			return "Chat";
		case ProjectUserSerurityEnum::$MINUTESREAD:
		case ProjectUserSerurityEnum::$MINUTESWRITE:
			return "Minutes";
		case ProjectUserSerurityEnum::$UPLOADREAD:
		case ProjectUserSerurityEnum::$UPLOADWRITE:
			return "Upload";
	}
	return $securitytype;
}
function GetActionTypeOfProjectUserSerurityCaption($securitytype)
{
	switch($securitytype)
	{
		case ProjectUserSerurityEnum::$DBTOOLREAD:
		case ProjectUserSerurityEnum::$HTMLREAD:
		case ProjectUserSerurityEnum::$TESTTOOLREAD:
		case ProjectUserSerurityEnum::$SPECTOOLREAD:
		case ProjectUserSerurityEnum::$REQREAD:
		case ProjectUserSerurityEnum::$CHATREAD:
		case ProjectUserSerurityEnum::$MINUTESREAD:
		case ProjectUserSerurityEnum::$UPLOADREAD:
			return "Read";
			
		case ProjectUserSerurityEnum::$DBTOOLWRITE:
		case ProjectUserSerurityEnum::$HTMLWRITE:
		case ProjectUserSerurityEnum::$TESTTOOLWRITE:
		case ProjectUserSerurityEnum::$SPECTOOLWRITE:
		case ProjectUserSerurityEnum::$REQWRITE:
		case ProjectUserSerurityEnum::$CHATWRITE:
		case ProjectUserSerurityEnum::$MINUTESWRITE:
		case ProjectUserSerurityEnum::$UPLOADWRITE:
			return "Write";
	}
	return $securitytype;
}
function GetAllSecurityTypeListOfProjectUser()
{
	return array(
		ProjectUserSerurityEnum::$CHATREAD,
		ProjectUserSerurityEnum::$CHATWRITE,
		ProjectUserSerurityEnum::$REQREAD,
		ProjectUserSerurityEnum::$REQWRITE,
		ProjectUserSerurityEnum::$SPECTOOLREAD,
		ProjectUserSerurityEnum::$SPECTOOLWRITE,
		ProjectUserSerurityEnum::$DBTOOLREAD,
		ProjectUserSerurityEnum::$DBTOOLWRITE,
		ProjectUserSerurityEnum::$HTMLREAD,
		ProjectUserSerurityEnum::$HTMLWRITE,
		ProjectUserSerurityEnum::$TESTTOOLREAD,
		ProjectUserSerurityEnum::$TESTTOOLWRITE,
		ProjectUserSerurityEnum::$MINUTESREAD,
		ProjectUserSerurityEnum::$MINUTESWRITE,
		ProjectUserSerurityEnum::$UPLOADREAD,
		ProjectUserSerurityEnum::$UPLOADWRITE
	);
	// この順番で表に出力されることがあるので注意
}


?>
