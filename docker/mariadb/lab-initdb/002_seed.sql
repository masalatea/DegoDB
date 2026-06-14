INSERT INTO lab_experiments (
    experiment_key,
    project_key,
    name,
    execution_status,
    runtime_target,
    executed_by,
    notes
) VALUES
    (
        'EXP-BOOTSTRAP-001',
        'MTOOL',
        'Bootstrap Health Check',
        'ready',
        'local-docker',
        'lab-user',
        'Docker 上の最小構成で request / DB / auth の足場を確認するための実験です。'
    ),
    (
        'EXP-COMPARE-001',
        'MTOOL',
        'Compare Output Prototype',
        'draft',
        'local-docker',
        NULL,
        '旧 compare_output 系を再設計する前に、実験用 workflow を切り出すための雛形です。'
    )
ON DUPLICATE KEY UPDATE
    project_key = VALUES(project_key),
    name = VALUES(name),
    execution_status = VALUES(execution_status),
    runtime_target = VALUES(runtime_target),
    executed_by = VALUES(executed_by),
    notes = VALUES(notes);
