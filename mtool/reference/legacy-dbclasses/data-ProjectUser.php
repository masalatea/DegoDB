<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectUserData
{
	public $ProjectPID;
	public $PID;
	public $username;
	public $IsOwner;
	public $dbtoolRead;
	public $dbtoolWrite;
	public $htmlRead;
	public $htmlWrite;
	public $testtoolRead;
	public $testtoolWrite;
	public $spectoolRead;
	public $spectoolWrite;
	public $ReqRead;
	public $ReqWrite;
	public $ChatRead;
	public $ChatWrite;
	public $MinutesRead;
	public $MinutesWrite;
	public $UploadRead;
	public $UploadWrite;
	public $ProjectUserInOtherProjectEmailForDropboxSharing;
	public $ProjectUserInOtherProjectProjectPID;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

class ProjectUserSerurityEnum
{
	static $DBTOOLREAD = "dbtoolRead";
	static $DBTOOLWRITE = "dbtoolWrite";
	static $HTMLREAD = "htmlRead";
	static $HTMLWRITE = "htmlWrite";
	static $TESTTOOLREAD = "testtoolRead";
	static $TESTTOOLWRITE = "testtoolWrite";
	static $SPECTOOLREAD = "spectoolRead";
	static $SPECTOOLWRITE = "spectoolWrite";
	static $REQREAD = "ReqRead";
	static $REQWRITE = "ReqWrite";
	static $CHATREAD = "ChatRead";
	static $CHATWRITE = "ChatWrite";
	static $MINUTESREAD = "MinutesRead";
	static $MINUTESWRITE = "MinutesWrite";
	static $UPLOADREAD = "UploadRead";
	static $UPLOADWRITE = "UploadWrite";
	
	// これらの値を使って上記クラスのプロパティに直接アクセスするので定義を増やす際はプロパティ名と一致させること
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

// == END OF EDITABLE AREA FOR BOTTOM ==

class ProjectUserIsOwnerEnum
{
	static $UNKNOWN = "Unknown";
	static $T = "t";
	static $F = "f";
}

?>