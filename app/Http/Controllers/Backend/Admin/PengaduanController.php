<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Pengaduan;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class PengaduanController extends Controller
{
    public function index()
    {
        return view('contents.backend.admin.pengaduan.index');
    }

    /**
     * Keperluan API DataTables
     *
     * @param DataTables $datatables
     * @return JsonResponse
     */
    public function data(DataTables $datatables): JsonResponse
    {
        return $datatables->eloquent(
            Pengaduan::query()
        )
            ->addIndexColumn()
            ->toJson();
    }

    public function chat()
    {
        return view('contents.backend.admin.pengaduan.chat.index');
    }
}
