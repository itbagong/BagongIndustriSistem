<!-- Add/Edit Employee Modal -->
<option value="S2">S2</option>
<option value="S3">S3</option>
</select>
</div>


<div class="form-group full">
<label>Address</label>
<textarea class="form-control" name="address" id="address" rows="2"></textarea>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button class="btn btn-secondary" onclick="closeModal('employeeModal')">Batal</button>
<button class="btn btn-primary" onclick="saveEmployee()">
<span class="btn-icon">💾</span>
Simpan
</button>
</div>
</div>
</div>




<!-- View Detail Modal -->
<div class="modal" id="viewModal">
<div class="modal-content modal-large">
<div class="modal-header">
<h2>Detail Karyawan</h2>
<button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
</div>
<div class="modal-body">
<div id="employeeDetail">
<!-- Detail will be inserted here -->
</div>
</div>
<div class="modal-footer">
<button class="btn btn-secondary" onclick="closeModal('viewModal')">Tutup</button>
</div>
</div>
</div>




<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
<div class="modal-content modal-small">
<div class="modal-header">
<h2>⚠️ Konfirmasi Hapus</h2>
<button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
</div>
<div class="modal-body">
<p>Apakah Anda yakin ingin menghapus data karyawan ini?</p>
<p><strong id="deleteEmployeeName"></strong></p>
<p class="text-warning">Data yang dihapus tidak dapat dikembalikan!</p>
</div>
<div class="modal-footer">
<button class="btn btn-secondary" onclick="closeModal('deleteModal')">Batal</button>
<button class="btn btn-danger" onclick="confirmDelete()">
<span class="btn-icon">🗑️</span>
Hapus
</button>
</div>
</div>
</div>