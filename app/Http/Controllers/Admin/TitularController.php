<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Titular;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TitularController extends Controller
{
    public function index(Request $request)
    {
        $titulars = Titular::withCount(['locations', 'coupons'])
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('admin.titulars.index', ['titulars' => $titulars]);
    }

    public function create()
    {
        return view('admin.titulars.form', ['titular' => new Titular]);
    }

    public function store(Request $request)
    {
        $titular = Titular::create($this->validated($request));

        return redirect()->route('admin.titulars.edit', $titular)
            ->with('success', 'Titular je spremljen. Sada mu dodaj lokacije (dolje).');
    }

    public function edit(Titular $titular)
    {
        return view('admin.titulars.form', ['titular' => $titular->load('locations')]);
    }

    public function update(Request $request, Titular $titular)
    {
        $titular->update($this->validated($request));

        return redirect()->route('admin.titulars.edit', $titular)->with('success', 'Promjene su spremljene.');
    }

    public function destroy(Titular $titular)
    {
        if ($titular->coupons()->exists()) {
            return back()->with('error', 'Titular ima kupone. Prvo obriši njegove kupone.');
        }

        $titular->delete();

        return redirect()->route('admin.titulars.index')->with('success', 'Titular je obrisan.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'website' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        return Arr::except($data, ['logo']);
    }
}
