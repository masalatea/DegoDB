<?php

declare(strict_types=1);

require_once __DIR__ . '/schema_proposal.php';

const APP_MATERIAL_INSIGHT_VERSION = 'material_insight_v0';

/**
 * @param array<string,mixed> $proposal
 * @return array<string,mixed>
 */
function app_material_insight_from_schema_proposal(
    array $proposal,
    string $sourceBytes,
    string $canonicalSnapshotBytes,
): array {
    $sourceHash = hash('sha256', $sourceBytes);
    $canonicalHash = hash('sha256', $canonicalSnapshotBytes);
    $snapshot = json_decode($canonicalSnapshotBytes, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($snapshot)) {
        throw new InvalidArgumentException('Canonical snapshot must decode to an object.');
    }
    $proposalValidation = app_schema_proposal_validate($proposal);
    if (!$proposalValidation['ok']) {
        throw new InvalidArgumentException('Invalid schema proposal: ' . implode(', ', $proposalValidation['errors']));
    }
    $derivedDiff = app_schema_proposal_derive_canonical_diff($proposal, $snapshot);
    if (!$derivedDiff['ok']) {
        throw new InvalidArgumentException('Canonical diff could not be derived: ' . implode(', ', $derivedDiff['errors']));
    }

    $entities = [];
    foreach ($proposal['entities'] as $entity) {
        $relationshipKeys = [];
        foreach ($proposal['relationships'] as $relationship) {
            if (($relationship['from_entity'] ?? '') === $entity['entity_key'] || ($relationship['to_entity'] ?? '') === $entity['entity_key']) {
                $relationshipKeys[] = (string) $relationship['relationship_key'];
            }
        }
        $entities[] = [
            'entity_key' => (string) $entity['entity_key'],
            'purpose' => (string) ($entity['purpose'] ?? ''),
            'field_count' => count(is_array($entity['fields'] ?? null) ? $entity['fields'] : []),
            'key_count' => count(is_array($entity['keys'] ?? null) ? $entity['keys'] : []),
            'relationship_keys' => $relationshipKeys,
            'evidence' => $entity['evidence'] ?? [],
        ];
    }
    $entityKeys = array_values(array_map(static fn (array $entity): string => (string) $entity['entity_key'], $entities));
    $relationshipKeys = array_values(array_map(static fn (array $relationship): string => (string) $relationship['relationship_key'], $proposal['relationships']));

    return [
        'version' => APP_MATERIAL_INSIGHT_VERSION,
        'project_key' => 'SAMPLE19',
        'source' => [
            'logical_filename' => (string) ($proposal['source']['logical_filename'] ?? 'article.json'),
            'media_type' => (string) ($proposal['source']['media_type'] ?? 'application/json'),
            'sha256' => $sourceHash,
            'byte_length' => strlen($sourceBytes),
            'root_pointer' => (string) ($proposal['source']['root_pointer'] ?? '/article'),
        ],
        'basis' => [
            'kind' => 'sample19_schema_proposal_review',
            'proposal_id' => (string) $proposal['proposal_id'],
            'proposal_version' => (string) $proposal['proposal_version'],
            'canonical_snapshot_sha256' => $canonicalHash,
            'canonical_diff_derivation' => 'mtool_derived',
            'canonical_diff_count' => count($derivedDiff['diff']),
        ],
        'entities' => $entities,
        'qa_cards' => [
            [
                'question_key' => 'entities_implied',
                'answer_category' => 'structure',
                'question' => 'Which entities does this material imply?',
                'answer' => 'The material supports ' . count($entityKeys) . ' entity candidates: ' . implode(', ', $entityKeys) . '.',
                'entity_refs' => $entityKeys,
                'evidence_refs' => ['/article'],
            ],
            [
                'question_key' => 'relationships_supported',
                'answer_category' => 'relationship',
                'question' => 'Which relationships are source-backed?',
                'answer' => $relationshipKeys === []
                    ? 'No source-backed relationships were identified.'
                    : 'The source-backed relationships are ' . implode(', ', $relationshipKeys) . '.',
                'entity_refs' => $entityKeys,
                'relationship_refs' => $relationshipKeys,
                'evidence_refs' => ['/article/author', '/article/category'],
            ],
            [
                'question_key' => 'ui_outline_candidate',
                'answer_category' => 'ui_outline',
                'question' => 'What read-only UI can be generated from the same structure?',
                'answer' => 'A read-only material insight UI can show entity list, entity detail, and Q&A review cards without apply/import/build/publish actions.',
                'entity_refs' => $entityKeys,
                'evidence_refs' => ['/article'],
            ],
        ],
        'ui_outline' => [
            'mode' => 'read_only_review',
            'screens' => [
                [
                    'screen_key' => 'material_entity_list',
                    'section' => 'entity_review',
                    'purpose' => 'List normalized entities implied by the material.',
                    'entity_refs' => $entityKeys,
                    'fields' => ['entity_key', 'purpose', 'field_count', 'relationship_keys'],
                ],
                [
                    'screen_key' => 'material_qa_cards',
                    'section' => 'qa_review',
                    'purpose' => 'Show bounded Q&A cards grounded in source evidence.',
                    'qa_refs' => ['entities_implied', 'relationships_supported', 'ui_outline_candidate'],
                    'fields' => ['question', 'answer', 'evidence_refs'],
                ],
            ],
            'actions' => [],
        ],
        'prohibited_actions' => ['apply', 'import', 'build', 'publish', 'metadata_mutation', 'route_execution'],
        'validation' => [
            'status' => 'fixture_validated',
            'stages' => ['artifact_decode', 'version_project', 'source_identity', 'basis_identity', 'entity_references', 'qa_references', 'ui_outline_references', 'prohibited_actions'],
            'mutation_performed' => false,
            'warnings' => [],
        ],
    ];
}

