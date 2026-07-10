# Cross-profile public runtime browser smoke promotion plan

Date: 2026-07-07

## Summary

#383 chooses an umbrella make target for the public no-code runtime browser smoke matrix.

Sample28, sample29, and sample31 already have individual public runtime browser smoke targets. The next small improvement is to promote them into one documented target that can be run before local stack review or push decisions.

## Planned Scope

- Add a make target that runs:
  - `sample28-no-code-public-runtime-browser-smoke`;
  - `sample29-no-code-public-runtime-browser-smoke`;
  - `sample31-no-code-public-runtime-browser-smoke`.
- Keep existing individual targets unchanged.
- Keep generated runtime contracts unchanged.

## Verification Target

The first implementation should run:

- `make sample-no-code-public-runtime-browser-smoke`
- `git diff --check`

Full `make test` can remain deferred if the change is limited to Makefile orchestration and the umbrella target runs the intended smoke matrix.

## Push Status

No push was performed for #383.
