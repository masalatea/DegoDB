<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_project_source_output.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");

$is_include_DBaaS_proxy = false;
$is_include_non_DBaaS_proxy = false;
mtool_check_if_project_source_output_include_DBaaS($ProjectPID, $is_include_DBaaS_proxy, $is_include_non_DBaaS_proxy);

function InitializeDAFuncDuplicateNameCheck($dafunclist)
{
	$duplicatedFunctionHT = array();
	$functionNameInSourceHT = array();
	for($i = 0 ; $i < count($dafunclist); $i++) {
		$dafunc = $dafunclist[$i];
		
		$functionNameInSource = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
		$key = strtoupper($functionNameInSource);
		if (array_key_exists($key, $functionNameInSourceHT)) {
			$duplicatedFunctionHT[$key] = true;
		}
		$functionNameInSourceHT[$key] = true;
	}
	return $duplicatedFunctionHT;
}
function CheckIfDAFuncDuplicateName($duplicatedFunctionHT, $dafunc)
{
	$isDuplicated = false;
	$functionNameInSource = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
	$key = strtoupper($functionNameInSource);
	if (array_key_exists($key, $duplicatedFunctionHT)) {
		$isDuplicated = true;
	}
	return $isDuplicated;
}

$any_proxy_server_exist = false;
$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($ProjectPID);
for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
	$ProjectSourceOutput = $ProjectSourceOutputList[$i];
	
	if ($ProjectSourceOutput->IsProxyServer()) {
		$any_proxy_server_exist = true;
		break;
	}
}

