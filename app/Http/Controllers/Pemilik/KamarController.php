<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KamarController extends Controller
{
    public function index(Request $request)
    {
        $query = Kamar::with('tipeKamar');

        if ($request->filled('tipe')) {
            $query->where('tipe_kamar_id', $request->tipe);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kamar', 'like', "%{$search}%")
                  ->orWhere('fasilitas', 'like', "%{$search}%");
            });
        }

        $kamars = $query->latest()->paginate(10)->withQueryString();
        $tipeKamars = TipeKamar::all();

        return view('pemilik.kamar.index', compact('kamars', 'tipeKamars'));
    }

    public function create()
    {
        $tipeKamars = TipeKamar::all();
        return view('pemilik.kamar.create', compact('tipeKamars'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar' => ['required', 'string', 'max:50', 'unique:kamar,nomor_kamar'],
            'tipe_kamar_id' => ['required', 'exists:tipe_kamar,id'],
            'lantai' => ['required', 'integer', 'min:1', 'max:10'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:Tersedia,Terisi,Tidak tersedia'],
            'fasilitas' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
            'fotos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        ], [
            'nomor_kamar.unique' => 'Nomor kamar tersebut sudah terdaftar.',
            'tipe_kamar_id.required' => 'Tipe kamar wajib dipilih.',
        ]);

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('kamar', 'public');
        }

        $kamar = Kamar::create([
            'nomor_kamar' => $validated['nomor_kamar'],
            'tipe_kamar_id' => $validated['tipe_kamar_id'],
            'lantai' => $validated['lantai'],
            'harga' => $validated['harga'],
            'status' => $validated['status'],
            'fasilitas' => $validated['fasilitas'],
            'deskripsi' => $validated['deskripsi'],
            'foto' => $path,
        ]);

        $galleryFiles = $request->file('fotos', []);
        foreach ($galleryFiles as $index => $galleryFile) {
            if ($galleryFile && $galleryFile->isValid()) {
                $galleryPath = $galleryFile->store('kamar', 'public');
                $kamar->galleryImages()->create([
                    'foto' => $galleryPath,
                    'is_cover' => $index === 0 && ! $path,
                    'urutan' => $index,
                ]);
            }
        }

        if ($path) {
            $kamar->galleryImages()->where('foto', $path)->firstOrCreate([
                'foto' => $path,
                'is_cover' => true,
                'urutan' => 0,
            ]);
        }

        return redirect()->route('pemilik.kamar.index')
            ->with('success', 'Data kamar ' . $validated['nomor_kamar'] . ' berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kamar = Kamar::with(['tipeKamar', 'sewas.user', 'bookings.user', 'keluhans'])->findOrFail($id);
        return view('pemilik.kamar.show', compact('kamar'));
    }

    public function edit($id)
    {
        $kamar = Kamar::findOrFail($id);
        $tipeKamars = TipeKamar::all();
        return view('pemilik.kamar.edit', compact('kamar', 'tipeKamars'));
    }

    public function update(Request $request, $id)
    {
        $kamar = Kamar::findOrFail($id);

        $validated = $request->validate([
            'nomor_kamar' => ['required', 'string', 'max:50', 'unique:kamar,nomor_kamar,' . $kamar->id],
            'tipe_kamar_id' => ['required', 'exists:tipe_kamar,id'],
            'lantai' => ['required', 'integer', 'min:1', 'max:10'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:Tersedia,Terisi,Tidak tersedia'],
            'fasilitas' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
            'fotos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('kamar', 'public');
            $kamar->foto = $path;
        }

        $kamar->nomor_kamar = $validated['nomor_kamar'];
        $kamar->tipe_kamar_id = $validated['tipe_kamar_id'];
        $kamar->lantai = $validated['lantai'];
        $kamar->harga = $validated['harga'];
        $kamar->status = $validated['status'];
        $kamar->fasilitas = $validated['fasilitas'];
        $kamar->deskripsi = $validated['deskripsi'];
        $kamar->save();

        $galleryFiles = $request->file('fotos', []);
        if (!empty($galleryFiles)) {
            $order = 0;
            foreach ($galleryFiles as $galleryFile) {
                if ($galleryFile && $galleryFile->isValid()) {
                    $galleryPath = $galleryFile->store('kamar', 'public');
                    $kamar->galleryImages()->create([
                        'foto' => $galleryPath,
                        'is_cover' => false,
                        'urutan' => $order++,
                    ]);
                }
            }
        }

        return redirect()->route('pemilik.kamar.index')
            ->with('success', 'Data kamar ' . $kamar->nomor_kamar . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);

        // Jangan dihapus jika ada sewa aktif
        if ($kamar->sewas()->where('status', 'Aktif')->exists()) {
            return back()->with('error', 'Kamar tidak dapat dihapus karena masih dihuni oleh penyewa aktif.');
        }

        $nomor = $kamar->nomor_kamar;
        $kamar->delete();

        return redirect()->route('pemilik.kamar.index')
            ->with('success', 'Kamar ' . $nomor . ' berhasil dihapus.');
    }
}
