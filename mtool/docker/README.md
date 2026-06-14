# MTOOL Docker Assets

`mtool/docker/` は `Project 1 = MTOOL` 専用の Docker 補助資産を置く。

- base Docker 定義は root `docker/` に残す
- `Project 1 = MTOOL` 専用 seed と compose override だけをここへ集約する
- 検証 scenario は `tests/scenarios/`、`Project 2+` sample pack は `sample/` に分ける

## Contents

- `compose/01_mtool.compose.yaml`
  - `Project 1 = MTOOL` の fresh initdb 用 compose override
- `mariadb/config-seed/`
  - `Project 1 = MTOOL` の canonical bootstrap seed
- `../old/language-resource/`
  - `LanguageResource` 旧 overlay compose / seed pack の archive

## Commands

fresh volume で `Project 1 = MTOOL` を起動:

```zsh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f mtool/docker/compose/01_mtool.compose.yaml down -v
docker compose -f compose.yaml -f compose.local-db-config.yaml -f mtool/docker/compose/01_mtool.compose.yaml up -d
```

`tests/scenarios/mtool-single-proxy/` は、この core seed を土台に single-function proxy 検証用 seed を追加する。