?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Name<br>
<font size="-2">[DB Access Function Name in Source]</font></th>
			  <th>Action Type</th>
              <?php if ($forList) { ?>
                  <th>Target Field(s)</th>
                  <th>Where to Affect</th>
			  <?php } ?>
			  <?php if ($DBWritePermission) { ?>
                  <th>Data Class Name <font size="-2">(for storing select result)</font><br>
                   <font size="-2">[Source's class name]</font></th>
                  <th>Insert/Update/Delete Target Table</th>
                  <th>Insert/Update/Delete Param Type</th>
                  <th>Limit</th>
                  <th>Memo</th>
                  <?php if ($forList) { ?>
					  <?php if ($ShowSourceLink) { ?>
                          <th>Source</th>
                      <?php } ?>
                      <?php if ($any_proxy_server_exist) { ?>
	                      <th> Endpoint</th>
                      <?php } ?>
      <th></th>
                      <th></th>
                      <th></th>
                  <?php } ?>
                  <?php if ($forSetProxyTarget) { ?>
	                  <th>Proxy Target?</th>
                  <?php } ?>
                  <?php if ($forSetProxyTarget || $forSetProxySetting) { ?>
	                  <th>Proxy Setting</th>
	                  <th>Authentication Type</th>
                  <?php } ?>
              <?php } ?>
			  <?php if ($forList) { ?>
                  <th></th>
                  <th></th>
              <?php } ?>
			</tr>
          </thead>
            <tbody>
		<?php
		$duplicatedFunctionHT = InitializeDAFuncDuplicateNameCheck($dafunclist);
		
		$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
		$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
	    $DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();

		for($i = 0 ; $i < count($dafunclist); $i++) {
			$dafunc = $dafunclist[$i];
			
			// filter
			if (isset($filterdafuncPID) && is_numeric($filterdafuncPID)) {
				if ($filterdafuncPID != $dafunc->PID) {
					continue;
				}
			}
			
			$proxy_exist = false;
			$dafuncSimpleProxyList = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxySourceOutputTargetList($ProjectPID, $DAPID, $dafunc->PID);
			for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
				$ProjectSourceOutput = $ProjectSourceOutputList[$j];
				
				for ( $k = 0 ; $k < count($dafuncSimpleProxyList) ; $k++) {
					$dafuncSimpleProxy = $dafuncSimpleProxyList[$k];
					
					if ($dafuncSimpleProxy->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
						$proxy_exist = true;
						break;
					}
				}
				if ($proxy_exist) {
					break;
				}
			}
			
			$isDuplicated = CheckIfDAFuncDuplicateName($duplicatedFunctionHT, $dafunc);
			?>
			<tr>
			  <td><?php print htmlspecialchars($dafunc->name); ?>
              <br>
			  <font size="-2">[<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); 
			  ?>]</font>
			  <?php
			  if ($isDuplicated) {
				  ?>
                  <font color="red">WARNING! Name is duplicated. Please Check</font>
                  <?php
			  }
			  ?></td>
			  <td><?php print htmlspecialchars(GetDAFuncActionTypeCaption($dafunc->ActionType)); 
			  
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
							break;
                        case dafuncActionTypeEnum::$SELECTLIST:
							if ($dafunc->SelectByDistinct == 1) {
								print "(Distinct)";
							}
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$DELETE:
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
			  ?></td>
              <?php if ($forList) { ?>
                  <td><?php 
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                        case dafuncActionTypeEnum::$SELECTLIST:
                            ?>
                            <a href="da_func_select_target_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">View Select Target Field(s)</a>
                            <?php if ($DBWritePermission) { ?>
                                <br>
                                [<a href="da_func_select_target_fields_sync.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Sync</a>]
                                <?php
                            } // if DBWritePermission
							
							$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $dafunc->PID);
                            if (!$dafuncselecttargetfieldlist || count($dafuncselecttargetfieldlist) <= 0) {
                                ?>
                                <font color="red">Warning! No Target Field.<br /></font>
                                <?php
							}
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                            ?>
                            <a href="da_func_insert_target_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">View Insert Target Field(s)</a>
                            <?php if ($DBWritePermission) { ?>
                                <br>
                                [<a href="da_func_insert_target_fields_sync.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Sync</a>]
                                <?php
                            } // if DBWritePermission
							
							$DAdafuncinserttargetfields = new dafuncinserttargetfieldsDBAccess();
							$dafuncinserttargetfieldlist = $DAdafuncinserttargetfields->GetdafuncinserttargetfieldsList($ProjectPID, $DAPID, $dafunc->PID); 
                            if (!$dafuncinserttargetfieldlist || count($dafuncinserttargetfieldlist) <= 0) {
                                ?>
                                <font color="red">Warning! No Target Field.<br /></font>
                                <?php
							}
                            break;
                        case dafuncActionTypeEnum::$UPDATE:
                            ?>
                            <a href="da_func_update_target_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">View Update Target Field(s)</a>
                            <?php if ($DBWritePermission) { ?>
                                <br>
                                [<a href="da_func_update_target_fields_sync.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Sync</a>]
                                <?php
                            } // if DBWritePermission
							
							$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
							$dafuncupdatetargetfieldlist = $DAdafuncupdatetargetfields->GetdafuncupdatetargetfieldsList($ProjectPID, $DAPID, $dafunc->PID); 
                            if (!$dafuncupdatetargetfieldlist || count($dafuncupdatetargetfieldlist) <= 0) {
                                ?>
                                <font color="red">Warning! No Target Field.<br /></font>
                                <?php
							}
                            break;
                        case dafuncActionTypeEnum::$DELETE:
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
                   ?></td>
                  <td><?php 
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                        case dafuncActionTypeEnum::$SELECTLIST:
                            ?>
                            <a href="da_func_select_where.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">View "Where"</a>
                            <?php if ($DBWritePermission) { ?>
                                <br>
                                [<a href="da_func_select_where_input_aid.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Input Aid</a>]
                                <br>
                                [<a href="da_func_select_where_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Change Order</a>]
                                <?php
                                $tmp = $DAdafuncselecttargetfields->GetGroupByTargetCount($ProjectPID, $DAPID, $dafunc->PID);
                                $GroupByCount = $tmp->GroupByTarget;
                                if ($GroupByCount > 0) {
                                    ?>
                                    <br>
                                    [<a href="da_func_select_having.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Define Having</a> (this definition includes Group By)]
                                    <?php
                                }
                            }	// if DBWritePermission
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                            break;
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$DELETE:
                            $DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
                            $dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($ProjectPID, $DAPID, $dafunc->PID); 
                            if (!$dafuncupdatedeletewherelist || count($dafuncupdatedeletewherelist) <= 0) {
                                ?>
                                <font color="red">Warning! No Condition.<br /></font>
                                <?php
                            }
                            ?>
                            <a href="da_func_update_delete_where.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">View "Where" <font size="-2">(for Update/Delete)</font></a>
                            <?php if ($DBWritePermission) { ?>
                                <br>
                                [<a href="da_func_update_delete_where_input_aid.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Input Aid</a>]
                                <br>
                                [<a href="da_func_update_delete_where_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Change Order</a>]
                                <?php
                            } // if DBWritePermission
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
                   ?></td>
              <?php } ?>
              <?php if ($DBWritePermission) { ?>
                  <td><?php
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                        case dafuncActionTypeEnum::$SELECTLIST:
                              print htmlspecialchars($dafunc->DataClassBaseNameForSelectAction);
                              
                              $thisBaseDataClassName = $dafunc->GetBaseDataClassName();
                              if ($thisBaseDataClassName != "") {
                                  ?><br>
                                  <font size="-2">[<?php print htmlspecialchars(CreateDataClassName($thisBaseDataClassName)); ?>]</font>
                                  <?php
                              }
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$DELETE:
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
                  ?>
                  </td>
                  <td><?php 
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                        case dafuncActionTypeEnum::$SELECTLIST:
                            break;
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$INSERT:
                        case dafuncActionTypeEnum::$DELETE:
							print htmlspecialchars($dafunc->InsertUpdateDeleteTargetTable); 
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
				  ?></td>
                  <td><?php
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                        case dafuncActionTypeEnum::$SELECTLIST:
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$DELETE:
                            switch($dafunc->InsertUpdateDeleteParamType)
                            {
                                case dafuncInsertUpdateDeleteParamTypeEnum::$DEFAULT:
                                    ?>
                                    <font color="red">Error! Parameter Type is not selected</font>
                                    <?php
                                    break;
                                case dafuncInsertUpdateDeleteParamTypeEnum::$VAL:
                                case dafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT:
                                case dafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE:
                                    print htmlspecialchars(GetdafuncInsertUpdateDeleteParamTypeCaption($dafunc->InsertUpdateDeleteParamType));
                                    break;
                            }
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
                  ?></td>
                  <td><?php
                    switch($dafunc->ActionType) {
                        case dafuncActionTypeEnum::$SELECTSINGLE:
                            break;
                        case dafuncActionTypeEnum::$SELECTLIST:
                                switch($dafunc->limitParameterType) {
                                    case dafunclimitParameterTypeEnum::$ARGUMENT:
                                        print htmlspecialchars("Limit by Argument");
                                        break;
                                    case dafunclimitParameterTypeEnum::$FIXED:
                                        print htmlspecialchars("Limit " . $dafunc->limitFixedParameter);
                                        break;
                                }
                            break;
                        case dafuncActionTypeEnum::$INSERT:
                        case dafuncActionTypeEnum::$UPDATE:
                        case dafuncActionTypeEnum::$DELETE:
                            break;
                        default:
                            print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                            break;
                    }
                  ?>
                  </td>
                  <td><font size="-2"><?php OutputShortenedStringWithExpansion($dafunc->memo, 50); ?></font></td>
                  <?php if ($forList) { ?>
					  <?php if ($ShowSourceLink) { ?>
                          <td><?php
						  $is_first = true;
						  $AllBuildSourceFuncCacheReleaseTargetTypeEnumList = GetAllBuildSourceFuncCacheReleaseTargetTypeEnumList();
						  for($j = 0 ; $j < count($AllBuildSourceFuncCacheReleaseTargetTypeEnumList) ; $j++) {
						  	  $AllBuildSourceFuncCacheReleaseTargetTypeEnum = $AllBuildSourceFuncCacheReleaseTargetTypeEnumList[$j];
							  
							  $BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($ProjectPID, $DAPID, $dafunc->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$DA, $AllBuildSourceFuncCacheReleaseTargetTypeEnum);
							  if ($BuildSourceFuncCache) {
								  if (!$is_first) {
									  print "<br>";
								  }
								  $is_first = false;
								  ?>
								  <a href="da_func_source.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&ReleaseType=<?php print urlencode($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>&<?php print makeRandStr(8); ?>"><?php print htmlspecialchars($BuildSourceFuncCache->FunctionName); ?> (<?php print GetAllBuildSourceFuncCacheReleaseTargetTypeEnumCaption($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>)</a>
								  <?php
							  }
						  }
                          ?></td>
                      <?php } ?>
                      <?php if ($any_proxy_server_exist) { ?>
                      	  <td><?php
						  $is_first = true;
						  $AllBuildSourceFuncCacheReleaseTargetTypeEnumList = GetAllBuildSourceFuncCacheReleaseTargetTypeEnumList();
						  for($j = 0 ; $j < count($AllBuildSourceFuncCacheReleaseTargetTypeEnumList) ; $j++) {
						  	  $AllBuildSourceFuncCacheReleaseTargetTypeEnum = $AllBuildSourceFuncCacheReleaseTargetTypeEnumList[$j];
							  $BuildSourceFuncCache = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($ProjectPID, $DAPID, $dafunc->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$PROXYSERVER, $AllBuildSourceFuncCacheReleaseTargetTypeEnum);
							  if ($BuildSourceFuncCache) {
								  if (!$is_first) {
									  print "<br>";
								  }
								  $is_first = false;
								  ?>
								  <a href="da_func_endpoint.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&ReleaseType=<?php print urlencode($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>&<?php print makeRandStr(8); ?>">View Endpoint (<?php print GetAllBuildSourceFuncCacheReleaseTargetTypeEnumCaption($AllBuildSourceFuncCacheReleaseTargetTypeEnum); ?>)</a>
								  <?php
							  }
						  }
                          ?></td>
                      <?php } ?>
                      <td><a href="da_func_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a>
                      <?php
                        switch($dafunc->ActionType) {
                            case dafuncActionTypeEnum::$SELECTSINGLE:
                            break;
                            case dafuncActionTypeEnum::$SELECTLIST:
                                ?>
                                <br />
                                [<a href="da_func_sort_order_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Sort Order Sync</a>]
                                <?php
                                break;
                            case dafuncActionTypeEnum::$INSERT:
                            case dafuncActionTypeEnum::$UPDATE:
                            case dafuncActionTypeEnum::$DELETE:
                                break;
                            default:
                                print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
                                break;
                        }
                      ?>
                      </td>
                      <td><a href="da_func_move.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Move</a></td>
                  <?php } ?>
                  <?php if ($forSetProxyTarget) { ?>
                      <td>
                      <?php
                        $DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
                        $dafuncSimpleProxyList = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxySourceOutputTargetList($ProjectPID, $DAPID, $dafunc->PID);
                        
                        // $isFirstLine = true;
                        
                        for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
                            $ProjectSourceOutput = $ProjectSourceOutputList[$j];
                            
                            if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
                                $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER ||
                                $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER ||
                                $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT) {
                                
                                $isSelected = false;
                                for($k = 0 ; $k < count($dafuncSimpleProxyList) ; $k++) {
                                    $dafuncSimpleProxy = $dafuncSimpleProxyList[$k];
                                    
                                    if ($dafuncSimpleProxy->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
                                        $isSelected = true;
                                        break;
                                    }
                                }
                                // if (!$isFirstLine) {
                                //     print "<br>";
                                // }
                                ?>
                                <span class="checkbox"><label>
                                <input name="IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID[]" type="checkbox" value="<?php print $dafunc->PID . "-" . $ProjectSourceOutput->PID; ?>"<?php if ($isSelected) { print " checked"; } ?> /> <?php print htmlspecialchars(MakeDropboxFolderByName($project->DropboxBaseFolderName, $ProjectSourceOutput->SourceOutputDir)) . " " . htmlspecialchars($ProjectSourceOutput->TargtServerPSOProxyBaseURL); ?>
								</label></span>
                                <?php
                                // $isFirstLine = false;
                            }
                        }
                      ?>
                      </td>
                  <?php } ?>
                  <?php if ($forSetProxyTarget || $forSetProxySetting) { ?>
                      <td><a href="da_funcs_edit_proxy_single_setting_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($dafunc->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
                      <td><?php 
							if ($is_include_DBaaS_proxy) {
								print htmlspecialchars(GetSingleProxyAuthTypeCaption($dafunc->SingleProxy_AuthType));
								if ($is_include_non_DBaaS_proxy) {
									?>
									for DBaaS Proxy<br />
									Manual for non DBaaS Proxy
									<?php
								}
								switch($dafunc->SingleProxy_AuthType) {
									case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
									case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
									case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
									case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
									case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
										break;
									case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
									case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
										$DAdafunc = new dafuncDBAccess();
										$this_dafunc = $DAdafunc->Getdafunc($dafunc->PID, $ProjectPID);
										if ($this_dafunc) {
											print "<br>\n";
											print GetFunctionNameFromFunctionActionType($this_dafunc->name, $this_dafunc->ActionType);
										}
										break;
									default:
										print "INTERNAL ERROR! Unknown Auth Type: " . $dafunc->SingleProxy_AuthType . "\n";
								}
							} else {
								?>
								Manual
								<?php
							}
					  ?></td>
                  <?php } ?>
              <?php } // if DBWritePermission ?>
			  <?php if ($forList) { ?>
                  <td><?php PrintAddMinutesLinkFordafunc($ProjectPID, $DAPID, $dafunc->PID); ?></td>
                  <td><?php PrintSearchMinutesLinkFordafunc($ProjectPID, $DAPID, $dafunc->PID); ?></td>
              <?php } ?>
			</tr>
			<?php
		}
		?>
			<?php if ($DBWritePermission) { ?>
				<?php if ($forSetProxyTarget) { ?>
                <tr>
                  <td></td>
                  <td></td>
                  <?php if ($forList) { ?>
                      <td></td>
                      <td></td>
                  <?php } ?>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <?php if ($forList) { ?>
                      <td></td>
                      <td></td>
                      <td></td>
                  <?php } ?>
                  <?php if ($forSetProxyTarget) { ?>
                      <td><input name="UPDATE" type="submit" value="UPDATE" />
                      </td>
                  <?php } ?>
                </tr>
                <?php } ?>
            <?php } ?>
        	</tbody>
		</table>
