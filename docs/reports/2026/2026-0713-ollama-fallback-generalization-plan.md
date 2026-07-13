# Ollama Fallback Generalization Plan

Status: `PLANNED_INDEPENDENT`

## Current result

The earlier work is still present and usable as a bounded Sample19 first slice. It was not intended to make Ollama the normal AI path.

- Codex or Claude reads the generated task packet and remains the primary workflow.
- Every packet contains a deterministic, hash-bound `input/scan.json` with JSON pointers and types but no inferred schema.
- `mtool/scripts/run_sample19_local_ai_proposal.php` provides an explicit local Ollama fallback using `qwen2.5-coder:7b` at `127.0.0.1`.
- The command refuses to run without both a concrete task and `--execute-local-fallback`.
- Its result is written as advisory input, not as the formal agent-owned candidate.
- The candidate uses the same Mtool validator and cannot apply metadata, build, publish, or mutate a database.

The first slice therefore proves feasibility and safety, but it is coupled to Sample19 through the command name, prompt construction, model, endpoint, and artifact assumptions.

## Product direction

Generalize the useful parts without creating a second primary workflow:

1. Codex/Claude is the normal path when available.
2. A deterministic local scan gives either a human or AI bounded source facts.
3. Ollama is an optional, no-charge, local candidate generator when the primary path is unavailable or intentionally avoided.
4. All providers converge on the same task packet and authoritative Mtool validation pipeline.
5. Nothing auto-runs, and no candidate is accepted or applied without explicit review and the normal Mtool boundary.

This is independent from the SQLite-to-MySQL promotion lane. It may progress separately, but it must not silently expand that migration's scope.

## Planned work units

| Order | Work unit | Exit condition |
| --- | --- | --- |
| 877 | Inventory and generalization preflight | Reusable contracts, Sample19 coupling, non-goals, and first code slice are explicit. |
| 878 | Generic deterministic scan contract | Any supported task packet can carry a validated advisory scan without inference. |
| 879 | Generic local-fallback CLI contract | A task-bound CLI requires explicit execution and reads only declared hash-bound inputs. |
| 880 | Configurable Ollama adapter | Local endpoint/model/runtime settings are validated and no credential or paid-provider dependency exists. |
| 881 | Shared validation integration | Fallback and primary candidates use one validator while preserving separate authority and artifact ownership. |
| 882 | Workspace handoff and guide | AI/user instructions, paths, confirmation, review, and promotion boundaries are unambiguous. |
| 883 | Qualification and checkpoint | Fake transport covers normal tests; opt-in real Ollama smoke proves one flow; supported scope is recorded. |

## Explicit non-goals

- Automatically choosing or launching Ollama.
- Treating scan output or an Ollama response as source of truth.
- Letting provider-specific logic decide whether metadata may be applied.
- Requiring Ollama or a downloaded model for the normal test suite.
- Sending project content, credentials, or personal information to an external paid provider.
- Building a general autonomous agent framework inside Mtool.

## First decision gate

Before implementation, #877 must determine whether the existing schema-proposal task packet is sufficiently generic or whether a small provider-neutral task contract is needed. The preferred first slice reuses the current task integrity and validation functions rather than duplicating them.

## Historical references

- `docs/reports/2026/2026-0712-optional-scan-ollama-fallback-alignment.md`
- `docs/ai-task-packet-workflow.md`
- archived plan items #769, #773-#775, #778, #784, and #821 in `docs/reports/2026/2026-0712-current-plan-history-archive.md`
