# legacy-dbclasses fixtures

- ここには sample / migration test 用の curated legacy `dbclasses` copy だけを置く。
- 元データは `original-codes/mtool_lib/dbclasses/` だが、runtime / generator / Docker container はそちらを直接読まない。
- fixture 更新が必要なときは必要な file だけを明示的にコピーし、`original-codes/` 全体を test runtime に持ち込まない。
