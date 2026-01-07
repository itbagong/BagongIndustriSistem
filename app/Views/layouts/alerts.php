<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success">
    <span class="alert-icon">✓</span>
    <span class="alert-message"><?= esc(session()->getFlashdata('success')) ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-error">
    <span class="alert-icon">⚠</span>
    <span class="alert-message"><?= esc(session()->getFlashdata('error')) ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
<div class="alert alert-info">
    <span class="alert-icon">ℹ</span>
    <span class="alert-message"><?= esc(session()->getFlashdata('info')) ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
<div class="alert alert-warning">
    <span class="alert-icon">⚠</span>
    <span class="alert-message"><?= esc(session()->getFlashdata('warning')) ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>