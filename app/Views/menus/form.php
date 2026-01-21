<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0"><?= $title ?></h4>
            </div>
            <div class="card-body">
                <?php $isEdit = !empty($menu); ?>
                <form action="<?= $isEdit ? base_url('menus/update/'.$menu['id']) : base_url('menus/create') ?>" method="post">
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Menu</label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?= $isEdit ? esc($menu['name']) : '' ?>" placeholder="Contoh: Karyawan">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Route (URL)</label>
                            <input type="text" name="route" class="form-control" required 
                                   value="<?= $isEdit ? esc($menu['route']) : '' ?>" placeholder="Contoh: /employees">
                            <small class="text-muted">Gunakan <code>#</code> jika ini menu induk (dropdown).</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Icon Class (FontAwesome)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                </div>
                                <input type="text" name="icon" class="form-control" 
                                       value="<?= $isEdit ? esc($menu['icon']) : 'fas fa-circle' ?>" placeholder="fas fa-user">
                            </div>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Permission Key</label>
                            <select name="permission" class="form-control select2">
                                <option value="">-- Public (Semua User) --</option>
                                <?php foreach($permissions as $perm): ?>
                                    <option value="<?= $perm['name'] ?>" 
                                        <?= ($isEdit && $menu['permission'] == $perm['name']) ? 'selected' : '' ?>>
                                        <?= $perm['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Kosongkan jika menu ini boleh dilihat semua user login.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Induk Menu (Parent)</label>
                            <select name="parent_id" class="form-control">
                                <option value="">-- Menu Utama (Root) --</option>
                                <?php foreach($parents as $p): ?>
                                    <?php if($isEdit && $p['id'] == $menu['id']) continue; ?>
                                    
                                    <option value="<?= $p['id'] ?>" 
                                        <?= ($isEdit && $menu['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                        <?= $p['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Urutan</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="<?= $isEdit ? esc($menu['sort_order']) : '1' ?>">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1" <?= ($isEdit && $menu['is_active'] == 1) ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= ($isEdit && $menu['is_active'] == 0) ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-4 text-right">
                        <a href="<?= base_url('menus') ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Menu
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>