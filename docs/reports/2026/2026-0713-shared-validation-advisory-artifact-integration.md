# Shared validation and advisory artifact integration / 共通validation・advisory artifact統合

Date: 2026-07-13

## Summary

#881 closes the ambiguity between formal agent output and optional fallback output.

The important rule is now machine-visible:

- formal candidates live under `output/`;
- fallback candidates live under `input/` as advisory artifacts;
- both pass through `app_schema_proposal_task_validate()`;
- fallback success does not mean acceptance;
- a fallback candidate must be reviewed and copied or adapted into `output/candidate.json`, then the declared validator must be run again, before it can become the formal review artifact.

## Code changes

- `app_schema_proposal_task_validate()` now accepts optional validation context.
- Validation results include `validation_pipeline` metadata:
  - validator name;
  - task version;
  - candidate authority;
  - review artifact authority;
  - advisory flag.
- `app_task_packet_local_fallback_validate_candidate()` passes advisory context.
- Sample19 task packets expose a `validation_pipeline` section that names both formal and fallback channels and the promotion rule.

## Boundary

This does not auto-promote fallback output.

Ollama/local fallback remains useful as a draft-producing fallback, but it remains advisory until an AI/user workflow reviews it, writes a formal candidate, and re-runs the declared validator.

## Next

Proceed to #882: AI workspace handoff and operator guide.

That should document the standard paths, confirmation wording, and copy/adaptation boundary for Codex/Claude-style agents working with these artifacts.
