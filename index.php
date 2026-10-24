<?php
// Gunakan forward slash agar aman di Windows
$queueDir = str_replace('\\', '/', __DIR__) . '/queue';

// Pastikan folder queue ada
if (!is_dir($queueDir)) {
    mkdir($queueDir, 0777, true);
}

$message = '';

// 1. Aksi ketika form disubmit (Producer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_name'])) {
    $taskName = trim($_POST['task_name']);
    if (!empty($taskName)) {
        $jobData = [
            'id' => rand(1000, 9999),
            'task' => $taskName,
            'time' => date('Y-m-d H:i:s')
        ];
        
        $filename = $queueDir . '/job_' . time() . '_' . mt_rand(100, 999) . '.json';
        
        if (file_put_contents($filename, json_encode($jobData)) !== false) {
            // Redirect agar form tidak ke-submit ulang saat halaman di-refresh
            header("Location: index.php?status=success");
            exit;
        } else {
            $message = "Gagal menyimpan file antrean! Periksa izin folder.";
        }
    }
}

// Tangkap pesan sukses dari redirect
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $message = "Antrean berhasil ditambahkan!";
}

// 2. Aksi ketika tombol "Proses" diklik (Consumer 1 job)
if (isset($_GET['action']) && $_GET['action'] === 'process') {
    $files = glob($queueDir . '/*.json');
    if (!empty($files)) {
        sort($files);
        $fileToProcess = $files[0];
        
        $jsonContent = file_get_contents($fileToProcess);
        $data = json_decode($jsonContent, true);
        
        // Hapus file (simulasi tugas selesai diproses)
        unlink($fileToProcess);
        
        header("Location: index.php?processed=" . urlencode($data['task']));
        exit;
    }
}

if (isset($_GET['processed'])) {
    $message = "Berhasil memproses tugas: " . htmlspecialchars($_GET['processed']);
}

// Ambil daftar file antrean yang masih pending
$files = glob($queueDir . '/*.json');
$pendingJobs = [];
if ($files !== false) {
    sort($files);
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content) {
            $pendingJobs[] = json_decode($content, true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Simple PHP File Queue Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-white rounded-xl shadow-lg overflow-hidden p-6">
        <h1 class="text-xl font-bold text-slate-800 mb-2">PHP File-Based Queue Dashboard</h1>
        <p class="text-sm text-slate-500 mb-6">Simulasi sederhana Message Queue menggunakan penyimpanan file lokal.</p>

        <!-- Notifikasi Pesan -->
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Antrean (Producer) -->
        <form method="POST" class="mb-6 flex gap-2">
            <input type="text" name="task_name" placeholder="Nama tugas (Contoh: Kirim Email Invoice)..." required autocomplete="off"
                class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-blue-700 transition">
                Tambah Antrean
            </button>
        </form>

        <div class="flex justify-between items-center mb-3">
            <h2 class="text-md font-semibold text-slate-700">Daftar Antrean Pending (<?= count($pendingJobs) ?>)</h2>
            
            <?php if (count($pendingJobs) > 0): ?>
                <a href="index.php?action=process" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                    Proses 1 Antrean
                </a>
            <?php endif; ?>
        </div>

        <!-- Daftar List Antrean -->
        <div class="space-y-2 max-h-60 overflow-y-auto">
            <?php if (empty($pendingJobs)): ?>
                <div class="text-slate-400 text-sm text-center py-8 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    Antrean kosong. Belum ada tugas yang menunggu.
                </div>
            <?php else: ?>
                <?php foreach ($pendingJobs as $job): ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center text-sm">
                        <div>
                            <span class="font-medium text-slate-800"><?= htmlspecialchars($job['task']) ?></span>
                            <span class="block text-xs text-slate-400 mt-0.5">ID: <?= $job['id'] ?> | Waktu: <?= $job['time'] ?></span>
                        </div>
                        <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-medium">Pending</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>