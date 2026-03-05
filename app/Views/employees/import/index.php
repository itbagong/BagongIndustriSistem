<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'DM Sans', sans-serif; }

    /* ── Animated gradient border on the drop zone ── */
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
    .drop-zone-inner {
        background: #ffffff;
        border-radius: 10px;
    }

    /* ── Log console ── */
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

    /* ── Blinking cursor while running ── */
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    #cursor { animation: blink 1s step-start infinite; display: none; }
    #cursor.active { display: inline; }

    /* ── Progress bar shimmer ── */
    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    #progressBar {
        background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 40%, #06b6d4 60%, #3b82f6 100%);
        background-size: 200% 100%;
        animation: shimmer 2s linear infinite;
        transition: width 0.2s ease;
    }

    /* ── Stat pills ── */
    .stat-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600;
    }

    /* ── Slide-in for new log lines ── */
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-6px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .log-line { animation: slideIn 0.12s ease; }

    /* ── Upload button pulse ── */
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); }
        70%  { box-shadow: 0 0 0 8px rgba(59,130,246,0); }
        100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
    }
    #importButton:not(:disabled) { animation: pulse-ring 2s ease-out infinite; }
</style>

<div class="p-6 max-w-5xl mx-auto">

    <!-- ── Page Header ─────────────────────────────────────────────────── -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employee Import</h1>
        <p class="text-sm text-gray-500 mt-1">
            Upload an <span class="font-medium text-gray-700">.xlsx / .xls / .csv</span> file —
            rows are streamed live as they are committed.
        </p>
    </div>

    <!-- ── Upload Card ─────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">
            1 · Select File
        </h2>

        <div class="drop-zone-ring mb-4" id="dropZone">
            <div class="drop-zone-inner p-8 text-center" id="dropInner">
                <i class="fas fa-file-excel text-5xl text-gray-300 mb-3 block"></i>

                <label id="fileLabel"
                       class="cursor-pointer inline-flex items-center gap-2
                              bg-blue-600 hover:bg-blue-700 text-white text-sm
                              font-semibold px-5 py-2.5 rounded-lg transition-colors">
                    <i class="fas fa-folder-open"></i> Browse file
                    <input type="file"
                           id="fileInput"
                           name="file_upload"
                           accept=".xlsx,.xls,.csv"
                           class="hidden"
                           onchange="onFileChosen(this)">
                </label>

                <p class="text-xs text-gray-400 mt-3" id="fileName">No file selected</p>
                <p class="text-xs text-gray-300 mt-1">
                    Supported headers: NIK, Name, Department, Division, Job Position, Gender, Site …
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <input type="hidden" id="csrfName"  value="<?= csrf_token() ?>">
            <input type="hidden" id="csrfValue" value="<?= csrf_hash() ?>">

            <div class="flex gap-3">
                <button id="importButton"
                        disabled
                        onclick="startImport()"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700
                            disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none
                            text-white text-sm font-semibold px-6 py-2.5 rounded-lg
                            transition-colors focus:outline-none focus:ring-2
                            focus:ring-green-400 focus:ring-offset-2">
                    <i class="fas fa-play-circle"></i>
                    <span id="importBtnLabel">Import Data</span>
                </button>

                <button id="stopButton"
                        disabled
                        onclick="stopImport()"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700
                            disabled:opacity-40 disabled:cursor-not-allowed
                            text-white text-sm font-semibold px-6 py-2.5 rounded-lg
                            transition-colors focus:outline-none focus:ring-2
                            focus:ring-red-400 focus:ring-offset-2">
                    <i class="fas fa-stop-circle"></i>
                    Stop
                </button>
            </div>

            <span id="statusBadge" class="stat-pill bg-gray-100 text-gray-500">
                <i class="fas fa-circle text-gray-300" style="font-size:8px"></i>
                Idle
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

        <!-- Progress bar -->
        <div class="w-full bg-gray-100 rounded-full h-2 mb-2 overflow-hidden">
            <div id="progressBar" class="h-2 rounded-full" style="width:0%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mb-4">
            <span>Processed: <strong id="processedCount" class="text-gray-700">0</strong></span>
            <span>Total: <strong id="totalCount" class="text-gray-700">—</strong></span>
        </div>

        <!-- Console -->
        <div id="logContainer"
             class="rounded-xl p-4 h-80 overflow-y-auto select-text">
            <span class="log-system">Waiting for import to start…</span>
        </div>

        <!-- Row count below console -->
        <div class="flex items-center justify-end mt-2 gap-2 text-xs text-gray-400">
            <span id="logLineCount">0 lines</span>
            <button onclick="clearLog()"
                    class="hover:text-gray-600 transition-colors focus:outline-none">
                <i class="fas fa-trash-alt"></i> Clear
            </button>
        </div>
    </div>

</div>

<!-- ── Script ──────────────────────────────────────────────────────────── -->
<script>
/* ── State ──────────────────────────────────────────────────────────── */
let uploadedFile   = null;
let totalRows      = 0;
let logLines       = 0;
let activeSource   = null;   // EventSource reference
let isRunning      = false;

/* ── Drag-and-drop on drop zone ─────────────────────────────────────── */
const dropZone = document.getElementById('dropZone');
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

/* ── File chosen ────────────────────────────────────────────────────── */
function onFileChosen(input) {
    const name = input.files[0]?.name;
    document.getElementById('fileName').textContent = name || 'No file selected';
    document.getElementById('importButton').disabled = !name;
}

