<?php

namespace App\Http\Controllers;

use App\Models\DrillingCompany;
use App\Models\DrillingRig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DrillingRigController extends Controller
{
    public function index(Request $request)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();
        $rigs = DrillingRig::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'capacity', 'year', 'region', 'photos', 'status']);

        return Inertia::render('ServiceDashboard/Drilling/Rigs/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'rigs' => $rigs,
        ]);
    }

    public function store(Request $request)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'region' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'passports.*' => ['nullable', 'file', 'mimes:pdf'],
        ]);

        $rig = new DrillingRig();
        $rig->company_id = $company->id;
        $rig->name = $data['name'];
        $rig->type = $data['type'] ?? null;
        $rig->capacity = $data['capacity'] ?? null;
        $rig->year = $data['year'] ?? null;
        $rig->region = $data['region'] ?? null;
        $rig->description = $data['description'] ?? null;
        $rig->photos = [];
        $rig->passports = [];
        $rig->save();

        // Handle file uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('drilling/rigs/' . $rig->id . '/photos', 'public');
                $photoPaths[] = $path;
            }
        }
        $passportPaths = [];
        if ($request->hasFile('passports')) {
            foreach ($request->file('passports') as $doc) {
                $path = $doc->store('drilling/rigs/' . $rig->id . '/passports', 'public');
                $passportPaths[] = $path;
            }
        }
        $rig->photos = $photoPaths;
        $rig->passports = $passportPaths;
        $rig->save();

        return redirect()->route('service.drilling.rigs.index')->with('success', 'Rig added');
    }

    public function destroy(Request $request, DrillingRig $rig)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();
        if ($rig->company_id !== $company->id) {
            abort(403);
        }
        // Delete files
        foreach (($rig->photos ?? []) as $p) {
            Storage::disk('public')->delete($p);
        }
        foreach (($rig->passports ?? []) as $p) {
            Storage::disk('public')->delete($p);
        }
        // Delete directories (best-effort)
        Storage::disk('public')->deleteDirectory('drilling/rigs/' . $rig->id);
        $rig->delete();

        return redirect()->route('service.drilling.rigs.index')->with('success', 'Rig removed');
    }
}