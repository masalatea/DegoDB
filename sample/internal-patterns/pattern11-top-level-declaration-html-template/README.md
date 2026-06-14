# pattern11-top-level-declaration-html-template

- 役割: legacy `data-htmlTemplate.php` の 4-class top-level declaration variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-htmlTemplate.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-htmlTemplate.php`, `reference/DATACLASS-PHP/base/data-htmlTemplateBase.php`
- disposable runtime root: `work/sample-packs/pattern11-top-level-declaration-html-template/output/`

検証:

```bash
php mtool/scripts/check_sample19_htmltemplate_top_level_declaration_outputs.php
make pattern11-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample19_htmltemplate_top_level_declaration_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern11-top-level-declaration-html-template/output/DATACLASS-PHP/data-htmlTemplate.php
work/sample-packs/pattern11-top-level-declaration-html-template/output/DATACLASS-PHP/base/data-htmlTemplateBase.php
```
