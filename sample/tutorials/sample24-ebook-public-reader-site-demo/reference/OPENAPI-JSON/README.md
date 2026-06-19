# OpenAPI Artifact

- source output: `SAMPLE24/OPENAPI-JSON`
- display name: `Sample24 OpenAPI JSON`
- function count: `5`
- proxy base URL: `http://127.0.0.1:8082/runs/proxy/SAMPLE24/API-PROXY-SERVER`

Files:

- `openapi.json` contains the generated OpenAPI 3.0.3 document.
- `build-plan.json` records the single-function proxy targets used to emit the spec.

Viewer:

- Lab/Admin viewer route: `/runs/swagger/SAMPLE24?source_output_key=OPENAPI-JSON`

Notes:

- This artifact models the generated single-function proxy runtime contract.
- Request bodies stay JSON `POST`.
- Response envelopes always include `_status` and `Message`.
