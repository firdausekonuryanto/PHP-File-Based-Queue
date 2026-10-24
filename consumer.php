<?php

$queueDir = __DIR__ . '/queue';

if (!is_dir($queueDir)) {
    mkdir($queueDir, 0777, true);
}

echo " [*] Worker sedang berjalan dan menunggu antrean... Tekan CTRL+C untuk berhenti.\n\n";

while (true) {
    // Ambil semua file berformat .json di dalam folder queue
    $files = glob($queueDir . '/*.json');

    if (empty($files)) {
        // Jika antrean kosong, tunggu 1 detik sebelum cek lagi (agar CPU tidak 100%)
        sleep(1);
        continue;
    }

    // Urutkan file berdasarkan nama (yang paling lama/awal dibuat akan diproses duluan - FIFO)
    sort($files);
    $fileToProcess = $files[0];

    // Baca isi pesan dari file
    $jsonContent = file_get_contents($fileToProcess);
    $data = json_decode($jsonContent, true);

    echo " [x] Sedang memproses: " . $data['task'] . " (ID: " . $data['id'] . ")\n";

    // Simulasi proses berat (misal: proses butuh waktu 2 detik)
    sleep(2);

    // Hapus file dari folder queue setelah selesai diproses agar tidak diproses ulang
    unlink($fileToProcess);

    echo " [-->] Selesai diproses!\n\n";
}