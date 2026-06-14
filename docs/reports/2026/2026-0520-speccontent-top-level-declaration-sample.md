# 2026-05-20 SpecContent Top-Level Declaration Sample

## 結論

- `sample17-speccontent-top-level-declaration` を追加し、`SpecContent` の 1-class top-level declaration variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-SpecContent.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで top-level-declaration lane は、まず最も単純な `method + top-level helper only` variant を durable reference compare 付きで回せるようになった。

## 背景

- top-level-declaration lane の残件は `ProjectUser` / `SpecContent` / `htmlTemplate` の 3 件で、これまでは `LegacyTopLevelDeclarationMigrationTest.php` の文字列検証で lane 全体を見ていた。
- ただし user 方針は「simple form から先に置き換え、complex/new form は sample gate で段階的に広げる」なので、同じ lane でも最初に固定すべきなのは最小 variant である。
- `SpecContent` は `class_count=1`、wrapper method 1 件、bottom helper function 1 件、base 側へ送る trailing class なしという最も軽い top-level declaration 亜種なので、sample 化の入口に適している。

## 実装

- `mtool/scripts/lib/sample17_speccontent_top_level_declaration_output_check.php`
- `mtool/scripts/check_sample17_speccontent_top_level_declaration_outputs.php`
- `tests/Integration/Sample17SpecContentTopLevelDeclarationOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample17-speccontent-top-level-declaration/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample17 と最新 test count を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample17_speccontent_top_level_declaration_outputs.php`
- `make test`
  - `49 tests / 1126 assertions`

## 次

1. top-level-declaration lane の次候補は `ProjectUser` を優先し、`class_count=3` + helper + trailing enum を sample 化する
2. 最後に `htmlTemplate` の support class 同居 variant を sample 化して、同 lane の複雑側を埋める
3. runtime promote は引き続き sample gate 緑化後にだけ行い、`original-codes/` は host-only reference のまま維持する
