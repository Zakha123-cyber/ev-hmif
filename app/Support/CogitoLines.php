<?php

namespace App\Support;

use App\Models\ElectionSetting;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Kalimat balon COGITO (sapaan + arahan) per halaman dan keadaan jadwal.
 * Hanya data nyata: tanggal dari jadwal, nama dari akun. Tidak pernah menyebut
 * paslon yang dipilih seseorang.
 *
 * Setiap metode mengembalikan ['lines' => string[], 'action' => ?['label' => …, 'href' => …]].
 * home() juga mengembalikan 'key': kunci "Tutup" per keadaan, supaya arahan baru
 * (setelah masuk atau memilih) tidak ikut tersembunyi.
 */
class CogitoLines
{
    private const HELLO = 'Halo! Aku COGITO, maskot HMIF.';

    public static function home(ElectionSetting $setting, ?User $user): array
    {
        $status = $setting->status();
        $who = match (true) {
            ! $user => 'guest',
            $user->hasRole('Admin') => 'admin',
            $user->status === 'voted' => 'voted',
            default => 'voter',
        };

        return ['key' => "home:{$status}:{$who}"] + self::homeFor($who, $setting, $status, $user);
    }

    private static function homeFor(string $who, ElectionSetting $setting, string $status, ?User $user): array
    {
        if ($who === 'admin') {
            return self::make(self::guestLines($setting, $status, admin: true), [
                'label' => 'Panel admin',
                'href' => url('/back-admin-logged/redirect-role'),
            ]);
        }

        if ($who === 'voted') {
            return self::make([self::thanks($setting)]);
        }

        if ($who === 'voter' && $status === ElectionSetting::STATUS_OPEN) {
            return self::make(
                ['Halo, ' . self::name($user) . '! Surat suaramu sudah menunggu.'],
                ['label' => 'Buka surat suara', 'href' => url('/vote')],
            );
        }

        // Sudah masuk tapi belum dibuka: jangan suruh mencari akun di email.
        if ($who === 'voter' && in_array($status, [ElectionSetting::STATUS_UPCOMING, ElectionSetting::STATUS_NOT_SET], true)) {
            return self::make([
                'Halo, ' . self::name($user) . '!',
                $status === ElectionSetting::STATUS_UPCOMING
                    ? 'Pemilihan dibuka ' . self::when($setting->vote_start) . '.'
                    : 'Jadwal pemilihan belum diumumkan panitia.',
                'Kamu sudah masuk. Surat suara muncul saat pemilihan dibuka.',
            ]);
        }

        $action = match ($status) {
            ElectionSetting::STATUS_OPEN => ['label' => 'Masuk untuk memilih', 'href' => route('login')],
            ElectionSetting::STATUS_CLOSED => ['label' => 'Lihat hasil', 'href' => '#hasil'],
            default => null,
        };

        return self::make(self::guestLines($setting, $status), $action);
    }

    /**
     * @param  'prepare'|'ballot'|'closed'|'receipt'  $page
     */
    public static function vote(string $page, ElectionSetting $setting, User $user): array
    {
        $hello = 'Halo, ' . self::name($user) . '!';

        return match ($page) {
            'prepare' => self::make($setting->vote_start && $setting->vote_start->isFuture()
                ? [$hello, 'Surat suara muncul di sini saat pemilihan dibuka ' . self::when($setting->vote_start) . '.', 'Sambil menunggu, buka amplop paslon di bawah.']
                : [$hello, 'Jadwal pemilihan belum diumumkan panitia.', 'Sambil menunggu, buka amplop paslon di bawah.']),
            'ballot' => self::make([
                $hello . ' Ketuk foto paslon pilihanmu.',
                'Ingin membandingkan? Tekan Visi & misi di bawah nama paslon.',
                'Sudah yakin? Tekan Kirim suara. Suara hanya bisa dikirim sekali.',
            ]),
            'closed' => self::make(
                ['Pemilihan sudah ditutup.', 'Hasil penghitungan ada di halaman awal.'],
                ['label' => 'Lihat hasil', 'href' => url('/') . '#hasil'],
            ),
            'receipt' => self::make(['Terima kasih sudah memilih!']),
        };
    }

    /** @return string[] */
    private static function guestLines(ElectionSetting $setting, string $status, bool $admin = false): array
    {
        return match ($status) {
            ElectionSetting::STATUS_UPCOMING => [
                self::HELLO,
                'Pemilihan dibuka ' . self::when($setting->vote_start) . '.',
                'Cek email kampusmu untuk akun memilih.',
            ],
            ElectionSetting::STATUS_OPEN => array_values(array_filter([
                self::HELLO,
                $setting->vote_end
                    ? 'Pemilihan sedang berlangsung sampai ' . self::when($setting->vote_end) . '.'
                    : 'Pemilihan sedang berlangsung.',
                $admin ? null : 'Masuk dengan akun dari email kampusmu.',
            ])),
            ElectionSetting::STATUS_CLOSED => [
                'Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!',
                'Lihat hasil penghitungannya di bawah.',
            ],
            default => [
                self::HELLO,
                'Jadwal pemilihan belum diumumkan. Akun memilih dikirim ke email kampusmu.',
                'Sambil menunggu, buka amplop paslon untuk kenalan.',
            ],
        };
    }

    private static function thanks(ElectionSetting $setting): string
    {
        $closed = $setting->status() === ElectionSetting::STATUS_CLOSED;

        return match (true) {
            $closed => 'Terima kasih sudah memilih! Hasil penghitungan sudah bisa dilihat di bawah.',
            (bool) $setting->vote_end => 'Terima kasih sudah memilih! Hasil diumumkan setelah pemilihan ditutup ' . self::when($setting->vote_end) . '.',
            default => 'Terima kasih sudah memilih!',
        };
    }

    private static function name(User $user): string
    {
        return $user->details?->participant_name ?: $user->username;
    }

    private static function when(CarbonInterface $date): string
    {
        $local = $date->copy()->locale('id');

        return $local->translatedFormat('l, j F') . ' pukul ' . $local->format('H.i') . ' WIB';
    }

    private static function make(array $lines, ?array $action = null): array
    {
        return ['lines' => $lines, 'action' => $action];
    }
}
