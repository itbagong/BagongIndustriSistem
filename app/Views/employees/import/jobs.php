<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --ink:     #0f1117;
        --ink-2:   #1e2130;
        --ink-3:   #2d3148;
        --muted:   #6b7280;
        --border:  #e5e7eb;
        --surface: #ffffff;
    }
    body { font-family: 'Sora', sans-serif; background: #f8f9fc; }

    /* ── Status pill ── */
    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 2px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 600; letter-spacing: .03em;
    }
    .badge-pending  { background: #fef3c7; color: #92400e; }
    .badge-running  { background: #dbeafe; color: #1e40af; }
    .badge-done     { background: #d1fae5; color: #065f46; }
    .badge-failed   { background: #fee2e2; color: #991b1b; }

    .badge-dot {
        width: 6px; height: 6px; border-radius: 50%;
        flex-shrink: 0;
    }
    .badge-pending  .badge-dot { background: #f59e0b; }
    .badge-running  .badge-dot { background: #3b82f6; animation: blink 1s step-start infinite; }
    .badge-done     .badge-dot { background: #10b981; }
    .badge-failed   .badge-dot { background: #ef4444; }

    @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:.2; } }

    /* ── Table ── */
    table { border-collapse: collapse; width: 100%; }
    thead th {
        background: var(--ink);
        color: #9ca3af;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: 10px 14px;
        text-align: left;
        white-space: nowrap;
    }
    thead th:first-child  { border-radius: 10px 0 0 0; }
    thead th:last-child   { border-radius: 0 10px 0 0; text-align: right; }
    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background .12s;
    }
    tbody tr:last-child   { border-bottom: none; }
    tbody tr:hover        { background: #f1f5fd; }
    tbody td {
        padding: 12px 14px;
        font-size: 13px;
        color: #374151;
        vertical-align: middle;
    }
    tbody td.mono {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 11.5px;
        color: #4b5563;
    }

    /* ── Progress mini-bar ── */
    .mini-bar-track {
        height: 5px; background: #e5e7eb; border-radius: 999px; min-width: 80px; overflow: hidden;
    }
    .mini-bar-fill {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        transition: width .3s ease;
    }
    .mini-bar-fill.done   { background: linear-gradient(90deg, #10b981, #34d399); }
    .mini-bar-fill.failed { background: linear-gradient(90deg, #ef4444, #f87171); }

    /* ── Action buttons ── */
    .btn-action {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 11px; border-radius: 7px;
        font-size: 12px; font-weight: 600;
        transition: all .15s; cursor: pointer; border: none;
        white-space: nowrap;
    }
    .btn-dl      { background: #eff6ff; color: #1d4ed8; }
    .btn-dl:hover      { background: #dbeafe; }
    .btn-dl.unavailable { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

    .btn-restart { background: #f0fdf4; color: #15803d; }
    .btn-restart:hover { background: #dcfce7; }
    .btn-restart:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

    .btn-del     { background: #fff1f2; color: #be123c; }
    .btn-del:hover { background: #ffe4e6; }
    .btn-del:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

    /* ── Log drawer ── */
    #logDrawer {
        display: none;
        position: fixed; inset: 0; z-index: 50;
        background: rgba(15,17,23,.75);
        backdrop-filter: blur(4px);
        align-items: flex-end;
        justify-content: center;
    }
    #logDrawer.open { display: flex; }
    #logPanel {
        background: var(--ink);
        width: 100%; max-width: 860px;
        border-radius: 16px 16px 0 0;
        padding: 0;
        max-height: 72vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 -20px 60px rgba(0,0,0,.4);
        animation: slideUp .22s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(40px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    #logPanelHeader {
        padding: 16px 20px;
        border-bottom: 1px solid #2d3148;
        display: flex; align-items: center; justify-content: space-between;
        flex-shrink: 0;
    }
    #logScroll {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 12px; line-height: 1.75;
        color: #9ca3af;
        overflow-y: auto;
        padding: 16px 20px;
        flex: 1;
    }
    #logScroll .log-success { color: #34d399; }
    #logScroll .log-update  { color: #60a5fa; }
    #logScroll .log-warn    { color: #fbbf24; }
    #logScroll .log-error   { color: #f87171; }
    #logScroll .log-info    { color: #a78bfa; }
    #logScroll .log-system  { color: #4b5563; font-style: italic; }

    /* ── Toast ── */
    #toast {
        position: fixed; bottom: 28px; right: 28px; z-index: 100;
        max-width: 320px; display: none;
    }
    #toast.show { display: block; animation: toastIn .2s ease; }
    @keyframes toastIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .toast-inner {
        padding: 12px 16px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        box-shadow: 0 8px 24px rgba(0,0,0,.15);
        display: flex; align-items: center; gap: 10px;
    }
    .toast-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .toast-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .toast-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }

    /* ── Empty state ── */
    .empty-state { padding: 60px 0; text-align: center; color: var(--muted); }
    .empty-state i { font-size: 40px; margin-bottom: 12px; opacity: .3; }

    /* ── Confirm dialog ── */
    #confirmDialog {
        display: none;
        position: fixed; inset: 0; z-index: 60;
        background: rgba(15,17,23,.7);
        align-items: center; justify-content: center;
    }
    #confirmDialog.open { display: flex; }
    #confirmPanel {
        background: #fff; border-radius: 14px;
        padding: 28px 32px; max-width: 380px; width: 90%;
        box-shadow: 0 24px 64px rgba(0,0,0,.25);
        animation: fadeScale .18s ease;
    }
    @keyframes fadeScale {
        from { opacity: 0; transform: scale(.96); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>

<div class="p-6 max-w-6xl mx-auto">

    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            <button onclick="history.back()"
                class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 mb-1 text-sm transition-colors">
                <i class="fas fa-chevron-left"></i> Back
            </button>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Import Jobs</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                <?= $total ?> job<?= $total !== 1 ? 's' : '' ?> total
            </p>
        </div>

        <a href="<?= base_url('employees/import') ?>"
           class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700
                  text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <i class="fas fa-plus"></i> New Import
        </a>
    </div>

    <!-- ── Table card ──────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox block"></i>
                <p class="font-semibold text-gray-500">No import jobs yet.</p>
                <p class="text-sm mt-1">Upload a file on the Import page to get started.</p>
            </div>

        <?php else: ?>
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Inserted</th>
                    <th>Updated</th>
                    <th>Skipped</th>
                    <th>Created</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $job): ?>
            <?php
                $pct     = $job['total'] > 0 ? min(100, round($job['processed'] / $job['total'] * 100)) : 0;
                $barClass = match($job['status']) {
                    'done'   => 'done',
                    'failed' => 'failed',
                    default  => '',
                };
                $canRestart = in_array($job['status'], ['done', 'failed']) && $job['file_exists'];
                $isRunning  = $job['status'] === 'running';
                $logsJson   = htmlspecialchars(json_encode($job['logs']), ENT_QUOTES, 'UTF-8');
                $logCount   = count($job['logs']);
            ?>
            <tr>
                <!-- # -->
                <td class="mono">#<?= $job['id'] ?></td>

                <!-- File -->
                <td>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-excel text-green-500 text-base flex-shrink-0"></i>
                        <div>
                            <p class="font-medium text-gray-800 text-xs leading-tight mono truncate max-w-[180px]"
                               title="<?= esc($job['file_name']) ?>">
                                employees_import_<?= $job['id'] ?>.<?= pathinfo($job['file_name'], PATHINFO_EXTENSION) ?>
                            </p>
                            <p class="text-gray-400 text-xs mt-0.5"><?= $job['total'] ?> rows</p>
                        </div>
                    </div>
                </td>

                <!-- Status -->
                <td>
                    <span class="badge badge-<?= $job['status'] ?>">
                        <span class="badge-dot"></span>
                        <?= ucfirst($job['status']) ?>
                    </span>
                    <?php if ($job['status'] === 'running'): ?>
                        <a href="<?= base_url('employees/import?watch=' . $job['id']) ?>"
                           class="ml-2 text-xs text-blue-500 hover:underline">
                            Watch <i class="fas fa-external-link-alt" style="font-size:9px"></i>
                        </a>
                    <?php endif; ?>
                </td>

                <!-- Progress -->
                <td>
                    <div class="flex flex-col gap-1">
                        <div class="mini-bar-track">
                            <div class="mini-bar-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-400 mono"><?= $job['processed'] ?> / <?= $job['total'] ?> &nbsp;(<?= $pct ?>%)</span>
                    </div>
                </td>

                <!-- Inserted -->
                <td class="mono text-green-600 font-semibold"><?= $job['inserted'] ?></td>

                <!-- Updated -->
                <td class="mono text-blue-600 font-semibold"><?= $job['updated'] ?></td>

                <!-- Skipped -->
                <td class="mono <?= $job['skipped'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-400' ?>">
                    <?= $job['skipped'] ?>
                </td>

                <!-- Created -->
                <td class="mono text-gray-400 text-xs">
                    <?= date('d M Y', strtotime($job['created_at'])) ?>
                    <span class="block text-gray-300"><?= date('H:i', strtotime($job['created_at'])) ?></span>
                </td>

                <!-- Actions -->
                <td>
                    <div class="flex items-center gap-1.5 justify-end flex-wrap">

                        <!-- View / Download file -->
                        <?php if ($job['file_exists']): ?>
                        <button class="btn-action btn-dl"
                                onclick="downloadFile(<?= $job['id'] ?>)"
                                title="Download original file">
                            <i class="fas fa-download"></i> File
                        </button>
                        <?php else: ?>
                        <span class="btn-action btn-dl unavailable" title="File no longer on disk">
                            <i class="fas fa-unlink"></i> File
                        </span>
                        <?php endif; ?>

                        <!-- View logs -->
                        <?php if ($logCount > 0): ?>
                        <button class="btn-action"
                                style="background:#f5f3ff;color:#6d28d9"
                                onclick='openLogs(<?= $job['id'] ?>, <?= $logsJson ?>)'
                                title="View <?= $logCount ?> log entries">
                            <i class="fas fa-list-ul"></i> Logs
                            <span style="background:#ede9fe;color:#7c3aed;padding:1px 6px;border-radius:999px;font-size:10px"><?= $logCount ?></span>
                        </button>
                        <?php endif; ?>

                        <!-- Restart -->
                        <button class="btn-action btn-restart"
                                <?= (! $canRestart) ? 'disabled title="Cannot restart: job is ' . $job['status'] . ($job['file_exists'] ? '' : ' (file missing)') . '"' : 'title="Restart this import"' ?>
                                onclick="restartJob(<?= $job['id'] ?>)">
                            <i class="fas fa-redo"></i> Restart
                        </button>

                        <!-- Delete -->
                        <button class="btn-action btn-del"
                                <?= $isRunning ? 'disabled title="Cannot delete a running job"' : '' ?>
                                onclick="confirmDelete(<?= $job['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>

                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>"
                   class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>"
                   class="px-3 py-1.5 rounded-lg bg-gray-900 hover:bg-gray-700 text-white font-medium transition-colors">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- ── Log Drawer ────────────────────────────────────────────────────────── -->
<div id="logDrawer" onclick="closeLogsOnBackdrop(event)">
    <div id="logPanel">
        <div id="logPanelHeader">
            <div>
                <p class="text-white font-semibold text-sm" id="logPanelTitle">Import Log</p>
                <p class="text-gray-500 text-xs mt-0.5" id="logPanelMeta"></p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="downloadVisibleLog()"
                        class="text-gray-400 hover:text-white text-xs flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-download"></i> Download
                </button>
                <button onclick="closeDrawer()"
                        class="text-gray-400 hover:text-white w-7 h-7 rounded-lg bg-gray-800
                               hover:bg-gray-700 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="logScroll"></div>
    </div>
</div>

<!-- ── Confirm delete dialog ─────────────────────────────────────────────── -->
<div id="confirmDialog" onclick="closeConfirmOnBackdrop(event)">
    <div id="confirmPanel">
        <div class="flex items-start gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash text-red-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Delete import job?</p>
                <p class="text-sm text-gray-500 mt-1">
                    This will permanently remove the job record and delete the uploaded file from disk.
                    This action cannot be undone.
                </p>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <button onclick="closeConfirm()"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600
                           bg-gray-100 hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <button id="confirmDeleteBtn"
                    onclick="executeDelete()"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white
                           bg-red-600 hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-1.5"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- ── Toast ─────────────────────────────────────────────────────────────── -->
<div id="toast">
    <div class="toast-inner" id="toastInner"></div>
</div>

<!-- ── Script ────────────────────────────────────────────────────────────── -->
<script>
'use strict';

const BASE = '<?= base_url() ?>';
let pendingDeleteId = null;
let drawerLogs      = [];
let drawerJobId     = null;

// ── Download file ──────────────────────────────────────────────────────────
function downloadFile(jobId) {
    window.location.href = `${BASE}employees/import/jobs/${jobId}/file`;
}

// ── Restart ────────────────────────────────────────────────────────────────
async function restartJob(jobId) {
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Resetting…';

    try {
        const res  = await fetch(`${BASE}employees/import/jobs/${jobId}/restart`, { method: 'POST' });
        const data = await res.json();

        if (data.success) {
            showToast('success', `Job #${jobId} reset. Redirecting to import page…`);
            setTimeout(() => {
                window.location.href = `${BASE}employees/import?job_id=${jobId}&file=${encodeURIComponent(data.file)}&total=${data.total}`;
            }, 1200);
        } else {
            showToast('error', data.message ?? 'Failed to restart job.');
            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    } catch (err) {
        showToast('error', 'Network error: ' + err.message);
        btn.disabled  = false;
        btn.innerHTML = orig;
    }
}

// ── Delete (confirm → execute) ─────────────────────────────────────────────
function confirmDelete(jobId) {
    pendingDeleteId = jobId;
    document.getElementById('confirmDialog').classList.add('open');
}
function closeConfirm() {
    pendingDeleteId = null;
    document.getElementById('confirmDialog').classList.remove('open');
}
function closeConfirmOnBackdrop(e) {
    if (e.target === document.getElementById('confirmDialog')) closeConfirm();
}
async function executeDelete() {
    if (! pendingDeleteId) return;

    const btn  = document.getElementById('confirmDeleteBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Deleting…';

    try {
        const res  = await fetch(`${BASE}employees/import/jobs/${pendingDeleteId}/delete`, { method: 'DELETE' });
        const data = await res.json();

        if (data.success) {
            closeConfirm();
            showToast('success', `Job #${pendingDeleteId} deleted.`);
            // Remove the row from the DOM
            const row = document.querySelector(`tr[data-job="${pendingDeleteId}"]`);
            if (row) {
                row.style.transition = 'opacity .25s, transform .25s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(10px)';
                setTimeout(() => row.remove(), 280);
            } else {
                setTimeout(() => location.reload(), 800);
            }
        } else {
            showToast('error', data.message ?? 'Failed to delete job.');
        }
    } catch (err) {
        showToast('error', 'Network error: ' + err.message);
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-trash mr-1.5"></i> Delete';
    }
}

// ── Log drawer ─────────────────────────────────────────────────────────────
function openLogs(jobId, logs) {
    drawerLogs  = logs;
    drawerJobId = jobId;

    document.getElementById('logPanelTitle').textContent = `Import Log — Job #${jobId}`;
    document.getElementById('logPanelMeta').textContent  = `${logs.length} entries`;

    const scroll = document.getElementById('logScroll');
    scroll.innerHTML = '';

    if (!logs.length) {
        scroll.innerHTML = '<span class="log-system">No log entries recorded.</span>';
    } else {
        logs.forEach(entry => {
            const div = document.createElement('div');
            div.className = `log-${entry.level ?? 'info'}`;
            div.textContent = entry.message;
            scroll.appendChild(div);
        });
        scroll.scrollTop = scroll.scrollHeight;
    }

    document.getElementById('logDrawer').classList.add('open');
}
function closeDrawer() {
    document.getElementById('logDrawer').classList.remove('open');
}
function closeLogsOnBackdrop(e) {
    if (e.target === document.getElementById('logDrawer')) closeDrawer();
}
function downloadVisibleLog() {
    if (!drawerLogs.length) return;
    const text = drawerLogs.map(e => e.message).join('\n');
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {
        href:     url,
        download: `import-log-job-${drawerJobId}.txt`,
    });
    a.click();
    URL.revokeObjectURL(url);
}

// ── Toast ──────────────────────────────────────────────────────────────────
let toastTimer;
function showToast(type, msg) {
    const toast = document.getElementById('toast');
    const inner = document.getElementById('toastInner');
    const icon  = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' }[type] ?? 'info-circle';

    inner.className = `toast-inner toast-${type}`;
    inner.innerHTML = `<i class="fas fa-${icon}"></i><span>${msg}</span>`;
    toast.classList.add('show');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
}

// ── Add data-job attr to rows so we can remove them after delete ───────────
document.querySelectorAll('tbody tr').forEach((row, i) => {
    const jobId = row.querySelector('[onclick*="confirmDelete"]')
        ?.getAttribute('onclick')
        ?.match(/\d+/)?.[0];
    if (jobId) row.setAttribute('data-job', jobId);
});

// ── Auto-reload if any job is running (poll every 8 s) ────────────────────
const hasRunning = <?= json_encode(!empty(array_filter($jobs ?? [], fn($j) => $j['status'] === 'running'))) ?>;
if (hasRunning) {
    setTimeout(() => location.reload(), 8000);
}
</script>

<?= $this->endSection() ?>