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
                'vote_start' => 'required|date',
                'vote_end' => 'required|date|after:vote_start',
            ],
            [
                'required' => ':attribute harus diisi.',
                'date' => ':attribute harus berupa tanggal dan jam yang valid.',
                'after' => ':attribute harus setelah Waktu Dibuka.',
            ],
            [
                'vote_start' => 'Waktu Dibuka',
                'vote_end' => 'Waktu Ditutup',
            ],
        );

        ElectionSetting::current()->fill($validated)->save();

        return redirect('/back-admin-logged/election-schedule')->withStatus('Jadwal pemilihan berhasil disimpan.');
    }
}
