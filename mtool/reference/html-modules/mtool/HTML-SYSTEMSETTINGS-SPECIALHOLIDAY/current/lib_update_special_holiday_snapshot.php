<?php

function update_special_holiday_snapshot()
{
	return check_or_update_special_holiday_snapshot(true);
}
function check_special_holiday_snapshot()
{
	return check_or_update_special_holiday_snapshot(false);
}
function check_or_update_special_holiday_snapshot($do_update)
{
	$something_update = false;
	
	$DASpecialHoliday = new SpecialHolidayDBAccess();
	$AllSpecialHolidayList = $DASpecialHoliday->GetAllList();
	
	$DASpecialHolicaySnapshot = new SpecialHolicaySnapshotDBAccess();
	$AllSpecialHolidaySnapshotList = $DASpecialHolicaySnapshot->GetAllList();
	
	// Add
	if ($AllSpecialHolidayList) {
		for($i = 0 ; $i < count($AllSpecialHolidayList) ; $i++) {
			$AllSpecialHoliday = $AllSpecialHolidayList[$i];
			
			$need_to_add = true;
			if ($AllSpecialHolidaySnapshotList) {
				for ($j = 0 ; $j < count($AllSpecialHolidaySnapshotList) ; $j++) {
					$AllSpecialHolidaySnapshot = $AllSpecialHolidaySnapshotList[$j];
					
					if ($AllSpecialHoliday->year  == $AllSpecialHolidaySnapshot->year &&
						$AllSpecialHoliday->month == $AllSpecialHolidaySnapshot->month &&
						$AllSpecialHoliday->day   == $AllSpecialHolidaySnapshot->day
					   )
					{
						$need_to_add = false;
						break;
					}
				}
			}
			if ($need_to_add) {
				$something_update = true;
				if ($do_update) {
					$thisSnapshot = new SpecialHolicaySnapshotData();
					$thisSnapshot->year  = $AllSpecialHoliday->year;
					$thisSnapshot->month = $AllSpecialHoliday->month;
					$thisSnapshot->day   = $AllSpecialHoliday->day;
				
					if ($DASpecialHolicaySnapshot->InsertSpecialHolicaySnapshot($thisSnapshot)) {
						// OK
					} else {
						error_log("Failed to add Special Holiday Snapshot.");
					}
				}
			}
		}
	}
	
	// Delete
	if ($AllSpecialHolidaySnapshotList) {
		for ($i = 0 ; $i < count($AllSpecialHolidaySnapshotList) ; $i++) {
			$AllSpecialHolidaySnapshot = $AllSpecialHolidaySnapshotList[$i];
			
			$need_to_delete = true;
			if ($AllSpecialHolidayList) {
				for($j = 0 ; $j < count($AllSpecialHolidayList) ; $j++) {
					$AllSpecialHoliday = $AllSpecialHolidayList[$j];
					
					if ($AllSpecialHoliday->year  == $AllSpecialHolidaySnapshot->year &&
						$AllSpecialHoliday->month == $AllSpecialHolidaySnapshot->month &&
						$AllSpecialHoliday->day   == $AllSpecialHolidaySnapshot->day
					   )
					{
						$need_to_delete = false;
						break;
					}
				}
			}
			if ($need_to_delete) {
				$something_update = true;
				if ($do_update) {
					if ($DASpecialHolicaySnapshot->DeleteSpecialHolicaySnapshot($AllSpecialHolidaySnapshot)) {
						// OK
					} else {
						error_log("Failed to delete Special Holiday Snapshot.");
					}
				}
			}
		}
	}
	return $something_update;
}

?>
