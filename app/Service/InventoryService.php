<?php

namespace App\Service;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\ProductVariable;

class InventoryService
{
    public static function isDeductedStatus($statusId)
    {
        // Deducted statuses: Pending (1), Processing (2), On The Way (3), On Hold (4), In Courier (5), Completed (6), Need Ready (10)
        return in_array((int)$statusId, [1, 2, 3, 4, 5, 6, 10]);
    }

    public static function isRevertedStatus($statusId)
    {
        // Reverted statuses: Cancelled (7), Returned (9)
        return in_array((int)$statusId, [7, 9]);
    }

    public static function deductStock(Order $order)
    {
        foreach ($order->orderdetails as $detail) {
            self::adjustItemStock($detail, 'subtract');
        }
    }

    public static function revertStock(Order $order)
    {
        foreach ($order->orderdetails as $detail) {
            self::adjustItemStock($detail, 'add');
        }
    }

    public static function adjustStockForStatusChange(Order $order, $oldStatusId, $newStatusId)
    {
        $oldIsDeducted = self::isDeductedStatus($oldStatusId);
        $newIsDeducted = self::isDeductedStatus($newStatusId);

        if ($oldIsDeducted && !$newIsDeducted) {
            // Deducted -> Reverted/Other
            self::revertStock($order);
        } elseif (!$oldIsDeducted && $newIsDeducted) {
            // Reverted/Other -> Deducted
            self::deductStock($order);
        }
    }

    public static function returnItemStock(OrderDetails $detail)
    {
        self::adjustItemStock($detail, 'add');
    }

    protected static function adjustItemStock(OrderDetails $detail, $action)
    {
        $qty = (int)$detail->qty;
        if ($qty <= 0) {
            return;
        }

        if ($detail->product_type == 1) {
            // Simple product
            $product = Product::find($detail->product_id);
            if ($product) {
                if ($action === 'subtract') {
                    $product->stock -= $qty;
                } else {
                    $product->stock += $qty;
                }
                $product->save();
            }
        } else {
            // Variable product
            $query = ProductVariable::where('product_id', $detail->product_id);
            if ($detail->product_color) {
                $query->where('color', $detail->product_color);
            }
            if ($detail->product_size) {
                $query->where('size', $detail->product_size);
            }
            $product = $query->first();
            if ($product) {
                if ($action === 'subtract') {
                    $product->stock -= $qty;
                } else {
                    $product->stock += $qty;
                }
                $product->save();
            }
        }
    }

    public static function checkStockAvailable($productId, $type, $color, $size, $qtyToAdd)
    {
        if ($type == 1) {
            $product = Product::find($productId);
            return $product ? $product->stock >= $qtyToAdd : false;
        } else {
            $var_product = ProductVariable::where([
                'product_id' => $productId,
                'color' => $color,
                'size' => $size
            ])->first();
            return $var_product ? $var_product->stock >= $qtyToAdd : false;
        }
    }
}
