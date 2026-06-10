<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lookup;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request, string $group)
    {
        $parent = $request->input('parent');
        $options = Lookup::getOptions($group, $parent);

        return response()->json($options);
    }
}
