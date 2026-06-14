<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-SpecialHolidayBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-SpecialHoliday.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-SpecialHoliday.php` and extend `SpecialHolidayDBAccessBase` for project-specific customizations.

    class SpecialHolidayDBAccess extends SpecialHolidayDBAccessBase
    {
    }
}

?>
