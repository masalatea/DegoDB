<?php
$any_proxy_server_exist = false;
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
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th></th>
			  <th>Class Type</th>
			  <th>Release Target</th>
			  <th>Program Language</th>
			  <th>Source Template Dir (on DropBox)</th>
			  <th>SourceOutputDir (on DropBox)</th>
              <?php if ($any_proxy_server_exist) { ?>
              	<th>Proxy Base URL</th>
              <?php } ?>
			  <th>Unit Test Template Dir (on DropBox)</th>
			  <th>Unit Test Output Dir (on DropBox)</th>
			  <th>Autoload Filename Suffix (for PHP only, not for C#)</th>
              <th>Target Server from Client</th>
              <th>C#'s Name Space</th>
              <th>Java's Package Name</th>
			  <?php if (isset($forEdit) && $forEdit) { ?>
				  <th></th>
			  <?php } ?>
			</tr>
            </thead>
            <tbody id="sortablebodyarea">
		<?php
		for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
			$ProjectSourceOutput = $ProjectSourceOutputList[$i];
			?>
			<tr id="<?php print $ProjectSourceOutput->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td>
			  <?php if (isset($forEdit) && $forEdit) { ?>
				<?php if ($IsMtoolProjectOwner) { ?>
				  <br>
				  <a href="project_source_output_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ProjectSourceOutputPID=<?php print urlencode($ProjectSourceOutput->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
				<?php } ?>
			  <?php } ?>
			  </td>
			  <td><?php print htmlspecialchars(GetProjectSourceOutputClassTypeCaption($ProjectSourceOutput->ClassType)); ?></td>
			  <td><?php print htmlspecialchars(GetProjectSourceOutputReleaseTargetTypeCaption($ProjectSourceOutput->ReleaseTargetType)); ?></td>
			  <td><?php print htmlspecialchars(GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->ProgramLanguage)); ?>
				<?php
                switch($ProjectSourceOutput->ClassType) {
					case ProjectSourceOutputClassTypeEnum::$DBACCESS:
						// Any Language OK
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
						// Only PHP is supported for Server
						switch($ProjectSourceOutput->ProgramLanguage)
						{
							case ProjectSourceOutputProgramLanguageEnum::$PHP:	// OK
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								?>
                                <h3><font color="red">ERROR! <?php print GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->ProgramLanguage); ?> is not supported for Proxy Server.</font></h3>
                                <?php
								break;
								break;
							default:
								break;
						}
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
						// Only C# is supported for Server
						switch($ProjectSourceOutput->ProgramLanguage)
						{
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								?>
                                <h3><font color="red">ERROR! PHP is not supported for Proxy Client.</font></h3>
                                <?php
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								break;
							default:
								break;
						}
						break;
					case ProjectSourceOutputClassTypeEnum::$HTML:
						// Only PHP is supported for Form
						switch($ProjectSourceOutput->ProgramLanguage)
						{
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								?>
                                <h3><font color="red">ERROR! <?php print GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->ProgramLanguage); ?> is not supported for Form.</font></h3>
                                <?php
								break;
							default:
								break;
						}
						break;
					case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
						break;
                }
                
				// switch($ProjectSourceOutput->ProgramLanguage)
				// {
				// 	case ProjectSourceOutputProgramLanguageEnum::$CUSTOM:
				// 		print "(." . htmlspecialchars($ProjectSourceOutput->CustomFileExtention) . ")";
				// 		break;
				// 	default:
				// 		break;
				// }
                ?>
              </td>
			  <td><?php print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutputIfNotBlank($ProjectPID, $ProjectSourceOutput, $ProjectSourceOutput->SourceTemplateDir)); ?>
              <?php
			if ($ProjectSourceOutput->ClassType != "" &&
			 	$ProjectSourceOutput->ProgramLanguage != "")
			{
				$TemplateTargetType = "";
				switch($ProjectSourceOutput->ClassType)
				{
					case ProjectSourceOutputClassTypeEnum::$DBACCESS:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$DB;
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$PROXYSERVER;
						break;
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER;
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$PROXYCLIENT;
						break;
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT;
						break;
					case ProjectSourceOutputClassTypeEnum::$HTML:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$HTML;
						break;
					case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
						$TemplateTargetType = htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE;
						break;
				}
				if (isset($forEdit) && $forEdit) {
					if ($TemplateTargetType != "") {
						?>
						[<a href="default_setting_show.php?TemplateType=source&TemplateTargetType=<?php print urlencode($TemplateTargetType); ?>&ProgramLanguage=<?php print urlencode($ProjectSourceOutput->ProgramLanguage); ?>&ClassType=<?php print urlencode($ProjectSourceOutput->ClassType); ?>">Show Default</a>]
						<?php
					}
				}
			}
			?>
              </td>
			  <td><?php 
			  if (trim($ProjectSourceOutput->SourceOutputDir) != "") {
				  print htmlspecialchars($ProjectSourceOutput->SourceOutputDir);
				  print "<br>";
				  ?>
                  <font size="1">
                  <?php
				  print "Shared folder Path: ";
				  print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutput($ProjectPID, $ProjectSourceOutput, $ProjectSourceOutput->SourceOutputDir));
				  ?>
                  </font>
                  <?php
				  
			  } ?></td>
              <?php if ($any_proxy_server_exist) { ?>
			    <td><?php
				if ($ProjectSourceOutput->IsProxyServer()) {
					print htmlspecialchars($ProjectSourceOutput->ProxyBaseURL);
				}
				?></td>
              <?php } ?>
			  <td><?php print htmlspecialchars($ProjectSourceOutput->UnitTestTemplateDir); ?>
              <?php
			  
			if ($ProjectSourceOutput->ProgramLanguage != "")
			{
				switch($ProjectSourceOutput->ClassType)
				{
					case ProjectSourceOutputClassTypeEnum::$DBACCESS:
					case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
					case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
						if (isset($forEdit) && $forEdit) {
							?>
							[<a href="default_setting_show.php?TemplateType=source&TemplateTargetType=<?php print urlencode(htmlTemplateTargetTypeEnum::$UNITTEST); ?>&ProgramLanguage=<?php print urlencode($ProjectSourceOutput->ProgramLanguage); ?>&ClassType=<?php print urlencode($ProjectSourceOutput->ClassType); ?>">Show Default</a>]
							<?php
						}
						break;
					case ProjectSourceOutputClassTypeEnum::$HTML:
					case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
						break;
				}
			}
			  ?>
              </td>
			  <td><?php print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutputIfNotBlank($ProjectPID, $ProjectSourceOutput, $ProjectSourceOutput->UnitTestOutputDir)); ?></td>
			  <td><?php print htmlspecialchars($ProjectSourceOutput->AutoloadFilenameSuffix); ?></td>
              <td>
              <?php
                switch($ProjectSourceOutput->ClassType) {
					case ProjectSourceOutputClassTypeEnum::$DBACCESS:
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
						break;
					case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
						if (is_numeric($ProjectSourceOutput->TargetServerProjectSourceOutputPID) && $ProjectSourceOutput->TargetServerProjectSourceOutputPID > 0) {
							if (trim($ProjectSourceOutput->TargtServerPSOProxyBaseURL) != "") {
								print htmlspecialchars($ProjectSourceOutput->TargtServerPSOProxyBaseURL); ?>
                                <br />
                                <?php
							}
							?>
                            Lang: <?php print htmlspecialchars(GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->TargtServerPSOProgramLanguage)); ?>
                            <?php
							// switch($ProjectSourceOutput->TargtServerPSOProgramLanguage)
							// {
							//	case ProjectSourceOutputProgramLanguageEnum::$CUSTOM:
							//		print "(." . htmlspecialchars($ProjectSourceOutput->TargtServerPSOCustomFileExtention) . ")";
							//		break;
							//	default:
							//		break;
							// }
						} else {
							?>
							<font color="red">Error! Undefined</font>
                            <?php
						}
						break;
					case ProjectSourceOutputClassTypeEnum::$HTML:
					case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
						break;
                }
			  ?>
              <td><?php
				switch($ProjectSourceOutput->ProgramLanguage)
				{
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						print $ProjectSourceOutput->CSNameSpace;
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						break;
					default:
						break;
				}
              ?></td>
              <td><?php
				switch($ProjectSourceOutput->ProgramLanguage)
				{
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						print $ProjectSourceOutput->JavaPackageName;
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						break;
					default:
						break;
				}
              ?></td>
              </td>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>