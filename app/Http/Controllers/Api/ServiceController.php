<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Daftar layanan, terbaru dulu. Query opsional ?active=1 hanya yang aktif.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()->latest();

        // Filter by status (bukan is_active)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $limit = $request->input('limit', 10);
        $services = $query->paginate($limit);

        return response()->json($services);
    }

    /**
     * Detail satu layanan.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(Service::findOrFail($id));
    }

    /**
     * Tambah layanan (admin).
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return response()->json($service, 201);
    }

    /**
     * Ubah layanan (admin) — semua field sometimes.
     */
    public function update(StoreServiceRequest $request, int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->update($request->validated());

        return response()->json($service);
    }

    /**
     * Hapus layanan (admin).
     */
    public function destroy(int $id): JsonResponse
    {
        Service::findOrFail($id)->delete();

        return response()->json(['message' => 'Layanan dihapus.']);
    }
}
