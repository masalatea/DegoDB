# pattern05-companion-declarations-basic

- 役割: legacy `data-CompareOutput.php` の top-level helper と同居 enum type class を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-CompareOutput.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-CompareOutput.php`, `reference/DATACLASS-PHP/base/data-CompareOutputBase.php`
- disposable runtime root: `work/sample-packs/pattern05-companion-declarations-basic/output/`

検証:

```bash
php mtool/scripts/check_sample10_compare_output_companion_declarations_outputs.php
make pattern05-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample10_compare_output_companion_declarations_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern05-companion-declarations-basic/output/DATACLASS-PHP/data-CompareOutput.php
work/sample-packs/pattern05-companion-declarations-basic/output/DATACLASS-PHP/base/data-CompareOutputBase.php
```
