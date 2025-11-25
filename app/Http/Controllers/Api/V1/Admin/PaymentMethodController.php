<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    // List all payment methods (admin only)
    public function index(Request $request)
    {
        if (!$request->user() || ($request->user()->user_type ?? null) !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $methods = PaymentMethod::orderBy('sort_order')->get();
        return response()->json($methods);
    }

    // Update method properties (e.g., is_active)
    public function update(Request $request, $id)
    {
        if (!$request->user() || ($request->user()->user_type ?? null) !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->only(['is_active', 'name', 'config', 'sort_order']);
        // Cast is_active to boolean when present
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        // Prevent updating type (unique identifier)
        unset($data['type']);

        $method->update($data);

        return response()->json($method);
    }
}
