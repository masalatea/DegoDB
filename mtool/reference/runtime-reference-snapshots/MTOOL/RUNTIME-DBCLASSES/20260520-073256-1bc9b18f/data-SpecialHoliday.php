<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-SpecialHolidayBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-SpecialHoliday.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-SpecialHoliday.php` and extend `SpecialHolidayDataBase` for project-specific customizations.

    class SpecialHolidayData extends SpecialHolidayDataBase
    {
    }
}

?>
