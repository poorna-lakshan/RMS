<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertEmptyStringsToNull
{
    public function handle($request, Closure $next)
    {
        $request->merge(array_map(function ($value) {
            if (is_array($value)) {
                // Recursively handle arrays (for warehouses, etc.)
                return array_map(fn($v) => ($v === '' || $v === 'null') ? null : $v, $value);
            }
            return ($value === '' || $value === 'null') ? null : $value;
        }, $request->all()));

        return $next($request);
    }
}

