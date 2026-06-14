# Test Scenarios

`tests/scenarios/` は Docker を使う検証 scenario の置き場。

- 運用用の durable sample は `sample/`
- `Project 1 = MTOOL` の core 起動 override は `mtool/docker/compose/01_mtool.compose.yaml`
- generator / bridge / migration の補助検証は `tests/scenarios/`

各 scenario は次を持つ。

- `README.md`
- `compose.yaml`
- `run.sh`
- `seed/`
