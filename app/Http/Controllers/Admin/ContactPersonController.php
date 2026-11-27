<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPerson;
use Illuminate\Http\Request;

class ContactPersonController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }

    public function index()
    {
        return view('admin.pages.contact-person.index', [
            'contacts' => ContactPerson::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        ContactPerson::create($this->validated($request));

        return redirect('/back-admin-logged/contact-person')->withStatus('Kontak panitia ditambahkan.');
    }

    public function edit(ContactPerson $contact)
    {
        return view('admin.pages.contact-person.edit', ['contact' => $contact]);
    }

    public function update(Request $request, ContactPerson $contact)
    {
        $contact->update($this->validated($request));

        return redirect('/back-admin-logged/contact-person')->withStatus('Kontak panitia diperbarui.');
    }

    public function destroy(ContactPerson $contact)
    {
        $contact->delete();

        return redirect('/back-admin-logged/contact-person')->withStatus('Kontak ' . $contact->name . ' dihapus.');
    }

    /** Validasi + nomor WhatsApp diseragamkan ke format 62xxxxxxxxxx. */
    private function validated(Request $request): array
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:60',
                'whatsapp' => ['required', function ($attribute, $value, $fail) {
                    if (! ContactPerson::normalizeWhatsapp($value)) {
                        $fail('Nomor WhatsApp tidak valid. Contoh: 081234567890.');
                    }
                }],
                'sort' => 'nullable|integer|min:0|max:999',
            ],
            [
                'required' => ':attribute harus diisi.',
                'max' => ':attribute terlalu panjang.',
                'integer' => ':attribute harus berupa angka.',
            ],
            ['name' => 'Nama', 'whatsapp' => 'Nomor WhatsApp', 'sort' => 'Urutan'],
        );

        return [
            'name' => $data['name'],
            'whatsapp' => ContactPerson::normalizeWhatsapp($data['whatsapp']),
            'sort' => $data['sort'] ?? 0,
        ];
    }
}
