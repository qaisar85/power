<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function dropdown(Request $request)
    {
        $level = (int) $request->query('level');
        $parentCode = $request->query('parent_code');
        $query = Account::query();
        if ($level) $query->where('level', $level);
        if ($parentCode) {
            $parent = Account::where('code', $parentCode)->first();
            if ($parent) $query->where('parent_id', $parent->id);
        }
        return response()->json($query->orderBy('code')->get(['id','code','name','level']));
    }

    public function setRootIdType(Request $request)
    {
        $data = $request->validate([
            'root' => ['required','string','in:Asset,Liability,Capital,Revenue,Expense'],
            'id_type' => ['required','string','in:alphabetical,numeric'],
        ]);
        $roots = Account::where('level', 1)->where('root_category', $data['root'])->get();
        foreach ($roots as $r) {
            $r->id_type = $data['id_type'];
            $r->save();
        }
        return response()->json(['ok' => true]);
    }

    public function createSegment(Request $request, Account $parent)
    {
        if (! in_array((int) $parent->level, [1,2,3])) {
            return response()->json(['error' => 'Invalid parent level'], 422);
        }
        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);

        $code = AccountCodeGenerator::nextSegment($parent);

        return DB::transaction(function () use ($parent, $data, $code) {
            if (Account::where('code', $code)->exists()) {
                return response()->json(['error' => 'Duplicate code'], 422);
            }
            $acc = Account::create([
                'code' => $code,
                'name' => $data['name'],
                'parent_id' => $parent->id,
                'level' => $parent->level + 1,
                'id_type' => $parent->id_type,
                'root_category' => $parent->root_category,
            ]);
            return response()->json($acc, 201);
        });
    }

    public function createLeaf(Request $request, Account $parent)
    {
        if ((int) $parent->level !== 4) {
            return response()->json(['error' => 'Parent must be level 4'], 422);
        }
        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);
        $code = AccountCodeGenerator::nextSegment($parent);
        return DB::transaction(function () use ($parent, $data, $code) {
            if (Account::where('code', $code)->exists()) {
                return response()->json(['error' => 'Duplicate code'], 422);
            }
            $acc = Account::create([
                'code' => $code,
                'name' => $data['name'],
                'parent_id' => $parent->id,
                'level' => 5,
                'id_type' => $parent->id_type,
                'root_category' => $parent->root_category,
            ]);
            return response()->json($acc, 201);
        });
    }
}

