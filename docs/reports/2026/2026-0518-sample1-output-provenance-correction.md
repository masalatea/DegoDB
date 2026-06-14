# Sample1 Output Provenance Correction

## 背景

- `sample/sample1-simple-table/reference/DATACLASS-PHP/data-Article.php` は、`Sample 1` の design metadata を現行 mtool へ読み込ませて生成した actual output ではなかった。
- `Sample 1` に置く output sample は、実際にツールが設計情報を読んで出した結果だけに限定するべきであり、Codex がそれっぽく補った file は置かない。

## 実施

- `sample/sample1-simple-table/reference/DATACLASS-PHP/data-Article.php` を削除した。
- `sample/sample1-simple-table/seed/900_030_sample1_source_output_seed.sql` は `DATACLASS-PHP` row を消す cleanup seed に差し替えた。
- `sample/sample1-simple-table/README.md` と `sample/README.md` を、`Sample 1` は metadata-only であること、sample `reference/` は provenance が明確な実 source だけを置くことが分かる形に更新した。
- root `README.md` と `docs/internal/source-output-path-policy.md` にも同じ rule を反映した。

## 結果

- `Sample 1` は 1 table / 1 data class の canonical metadata sample としてだけ残る。
- actual generator 未対応の output を、手書きや AI 補完で sample に見せかける状態は解消した。
- 今後 `sample/<pack>/reference/` へ置く file は、actual tool output か provenance を確認できる legacy curated source に限定する。
