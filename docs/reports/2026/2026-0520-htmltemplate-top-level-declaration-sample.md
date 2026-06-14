# 2026-05-20 htmlTemplate Top-Level Declaration Sample

## 結論

- `sample19-htmltemplate-top-level-declaration` を追加し、`htmlTemplate` の 4-class top-level declaration variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-htmlTemplate.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで top-level-declaration lane は、`SpecContent` / `ProjectUser` / `htmlTemplate` の representative 3 亜種すべてを durable reference compare 付きで回せる状態になった。

## 背景

- top-level-declaration lane は `SpecContent` の最小形、`ProjectUser` の中位形、`htmlTemplate` の support class 同居 variant の 3 亜種に整理して段階的に sample 化してきた。
- `htmlTemplate` は `class_count=4` で、bottom helper function 群に加えて `SortedhtmlTemplateDataContainer` を base 側へ送り、さらに trailing enum 2 件も保持するため、この lane では最も複雑な representative だった。
- ここを sample 化できれば、top-level lane は broad string assertion だけでなく file-based reference compare でも代表形を一巡できる。

## 実装

- `mtool/scripts/lib/sample19_htmltemplate_top_level_declaration_output_check.php`
- `mtool/scripts/check_sample19_htmltemplate_top_level_declaration_outputs.php`
- `tests/Integration/Sample19HtmlTemplateTopLevelDeclarationOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample19-htmltemplate-top-level-declaration/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample19 と最新 test count を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample19_htmltemplate_top_level_declaration_outputs.php`
- `make test`
  - `51 tests / 1138 assertions`

## 次

1. top-level-declaration lane の representative 拡張は一巡したので、次は host-only helper inventory 整理か別 lane の複雑側整理へ戻る
2. runtime promote は引き続き sample gate 緑化後にだけ行い、`original-codes/` は host-only reference のまま維持する
