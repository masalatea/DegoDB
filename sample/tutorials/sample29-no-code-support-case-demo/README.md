# Sample29 No-Code Support Case Demo

- Role: second data-first no-code domain sample.
- Path: canonical table metadata -> shared contract -> managed operation -> `NO-CODE-RUNTIME` artifact -> generated list/detail/form preview.
- Current first-slice scope: support case read-model table with customer/context fields, no-code runtime artifact generation, and headless browser smoke for generated action dispatch.

Run:

```bash
./sample/tutorials/sample29-no-code-support-case-demo/run.sh up
./sample/tutorials/sample29-no-code-support-case-demo/run.sh apply-seed
make sample29-pack-runtime-test
make sample29-no-code-runtime-ui-smoke
bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample29-no-code-support-case-demo
bash mtool/scripts/check_sample_pack_runtime_smoke.sh --pack=sample29-no-code-support-case-demo
```

Generated runtime preview target:

```text
work/source-outputs/SAMPLE29/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE29/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE29/NO-CODE-RUNTIME/screen-definition.json
```

The generated browser smoke verifies:

- `support_case_list`
- `support_case_detail`
- `support_case_form`
- disabled action dispatch fails closed
- authorized `update_support_case` dispatch maps key/input fields into a runtime action intent
