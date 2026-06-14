<?php

$dafunc = NULL;
$IsBlobTarget = false;

function CheckDAFuncStatus($dafuncPID, $ProjectPID)
{
	global $NoError;
	global $IsBlobTarget;
	global $dafunc;
	
	if ($NoError) {
		$DAdafunc = new dafuncDBAccess();
		$dafunc = $DAdafunc->Getdafunc($dafuncPID, $ProjectPID);
		if ($dafunc) {
			$IsBlobTarget = ($dafunc->IsBlobTarget == 1);
		} else {
			?>
			<h3><font color="red">Error! Unknown Function</font></h3>
			<?php
			$NoError = false;
		}
	}
}

$FileDataTypeCount = 0;

function IncrementFileDataTypeCount()
{
	global $FileDataTypeCount;
	$FileDataTypeCount++;
}
function CheckAndDisplayFileDataTypeCountWarning()
{
	global $FileDataTypeCount;
	if ($FileDataTypeCount > 1) {
		?>
		<H3><font color="red">WARNING! "File" Data Type must be one in Request.</font></H3>
		<?php
	}
}

?>
