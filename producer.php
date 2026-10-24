<?php

$queueDir = __DIR__ . '/queue';

// Pastikan folder queue ada
if (!is_dir($queueDir)) {
    mkdir($queueDir, 0777, true);
}

// Data simulasi pekerjaan (misal: kirim email / cetak laporan)
$jobData = [
    'id' => rand(1000, 9999),
    'task' => 'Kirim Email ke Pelanggan #' . rand(10, 99),
    'time' => date('Y-m-d H:i:s')
];

// Buat nama file unik berdasarkan waktu
$filename = $queueDir . '/job_' . microtime(true) . '.json';

// Simpan data ke dalam file JSON di folder queue
file_put_contents($filename, json_encode($jobData));

echo " [x] Sukses memasukkan antrean baru: " . $jobData['task'] . "\n";