<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'DM Sans', sans-serif; }

    @keyframes borderSpin {
        0%   { background-position: 0% 50%; }
        100% { background-position: 100% 50%; }
    }
    .drop-zone-ring {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6, #06b6d4, #3b82f6);
        background-size: 300% 300%;
        animation: borderSpin 4s linear infinite;
        padding: 2px;
        border-radius: 12px;
    }
    .drop-zone-ring.locked {
        background: linear-gradient(135deg, #f59e0b, #ef4444, #f59e0b);
        background-size: 300% 300%;
        animation: borderSpin 3s linear infinite;
        pointer-events: none;
    }
    .drop-zone-inner { background: #ffffff; border-radius: 10px; }

    #logContainer {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12.5px;
        line-height: 1.7;
        background: #0f1117;
        color: #9ca3af;
    }
    #logContainer .log-success { color: #34d399; }
    #logContainer .log-update  { color: #60a5fa; }
    #logContainer .log-warn    { color: #fbbf24; }
    #logContainer .log-error   { color: #f87171; }
    #logContainer .log-info    { color: #a78bfa; }
    #logContainer .log-system  { color: #6b7280; font-style: italic; }

    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    #progressBar {
        background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 40%, #06b6d4 60%, #3b82f6 100%);
        background-size: 200% 100%;
        animation: shimmer 2s linear infinite;
        transition: width 0.25s ease;
    }

    .stat-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-6px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .log-line { animation: slideIn 0.12s ease; }

    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); }
        70%  { box-shadow: 0 0 0 8px rgba(59,130,246,0); }
        100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
    }
    #importButton:not(:disabled) { animation: pulse-ring 2s ease-out infinite; }

    /* ── Lock banner ── */
    #lockBanner { display: none; }
    #lockBanner.visible { display: flex; }

    /* ── Poll spinner ── */
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { animation: spin 1s linear infinite; display: inline-block; }
</style>

<div class="p-6 max-w-5xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <button onclick="if(document.referrer){history.back()}else{window.location='<?= base_url('employees') ?>'}"
            class="inline-flex items-center gap-2 text-gray py-2">
            <i class="fas fa-chevron-left"></i> Back
        </button>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employee Import</h1>
        <p class="text-sm text-gray-500 mt-1">
            Upload an <span class="font-medium text-gray-700">.xlsx / .xls / .csv</span> file —
            rows are streamed live as they are committed.
        </p>
    </div>

    <!-- ── Lock Banner ─────────────────────────────────────────────────── -->
    <div id="lockBanner"
         class="lockBanner items-start gap-3 bg-amber-50 border border-amber-200
                rounded-2xl px-5 py-4 mb-5 text-sm text-amber-800">
        <i class="fas fa-lock mt-0.5 text-amber-500 flex-shrink-0"></i>
        <div class="flex-1">
            <p class="font-semibold">Another import is currently in progress.</p>
            <p class="text-amber-700 mt-0.5" id="lockDetail">
                Please wait until it completes before uploading a new file.
            </p>
            <button onclick="watchRunningJob()"
                    id="watchBtn"
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold
                           bg-amber-100 hover:bg-amber-200 text-amber-800
                           px-3 py-1.5 rounded-lg transition-colors">
                <i class="fas fa-eye"></i> Watch progress
            </button>
        </div>
    </div>

    <!-- ── Upload Card ─────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">
            1 · Select File
        </h2>

        <div class="drop-zone-ring mb-4" id="dropZone">
            <div class="drop-zone-inner p-8 text-center flex flex-col items-center" id="dropInner">
                <i class="fas fa-file-excel text-5xl text-gray-300 mb-4"></i>

                <label id="fileLabel"
                    class="cursor-pointer flex items-center gap-2
                           bg-blue-600 hover:bg-blue-700 text-white text-sm
                           font-semibold px-5 py-2.5 rounded-lg transition-colors mb-3">
                    <i class="fas fa-folder-open"></i> Browse file
                    <input type="file" id="fileInput" name="file_upload"
                           accept=".xlsx,.xls,.csv" class="hidden"
                           onchange="onFileChosen(this)">
                </label>

                <p class="text-xs text-gray-400" id="fileName">No file selected</p>
                <p class="text-xs text-gray-300 mt-1">
                    Supported headers: NIK, Name, Department, Division, Job Position, Gender, Site …
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <input type="hidden" id="csrfName"  value="<?= csrf_token() ?>">
            <input type="hidden" id="csrfValue" value="<?= csrf_hash() ?>">

            <div class="flex gap-3">
                <button id="importButton" disabled onclick="startImport()"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none
                               text-white text-sm font-semibold px-6 py-2.5 rounded-lg
                               transition-colors focus:outline-none focus:ring-2
                               focus:ring-green-400 focus:ring-offset-2">
                    <i class="fas fa-play-circle"></i>
                    <span id="importBtnLabel">Import Data</span>
                </button>

                <button id="stopButton" disabled onclick="stopImport()"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700
                               disabled:opacity-40 disabled:cursor-not-allowed
                               text-white text-sm font-semibold px-6 py-2.5 rounded-lg
                               transition-colors focus:outline-none focus:ring-2
                               focus:ring-red-400 focus:ring-offset-2">
                    <i class="fas fa-stop-circle"></i> Stop
                </button>
            </div>

            <span id="statusBadge" class="stat-pill bg-gray-100 text-gray-500">
                <i class="fas fa-circle text-gray-300" style="font-size:8px"></i> Idle
            </span>
        </div>
    </div>

    <!-- ── Progress + Log Card ─────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">
            2 · Live Log
        </h2>

        <!-- Stats row -->
        <div class="flex flex-wrap gap-2 mb-4" id="statsRow" style="display:none!important">
            <span class="stat-pill bg-green-50 text-green-700">
                <i class="fas fa-check-circle"></i>
                <span id="statInserted">0</span> inserted
            </span>
            <span class="stat-pill bg-blue-50 text-blue-700">
                <i class="fas fa-sync-alt"></i>
                <span id="statUpdated">0</span> updated
            </span>
            <span class="stat-pill bg-red-50 text-red-700">
                <i class="fas fa-times-circle"></i>
                <span id="statSkipped">0</span> skipped
            </span>
        </div>

        <!-- Poll indicator (shown while polling) -->
        <div id="pollIndicator"
             class="hidden items-center gap-2 text-xs text-blue-600 font-medium mb-3">
            <i class="fas fa-circle-notch spin"></i>
            <span id="pollMsg">Reconnecting — polling server for progress…</span>
        </div>

        <!-- Progress bar -->
        <div class="w-full bg-gray-100 rounded-full h-2 mb-2 overflow-hidden">
            <div id="progressBar" class="h-2 rounded-full" style="width:0%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mb-4">
            <span>Processed: <strong id="processedCount" class="text-gray-700">0</strong></span>
            <span>Total: <strong id="totalCount" class="text-gray-700">—</strong></span>
        </div>

        <!-- Console -->
        <div id="logContainer" class="rounded-xl p-4 h-80 overflow-y-auto select-text">
            <span class="log-system">Waiting for import to start…</span>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end mt-2 gap-2 text-xs text-gray-400">
            <span id="logLineCount">0 lines</span>
            <button onclick="downloadLog()" class="hover:text-gray-600 transition-colors focus:outline-none">
                <i class="fas fa-download"></i> Download
            </button>
            <button onclick="clearLog()" class="hover:text-gray-600 transition-colors focus:outline-none">
                <i class="fas fa-trash-alt"></i> Clear
            </button>
        </div>
    </div>

