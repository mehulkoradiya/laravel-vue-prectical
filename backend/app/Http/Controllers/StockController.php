<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Stock::with('store')
            ->select('stocks.*');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $stocks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $stocks->items(),
            'pagination' => [
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'per_page' => $stocks->perPage(),
                'total' => $stocks->total()
            ]
        ]);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'stocks' => 'required|array|min:1',
            'stocks.*.item_code' => 'required|string',
            'stocks.*.item_name' => 'required|string',
            'stocks.*.quantity' => 'required|integer|min:1',
            'stocks.*.location' => 'required|string',
            'stocks.*.store_id' => 'required|exists:stores,id',
            'stocks.*.in_stock_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $createdStocks = [];
            foreach ($request->stocks as $stockData) {
                $stock = Stock::create([
                    'stock_no' => 'STK' . time() . rand(1000, 9999),
                    'item_code' => $stockData['item_code'],
                    'item_name' => $stockData['item_name'],
                    'quantity' => $stockData['quantity'],
                    'location' => $stockData['location'],
                    'store_id' => $stockData['store_id'],
                    'in_stock_date' => $stockData['in_stock_date'],
                    'status' => 'pending'
                ]);

                $createdStocks[] = $stock->load('store');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdStocks) . ' stocks created successfully',
                'data' => $createdStocks
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create stocks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock deleted successfully'
        ]);
    }
}
