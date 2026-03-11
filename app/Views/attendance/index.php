<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Absensi Harian</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #0a0e1a; --surface: #111827; --surface2: #1a2235;
      --border: rgba(255,255,255,0.08); --accent: #00e5a0; --accent2: #0ea5e9;
      --warn: #f59e0b; --danger: #ef4444; --text: #f1f5f9; --muted: #64748b;
    }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding: 16px; overflow-x: hidden; }
    body::before { content: ''; position: fixed; inset: 0; background: radial-gradient(ellipse 60% 50% at 20% 20%, rgba(0,229,160,0.07) 0%, transparent 60%), radial-gradient(ellipse 50% 60% at 80% 80%, rgba(14,165,233,0.07) 0%, transparent 60%); pointer-events: none; z-index: 0; }

    .wrapper { position: relative; z-index: 1; max-width: 440px; margin: 0 auto; }

    /* Header */
    .header { text-align: center; margin-bottom: 24px; padding-top: 8px; animation: fadeDown 0.6s ease both; }
    .header .badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(0,229,160,0.1); border: 1px solid rgba(0,229,160,0.25); color: var(--accent); font-size: 11px; font-weight: 500; letter-spacing: 0.12em; text-transform: uppercase; padding: 5px 12px; border-radius: 100px; margin-bottom: 14px; }
    .header h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; }
    .header h1 span { color: var(--accent); }
    .header p { font-size: 13px; color: var(--muted); margin-top: 6px; }

    /* Time widget */
    .time-widget { background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; animation: fadeUp 0.6s 0.1s ease both; }
    .time-main { font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
    .time-main span { color: var(--accent); }
    .time-sub { font-size: 11.5px; color: var(--muted); margin-top: 3px; }
    .time-status { text-align: right; }
    .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; }
    .status-pill.masuk   { background: rgba(0,229,160,0.12); color: var(--accent); border: 1px solid rgba(0,229,160,0.2); }
    .status-pill.pulang  { background: rgba(239,68,68,0.12); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
    .status-pill.selesai { background: rgba(100,116,139,0.15); color: var(--muted); border: 1px solid rgba(100,116,139,0.2); }
    .status-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; animation: pulse 1.5s infinite; }
    .status-limit { font-size: 11px; color: var(--muted); margin-top: 4px; }

    /* Card */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; animation: fadeUp 0.6s 0.2s ease both; margin-bottom: 14px; }
    .camera-section { position: relative; background: #000; aspect-ratio: 4/3; overflow: hidden; }
    #videoEl { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); display: block; }
    #canvasEl { display: none; }
    .photo-preview { width: 100%; height: 100%; object-fit: cover; display: none; }
    .camera-overlay { position: absolute; inset: 0; pointer-events: none; }
    .corner { position: absolute; width: 26px; height: 26px; }
    .corner-tl { top: 14px; left: 14px; border-top: 2px solid var(--accent); border-left: 2px solid var(--accent); border-radius: 3px 0 0 0; }
    .corner-tr { top: 14px; right: 14px; border-top: 2px solid var(--accent); border-right: 2px solid var(--accent); border-radius: 0 3px 0 0; }
    .corner-bl { bottom: 14px; left: 14px; border-bottom: 2px solid var(--accent); border-left: 2px solid var(--accent); border-radius: 0 0 0 3px; }
    .corner-br { bottom: 14px; right: 14px; border-bottom: 2px solid var(--accent); border-right: 2px solid var(--accent); border-radius: 0 0 3px 0; }
    .scan-line { position: absolute; left: 14px; right: 14px; height: 1px; background: linear-gradient(90deg, transparent, var(--accent), transparent); animation: scan 2.5s ease-in-out infinite; opacity: 0.6; display: none; }
    @keyframes scan { 0% { top: 14px; opacity: 0; } 10% { opacity: 0.6; } 90% { opacity: 0.6; } 100% { top: calc(100% - 14px); opacity: 0; } }
    .camera-badge { position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1); border-radius: 100px; padding: 3px 10px; font-size: 11px; color: var(--accent); display: none; align-items: center; gap: 5px; pointer-events: none; }
    .rec-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); animation: pulse 1s infinite; }
    .camera-placeholder { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; background: var(--surface2); }
    .camera-placeholder svg { opacity: 0.25; }
    .camera-placeholder p { font-size: 13px; color: var(--muted); }
    .flash { position: absolute; inset: 0; background: white; opacity: 0; pointer-events: none; transition: opacity 0.05s; }
    .flash.active { opacity: 1; }

    /* Selesai overlay — tampil kalau sudah absen masuk & pulang */
    .done-overlay { position: absolute; inset: 0; background: rgba(10,14,26,0.92); backdrop-filter: blur(6px); display: none; align-items: center; justify-content: center; flex-direction: column; gap: 8px; }
    .done-overlay.show { display: flex; }
    .done-overlay svg { color: var(--accent); }
    .done-overlay p { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: var(--accent); }
    .done-overlay small { font-size: 12px; color: var(--muted); }

    .card-body { padding: 16px 18px 18px; }
    .info-row { display: flex; align-items: flex-start; gap: 11px; padding: 12px 14px; background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 10px; }
    .info-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .info-icon.loc { background: rgba(14,165,233,0.12); color: var(--accent2); }
    .info-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px; }
    .info-value { font-size: 13px; font-weight: 500; color: var(--text); line-height: 1.35; }
    .info-value.loading { display: flex; align-items: center; gap: 6px; color: var(--muted); font-size: 13px; }
    .spinner { width: 11px; height: 11px; border: 2px solid rgba(255,255,255,0.1); border-top-color: var(--accent2); border-radius: 50%; animation: spin 0.8s linear infinite; flex-shrink: 0; }
    .btn-row { display: flex; gap: 10px; }
    .btn { flex: 1; padding: 13px; border-radius: 12px; border: none; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn:active { transform: scale(0.97); }
    .btn-camera { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .btn-camera:hover { background: rgba(255,255,255,0.07); }
    .btn-submit { background: linear-gradient(135deg, var(--accent) 0%, #00c88a 100%); color: #0a0e1a; flex: 1.6; box-shadow: 0 4px 18px rgba(0,229,160,0.25); }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(0,229,160,0.35); }
    .btn-submit:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }
    .error-msg { display: none; align-items: center; gap: 8px; padding: 10px 13px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; font-size: 12.5px; color: #fca5a5; margin-bottom: 10px; }
    .error-msg.show { display: flex; }

    /* ── Today status bar ── */
    .today-bar { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 14px 16px; margin-bottom: 14px; animation: fadeUp 0.6s 0.25s ease both; }
    .today-bar .tb-title { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px; }
    .today-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .today-check { background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; padding: 10px 12px; display: flex; align-items: center; gap: 10px; }
    .today-check.done { border-color: rgba(0,229,160,0.25); background: rgba(0,229,160,0.06); }
    .today-check.done-pulang { border-color: rgba(239,68,68,0.25); background: rgba(239,68,68,0.06); }
    .check-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .today-check .check-icon { background: rgba(255,255,255,0.05); color: var(--muted); }
    .today-check.done .check-icon { background: rgba(0,229,160,0.15); color: var(--accent); }
    .today-check.done-pulang .check-icon { background: rgba(239,68,68,0.15); color: var(--danger); }
    .check-info .c-label { font-size: 10.5px; color: var(--muted); }
    .check-info .c-val { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: var(--text); line-height: 1.1; margin-top: 1px; }
    .check-info .c-val.empty { color: var(--muted); font-size: 13px; font-family: 'DM Sans', sans-serif; font-weight: 400; }

    /* ── History list ── */
    .history-section { animation: fadeUp 0.6s 0.3s ease both; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .section-header h3 { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; }
    .section-header a { font-size: 12px; color: var(--accent); text-decoration: none; }

    .record-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; margin-bottom: 8px; overflow: hidden; cursor: pointer; transition: border-color 0.2s; }
    .record-card:hover { border-color: rgba(255,255,255,0.14); }
    .record-main { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }
    .r-date-box { width: 42px; height: 42px; border-radius: 11px; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; }
    .r-date-box.masuk  { background: rgba(0,229,160,0.1); }
    .r-date-box.pulang { background: rgba(239,68,68,0.1); }
    .r-date-box .r-day   { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 800; line-height: 1; }
    .r-date-box .r-mon   { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); }
    .r-date-box.masuk  .r-day { color: var(--accent); }
    .r-date-box.pulang .r-day { color: var(--danger); }
    .r-info { flex: 1; min-width: 0; }
    .r-info .r-name { font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; margin-bottom: 2px; }
    .r-badge { font-size: 9.5px; font-weight: 500; padding: 2px 7px; border-radius: 100px; }
    .r-badge.masuk  { background: rgba(0,229,160,0.12); color: var(--accent); border: 1px solid rgba(0,229,160,0.2); }
    .r-badge.pulang { background: rgba(239,68,68,0.12); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
    .r-badge.telat  { background: rgba(245,158,11,0.12); color: var(--warn); border: 1px solid rgba(245,158,11,0.2); }
    .r-info .r-addr { font-size: 11.5px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .r-time { text-align: right; flex-shrink: 0; }
    .r-time .r-jam  { font-family: 'Syne', sans-serif; font-size: 17px; font-weight: 700; letter-spacing: -0.02em; }
    .r-time .r-wib  { font-size: 10px; color: var(--muted); }

    .record-detail { display: none; border-top: 1px solid var(--border); padding: 11px 14px; background: var(--surface2); gap: 11px; align-items: flex-start; }
    .record-detail.open { display: flex; }
    .d-photo { width: 70px; height: 70px; border-radius: 10px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); background: var(--surface); }
    .d-photo-ph { width: 70px; height: 70px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .d-rows { flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .d-row .d-lbl { font-size: 9.5px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
    .d-row .d-val { font-size: 12px; color: var(--text); }

    .empty-state { text-align: center; padding: 40px 20px; }
    .empty-state svg { opacity: 0.15; margin-bottom: 12px; }
    .empty-state p { color: var(--muted); font-size: 13.5px; }

    /* Success overlay */
    .success-overlay { display: none; position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.88); backdrop-filter: blur(14px); align-items: center; justify-content: center; flex-direction: column; gap: 14px; }
    .success-overlay.show { display: flex; }
    .success-icon { width: 76px; height: 76px; border-radius: 50%; background: rgba(0,229,160,0.15); border: 2px solid var(--accent); display: flex; align-items: center; justify-content: center; animation: popIn 0.4s 0.05s cubic-bezier(0.34,1.56,0.64,1) both; }
    .success-title { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: var(--accent); animation: fadeUp 0.4s 0.15s ease both; }
    .success-sub { font-size: 13px; color: var(--muted); text-align: center; padding: 0 24px; animation: fadeUp 0.4s 0.2s ease both; }
    .btn-close-success { margin-top: 6px; padding: 12px 32px; border-radius: 100px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 14px; cursor: pointer; animation: fadeUp 0.4s 0.25s ease both; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn-close-success:hover { background: var(--surface2); }

    @keyframes fadeDown { from { opacity:0; transform:translateY(-16px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeUp   { from { opacity:0; transform:translateY(16px);  } to { opacity:1; transform:translateY(0); } }
    @keyframes spin     { to { transform: rotate(360deg); } }
    @keyframes pulse    { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
    @keyframes popIn    { from { transform:scale(0); opacity:0; } to { transform:scale(1); opacity:1; } }
  </style>
</head>
<body>
<div class="wrapper">

<?php
  // ── Data dari controller ──────────────────────────────
  // $todayMasuk  = record absen masuk hari ini (null jika belum)
  // $todayPulang = record absen pulang hari ini (null jika belum)
  // $records     = riwayat 10 absen terakhir

  $sudahMasuk  = !empty($todayMasuk);
  $sudahPulang = !empty($todayPulang);

  // Tentukan tipe absen berikutnya BERDASARKAN DATA, bukan jam
  if (!$sudahMasuk) {
      $nextType  = 'masuk';
      $pillClass = 'masuk';
      $pillLabel = 'Absen Masuk';
  } elseif (!$sudahPulang) {
      $nextType  = 'pulang';
      $pillClass = 'pulang';
      $pillLabel = 'Absen Pulang';
  } else {
      $nextType  = null;   // sudah lengkap
      $pillClass = 'selesai';
      $pillLabel = 'Selesai';
  }
?>

  <!-- Header -->
  <div class="header">
    <div class="badge"><svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg> Sistem Absensi</div>
    <h1>Catat <span>Kehadiran</span><br>Kamu Hari Ini</h1>
    <p>Pastikan wajah terlihat jelas & lokasi aktif</p>
  </div>

  <!-- Time widget -->
  <div class="time-widget">
    <div>
      <div class="time-main" id="clockDisplay">00<span>:</span>00</div>
      <div class="time-sub" id="dateDisplay">—</div>
    </div>
    <div class="time-status">
      <div class="status-pill <?= $pillClass ?>" id="statusPill">
        <?php if ($nextType): ?><span class="dot"></span><?php endif; ?>
        <?= $pillLabel ?>
      </div>
      <div class="status-limit">Batas masuk: 08:00</div>
    </div>
  </div>

  <!-- Today status bar -->
  <div class="today-bar">
    <div class="tb-title">Status Hari Ini</div>
    <div class="today-checks">
      <!-- Masuk -->
      <div class="today-check <?= $sudahMasuk ? 'done' : '' ?>">
        <div class="check-icon">
          <?php if ($sudahMasuk): ?>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <?php else: ?>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
          <?php endif; ?>
        </div>
        <div class="check-info">
          <div class="c-label">Masuk</div>
          <div class="c-val <?= $sudahMasuk ? '' : 'empty' ?>">
            <?= $sudahMasuk ? date('H:i', strtotime($todayMasuk['created_at'])) : '—' ?>
          </div>
        </div>
      </div>
      <!-- Pulang -->
      <div class="today-check <?= $sudahPulang ? 'done-pulang' : '' ?>">
        <div class="check-icon">
          <?php if ($sudahPulang): ?>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <?php else: ?>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
          <?php endif; ?>
        </div>
        <div class="check-info">
          <div class="c-label">Pulang</div>
          <div class="c-val <?= $sudahPulang ? '' : 'empty' ?>">
            <?= $sudahPulang ? date('H:i', strtotime($todayPulang['created_at'])) : '—' ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form absen (sembunyikan kalau sudah lengkap) -->
  <?php if ($nextType): ?>
  <div class="card">
    <div class="camera-section">
      <div class="camera-placeholder" id="camPlaceholder">
        <svg width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="1.5"/></svg>
        <p>Kamera belum aktif</p>
      </div>
      <video id="videoEl" autoplay muted playsinline></video>
      <canvas id="canvasEl"></canvas>
      <canvas id="faceCanvas" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:3;"></canvas>
      <img id="photoPreview" class="photo-preview" alt="Foto absen" />
      <div class="flash" id="flash"></div>
      <div class="camera-overlay">
        <div class="corner corner-tl"></div><div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div><div class="corner corner-br"></div>
        <div class="scan-line" id="scanLine"></div>
      </div>
      <div class="camera-badge" id="liveBadge"><span class="rec-dot"></span> LIVE</div>
      <div id="faceStatus" style="display:none;position:absolute;bottom:12px;left:50%;transform:translateX(-50%);font-size:11px;font-weight:600;padding:4px 12px;border-radius:100px;border:1px solid;white-space:nowrap;z-index:4;pointer-events:none;backdrop-filter:blur(8px);background:rgba(0,0,0,0.5);color:var(--warn);border-color:rgba(245,158,11,0.4);transition:color .3s,border-color .3s;">⟳ Posisikan wajah</div>
    </div>
    <div class="card-body">
      <div class="error-msg" id="errorMsg">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span id="errorText"></span>
      </div>
      <div class="info-row">
        <div class="info-icon loc">
          <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div style="flex:1">
          <div class="info-label">Lokasi</div>
          <div class="info-value loading" id="locationVal"><div class="spinner"></div> Mendeteksi...</div>
        </div>
      </div>
      <div class="btn-row">
        <button class="btn btn-camera" id="btnCamera" onclick="startCamera()">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="1.5"/></svg>
          Kamera
        </button>
        <button class="btn btn-submit" id="btnSubmit" disabled onclick="submitAbsen()">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Absen <?= ucfirst($nextType) ?>
        </button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- History 10 terakhir -->
  <div class="history-section">
    <div class="section-header">
      <h3>Riwayat Absen</h3>
      <a href="<?= base_url('attendance/history') ?>">Lihat semua →</a>
    </div>

    <?php if (empty($records)): ?>
      <div class="empty-state">
        <svg width="56" height="56" fill="none" viewBox="0 0 24 24" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p>Belum ada riwayat absensi</p>
      </div>
    <?php else: ?>
      <?php
        $monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $dayNames   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        foreach ($records as $i => $r):
          $ts      = strtotime($r['created_at']);
          $type    = $r['type'];
          $isTelat = ($type === 'masuk' && date('H', $ts) >= 8);
          $photo   = !empty($r['photo']) ? base_url('writable/' . $r['photo']) : null;
      ?>
        <div class="record-card" onclick="toggleDetail(<?= $i ?>)">
          <div class="record-main">
            <div class="r-date-box <?= $type ?>">
              <span class="r-day"><?= date('j', $ts) ?></span>
              <span class="r-mon"><?= $monthNames[(int)date('n', $ts)] ?></span>
            </div>
            <div class="r-info">
              <div class="r-name">
                <?= $dayNames[date('w', $ts)] ?>
                <span class="r-badge <?= $isTelat ? 'telat' : $type ?>">
                  <?= $isTelat ? 'Telat' : ($type === 'masuk' ? 'Masuk' : 'Pulang') ?>
                </span>
              </div>
              <div class="r-addr"><?= esc(!empty($r['address']) ? $r['address'] : $r['latitude'].', '.$r['longitude']) ?></div>
            </div>
            <div class="r-time">
              <div class="r-jam"><?= date('H:i', $ts) ?></div>
              <div class="r-wib">WIB</div>
            </div>
          </div>
          <div class="record-detail" id="detail-<?= $i ?>">
            <?php if ($photo): ?>
              <img src="<?= $photo ?>" class="d-photo" alt="" onerror="this.style.display='none'" />
            <?php else: ?>
              <div class="d-photo-ph"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg></div>
            <?php endif; ?>
            <div class="d-rows">
              <div class="d-row"><span class="d-lbl">Waktu</span><span class="d-val"><?= date('d/m/Y H:i', $ts) ?> WIB</span></div>
              <div class="d-row"><span class="d-lbl">Koordinat</span><span class="d-val"><?= number_format($r['latitude'],5) ?>, <?= number_format($r['longitude'],5) ?></span></div>
              <?php if (!empty($r['accuracy'])): ?>
              <div class="d-row"><span class="d-lbl">Akurasi</span><span class="d-val">±<?= round($r['accuracy']) ?>m</span></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div><!-- /wrapper -->

<!-- Success overlay -->
<div class="success-overlay" id="successOverlay">
  <div class="success-icon"><svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="#00e5a0" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
  <div class="success-title">Absen Berhasil! 🎉</div>
  <div class="success-sub" id="successDetail">—</div>
  <a href="<?= base_url('dashboard') ?>" class="btn-close-success">Ke Dashboard</a>
</div>

<script>
  const BASE_URL   = '<?= base_url() ?>';
  const CSRF_NAME  = '<?= csrf_token() ?>';
  const CSRF_TOKEN = '<?= csrf_hash() ?>';
  // FIX: tipe absen diambil dari PHP (berdasarkan data DB), bukan dari jam
  const NEXT_TYPE  = '<?= $nextType ?>'; // 'masuk', 'pulang', atau ''

  let stream = null, photoTaken = false, locationData = null, cameraActive = false, photoBlob = null;

  // ── Face Detection — pixel analysis + consecutive frame guard ──
  // Fix 1: warna coklat benda → tambah filter variance & saturation
  // Fix 2: state latch → faceDetected hanya true jika 3 frame BERTURUT-TURUT
  //         dan langsung false begitu 1 frame tidak terdeteksi
  let faceDetected     = false;
  let faceLoopTimer    = null;
  let consecutiveHits  = 0;        // harus 3 frame berturut sebelum "terdeteksi"
  const FRAMES_NEEDED  = 3;        // anti false-positive sesaat
  const _sampleCanvas  = document.createElement('canvas');
  _sampleCanvas.width  = 80;
  _sampleCanvas.height = 60;
  const _sampleCtx = _sampleCanvas.getContext('2d', { willReadFrequently: true });

  function isSkinPixel(r, g, b) {
    // [A] Buang terlalu gelap / terlalu terang
    if (r < 60  || g < 30  || b < 15)  return false;
    if (r > 245 && g > 220 && b > 200) return false;   // overexpose

    // [B] R harus paling dominan, dan selisih R-G tidak boleh terlalu kecil
    // → menyaring abu-abu & putih
    if (r <= g || r <= b)              return false;
    if ((r - g) < 15)                  return false;

    // [C] Saturation check — warna benda mati (kayu/tembok coklat) cenderung
    // memiliki saturation tinggi ATAU terlalu monoton.
    // Warna kulit punya saturation menengah (20–70%).
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const sat = max === 0 ? 0 : (max - min) / max;
    if (sat < 0.10 || sat > 0.75) return false;       // terlalu pucat / terlalu jenuh

    // [D] YCbCr — range yang sudah terbukti cover semua tone kulit
    const cb = 128 - 0.168736 * r - 0.331264 * g + 0.5   * b;
    const cr = 128 + 0.5      * r - 0.418688 * g - 0.081312 * b;
    if (cb < 80 || cb > 125)  return false;
    if (cr < 135 || cr > 170) return false;

    // [E] Variance guard — kulit punya variasi warna antar channel
    // Benda coklat solid cenderung R≈G*1.x dengan B sangat rendah
    // Kulit: B tidak terlalu jauh dari G
    if (b < g * 0.35) return false;                   // B terlalu rendah = kayu/tanah

    return true;
  }

  // Hitung rata-rata brightness seluruh frame
  function getAvgBrightness(data) {
    let sum = 0;
    const total = data.length / 4;
    for (let i = 0; i < data.length; i += 4) {
      sum += (data[i] * 0.299 + data[i+1] * 0.587 + data[i+2] * 0.114);
    }
    return sum / total; // 0–255
  }

  // Deteksi backlight: area tepi jauh lebih terang dari area tengah
  function isBacklit(fullData, centerData) {
    const fullBright   = getAvgBrightness(fullData);
    const centerBright = getAvgBrightness(centerData);
    // Kalau tepi rata-rata > 1.6x lebih terang dari tengah → backlit
    return fullBright > 60 && centerBright < fullBright * 0.55;
  }

  // Normalize brightness pixel sebelum cek warna kulit
  // Kalau frame gelap, angkat semua channel secara proporsional
  function normalizeBrightness(r, g, b, factor) {
    return [
      Math.min(255, Math.round(r * factor)),
      Math.min(255, Math.round(g * factor)),
      Math.min(255, Math.round(b * factor)),
    ];
  }

  function getSkinRatio(imgData, brightnessFactor) {
    let skinCount = 0;
    const total   = imgData.data.length / 4;
    for (let i = 0; i < imgData.data.length; i += 4) {
      let r = imgData.data[i], g = imgData.data[i+1], b = imgData.data[i+2];
      if (brightnessFactor !== 1) {
        [r, g, b] = normalizeBrightness(r, g, b, brightnessFactor);
      }
      if (isSkinPixel(r, g, b)) skinCount++;
    }
    return skinCount / total;
  }

  function runFaceDetection() {
    if (!cameraActive || photoTaken) return;
    const video = document.getElementById('videoEl');
    if (!video || video.readyState < 2 || video.videoWidth === 0) {
      faceLoopTimer = setTimeout(runFaceDetection, 300);
      return;
    }

    // Gambar frame ke canvas kecil
    _sampleCtx.save();
    _sampleCtx.translate(_sampleCanvas.width, 0);
    _sampleCtx.scale(-1, 1);
    _sampleCtx.drawImage(video, 0, 0, _sampleCanvas.width, _sampleCanvas.height);
    _sampleCtx.restore();

    let fullData, centerData;
    try {
      // Data seluruh frame untuk deteksi backlight
      fullData = _sampleCtx.getImageData(0, 0, _sampleCanvas.width, _sampleCanvas.height);
      // Area tengah oval untuk deteksi wajah
      const sw = Math.floor(_sampleCanvas.width  * 0.40);
      const sh = Math.floor(_sampleCanvas.height * 0.55);
      const sx = Math.floor((_sampleCanvas.width  - sw) / 2);
      const sy = Math.floor(_sampleCanvas.height  * 0.08);
      centerData = _sampleCtx.getImageData(sx, sy, sw, sh);
    } catch(_) {
      faceLoopTimer = setTimeout(runFaceDetection, 400);
      return;
    }

    // Cek backlight — kalau iya, hitung faktor normalisasi
    const backlit     = isBacklit(fullData.data, centerData.data);
    const centerBright = getAvgBrightness(centerData.data);
    // Target brightness ~110 (cukup untuk deteksi), max factor 3x
    const brightFactor = backlit
      ? Math.min(3.0, centerBright < 10 ? 3.0 : 110 / centerBright)
      : 1;

    // Threshold lebih longgar saat backlit karena normalisasi tidak sempurna
    const threshold = backlit ? 0.07 : 0.12;
    const ratio     = getSkinRatio(centerData, brightFactor);
    const hasFrame  = ratio >= threshold;

    if (hasFrame) {
      consecutiveHits = Math.min(consecutiveHits + 1, FRAMES_NEEDED);
    } else {
      consecutiveHits = 0;
      faceDetected    = false;
    }

    if (consecutiveHits >= FRAMES_NEEDED) faceDetected = true;

    updateFaceStatus(faceDetected, ratio, backlit);

    if (cameraActive && !photoTaken) {
      faceLoopTimer = setTimeout(runFaceDetection, 350);
    }
  }

  function updateFaceStatus(detected, ratio, backlit) {
    const status = document.getElementById('faceStatus');
    if (!status) return;
    if (detected) {
      status.textContent       = backlit ? '✓ Wajah terdeteksi (backlit)' : '✓ Wajah terdeteksi';
      status.style.color       = '#00e5a0';
      status.style.borderColor = 'rgba(0,229,160,0.4)';
    } else if (backlit) {
      // Peringatan khusus backlight — lebih informatif
      status.textContent       = '⚠ Hindari cahaya di belakang';
      status.style.color       = '#ef4444';
      status.style.borderColor = 'rgba(239,68,68,0.4)';
    } else {
      const hint = ratio > 0.05 ? '⟳ Dekatkan wajah' : '⟳ Posisikan wajah';
      status.textContent       = hint;
      status.style.color       = 'var(--warn)';
      status.style.borderColor = 'rgba(245,158,11,0.4)';
    }
    drawGuideBox(detected, backlit);
  }

  function drawGuideBox(detected, backlit) {
    const canvas = document.getElementById('faceCanvas');
    const video  = document.getElementById('videoEl');
    if (!canvas || !video) return;
    const rect = video.getBoundingClientRect();
    canvas.width  = rect.width;
    canvas.height = rect.height;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const cx = canvas.width  / 2;
    const cy = canvas.height * 0.40;
    const rw = canvas.width  * 0.27;
    const rh = canvas.height * 0.36;

    // Warna: hijau = ok, merah = backlit, kuning = belum ada wajah
    const color = detected ? 'rgba(0,229,160,0.9)'
                : backlit  ? 'rgba(239,68,68,0.8)'
                :            'rgba(245,158,11,0.55)';
    const dotColor = detected ? '#00e5a0'
                   : backlit  ? '#ef4444'
                   :            'rgba(245,158,11,0.8)';

    ctx.beginPath();
    ctx.ellipse(cx, cy, rw, rh, 0, 0, Math.PI * 2);
    ctx.strokeStyle = color;
    ctx.lineWidth   = 2.5;
    ctx.setLineDash(detected ? [] : [7, 4]);
    ctx.stroke();
    ctx.setLineDash([]);

    // Kalau backlit, tambahkan overlay gelap di area luar oval sebagai hint visual
    if (backlit && !detected) {
      ctx.fillStyle = 'rgba(239,68,68,0.06)';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      // Hapus area oval agar tetap bersih
      ctx.globalCompositeOperation = 'destination-out';
      ctx.beginPath();
      ctx.ellipse(cx, cy, rw, rh, 0, 0, Math.PI * 2);
      ctx.fill();
      ctx.globalCompositeOperation = 'source-over';
    }

    [[cx,cy-rh],[cx+rw,cy],[cx,cy+rh],[cx-rw,cy]].forEach(([x,y])=>{
      ctx.beginPath();
      ctx.arc(x, y, 3.5, 0, Math.PI*2);
      ctx.fillStyle = dotColor;
      ctx.fill();
    });
  }

  function stopFaceDetection() {
    if (faceLoopTimer) { clearTimeout(faceLoopTimer); faceLoopTimer = null; }
    faceDetected    = false;
    consecutiveHits = 0;
    const canvas = document.getElementById('faceCanvas');
    if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    const status = document.getElementById('faceStatus');
    if (status) status.style.display = 'none';
  }

  // ── Jam ──
  function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    document.getElementById('clockDisplay').innerHTML = `${h}<span>:</span>${m}`;
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    document.getElementById('dateDisplay').textContent =
      `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }
  updateClock(); setInterval(updateClock, 1000);

  // ── Lokasi ──
  function getLocation() {
    if (!navigator.geolocation) { setLocation('Tidak didukung', true); return; }
    navigator.geolocation.getCurrentPosition(async (pos) => {
      const { latitude, longitude, accuracy } = pos.coords;
      locationData = { latitude, longitude, accuracy };
      try {
        const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`);
        const d = await r.json();
        const a = d.address;
        const parts = [a.road, a.suburb || a.village, a.city || a.town].filter(Boolean);
        setLocation(parts.join(', ') || `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`);
      } catch { setLocation(`${latitude.toFixed(5)}, ${longitude.toFixed(5)}`); }
      checkReady();
    }, (e) => {
      setLocation({1:'Izin lokasi ditolak',2:'Lokasi tidak tersedia',3:'Timeout'}[e.code] || 'Gagal', true);
    }, { enableHighAccuracy: true, timeout: 10000 });
  }
  function setLocation(text, isErr = false) {
    const el = document.getElementById('locationVal');
    el.className = 'info-value'; el.style.color = isErr ? 'var(--danger)' : 'var(--text)';
    el.textContent = text; if (isErr) locationData = null;
  }
  if (NEXT_TYPE) getLocation();

  // ── Kamera ──
  async function startCamera() {
    if (cameraActive && !photoTaken) { takePhoto(); return; }
    if (photoTaken) { retakePhoto(); return; }
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode:'user', width:{ideal:640}, height:{ideal:480} }, audio: false });
      const v = document.getElementById('videoEl');
      v.srcObject = stream;
      document.getElementById('camPlaceholder').style.display = 'none';
      document.getElementById('liveBadge').style.display      = 'flex';
      document.getElementById('scanLine').style.display       = 'block';
      document.getElementById('btnCamera').innerHTML = `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg> Ambil Foto`;
      cameraActive = true; hideError();
      // Mulai deteksi wajah setelah video siap
      const status = document.getElementById('faceStatus');
      if (status) status.style.display = 'block';
      v.addEventListener('loadeddata', () => { runFaceDetection(); }, { once: true });
    } catch { showError('Kamera tidak dapat diakses. Cek izin.'); }
  }

  function takePhoto() {
    // Cek wajah — blokir jika tidak terdeteksi
    if (!faceDetected) {
      showError('Wajah tidak terdeteksi. Hadapkan wajah ke kamera dengan pencahayaan cukup.');
      return;
    }
    stopFaceDetection();
    const v = document.getElementById('videoEl'), c = document.getElementById('canvasEl');
    const p = document.getElementById('photoPreview'), f = document.getElementById('flash');
    c.width = v.videoWidth||640; c.height = v.videoHeight||480;
    const ctx = c.getContext('2d');
    ctx.save(); ctx.translate(c.width,0); ctx.scale(-1,1); ctx.drawImage(v,0,0); ctx.restore();
    f.classList.add('active'); setTimeout(()=>f.classList.remove('active'),150);
    c.toBlob((b)=>{ photoBlob=b; },'image/jpeg',0.85);
    p.src = c.toDataURL('image/jpeg',0.85); p.style.display='block'; v.style.display='none';
    if(stream){stream.getTracks().forEach(t=>t.stop());stream=null;}
    document.getElementById('liveBadge').style.display='none';
    document.getElementById('scanLine').style.display='none';
    const btn = document.getElementById('btnCamera');
    btn.innerHTML=`<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Ulangi`;
    btn.style.color='var(--warn)';
    photoTaken=true; cameraActive=false; checkReady();
  }

  function retakePhoto() {
    stopFaceDetection();
    document.getElementById('photoPreview').style.display='none';
    document.getElementById('videoEl').style.display='block';
    photoTaken=false; cameraActive=false; photoBlob=null;
    const btn=document.getElementById('btnCamera');
    btn.innerHTML=`<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="1.5"/></svg> Kamera`;
    btn.style.color=''; document.getElementById('btnSubmit').disabled=true; startCamera();
  }

  function checkReady() {
    const b = document.getElementById('btnSubmit');
    if (b) b.disabled = !(photoTaken && locationData !== null);
  }

  // ── Submit ──
  async function submitAbsen() {
    if (!photoBlob || !locationData || !NEXT_TYPE) return;
    const btn = document.getElementById('btnSubmit');
    btn.disabled=true;
    btn.innerHTML=`<div class="spinner" style="border-top-color:#0a0e1a;width:15px;height:15px;flex-shrink:0"></div> Mengirim...`;

    const fd = new FormData();
    fd.append('photo',    photoBlob, `absen_${Date.now()}.jpg`);
    fd.append('latitude', locationData.latitude);
    fd.append('longitude',locationData.longitude);
    fd.append('accuracy', locationData.accuracy ?? 0);
    fd.append('type',     NEXT_TYPE);   // langsung pakai nilai dari PHP
    fd.append('address',  document.getElementById('locationVal').textContent);
    fd.append(CSRF_NAME,  CSRF_TOKEN);

    try {
      const res  = await fetch(`${BASE_URL}attendance/store`, { method:'POST', body:fd });
      const text = await res.text();
      let json;
      try { json = JSON.parse(text); } catch { showError('Respons server tidak valid.'); resetBtn(); return; }

      if (json.success) {
        const now = new Date();
        const h = String(now.getHours()).padStart(2,'0');
        const m = String(now.getMinutes()).padStart(2,'0');
        document.getElementById('successDetail').textContent =
          `Absen ${NEXT_TYPE === 'masuk' ? 'Masuk' : 'Pulang'} — ${h}:${m} • ${json.message}`;
        document.getElementById('successOverlay').classList.add('show');
        // Auto redirect ke dashboard setelah 2 detik
        setTimeout(() => { window.location.href = json.redirect_url || `${BASE_URL}dashboard`; }, 2000);
      } else {
        showError(json.message || 'Gagal menyimpan absen.'); resetBtn();
      }
    } catch { showError('Gagal terhubung ke server.'); resetBtn(); }
  }

  function resetBtn() {
    const btn = document.getElementById('btnSubmit');
    if (!btn) return;
    btn.disabled=false;
    btn.innerHTML=`<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Absen ${NEXT_TYPE==='masuk'?'Masuk':'Pulang'}`;
  }

  function toggleDetail(id) { document.getElementById('detail-'+id).classList.toggle('open'); }
  function showError(msg) { document.getElementById('errorText').textContent=msg; document.getElementById('errorMsg').classList.add('show'); }
  function hideError() { document.getElementById('errorMsg').classList.remove('show'); }
</script>
</body>
</html>