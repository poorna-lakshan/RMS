<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreserveEmptyStrings
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge(array_map(function ($value) {
            if (is_array($value)) {
                // Handle arrays too
                return array_map(fn($v) => ($v === 'null' ? null : $v), $value);
            }

            // Convert only the string "null" → null
            return $value === 'null' ? null : $value;

            // NOTE: "" will remain ""
        }, $request->all()));

        return $next($request);
    }
}
