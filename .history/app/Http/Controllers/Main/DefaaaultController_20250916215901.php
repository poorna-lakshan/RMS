<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\default\Codegen;

class DefaaaultController extends Controller
{
    public function generateCode($type)
    {
        try {
            // Fetch the configuration for the given type (e.g., 'Item')
            $config = Codegen::where('type', $type)->first();

            // Check if configuration exists
            if (!$config) {
                return response()->json([
                    'message' => 'No configuration found for the given type.'
                ], 404);
            }

            // Get the current 'no' and increment it
            $currentNo = $config->no;
            $newNo = str_pad($currentNo + 1, $config->pad, '0', STR_PAD_LEFT);

            // Generate the new code
            $newCode = $config->prefix . $newNo;

            // Update the 'no' in the database
            // $config->no = $currentNo + 1;
            // $config->save();

            // Return the generated code
            return $newCode;
          

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while generating the code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
