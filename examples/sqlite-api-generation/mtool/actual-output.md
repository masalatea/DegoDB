# Actual Output Backing / 実出力の裏付け

Status: `ACTUAL_OUTPUT_BACKED`

This example does not store generated output under its own `reference/` directory yet. / この example は、まだ自身の `reference/` directory には生成出力を保存していません。

Instead, it is backed by the current task-board tutorial that already publishes actual Mtool output. / 代わりに、既に Mtool 実出力を publish している current task-board tutorial に接続します。

## Current Actual Output / 現在の実出力

Use `sample18-mini-task-board-demo` as the current actual generated-output backing for a small task board app. / 小さな task board app の current な実生成出力の裏付けとして、`sample18-mini-task-board-demo` を使います。

Actual generated reference directories:

- `sample/tutorials/sample18-mini-task-board-demo/reference/DATACLASS-PHP/`
- `sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/`
- `sample/tutorials/sample18-mini-task-board-demo/reference/OPENAPI-JSON/`

The `HTML-PAGE` reference in sample18 is a curated HTML module output, not a generated DBAccess artifact. / sample18 の `HTML-PAGE` reference は curated HTML module output であり、生成 DBAccess artifact ではありません。

Verification commands:

```bash
make sample18-pack-runtime-test
make sample18-pack-runtime-test-sqlite
```

## Boundary / 境界

- The files above are actual Mtool generated output from `sample18`. / 上記 file は `sample18` の Mtool 実生成出力です。
- This example's `projects` / `tasks` SQLite schema is still an input draft. / この example の `projects` / `tasks` SQLite schema はまだ入力ドラフトです。
- Do not copy unrelated generated output into this example's `reference/` directory. / 無関係な生成出力をこの example の `reference/` directory へコピーしません。
- Add `examples/sqlite-api-generation/reference/` only after this exact scenario is wired into Mtool and generated. / この scenario 自体を Mtool に接続して生成した後だけ、`examples/sqlite-api-generation/reference/` を追加します。
