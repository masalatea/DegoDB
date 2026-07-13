# Optional fallback qualification checkpoint / optional fallback認定checkpoint

Date: 2026-07-13

## Summary

#883 closes the optional local fallback lane as a supported boundary checkpoint, with one important qualification:

- deterministic/fake-transport behavior is tested and can be part of normal gates;
- real local Ollama generation is reachable and opt-in, but not reliable enough to become a normal test gate.

## Completed evidence

Recent commits provide:

- generic deterministic source scan;
- generic task-bound local fallback runner and CLI;
- configurable local-only Ollama adapter;
- shared formal/advisory validation pipeline metadata;
- AI handoff/operator guide for copy/adapt and validator rerun.

Normal tests do not require Ollama, a model download, network access to a paid provider, or credentials.

## Real local Ollama preflight

Local preflight on 2026-07-13:

- `ollama` command exists at `/opt/homebrew/bin/ollama`;
- `http://127.0.0.1:11434/api/tags` responded;
- `qwen2.5-coder:7b` is installed locally.

## Real smoke attempt

Command:

```bash
php mtool/scripts/create_sample19_schema_proposal_task.php /private/tmp/mtool-ollama-smoke-codex-20260713

php mtool/scripts/run_sample19_local_ai_proposal.php \
  --task=/private/tmp/mtool-ollama-smoke-codex-20260713/task.json \
  --execute-local-fallback \
  --ollama-model=qwen2.5-coder:7b
```

Result:

- interrupted after about 240 seconds with no provider response;
- no `input/fallback-candidate.json` was written;
- no `output/candidate.json` was written;
- no formal artifact was mutated.

This is acceptable for the supported boundary: real local model generation remains an explicit, manual smoke and not a CI/default test.

## Supported boundary after checkpoint

Supported:

- task packet scan and validation are deterministic;
- fallback candidates are advisory;
- fallback and formal candidates share the same validator;
- local Ollama config is local-only and credential-free;
- Codex/Claude can use fallback output as a draft only after user-visible review;
- formal acceptance still requires writing `output/candidate.json` and running the declared validator.

Not claimed:

- model quality;
- model latency;
- guaranteed JSON correctness from local models;
- automatic promotion from fallback to formal candidate;
- automatic metadata apply/import/build/publish.

## Integration decision

Keep the optional fallback lane integrated as helper infrastructure and documentation.

Do not make real local Ollama generation part of `make test` or normal PR gates.
