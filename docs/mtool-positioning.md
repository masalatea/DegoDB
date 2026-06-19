# Mtool Positioning

English companion:
This living reference explains Mtool's positioning, strengths, limitations, extension boundary, and comparison with ORM-like tools. It also records what the ebook CMS sample lane demonstrates about the intended metadata-driven generation workflow.

## Purpose

This document is the living reference for Mtool's positioning, strengths, limitations, and comparison with adjacent tools.

Historical reports may capture a point-in-time judgment. This document can be updated as Mtool's generator, runtime, sample lane, and use cases evolve.

## Current Positioning

Mtool is best understood as a metadata-driven output generator and tutorial sample builder.

It is not only an ORM / OR mapper. It uses project metadata to publish multiple artifacts around a database-backed application shape.

Current generated / published artifact categories include:

- DataClass
- DBAccess
- OpenAPI
- HTML artifact
- authenticated proxy
- project metadata bundle

Mtool is especially useful when the goal is to show a reproducible path from a user-facing business idea to generated technical artifacts.

Mtool's core policy is not to chase every complex application case directly inside the generator core. Most practical cases can be normalized and simplified into repeatable metadata patterns. Mtool optimizes that common path first, and treats the remaining complex cases as explicit extension points.

In short:

- optimize the normalized / simplified 90%;
- keep the generated path predictable and repeatable;
- avoid making the core flow complex for rare cases;
- handle cases that cannot be normalized as explicit extensions or custom runtime work;
- keep the boundary clear between generated reusable classes and hand-written / AI-written application-specific code.

In the ebook CMS sample lane, the intended path is:

```text
JSON-first user idea
-> AI-interpreted DB / API / output metadata
-> Mtool-generated artifacts
-> reference output comparison
```

## Compared With ORM-like Tools

Here, ORM / OR mapper-like tools means developer tools such as Prisma, TypeORM, Sequelize, SQLAlchemy, Doctrine, Rails ActiveRecord, and Django ORM.

### Typical ORM / OR Mapper Role

General ORM / OR mapper-like tools mainly do the following:

- Make database rows and relations easier to use from application code.
- Manage models, queries, and migrations in code-first or schema-first workflows.
- Assume developers understand schema, relations, queries, and transactions.
- Leave API specs, HTML artifacts, authenticated proxies, and project metadata bundles to separate frameworks, tools, or custom code.

### Mtool Role

Mtool mainly does the following:

- Publishes DataClass, DBAccess, OpenAPI, HTML artifacts, auth proxies, and metadata bundles from project metadata.
- Works well with a JSON-first story where AI interprets a non-DB user's input into normalized DB / API / output metadata.
- Fixes generated artifacts under `reference/` for tutorial and regression checks.
- Focuses on explaining and generating DB / API / output artifact structure rather than implementing all runtime application business logic.

## Extension Boundary

Mtool's policy does not mean complex cases are impossible. It means the default generated path should stay simple, reusable, and predictable, while application-specific complexity is written at the boundary.

The generated DataClass output can be reused as the common data representation. When custom behavior is needed, the project can extend the generated base data class and add hand-written or AI-written domain methods in the derived class.

The generated DBAccess output can also be reused as the common database access foundation. When a query, transaction, workflow, or integration is outside Mtool's current generator support, the project can extend the DBAccess class or add custom runtime code around it.

In other words, unsupported generator features are not a hard limit on the application. The intended boundary is:

- let Mtool generate the normalized reusable foundation;
- reuse the generated framework pieces as much as possible;
- write only the unsupported or application-specific part by hand or with AI assistance;
- keep those custom parts outside the generator core unless they become common enough to normalize later.

## Advantages

- Multiple output types can be generated from the same project metadata.
- It can show a full sample path from input story to DB / API metadata, generated artifact, and reference comparison.
- It focuses effort on normalized and simplified metadata patterns, where most sample and application scaffolding work can be made repeatable.
- AI-designed DB / API structures can be converted into repeatable seed and checker workflows.
- Tutorial lanes can include OpenAPI, HTML, auth proxy, and metadata bundle output without hand-writing each artifact.
- Verification can focus on metadata and generated output diffs instead of large amounts of hand-written application code.

## Limitations

- Flexible query composition inside application code is usually stronger in conventional ORM tools.
- Complex transactions, domain services, and business logic are not Mtool's primary generator focus, but they can be implemented by extending the generated DataClass / DBAccess foundation or by adding custom runtime code.
- Migration workflow, IDE completion, ecosystem, and framework integration are often more mature in established ORM tools.
- Daily production application model operations, test fixtures, and developer ergonomics may be stronger in conventional ORM stacks.
- Mtool's strength is metadata / artifact generation, so detailed runtime application behavior still needs separate design and implementation at the extension boundary.

## Sample Lane Findings

The `sample19-26` ebook / headless CMS lane showed that Mtool can reduce sample implementation time substantially when the scope is intentionally kept to generated artifacts and reproducible tutorial packs.

In that lane, an initial conservative estimate of 16-24 hours was higher than actual effort. The implementation landed in a few hours, roughly 70-85% faster than the original estimate, because:

- production CMS features were deliberately out of scope;
- existing runtime pack, Source Output, and checker patterns were reusable;
- generated artifacts were produced by Mtool and fixed as actual references;
- manual work concentrated on seed, checker, reference promotion, and README updates.

This result matches Mtool's intended policy: simplify and normalize the common path first, optimize that repeatable path, and keep exceptional complexity at clear extension boundaries outside the core sample flow.

This speed should be understood as a result for Mtool sample creation, not as a direct estimate for building a production-ready CMS.

## Maintenance Notes

- Keep dated reports as historical records.
- Update this document when Mtool's positioning changes.
- If comparison expands beyond ORM-like tools, add separate sections for OpenAPI generators, admin builders, low-code tools, headless CMS tools, or metadata management systems.
- Keep claims grounded in repository samples, generated artifacts, tests, and explicit scope boundaries.
