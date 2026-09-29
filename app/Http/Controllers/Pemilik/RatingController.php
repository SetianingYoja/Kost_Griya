<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $jenis = $request->query('jenis');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $search = $request->query('q');

        $query = Rating::with(['user', 'kamar', 'keluhan.kamar']);

        if ($status && in_array($status, ['Menunggu Validasi', 'Disetujui', 'Ditolak'])) {
            $query->where('status', $status);
        }

        if ($jenis) {
            $query->where('jenis_rating', $jenis);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('komentar', 'like', "%{$search}%")
                    ->orWhere('balasan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $ratings = $query->latest()->paginate(10)->withQueryString();

        // Hitungan tab status
        $countMenunggu = Rating::where('status', 'Menunggu Validasi')->count();
        $countDisetujui = Rating::where('status', 'Disetujui')->count();
        $countDitolak = Rating::where('status', 'Ditolak')->count();
        $countSemua = Rating::count();

        // Ringkasan nilai rata-rata (hanya ulasan yang disetujui)
        $avgKost = Rating::where('status', 'Disetujui')->where('jenis_rating', 'Kost')->avg('skor') ?? 5.0;
        $avgKeluhan = Rating::where('status', 'Disetujui')->where('jenis_rating', 'Penanganan Keluhan')->avg('skor') ?? 5.0;
        $totalRating = Rating::count();

        return view('pemilik.rating.index', compact(
            'ratings',
            'status',
            'jenis',
            'avgKost',
            'avgKeluhan',
            'totalRating',
            'fromDate',
            'toDate',
            'search',
            'countMenunggu',
            'countDisetujui',
            'countDitolak',
            'countSemua'
        ));
    }

    public function approve($id)
    {
        $rating = Rating::with('user')->findOrFail($id);
        $rating->status = 'Disetujui';
        $rating->disetujui_pada = Carbon::now();
        $rating->save();

        return back()->with('success', 'Ulasan dari '.($rating->user->name ?? 'penghuni').' berhasil disetujui dan kini tampil di publik.');
    }

    public function reject($id)
    {
        $rating = Rating::with('user')->findOrFail($id);
        $rating->status = 'Ditolak';
        $rating->save();

        return back()->with('success', 'Ulasan dari '.($rating->user->name ?? 'penghuni').' berhasil ditolak dan tidak akan tampil di publik.');
    }

    public function balas(Request $request, $id)
    {
        $rating = Rating::with('user')->findOrFail($id);

        $request->validate([
            'balasan' => ['required', 'string', 'max:1000'],
            'setujui_sekaligus' => ['nullable', 'boolean'],
        ], [
            'balasan.required' => 'Teks balasan ulasan tidak boleh kosong.',
            'balasan.max' => 'Teks balasan maksimal 1000 karakter.',
        ]);

        $rating->balasan = $request->balasan;
        $rating->dibalas_pada = Carbon::now();

        if ($request->boolean('setujui_sekaligus') || $rating->status === 'Menunggu Validasi') {
            $rating->status = 'Disetujui';
            $rating->disetujui_pada = Carbon::now();
        }

        $rating->save();

        return back()->with('success', 'Balasan untuk ulasan '.($rating->user->name ?? 'penghuni').' berhasil disimpan'.($rating->status === 'Disetujui' ? ' dan ulasan telah disetujui.' : '.'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rating_ids' => ['required', 'array', 'min:1'],
            'rating_ids.*' => ['exists:ratings,id'],
        ], [
            'rating_ids.required' => 'Pilih minimal satu ulasan untuk memproses aksi massal.',
            'rating_ids.min' => 'Pilih minimal satu ulasan untuk memproses aksi massal.',
        ]);

        $count = count($request->rating_ids);

        if ($request->action === 'approve') {
            Rating::whereIn('id', $request->rating_ids)->update([
                'status' => 'Disetujui',
                'disetujui_pada' => Carbon::now(),
            ]);
            $message = "Berhasil menyetujui {$count} ulasan sekaligus. Ulasan kini tampil di website publik.";
        } else {
            Rating::whereIn('id', $request->rating_ids)->update([
                'status' => 'Ditolak',
            ]);
            $message = "Berhasil menolak {$count} ulasan sekaligus.";
        }

        return back()->with('success', $message);
    }

    public function approveAll()
    {
        $pendingCount = Rating::where('status', 'Menunggu Validasi')->count();

        if ($pendingCount === 0) {
            return back()->with('info', 'Tidak ada ulasan yang sedang menunggu validasi.');
        }

        Rating::where('status', 'Menunggu Validasi')->update([
            'status' => 'Disetujui',
            'disetujui_pada' => Carbon::now(),
        ]);

        return back()->with('success', "Seluruh ulasan ({$pendingCount} ulasan) yang menunggu validasi telah berhasil disetujui.");
    }
}
