# pattern09-top-level-declaration-single

- 役割: legacy `data-SpecContent.php` の 1-class top-level declaration variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-SpecContent.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-SpecContent.php`, `reference/DATACLASS-PHP/base/data-SpecContentBase.php`
- disposable runtime root: `work/sample-packs/pattern09-top-level-declaration-single/output/`

検証:

```bash
php mtool/scripts/check_sample17_speccontent_top_level_declaration_outputs.php
make pattern09-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample17_speccontent_top_level_declaration_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern09-top-level-declaration-single/output/DATACLASS-PHP/data-SpecContent.php
work/sample-packs/pattern09-top-level-declaration-single/output/DATACLASS-PHP/base/data-SpecContentBase.php
```
