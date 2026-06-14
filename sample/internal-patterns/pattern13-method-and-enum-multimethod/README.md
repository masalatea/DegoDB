# pattern13-method-and-enum-multimethod

- 役割: legacy `data-Project.php` の multi-method + top-level helper + enum variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-Project.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-Project.php`, `reference/DATACLASS-PHP/base/data-ProjectBase.php`
- disposable runtime root: `work/sample-packs/pattern13-method-and-enum-multimethod/output/`

検証:

```bash
php mtool/scripts/check_sample21_project_method_and_enum_outputs.php
make pattern13-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample21_project_method_and_enum_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern13-method-and-enum-multimethod/output/DATACLASS-PHP/data-Project.php
work/sample-packs/pattern13-method-and-enum-multimethod/output/DATACLASS-PHP/base/data-ProjectBase.php
```
