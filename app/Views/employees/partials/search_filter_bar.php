<div class="search-filter-bar">
    <div class="form-group">
        <label>🔍 Cari Karyawan</label>
        <input type="text" class="form-control" id="searchInput" placeholder="Cari NIK, Nama, Position...">
    </div>
    <div class="form-group">
        <label>Department</label>
        <select class="form-control" id="departmentFilter">
        <option value="">Semua Department</option>
        <option value="Production">Production</option>
        <option value="HR">HR</option>
        <option value="Finance">Finance</option>
        <option value="IT">IT</option>
        <option value="Warehouse">Warehouse</option>
        </select>
    </div>
    <div class="form-group">
        <label>Employment Status</label>
        <select class="form-control" id="employmentFilter">
        <option value="">Semua Status</option>
        <option value="Permanent">Permanent</option>
        <option value="Contract">Contract</option>
        <option value="Probation">Probation</option>
        </select>
    </div>
    <div class="form-group">
        <label>Employee Status</label>
        <select class="form-control" id="statusFilter">
            <option value="">Semua</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Resigned">Resigned</option>
        </select>
    </div>
    <div class="form-group">
        <label>&nbsp;</label>
        <button class="btn btn-info" onclick="applyFilters()">
        <span class="btn-icon">🔍</span>
            Filter
        </button>
    </div>
</div>