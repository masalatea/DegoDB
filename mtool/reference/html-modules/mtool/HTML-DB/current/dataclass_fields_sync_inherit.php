<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DATA_CLASS_FIELD_SYNC_INHERIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasse_fields_sync.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DataClassPID = trim(GetParam("DataClassPID"));

$DoSync = trim(GetParam("DoSync"));
$DoSyncOrder = trim(GetParam("DoSyncOrder"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DataClassPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Data Class PID</font></H3>
    <?php
	$NoError = false;
}

InitializeOutputShortenedStringWithExpansion();

$DAdataclass = new dataclassDBAccess();
$dataclass = NULL;
$ParentDataclass = NULL;

if ($NoError) {
	$dataclass = $DAdataclass->Getdataclass($DataClassPID, $ProjectPID);
	
	if ($dataclass == NULL) {
		?>
		<H3><font color="red">Error. Corresponding Data Class is not exist</font></H3>
		<?php
		$NoError = false;
	}
}
if ($NoError) {
	if (trim($dataclass->InheritParentDataClassName) == "") {
		?>
		<H3><font color="red">Stop. Inherit Parent Data Class is not set</font></H3>
		<?php
		$NoError = false;
	}
}
if ($NoError) {
	$ParentDataclass = $DAdataclass->GetdataclassByName($ProjectPID, trim($dataclass->InheritParentDataClassName));
	
	if ($ParentDataclass == NULL) {
		?>
		<H3><font color="red">Error. Corresponding Data Class is not exist for: <?php print $dataclass->InheritParentDataClassName; ?></font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	printPathOnTopForDataClasses("Sync Field(s) with Parent", $ProjectPID, $DataClassPID, "");
	
	mtool_dataclass_fields_sync_do($ProjectPID, $DataClassPID, $DoSync, $DoSyncOrder);
	
	$DAdataclassfields = new dataclassfieldsDBAccess();
		
	$Parentdataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $ParentDataclass->PID); 
	$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $DataClassPID); 
	
	if (count($Parentdataclassfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th colspan="2">Parent Class</th>
			  <th colspan="2">This Class</th>
              <th></th>
			</tr>
			<tr bgcolor="#ECECEC">
			  <th>Name</th>
			  <th>Data Type<br>
<font size="-2">(For C#, not for PHP)</font></th>
			  <th>Name</th>
			  <th>Data Type<br>
<font size="-2">(For C#, not for PHP)</font></th>
              <th></th>
			</tr>
          </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($Parentdataclassfieldlist); $i++) {
			$Parentdataclassfield = $Parentdataclassfieldlist[$i];
			$FieldNameSupposedToBe = $Parentdataclassfield->name;
			
			$IsOrderMatched = false;
			
			$correspondingdataclassfield = NULL;
			for($j = 0 ; $j < count($dataclassfieldlist); $j++) {
				$thisdataclassfield = $dataclassfieldlist[$j];
				
				if ($FieldNameSupposedToBe == $thisdataclassfield->name) {
					$correspondingdataclassfield = $thisdataclassfield;
					
					if ($i == $j) {
						$IsOrderMatched = true;
					}
					break;
				}
			}
			?>
			<tr>
			  <td><?php print htmlspecialchars($Parentdataclassfield->name); ?></td>
			  <td><?php OutputShortenedStringWithExpansion($Parentdataclassfield->datatype, 20); ?></td>
			  <td><?php if ($correspondingdataclassfield != NULL) {
				   print htmlspecialchars($correspondingdataclassfield->name);
				   
				   if (!$IsOrderMatched) {
					   ?>
                       <br>
                       <font color="red" size="-1">Warning: Order is not matched (you can ignore)</font>
                       <?php
				   }
			  } else {
				  ?>
                  <font color="red"><?php print htmlspecialchars("Field is not exist: " . $FieldNameSupposedToBe); ?></font>
                  <?php
			  } ?></td>
			  <td><?php if ($correspondingdataclassfield != NULL) {
				  OutputShortenedStringWithExpansion($correspondingdataclassfield->datatype, 20);
			  } ?></td>
              <td><?php if ($correspondingdataclassfield != NULL ) { ?>
              <a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&DataClassFieldPID=<?php print urlencode($correspondingdataclassfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
              <?php } ?></td>
			</tr>
			<?php
		}
		?>
			<tr>
              <td></td>
              <td></td>
              <td><a href="dataclass_fields_sync_inherit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&DoSync=y&<?php print makeRandStr(8); ?>">Do Synchronize (without order)</a><br>
              <a href="dataclass_fields_sync_inherit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&DoSync=y&DoSyncOrder=y&<?php print makeRandStr(8); ?>">Do Synchronize (with order)</a>
</td>
              <td></td>
			</tr>
        	</tbody>
		</table>
        <?php
		
		$otherfieldlist = GetNonExistFieldBasedOnInheritClassField($dataclassfieldlist, $Parentdataclassfieldlist);
		
		if (count($otherfieldlist) > 0) {
			?>
            <h3>FYI: Other Fields:</h3>
            <?Php
			for($i = 0 ; $i < count($otherfieldlist); $i++) {
				$thisdataclassfield = $otherfieldlist[$i];
				?>
                <h4><?php print $thisdataclassfield->name; ?> [<a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&DataClassFieldPID=<?php print urlencode($thisdataclassfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>]</h4>
                <?Php
			}
		}
		
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="dataclass_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&<?php print makeRandStr(8); ?>">Back to Field(s) List</a></p>
    <p><a href="./dataclasses.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Data Class List</a></p>
    <?php
}
?>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content
