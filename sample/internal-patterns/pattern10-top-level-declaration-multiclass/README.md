# pattern10-top-level-declaration-multiclass

- 役割: legacy `data-ProjectUser.php` の 3-class top-level declaration variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-ProjectUser.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-ProjectUser.php`, `reference/DATACLASS-PHP/base/data-ProjectUserBase.php`
- disposable runtime root: `work/sample-packs/pattern10-top-level-declaration-multiclass/output/`

検証:

```bash
php mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php
make pattern10-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern10-top-level-declaration-multiclass/output/DATACLASS-PHP/data-ProjectUser.php
work/sample-packs/pattern10-top-level-declaration-multiclass/output/DATACLASS-PHP/base/data-ProjectUserBase.php
```
