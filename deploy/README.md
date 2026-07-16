# Deployment Configuration Templates / deployment 設定 template

English companion:
`deploy/` contains checked-in templates for durable or team-use operation. It does not contain populated secrets, generated deployment bundles, or a claim of production readiness.

`deploy/` は、継続利用・team 利用向けに Git 管理できる設定 template を置く directory です。入力済み secret、生成済み deployment bundle、production readiness の claim は置きません。

## Current template / 現行 template

- [`durable-config-db.env.example`](durable-config-db.env.example)
  - external/durable MySQL・MariaDB config store、admin/lab port、local stub auth の example
  - copy 先は `.env.durable` など Git 管理外の file にする
  - password / token / API key を入力した file は commit しない

## Usage / 使い方

```bash
cp deploy/durable-config-db.env.example .env.durable
make up-durable-config-db DURABLE_ENV_FILE=.env.durable
make config-db-preflight-durable-config-db DURABLE_ENV_FILE=.env.durable
```

運用、backup、external config DB の詳細は [Config DB Externalization](../docs/config-db-externalization.md) と [Quickstart](../docs/quickstart.md) を正本にします。
