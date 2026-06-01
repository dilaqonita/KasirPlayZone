<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Package;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FunParkSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. PACKAGES ───────────────────────────────────────────
        $packages = [
            [
                'name'             => 'Tiny Tots',
                'emoji'            => '🐣',
                'description'      => 'Paket seru untuk si kecil usia 1–3 tahun',
                'age_label'        => '1–3 Thn',
                'duration_minutes' => 60,
                'price'            => 50000,
                'features'         => ['Mandi Bola', 'Perosotan Mini'],
                'is_active'        => true,
            ],
            [
                'name'             => 'Kids Explorer',
                'emoji'            => '🚀',
                'description'      => 'Eksplorasi seru untuk anak usia 4–8 tahun',
                'age_label'        => '4–8 Thn',
                'duration_minutes' => 90,
                'price'            => 75000,
                'features'         => ['Trampolin', 'Panjat Dinding', 'Mandi Bola'],
                'is_active'        => true,
            ],
            [
                'name'             => 'Junior Champ',
                'emoji'            => '⚡',
                'description'      => 'Tantangan hebat untuk anak usia 9–12 tahun',
                'age_label'        => '9–12 Thn',
                'duration_minutes' => 120,
                'price'            => 100000,
                'features'         => ['Ninja Course', 'Trampolin'],
                'is_active'        => true,
            ],
            [
                'name'             => 'Family Blast',
                'emoji'            => '👨‍👩‍👧',
                'description'      => 'Paket keluarga lengkap dengan semua zona',
                'age_label'        => 'Semua Usia',
                'duration_minutes' => 180,
                'price'            => 200000,
                'features'         => ['Semua Zona', 'Snack Gratis'],
                'is_active'        => true,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::firstOrCreate(['name' => $pkg['name']], $pkg);
        }

        $pkgIds = Package::pluck('id')->toArray();

        // ─── 2. TRANSACTIONS ──────────────────────────────────────
        $names = [
            'Arisa Putri','Budi Santoso','Citra Dewi','Dimas Pratama',
            'Eka Wulandari','Fajar Nugroho','Gita Rahayu','Hendra Wijaya',
            'Indah Permata','Joko Susilo','Kevin Pratama','Lina Sari',
            'Maya Kusuma','Nanda Putra','Olivia Tan','Pandu Aditya',
            'Rini Agustina','Sari Wati','Teguh Prasetyo','Umi Kalsum',
        ];
        $payMethods = ['cash', 'bank', 'ewallet', 'debit'];
        $statuses   = ['lunas', 'lunas', 'lunas', 'pending', 'checkin', 'selesai'];
        $times      = ['09:00','10:00','13:00','14:00','16:00','17:00'];

        $today = Carbon::today();

        foreach ($names as $i => $name) {
            $pkg    = Package::find($pkgIds[$i % count($pkgIds)]);
            $pax    = rand(1, 4);
            $status = $statuses[array_rand($statuses)];
            $date   = $today->copy()->subDays(rand(0, 3));

            $tx = Transaction::create([
                'ticket_code'   => Transaction::generateTicketCode(),
                'package_id'    => $pkg->id,
                'customer_name' => $name,
                'phone'         => '08' . rand(100000000, 999999999),
                'visitor_count' => $pax,
                'visit_date'    => $date,
                'visit_time'    => $times[array_rand($times)],
                'payment_method'=> $payMethods[array_rand($payMethods)],
                'total_amount'  => $pkg->price * $pax,
                'status'        => $status,
                'check_in_at'   => in_array($status, ['checkin','selesai'])
                    ? $date->copy()->setTime(rand(9, 15), rand(0, 59))
                    : null,
                'check_out_at'  => $status === 'selesai'
                    ? $date->copy()->setTime(rand(16, 18), rand(0, 59))
                    : null,
            ]);

            // Buat log aktivitas
            if ($tx->check_in_at) {
                ActivityLog::create([
                    'transaction_id' => $tx->id,
                    'customer_name'  => $name,
                    'type'           => 'in',
                    'created_at'     => $tx->check_in_at,
                    'updated_at'     => $tx->check_in_at,
                ]);
            }
            if ($tx->check_out_at) {
                ActivityLog::create([
                    'transaction_id' => $tx->id,
                    'customer_name'  => $name,
                    'type'           => 'out',
                    'created_at'     => $tx->check_out_at,
                    'updated_at'     => $tx->check_out_at,
                ]);
            }
        }

        $this->command->info('✅ FunPark seeder selesai: ' . count($names) . ' transaksi dibuat.');
    }
}
