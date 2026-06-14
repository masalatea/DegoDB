# Docker Base Assets

`docker/` は repo 全体で共有する base Docker 資産だけを置く。

- `compose.yaml` から参照する共通 image build 定義を置く
- 全 scenario 共通の base initdb SQL を置く
- `Project 1 = MTOOL` 専用 override や seed は置かない

## Contents

- `php-apache/`
  - `web-admin` / `web-lab` 共通の base image 定義
- `mariadb/config-initdb/`
  - `db-config` 共通の base schema / metadata initdb
- `mariadb/lab-initdb/`
  - `db-lab` 共通の lab initdb
- `compose.yaml` の `lab-db-ui`
  - `db-lab` をブラウザで触るための軽量 open-source DB UI

## Scenario Boundaries

- `Project 1 = MTOOL` の compose override と seed は `mtool/docker/` に置く
- `Project 2+` の sample pack は `sample/<category>/<pack>/` に置く
- 検証 scenario は `tests/scenarios/` に置く
