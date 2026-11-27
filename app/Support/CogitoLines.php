<?php

namespace App\Support;

use App\Models\ElectionSetting;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Kalimat balon COGITO per halaman dan keadaan: sapaan, info akun, arahan, dan
 * kalimat lucu. Kalimat pertama selalu yang paling sesuai keadaan; sisanya diacak
 * bergiliran oleh home.js.
 *
 * Hanya fakta nyata: tanggal dari jadwal, akun dikirim ke email kampus, kendala ke
 * WhatsApp panitia di footer. Tidak pernah menyebut paslon yang dipilih seseorang.
 *
 * Mengembalikan ['key' => string, 'lines' => list<array{text: string, action: ?array{label: string, href: string}}>].
 * 'key' membedakan keadaan, supaya "Tutup" tidak ikut menyembunyikan arahan baru
 * (setelah masuk atau memilih).
 */
class CogitoLines
{
    private const HELLO = 'Halo! Aku COGITO, maskot HMIF.';

    private const SECRET = 'Psst, pilihanmu rahasia. Aku pun tidak boleh tahu.';
    private const ONE_VOTE = 'Satu akun, satu suara. Tidak bisa titip, ya!';
    private const HAT = 'Topi ini bukan cuma gaya. Oke, sebagian besar gaya.';
    private const WAVE = 'Aku sudah latihan melambai dari pagi. Tanganku pegal, tapi demi kamu.';
    private const WATER = 'Jangan lupa minum air putih. Memilih juga butuh fokus.';
    private const HELP = 'Kendala login atau belum terima email akun? Chat panitia lewat WhatsApp di bagian paling bawah.';
    private const MAIL = 'Akun memilih dikirim ke email kampusmu (@mail.unej.ac.id). Cek juga folder spam, ya.';
    private const FINAL = 'Suara hanya bisa dikirim satu kali dan tidak bisa diubah.';
    private const ENVELOPES = 'Bingung pilih siapa? Buka amplop paslon untuk membaca visi dan misinya.';

    public static function home(ElectionSetting $setting, ?User $user): array
    {
        $status = $setting->status();
        $who = match (true) {
            ! $user => 'guest',
            $user->hasRole('Admin') => 'admin',
            $user->status === 'voted' => 'voted',
            default => 'voter',
        };

        return [
            'key' => "home:{$status}:{$who}",
            'lines' => self::homeLines($who, $setting, $status, $user),
        ];
    }

    /**
     * @param  'prepare'|'ballot'|'closed'|'receipt'  $page
     */
    public static function vote(string $page, ElectionSetting $setting, User $user): array
    {
        $results = ['label' => 'Lihat hasil', 'href' => url('/') . '#hasil'];

        $lines = match ($page) {
            'prepare' => array_values(array_filter([
                self::line($setting->vote_start && $setting->vote_start->isFuture()
                    ? 'Surat suara muncul di sini saat pemilihan dibuka ' . self::when($setting->vote_start) . '.'
                    : 'Jadwal pemilihan belum diumumkan panitia.'),
                self::line(self::hello($user) . ' Kamu sudah masuk, tinggal menunggu.'),
                self::line('Sambil menunggu, buka amplop paslon di bawah.'),
                self::line(self::FINAL),
                self::line(self::HAT),
                self::line(self::WAVE),
                self::help(),
            ])),
            'ballot' => array_values(array_filter([
                self::line(self::hello($user) . ' Ketuk foto paslon pilihanmu.'),
                self::line('Mau membandingkan dulu? Tekan Visi & misi di bawah nama paslon.'),
                self::line('Sudah yakin? Tekan Kirim suara. Suara hanya bisa dikirim sekali.'),
                $setting->vote_end ? self::line('Tidak perlu buru-buru. Pemilihan dibuka sampai ' . self::when($setting->vote_end) . '.') : null,
                self::line('Tenang, aku tidak mengintip pilihanmu.'),
                self::line(self::WATER),
                self::line(self::ONE_VOTE),
            ])),
            'closed' => [
                self::line('Pemilihan sudah ditutup.', $results),
                self::line('Hasil penghitungan ada di halaman awal.', $results),
                self::line('Sampai jumpa di pemilihan berikutnya!'),
                self::line(self::HAT),
            ],
            'receipt' => array_values(array_filter([
                self::line('Terima kasih sudah memilih!'),
                self::line('Hormat untukmu, pemilih teladan!'),
                self::line('Suaramu sudah masuk kotak. Aman.'),
                self::line(self::SECRET),
                $setting->status() === ElectionSetting::STATUS_OPEN ? self::line('Ajak temanmu yang belum memilih, ya!') : null,
                $setting->status() === ElectionSetting::STATUS_OPEN && $setting->vote_end
                    ? self::line('Hasil diumumkan setelah pemilihan ditutup ' . self::when($setting->vote_end) . '.')
                    : null,
            ])),
        };

        return ['key' => "vote:{$page}", 'lines' => $lines];
    }

