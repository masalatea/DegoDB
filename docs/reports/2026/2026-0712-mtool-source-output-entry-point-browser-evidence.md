# Mtool Source Output entry-point browser evidence

Date: 2026-07-12

## Summary

#804 extends the MTOOL Source Output inspection browser smoke so it covers both the read-only inspection route and the canonical Source Outputs entry point.

The smoke now verifies:

- unauthenticated access to the inspection route redirects to login;
- default-off hides the canonical entry point;
- flag-on shows exactly one canonical entry point;
- the entry point href is `/projects/MTOOL/source-outputs/no-code-inspection`;
- the entry point text says it opens read-only inspection;
- the entry point explains that it does not replace canonical Source Outputs;
- the inspection route still exposes the hybrid contract marker when enabled;
- rollback-by-flag hides both the canonical entry point and inspection marker again;
- no Source Output inspection or operation POSTs occur during the browser flow.

## Browser evidence

Default-off:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "canonical_entry_point_count": 0,
  "hybrid_contract_marker_count": 0,
  "inspection_post_count": 0,
  "source_output_operation_post_count": 0,
  "request_count": 7
}
```

Flag-on:

```json
{
  "ok": true,
  "expected_state": "enabled",
  "unauthenticated_redirected_to_login": true,
  "canonical_entry_point_count": 1,
  "hybrid_contract_marker_count": 1,
  "inspection_post_count": 0,
  "source_output_operation_post_count": 0,
  "request_count": 7
}
```

Rollback-by-flag:

```json
{
  "ok": true,
  "expected_state": "off",
  "unauthenticated_redirected_to_login": true,
  "canonical_entry_point_count": 0,
  "hybrid_contract_marker_count": 0,
  "inspection_post_count": 0,
  "source_output_operation_post_count": 0,
  "request_count": 7
}
```

## Decision

The canonical entry point is now covered at the same evidence level as the inspection contract route. The entry-point lane can be closed before selecting the next contained Mtool hybrid replacement step.

## Next lane

#805: Mtool inspection entry-point evidence lane closure.

