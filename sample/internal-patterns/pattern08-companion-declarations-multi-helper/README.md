# pattern08-companion-declarations-multi-helper

- 役割: legacy `data-BuildSourceFuncCache.php` の複数 top-level helper と同居 enum type class を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-BuildSourceFuncCache.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-BuildSourceFuncCache.php`, `reference/DATACLASS-PHP/base/data-BuildSourceFuncCacheBase.php`
- disposable runtime root: `work/sample-packs/pattern08-companion-declarations-multi-helper/output/`

検証:

```bash
php mtool/scripts/check_sample14_buildsourcefunccache_companion_declarations_outputs.php
make pattern08-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample14_buildsourcefunccache_companion_declarations_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern08-companion-declarations-multi-helper/output/DATACLASS-PHP/data-BuildSourceFuncCache.php
work/sample-packs/pattern08-companion-declarations-multi-helper/output/DATACLASS-PHP/base/data-BuildSourceFuncCacheBase.php
```
