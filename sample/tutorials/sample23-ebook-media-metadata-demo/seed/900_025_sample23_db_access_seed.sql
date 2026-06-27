SET @sample23_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE23'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample23_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample23_project_id;

INSERT INTO project_db_access_classes (
    project_id,
    source_name,
    store_base_path,
    is_autoload,
    notes,
    source_of_truth,
    last_detected_dbaccess_file,
    last_detected_data_file
) VALUES (
    @sample23_project_id,
    'ebook_media_asset',
    '',
    0,
    'Ebook media metadata sample with public delivery reads and minimal asset metadata writes.',
    'manual',
    '',
    ''
);

SET @sample23_db_access_class_id = LAST_INSERT_ID();

INSERT INTO project_db_access_functions (
    db_access_class_id,
    function_name,
    function_list_order,
    function_suffix,
    action_type,
    data_class_base_name,
    target_table_name,
    parameter_type,
    select_by_distinct,
    sort_order_columns,
    memo,
    limit_parameter_type,
    limit_fixed_parameter,
    or_group_type,
    single_proxy_auth_type,
    single_proxy_single_get_function_name,
    is_blob_target,
    detected_signature,
    detected_line,
    source_of_truth
) VALUES
(@sample23_db_access_class_id, 'GetPublicEbookMediaDeliveryList', 10, '', 'SELECTLIST', 'EbookMediaDelivery', 'ebook_media_delivery', '', 0, 'ebook_media_delivery.sort_order asc, ebook_media_delivery.delivery_id asc', 'List published media delivery metadata for a book.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookMediaDeliveryList($param_EbookMediaDelivery_BookSlug_where)', 10, 'manual'),
(@sample23_db_access_class_id, 'GetPublicEbookMediaAsset', 20, '', 'SELECTSINGLE', 'EbookMediaDelivery', 'ebook_media_delivery', '', 0, '', 'Get one published media asset by asset slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookMediaAsset($param_EbookMediaDelivery_AssetSlug_where)', 20, 'manual'),
(@sample23_db_access_class_id, 'InsertEbookMediaAsset', 30, '', 'INSERT', 'EbookMediaAsset', 'ebook_media_asset', 'classobject', 0, '', 'Register ebook media asset metadata. The file body is not uploaded by this sample.', '', '', '', 'NoSecurity', '', 0, 'public function InsertEbookMediaAsset($EbookMediaAssetObj)', 30, 'manual'),
(@sample23_db_access_class_id, 'UpdateEbookMediaAssetMetadata', 40, '', 'UPDATE', 'EbookMediaAsset', 'ebook_media_asset', 'classobject', 0, '', 'Update ebook media asset URL, checksum, size, version, and status metadata.', '', '', '', 'NoSecurity', '', 0, 'public function UpdateEbookMediaAssetMetadata($EbookMediaAssetObj)', 40, 'manual');

SET @sample23_list_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample23_db_access_class_id AND function_name = 'GetPublicEbookMediaDeliveryList');
SET @sample23_detail_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample23_db_access_class_id AND function_name = 'GetPublicEbookMediaAsset');
SET @sample23_insert_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample23_db_access_class_id AND function_name = 'InsertEbookMediaAsset');
SET @sample23_update_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample23_db_access_class_id AND function_name = 'UpdateEbookMediaAssetMetadata');

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
) VALUES
(@sample23_list_function_id, 'ebook_media_delivery', '', 'delivery_id', '', '', 'deliveryId', 0, 10, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'book_id', '', '', 'bookId', 0, 20, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'asset_id', '', '', 'assetId', 0, 40, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'asset_slug', '', '', 'assetSlug', 0, 50, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'asset_kind', '', '', 'assetKind', 0, 60, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'display_role', '', '', 'displayRole', 0, 70, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'display_name', '', '', 'displayName', 0, 80, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'public_url', '', '', 'publicUrl', 0, 90, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'mime_type', '', '', 'mimeType', 0, 100, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'file_size_bytes', '', '', 'fileSizeBytes', 0, 110, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'sha256', '', '', 'sha256', 0, 120, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'version_label', '', '', 'versionLabel', 0, 130, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'sort_order', '', '', 'sortOrder', 0, 140, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'is_primary_asset', '', '', 'isPrimaryAsset', 0, 150, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'delivery_id', '', '', 'deliveryId', 0, 10, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'book_id', '', '', 'bookId', 0, 20, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'asset_id', '', '', 'assetId', 0, 40, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'asset_slug', '', '', 'assetSlug', 0, 50, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'asset_kind', '', '', 'assetKind', 0, 60, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'display_role', '', '', 'displayRole', 0, 70, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'display_name', '', '', 'displayName', 0, 80, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'public_url', '', '', 'publicUrl', 0, 90, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'mime_type', '', '', 'mimeType', 0, 100, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'file_size_bytes', '', '', 'fileSizeBytes', 0, 110, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'sha256', '', '', 'sha256', 0, 120, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'version_label', '', '', 'versionLabel', 0, 130, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'sort_order', '', '', 'sortOrder', 0, 140, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'is_primary_asset', '', '', 'isPrimaryAsset', 0, 150, 'manual');

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
) VALUES
(@sample23_list_function_id, 'ebook_media_delivery', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample23_list_function_id, 'ebook_media_delivery', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample23_detail_function_id, 'ebook_media_delivery', '', 'asset_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual');

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample23_insert_function_id, 'asset_slug', 'argument', '', '', 10, 'manual'),
(@sample23_insert_function_id, 'asset_kind', 'argument', '', '', 20, 'manual'),
(@sample23_insert_function_id, 'display_name', 'argument', '', '', 30, 'manual'),
(@sample23_insert_function_id, 'public_url', 'argument', '', '', 40, 'manual'),
(@sample23_insert_function_id, 'storage_path', 'argument', '', '', 50, 'manual'),
(@sample23_insert_function_id, 'mime_type', 'argument', '', '', 60, 'manual'),
(@sample23_insert_function_id, 'file_size_bytes', 'argument', '', '', 70, 'manual'),
(@sample23_insert_function_id, 'sha256', 'argument', '', '', 80, 'manual'),
(@sample23_insert_function_id, 'version_label', 'argument', '', '', 90, 'manual'),
(@sample23_insert_function_id, 'status', 'fixed', 'varchar', 'draft', 100, 'manual'),
(@sample23_insert_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 110, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample23_update_function_id, 'display_name', 'argument', '', '', 10, 'manual'),
(@sample23_update_function_id, 'public_url', 'argument', '', '', 20, 'manual'),
(@sample23_update_function_id, 'storage_path', 'argument', '', '', 30, 'manual'),
(@sample23_update_function_id, 'mime_type', 'argument', '', '', 40, 'manual'),
(@sample23_update_function_id, 'file_size_bytes', 'argument', '', '', 50, 'manual'),
(@sample23_update_function_id, 'sha256', 'argument', '', '', 60, 'manual'),
(@sample23_update_function_id, 'version_label', 'argument', '', '', 70, 'manual'),
(@sample23_update_function_id, 'status', 'argument', '', '', 80, 'manual'),
(@sample23_update_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 90, 'manual');

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
) VALUES
(@sample23_update_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample23_project_id = NULL;
SET @sample23_db_access_class_id = NULL;
SET @sample23_list_function_id = NULL;
SET @sample23_detail_function_id = NULL;
SET @sample23_insert_function_id = NULL;
SET @sample23_update_function_id = NULL;
