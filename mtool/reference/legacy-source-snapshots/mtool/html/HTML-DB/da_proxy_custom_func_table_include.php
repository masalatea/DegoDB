<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
              <th></th>
			  <th>Database Access Class Name</th>
			  <th>Function Name</th>
			  <th>Single or List</th>
			  <th>Add Indent on Source</th>
              <th></th>
              <?php if ($for_list) { ?>
			  <th></th>
              <?php } ?>
			</tr>
            </thead>
            <tbody id="sortablebodyarea">
		<?php
		for($i = 0 ; $i < count($daCustomProxyFuncList); $i++) {
			$daCustomProxyFunc = $daCustomProxyFuncList[$i];
			?>
			<tr id="<?php print $daCustomProxyFunc->PID; ?>">
              <td><?php print ($i + 1); ?></td>
			  <td><?php print htmlspecialchars($daCustomProxyFunc->daname); ?></td>
			  <td><?php print htmlspecialchars($daCustomProxyFunc->dafuncname); ?> [<?php print GetFunctionNameFromFunctionActionType($daCustomProxyFunc->dafuncname, $daCustomProxyFunc->dafuncActionType); ?>]</td>
			  <td><?php
			  if ($daCustomProxyFunc->IsList == 1) {
				  print "List";
			  } else {
				  print "Single";
			  }
			   ?></td>
			  <td><?php print htmlspecialchars($daCustomProxyFunc->AddIndentCount); ?></td>
			  <td><?php
			  if ($daCustomProxyFunc->AddIndentCount > 0) {
				  print htmlspecialchars(GetCustomProxyFuncAddIndentTypeEnumCaption($daCustomProxyFunc->AddIndentType));
			  }
			  ?></td>
              <?php if ($for_list) { ?>
			  <td><a href="da_proxy_custom_func_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyPID); ?>&daCustomProxyFuncPID=<?php print urlencode($daCustomProxyFunc->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
              <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