/** @param array<string,mixed> $artifact @return array{ok:bool,errors:list<string>,warnings:list<string>} */
function app_material_insight_validate(array $artifact, ?string $sourceBytes = null, ?string $canonicalSnapshotBytes = null): array
{
    $errors = [];
    if (($artifact['version'] ?? '') !== APP_MATERIAL_INSIGHT_VERSION) {
        $errors[] = 'unsupported_material_insight_version';
    }
    if (($artifact['project_key'] ?? '') !== 'SAMPLE19') {
        $errors[] = 'material_project_must_be_SAMPLE19';
    }
    $source = is_array($artifact['source'] ?? null) ? $artifact['source'] : [];
    if (preg_match('/^[a-f0-9]{64}$/', (string) ($source['sha256'] ?? '')) !== 1) {
        $errors[] = 'invalid_source_sha256';
    } elseif ($sourceBytes !== null && !hash_equals((string) $source['sha256'], hash('sha256', $sourceBytes))) {
        $errors[] = 'source_sha256_mismatch';
    }
    if (!app_schema_proposal_json_pointer_is_valid((string) ($source['root_pointer'] ?? ''))) {
        $errors[] = 'invalid_source_root_pointer';
    }
    $basis = is_array($artifact['basis'] ?? null) ? $artifact['basis'] : [];
    if (($basis['kind'] ?? '') !== 'sample19_schema_proposal_review') {
        $errors[] = 'invalid_basis_kind';
    }
    if ($canonicalSnapshotBytes !== null && !hash_equals((string) ($basis['canonical_snapshot_sha256'] ?? ''), hash('sha256', $canonicalSnapshotBytes))) {
        $errors[] = 'canonical_snapshot_sha256_mismatch';
    }

    $entities = is_array($artifact['entities'] ?? null) ? $artifact['entities'] : [];
    if ($entities === []) {
        $errors[] = 'entities_required';
    }
    $entityKeys = [];
    foreach ($entities as $index => $entity) {
        $entityKey = is_array($entity) ? trim((string) ($entity['entity_key'] ?? '')) : '';
        if ($entityKey === '' || isset($entityKeys[$entityKey])) {
            $errors[] = ($entityKey === '' ? 'missing' : 'duplicate') . '_entity_key:' . $entityKey;
            continue;
        }
        $entityKeys[$entityKey] = true;
    }

    $qaKeys = [];
    $allowedCategories = ['structure', 'relationship', 'ui_outline'];
    foreach (is_array($artifact['qa_cards'] ?? null) ? $artifact['qa_cards'] : [] as $index => $card) {
        $questionKey = is_array($card) ? trim((string) ($card['question_key'] ?? '')) : '';
        if ($questionKey === '' || isset($qaKeys[$questionKey])) {
            $errors[] = ($questionKey === '' ? 'missing' : 'duplicate') . '_qa_card_key:' . $questionKey;
            continue;
        }
        $qaKeys[$questionKey] = true;
        $answerCategory = trim((string) ($card['answer_category'] ?? ''));
        if ($answerCategory === '' || !in_array($answerCategory, $allowedCategories, true)) {
            $errors[] = 'invalid_qa_answer_category:' . $questionKey;
        }
        $evidenceRefs = is_array($card['evidence_refs'] ?? null) ? $card['evidence_refs'] : [];
        if ($evidenceRefs === []) {
            $errors[] = 'missing_qa_evidence_refs:' . $questionKey;
        }
        foreach ($evidenceRefs as $evidenceRef) {
            if (!app_schema_proposal_json_pointer_is_valid((string) $evidenceRef)) {
                $errors[] = 'invalid_qa_evidence_ref:' . $questionKey . ':' . (string) $evidenceRef;
            }
        }
        foreach (is_array($card['entity_refs'] ?? null) ? $card['entity_refs'] : [] as $entityRef) {
            if (!isset($entityKeys[(string) $entityRef])) {
                $errors[] = 'unknown_qa_entity_ref:' . $questionKey . ':' . (string) $entityRef;
            }
        }
    }

    $uiOutline = is_array($artifact['ui_outline'] ?? null) ? $artifact['ui_outline'] : [];
    if (($uiOutline['mode'] ?? '') !== 'read_only_review') {
        $errors[] = 'ui_outline_must_be_read_only_review';
    }
    if (($uiOutline['actions'] ?? null) !== []) {
        $errors[] = 'ui_outline_actions_must_be_empty';
    }
    $allowedSections = ['entity_review', 'qa_review'];
    foreach (is_array($uiOutline['screens'] ?? null) ? $uiOutline['screens'] : [] as $screen) {
        if (!is_array($screen)) {
            continue;
        }
        $screenKey = (string) ($screen['screen_key'] ?? '');
        $section = trim((string) ($screen['section'] ?? ''));
        if ($section === '' || !in_array($section, $allowedSections, true)) {
            $errors[] = 'invalid_ui_section:' . $screenKey;
        }
        foreach (is_array($screen['entity_refs'] ?? null) ? $screen['entity_refs'] : [] as $entityRef) {
            if (!isset($entityKeys[(string) $entityRef])) {
                $errors[] = 'unknown_ui_entity_ref:' . $screenKey . ':' . (string) $entityRef;
            }
        }
        foreach (is_array($screen['qa_refs'] ?? null) ? $screen['qa_refs'] : [] as $qaRef) {
            if (!isset($qaKeys[(string) $qaRef])) {
                $errors[] = 'unknown_ui_qa_ref:' . $screenKey . ':' . (string) $qaRef;
            }
        }
    }

    $prohibited = is_array($artifact['prohibited_actions'] ?? null) ? $artifact['prohibited_actions'] : [];
    foreach (['apply', 'import', 'build', 'publish', 'metadata_mutation', 'route_execution'] as $action) {
        if (!in_array($action, $prohibited, true)) {
            $errors[] = 'missing_prohibited_action:' . $action;
        }
    }
    if (($artifact['validation']['mutation_performed'] ?? null) !== false) {
        $errors[] = 'mutation_performed_must_be_false';
    }

    return ['ok' => $errors === [], 'errors' => array_values(array_unique($errors)), 'warnings' => []];
}
