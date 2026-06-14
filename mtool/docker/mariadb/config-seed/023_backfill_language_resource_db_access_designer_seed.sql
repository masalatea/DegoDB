-- Canonical DB Access designer backfill for LanguageResource-related sources.
-- 020_project_db_access_designer_seed.sql does not currently contain these rows,
-- so self-generated runtime generation falls back to legacy delegates for them.

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_select_wheres`;
CREATE TEMPORARY TABLE `tmp_backfill_language_resource_select_wheres` (
    `source_name` VARCHAR(191) NOT NULL,
    `function_name` VARCHAR(191) NOT NULL,
    `target_table_name` VARCHAR(191) NOT NULL,
    `target_table_alias_name` VARCHAR(191) NOT NULL,
    `target_table_column_name` VARCHAR(191) NOT NULL,
    `parameter_type` VARCHAR(32) NOT NULL,
    `parameter_data_type` VARCHAR(32) NOT NULL,
    `fixed_parameter` TEXT NOT NULL,
    `another_table_name` VARCHAR(191) NOT NULL,
    `another_table_alias_name` VARCHAR(191) NOT NULL,
    `another_field_name` VARCHAR(191) NOT NULL,
    `join_type` VARCHAR(32) NOT NULL,
    `or_group` VARCHAR(64) NOT NULL,
    `relational_operator` VARCHAR(32) NOT NULL,
    `where_order` INT UNSIGNED NOT NULL,
    `source_of_truth` VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tmp_backfill_language_resource_select_wheres` (
    `source_name`,
    `function_name`,
    `target_table_name`,
    `target_table_alias_name`,
    `target_table_column_name`,
    `parameter_type`,
    `parameter_data_type`,
    `fixed_parameter`,
    `another_table_name`,
    `another_table_alias_name`,
    `another_field_name`,
    `join_type`,
    `or_group`,
    `relational_operator`,
    `where_order`,
    `source_of_truth`
) VALUES
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResourceAdditionalGroupAssignment', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourcePID', 'anotherfield', '', '', 'LanguageResource', '', 'PID', '', '', '', 3, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResourceAdditionalGroupAssignment', '', 'ProjectPID', 'anotherfield', '', '', 'LanguageResource', '', 'ProjectPID', '', '', '', 4, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'PID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'KeyName', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourcePID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourceGroupPID', 'anotherfield', '', '', 'LanguageResourceGroup', '', 'PID', 'left', '', '', 3, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'ProjectPID', 'anotherfield', '', '', 'LanguageResourceGroup', '', 'ProjectPID', 'left', '', '', 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourcePID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourceLangPID', 'anotherfield', '', '', 'LanguageResourceLang', '', 'PID', '', '', '', 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourcePID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourceLangPID', 'argument', '', '', '', '', '', '', '', '', 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourceLangPID', 'anotherfield', '', '', 'LanguageResourceLang', '', 'PID', '', '', '', 5, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'PID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'LanguageResourceLangPID', 'anotherfield', '', '', 'LanguageResourceLang', '', 'PID', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', 'anotherfield', '', '', 'LanguageResourceGroup', '', 'PID', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', 'anotherfield', '', '', 'LanguageResourceGroup', '', 'PID', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'PID', 'argument', '', '', '', '', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', 'argument', '', '', '', '', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', 'anotherfield', '', '', 'LanguageResourceGroup', '', 'PID', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'IsDefault', 'fixed', '', '1', '', '', '', '', '', '', 1, 'seed-legacy');

DELETE target
FROM project_db_access_function_select_wheres AS target
INNER JOIN project_db_access_functions AS f
    ON f.id = target.db_access_function_id
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT DISTINCT source_name, function_name
    FROM `tmp_backfill_language_resource_select_wheres`
) AS seed
    ON seed.source_name = c.source_name
   AND seed.function_name = f.function_name
WHERE p.project_key = 'MTOOL';

