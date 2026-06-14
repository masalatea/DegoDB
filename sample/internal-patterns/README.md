# internal-patterns

- 役割: rewrite / generator / migration contract を小さく固定して検証する internal category
- user-facing tutorial lane とは分け、pack 名は `pattern01-*` から並べる
- 個別の PHP check script / PHPUnit class 名は互換のため historical な `sample9-22` / `Sample9-22` を維持する

current file-based migration samples:

- `pattern01-default-property-split`
- `pattern02-wrapper-property-helper`
- `pattern03-method-only-split`
- `pattern04-method-and-enum-basic`
- `pattern05-companion-declarations-basic`
- `pattern06-companion-declarations-no-top-level`
- `pattern07-companion-declarations-multiclass`
- `pattern08-companion-declarations-multi-helper`
- `pattern09-top-level-declaration-single`
- `pattern10-top-level-declaration-multiclass`
- `pattern11-top-level-declaration-html-template`
- `pattern12-method-and-enum-no-top-level`
- `pattern13-method-and-enum-multimethod`
- `pattern14-method-and-enum-heavy-multimethod`

上記はすべて `tests/fixtures/legacy-dbclasses/` の curated fixture を入力にし、`reference/` と compare する migration gate です。

基本操作:

```bash
make pattern01-output-test
make pattern14-output-test
make test
```

個別の file-based sample は各 sample README の check script を直接実行してもよく、全体は `make test` が正本です。
