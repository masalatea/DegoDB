<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class MySQLShowColumnDBAccess
{
	public function __construct() {
	}
	
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	private $MySQLiObj;
	public function Initialize($param_mysqliObj)
	{
		$this->MySQLiObj = $param_mysqliObj;
	}

	public function GetTables()
	{
		$result = array();
		
		$ret = $this->MySQLiObj->query("show tables");
		while($thisline=$ret->fetch_row()) {
			$thisresult = new MySQLShowColumnData();
			$tablename = $thisline[0];
			array_push($result, $tablename);
		}
		return $result;
	}
	
	public function GetTableColumns($tablename)
	{
		$result = array();
		
		// 注: テーブル名をシングルクオートで囲むとエラーになるので囲まないこと。
		$ret = $this->MySQLiObj->query("show columns from " . $this->MySQLiObj->real_escape_string($tablename));
		while($thisline=$ret->fetch_row()) {
			$thisresult = new MySQLShowColumnData;
			$thisresult->Field = $thisline[0];
			$thisresult->Type = $thisline[1];
			$thisresult->IsNull = $thisline[2];
			$thisresult->IsKey = $thisline[3];
			$thisresult->IsDefault = $thisline[4];
			$thisresult->Extra = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>