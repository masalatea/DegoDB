# Agent Instructions

- Treat DB metadata and generated artifacts as source-of-truth inputs.
- Do not invent table meaning, relationship intent, or migration safety.
- Mark unknown meaning as unknown when metadata does not explain it.
- Review `risky-areas.md` before editing schema-dependent code.
- Prefer generated DataClass / DBAccess surfaces before custom SQL.
