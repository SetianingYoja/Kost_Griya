<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\RiwayatAktivitas;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeluhanController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $query = Keluhan::with(['user', 'kamar', 'rating']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $keluhans = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.keluhan.index', compact('keluhans', 'status', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $keluhan = Keluhan::with(['user', 'kamar', 'rating'])->findOrFail($id);

        return view('pemilik.keluhan.show', compact('keluhan'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Super Admin hanya memiliki akses View Only pada modul Keluhan.');
        }

        $keluhan = Keluhan::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:Menunggu,Diproses,Selesai,Ditolak'],
            'tanggapan' => ['required', 'string', 'max:2000'],
        ], [
            'tanggapan.required' => 'Tanggapan atau keterangan penanganan keluhan wajib diisi.',
        ]);

        $keluhan->status = $request->status;
        $keluhan->tanggapan = $request->tanggapan;

        if ($request->status === 'Selesai') {
            $keluhan->selesai_pada = Carbon::now();
        }

        $keluhan->save();

        RiwayatAktivitas::catat(
            $keluhan->user_id,
            'Keluhan '.$request->status,
            'Penanganan keluhan "'.$keluhan->judul.'" berstatus '.$request->status.'. Tanggapan: '.$request->tanggapan,
            $request->status === 'Selesai' ? 'success' : 'info'
        );

        $notification = new BusinessNotification(
            'keluhan',
            'status_diperbarui',
            'Status keluhan diperbarui',
            'Keluhan "'.$keluhan->judul.'" berstatus '.$keluhan->status.'.',
            'keluhan:'.$keluhan->id.':status:'.strtolower($keluhan->status),
            ['entity_type' => 'keluhan', 'entity_id' => $keluhan->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($keluhan->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Status keluhan dan tanggapan berhasil diperbarui.');
    }
}
