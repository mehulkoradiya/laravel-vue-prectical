<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function index(): JsonResponse
    {
        $stores = Store::where('is_active', true)
            ->select('id', 'name', 'location')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    }
}
