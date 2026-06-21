# Legacy mtool templates reference / 旧 mtool テンプレート参照スナップショット

This directory stores committed reference copies of legacy mtool system-default templates used by the old generator. / このディレクトリは、旧ジェネレータが実際に使っていた旧 mtool の system-default テンプレートを、commit 済みの参照用スナップショットとして置きます。

Copied template groups / コピーしたテンプレート群:

- `dbclasses_template_system_default/` for Data Class and DB Access output in PHP, C#, Java, Objective-C, and Swift. / PHP、C#、Java、Objective-C、Swift の Data Class と DB Access 出力用の `dbclasses_template_system_default/`。
- `proxyclient_template_system_default/` for proxy client output in PHP, C#, Java, Objective-C, and Swift. / PHP、C#、Java、Objective-C、Swift の proxy client 出力用の `proxyclient_template_system_default/`。
- `dbaas_proxyclient_template_system_default/` for DBaaS proxy client output in PHP, C#, Java, Objective-C, and Swift. / PHP、C#、Java、Objective-C、Swift の DBaaS proxy client 出力用の `dbaas_proxyclient_template_system_default/`。
- `proxyserver_template_system_default/php/` and `dbaas_proxyserver_template_system_default/php/` for PHP proxy server output. / PHP proxy server 出力用の `proxyserver_template_system_default/php/` と `dbaas_proxyserver_template_system_default/php/`。
- `unit_test_template_system_default/php/` and `unit_test_template_system_default/cs/` for legacy unit-test output. / 旧 unit-test 出力用の `unit_test_template_system_default/php/` と `unit_test_template_system_default/cs/`。
- `html_template_system_default/php/` for PHP HTML output. / PHP HTML 出力用の `html_template_system_default/php/`。
- `language_resource/` for legacy language-resource output in PHP, C#, Java, Objective-C, and Swift. / PHP、C#、Java、Objective-C、Swift の旧 language-resource 出力用の `language_resource/`。
- `project_settings_system_default/` for legacy datatype translation, nullable handling, initialization, and DB Access default-check settings. / 旧 datatype 変換、nullable 処理、初期化、DB Access default-check 設定用の `project_settings_system_default/`。

Legacy output matrix / 旧出力対応表:

| Area | PHP | C# | Java | Objective-C | Swift |
| --- | --- | --- | --- | --- | --- |
| Data Class | Generated | Generated | Generated | Generated | Generated |
| DB Access | Generated | Generated | Generated | Generated | Generated |
| Proxy Client | Template-backed | Template-backed | Template-backed | Template-backed | Template-backed |
| DBaaS Proxy Client | Template-backed | Template-backed | Template-backed | Template-backed | Template-backed |
| Proxy Server / API | PHP-oriented | Not claimed | Not claimed | Not claimed | Not claimed |
| HTML output | PHP-oriented | Not supported in legacy build path | Not supported in legacy build path | Not supported in legacy build path | Not supported in legacy build path |
| Language Resource | Template-backed | Template-backed | Template-backed | Template-backed | Template-backed |

Notes / 注記:

- The strongest multi-language evidence is under `dbclasses_template_system_default/`, which was used by the legacy generator for Data Class and DB Access output in PHP, C#, Java, Objective-C, and Swift. / 多言語対応の根拠として最も強いのは、旧ジェネレータが PHP、C#、Java、Objective-C、Swift の Data Class と DB Access 出力に使っていた `dbclasses_template_system_default/` です。
- `project_settings_system_default/` is included because the templates depend on legacy datatype and initialization rules. / `project_settings_system_default/` は、テンプレートが旧 datatype や初期化ルールに依存するため含めています。
- Proxy server output is PHP-oriented in the copied templates; C# proxy server directories are retained only when present in the source tree. / proxy server 出力はコピーしたテンプレート上では PHP 中心で、C# proxy server ディレクトリはコピー元に存在する場合のみ残しています。
- `old/` subdirectories are intentionally excluded to keep this reference focused on the active legacy template set. / `old/` サブディレクトリは、参照対象を当時の主なテンプレート群に絞るため意図的に除外しています。
- Historical absolute paths in copied PHP templates were replaced with external variables such as `$MTOOL_LIB` and `$MTOOL_WORK_LIB`. / コピーした PHP テンプレート内の過去の絶対パスは、`$MTOOL_LIB` や `$MTOOL_WORK_LIB` のような外部定義変数へ置き換えています。
- `.gitattributes` disables whitespace checks for this directory so historical template formatting is not rewritten during review. / `.gitattributes` でこの directory の whitespace check を外し、履歴由来の template formatting を review 中に書き換えないようにしています。
- `.resx` / `.resw` `PublicKeyToken` values are .NET public assembly identifiers, not copied credentials. / `.resx` / `.resw` の `PublicKeyToken` は .NET の公開アセンブリ識別子であり、コピーされた認証情報ではありません。
- This is reference material only and must not be used directly by the current runtime, generator, Docker image, or artifact bundle. / これは参照資料専用であり、現行のランタイム、ジェネレータ、Docker イメージ、成果物バンドルから直接使ってはいけません。
- Review this directory before any public release; it was copied from local historical project material. / 公開前には、このディレクトリを必ず確認してください。ローカルに残っていた過去プロジェクト資料からコピーしたものです。
