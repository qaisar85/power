<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Str;

class AccountCodeGenerator
{
    public static function nextSegment(Account $parent): string
    {
        $prefix = $parent->code;
        $level = (int) $parent->level + 1;
        if ($level >= 2 && $level <= 4) {
            $children = Account::where('parent_id', $parent->id)->where('level', $level)->pluck('code');
            $max = 0;
            foreach ($children as $code) {
                $last = Str::of($code)->explode('-')->last();
                $val = (int) $last;
                if ($val > $max) $max = $val;
            }
            $next = str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
            return $prefix.'-'.$next;
        }
        if ($level === 5) {
            $children = Account::where('parent_id', $parent->id)->where('level', $level)->pluck('code');
            $max = 0;
            foreach ($children as $code) {
                $last = Str::of($code)->explode('-')->last();
                $val = (int) $last;
                if ($val > $max) $max = $val;
            }
            $next = str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
            return $prefix.'-'.$next;
        }
        return $prefix;
    }
}