    private static function homeLines(string $who, ElectionSetting $setting, string $status, ?User $user): array
    {
        $results = ['label' => 'Lihat hasil', 'href' => '#hasil'];

        if ($who === 'admin') {
            return [
                self::line('Halo, admin! Panel admin ada di sini.', ['label' => 'Panel admin', 'href' => url('/back-admin-logged/redirect-role')]),
                ...self::scheduleLines($setting, $status, $results),
                self::line(self::HAT),
                self::line(self::WATER),
            ];
        }

        if ($who === 'voted') {
            return array_values(array_filter([
                self::line(($name = self::name($user)) ? "Terima kasih sudah memilih, {$name}!" : 'Terima kasih sudah memilih!'),
                $status === ElectionSetting::STATUS_CLOSED
                    ? self::line('Hasil penghitungan sudah bisa dilihat di bawah.', $results)
                    : ($setting->vote_end ? self::line('Hasil diumumkan setelah pemilihan ditutup ' . self::when($setting->vote_end) . '.') : null),
                $status === ElectionSetting::STATUS_OPEN ? self::line('Ajak temanmu yang belum memilih, ya!') : null,
                self::line(self::SECRET),
                self::line(self::WAVE),
            ]));
        }

        if ($who === 'voter') {
            $lead = match ($status) {
                ElectionSetting::STATUS_OPEN => self::line(self::hello($user) . ' Surat suaramu sudah menunggu.', ['label' => 'Buka surat suara', 'href' => url('/vote')]),
                ElectionSetting::STATUS_CLOSED => self::line('Pemilihan sudah ditutup. Terima kasih sudah mampir!', $results),
                // Sudah masuk tapi belum dibuka: jangan suruh mencari akun di email.
                default => self::line(self::hello($user) . ' Kamu sudah masuk. Surat suara muncul saat pemilihan dibuka.'),
            };

            return [
                $lead,
                ...self::scheduleLines($setting, $status, $results),
                self::line(self::ENVELOPES),
                self::line(self::FINAL),
                self::line(self::HAT),
                self::line(self::WATER),
            ];
        }

        // Tamu
        $lead = $status === ElectionSetting::STATUS_CLOSED
            ? self::line('Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!', $results)
            : self::line(self::HELLO);

        return array_values(array_filter([
            $lead,
            ...self::scheduleLines($setting, $status, $results),
            $status === ElectionSetting::STATUS_OPEN ? self::line('Sudah punya akun? Masuk dan pilih paslonmu.', ['label' => 'Masuk untuk memilih', 'href' => route('login')]) : null,
            $status !== ElectionSetting::STATUS_CLOSED ? self::line(self::MAIL) : null,
            self::help(),
            self::line(self::ENVELOPES),
            self::line(self::ONE_VOTE),
            self::line(self::HAT),
            self::line(self::WAVE),
        ]));
    }

    /** Kalimat jadwal sesuai keadaan (tanpa sapaan). */
    private static function scheduleLines(ElectionSetting $setting, string $status, array $results): array
    {
        return match ($status) {
            ElectionSetting::STATUS_UPCOMING => [
                self::line('Pemilihan dibuka ' . self::when($setting->vote_start) . '. Pasang pengingat, ya!'),
                self::line('Hitung mundurnya ada di pita jadwal. Aku juga ikut menghitung.'),
            ],
            ElectionSetting::STATUS_OPEN => [
                self::line($setting->vote_end
                    ? 'Pemilihan sedang berlangsung sampai ' . self::when($setting->vote_end) . '.'
                    : 'Pemilihan sedang berlangsung.'),
            ],
            ElectionSetting::STATUS_CLOSED => [
                self::line('Hasil penghitungannya ada di bawah.', $results),
            ],
            default => [
                self::line('Jadwal pemilihan belum diumumkan. Sabar ya, aku juga menunggu.'),
            ],
        };
    }

    /** Nama peserta untuk sapaan. Username acak tidak pernah dipakai sebagai nama. */
    private static function name(User $user): ?string
    {
        $name = trim((string) $user->details?->participant_name);

        return $name === '' ? null : $name;
    }

    private static function hello(User $user): string
    {
        return ($name = self::name($user)) ? "Halo, {$name}!" : 'Halo!';
    }

    private static function when(CarbonInterface $date): string
    {
        $local = $date->copy()->locale('id');

        return $local->translatedFormat('l, j F') . ' pukul ' . $local->format('H.i') . ' WIB';
    }

    /** Arahan ke kontak panitia hanya bila ada kontak yang tampil di footer. */
    private static function help(): ?array
    {
        return \App\Models\ContactPerson::query()->exists() ? self::line(self::HELP) : null;
    }

    private static function line(string $text, ?array $action = null): array
    {
        return ['text' => $text, 'action' => $action];
    }
}
