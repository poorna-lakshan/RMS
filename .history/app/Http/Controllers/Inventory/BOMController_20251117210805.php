<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\BOMHeader;
use App\Models\Inventory\BOMDetail;
use App\Models\Inventory\Item;
use App\Http\Controllers\Main\DefaultController;
use App\Models\Inventory\BOMAlternative;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
class BOMController extends Controller
{
    /**
     * List all BOMs
     */
    public function index()
    {
        $boms = BOMHeader::with(['finalItem', 'details.alternatives', 'details.item'])->get();
        return response()->json($boms);
    }

    /**
     * Get a BOM by ID
     */
    public function getById($id)
    {
        $bom = BOMHeader::with(['finalItem','details.alternatives.uom', 'details.alternatives.item.uom', 'details.alternatives.item.itemClass', 'details.item.itemClass', 'details.item.uom', 'details.uom'])
            ->findOrFail($id);
        return response()->json($bom);
    }

    /**
     * Create or update BOM
     */
    public function createOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'date' => 'required|date',
            'ware_house_id' => 'required|integer',
            'item_id' => 'required|integer',
            'active' => 'nullable|boolean',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // BOM Header
            $bomHeader = BOMHeader::updateOrCreate(
                ['code' => $validated['code']],
                [
                    'date' => $validated['date'],
                    'ware_house_id' => $validated['ware_house_id'],
                    'item_id' => $validated['item_id'],
                    'active' => $validated['active'] ?? true,
                ]
            );

            $existingDetailIds = [];

            foreach ($validated['details'] as $line) {
                // BOM Detail
                $bomDetail = BOMDetail::updateOrCreate(
                    [
                        'bom_id' => $bomHeader->id,
                        'line_no' => $line['line_no']
                    ],
                    [
                        'item_id' => $line['item_id'],
                        'base_uom_id' => $line['uom'] ?? null,
                        'qty' => $line['qty'] ?? 0,
                        'base_qty' => $line['base_qty'] ?? 0,
                        'is_alternative' => $line['is_alternative'] ?? false,
                    ]
                );
                $existingDetailIds[] = $bomDetail->id;

                // BOM Alternatives
                if (!empty($line['alternatives'])) {
                    $altIds = [];
                    foreach ($line['alternatives'] as $alt) {
                        $bomAlt = BOMAlternative::updateOrCreate(
                            [
                                'bom_detail_id' => $bomDetail->id,
                                'item_id' => $alt['item_id'],
                            ],
                            [
                                'parent_item_id' => $line['item_id'],
                                'base_uom_id' => $alt['uom'] ?? null,
                                'base_qty' => $alt['base_qty'] ?? 0,
                                'qty' => $alt['qty'] ?? 0,
                            ]
                        );
                        $altIds[] = $bomAlt->id;
                    }

                    // Remove old alternatives not in input
                    BOMAlternative::where('bom_detail_id', $bomDetail->id)
                        ->whereNotIn('id', $altIds)
                        ->delete();
                } else {
                    BOMAlternative::where('bom_detail_id', $bomDetail->id)->delete();
                }
            }

            // Remove old BOM details not in input
            BOMDetail::where('bom_id', $bomHeader->id)
                ->whereNotIn('id', $existingDetailIds)
                ->delete();

            if (!$request->id) {
                $df = new DefaultController();
                $df->UpdateCode('bom');
            }

            DB::commit();

             LogHelper::log(
                auth()->user()->name,
                $request->id ? 'update' : 'create',
                'BOM',
                $request->id ? 'update' : 'create' . $bomHeader->id
            );

            return response()->json([
                'message' => 'BOM saved successfully',
                'bom_id' => $bomHeader->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete BOM
     */
    public function delete($id)
    {
        try {
            $bom = BOMHeader::findOrFail($id);
            $bom->delete();
            LogHelper::log(
            auth()->user()->name,
            'delete',
            'BOM',
            'delete BOM: ' . $bom
        );
            return response()->json(['message' => 'BOM deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function setActive($id, $active)
    {
        try {
            // Find the BOM record by ID
            $bom = BOMHeader::findOrFail($id);

            // Set active status
            $bom->active = $active;
            $bom->save();

            $status = $active ? 'activated' : 'deactivated';

             LogHelper::log(
            auth()->user()->name,
            $status,
            'BOM',
            $status.' BOM: ' . $bom
        );

            return response()->json(['message' => "BOM {$status} successfully"]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'BOM not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function getDefault()
    {
        $df = new DefaultController();
        $item_code = $df->generateCode('bom');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);


        $itm_uom = new ItemUOMController();
        $itm_uom = $itm_uom->index()->getData(true);

        // $menus = Item::with(['category', 'itemClass', 'warehouses', 'skus', 'uom'])
        //     ->whereIn('class_id', [3])
        //     ->whereNotIn('id', function ($query) {
        //         $query->select('item_id')
        //             ->from('bom_headers'); // table name
        //     })
        //     ->get();
        $menus = Item::with(['category', 'itemClass', 'warehouses', 'skus', 'uom'])->whereIn('class_id', [3])->get();
        $ingridents = Item::with(['category', 'itemClass', 'warehouses', 'skus', 'uom'])->whereIn('class_id', [2, 3])->get();

        return response()->json([
            'code' => $item_code,
            'warehouse_list' => $warehouse_list,
            'menus' => $menus,
            'ingridents' => $ingridents,
            'uom' => $itm_uom,
        ]);
    }
}
