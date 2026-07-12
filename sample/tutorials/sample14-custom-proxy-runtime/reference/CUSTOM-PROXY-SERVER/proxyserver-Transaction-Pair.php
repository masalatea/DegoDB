<?php

declare(strict_types=1);

require_once __DIR__ . '/' . '_support/custom_proxy_loader.php';

mtool_generated_custom_proxy_run(
    __DIR__,
    'handlers/TransactionPairProxyHandler.php',
    'TransactionPairProxyHandlerBase',
    'TransactionPairProxyHandler',
);
