<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($ProjectPID);
if (!$project) {
	die("Something strange. Project is not found\n");
}

$ShowProxyLink = $project->Getoption_show_proxy_link();
$ShowSourceLink = $project->Getoption_show_source();
$ShowDetailOfProject = $project->Getoption_show_detail();
$AllSourceInclude = $project->Getoption_all_source_include();

$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 

$IncludeProxy = CheckIfProjectIncludeProxy($ProjectPID);

$DAda = new daDBAccess();
$dalist = $DAda->GetdaList($ProjectPID); 

if (count($dalist) > 0) {
	?>
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th>Name
          <?php if ($DBWritePermission) { ?>
              <br />
              <font size="-2">[DB Access Class Name in Source]</font>
          <?php } // if DBWritePermission ?>
          </th>
          <?php if ($DBWritePermission) { ?>
              <th>Store Base Path</th>
              <?php if (!$AllSourceInclude) { ?>
                  <th>Include in Autoload <font size="-1">(for PHP only, not for C#)</font></th>
              <?php } ?>
          <?php } // if DBWritePermission ?>
          <?php if ($forList) { ?>
              <th></th>
              <?php if ($DBWritePermission) { ?>
                  <th></th>
				  <?php if ($project->Getoption_user_can_change_da_func_order()) { ?>
                  <th></th>
                  <?php } ?>
	              <?php if ($ShowSourceLink) { ?>
                  <th>Source</th>
	              <?php } ?>
              <?php } // if DBWritePermission ?>
          <?php } ?>
          <?php if ($DBWritePermission) { ?>
          	  <?php if ($forSetProxyTarget) { ?>
				  <?php if ($IncludeProxy && $ShowProxyLink && !$project->Getoption_automatically_create_simple_proxy()) { ?>
                  <th>Proxy Target Function Count</th>
                  <th></th>
                  <?php } ?>
                  <th>Setting</th>
              <?php } ?>
          <?php } // if DBWritePermission ?>
		  <?php if ($forList) { ?>
              <th></th>
              <th></th>
          <?php } ?>
		</tr>
        </thead>
        <tbody>
	<?php
	for($i = 0 ; $i < count($dalist); $i++) {
		$da = $dalist[$i];
		
		// filter
		if (is_numeric($filterdaPID)) {
			if ($filterdaPID != $da->PID) {
				continue;
			}
		}
		?>
		<tr>
		  <td><?php print htmlspecialchars($da->name); ?>
          <?php if ($DBWritePermission) { ?>
          <br />
		      <font size="-2">[<?php print htmlspecialchars(CreateDatabaseAccessClassName($da->name)); ?>]</font>
          <?php } // if DBWritePermission ?>
          </td>
          <?php if ($DBWritePermission) { ?>
              <td><?php print htmlspecialchars($da->StoreBasePath); ?></td>
              <?php if (!$AllSourceInclude) { ?>
                  <td><?php print htmlspecialchars($da->GetIsAutoloadCaption()); ?></td>
              <?php } ?>
              <?php } // if DBWritePermission ?>
              <?php if ($forList) { ?>
                  <td><a href="da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($da->PID); ?>&<?php print makeRandStr(8); ?>">View Function(s)</a></td>
                  <?php if ($DBWritePermission) { ?>
                      <td><a href="da_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($da->PID); ?>&<?php print makeRandStr(8); ?>">Edit DB Access Class Info</a></td>
				  	  <?php if ($project->Getoption_user_can_change_da_func_order()) { ?>
                      	  <td><a href="da_funcs_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($da->PID); ?>&<?php print makeRandStr(8); ?>">Change Function's Order</a></td>
                      <?php } ?>
                    <?php if ($ShowSourceLink) { ?>
                    <td>
                    <?php
                    $DABuildSourceCache = new BuildSourceCacheDBAccess();
                    $BuildSourceCacheByDataClassList = $DABuildSourceCache->GetBuildSourceCacheByDAList($ProjectPID, $da->PID);
                    if ($BuildSourceCacheByDataClassList) {
                        for ($j = 0 ; $j < count($BuildSourceCacheByDataClassList) ; $j++) {
                            $BuildSourceCacheByDataClass = $BuildSourceCacheByDataClassList[$j];
                            if ($j > 0) {
                                print "<br>";
                            }
                            ?>
                            <a href="da_source.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&PID=<?php print urlencode($BuildSourceCacheByDataClass->PID); ?>&<?php print makeRandStr(8); ?>"><font size="-2"><?php print htmlspecialchars($BuildSourceCacheByDataClass->Filename); ?></font></a>
                            <?php
                        }
                    }
                    ?>
                    </td>
                    <?php } ?>
                  <?php } // if DBWritePermission ?>
              <?php } ?>
          <?php if ($DBWritePermission) { ?>
          	  <?php if ($forSetProxyTarget) { ?>
				  <?php if ($IncludeProxy && $ShowProxyLink && !$project->Getoption_automatically_create_simple_proxy()) { ?>
                  <td><?php
                    
                    $AllCount = 0;
                    $ProxyCount = 0;
                    
                    $DAdafunc = new dafuncDBAccess();
                    $DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
                    $dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $da->PID);
                    for($j = 0 ; $j < count($dafunclist); $j++) {
                        $dafunc = $dafunclist[$j];
                        
                        $AllCount++;
                        
                        $dafuncSimpleProxyList = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxySourceOutputTargetList($ProjectPID, $da->PID, $dafunc->PID);
                        if ($dafuncSimpleProxyList && is_array($dafuncSimpleProxyList)) {
                            if (count($dafuncSimpleProxyList) > 0) {
                                $ProxyCount++;
                            }
                        }
                    }
                  ?>
                  <?php print $ProxyCount . "/" . $AllCount; ?>
                  </td>
                  <td><a href="da_funcs_edit_proxy_single_target.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($da->PID); ?>&<?php print makeRandStr(8); ?>">Proxy Target Setting[Single]</a></td>
                  <?php } ?>
              <td><a href="da_funcs_edit_proxy_single_setting.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($da->PID); ?>&<?php print makeRandStr(8); ?>">View</a></td>
              <?php } ?>
          <?php } // if DBWritePermission ?>
		  <?php if ($forList) { ?>
              <td><?php PrintAddMinutesLinkForda($ProjectPID, $da->PID); ?></td>
              <td><?php PrintSearchMinutesLinkForda($ProjectPID, $da->PID); ?></td>
          <?php } ?>
		</tr>
		<?php
	}
	?>
    	</tbody>
	</table>
<?php
} else {
	?>
<p>none</p>
	<?php
}
?>