</div>

<!-- ── Script ──────────────────────────────────────────────────────────── -->
<script>
'use strict';

// ── State ─────────────────────────────────────────────────────────────────
let uploadedFile  = null;
let currentJobId  = null;
let totalRows     = 0;
let logLines      = 0;
let activeSource  = null;    // EventSource
let pollTimer     = null;    // setInterval handle for polling fallback
let lastLogCount  = 0;       // tracks how many log entries we've already shown
let isRunning     = false;
let lockedJobId   = null;    // job_id of the import blocking us

const BASE = '<?= base_url() ?>';

// ── Drag-and-drop ──────────────────────────────────────────────────────────
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

['dragenter','dragover'].forEach(ev =>
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('scale-[1.01]'); })
);
['dragleave','drop'].forEach(ev =>
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('scale-[1.01]'); })
);
dropZone.addEventListener('drop', e => {
    const f = e.dataTransfer.files[0];
    if (!f) return;
    const dt = new DataTransfer();
    dt.items.add(f);
    fileInput.files = dt.files;
    onFileChosen(fileInput);
});

function onFileChosen(input) {
    const name = input.files[0]?.name;
    document.getElementById('fileName').textContent = name || 'No file selected';
    document.getElementById('importButton').disabled = !name || isRunning;
}

// ── Start import ───────────────────────────────────────────────────────────
async function startImport() {
    if (isRunning) return;
    if (!fileInput.files[0]) return;

    setStatus('uploading');
    resetUI();
    appendLog('system', 'Uploading file to server…');

    const formData = new FormData();
    formData.append('file_upload', fileInput.files[0]);
    formData.append(
        document.getElementById('csrfName').value,
        document.getElementById('csrfValue').value
    );

    let uploadData;
    try {
        const res  = await fetch(`${BASE}employees/upload`, { method: 'POST', body: formData });
        uploadData = await res.json();
    } catch (err) {
        appendLog('error', '❌ Upload failed: ' + err.message);
        setStatus('idle');
        return;
    }

    // ── Server is locked ──────────────────────────────────────────────────
    if (uploadData.status === 'locked') {
        setStatus('idle');
        showLockBanner(uploadData);
        appendLog('warn', '⚠️ ' + uploadData.message);
        return;
    }

    if (uploadData.status !== 'success') {
        appendLog('error', '❌ ' + uploadData.message);
        setStatus('idle');
        return;
    }

    uploadedFile  = uploadData.file;
    totalRows     = uploadData.totalRows ?? 0;
    currentJobId  = uploadData.job_id;
    lastLogCount  = 0;

    document.getElementById('totalCount').textContent = totalRows || '—';
    appendLog('system', `File accepted — ${totalRows} data rows detected (Job #${currentJobId}).`);

    openStream(currentJobId);
}

