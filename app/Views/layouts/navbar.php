
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── Design Tokens — Light Warm ── */
:root {
  --bg:           #ffffff;
  --bg-subtle:    #fdf8f4;
  --surface:      #ffffff;
  --border:       #ede8e3;
  --border-soft:  #f3ede8;
  --hover:        #faf5f0;
  --text-strong:  #1c1917;
  --text-base:    #57534e;
  --text-muted:   #a8a29e;

  /* Brand accent — orange */
  --accent:       #e8925e;
  --accent-dark:  #d97b45;
  --accent-soft:  #fef3eb;
  --accent-mid:   rgba(232,146,94,0.15);

  /* Status */
  --warn:         #d97706;
  --warn-soft:    #fffbeb;
  --danger:       #dc2626;
  --danger-soft:  #fef2f2;
  --success:      #16a34a;
  --success-soft: #f0fdf4;
  --info:         #2563eb;
  --info-soft:    #eff6ff;

  --radius:       10px;
  --font:         'DM Sans', sans-serif;
  --mono:         'DM Mono', monospace;
  --hdr:          62px;
  --shadow-sm:    0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
  --shadow-drop:  0 8px 32px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
  font-family: var(--font);
  background: var(--bg-subtle);
  color: var(--text-base);
  min-height: 100vh;
}

/* ════════════════════════════════
   TOPBAR
   ════════════════════════════════ */
.topbar {
  height: var(--hdr);
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 20px 0 18px;
  gap: 10px;
  position: sticky;
  top: 0;
  z-index: 500;
  box-shadow: var(--shadow-sm);
  /* Left accent line matching sidebar brand colour */
  border-left: 3px solid var(--accent);
}

/* ── Hamburger ── */
.menu-toggle {
  display: flex; flex-direction: column;
  justify-content: center; gap: 5px;
  width: 32px; height: 32px;
  background: none; border: none;
  cursor: pointer; padding: 4px; border-radius: 7px;
  flex-shrink: 0; transition: background .15s;
}
.menu-toggle:hover { background: var(--hover); }
.menu-toggle span {
  display: block; height: 2px; border-radius: 2px;
  background: var(--text-muted); transition: background .15s;
}
.menu-toggle span:nth-child(1) { width: 18px; }
.menu-toggle span:nth-child(2) { width: 13px; }
.menu-toggle span:nth-child(3) { width: 18px; }
.menu-toggle:hover span { background: var(--text-strong); }

/* ── Title block ── */
.topbar-title { flex: 1; min-width: 0; }
.topbar-title h1 {
  font-size: 16px; font-weight: 600; color: var(--text-strong);
  line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.topbar-bc {
  display: inline-flex; align-items: center; gap: 3px;
  margin-top: 3px;
  font-size: 11px; color: var(--text-muted); font-family: var(--mono);
  background: var(--border-soft);
  padding: 2px 8px; border-radius: 4px;
}
.topbar-bc span { color: var(--accent-dark); font-weight: 500; }

/* ── Actions ── */
.topbar-actions {
  display: flex; align-items: center; gap: 6px;
  margin-left: auto; flex-shrink: 0;
}

/* ── Search ── */
.tb-search {
  display: flex; align-items: center; gap: 8px;
  background: var(--bg-subtle); border: 1px solid var(--border);
  border-radius: 8px; padding: 0 12px; height: 34px;
  transition: border-color .2s, box-shadow .2s;
}
.tb-search:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-mid);
}
.tb-search i { font-size: 11px; color: var(--text-muted); }
.tb-search input {
  background: none; border: none; outline: none;
  font-family: var(--font); font-size: 12.5px;
  color: var(--text-strong); width: 150px;
}
.tb-search input::placeholder { color: var(--text-muted); }

/* ── Icon button ── */
.tb-btn {
  position: relative;
  width: 34px; height: 34px;
  background: var(--bg-subtle); border: 1px solid var(--border);
  border-radius: 8px; color: var(--text-muted);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  font-size: 14.5px; transition: all .15s; flex-shrink: 0;
}
.tb-btn:hover {
  background: var(--accent-soft);
  border-color: var(--accent);
  color: var(--accent);
}
.tb-btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