/* ── Start: upload then open SSE stream ─────────────────────────────── */
async function startImport() {
    if (isRunning) return;

    const fileInput = document.getElementById('fileInput');
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
        const res  = await fetch("<?= base_url('employees/upload') ?>", {
            method: 'POST', body: formData,
        });
        uploadData = await res.json();
    } catch (err) {
        appendLog('error', '❌ Upload failed: ' + err.message);
        setStatus('idle');
        return;
    }

    if (uploadData.status !== 'success') {
        appendLog('error', '❌ ' + uploadData.message);
        setStatus('idle');
        return;
    }

    uploadedFile = uploadData.file;
    totalRows    = uploadData.totalRows ?? 0;

    document.getElementById('totalCount').textContent = totalRows || '—';
    appendLog('system', `File accepted — ${totalRows} data rows detected.`);

    openStream();
}

/* ── Open EventSource ───────────────────────────────────────────────── */
function openStream() {
    if (activeSource) { activeSource.close(); activeSource = null; }

    setStatus('running');
    isRunning = true;

    const url = `<?= base_url('employees/stream') ?>?file=${encodeURIComponent(uploadedFile)}`;
    const src = new EventSource(url);
    activeSource = src;

    // ── meta: server tells us the actual row count ────────────────────
    src.addEventListener('meta', e => {
        const d = JSON.parse(e.data);
        totalRows = d.total;
        document.getElementById('totalCount').textContent = totalRows;
    });

    // ── log: a single row result ──────────────────────────────────────
    src.addEventListener('log', e => {
        const d = JSON.parse(e.data);
        appendLog(d.level ?? 'info', d.message);
    });

    // ── progress: update bar + counter ───────────────────────────────
    src.addEventListener('progress', e => {
        const d = JSON.parse(e.data);
        updateProgress(d.processed, d.total ?? totalRows);
    });

    // ── done: import complete ─────────────────────────────────────────
    src.addEventListener('done', e => {
        const d = JSON.parse(e.data);
        updateProgress(d.processed, d.processed);   // fill bar to 100 %

        // Show summary stats
        document.getElementById('statInserted').textContent = d.inserted ?? 0;
        document.getElementById('statUpdated').textContent  = d.updated  ?? 0;
        document.getElementById('statSkipped').textContent  = d.skipped  ?? 0;
        document.getElementById('statsRow').style.display   = 'flex';

        appendLog('info',
            `🎉 Done — ${d.inserted} inserted · ${d.updated} updated · ${d.skipped} skipped`
        );

        setStatus('done');
        src.close();
        activeSource = null;
        isRunning = false;
    });

    // ── error: SSE-level error ────────────────────────────────────────
    src.addEventListener('error', e => {
        if (e.data) {
            const d = JSON.parse(e.data);
            appendLog('error', '❌ ' + d.message);
        } else if (src.readyState === EventSource.CLOSED) {
            appendLog('error', '⚠️ Connection closed unexpectedly.');
        }
        setStatus('idle');
        src.close();
        activeSource = null;
        isRunning = false;
    });
}

/* ── Helpers ────────────────────────────────────────────────────────── */
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

function updateProgress(processed, total) {
    document.getElementById('processedCount').textContent = processed;
    if (!total) return;
    const pct = Math.min(Math.round((processed / total) * 100), 100);
    document.getElementById('progressBar').style.width = pct + '%';
}

function resetUI() {
    clearLog();
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('processedCount').textContent = '0';
    document.getElementById('totalCount').textContent = '—';
    document.getElementById('statsRow').style.display = 'none';
    document.getElementById('statInserted').textContent = '0';
    document.getElementById('statUpdated').textContent  = '0';
    document.getElementById('statSkipped').textContent  = '0';
}

const statusCfg = {
    idle:       { icon:'circle',        color:'bg-gray-100 text-gray-500',   dot:'text-gray-300', label:'Idle'      },
    uploading:  { icon:'cloud-upload-alt', color:'bg-yellow-50 text-yellow-700', dot:'text-yellow-400', label:'Uploading…' },
    running:    { icon:'cog fa-spin',   color:'bg-blue-50 text-blue-700',    dot:'text-blue-400', label:'Importing…' },
    done:       { icon:'check-circle',  color:'bg-green-50 text-green-700',  dot:'text-green-400', label:'Complete'  },
};

function stopImport() {
    if (!activeSource) return;

    activeSource.close();
    activeSource = null;
    isRunning    = false;

    appendLog('warn', '⛔ Import stopped by user.');
    setStatus('idle');
}

function setStatus(state) {
    const cfg = statusCfg[state] || statusCfg.idle;
    const badge = document.getElementById('statusBadge');
    badge.className = `stat-pill ${cfg.color}`;
    badge.innerHTML = `<i class="fas fa-${cfg.icon} ${cfg.dot}" style="font-size:9px"></i> ${cfg.label}`;

    const importBtn = document.getElementById('importButton');
    const stopBtn   = document.getElementById('stopButton');
    const label     = document.getElementById('importBtnLabel');

    const isActive = state === 'running' || state === 'uploading';

    importBtn.disabled = isActive || !document.getElementById('fileInput').files[0];
    label.textContent  = isActive
        ? (state === 'uploading' ? 'Uploading…' : 'Importing…')
        : 'Import Data';

    // Stop button is only enabled while actively streaming
    stopBtn.disabled = !isActive;
}
</script>

<?= $this->endSection() ?>