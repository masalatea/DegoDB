import { useMemo, useState } from 'react';
import { MtoolActionIntentPanel } from './MtoolActionIntentPanel';
import { MtoolArtifactSummary } from './MtoolArtifactSummary';
import { MtoolScreenRenderer } from './MtoolScreenRenderer';
import { mtoolArtifacts } from './mtoolArtifacts';
import { createActionIntent, MtoolScreen, requiredValidationMessages, runtimeInputValue } from './mtoolNoCodeBridge';

export function App() {
  const screens = mtoolArtifacts.bridgeContract.runtime_preview.screens as MtoolScreen[];
  const [activeScreenKey, setActiveScreenKey] = useState(screens[0]?.screen_key ?? '');
  const [selectedRowIndex, setSelectedRowIndex] = useState(0);
  const [draft, setDraft] = useState<Record<string, string>>({});

  const activeScreen = screens.find((screen) => screen.screen_key === activeScreenKey) ?? screens[0];
  const formScreen = screens.find((screen) => screen.screen_type === 'form') ?? activeScreen;
  const selectedRow = activeScreen.data.rows[selectedRowIndex] ?? activeScreen.data.rows[0];
  const action = formScreen.actions[0];

  const normalizedDraft = useMemo(() => {
    const next = { ...draft };
    if (action) {
      for (const field of action.fields) {
        if (field.role === 'input' && next[field.field_key] === undefined) {
          next[field.field_key] = runtimeInputValue(selectedRow, field.field_key);
        }
      }
    }
    return next;
  }, [action, draft, selectedRow]);

  const validationMessages = action ? requiredValidationMessages(action, normalizedDraft) : ['No action metadata found.'];
  const intent = action && validationMessages.length === 0 ? createActionIntent(formScreen, action, selectedRow, normalizedDraft) : null;

  return (
    <main>
      <header>
        <h1>Mtool Sample35 Capacitor Artifact Import</h1>
        <p>
          A Capacitor-ready React shell importing Mtool artifacts directly. No AI or Mtool runtime is required in this
          app path.
        </p>
      </header>

      <MtoolArtifactSummary />

      <nav className="tabs" data-mtool-operation="navigation-selection">
        {screens.map((screen) => (
          <button
            type="button"
            key={screen.screen_key}
            className={screen.screen_key === activeScreen.screen_key ? 'active' : ''}
            onClick={() => setActiveScreenKey(screen.screen_key)}
          >
            {screen.screen_type}: {screen.screen_title}
          </button>
        ))}
      </nav>

      <MtoolScreenRenderer
        screen={activeScreen}
        selectedRowIndex={selectedRowIndex}
        draft={normalizedDraft}
        onSelectRow={setSelectedRowIndex}
        onDraftChange={(fieldKey, value) => setDraft((current) => ({ ...current, [fieldKey]: value }))}
      />

      <MtoolActionIntentPanel
        messages={validationMessages}
        intent={intent}
        actionAvailability={action?.availability ?? 'missing'}
        failedChecks={action?.failed_checks ?? []}
      />

      <section className="card" data-mtool-operation="ownership-boundary-display">
        <h2>Ownership boundary</h2>
        <p>{mtoolArtifacts.reactWrapperAppHandoff.react_app_boundary.external_wrapper_owner_owns.join(', ')}</p>
        <p>{mtoolArtifacts.reactWrapperAppHandoff.capacitor_preparation_boundary.external_owner_next_checks.join(', ')}</p>
      </section>
    </main>
  );
}

