# 2026-05-20 Project Method-And-Enum Sample

## 結論

- `sample21-project-method-and-enum` を追加し、`Project` の multi-method + top-level helper + enum variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-Project.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで method-and-enum lane は、`Req` の top-level helper あり representative、`daCustomProxy` の no-top-level 最小 representative に加えて、`Project` の multi-method representative でも durable reference compare を回せるようになった。

## 背景

- 2 段 rollout の current 方針では、simple form は direct replacement、complex/new form は focused sample を先に green にしてから広げる。
- method-and-enum lane では `sample13-req-method-and-enum` と `sample20-dacustomproxy-method-and-enum` により、top-level helper あり / なしの両側は押さえられていた。
- ただし `Project` / `ProjectSourceOutput` のような multi-method 側は未固定で、lane 代表としてはまだ軽い寄りだった。
- `Project` は curated fixture がすでにあり、`class_count=3`、wrapper method `11` 件、trailing enum `2` 件、top-level helper ありのため、次の representative として `ProjectSourceOutput` より先に選んだ。

## 実装

- `mtool/scripts/lib/sample21_project_method_and_enum_output_check.php`
- `mtool/scripts/check_sample21_project_method_and_enum_outputs.php`
- `tests/Integration/Sample21ProjectMethodAndEnumOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample21-project-method-and-enum/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/generated-code-strategy.md`
  - current sample gate / coverage に sample21 を反映した。

## 検証

- `php mtool/scripts/check_sample21_project_method_and_enum_outputs.php`
- `make test`
  - `53 tests / 1150 assertions`

## 次

1. method-and-enum lane のさらに重い representative が必要なら、次は `ProjectSourceOutput` を sample 化する
2. それ以外の complex/new form は引き続き sample gate を先に足し、runtime promote は gate 緑化後だけに絞る
3. `original-codes/` は host-only reference のまま維持し、fixture が必要な場合だけ `tests/fixtures/legacy-dbclasses/` へ最小限コピーする
