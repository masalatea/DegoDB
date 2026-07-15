import { displayRuntimeValue, editableFieldsForAction, MtoolScreen, runtimeInputValue } from './mtoolNoCodeBridge';

type Props = {
  screen: MtoolScreen;
  selectedRowIndex: number;
  draft: Record<string, string>;
  onSelectRow: (index: number) => void;
  onDraftChange: (fieldKey: string, value: string) => void;
};

export function MtoolScreenRenderer({ screen, selectedRowIndex, draft, onSelectRow, onDraftChange }: Props) {
  const rows = screen.data.rows;
  const selectedRow = rows[selectedRowIndex] ?? rows[0];
  const action = screen.actions[0];
  const editableFields = action ? editableFieldsForAction(action, screen) : [];

  return (
    <section className="card" data-mtool-operation={`screen-rendering-${screen.screen_type}`}>
      <h2>{screen.screen_title}</h2>
      <p>
        Screen type: <strong>{screen.screen_type}</strong>
      </p>

      {screen.screen_type === 'list' && (
        <table>
          <thead>
            <tr>
              {screen.fields.map((field) => (
                <th key={field.field_key}>
                  {field.label}
                  {field.required ? <span className="required"> required</span> : null}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((row, rowIndex) => (
              <tr key={rowIndex} className={rowIndex === selectedRowIndex ? 'selected' : ''}>
                {screen.fields.map((field) => (
                  <td key={field.field_key}>
                    <button type="button" onClick={() => onSelectRow(rowIndex)}>
                      {displayRuntimeValue(row, field.field_key)}
                    </button>
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {screen.screen_type === 'detail' && (
        <dl data-mtool-operation="field-rendering-readonly-detail">
          {screen.fields.map((field) => (
            <div key={field.field_key}>
              <dt>
                {field.label}
                {field.readonly ? <span className="readonly"> readonly</span> : null}
              </dt>
              <dd>{displayRuntimeValue(selectedRow, field.field_key)}</dd>
            </div>
          ))}
        </dl>
      )}

      {screen.screen_type === 'form' && (
        <div data-mtool-operation="local-form-draft-required-validation">
          {editableFields.map((field) => (
            <label key={field.field_key}>
              <span>
                {field.label}
                {field.required ? <span className="required"> required</span> : null}
              </span>
              <input
                value={draft[field.field_key] ?? runtimeInputValue(selectedRow, field.field_key)}
                onChange={(event) => onDraftChange(field.field_key, event.target.value)}
              />
            </label>
          ))}
        </div>
      )}
    </section>
  );
}

