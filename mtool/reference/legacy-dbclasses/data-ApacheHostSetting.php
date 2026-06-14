<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ApacheHostSettingData
{
	public $PID;
	public $ApacheSettingPID;
	public $ApacheHostSettingTemplatePID;
	public $CategoryName;
	public $VirtualHostName;
	public $DocumentRootSuffix;
	public $Email;
	public $MonitorLog;
	public $ApacheHostSettingTemplatename;
	public $ServerLocalServerName;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	function InitializeByTemplate($ApacheHostSettingTemplate)
	{
		$filename = $ApacheHostSettingTemplate->FilenameFormat;
		$filename = preg_replace("/__HOST__/", $this->VirtualHostName, $filename);
		$this->Filename = $filename;
		
		$accesslogfilename = $ApacheHostSettingTemplate->AccessLogFilenameFormat;
		$accesslogfilename = preg_replace("/__HOST__/", $this->VirtualHostName, $accesslogfilename);
		$this->AccessLogFilename = $accesslogfilename;
		
		$errorlogfilename = $ApacheHostSettingTemplate->ErrorLogFilenameFormat;
		$errorlogfilename = preg_replace("/__HOST__/", $this->VirtualHostName, $errorlogfilename);
		$this->ErrorLogFilename = $errorlogfilename;
		
		$document_root_suffix = $this->VirtualHostName;	// Default
		if ($this->DocumentRootSuffix != "") {
			$document_root_suffix = $this->DocumentRootSuffix;
		}
		
		$lines = $ApacheHostSettingTemplate->Template;
		$lines = preg_replace("/__EMAIL__/", $this->Email, $lines);
		$lines = preg_replace("/__HOST__/", $this->VirtualHostName, $lines);
		$lines = preg_replace("/__DOCUMENT_ROOT_SUFFIX__/", $document_root_suffix, $lines);
		$lines = preg_replace("/__ACCESS_LOG_FILENAME__/", $accesslogfilename, $lines);
		$lines = preg_replace("/__ERROR_LOG_FILENAME__/", $errorlogfilename, $lines);
		$this->Lines = $lines;
	}
	public $Filename;
	public $AccessLogFilename;
	public $ErrorLogFilename;
	public $Lines;
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>