<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionSetting;
use Illuminate\Http\Request;

class ElectionScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }

    public function edit()
    {
        return view('admin.pages.election-schedule.edit', [
            'setting' => ElectionSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate(
            [
                // Dua tahun berurutan, mis. 2025/2026
                'period' => ['bail', 'nullable', 'regex:/^\d{4}\/\d{4}$/', function ($attribute, $value, $fail) {
                    [$from, $to] = array_map('intval', explode('/', $value));
                    if ($to !== $from + 1) {
                        $fail('Periode harus dua tahun berurutan, mis. 2025/2026.');
                    }
                }],
                'vote_start' => 'required|date',
                'vote_end' => 'required|date|after:vote_start',
            ],
            [
                'period.regex' => 'Periode ditulis seperti 2025/2026.',
                'required' => ':attribute harus diisi.',
                'date' => ':attribute harus berupa tanggal dan jam yang valid.',
                'after' => ':attribute harus setelah Waktu Dibuka.',
            ],
            [
                'period' => 'Periode',
                'vote_start' => 'Waktu Dibuka',
                'vote_end' => 'Waktu Ditutup',
            ],
        );

        ElectionSetting::current()->fill($validated)->save();

        return redirect('/back-admin-logged/election-schedule')->withStatus('Periode dan jadwal pemilihan berhasil disimpan.');
    }
}