INSERT INTO project_db_access_function_select_wheres (
    db_access_function_id,
    target_table_name,
    target_table_alias_name,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    another_table_name,
    another_table_alias_name,
    another_field_name,
    join_type,
    or_group,
    relational_operator,
    where_order,
    source_of_truth
)
SELECT
    f.id,
    seed.target_table_name,
    seed.target_table_alias_name,
    seed.target_table_column_name,
    seed.parameter_type,
    seed.parameter_data_type,
    seed.fixed_parameter,
    seed.another_table_name,
    seed.another_table_alias_name,
    seed.another_field_name,
    seed.join_type,
    seed.or_group,
    seed.relational_operator,
    seed.where_order,
    seed.source_of_truth
FROM `tmp_backfill_language_resource_select_wheres` AS seed
INNER JOIN projects AS p
    ON p.project_key = 'MTOOL'
INNER JOIN project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = seed.source_name
INNER JOIN project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = seed.function_name;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_select_wheres`;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_select_target_fields`;
CREATE TEMPORARY TABLE `tmp_backfill_language_resource_select_target_fields` (
    `source_name` VARCHAR(191) NOT NULL,
    `function_name` VARCHAR(191) NOT NULL,
    `target_table_name` VARCHAR(191) NOT NULL,
    `target_table_alias_name` VARCHAR(191) NOT NULL,
    `target_table_column_name` VARCHAR(191) NOT NULL,
    `target_table_column_prefix` VARCHAR(191) NOT NULL,
    `target_table_column_suffix` VARCHAR(191) NOT NULL,
    `store_class_field_name` VARCHAR(191) NOT NULL,
    `group_by_target` TINYINT(1) NOT NULL,
    `field_list_order` INT UNSIGNED NOT NULL,
    `source_of_truth` VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tmp_backfill_language_resource_select_target_fields` (
    `source_name`,
    `function_name`,
    `target_table_name`,
    `target_table_alias_name`,
    `target_table_column_name`,
    `target_table_column_prefix`,
    `target_table_column_suffix`,
    `store_class_field_name`,
    `group_by_target`,
    `field_list_order`,
    `source_of_truth`
) VALUES
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 3, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'KeyForUpdate', '', '', 'KeyForUpdate', 0, 4, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'SortGroup', '', '', 'SortGroup', 0, 5, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'KeyName', '', '', 'KeyName', 0, 6, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'KeyNameForXcode', '', '', 'KeyNameForXcode', 0, 7, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'UWPTargetProperty', '', '', 'UWPTargetProperty', 0, 8, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'IsResourceFixed', '', '', 'IsResourceFixed', 0, 9, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceList', 'LanguageResource', '', 'UseDefaultIfCaptionIsBlank', '', '', 'UseDefaultIfCaptionIsBlank', 0, 10, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 3, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'KeyForUpdate', '', '', 'KeyForUpdate', 0, 4, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'SortGroup', '', '', 'SortGroup', 0, 5, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'KeyName', '', '', 'KeyName', 0, 6, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'KeyNameForXcode', '', '', 'KeyNameForXcode', 0, 7, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'UWPTargetProperty', '', '', 'UWPTargetProperty', 0, 8, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'IsResourceFixed', '', '', 'IsResourceFixed', 0, 9, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceOfAdditionalGroupList', 'LanguageResource', '', 'UseDefaultIfCaptionIsBlank', '', '', 'UseDefaultIfCaptionIsBlank', 0, 10, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 3, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'KeyForUpdate', '', '', 'KeyForUpdate', 0, 4, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'SortGroup', '', '', 'SortGroup', 0, 5, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'KeyName', '', '', 'KeyName', 0, 6, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'KeyNameForXcode', '', '', 'KeyNameForXcode', 0, 7, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'UWPTargetProperty', '', '', 'UWPTargetProperty', 0, 8, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'IsResourceFixed', '', '', 'IsResourceFixed', 0, 9, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResource', 'LanguageResource', '', 'UseDefaultIfCaptionIsBlank', '', '', 'UseDefaultIfCaptionIsBlank', 0, 10, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 3, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'KeyForUpdate', '', '', 'KeyForUpdate', 0, 4, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'SortGroup', '', '', 'SortGroup', 0, 5, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'KeyName', '', '', 'KeyName', 0, 6, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'KeyNameForXcode', '', '', 'KeyNameForXcode', 0, 7, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'UWPTargetProperty', '', '', 'UWPTargetProperty', 0, 8, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'IsResourceFixed', '', '', 'IsResourceFixed', 0, 9, 'seed-legacy'),
    ('LanguageResource', 'GetLanguageResourceByKeyName', 'LanguageResource', '', 'UseDefaultIfCaptionIsBlank', '', '', 'UseDefaultIfCaptionIsBlank', 0, 10, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourcePID', '', '', 'LanguageResourcePID', 0, 3, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceAdditionalGroupAssignment', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 4, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'GetLanguageResourceAdditionalGroupAssignmentList', 'LanguageResourceGroup', '', 'Name', '', '', 'LanguageResourceGroupName', 0, 5, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourcePID', '', '', 'LanguageResourcePID', 0, 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'LanguageResourceLangPID', '', '', 'LanguageResourceLangPID', 0, 5, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'Caption', '', '', 'Caption', 0, 6, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceCaption', '', 'CaptionAutoTranslated', '', '', 'CaptionAutoTranslated', 0, 7, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaptionList', 'LanguageResourceLang', '', 'TemplateKey', '', '', 'LanguageResourceLangTemplateKey', 0, 8, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourcePID', '', '', 'LanguageResourcePID', 0, 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'LanguageResourceLangPID', '', '', 'LanguageResourceLangPID', 0, 5, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'Caption', '', '', 'Caption', 0, 6, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceCaption', '', 'CaptionAutoTranslated', '', '', 'CaptionAutoTranslated', 0, 7, 'seed-legacy'),
    ('LanguageResourceCaption', 'GetLanguageResourceCaption', 'LanguageResourceLang', '', 'TemplateKey', '', '', 'LanguageResourceLangTemplateKey', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'Name', '', '', 'Name', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'FunctionNamePrefix', '', '', 'FunctionNamePrefix', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'FunctionNameSuffix', '', '', 'FunctionNameSuffix', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'FilenameSuffixForPHP', '', '', 'FilenameSuffixForPHP', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'FilenameSuffix', '', '', 'FilenameSuffix', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'FilenameForXcode', '', '', 'FilenameForXcode', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroupList', 'LanguageResourceGroup', '', 'LastModifiedDT', '', '', 'LastModifiedDT', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'ProjectPID', '', '', 'ProjectPID', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'Name', '', '', 'Name', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'FunctionNamePrefix', '', '', 'FunctionNamePrefix', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'FunctionNameSuffix', '', '', 'FunctionNameSuffix', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'FilenameSuffixForPHP', '', '', 'FilenameSuffixForPHP', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'FilenameSuffix', '', '', 'FilenameSuffix', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'FilenameForXcode', '', '', 'FilenameForXcode', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroup', 'GetLanguageResourceGroup', 'LanguageResourceGroup', '', 'LastModifiedDT', '', '', 'LastModifiedDT', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'LangForGoogle', '', '', 'LanguageResourceLangLangForGoogle', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'PID', '', '', 'PID', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'ProjectPID', '', '', 'ProjectPID', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceGroupLang', '', 'LanguageResourceLangPID', '', '', 'LanguageResourceLangPID', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'Caption', '', '', 'LanguageResourceLangCaption', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'IsDefault', '', '', 'LanguageResourceLangIsDefault', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'LangForCS', '', '', 'LanguageResourceLangLangForCS', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'LangForAndroid', '', '', 'LanguageResourceLangLangForAndroid', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'LangForiOS', '', '', 'LanguageResourceLangLangForiOS', 0, 10, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'GetLanguageResourceGroupLangList', 'LanguageResourceLang', '', 'TemplateKey', '', '', 'LanguageResourceLangTemplateKey', 0, 11, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'Name', '', '', 'LanguageResourceGroupName', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'LastModifiedDT', '', '', 'LanguageResourceGroupLastModifiedDT', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'FunctionNamePrefix', '', '', 'LanguageResourceGroupFunctionNamePrefix', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'FunctionNameSuffix', '', '', 'LanguageResourceGroupFunctionNameSuffix', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'FilenameSuffix', '', '', 'LanguageResourceGroupFilenameSuffix', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'FilenameForXcode', '', '', 'LanguageResourceGroupFilenameForXcode', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroup', '', 'FilenameSuffixForPHP', '', '', 'LanguageResourceGroupFilenameSuffixForPHP', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'PID', '', '', 'PID', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', '', '', 'ProjectPID', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 10, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectSourceOutputPID', '', '', 'ProjectSourceOutputPID', 0, 11, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'Name', '', '', 'LanguageResourceGroupName', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'LastModifiedDT', '', '', 'LanguageResourceGroupLastModifiedDT', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'FunctionNamePrefix', '', '', 'LanguageResourceGroupFunctionNamePrefix', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'FunctionNameSuffix', '', '', 'LanguageResourceGroupFunctionNameSuffix', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'FilenameSuffix', '', '', 'LanguageResourceGroupFilenameSuffix', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'FilenameForXcode', '', '', 'LanguageResourceGroupFilenameForXcode', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroup', '', 'FilenameSuffixForPHP', '', '', 'LanguageResourceGroupFilenameSuffixForPHP', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'PID', '', '', 'PID', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', '', '', 'ProjectPID', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 10, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutputForTheGroupList', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectSourceOutputPID', '', '', 'ProjectSourceOutputPID', 0, 11, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'Name', '', '', 'LanguageResourceGroupName', 0, 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'LastModifiedDT', '', '', 'LanguageResourceGroupLastModifiedDT', 0, 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'FunctionNamePrefix', '', '', 'LanguageResourceGroupFunctionNamePrefix', 0, 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'FunctionNameSuffix', '', '', 'LanguageResourceGroupFunctionNameSuffix', 0, 4, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'FilenameSuffix', '', '', 'LanguageResourceGroupFilenameSuffix', 0, 5, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'FilenameForXcode', '', '', 'LanguageResourceGroupFilenameForXcode', 0, 6, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroup', '', 'FilenameSuffixForPHP', '', '', 'LanguageResourceGroupFilenameSuffixForPHP', 0, 7, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'PID', '', '', 'PID', 0, 8, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectPID', '', '', 'ProjectPID', 0, 9, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'LanguageResourceGroupPID', '', '', 'LanguageResourceGroupPID', 0, 10, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'GetLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupProjectSourceOutput', '', 'ProjectSourceOutputPID', '', '', 'ProjectSourceOutputPID', 0, 11, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'FilenameSuffix', '', '', 'FilenameSuffix', 0, 2, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'TemplateKey', '', '', 'TemplateKey', 0, 3, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'IsDefault', '', '', 'IsDefault', 0, 4, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'Caption', '', '', 'Caption', 0, 5, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'LangForCS', '', '', 'LangForCS', 0, 6, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'LangForAndroid', '', '', 'LangForAndroid', 0, 7, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'LangForiOS', '', '', 'LangForiOS', 0, 8, 'seed-legacy'),
    ('LanguageResourceLang', 'GetLanguageResourceLangList', 'LanguageResourceLang', '', 'LangForGoogle', '', '', 'LangForGoogle', 0, 9, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'PID', '', '', 'PID', 0, 1, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'FilenameSuffix', '', '', 'FilenameSuffix', 0, 2, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'TemplateKey', '', '', 'TemplateKey', 0, 3, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'IsDefault', '', '', 'IsDefault', 0, 4, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'Caption', '', '', 'Caption', 0, 5, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'LangForCS', '', '', 'LangForCS', 0, 6, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'LangForAndroid', '', '', 'LangForAndroid', 0, 7, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'LangForiOS', '', '', 'LangForiOS', 0, 8, 'seed-legacy'),
    ('LanguageResourceLang', 'GetDefault', 'LanguageResourceLang', '', 'LangForGoogle', '', '', 'LangForGoogle', 0, 9, 'seed-legacy');

DELETE target
FROM project_db_access_function_select_target_fields AS target
INNER JOIN project_db_access_functions AS f
    ON f.id = target.db_access_function_id
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT DISTINCT source_name, function_name
    FROM `tmp_backfill_language_resource_select_target_fields`
) AS seed
    ON seed.source_name = c.source_name
   AND seed.function_name = f.function_name
WHERE p.project_key = 'MTOOL';

INSERT INTO project_db_access_function_select_target_fields (
    db_access_function_id,
    target_table_name,
    target_table_alias_name,
    target_table_column_name,
    target_table_column_prefix,
    target_table_column_suffix,
    store_class_field_name,
    group_by_target,
    field_list_order,
    source_of_truth
)
SELECT
    f.id,
    seed.target_table_name,
    seed.target_table_alias_name,
    seed.target_table_column_name,
    seed.target_table_column_prefix,
    seed.target_table_column_suffix,
    seed.store_class_field_name,
    seed.group_by_target,
    seed.field_list_order,
    seed.source_of_truth
FROM `tmp_backfill_language_resource_select_target_fields` AS seed
INNER JOIN projects AS p
    ON p.project_key = 'MTOOL'
INNER JOIN project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = seed.source_name
INNER JOIN project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = seed.function_name;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_select_target_fields`;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_insert_target_fields`;
CREATE TEMPORARY TABLE `tmp_backfill_language_resource_insert_target_fields` (
    `source_name` VARCHAR(191) NOT NULL,
    `function_name` VARCHAR(191) NOT NULL,
    `target_table_column_name` VARCHAR(191) NOT NULL,
    `parameter_type` VARCHAR(32) NOT NULL,
    `parameter_data_type` VARCHAR(32) NOT NULL,
    `fixed_parameter` TEXT NOT NULL,
    `field_list_order` INT UNSIGNED NOT NULL,
    `source_of_truth` VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tmp_backfill_language_resource_insert_target_fields` (
    `source_name`,
    `function_name`,
    `target_table_column_name`,
    `parameter_type`,
    `parameter_data_type`,
    `fixed_parameter`,
    `field_list_order`,
    `source_of_truth`
) VALUES
    ('LanguageResource', 'InsertLanguageResource', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'LanguageResourceGroupPID', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'KeyForUpdate', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'SortGroup', 'argument', '', '', 4, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'KeyName', 'argument', '', '', 5, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'KeyNameForXcode', 'argument', '', '', 6, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'UWPTargetProperty', 'argument', '', '', 7, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'IsResourceFixed', 'argument', '', '', 8, 'seed-legacy'),
    ('LanguageResource', 'InsertLanguageResource', 'UseDefaultIfCaptionIsBlank', 'argument', '', '', 9, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'InsertLanguageResourceAdditionalGroupAssignment', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'InsertLanguageResourceAdditionalGroupAssignment', 'LanguageResourcePID', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'InsertLanguageResourceAdditionalGroupAssignment', 'LanguageResourceGroupPID', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'LanguageResourcePID', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'LanguageResourceGroupPID', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'LanguageResourceLangPID', 'argument', '', '', 4, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'Caption', 'argument', '', '', 5, 'seed-legacy'),
    ('LanguageResourceCaption', 'InsertLanguageResourceCaption', 'CaptionAutoTranslated', 'argument', '', '', 6, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'Name', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'FunctionNamePrefix', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'FunctionNameSuffix', 'argument', '', '', 4, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'FilenameSuffixForPHP', 'argument', '', '', 5, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'FilenameSuffix', 'argument', '', '', 6, 'seed-legacy'),
    ('LanguageResourceGroup', 'InsertLanguageResourceGroup', 'FilenameForXcode', 'argument', '', '', 7, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'InsertLanguageResourceGroupLang', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'InsertLanguageResourceGroupLang', 'LanguageResourceGroupPID', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'InsertLanguageResourceGroupLang', 'LanguageResourceLangPID', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'InsertLanguageResourceGroupProjectSourceOutput', 'ProjectPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'InsertLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupPID', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'InsertLanguageResourceGroupProjectSourceOutput', 'ProjectSourceOutputPID', 'argument', '', '', 3, 'seed-legacy');

DELETE target
FROM project_db_access_function_insert_target_fields AS target
INNER JOIN project_db_access_functions AS f
    ON f.id = target.db_access_function_id
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT DISTINCT source_name, function_name
    FROM `tmp_backfill_language_resource_insert_target_fields`
) AS seed
    ON seed.source_name = c.source_name
   AND seed.function_name = f.function_name
WHERE p.project_key = 'MTOOL';

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
)
SELECT
    f.id,
    seed.target_table_column_name,
    seed.parameter_type,
    seed.parameter_data_type,
    seed.fixed_parameter,
    seed.field_list_order,
    seed.source_of_truth
