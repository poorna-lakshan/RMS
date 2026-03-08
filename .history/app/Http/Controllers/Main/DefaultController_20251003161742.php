<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\default\Codegen;

class DefaultController extends Controller
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
            $newNo = str_pad($currentNo, $config->pad, '0', STR_PAD_LEFT);

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


    public function UpdateCode($type)
    {
        // Get the record
        $config = Codegen::where('type', $type)->first();

        if ($config) {
            // Increment the 'no' field by 1
            $config->increment('no');

            // Optional: return the new value
            return $config->no + 1; // because increment() updates directly in DB
        } else {
            // If no record exists, you can create one or return error
            return null;
        }
    }

    public $timestamps = false;

}
