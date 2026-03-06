<style>
    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; }
    .detail-section { padding:20px 24px; border-bottom:1px solid #f3f4f6; }
    .detail-section:last-child { border-bottom:none; }
    .detail-section.full { grid-column:1/-1; }
    .section-title {
        font-size:.7rem; font-weight:700; text-transform:uppercase;
        letter-spacing:.08em; color:#9ca3af; margin:0 0 14px;
        display:flex; align-items:center; gap:7px;
    }
    .section-title::after {
        content:''; flex:1; height:1px; background:#f3f4f6;
    }
    .detail-row { display:flex; flex-direction:column; gap:3px; margin-bottom:12px; }
    .detail-row:last-child { margin-bottom:0; }
    .detail-label { font-size:.72rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; }
    .detail-value { font-size:.9rem; color:#111827; font-weight:500; }
    .detail-value.muted { color:#6b7280; font-weight:400; }
    .detail-value.mono  { font-family:monospace; background:#f3f4f6; padding:2px 8px; border-radius:5px; font-size:.82rem; display:inline-block; }

    /* Avatar banner */
    .detail-banner {
        background:linear-gradient(180deg, #e89a6b 0%, #d88759 100%);
        padding:24px; display:flex; align-items:center; gap:18px;
        border-radius:14px 14px 0 0;
    }
    .detail-avatar {
        width:56px; height:56px; border-radius:50%;
        background:rgba(255,255,255,.2); display:flex; align-items:center;
        justify-content:center; font-size:1.6rem; flex-shrink:0;
        border:2px solid rgba(255,255,255,.4);
    }
    .detail-banner-name  { font-size:1.1rem; font-weight:700; color:#fff; margin:0 0 4px; }
    .detail-banner-sub   { font-size:.8rem; color:rgba(255,255,255,.7); margin:0; }
    .detail-banner-badges { display:flex; gap:6px; margin-top:8px; flex-wrap:wrap; }
    .dbadge {
        display:inline-flex; align-items:center; gap:4px;
        padding:3px 10px; border-radius:999px; font-size:.72rem; font-weight:600;
        background:rgba(255,255,255,.2); color:#fff;
    }

    /* Two-col inside section */
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 24px; }

    @media(max-width:540px) {
        .detail-grid  { grid-template-columns:1fr; }
        .info-grid    { grid-template-columns:1fr; }
        .detail-section.full { grid-column:1; }
    }
</style>

<?php
$e = $employee;

// Helper: show value or dash
$val = fn($v) => (isset($v) && $v !== '' && $v !== null) ? esc($v) : '<span class="muted">—</span>';

// Status badge colour
$empStatusClass = 'dbadge';
?>

<!-- ── Banner ───────────────────────────────────────────────── -->
<div class="detail-banner">
    <div class="detail-avatar">👤</div>
    <div>
        <p class="detail-banner-name"><?= esc($e['name']) ?></p>
        <p class="detail-banner-sub">
            NIK: <strong><?= esc($e['nik']) ?></strong>
            <?php if (!empty($e['bis_id'])): ?>
                &nbsp;·&nbsp; BIS ID: <strong><?= esc($e['bis_id']) ?></strong>
            <?php endif; ?>
        </p>
        <div class="detail-banner-badges">
            <?php if (!empty($e['employee_status'])): ?>
                <span class="dbadge">✅ <?= esc($e['employee_status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($e['employment_status'])): ?>
                <span class="dbadge">📋 <?= esc($e['employment_status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($e['site'])): ?>
                <span class="dbadge">🗺️ <?= esc($e['site']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Grid sections ────────────────────────────────────────── -->
<div class="detail-grid">

    <!-- Personal Info -->
    <div class="detail-section">
        <p class="section-title">👤 Personal</p>
        <div class="info-grid">
            <div class="detail-row">
                <span class="detail-label">Gender</span>
                <span class="detail-value"><?= $val($e['gender']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Agama</span>
                <span class="detail-value"><?= $val($e['religion']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tempat Lahir</span>
                <span class="detail-value"><?= $val($e['place_of_birth']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tanggal Lahir</span>
                <span class="detail-value"><?= $val($e['date_of_birth']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Umur</span>
                <span class="detail-value"><?= isset($e['age']) ? esc($e['age']) . ' tahun' : '—' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Pendidikan</span>
                <span class="detail-value"><?= $val($e['last_education']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">No. KTP</span>
                <span class="detail-value mono"><?= $val($e['national_id']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">No. HP</span>
                <span class="detail-value"><?= $val($e['phone_number']) ?></span>
            </div>
        </div>
    </div>

    <!-- Work Info -->
    <div class="detail-section">
        <p class="section-title">💼 Pekerjaan</p>
        <div class="info-grid">
            <div class="detail-row">
                <span class="detail-label">Department</span>
                <span class="detail-value"><?= $val($e['department']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Division</span>
                <span class="detail-value"><?= $val($e['division']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Job Position</span>
                <span class="detail-value"><?= $val($e['job_position']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">User</span>
                <span class="detail-value mono"><?= $val($e['work_user']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Site</span>
                <span class="detail-value"><?= $val($e['site']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PKWT Date</span>
                <span class="detail-value"><?= $val($e['pkwt_date']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cutoff Date</span>
                <span class="detail-value"><?= $val($e['cutoff_date']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tenure</span>
                <span class="detail-value"><?= $val($e['tenure']) ?></span>
            </div>
        </div>
    </div>

    <!-- Address — full width -->
    <div class="detail-section full">
        <p class="section-title">📍 Alamat</p>
        <span class="detail-value <?= empty($e['address']) ? 'muted' : '' ?>">
            <?= !empty($e['address']) ? esc($e['address']) : '—' ?>
        </span>
    </div>

    <!-- Meta — full width -->
    <div class="detail-section full" style="background:#fafafa; border-radius:0 0 14px 14px;">
        <p class="section-title">🕒 Meta</p>
        <div class="info-grid">
            <div class="detail-row">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value muted"><?= $val($e['created_at']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ID</span>
                <span class="detail-value mono"><?= $val($e['id']) ?></span>
            </div>
        </div>
    </div>

</div>