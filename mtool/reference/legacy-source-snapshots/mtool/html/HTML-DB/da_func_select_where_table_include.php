		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th>Join Type</th>
			  <th>Target Table Name</th>
			  <?php if ($DBWritePermission) { // Read Onlyユーザには複雑なことを見せても何なのでこれは隠す。説明も手間なので見せない方が良い ?>
				  <th>Target Table Alias Name</th>
			  <?php } // if DBWritePermission ?>
			  <th>Column Name on Target Table</th>
			  <th>Relational Operator</th>
			  <th>Parameter Type</th>
			  <th>Parameter's Data Type</th>
			  <th>Fixed Parameter</th>
			  <th>Another Table Name</th>
			  <?php if ($DBWritePermission) { // Read Onlyユーザには複雑なことを見せても何なのでこれは隠す。説明も手間なので見せない方が良い ?>
				  <th>Another Table Alias Name</th>
			  <?php } // if DBWritePermission ?>
			  <th>Another Field Name</th>
			  <th>OR Group</th>
			  <?php if (!isset($forSort) || !$forSort) { ?>
				  <?php if ($DBWritePermission) { ?>
                  <th></th>
                  <?php } // if DBWritePermission ?>
			  <?php } ?>
			</tr>
          </thead>
            <tbody id="sortablebodyarea">
		<?php

		for($i = 0 ; $i < count($dafuncselectwherelist); $i++) {
			$dafuncselectwhere = $dafuncselectwherelist[$i];
			?>
			<tr id="<?php print $dafuncselectwhere->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td><?php print htmlspecialchars(GetdafuncselectwhereJoinTypeCaption($dafuncselectwhere->JoinType)); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->targetTableName); ?></td>
			  <?php if ($DBWritePermission) { ?>
				  <td><?php print htmlspecialchars($dafuncselectwhere->targetTableAliasName); ?></td>
			  <?php } // if DBWritePermission ?>
			  <td><?php print htmlspecialchars($dafuncselectwhere->targetTableColumnName); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->GetRelationalOperatorCaption()); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->ParameterType); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->GetParameterDataTypeCaptionIfParameterTypeIsNotAnotherField()); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->GetFixedParameterCaptionIfParameterTypeIsFixed()); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->AnotherTableName); ?></td>
			  <?php if ($DBWritePermission) { ?>
				  <td><?php print htmlspecialchars($dafuncselectwhere->AnotherTableAliasName); ?></td>
			  <?php } // if DBWritePermission ?>
			  <td><?php print htmlspecialchars($dafuncselectwhere->AnotherFieldName); ?></td>
			  <td><?php print htmlspecialchars($dafuncselectwhere->ORGroup); ?></td>
			  <?php if (!isset($forSort) || !$forSort) { ?>
				  <?php if ($DBWritePermission) { ?>
                  <td><a href="da_func_select_where_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselectwhere->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselectwhere->dafuncPID); ?>&PID=<?php print urlencode($dafuncselectwhere->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
                  <?php } // if DBWritePermission ?>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
