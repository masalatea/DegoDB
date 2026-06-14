# 2026-05-28 canonical data wrapper/base completion

## 要約

- `M3. canonical generated data-* wrapper/base migration` は current promoted runtime reference の実態上すでに完了していたため、この slice では status / focused verification / handoff wording をその reality に揃えた。
- current promoted `mtool/reference/dbclasses/` では `data-*` 全 `99` class が top-level wrapper + `base/data-*Base.php` で揃っており、`bootstrap_data_class_count=0` である。
- non-plain candidate `36` class も current emitted runtime では bootstrap copy ではなく wrapper/base lane に吸収済みであり、historical layered provenance は manifest の `generated-layered-runtime-wrapper-base` に残る。

## 今回固定したこと

- `tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - promoted `data-*.php` がすべて `base/data-*Base.php` を `require_once` していること
  - promoted `data-*.php` が custom wrapper loader を使い、`mtool_runtime_bundle_load_layered_file()` を呼ばないこと
  - promoted `base/data-*Base.php` が generated base header を持つこと
  - promoted runtime manifest の
    - `canonical_data_class_count=99`
    - `data_entity_count=99`
    - `bootstrap_data_class_count=0`
    - `data_generation_items[*].decision=generated`
    - `reason_code` が current data wrapper/base lane の範囲内
    を focused test で固定した

## 現在の読み方

- `plain_data_candidate_count=63`
- `non_plain_data_candidate_count=36`
- `bootstrap_data_class_count=0`

これは「non-plain が残っている」のではなく、「non-plain source pattern は `36` 件あるが、runtime emitted contract としては全件 wrapper/base 化済み」と読む。

current wave の `M3` では、`non-plain bootstrap` の全面解消を新規実装する必要はもうなく、current repo の active plan と resume wording をこの実態へ揃えればよい。

## focused verification

- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReferenceLayoutContractTest.php`
  - `OK (2 tests, 1910 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeReplacementRolloutLaneTest.php`
  - `OK (3 tests, 843 assertions)`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - `OK (5 tests, 48 assertions)`

## 補足

- この slice では runtime data generator の追加修正は入れていない。current promoted/runtime manifest がすでに `99/99/0` を満たしていたためである。
- `make mtool-self-loop-check` はこの slice では再実行していない。at-the-time の pending issue は `M2` から持ち越した `db_access_sync candidate count drift` だけであり、最終的な切り分けと baseline 整理は `docs/reports/2026/2026-0528-close-verification-status-freeze.md` に記録した。
- 次の着手点は `Close. verification / docs / status freeze` である。
