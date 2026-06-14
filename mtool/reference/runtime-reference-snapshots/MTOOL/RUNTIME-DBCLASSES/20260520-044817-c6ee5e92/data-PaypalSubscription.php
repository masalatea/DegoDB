<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-PaypalSubscriptionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-PaypalSubscription.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-PaypalSubscription.php` and extend `PaypalSubscriptionDataBase` for project-specific customizations.

    class PaypalSubscriptionData extends PaypalSubscriptionDataBase
    {
    }
}

?>