// ── EventSource stream ─────────────────────────────────────────────────────
function openStream(jobId) {
    if (activeSource) { activeSource.close(); activeSource = null; }

    setStatus('running');
    isRunning = true;

    const url = `${BASE}employees/stream?job_id=${encodeURIComponent(jobId)}`;
    const src = new EventSource(url);
    activeSource = src;

    src.addEventListener('meta', e => {
        const d = JSON.parse(e.data);
        totalRows = d.total;
        document.getElementById('totalCount').textContent = totalRows;
    });

    src.addEventListener('log', e => {
        const d = JSON.parse(e.data);
        appendLog(d.level ?? 'info', d.message);
        lastLogCount++;
    });

    src.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        updateProgress(d.processed, d.total ?? totalRows);
    });

    src.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        updateProgress(d.processed, d.processed);
        showStats(d.inserted, d.updated, d.skipped);
        appendLog('info',
            `🎉 Done — ${d.inserted} inserted · ${d.updated} updated · ${d.skipped} skipped`
        );
        setStatus('done');
        src.close();
        activeSource = null;
        isRunning = false;
    });

    // If SSE closes unexpectedly (browser disconnect, nginx timeout, etc.)
    // switch to polling so the user can still track progress.
    src.addEventListener('error', e => {
        const wasRunning = isRunning;
        src.close();
        activeSource = null;
        isRunning = false;

        if (e.data) {
            try {
                const d = JSON.parse(e.data);
                appendLog('error', '❌ ' + d.message);
            } catch (_) {}
            setStatus('idle');
            return;
        }

        // Connection dropped — fall back to polling if we have a job to track
        if (wasRunning && currentJobId) {
            appendLog('warn', '⚠️ SSE connection lost. Switching to polling…');
            startPolling(currentJobId);
        } else {
            setStatus('idle');
        }
    });
}

// ── Polling fallback ───────────────────────────────────────────────────────
function startPolling(jobId) {
    stopPolling();
    setStatus('polling');
    showPollIndicator(true, 'Reconnected — polling server for progress…');

    pollTimer = setInterval(async () => {
        let data;
        try {
            const res = await fetch(`${BASE}employees/import/status?job_id=${jobId}`);
            data = await res.json();
        } catch (_) { return; }  // network hiccup, try again next tick

        // Render any new log entries (server persists them in the DB)
        if (Array.isArray(data.logs)) {
            const newEntries = data.logs.slice(lastLogCount);
            newEntries.forEach(e => appendLog(e.level ?? 'info', e.message));
            lastLogCount = data.logs.length;
        }

        updateProgress(data.processed, data.total || totalRows);

        if (data.status === 'done') {
            showStats(data.inserted, data.updated, data.skipped);
            appendLog('info',
                `🎉 Done — ${data.inserted} inserted · ${data.updated} updated · ${data.skipped} skipped`
            );
            setStatus('done');
            stopPolling();
        } else if (data.status === 'failed') {
            appendLog('error', '❌ Import failed: ' + (data.message ?? 'unknown error'));
            setStatus('idle');
            stopPolling();
        }
    }, 2000);
}

function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    showPollIndicator(false);
}

// Watch an already-running job (called from the lock banner)
function watchRunningJob() {
    if (!lockedJobId) return;
    currentJobId = lockedJobId;
    lastLogCount = 0;
    hideLockBanner();
    resetUI();
    appendLog('system', `Watching running job #${lockedJobId}…`);
    startPolling(lockedJobId);
}

// ── Stop (client-side only — server continues) ─────────────────────────────
function stopImport() {
    if (activeSource) {
        activeSource.close();
        activeSource = null;
    }
    stopPolling();
    isRunning = false;
    appendLog('warn', '⛔ Import stopped. The server will halt processing within the current batch.');
    setStatus('idle');
}

// ── UI helpers ─────────────────────────────────────────────────────────────
function appendLog(level, message) {
    const container = document.getElementById('logContainer');
    const line = document.createElement('div');
    line.className = `log-line log-${level}`;
    line.textContent = message;
    container.appendChild(line);
    container.scrollTop = container.scrollHeight;
    logLines++;
    document.getElementById('logLineCount').textContent = logLines + ' lines';
}

