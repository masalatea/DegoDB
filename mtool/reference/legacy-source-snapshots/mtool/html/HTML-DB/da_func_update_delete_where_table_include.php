		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th>Column Name on Target Table</th>
			  <th>Relational Operator</th>
			  <th>Parameter Type</th>
			  <th>Parameter's Data Type</th>
			  <th>Fixed Parameter</th>
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

		for($i = 0 ; $i < count($dafuncupdatedeletewherelist); $i++) {
			$dafuncupdatedeletewhere = $dafuncupdatedeletewherelist[$i];
			
			?>
			<tr id="<?php print $dafuncupdatedeletewhere->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->targetTableColumnName); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->GetRelationalOperatorCaption()); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->ParameterType); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->GetParameterDataTypeCaption()); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->GetFixedParameterCaptionIfParameterTypeIsFixed()); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatedeletewhere->ORGroup); ?></td>
			  <?php if (!isset($forSort) || !$forSort) { ?>
				  <?php if ($DBWritePermission) { ?>
                  <td><a href="da_func_update_delete_where_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($dafuncupdatedeletewhere->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncupdatedeletewhere->dafuncPID); ?>&PID=<?php print urlencode($dafuncupdatedeletewhere->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
                  <?php } // if DBWritePermission ?>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
