export type RuntimeCell = {
  value: string | number | boolean | null;
  display_value?: string;
};

export type RuntimeRow = Record<string, RuntimeCell>;

export type MtoolField = {
  field_key: string;
  label: string;
  type: string;
  is_key?: boolean;
  required?: boolean;
  readonly?: boolean;
  visibility?: string;
};

export type MtoolActionField = {
  field_key: string;
  role: 'key' | 'input';
  required?: boolean;
  client_write?: boolean;
};

export type MtoolAction = {
  action_key: string;
  label: string;
  operation_key: string;
  operation_type: string;
  enabled: boolean;
  availability: string;
  fields: MtoolActionField[];
  failed_checks?: string[];
};

export type MtoolScreen = {
  screen_key: string;
  screen_type: 'list' | 'detail' | 'form';
  screen_title: string;
  fields: MtoolField[];
  actions: MtoolAction[];
  data: {
    rows: RuntimeRow[];
  };
};

export type MtoolActionIntent = {
  intent_version: 'no-code-runtime-action-intent-v0';
  action_key: string;
  operation_key: string;
  operation_type: string;
  screen_key: string;
  contract_key: string;
  key: Record<string, string | number | boolean | null>;
  input: Record<string, string | number | boolean | null>;
  metadata: {
    source: 'sample35-capacitor-artifact-import';
    submit_boundary: 'mock-disabled';
  };
};

export function displayRuntimeValue(row: RuntimeRow, fieldKey: string): string {
  const cell = row[fieldKey];
  if (!cell) {
    return '';
  }
  if (typeof cell.display_value === 'string') {
    return cell.display_value;
  }
  return cell.value === null ? '' : String(cell.value);
}

export function runtimeInputValue(row: RuntimeRow, fieldKey: string): string {
  const cell = row[fieldKey];
  if (!cell || cell.value === null) {
    return '';
  }
  return String(cell.value);
}

export function editableFieldsForAction(action: MtoolAction, screen: MtoolScreen): MtoolField[] {
  const writableKeys = new Set(
    action.fields.filter((field) => field.role === 'input' && field.client_write !== false).map((field) => field.field_key)
  );
  return screen.fields.filter((field) => writableKeys.has(field.field_key) && !field.readonly);
}

export function requiredValidationMessages(action: MtoolAction, draft: Record<string, string>): string[] {
  return action.fields
    .filter((field) => field.role === 'input' && field.required)
    .filter((field) => (draft[field.field_key] ?? '').trim() === '')
    .map((field) => `${field.field_key} is required before creating an action intent.`);
}

export function createActionIntent(
  screen: MtoolScreen,
  action: MtoolAction,
  row: RuntimeRow,
  draft: Record<string, string>
): MtoolActionIntent {
  const key: MtoolActionIntent['key'] = {};
  const input: MtoolActionIntent['input'] = {};

  for (const field of action.fields) {
    if (field.role === 'key') {
      key[field.field_key] = row[field.field_key]?.value ?? null;
    }
    if (field.role === 'input') {
      input[field.field_key] = draft[field.field_key] ?? runtimeInputValue(row, field.field_key);
    }
  }

  return {
    intent_version: 'no-code-runtime-action-intent-v0',
    action_key: action.action_key,
    operation_key: action.operation_key,
    operation_type: action.operation_type,
    screen_key: screen.screen_key,
    contract_key: 'no_code_ticket',
    key,
    input,
    metadata: {
      source: 'sample35-capacitor-artifact-import',
      submit_boundary: 'mock-disabled'
    }
  };
}

