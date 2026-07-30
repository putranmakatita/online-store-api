<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:hidden-item-game-command')]
#[Description('Command description')]
class HiddenItemGameCommand extends Command
{
    protected $signature = 'game:hidden-item';
    protected $description = 'Menjalankan permainan Hidden Item untuk mencari kemungkinan lokasi item pada grid';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Inisialisasi layout grid berdasarkan gambar
        $gridRaw = [
            "########",
            "#......#",
            "#.###..#",
            "#...#.##",
            "#X#....#",
            "########",
        ];

        // Mengubah array string menjadi array 2 dimensi
        $grid = array_map('str_split', $gridRaw);
        $rows = count($grid);
        $cols = count($grid[0]);

        // Mencari posisi awal pemain ('X')
        $startX = -1;
        $startY = -1;

        for ($y = 0; $y < $rows; $y++) {
            for ($x = 0; $x < $cols; $x++) {
                if ($grid[$y][$x] === 'X') {
                    $startX = $x;
                    $startY = $y;
                    break 2;
                }
            }
        }

        if ($startX === -1 || $startY === -1) {
            $this->error("Error: Posisi awal 'X' tidak ditemukan dalam grid.");
            return 1;
        }

        $probableLocations = [];

        // Algoritma Navigasi: Up (A) -> Right (B) -> Down (C)
        for ($a = 1; $startY - $a >= 0; $a++) {
            $currentY = $startY - $a;
            $currentX = $startX;

            if ($grid[$currentY][$currentX] === '#') {
                break;
            }

            $upX = $currentX;
            $upY = $currentY;

            for ($b = 1; $upX + $b < $cols; $b++) {
                $rightX = $upX + $b;
                $rightY = $upY;

                if ($grid[$rightY][$rightX] === '#') {
                    break;
                }

                for ($c = 1; $rightY + $c < $rows; $c++) {
                    $downX = $rightX;
                    $downY = $rightY + $c;

                    if ($grid[$downY][$downX] === '#') {
                        break;
                    }

                    $key = "{$downX},{$downY}";
                    $probableLocations[$key] = [
                        'x' => $downX,
                        'y' => $downY,
                        'steps' => "Atas: {$a}, Kanan: {$b}, Bawah: {$c}"
                    ];
                }
            }
        }

        if (empty($probableLocations)) {
            $this->warn('Tidak ada titik lokasi yang memenuhi syarat langkah.');
            return 0;
        }

        // Menandai lokasi '$' pada grid untuk tampilan
        $displayGrid = $grid;
        foreach ($probableLocations as $loc) {
            $x = $loc['x'];
            $y = $loc['y'];
            if ($displayGrid[$y][$x] === '.') {
                $displayGrid[$y][$x] = '$';
            }
        }

        // Tampilan Informasi Awal
        $this->info('       HIDDEN ITEM GAME - LARAVEL        ');
        $this->newLine();

        $this->line("Posisi Awal Pemain (X) : <fg=cyan>(x = {$startX}, y = {$startY})</>");
        $this->newLine();

        $this->comment('Kemungkinan Koordinat Lokasi Item (x, y):');
        foreach ($probableLocations as $loc) {
            $this->line("- <fg=green>({$loc['x']}, {$loc['y']})</> ---> [<fg=gray>Langkah: {$loc['steps']}</>]");
        }

        $this->newLine();
        $this->info('Tampilan Grid dengan Lokasi Kemungkinan ($):');

        foreach ($displayGrid as $row) {
            $formattedRow = '';
            foreach ($row as $char) {
                if ($char === '$') {
                    $formattedRow .= '<fg=yellow;options=bold>$</> ';
                } elseif ($char === 'X') {
                    $formattedRow .= '<fg=cyan;options=bold>X</> ';
                } elseif ($char === '#') {
                    $formattedRow .= '<fg=gray>#</> ';
                } else {
                    $formattedRow .= '. ';
                }
            }
            $this->line($formattedRow);
        }

        $this->newLine();
        $this->line('Keterangan:');
        $this->line('<fg=cyan>X</> = Posisi Awal Pemain');
        $this->line('<fg=gray>#</> = Rintangan (Obstacle)');
        $this->line('. = Jalan Kosong (Clear Path)');
        $this->line('<fg=yellow>$</> = Kemungkinan Lokasi Item');

        // FITUR INTERAKTIF: Memilih 1 lokasi acak & meminta user menebak
        $probableKeys = array_keys($probableLocations);
        $hiddenKey = $probableKeys[array_rand($probableKeys)];
        $hiddenItem = $probableLocations[$hiddenKey];

        $this->newLine();
        $this->alert("GAME DIMULAI: Item telah disembunyikan secara acak di salah satu lokasi '$'!");

        $attempts = 0;

        while (true) {
            $inputX = $this->ask('Masukkan koordinat X tebakan Anda (atau ketik "exit" untuk keluar)');

            if (strtolower($inputX) === 'exit') {
                $this->warn("Game dihentikan. Item sebenarnya berada di koordinat ({$hiddenItem['x']}, {$hiddenItem['y']}).");
                break;
            }

            $inputY = $this->ask('Masukkan koordinat Y tebakan Anda');

            if (!is_numeric($inputX) || !is_numeric($inputY)) {
                $this->error('Koordinat harus berupa angka!');
                continue;
            }

            $guessX = (int) $inputX;
            $guessY = (int) $inputY;
            $attempts++;

            // Cek apakah tebakan benar
            if ($guessX === $hiddenItem['x'] && $guessY === $hiddenItem['y']) {
                $this->newLine();
                $this->info("SELAMAT! Anda menemukan item tersembunyi di koordinat ({$guessX}, {$guessY}) dalam {$attempts} kali percobaan!");
                break;
            } else {
                $this->error("Meleset! Tidak ada item di koordinat ({$guessX}, {$guessY}). Coba lagi!");
                $this->newLine();
            }
        }

        return 0;
    }
}
