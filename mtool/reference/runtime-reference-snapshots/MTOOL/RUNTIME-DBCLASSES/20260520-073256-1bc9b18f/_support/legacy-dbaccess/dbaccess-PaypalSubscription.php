<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-PaypalSubscriptionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-PaypalSubscription.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-PaypalSubscription.php` and extend `PaypalSubscriptionDBAccessBase` for project-specific customizations.

    class PaypalSubscriptionDBAccessLegacy extends PaypalSubscriptionDBAccessBase
    {
    }
}

?>
