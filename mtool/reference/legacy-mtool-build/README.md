# Legacy mtool build reference / 旧 mtool build 参照スナップショット

This directory stores a copied reference snapshot of the legacy mtool build implementation. / このディレクトリは、旧 mtool build 実装の参照用コピーです。

Snapshot contents / スナップショット内容:

- `mtool_lib/` contains only the legacy `lib_mtool_build*.php` build-related files. / `mtool_lib/` には旧 `lib_mtool_build*.php` 系の build 関連ファイルだけを置いています。
- Legacy generated `dbclasses` are intentionally not duplicated here; use `mtool/reference/legacy-dbclasses/` instead. / 旧生成 `dbclasses` はここに重複配置せず、`mtool/reference/legacy-dbclasses/` を参照します。
- The snapshot is committed for content review; git does not preserve original filesystem timestamps. / この snapshot は内容確認用に commit します。git は元ファイルシステムの timestamp を保持しません。
- `.gitattributes` disables whitespace checks for this directory so historical formatting is not rewritten during review. / `.gitattributes` でこの directory の whitespace check を外し、履歴由来の formatting を review 中に書き換えないようにしています。
- This is reference material only and must not be used directly by the current runtime, generator, Docker image, or artifact bundle. / これは参照資料専用であり、現行のランタイム、ジェネレータ、Docker イメージ、成果物バンドルから直接使ってはいけません。

Safety review / 安全確認:

- A keyword scan found no literal secret keys, API keys, private keys, bearer tokens, or email addresses in this copied subset. / このコピー範囲では、秘密鍵、API キー、秘密鍵ファイル、Bearer token、メールアドレスの実値はキーワード走査で見つかっていません。
- Historical absolute paths in the copied legacy files were replaced with external variables such as `$MTOOL_LIB` and `$MTOOL_SETTINGS_DIR`. / コピーした旧ファイル内の過去の絶対パスは、`$MTOOL_LIB` や `$MTOOL_SETTINGS_DIR` のような外部定義変数へ置き換えています。
- `DBUserPassword` and `__DB_PASSWORD__` are field and template placeholder names, not copied credential values. / `DBUserPassword` と `__DB_PASSWORD__` はフィールド名・テンプレートプレースホルダであり、コピーされた認証情報の実値ではありません。
- `php -l` reports no syntax errors for the copied build files. It reports legacy deprecation warnings in `lib_mtool_build_proxyclient.php` for semicolon-style `case` statements. / コピーした build file は `php -l` で syntax error なしです。`lib_mtool_build_proxyclient.php` では legacy な semicolon-style `case` statement に対する deprecation warning が出ます。

Primary review targets / 主な確認対象:

- `mtool_lib/lib_mtool_build.php`
- `mtool_lib/lib_mtool_build_dataclass.php`
- `mtool_lib/lib_mtool_build_dafunc.php`
- `mtool_lib/lib_mtool_build_html.php`
- `mtool_lib/lib_mtool_build_proxyserver.php`
- `mtool_lib/lib_mtool_build_template.php`
- `mtool/reference/legacy-dbclasses/`
- `mtool/reference/legacy-mtool-templates/`
