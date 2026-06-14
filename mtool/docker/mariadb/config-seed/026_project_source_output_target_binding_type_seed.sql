UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET so.target_binding_type = 'runtime'
WHERE p.project_key = 'MTOOL'
  AND so.source_output_key = 'RUNTIME-DBCLASSES';

UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET so.target_binding_type = 'custom-proxy'
WHERE p.project_key = 'MTOOL'
  AND so.source_output_key IN (
      'DBIMPORT-PROXY-SERVER',
      'DBIMPORT-PROXY-CLIENT'
  );