function clearLog() {
    document.getElementById('logContainer').innerHTML = '';
    logLines = 0;
    document.getElementById('logLineCount').textContent = '0 lines';
}

function downloadLog() {
    const lines = document.querySelectorAll('#logContainer .log-line');
    if (!lines.length) return;
    const text = Array.from(lines).map(l => l.textContent).join('\n');
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {
        href:     url,
        download: `import-log-${new Date().toISOString().slice(0,19).replace(/[:T]/g,'-')}.txt`,
    });
    a.click();
    URL.revokeObjectURL(url);
}

document.getElementById('logContainer').addEventListener('keydown', function (e) {
    if (e.key === 'a' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        const sel = window.getSelection(), r = document.createRange();
        r.selectNodeContents(this);
        sel.removeAllRanges();
        sel.addRange(r);
    }
});

function updateProgress(processed, total) {
    document.getElementById('processedCount').textContent = processed;
    if (!total) return;
    const pct = Math.min(Math.round((processed / total) * 100), 100);
    document.getElementById('progressBar').style.width = pct + '%';
}

function showStats(ins, upd, skp) {
    document.getElementById('statInserted').textContent = ins ?? 0;
    document.getElementById('statUpdated').textContent  = upd ?? 0;
    document.getElementById('statSkipped').textContent  = skp ?? 0;
    document.getElementById('statsRow').style.display   = 'flex';
}

function resetUI() {
    clearLog();
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('processedCount').textContent = '0';
    document.getElementById('totalCount').textContent     = '—';
    document.getElementById('statsRow').style.display     = 'none';
    document.getElementById('statInserted').textContent   = '0';
    document.getElementById('statUpdated').textContent    = '0';
    document.getElementById('statSkipped').textContent    = '0';
    showPollIndicator(false);
}

function showPollIndicator(visible, msg = '') {
    const el = document.getElementById('pollIndicator');
    if (visible) {
        el.classList.remove('hidden');
        el.classList.add('flex');
        if (msg) document.getElementById('pollMsg').textContent = msg;
    } else {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
}

// ── Lock banner helpers ────────────────────────────────────────────────────
function showLockBanner(uploadData) {
    lockedJobId = uploadData.job_id ?? null;
    const banner = document.getElementById('lockBanner');
    const detail = document.getElementById('lockDetail');

    banner.classList.add('visible');
    dropZone.classList.add('locked');

    if (uploadData.progress) {
        const { processed, total } = uploadData.progress;
        const pct = total ? Math.round((processed / total) * 100) : 0;
        detail.textContent = `Progress: ${processed} / ${total} rows (${pct}%)`;
    } else {
        detail.textContent = 'Please wait until the current import completes.';
    }

    document.getElementById('importButton').disabled = true;
}

function hideLockBanner() {
    lockedJobId = null;
    document.getElementById('lockBanner').classList.remove('visible');
    dropZone.classList.remove('locked');
}

// ── Status badge ───────────────────────────────────────────────────────────
const statusCfg = {
    idle:     { icon: 'circle',           color: 'bg-gray-100 text-gray-500',    dot: 'text-gray-300',  label: 'Idle'        },
    uploading:{ icon: 'cloud-upload-alt', color: 'bg-yellow-50 text-yellow-700', dot: 'text-yellow-400',label: 'Uploading…'  },
    running:  { icon: 'cog fa-spin',      color: 'bg-blue-50 text-blue-700',     dot: 'text-blue-400',  label: 'Importing…'  },
    polling:  { icon: 'circle-notch fa-spin', color: 'bg-purple-50 text-purple-700', dot: 'text-purple-400', label: 'Polling…' },
    done:     { icon: 'check-circle',     color: 'bg-green-50 text-green-700',   dot: 'text-green-400', label: 'Complete'    },
};

function setStatus(state) {
    const cfg = statusCfg[state] || statusCfg.idle;
    const badge = document.getElementById('statusBadge');
    badge.className = `stat-pill ${cfg.color}`;
    badge.innerHTML = `<i class="fas fa-${cfg.icon} ${cfg.dot}" style="font-size:9px"></i> ${cfg.label}`;

    const importBtn = document.getElementById('importButton');
    const stopBtn   = document.getElementById('stopButton');
    const label     = document.getElementById('importBtnLabel');
    const active    = state === 'running' || state === 'uploading' || state === 'polling';

    isRunning = active;
    importBtn.disabled = active || !fileInput.files[0];
    stopBtn.disabled   = !active;
    label.textContent  = active
        ? ({ uploading: 'Uploading…', running: 'Importing…', polling: 'Monitoring…' }[state] ?? 'Working…')
        : 'Import Data';
}
</script>

<?= $this->endSection() ?>