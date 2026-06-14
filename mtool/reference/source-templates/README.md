# source-templates

- Canonical source-output generator の固定骨格をこの tree に置く。
- 分岐や metadata 解釈は `mtool/app/` の PHP code に残し、安定した出力形だけを template file 化する。
- 現在は `canonical-dataclass-php/`、`canonical-dbaccess-php/`、`legacy-dataclass-php/` をこの方式で使う。
