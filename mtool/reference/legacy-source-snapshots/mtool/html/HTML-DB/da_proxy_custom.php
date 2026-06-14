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
<title><?php print getres("TITLE_DA_EDIT_PROXY_CUSTOM"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_proxy.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$ProjectPID = GetParam("ProjectPID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForProxyCustom("Proxy Target Setting [Custom, Multi]", $ProjectPID, "");
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	if (!$project) {
		die("Something wrong. No Project");
	}
	
	$IncludeProxy = CheckIfProjectIncludeProxy($ProjectPID);
	
	$DAda = new daDBAccess();
	$dalist = $DAda->GetdaList($ProjectPID); 
	
	$DAdaCustomProxy = new daCustomProxyDBAccess();
	$daCustomProxyList = $DAdaCustomProxy->GetdaCustomProxyList($ProjectPID);
	
	$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
	
	if (count($daCustomProxyList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Base Name</th>
			  <th>Name</th>
			  <th>Enable Transaction</th>
			  <th></th>
              <th>Endpoint</th>
			  <th></th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($daCustomProxyList); $i++) {
			$daCustomProxy = $daCustomProxyList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($daCustomProxy->basename); ?>
              <?php
			  for($j = 0 ; $j < count($dalist); $j++) {
				  $da = $dalist[$j];
				  
				  if ($da->name == $daCustomProxy->basename) {
					  ?>
                      <font color="red">ERROR! Base Name is matched with Data Access class's name. Please define another name.</font>
                      <?PHP
				  }
			  }
			  ?>
              
              </td>
			  <td><?php print htmlspecialchars($daCustomProxy->name); ?></td>
			  <td><?php 
			  if ($daCustomProxy->InTransaction == "1") {
				  print "Yes";
			  } else {
				  print "No";
			  }
			   ?></td>
              <td><a href="da_proxy_custom_func.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxy->PID); ?>&<?php print makeRandStr(8); ?>">View Functions to be called</a></td>
              <td><?php
				  $is_first = true;
				  $AllBuildSourceFuncCacheReleaseTargetTypeEnumList = GetAllBuildSourceFuncCacheReleaseTargetTypeEnumList();
				  for($j = 0 ; $j < count($AllBuildSourceFuncCacheReleaseTargetTypeEnumList) ; $j++) {
					  $AllBuildSourceFuncCacheReleaseTargetTypeEnum = $AllBuildSourceFuncCacheReleaseTargetTypeEnumList[$j];
					  $BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByCustomProxy($ProjectPID, $daCustomProxy->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$CUSTOMPROXYSERVER, $AllBuildSourceFuncCacheReleaseTargetTypeEnum);
					  if ($BuildSourceFuncCache) {
						  if (!$is_first) {
							  print "<br>";
						  }
						  $is_first = false;
						  ?>
						  <a href="da_proxy_custom_endpoint.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DACustomProxyPID=<?php print urlencode($daCustomProxy->PID); ?>&ReleaseType=<?php print urlencode($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>&<?php print makeRandStr(8); ?>">View Endpoint (<?php print GetAllBuildSourceFuncCacheReleaseTargetTypeEnumCaption($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>)</a>
						  <?php
					  }
				  }
				  ?></td>
              <td><a href="da_proxy_custom_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxy->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a></td>
              <td><a href="da_proxy_custom_func_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxy->PID); ?>&<?php print makeRandStr(8); ?>">Change Function's Order</a></td>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
        <?php
        
		synchronize_mtool_custom_proxy_if_automatic($ProjectPID);
		
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
	<p align="right"><a href="da_proxy_custom_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add Proxy Target Setting[Custom, Multi]</a></p>
	<br>
	<br>
	<br>
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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
