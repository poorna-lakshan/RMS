<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertEmptyStringsToNull
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge(
            $this->transform($request->all())
        );

        return $next($request);
    }

    private function transform($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // recursively transform nested arrays
                $data[$key] = $this->transform($value);
            } elseif ($value === 'null') {
                // only convert string "null" → PHP null
                $data[$key] = null;
            } 
            // if $value === '', keep it as '' (do nothing)
        }
        return $data;
    }
}