FROM `tmp_backfill_language_resource_insert_target_fields` AS seed
INNER JOIN projects AS p
    ON p.project_key = 'MTOOL'
INNER JOIN project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = seed.source_name
INNER JOIN project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = seed.function_name;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_insert_target_fields`;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_update_target_fields`;
CREATE TEMPORARY TABLE `tmp_backfill_language_resource_update_target_fields` (
    `source_name` VARCHAR(191) NOT NULL,
    `function_name` VARCHAR(191) NOT NULL,
    `target_table_column_name` VARCHAR(191) NOT NULL,
    `parameter_type` VARCHAR(32) NOT NULL,
    `parameter_data_type` VARCHAR(32) NOT NULL,
    `fixed_parameter` TEXT NOT NULL,
    `field_list_order` INT UNSIGNED NOT NULL,
    `source_of_truth` VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tmp_backfill_language_resource_update_target_fields` (
    `source_name`,
    `function_name`,
    `target_table_column_name`,
    `parameter_type`,
    `parameter_data_type`,
    `fixed_parameter`,
    `field_list_order`,
    `source_of_truth`
) VALUES
    ('LanguageResource', 'UpdateLanguageResource', 'KeyForUpdate', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'SortGroup', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'KeyName', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'KeyNameForXcode', 'argument', '', '', 4, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'UWPTargetProperty', 'argument', '', '', 5, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'IsResourceFixed', 'argument', '', '', 6, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'UseDefaultIfCaptionIsBlank', 'argument', '', '', 7, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageGroup', 'LanguageResourceGroupPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'Caption', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'CaptionAutoTranslated', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'Name', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'FunctionNamePrefix', 'argument', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'FunctionNameSuffix', 'argument', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'FilenameSuffixForPHP', 'argument', '', '', 4, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'FilenameSuffix', 'argument', '', '', 5, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'FilenameForXcode', 'argument', '', '', 6, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLastModifiedDT', 'LastModifiedDT', 'fixed', 'raw', 'now()', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'UpdateLanguageResourceGroupProjectSourceOutput', 'LanguageResourceGroupPID', 'argument', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'UpdateLanguageResourceGroupProjectSourceOutput', 'ProjectSourceOutputPID', 'argument', '', '', 2, 'seed-legacy');

DELETE target
FROM project_db_access_function_update_target_fields AS target
INNER JOIN project_db_access_functions AS f
    ON f.id = target.db_access_function_id
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT DISTINCT source_name, function_name
    FROM `tmp_backfill_language_resource_update_target_fields`
) AS seed
    ON seed.source_name = c.source_name
   AND seed.function_name = f.function_name
WHERE p.project_key = 'MTOOL';

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
)
SELECT
    f.id,
    seed.target_table_column_name,
    seed.parameter_type,
    seed.parameter_data_type,
    seed.fixed_parameter,
    seed.field_list_order,
    seed.source_of_truth
FROM `tmp_backfill_language_resource_update_target_fields` AS seed
INNER JOIN projects AS p
    ON p.project_key = 'MTOOL'
INNER JOIN project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = seed.source_name
INNER JOIN project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = seed.function_name;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_update_target_fields`;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_update_delete_wheres`;
CREATE TEMPORARY TABLE `tmp_backfill_language_resource_update_delete_wheres` (
    `source_name` VARCHAR(191) NOT NULL,
    `function_name` VARCHAR(191) NOT NULL,
    `target_table_column_name` VARCHAR(191) NOT NULL,
    `parameter_type` VARCHAR(32) NOT NULL,
    `parameter_data_type` VARCHAR(32) NOT NULL,
    `fixed_parameter` TEXT NOT NULL,
    `or_group` VARCHAR(64) NOT NULL,
    `relational_operator` VARCHAR(32) NOT NULL,
    `where_order` INT UNSIGNED NOT NULL,
    `source_of_truth` VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tmp_backfill_language_resource_update_delete_wheres` (
    `source_name`,
    `function_name`,
    `target_table_column_name`,
    `parameter_type`,
    `parameter_data_type`,
    `fixed_parameter`,
    `or_group`,
    `relational_operator`,
    `where_order`,
    `source_of_truth`
) VALUES
    ('LanguageResource', 'UpdateLanguageResource', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageResource', 'KeyForUpdate', 'argument', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageGroup', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'UpdateLanguageGroup', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResource', 'DeleteLanguageResource', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResource', 'DeleteLanguageResource', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'DeleteLanguageResourceAdditionalGroupAssignment', 'LanguageResourcePID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'DeleteLanguageResourceAdditionalGroupAssignment', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceAdditionalGroupAssignment', 'DeleteLanguageResourceAdditionalGroupAssignment', 'LanguageResourceGroupPID', 'argument', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'ProjectPID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'LanguageResourcePID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'LanguageResourceGroupPID', 'argument', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceCaption', 'UpdateLanguageResourceCaption', 'LanguageResourceLangPID', 'argument', '', '', '', '', 4, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLanguageResourceGroup', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLastModifiedDT', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'UpdateLastModifiedDT', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroup', 'DeleteLanguageResourceGroup', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroup', 'DeleteLanguageResourceGroup', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'DeleteLanguageResourceGroupLang', 'ProjectPID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'DeleteLanguageResourceGroupLang', 'LanguageResourceGroupPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupLang', 'DeleteLanguageResourceGroupLang', 'LanguageResourceLangPID', 'argument', '', '', '', '', 3, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'UpdateLanguageResourceGroupProjectSourceOutput', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'UpdateLanguageResourceGroupProjectSourceOutput', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'DeleteLanguageResourceGroupProjectSourceOutput', 'PID', 'argument', '', '', '', '', 1, 'seed-legacy'),
    ('LanguageResourceGroupProjectSourceOutput', 'DeleteLanguageResourceGroupProjectSourceOutput', 'ProjectPID', 'argument', '', '', '', '', 2, 'seed-legacy');

DELETE target
FROM project_db_access_function_update_delete_wheres AS target
INNER JOIN project_db_access_functions AS f
    ON f.id = target.db_access_function_id
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT DISTINCT source_name, function_name
    FROM `tmp_backfill_language_resource_update_delete_wheres`
) AS seed
    ON seed.source_name = c.source_name
   AND seed.function_name = f.function_name
WHERE p.project_key = 'MTOOL';

INSERT INTO project_db_access_function_update_delete_wheres (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    or_group,
    relational_operator,
    where_order,
    source_of_truth
)
SELECT
    f.id,
    seed.target_table_column_name,
    seed.parameter_type,
    seed.parameter_data_type,
    seed.fixed_parameter,
    seed.or_group,
    seed.relational_operator,
    seed.where_order,
    seed.source_of_truth
FROM `tmp_backfill_language_resource_update_delete_wheres` AS seed
INNER JOIN projects AS p
    ON p.project_key = 'MTOOL'
INNER JOIN project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = seed.source_name
INNER JOIN project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = seed.function_name;

DROP TEMPORARY TABLE IF EXISTS `tmp_backfill_language_resource_update_delete_wheres`;
