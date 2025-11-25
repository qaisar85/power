<?php

use App\Models\Account;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

it('generates numeric segments and leaves with correct increments', function () {
    $root = Account::where('code', '11')->first();
    $seg1 = postJson('/api/v1/accounts/'.$root->id.'/segment', ['name' => 'Current Assets'])->assertCreated()->json();
    $seg2 = postJson('/api/v1/accounts/'.$root->id.'/segment', ['name' => 'Non-Current Assets'])->assertCreated()->json();
    expect($seg1['code'])->toBe('11-01');
    expect($seg2['code'])->toBe('11-02');

    $p2 = Account::where('code', '11-01')->first();
    $l3 = postJson('/api/v1/accounts/'.$p2->id.'/segment', ['name' => 'Cash'])->assertCreated()->json();
    expect($l3['code'])->toBe('11-01-01');

    $p4 = postJson('/api/v1/accounts/'.Account::where('code','11-01-01')->first()->id.'/segment', ['name' => 'On Hand'])->assertCreated()->json();
    expect($p4['code'])->toBe('11-01-01-01');

    $leaf1 = postJson('/api/v1/accounts/'.Account::where('code','11-01-01-01')->first()->id.'/leaf', ['name' => 'Main Cash'])->assertCreated()->json();
    $leaf2 = postJson('/api/v1/accounts/'.Account::where('code','11-01-01-01')->first()->id.'/leaf', ['name' => 'Petty Cash'])->assertCreated()->json();
    expect($leaf1['code'])->toBe('11-01-01-01-000001');
    expect($leaf2['code'])->toBe('11-01-01-01-000002');
});

it('generates alphabetical segments starting from letter base', function () {
    $root = Account::where('code', 'E')->first();
    $seg1 = postJson('/api/v1/accounts/'.$root->id.'/segment', ['name' => 'Operating'])->assertCreated()->json();
    expect($seg1['code'])->toBe('E-01');
});

