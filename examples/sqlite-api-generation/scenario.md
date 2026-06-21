# Scenario / シナリオ

Status: `DRAFT`

## Background / 背景

A team keeps lightweight project tasks in a local SQLite database. / チームは軽量な project task を local SQLite database に保存しています。

They want to expose a small API for internal tooling without hand-writing repetitive data access code. / 反復的な data access code を手書きせずに、internal tool 向けの小さな API を出したいと考えています。

## Domain / ドメイン

- `projects` stores task board projects.
- `tasks` stores tasks within a project.
- `tasks.status` is string-coded for the example.

## Important Behavior / 重要な振る舞い

- `project_key` and `task_key` are public identifiers.
- `tasks.internal_note` must not be returned from public API responses.
- Archived projects should not be shown by default in project list APIs.

## First API Surface / 最初の API surface

- `GET /api/projects`
- `GET /api/projects/{project_key}/tasks`
- `POST /api/projects/{project_key}/tasks`
- `PATCH /api/tasks/{task_key}`
