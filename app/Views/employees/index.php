<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="p-6 max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Employee Data Import
        </h1>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">
            <i class="fas fa-upload mr-2"></i>Upload Employee Excel / CSV
        </h2>

        <form id="importForm" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <i class="fas fa-file-excel text-6xl text-gray-400 mb-4"></i>

                <div class="mb-4">
                    <label class="cursor-pointer bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 inline-block">
                        <i class="fas fa-folder-open mr-2"></i>Pilih File Excel
                        <input type="file"
                               name="file_upload"
                               accept=".xlsx,.xls,.csv"
                               class="hidden"
                               required
                               onchange="showFileName(this)">
                    </label>
                </div>

                <p class="text-sm text-gray-500" id="file-name">
                    Belum ada file dipilih
                </p>

                <p class="text-xs text-gray-400 mt-2">
                    Format Header Example: NIK, Department, Division, Job Position, Gender, Site, dll.
                </p>

                <button type="submit"
                        id="importButton"
                        class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Import Data
                </button>
            </div>
        </form>
    </div>

    <!-- Live Log Card -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">
            <i class="fas fa-terminal mr-2"></i>Live Import Log
        </h2>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <div id="progressBar"
                 class="bg-green-500 h-4 rounded-full transition-all duration-300"
                 style="width: 0%">
            </div>
        </div>

        <!-- Counter -->
        <div class="flex justify-between text-sm mb-3">
            <span>Processed: <strong id="processedCount">0</strong></span>
            <span>Total: <strong id="totalCount">0</strong></span>
        </div>

        <!-- Console Log -->
        <div id="logContainer"
             class="bg-black text-green-400 p-4 rounded-lg
                    h-80 overflow-y-auto text-sm font-mono">
        </div>
    </div>

</div>

<script>
let uploadedFile = null;
let totalRows = 0;
let isImporting = false;

document.getElementById("importForm").addEventListener("submit", async function(e){
    e.preventDefault();

    if (isImporting) return;

    const formData = new FormData(this);

    resetUI();
    disableButton(true);
    log("Uploading file...");

    const uploadRes = await fetch("<?= base_url('employees/upload') ?>", {
        method: "POST",
        body: formData
    });

    const uploadData = await uploadRes.json();

    if(uploadData.status !== 'success'){
        log("❌ " + uploadData.message);
        disableButton(false);
        return;
    }

    uploadedFile = uploadData.file;
    totalRows = uploadData.totalRows;

    document.getElementById("totalCount").textContent = totalRows;

    log("File uploaded successfully.");
    processChunk(0);
});

async function processChunk(offset)
{
    isImporting = true;

    const formData = new FormData();
    formData.append("file", uploadedFile);
    formData.append("offset", offset);

    const res = await fetch("<?= base_url('employees/process') ?>", {
        method: "POST",
        body: formData
    });

    const data = await res.json();

    data.logs.forEach(msg => log(msg));

    updateProgress(data.nextOffset);

    if(!data.done){
        processChunk(data.nextOffset);
    } else {
        log("🎉 Import Completed!");
        disableButton(false);
        isImporting = false;
    }
}

function log(message)
{
    const container = document.getElementById("logContainer");
    container.innerHTML += message + "<br>";
    container.scrollTop = container.scrollHeight;
}

function updateProgress(processed)
{
    document.getElementById("processedCount").textContent = processed;

    if(totalRows === 0) return;

    const percent = Math.min((processed / totalRows) * 100, 100);
    document.getElementById("progressBar").style.width = percent + "%";
}

function resetUI()
{
    document.getElementById("logContainer").innerHTML = "";
    document.getElementById("progressBar").style.width = "0%";
    document.getElementById("processedCount").textContent = "0";
    document.getElementById("totalCount").textContent = "0";
}

function disableButton(state)
{
    const btn = document.getElementById("importButton");

    if(state){
        btn.disabled = true;
        btn.classList.add("opacity-50", "cursor-not-allowed");
    } else {
        btn.disabled = false;
        btn.classList.remove("opacity-50", "cursor-not-allowed");
    }
}

function showFileName(input) {
    document.getElementById('file-name').textContent =
        input.files[0]?.name || 'Belum ada file dipilih';
}
</script>

<?= $this->endSection() ?>