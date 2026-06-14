# 2026-05-20 ProjectUser Top-Level Declaration Sample

## 結論

- `sample18-projectuser-top-level-declaration` を追加し、`ProjectUser` の 3-class top-level declaration variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-ProjectUser.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで top-level-declaration lane は、`SpecContent` の最小 variant に加えて、bottom class 1 件 + helper function 群 + trailing enum 1 件を持つ中位 variant でも durable reference compare を回せるようになった。

## 背景

- top-level-declaration lane の representative 拡張は、単純形から先に固定して複雑形を後追いする 2 段で進めている。
- `sample17-speccontent-top-level-declaration` で `method + top-level helper only` の最小形は固定できたが、`ProjectUser` のような `class_count=3` かつ bottom class / trailing enum を伴う亜種はまだ file-based sample が無かった。
- `ProjectUser` は `htmlTemplate` ほど複雑ではなく、top-level lane の次段として扱いやすい。

## 実装

- `mtool/scripts/lib/sample18_projectuser_top_level_declaration_output_check.php`
- `mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php`
- `tests/Integration/Sample18ProjectUserTopLevelDeclarationOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample18-projectuser-top-level-declaration/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample18 と最新 test count を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php`
- `make test`
  - `50 tests / 1132 assertions`

## 次

1. top-level-declaration lane の残りは `htmlTemplate` なので、support class 同居 variant を sample 化して lane の複雑側を埋める
2. runtime promote は引き続き sample gate 緑化後にだけ行い、`original-codes/` は host-only reference のまま維持する
