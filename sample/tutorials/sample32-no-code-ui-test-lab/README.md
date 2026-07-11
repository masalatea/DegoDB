# sample32-no-code-ui-test-lab

Dedicated no-code UI test lab sample.

- Project key: `SAMPLE32`
- Table: `no_code_lab_card`
- Source output: `NO-CODE-RUNTIME`
- Scope: list/detail/form metadata, fixed preview rows, one disabled dry-run action, and fast PHPUnit JSON/DOM contract tests.

## Run

```bash
./sample/tutorials/sample32-no-code-ui-test-lab/run.sh up
./sample/tutorials/sample32-no-code-ui-test-lab/run.sh apply-seed
make sample32-pack-runtime-test
```

## Boundary

This sample is intentionally smaller than the domain no-code samples. It exists to grow no-code UI fixtures with fast contract tests before relying on headless browser smoke tests.
