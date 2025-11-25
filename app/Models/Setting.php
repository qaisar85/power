<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public static function get(string $key, $default = null)
    {
        $s = static::where('key', $key)->first();
        if (! $s) {
            return $default;
        }
        $val = $s->value;
        return is_array($val) && array_key_exists('value', $val) ? $val['value'] : $val;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => ['value' => $value]]);
    }
}

