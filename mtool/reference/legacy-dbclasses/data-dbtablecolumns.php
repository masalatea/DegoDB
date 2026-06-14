<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class dbtablecolumnsData
{
	public $ProjectPID;
	public $dbtablePID;
	public $PID;
	public $name;
	public $datatype;
	public $IsNull;
	public $IsKey;
	public $IsDefault;
	public $Extra;
	public $ColumnListOrder;
	public $memo;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public $ColumnListOrderSupposedToBe;
	
	function IsAutoIncrement()
	{
		$ThisIsAutoIncrement = false;
		if (preg_match("/auto.*inc/i", $this->Extra)) {
			$ThisIsAutoIncrement = true;
		}
		return $ThisIsAutoIncrement;
	}
	
	function NotSupportedDataTypeForInsertOrUpdateBasedOnDBType($project)
	{
		switch($project->DBType)
		{
			case ProjectDBTypeEnum::$DEFAULT:
			case ProjectDBTypeEnum::$MYSQLONCLOUD:
				return false;
			case ProjectDBTypeEnum::$SQLSERVER:
				
				if (
				    preg_match("/timestamp/i", $this->datatype)
					) {
					return true;
				}
				break;
		}
		return false;
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>