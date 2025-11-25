<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\BusinessSector;
use App\Models\BusinessSubsector;

class BusinessSubsectorSeeder extends Seeder
{
    public function run(): void
    {
        // Populate subsectors for each level-1 sector using its level-2 children
        $sectors = BusinessSector::query()->where('level', 1)->orderBy('sort_order')->get();

        foreach ($sectors as $sector) {
            $children = $sector->children()->where('level', 2)->orderBy('sort_order')->get();
            foreach ($children as $child) {
                $slug = $child->slug ?: Str::slug($child->code ?: $child->name);

                BusinessSubsector::updateOrCreate(
                    ['sector_id' => $sector->id, 'slug' => $slug],
                    [
                        'name' => $child->name,
                        'description' => $child->description,
                        'standard' => $child->standard,
                        'code' => $child->code,
                        'is_active' => $child->is_active ?? true,
                        'sort_order' => $child->sort_order ?? 0,
                    ]
                );
            }
        }
    }
}