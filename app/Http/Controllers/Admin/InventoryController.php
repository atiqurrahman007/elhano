<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariable;
use App\Models\ScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class InventoryController extends Controller
{
    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->search;

        // Simple products (type = 1)
        $simpleQuery = Product::select('id','name','type','stock','stock_alert','new_price','purchase_price','pro_barcode','status')
            ->with('image', 'category')
            ->where('type', 1);

        if ($search) {
            $simpleQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('pro_barcode', 'LIKE', "%$search%");
            });
        }

        $simpleProducts = $simpleQuery->orderBy('id', 'DESC')->paginate(30, ['*'], 'simple_page');

        // Variable products summary
        $variableQuery = ProductVariable::with(['product' => function ($q) {
            $q->select('id', 'name', 'stock_alert');
        }, 'product.image'])
        ->select('id','product_id','size','color','new_price','purchase_price','stock','pro_barcode');

        if ($search) {
            $variableQuery->where(function ($q) use ($search) {
                $q->where('pro_barcode', 'LIKE', "%$search%")
                  ->orWhereHas('product', fn($q2) => $q2->where('name', 'LIKE', "%$search%"));
            });
        }

        $variables = $variableQuery->orderBy('id', 'DESC')->paginate(30, ['*'], 'var_page');

        // Stats
        $totalSimple       = Product::where('type', 1)->count();
        $totalVariant      = ProductVariable::count();
        $lowStockSimple    = Product::where('type', 1)->whereColumn('stock', '<=', 'stock_alert')->count();
        $lowStockVariant   = ProductVariable::whereHas('product', fn($q) => $q->whereRaw('product_variables.stock <= products.stock_alert'))->count();
        $outOfStockSimple  = Product::where('type', 1)->where('stock', '<=', 0)->count();
        $outOfStockVariant = ProductVariable::where('stock', '<=', 0)->count();

        return view('backEnd.inventory.index', compact(
            'simpleProducts', 'variables', 'search',
            'totalSimple', 'totalVariant',
            'lowStockSimple', 'lowStockVariant',
            'outOfStockSimple', 'outOfStockVariant'
        ));
    }

    // ─── SCANNER PAGE ─────────────────────────────────────────────────────────

    public function scanPage()
    {
        return view('backEnd.inventory.scan');
    }

    // ─── AJAX BARCODE LOOKUP ──────────────────────────────────────────────────

    public function lookup(Request $request)
    {
        $barcode = trim($request->barcode);

        if (!$barcode) {
            return response()->json(['found' => false, 'message' => 'Please enter a barcode.']);
        }

        // 1. Try simple product first
        $product = Product::select('id','name','type','stock','stock_alert','new_price','purchase_price','pro_barcode','status')
            ->with('image', 'category')
            ->where('type', 1)
            ->where('pro_barcode', $barcode)
            ->first();

        if ($product) {
            return response()->json([
                'found'     => true,
                'type'      => 'simple',
                'id'        => $product->id,
                'name'      => $product->name,
                'barcode'   => $product->pro_barcode,
                'price'     => $product->new_price,
                'purchase_price' => $product->purchase_price,
                'stock'     => $product->stock,
                'stock_alert'=> $product->stock_alert,
                'category'  => $product->category ? $product->category->name : 'N/A',
                'image'     => $product->image ? asset($product->image->image) : null,
            ]);
        }

        // 2. Try variant
        $variable = ProductVariable::select('id','product_id','size','color','new_price','purchase_price','stock','pro_barcode')
            ->with(['product' => fn($q) => $q->select('id','name','stock_alert')->with('image','category')])
            ->where('pro_barcode', $barcode)
            ->first();

        if ($variable) {
            return response()->json([
                'found'     => true,
                'type'      => 'variable',
                'id'        => $variable->id,
                'product_id'=> $variable->product_id,
                'name'      => $variable->product ? $variable->product->name : 'Unknown',
                'barcode'   => $variable->pro_barcode,
                'size'      => $variable->size,
                'color'     => $variable->color,
                'price'     => $variable->new_price,
                'purchase_price' => $variable->purchase_price,
                'stock'     => $variable->stock,
                'stock_alert'=> $variable->product ? $variable->product->stock_alert : 0,
                'category'  => ($variable->product && $variable->product->category) ? $variable->product->category->name : 'N/A',
                'image'     => ($variable->product && $variable->product->image) ? asset($variable->product->image->image) : null,
            ]);
        }

        // Log failed lookup
        ScanLog::create([
            'barcode'    => $barcode,
            'product_id' => 0,
            'action'     => 'not_found',
            'qty_before' => 0,
            'qty_change' => 0,
            'qty_after'  => 0,
            'user_id'    => Auth::id(),
            'note'       => 'Barcode not found in system',
        ]);

        return response()->json(['found' => false, 'message' => 'No product found for barcode: ' . $barcode]);
    }

    // ─── STOCK RECEIVE (ADD STOCK) ────────────────────────────────────────────

    public function receive(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'qty'     => 'required|integer|min:1',
            'type'    => 'required|in:simple,variable',
            'id'      => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            if ($request->type === 'simple') {
                $product = Product::findOrFail($request->id);
                $before  = $product->stock;
                $product->stock += $request->qty;
                $product->save();

                ScanLog::create([
                    'barcode'    => $request->barcode,
                    'product_id' => $product->id,
                    'variable_id'=> null,
                    'action'     => 'receive',
                    'qty_before' => $before,
                    'qty_change' => +$request->qty,
                    'qty_after'  => $product->stock,
                    'user_id'    => Auth::id(),
                    'note'       => $request->note,
                ]);

                $newStock = $product->stock;
            } else {
                $variable = ProductVariable::findOrFail($request->id);
                $before   = $variable->stock;
                $variable->stock += $request->qty;
                $variable->save();

                ScanLog::create([
                    'barcode'    => $request->barcode,
                    'product_id' => $variable->product_id,
                    'variable_id'=> $variable->id,
                    'action'     => 'receive',
                    'qty_before' => $before,
                    'qty_change' => +$request->qty,
                    'qty_after'  => $variable->stock,
                    'user_id'    => Auth::id(),
                    'note'       => $request->note,
                ]);

                $newStock = $variable->stock;
            }

            DB::commit();
            return response()->json([
                'success'   => true,
                'message'   => 'Stock received successfully!',
                'new_stock' => $newStock,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ─── STOCK ADJUST (SET / INCREMENT / DECREMENT) ───────────────────────────

    public function adjust(Request $request)
    {
        $request->validate([
            'barcode'      => 'required|string',
            'qty'          => 'required|integer',
            'adjust_type'  => 'required|in:set,add,subtract',
            'type'         => 'required|in:simple,variable',
            'id'           => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            if ($request->type === 'simple') {
                $product = Product::findOrFail($request->id);
                $before  = $product->stock;

                switch ($request->adjust_type) {
                    case 'set':      $product->stock  = $request->qty; break;
                    case 'add':      $product->stock += $request->qty; break;
                    case 'subtract': $product->stock -= $request->qty; break;
                }
                $product->stock = max(0, $product->stock);
                $product->save();

                ScanLog::create([
                    'barcode'    => $request->barcode,
                    'product_id' => $product->id,
                    'variable_id'=> null,
                    'action'     => 'adjust',
                    'qty_before' => $before,
                    'qty_change' => $product->stock - $before,
                    'qty_after'  => $product->stock,
                    'user_id'    => Auth::id(),
                    'note'       => $request->note ?? 'Manual adjust (' . $request->adjust_type . ')',
                ]);

                $newStock = $product->stock;
            } else {
                $variable = ProductVariable::findOrFail($request->id);
                $before   = $variable->stock;

                switch ($request->adjust_type) {
                    case 'set':      $variable->stock  = $request->qty; break;
                    case 'add':      $variable->stock += $request->qty; break;
                    case 'subtract': $variable->stock -= $request->qty; break;
                }
                $variable->stock = max(0, $variable->stock);
                $variable->save();

                ScanLog::create([
                    'barcode'    => $request->barcode,
                    'product_id' => $variable->product_id,
                    'variable_id'=> $variable->id,
                    'action'     => 'adjust',
                    'qty_before' => $before,
                    'qty_change' => $variable->stock - $before,
                    'qty_after'  => $variable->stock,
                    'user_id'    => Auth::id(),
                    'note'       => $request->note ?? 'Manual adjust (' . $request->adjust_type . ')',
                ]);

                $newStock = $variable->stock;
            }

            DB::commit();
            return response()->json([
                'success'   => true,
                'message'   => 'Stock adjusted successfully!',
                'new_stock' => $newStock,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ─── SCAN LOG HISTORY ─────────────────────────────────────────────────────

    public function log(Request $request)
    {
        $search  = $request->search;
        $action  = $request->action;
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $logs = ScanLog::with('product', 'variable', 'user')
            ->when($search, fn($q) => $q->where('barcode', 'LIKE', "%$search%")
                ->orWhereHas('product', fn($q2) => $q2->where('name', 'LIKE', "%$search%")))
            ->when($action, fn($q) => $q->where('action', $action))
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy('id', 'DESC')
            ->paginate(50);

        return view('backEnd.inventory.log', compact('logs', 'search', 'action', 'dateFrom', 'dateTo'));
    }
}