/* Badge */
.tb-badge {
  position: absolute; top: -4px; right: -4px;
  min-width: 16px; height: 16px; padding: 0 4px;
  background: var(--danger); color: #fff;
  font-family: var(--mono); font-size: 9px; font-weight: 700;
  border-radius: 20px; display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--bg);
  animation: pulse-badge 2.5s ease infinite;
}
@keyframes pulse-badge {
  0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.4); }
  55%      { box-shadow: 0 0 0 5px rgba(220,38,38,0); }
}

/* Divider */
.tb-divider { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; margin: 0 2px; }

/* ── User pill ── */
.tb-user {
  display: flex; align-items: center; gap: 9px;
  height: 34px; padding: 0 10px 0 4px;
  background: var(--bg-subtle); border: 1px solid var(--border);
  border-radius: 8px; cursor: pointer;
  transition: all .15s; user-select: none; position: relative;
}
.tb-user:hover { background: var(--accent-soft); border-color: var(--accent); }
.tb-user.open  { background: var(--accent-soft); border-color: var(--accent); }

.tb-avatar {
  width: 26px; height: 26px; border-radius: 7px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(232,146,94,.35);
}
.tb-user-info { line-height: 1.25; }
.tb-user-name { font-size: 12.5px; font-weight: 600; color: var(--text-strong); white-space: nowrap; }
.tb-user-role { font-size: 10px; color: var(--text-muted); font-family: var(--mono); white-space: nowrap; }
.tb-chevron   { font-size: 9px; color: var(--text-muted); transition: transform .25s; margin-left: 2px; }
.tb-user.open .tb-chevron { transform: rotate(180deg); }

/* ════════════════════════════════
   DROPDOWN BASE
   ════════════════════════════════ */
.dropdown {
  position: absolute;
  top: calc(100% + 8px); right: 0;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-drop);
  z-index: 600;
  opacity: 0; visibility: hidden; pointer-events: none;
  transform: translateY(-6px);
  transition: opacity .2s, transform .2s, visibility .2s;
}
.dropdown.open {
  opacity: 1; visibility: visible; pointer-events: all;
  transform: translateY(0);
}

/* ════════════════════════════════
   NOTIFICATION DROPDOWN
   ════════════════════════════════ */
.notif-drop { width: 370px; }

.notif-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px 11px;
  border-bottom: 1px solid var(--border);
}
.notif-head-left  { display: flex; align-items: center; gap: 8px; }
.notif-head-title { font-size: 13.5px; font-weight: 600; color: var(--text-strong); }
.notif-count-pill {
  background: var(--danger); color: #fff;
  font-family: var(--mono); font-size: 9.5px; font-weight: 700;
  padding: 2px 7px; border-radius: 20px;
}
.notif-mark-all {
  font-size: 11.5px; color: var(--accent-dark);
  background: none; border: none; cursor: pointer;
  font-family: var(--font); font-weight: 500;
  padding: 4px 8px; border-radius: 6px; transition: background .15s;
}
.notif-mark-all:hover { background: var(--accent-soft); }

/* Tabs */
.notif-tabs {
  display: flex; gap: 0; padding: 0 14px;
  border-bottom: 1px solid var(--border);
}
.ntab {
  padding: 8px 12px; font-size: 12px; font-weight: 500;
  color: var(--text-muted); cursor: pointer;
  border-bottom: 2px solid transparent; margin-bottom: -1px;
  transition: color .15s; white-space: nowrap;
}
.ntab:hover { color: var(--text-strong); }
.ntab.active { color: var(--accent-dark); border-bottom-color: var(--accent); font-weight: 600; }

/* List */
.notif-list {
  max-height: 330px; overflow-y: auto;
  scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.notif-list::-webkit-scrollbar { width: 3px; }
.notif-list::-webkit-scrollbar-thumb { background: var(--border); }

.notif-item {
  display: flex; align-items: flex-start; gap: 11px;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-soft);
  transition: background .15s; cursor: pointer; position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--hover); }
