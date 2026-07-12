# Task Review-Artifact Consumption Preflight

Status: `DONE`

## Route and selection

Add a parallel authenticated GET-only route:

`GET /projects/SAMPLE19/schema-proposal-tasks/{task_id}/review`

The first slice accepts task IDs matching `sample19-schema-proposal-[a-f0-9]{12}` and resolves only `work/ai-tasks/<task_id>`. No request path, filename, query override, symlink escape, latest alias, upload, or arbitrary project selector is accepted.

## Gate and authorization

- default-off `MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED`;
- existing shared-contract project bootstrap and authentication;
- exact project `SAMPLE19`;
- GET only; no POST route or form;
- disabled/unknown/missing/invalid task fails closed.

## Integrity order

1. Resolve confined task root and reject symlink/path escape.
2. Read `task.json`, declared source/canonical inputs, validation output, and review artifact.
3. Run task contract validation.
4. Recompute source/canonical hashes against task declarations.
5. Require validation `ok=true`, `stage=review_artifact_ready`, `mutation_performed=false`.
6. Recompute candidate and review-artifact hashes and compare with validation output.
7. Decode/validate review artifact and require its derivation candidate/snapshot hashes to match.
8. Independently exact-verify declared versus derived canonical diff again.
9. Only then render through the existing read-only review presentation.

The route never trusts validation JSON alone and never regenerates or repairs a missing artifact.

## Presentation

Reuse the existing safety/source/entity/evidence/diff/question/assumption view, but accurately label:

- AI-authored interactive candidate;
- task ID;
- candidate hash;
- Mtool-derived canonical diff and derivation version;
- review artifact hash;
- `mutation=false`, proposal-only, apply unsupported.

The deterministic fixture route remains unchanged.

## Zero-authority boundary

The page contains no form, button, script, execution binding, approve/apply/import link, candidate editor, fallback trigger, task-state mutation, or artifact promotion. It does not run Codex, Claude, Ollama, validation, generation, SQL, build, or publish.

## Coverage

Fast tests must cover disabled, auth/bootstrap delegation, invalid task ID, path confinement, missing files, task/input/validation/candidate/review hash mismatches, invalid derivation/diff, successful AI provenance rendering, and zero action controls. Browser/HTTP promotion is separate.

## Next

#781 implements the confined loader and parallel read-only route with fast coverage only. It performs no AI/provider execution and no task/output mutation.
