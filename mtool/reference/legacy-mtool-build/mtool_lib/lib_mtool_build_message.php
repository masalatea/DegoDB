<?PHP

$mtool_build_message_list = NULL;
$mtool_output_debug_message = false;
$mtool_output_debug_message_token_PID = -1;

function InitializeMtoolBuildResultMessage()
{
	global $mtool_build_message_list;
	
	$mtool_build_message_list = array();
}
function SetMtoolBuildOutputMessageMode($output_debug_message)
{
	global $mtool_output_debug_message;
	
	$mtool_output_debug_message = $output_debug_message;
}
function SetMtoolBuildOutputMessageTokenPID($BuildTokenPID)
{
	global $mtool_output_debug_message_token_PID;
	
	$mtool_output_debug_message_token_PID = $BuildTokenPID;
}

function AddMtoolGeneralBuildMessage($mesage)
{
	AddMtoolBuildMessage(BuildLogMessageTypeEnum::$DEFAULT, $mesage);
}
function AddMtoolDebugBuildMessage($mesage)
{
	AddMtoolBuildMessage(BuildLogMessageTypeEnum::$DEBUG, $mesage);
}
function AddMtoolErrorBuildMessage($mesage)
{
	AddMtoolBuildMessage(BuildLogMessageTypeEnum::$ERROR, $mesage);
}
function AddMtoolBuildMessage($message_type, $mesage)
{
	global $mtool_build_message_list;
	global $mtool_output_debug_message_token_PID;
	
	if (is_array($mtool_build_message_list)) {
		$new_build_message = new BuildLogData();
		$new_build_message->BuildTokenPID = $mtool_output_debug_message_token_PID;
		$new_build_message->MessageType = $message_type;
		$new_build_message->Message = $mesage;
		
		array_push($mtool_build_message_list, $new_build_message);
		
		$DABuildLog = new BuildLogDBAccess();
		if (!$DABuildLog->InsertBuildLog($new_build_message)) {
			// Failed
			die("Fatal Error! Failed to save build log");
		}
		
	} else {
		die("Something Strange. Build Message List is NULL.");
	}
}
function PrintOutMtoolBuildResultMessage()
{
	global $mtool_build_message_list;
	global $mtool_output_debug_message;
	
	for($i = 0 ; $i < count($mtool_build_message_list) ; $i++) {
		$mtool_build_message = $mtool_build_message_list[$i];
		
		if (($mtool_build_message->MessageType == BuildLogMessageTypeEnum::$DEFAULT) ||
		    ($mtool_output_debug_message && $mtool_build_message->MessageType == BuildLogMessageTypeEnum::$DEBUG) ||
			($mtool_build_message->MessageType == BuildLogMessageTypeEnum::$ERROR)
		   )
		{
			if ($mtool_build_message->MessageType == BuildLogMessageTypeEnum::$ERROR) {
				?>
                <font color="red">
                <?php
			}
			print $mtool_build_message->Message;
			
			if ($mtool_build_message->MessageType == BuildLogMessageTypeEnum::$ERROR) {
				?>
                </font>
                <?php
			}
			print "\n";
		}
	}
}

?>
