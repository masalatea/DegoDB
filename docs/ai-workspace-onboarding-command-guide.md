# AI Workspace Onboarding Command Guide / AI workspace onboarding command guide

English companion:
Use this guide when Codex, Claude, or a human user wants to initialize the Mtool AI workspace layout for a project. Start with a dry-run JSON preflight, ask the user for approval, and only then run the approved apply command.

この文書は、Codex / Claude / 利用者が Mtool AI workspace を初期化するための恒久導線です。  
まず dry-run JSON preflight で内容を確認し、利用者に確認してから、明示承認付き apply を実行します。

## いつ使うか

使う場面:

- ユーザ project の近くに `mtool-workspace/` を用意したい
- Mtool 開発中に `work/<project-key>/` 配下で AI workspace を試したい
- Codex / Claude に「この Mtool を使いたい」と Git URL や repo path とともに渡す
- scan / design brief / task packet / proposal / validation などの置き場所を先に揃えたい

使わない場面:

- DB import、scan、generation、validation、copy/adaptation を今すぐ実行したい
- 既存 project file を直接書き換えたい
- Mtool-owned `mtool-project/` の中身を AI が直接編集したい

この command は workspace layout を初期化する入口であり、application 生成や project 変更の実行 command ではありません。

## 最初に dry-run する

通常の user project では、まず project root を指定して JSON preflight を見ます。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --json
```

この段階では filesystem write はありません。  
AI は JSON の `preflight` を読み、少なくとも次を利用者に説明します。

- selected profile
- workspace root
- `mtool-project/` が Mtool-owned であること
- 作成予定 directory
- 作成予定 manifest
- warning / error
- apply してよいか

## 利用者に確認する

AI は dry-run 結果を読んだ後、具体的に確認します。

例:

> `/path/to/user-project/mtool-workspace` に Mtool workspace を作ります。  
> `mtool-project/` は Mtool-owned として扱い、AI は直接編集しません。  
> `inputs/`、`design-briefs/`、`task-packets/`、`proposals/`、`validation/` などは標準 workspace directory として使います。  
> この内容で初期化してよいですか？

過去の一般的な「継続」は、この apply 承認にはなりません。dry-run 結果を示した上で、その workspace plan への明示確認を取ります。

## 承認後に apply する

利用者がその workspace plan に同意した場合だけ、apply を実行します。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --mode=apply \
  --approve \
  --json
```

成功時は missing directory と missing manifest だけが作られます。既存 manifest は上書きされません。

## warning がある場合

relative path などの warning がある場合、command はそのままでは apply できません。  
AI は warning を説明し、解消するか、利用者が明示的に受け入れた場合だけ `--accept-warnings` を付けます。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=relative-project \
  --mode=apply \
  --approve \
  --accept-warnings \
  --json
```

通常は absolute path に直す方を優先します。

## workspace profile

### project-local

通常利用の default です。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --json
```

default workspace:

```text
/path/to/user-project/mtool-workspace
```

### mtool-work

Mtool 開発や sample 検証では `mtool_home/work/<project-key>` を使えます。

```bash
php mtool/scripts/init_ai_workspace.php \
  --profile=mtool-work \
  --mtool-home=/path/to/DegoDB \
  --project-key=my-check \
  --json
```

default workspace:

```text
/path/to/DegoDB/work/my-check
```

### external

明示 workspace root を使う場合です。

```bash
php mtool/scripts/init_ai_workspace.php \
  --workspace-root=/path/to/shared/workspace \
  --json
```

`--workspace-root` は `MTOOL_AI_WORKSPACE_ROOT` より優先されます。

## role mapping

標準 directory を別の場所に寄せる場合は role mapping を使います。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --role=task_packets=notes/mtool-task-packets \
  --json
```

外部の Obsidian vault などを参照する場合は external role にします。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --external-role=design_briefs=/Users/me/Obsidian/Mtool \
  --json
```

使わない role は disabled にできます。

```bash
php mtool/scripts/init_ai_workspace.php \
  --project-root=/path/to/user-project \
  --disable-role=logs \
  --json
```

external role directory は Mtool が作成しません。disabled role は作成対象から外れます。

## 出力の読み方

`--json` の top-level は次です。

```text
preflight
apply
```

dry-run では `apply` は `null` です。

見る場所:

- `preflight.ok`
- `preflight.errors`
- `preflight.resolution.workspace_root`
- `preflight.resolution.profile`
- `preflight.initialization_preflight.create_directories`
- `preflight.initialization_preflight.write_manifests`
- `preflight.initialization_preflight.skip_manifests`
- `preflight.can_run_apply`
- `apply.ok`
- `apply.created_directories`
- `apply.written_manifests`
- `apply.skipped_manifests`

## 安全境界

- default は `dry-run`
- `apply` には `--mode=apply --approve` が必要
- warning は解消または `--accept-warnings` が必要
- 既存 manifest は上書きしない
- external role directory は作らない
- disabled role は作成対象にしない
- scan は開始しない
- import はしない
- generation はしない
- validation はしない
- copy/adaptation workflow は実行しない
- `mtool-project/` は Mtool-owned として扱い、AI は直接編集しない

## 次に進む作業

workspace 初期化後、AI は必要に応じて標準 directory に artifact を作ります。

- `inputs/`: user-provided input
- `scans/`: scan/cache result
- `design-briefs/`: AI が整理した設計 brief
- `task-packets/`: Mtool に渡す task packet
- `proposals/`: proposal / copy plan
- `review-artifacts/`: review 用 artifact
- `validation/`: validation summary

ただし、どの artifact を作るかは利用者の次の具体 task によります。workspace 初期化だけで自動的に scan や生成へ進みません。

## 関連文書

- [AI Task-Packet Workflow](ai-task-packet-workflow.md)
- [Storage And State Model](storage-and-state-model.md)
- [Security And Data Handling](security-and-data-handling.md)
- [Current Plans](current-plans.md)

