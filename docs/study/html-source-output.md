# HTML Source Output

English companion:
This study note uses `sample11-html-template-output` to show the smallest HTML Source Output publish flow after the DBAccess tutorial lane.

`sample11-html-template-output` は、DBAccess lane の後に HTML template / HTML Source Output を見るための tutorial です。
`html-module-catalog` strategy で curated module tree を publish し、actual output を reference と比較します。

## 実行

```bash
make sample11-pack-runtime-test
```

## 読むファイル

- [sample11 README](../../sample/tutorials/sample11-html-template-output/README.md)
- [seed](../../sample/tutorials/sample11-html-template-output/seed/)
- [reference/HTML-PAGE](../../sample/tutorials/sample11-html-template-output/reference/HTML-PAGE/)
- [module source](../../mtool/reference/html-modules/sample11/HTML-PAGE/current/)

## 見るポイント

- `project_source_outputs.source_output_key = HTML-PAGE`
- `artifact_strategy = html-module-catalog`
- `source_template_dir = catalog://html-module/SAMPLE11/HTML-PAGE`
- `project_html_definitions` / `project_html_parameters`
- `html_templates` / `html_template_parameters`

`sample11` では proxy runtime、OpenAPI、LanguageResource は扱いません。OpenAPI は `sample13` で扱います。
