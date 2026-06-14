<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DBConnectionData
{
	public $PID;
	public $SettingGroupPID;
	public $ServerPID;
	public $DBServerType;
	public $DBName;
	public $ObjectNameForPHP;
	public $ServerLocalServerName;
	public $ServerIP;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GetDBConnectionDBServerTypeCaption($value)
{
	switch($value)
	{
		case DBConnectionDBServerTypeEnum::$DEFAULT:
			return "Default";
		case DBConnectionDBServerTypeEnum::$MYSQL:
			return "MySQL";
	}
	return $value;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class DBConnectionDBServerTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $DEFAULT = "";
	static $MYSQL = "MySQL";
}

?>