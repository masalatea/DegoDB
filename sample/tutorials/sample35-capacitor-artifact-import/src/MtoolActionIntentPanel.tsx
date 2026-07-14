import { MtoolActionIntent } from './mtoolNoCodeBridge';

type Props = {
  messages: string[];
  intent: MtoolActionIntent | null;
  actionAvailability: string;
  failedChecks: string[];
};

export function MtoolActionIntentPanel({ messages, intent, actionAvailability, failedChecks }: Props) {
  const blocked = messages.length > 0 || actionAvailability !== 'enabled';

  return (
    <section className="card" data-mtool-operation="action-intent-draft-submit-handoff-boundary">
      <h2>Action intent draft</h2>
      <p>
        Submit handoff boundary: <strong>mock/disabled</strong>. This sample shows what would be sent, but does not
        mutate server state.
      </p>
      <p className={blocked ? 'blocked' : 'ready'} data-mtool-operation="blocked-error-state">
        {blocked ? 'Blocked or unavailable action state is visible.' : 'Local draft is ready for handoff.'}
      </p>
      {failedChecks.length > 0 && <p>Metadata failed checks: {failedChecks.join(', ')}</p>}
      {messages.length > 0 && (
        <ul>
          {messages.map((message) => (
            <li key={message}>{message}</li>
          ))}
        </ul>
      )}
      <pre>{JSON.stringify(intent, null, 2)}</pre>
    </section>
  );
}

