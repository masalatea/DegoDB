# pattern14-method-and-enum-heavy-multimethod

- 役割: legacy `data-ProjectSourceOutput.php` の heavy multi-method + top-level helper + enum variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-ProjectSourceOutput.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-ProjectSourceOutput.php`, `reference/DATACLASS-PHP/base/data-ProjectSourceOutputBase.php`
- disposable runtime root: `work/sample-packs/pattern14-method-and-enum-heavy-multimethod/output/`

検証:

```bash
php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php
make pattern14-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern14-method-and-enum-heavy-multimethod/output/DATACLASS-PHP/data-ProjectSourceOutput.php
work/sample-packs/pattern14-method-and-enum-heavy-multimethod/output/DATACLASS-PHP/base/data-ProjectSourceOutputBase.php
```
