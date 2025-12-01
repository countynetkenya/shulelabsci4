<?php
/** @var array<string, mixed> $summary */
/** @var list<array<string, mixed>> $approvals */
/** @var int|null $schoolId */
/** @var string $baseUrl */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Approvals</title>
    <style>
        :root {
            color-scheme: light;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            color: #1f2933;
            background-color: #f8fafc;
        }
        h1 {
            margin-bottom: 0.5rem;
        }
        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            min-width: 160px;
        }
        .summary-card h2 {
            font-size: 0.9rem;
            margin: 0;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .summary-card p {
            font-size: 1.8rem;
            margin: 0.35rem 0 0;
            font-weight: bold;
        }
        .summary-card time {
            font-size: 1rem;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #475569;
        }
        tbody tr:hover {
            background-color: #f8fafc;
        }
        .empty-state {
            padding: 2rem;
            text-align: center;
            color: #64748b;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        button.approval-action {
            border: none;
            border-radius: 6px;
            padding: 0.45rem 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        button.approval-action:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        button.approval-action.approve {
            background-color: #16a34a;
            color: #ffffff;
        }
        button.approval-action.reject {
            background-color: #dc2626;
            color: #ffffff;
        }
        button.approval-action:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        button.approval-action:not(:disabled):hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
        }
        .toast {
            margin-bottom: 1rem;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            display: none;
            font-weight: 600;
        }
        .toast.success {
            background-color: #dcfce7;
            color: #166534;
        }
        .toast.error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body data-base-url="<?= esc($baseUrl) ?>" data-school-id="<?= esc($schoolId ?? '') ?>">
    <h1>Payroll Approvals</h1>
    <p>Review pending payroll approvals and track maker-checker activity.</p>

    <div class="toast" role="status" aria-live="polite"></div>

    <section class="summary">
        <article class="summary-card">
            <h2>Pending</h2>
            <p data-summary="pending"><?= esc($summary['counts']['pending'] ?? 0) ?></p>
        </article>
        <article class="summary-card">
            <h2>Approved</h2>
            <p data-summary="approved"><?= esc($summary['counts']['approved'] ?? 0) ?></p>
        </article>
        <article class="summary-card">
            <h2>Rejected</h2>
            <p data-summary="rejected"><?= esc($summary['counts']['rejected'] ?? 0) ?></p>
        </article>
        <article class="summary-card">
            <h2>Last Updated</h2>
            <time datetime="<?= esc($summary['updated_at'] ?? '') ?>" data-summary="updated">
                <?= esc($summary['updated_at'] ?? '') ?>
            </time>
        </article>
    </section>

    <div class="empty-state" data-empty-state <?= $approvals === [] ? '' : 'style="display:none;"' ?>>
        <p>No pending payroll approvals. All payslips are up to date.</p>
    </div>

    <table aria-label="Pending payroll approvals" data-approvals-table <?= $approvals === [] ? 'style="display:none;"' : '' ?>>
        <thead>
            <tr>
                <th scope="col">Request #</th>
                <th scope="col">Employee</th>
                <th scope="col">Period</th>
                <th scope="col">Gross Pay</th>
                <th scope="col">Net Pay</th>
                <th scope="col">Submitted</th>
                <th scope="col">Maker</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody data-approvals-body>
            <?php foreach ($approvals as $approval) : ?>
                <tr data-approval-row data-approval-id="<?= esc((string) $approval['id']) ?>">
                    <td><span class="badge">#<?= esc($approval['id']) ?></span></td>
                    <td>
                        <strong><?= esc($approval['employee']['name'] ?? 'Unknown') ?></strong><br>
                        <small><?= esc($approval['employee']['id'] ?? '—') ?></small>
                    </td>
                    <td><?= esc($approval['period'] ?? '—') ?></td>
                    <td><?= esc(number_format((float) ($approval['gross_pay'] ?? 0), 2)) ?></td>
                    <td><?= esc(number_format((float) ($approval['net_pay'] ?? 0), 2)) ?></td>
                    <td><?= esc($approval['submitted_at'] ?? '') ?></td>
                    <td><?= esc($approval['maker_id'] ?? '—') ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="approval-action approve" data-action="approve">
                                Approve
                            </button>
                            <button type="button" class="approval-action reject" data-action="reject">
                                Reject
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        (function () {
            const root = document.body;
            const baseUrl = root.dataset.baseUrl || '';
            const schoolId = root.dataset.schoolId || '';
            const toast = document.querySelector('.toast');
            const table = document.querySelector('[data-approvals-table]');
            const tableBody = document.querySelector('[data-approvals-body]');
            const emptyState = document.querySelector('[data-empty-state]');

            const summaryFields = {
                pending: document.querySelector('[data-summary="pending"]'),
                approved: document.querySelector('[data-summary="approved"]'),
                rejected: document.querySelector('[data-summary="rejected"]'),
                updated: document.querySelector('[data-summary="updated"]'),
            };

            function showToast(message, type) {
                if (!toast) {
                    return;
                }

                toast.textContent = message;
                toast.className = `toast ${type}`;
                toast.style.display = 'block';

                window.setTimeout(() => {
                    toast.style.display = 'none';
                }, 4000);
            }

            function buildEndpoint(action, id) {
                const path = action === 'approve'
                    ? `/hr/payroll/approvals/${id}/approve`
                    : `/hr/payroll/approvals/${id}/reject`;

                return `${baseUrl}${path}`;
            }

            function buildRefreshUrl() {
                const url = new URL(`${baseUrl}/hr/payroll/approvals/pending`);
                if (schoolId) {
                    url.searchParams.set('school_id', schoolId);
                }

                return url.toString();
            }

            function setLoading(row, loading) {
                const buttons = row.querySelectorAll('button.approval-action');
                buttons.forEach((button) => {
                    button.disabled = loading;
                });
            }

            async function refreshDataset() {
                const response = await fetch(buildRefreshUrl());
                if (!response.ok) {
                    throw new Error('Unable to refresh approvals.');
                }

                const payload = await response.json();
                updateSummary(payload.summary || {});
                renderApprovals(payload.approvals || []);
            }

            function updateSummary(summary) {
                if (summaryFields.pending) {
                    summaryFields.pending.textContent = summary.counts?.pending ?? 0;
                }
                if (summaryFields.approved) {
                    summaryFields.approved.textContent = summary.counts?.approved ?? 0;
                }
                if (summaryFields.rejected) {
                    summaryFields.rejected.textContent = summary.counts?.rejected ?? 0;
                }
                if (summaryFields.updated) {
                    summaryFields.updated.textContent = summary.updated_at ?? '';
                    summaryFields.updated.setAttribute('datetime', summary.updated_at ?? '');
                }
            }

            function renderApprovals(approvals) {
                if (!tableBody || !table || !emptyState) {
                    return;
                }

                if (approvals.length === 0) {
                    table.style.display = 'none';
                    emptyState.style.display = 'block';
                    tableBody.innerHTML = '';
                    return;
                }

                table.style.display = 'table';
                emptyState.style.display = 'none';
                tableBody.innerHTML = '';

                const formatter = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                approvals.forEach((approval) => {
                    const row = document.createElement('tr');
                    row.dataset.approvalId = String(approval.id ?? '');

                    row.innerHTML = `
                        <td><span class="badge">#${escapeHtml(approval.id)}</span></td>
                        <td>
                            <strong>${escapeHtml(approval.employee?.name ?? 'Unknown')}</strong><br>
                            <small>${escapeHtml(approval.employee?.id ?? '—')}</small>
                        </td>
                        <td>${escapeHtml(approval.period ?? '—')}</td>
                        <td>${formatter.format(Number(approval.gross_pay ?? 0))}</td>
                        <td>${formatter.format(Number(approval.net_pay ?? 0))}</td>
                        <td>${escapeHtml(approval.submitted_at ?? '')}</td>
                        <td>${escapeHtml(approval.maker_id ?? '—')}</td>
                        <td>
                            <div class="actions">
                                <button type="button" class="approval-action approve" data-action="approve">Approve</button>
                                <button type="button" class="approval-action reject" data-action="reject">Reject</button>
                            </div>
                        </td>
                    `;

                    tableBody.appendChild(row);
                });
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            async function processAction(row, action) {
                const approvalId = row?.dataset.approvalId;
                if (!approvalId) {
                    return;
                }

                let payload;
                if (action === 'reject') {
                    const reason = window.prompt('Enter a rejection reason for this payslip:');
                    if (reason === null) {
                        return;
                    }
                    if (reason.trim() === '') {
                        showToast('Rejection reason is required.', 'error');
                        return;
                    }
                    payload = { reason: reason.trim() };
                }

                setLoading(row, true);

                try {
                    const response = await fetch(buildEndpoint(action, approvalId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: payload ? JSON.stringify(payload) : undefined,
                    });

                    if (!response.ok) {
                        const error = await response.json().catch((parseError) => { console.error('Failed to parse JSON response:', parseError); return {}; });
                        const message = error.error ?? `Unable to ${action} request.`;
                        throw new Error(message);
                    }

                    await refreshDataset();
                    showToast(`Request ${action}d successfully.`, 'success');
                } catch (error) {
                    showToast(error instanceof Error ? error.message : 'Unexpected error occurred.', 'error');
                } finally {
                    setLoading(row, false);
                }
            }

            tableBody?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLButtonElement)) {
                    return;
                }

                const action = target.dataset.action;
                if (!action) {
                    return;
                }

                const row = target.closest('[data-approval-row]');
                if (!row) {
                    return;
                }

                processAction(row, action).catch((error) => {
                    console.error(error);
                });
            });
        })();
    </script>
</body>
</html>