.notif-item.unread { background: #fffaf6; }
.notif-item.unread::before {
  content: ''; position: absolute;
  left: 0; top: 0; bottom: 0; width: 3px;
  background: var(--accent); border-radius: 0 3px 3px 0;
}

.notif-icon-wrap {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: 14px;
}
.notif-icon-wrap.warn    { background: var(--warn-soft);    color: var(--warn); }
.notif-icon-wrap.info    { background: var(--info-soft);    color: var(--info); }
.notif-icon-wrap.success { background: var(--success-soft); color: var(--success); }
.notif-icon-wrap.danger  { background: var(--danger-soft);  color: var(--danger); }
.notif-icon-wrap.orange  { background: var(--accent-soft);  color: var(--accent-dark); }

.notif-body { flex: 1; min-width: 0; }
.notif-title { font-size: 12.5px; font-weight: 600; color: var(--text-strong); line-height: 1.4; }
.notif-desc  { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; line-height: 1.5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.notif-meta  { display: flex; align-items: center; gap: 7px; margin-top: 5px; flex-wrap: wrap; }
.notif-time  { font-size: 10.5px; color: var(--text-muted); font-family: var(--mono); }
.notif-tag   { font-size: 9.5px; font-weight: 600; padding: 2px 7px; border-radius: 20px; font-family: var(--mono); }
.notif-tag.t-approve { background: #fff3e0; color: #c2410c; }
.notif-tag.t-new     { background: var(--info-soft);    color: var(--info); }
.notif-tag.t-done    { background: var(--success-soft); color: var(--success); }

/* Inline approve buttons */
.notif-actions { display: flex; gap: 6px; margin-top: 8px; }
.nact-btn {
  font-size: 11px; font-weight: 600; font-family: var(--font);
  padding: 4px 12px; border-radius: 6px; border: 1px solid;
  cursor: pointer; transition: all .15s;
}
.nact-btn:active { transform: scale(.96); }
.nact-approve {
  background: var(--success-soft); color: var(--success);
  border-color: rgba(22,163,74,.2);
}
.nact-approve:hover { background: #dcfce7; }
.nact-reject  {
  background: var(--danger-soft); color: var(--danger);
  border-color: rgba(220,38,38,.18);
}
.nact-reject:hover { background: #fee2e2; }

.notif-footer {
  padding: 10px 16px;
  border-top: 1px solid var(--border);
  text-align: center;
  background: var(--bg-subtle);
  border-radius: 0 0 var(--radius) var(--radius);
}
.notif-footer a {
  font-size: 12px; color: var(--accent-dark);
  text-decoration: none; font-weight: 600;
  transition: opacity .15s;
}
.notif-footer a:hover { opacity: .7; }

/* ════════════════════════════════
   USER DROPDOWN
   ════════════════════════════════ */
.user-drop { width: 224px; padding: 6px 0; }

.user-drop-header {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px 12px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 4px;
}
.udrop-avatar {
  width: 38px; height: 38px; border-radius: 11px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dark));
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
  box-shadow: 0 3px 10px rgba(232,146,94,.3);
}
.udrop-name { font-size: 13px; font-weight: 600; color: var(--text-strong); }
.udrop-role { font-size: 10.5px; color: var(--text-muted); font-family: var(--mono); margin-top: 1px; }

.udrop-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 14px; font-size: 13px; color: var(--text-base);
  cursor: pointer; text-decoration: none;
  transition: background .13s, color .13s;
}
.udrop-item:hover { background: var(--hover); color: var(--text-strong); }
.udrop-item i { width: 16px; font-size: 12.5px; color: var(--text-muted); flex-shrink: 0; }
.udrop-item:hover i { color: var(--accent); }

.udrop-sep { height: 1px; background: var(--border); margin: 4px 0; }

.udrop-item.logout { color: var(--danger); }
.udrop-item.logout i { color: var(--danger); }
.udrop-item.logout:hover { background: var(--danger-soft); color: #b91c1c; }
.udrop-item.logout:hover i { color: #b91c1c; }

/* ════════════════════════════════
   DEMO PAGE SHELL
   ════════════════════════════════ */
.page-body {
  padding: 28px 24px;
  max-width: 560px;
}
.demo-card {
  background: #fff; border: 1px solid var(--border);
  border-radius: 12px; padding: 20px 22px;
  box-shadow: var(--shadow-sm);
  font-size: 13.5px; color: var(--text-base); line-height: 1.75;
}
.demo-card h3 { font-size: 14px; font-weight: 600; color: var(--text-strong); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.demo-card ul { list-style: none; display: flex; flex-direction: column; gap: 7px; }
.demo-card li { display: flex; gap: 8px; align-items: flex-start; }
.demo-card li::before { content: '›'; color: var(--accent); flex-shrink: 0; font-size: 15px; line-height: 1.5; }
code { font-family: var(--mono); font-size: 11.5px; background: #fff3e0; color: var(--accent-dark); padding: 1px 5px; border-radius: 4px; }
</style>
</head>
<body>

<!-- ══════════════════════════════
     TOPBAR
     ══════════════════════════════ -->
<header class="topbar" role="banner">

  <!-- Hamburger -->
  <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>

  <!-- Title + breadcrumb -->
  <div class="topbar-title">
    <h1>Dashboard</h1>
    <nav class="topbar-bc" aria-label="Breadcrumb">
      Home / <span>Dashboard</span>
    </nav>
  </div>

  <!-- Right actions -->
  <div class="topbar-actions" role="toolbar" aria-label="Top actions">

    <!-- Search -->
    <div class="tb-search" role="search">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <input type="search" placeholder="Cari menu, karyawan…" aria-label="Cari">
    </div>

    <!-- Notification -->
    <div style="position:relative">
      <button class="tb-btn" id="notifBtn"
              aria-label="Notifikasi — 3 perlu disetujui"
              aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-bell"></i>
        <span class="tb-badge" id="notifBadge">3</span>
      </button>

      <!-- Notification Dropdown -->
      <div class="dropdown notif-drop" id="notifDrop" role="dialog" aria-label="Panel Notifikasi">
        
        <div class="notif-head">
          <div class="notif-head-left">
            <span class="notif-head-title">Notifikasi</span>
            <span class="notif-count-pill" id="notifCountPill">3 baru</span>
          </div>
          <button class="notif-mark-all" onclick="markAllRead()">Tandai dibaca</button>
        </div>

        <div class="notif-tabs" role="tablist">
          <div class="ntab active" role="tab" onclick="switchTab(this,'all')">Semua</div>
          <div class="ntab"        role="tab" onclick="switchTab(this,'approval')">Perlu Disetujui <span id="approvalCount" style="font-size:9px;background:#fee2e2;color:#dc2626;padding:1px 5px;border-radius:10px;margin-left:3px;font-family:var(--mono)">3</span></div>
          <div class="ntab"        role="tab" onclick="switchTab(this,'info')">Info</div>
        </div>

        <div class="notif-list" id="notifList" role="list">

          <!-- Approval 1: Cuti -->
          <div class="notif-item unread" data-cat="approval" role="listitem">
            <div class="notif-icon-wrap warn">
              <i class="fa-solid fa-umbrella-beach"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">Pengajuan Cuti — Andi Wijaya</div>
              <div class="notif-desc">Cuti tahunan 3 hari · 10–12 Feb 2026</div>
              <div class="notif-meta">
                <span class="notif-time">2 jam lalu</span>
                <span class="notif-tag t-approve">Perlu Approve</span>
              </div>
              <div class="notif-actions">
                <button class="nact-btn nact-approve" onclick="approveItem(this,'Cuti Andi')">
                  <i class="fa-solid fa-check" style="margin-right:4px"></i>Setujui
                </button>
                <button class="nact-btn nact-reject" onclick="rejectItem(this)">
                  <i class="fa-solid fa-xmark" style="margin-right:4px"></i>Tolak
                </button>
              </div>
            </div>
          </div>

          <!-- Approval 2: Lembur -->
          <div class="notif-item unread" data-cat="approval" role="listitem">
            <div class="notif-icon-wrap orange">
              <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">Klaim Lembur — Rudi Santoso</div>
              <div class="notif-desc">12 jam · Jan 2026 · Rp 1.440.000</div>
              <div class="notif-meta">
                <span class="notif-time">5 jam lalu</span>
                <span class="notif-tag t-approve">Perlu Approve</span>
              </div>
              <div class="notif-actions">
                <button class="nact-btn nact-approve" onclick="approveItem(this,'Lembur Rudi')">
                  <i class="fa-solid fa-check" style="margin-right:4px"></i>Setujui
                </button>
                <button class="nact-btn nact-reject" onclick="rejectItem(this)">
                  <i class="fa-solid fa-xmark" style="margin-right:4px"></i>Tolak
                </button>
              </div>
            </div>
          </div>

          <!-- Approval 3: Work Order Darurat -->
          <div class="notif-item unread" data-cat="approval" role="listitem">
            <div class="notif-icon-wrap danger">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">Work Order Darurat — Mesin A-03</div>
              <div class="notif-desc">Kerusakan kritis dilaporkan oleh Workshop</div>
              <div class="notif-meta">
                <span class="notif-time">1 jam lalu</span>
                <span class="notif-tag t-approve">Perlu Approve</span>
              </div>
              <div class="notif-actions">
                <button class="nact-btn nact-approve" onclick="approveItem(this,'WO Mesin A-03')">
                  <i class="fa-solid fa-check" style="margin-right:4px"></i>Setujui
                </button>
                <button class="nact-btn nact-reject" onclick="rejectItem(this)">
                  <i class="fa-solid fa-xmark" style="margin-right:4px"></i>Tolak
                </button>
              </div>
            </div>
          </div>

          <!-- Info 1 -->
          <div class="notif-item" data-cat="info" role="listitem">
            <div class="notif-icon-wrap info">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">2 Karyawan Baru Bergabung</div>
              <div class="notif-desc">Siti Rahayu &amp; Budi Prasetyo · Divisi Produksi</div>
              <div class="notif-meta">
                <span class="notif-time">Kemarin</span>
                <span class="notif-tag t-new">Baru</span>
              </div>
            </div>
          </div>

          <!-- Info 2 -->
          <div class="notif-item" data-cat="info" role="listitem">
            <div class="notif-icon-wrap success">
              <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">Slip Gaji Januari Berhasil Digenerate</div>
              <div class="notif-desc">348 slip gaji siap diunduh karyawan</div>
              <div class="notif-meta">
                <span class="notif-time">2 hari lalu</span>
                <span class="notif-tag t-done">Selesai</span>
              </div>
            </div>
          </div>

        </div><!-- /notif-list -->

        <div class="notif-footer">
          <a href="#">Lihat semua notifikasi <i class="fa-solid fa-arrow-right" style="font-size:10px"></i></a>
        </div>
      </div>
    </div><!-- /notif wrapper -->

    <div class="tb-divider"></div>

    <!-- User -->
    <div class="tb-user" id="userBtn" tabindex="0" role="button"
         aria-haspopup="true" aria-expanded="false" aria-label="Menu pengguna">
      <div class="tb-avatar">B</div>
      <div class="tb-user-info">
        <div class="tb-user-name">Bagong</div>
        <div class="tb-user-role">Superadmin</div>
      </div>
      <i class="fa-solid fa-chevron-down tb-chevron"></i>

      <!-- User Dropdown -->
      <div class="dropdown user-drop" id="userDrop" role="menu">
        <div class="user-drop-header">
          <div class="udrop-avatar">B</div>
          <div>
            <div class="udrop-name">Bagong</div>
            <div class="udrop-role">Superadmin · HR</div>
          </div>
        </div>

        <a href="#" class="udrop-item" role="menuitem">
          <i class="fa-regular fa-circle-user"></i>Profil Saya
        </a>
        <a href="#" class="udrop-item" role="menuitem">
          <i class="fa-solid fa-key"></i>Ganti Password
        </a>
        <a href="#" class="udrop-item" role="menuitem">
          <i class="fa-solid fa-sliders"></i>Preferensi
        </a>

        <div class="udrop-sep"></div>

        <a href="#" class="udrop-item" role="menuitem">
          <i class="fa-regular fa-circle-question"></i>Bantuan
        </a>

        <div class="udrop-sep"></div>

        <a href="/logout" class="udrop-item logout" role="menuitem">
          <i class="fa-solid fa-right-from-bracket"></i>Keluar
        </a>
      </div>
    </div>

  </div>
</header>

<!-- Demo page body -->
<!-- <div class="page-body">
  <div class="demo-card">
    <h3><i class="fa-solid fa-circle-info" style="color:var(--accent)"></i> Cara Pakai Topbar</h3>
    <ul>
      <li>Klik <b>🔔 bell</b> → dropdown notifikasi · tab Semua / Perlu Disetujui / Info</li>
      <li>Tiap item approval punya tombol <b>Setujui / Tolak</b> langsung</li>
      <li>Badge count otomatis berkurang setelah di-approve/tolak</li>
      <li>Klik <b>avatar "B"</b> → dropdown user dengan menu <b>Keluar</b></li>
      <li>Klik di luar dropdown → otomatis menutup</li>
      <li>Ganti data notifikasi via AJAX dari controller CodeIgniter kamu</li>
    </ul>
  </div>
</div> -->

<script>
// ── Dropdown logic ──
function openDrop(id, btn) {
  document.getElementById(id).classList.add('open');
  if (btn) { btn.setAttribute('aria-expanded','true'); btn.classList.add('open'); }
}
function closeDrop(id, btn) {
  document.getElementById(id).classList.remove('open');
  if (btn) { btn.setAttribute('aria-expanded','false'); btn.classList.remove('open'); }
}
function closeAll() {
  closeDrop('notifDrop', document.getElementById('notifBtn'));
  closeDrop('userDrop',  document.getElementById('userBtn'));
}

document.getElementById('notifBtn').addEventListener('click', e => {
  e.stopPropagation();
  const open = document.getElementById('notifDrop').classList.contains('open');
  closeAll();
  if (!open) openDrop('notifDrop', document.getElementById('notifBtn'));
});

document.getElementById('userBtn').addEventListener('click', e => {
  e.stopPropagation();
  const open = document.getElementById('userDrop').classList.contains('open');
  closeAll();
  if (!open) openDrop('userDrop', document.getElementById('userBtn'));
});
document.getElementById('userBtn').addEventListener('keydown', e => {
  if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); document.getElementById('userBtn').click(); }
});

document.addEventListener('click', closeAll);
document.querySelectorAll('.dropdown').forEach(d => d.addEventListener('click', e => e.stopPropagation()));

// ── Tabs ──
function switchTab(el, cat) {
  document.querySelectorAll('.ntab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('#notifList .notif-item').forEach(item => {
    item.style.display = (cat === 'all' || item.dataset.cat === cat) ? 'flex' : 'none';
  });
}

// ── Mark all read ──
function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(i => i.classList.remove('unread'));
  updateBadge(0);
}

// ── Approve / Reject ──
let pending = 3;

function approveItem(btn) {
  const item = btn.closest('.notif-item');
  item.querySelector('.notif-actions').innerHTML =
    `<span style="font-size:11.5px;color:var(--success);font-weight:600"><i class="fa-solid fa-circle-check" style="margin-right:5px"></i>Disetujui</span>`;
  item.classList.remove('unread');
  item.style.opacity = '.55';
  pending = Math.max(0, pending - 1);
  updateBadge(pending);
}

function rejectItem(btn) {
  const item = btn.closest('.notif-item');
  item.querySelector('.notif-actions').innerHTML =
    `<span style="font-size:11.5px;color:var(--danger);font-weight:600"><i class="fa-solid fa-circle-xmark" style="margin-right:5px"></i>Ditolak</span>`;
  item.classList.remove('unread');
  item.style.opacity = '.45';
  pending = Math.max(0, pending - 1);
  updateBadge(pending);
}

function updateBadge(n) {
  const badge = document.getElementById('notifBadge');
  const pill  = document.getElementById('notifCountPill');
  const acBadge = document.getElementById('approvalCount');
  if (badge) { badge.textContent = n; badge.style.display = n === 0 ? 'none' : 'flex'; }
  if (pill)  { pill.textContent = n + ' baru'; }
  if (acBadge) { acBadge.textContent = n; acBadge.style.display = n === 0 ? 'none' : ''; }
  document.getElementById('notifBtn').setAttribute('aria-label', `Notifikasi — ${n} perlu disetujui`);
}
</script>
