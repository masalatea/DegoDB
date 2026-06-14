<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th colspan="3">Left Target</th>
			  <th rowspan="2">Relational Operator</th>
			  <th colspan="6">Right Target</th>
			  <?php if (!isset($forSort) || !$forSort) { ?>
			  <th></th>
			  <?php } ?>
			</tr>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <?php if (isset($forSort) && $forSort) { ?>
			  <th></th>
			  <?php } ?>
			  <th>Prefix</th>
			  <th>Field</th>
			  <th>Suffix</th>
			  <th>Prefix</th>
			  <th>Parameter Type</th>
			  <th>Parameter Data Type</th>
			  <th>Fixed Parameter</th>
			  <th>Field</th>
			  <th>Suffix</th>
			  <?php if (!isset($forSort) || !$forSort) { ?>
			  <th></th>
			  <?php } ?>
			</tr>
          </thead>
            <tbody id="sortablebodyarea">
		<?php
		

		for($i = 0 ; $i < count($dafuncselecthavinglist); $i++) {
			$dafuncselecthaving = $dafuncselecthavinglist[$i];
			?>
			<tr id="<?php print $dafuncselecthaving->PID; ?>">
			  <?php if (isset($forSort) && $forSort) { ?>
              <td><?php print ($i + 1); ?></td>
			  <?php } ?>
			  <td><?php print htmlspecialchars($dafuncselecthaving->LeftTargetPrefix); ?></td>
			  <td><?php
			  	print htmlspecialchars(GetReferencingFieldColumnIfThereis($ProjectPID, $dafuncselecthaving->LeftTargetFieldPID));
				?></td>
			  <td><?php print htmlspecialchars($dafuncselecthaving->LeftTargetSuffix); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecthaving->RelationalOperator); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecthaving->RightTargetPrefix); ?></td>
			  <td><?php print htmlspecialchars(GetdafuncselecthavingRightParameterTypeCaption($dafuncselecthaving->RightParameterType)); ?></td>
			  <td><?php
				if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$ARGUMENT ||
					$dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$FIXED)
				{
					print htmlspecialchars(GetdafuncselecthavingRightParameterDataTypeCaption($dafuncselecthaving->RightParameterDataType));
				}
			  ?></td>
			  <td><?php
				if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$FIXED)
				{
	              	print htmlspecialchars($dafuncselecthaving->RightFixedParameter);
				}
				?></td>
			  <td><?php
				if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$FIELD)
				{
				  	print htmlspecialchars(GetReferencingFieldColumnIfThereis($ProjectPID, $dafuncselecthaving->RightTargetFieldPID));
				}
				?></td>
			  <td><?php print htmlspecialchars($dafuncselecthaving->RightTargetSuffix); ?></td>
			  <?php if (!isset($forSort) || !$forSort) { ?>
			  <td><a href="da_func_select_having_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselecthaving->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselecthaving->dafuncPID); ?>&PID=<?php print urlencode($dafuncselecthaving->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
