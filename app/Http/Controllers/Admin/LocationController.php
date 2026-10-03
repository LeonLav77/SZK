<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Titular;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function store(Request $request, Titular $titular)
    {
        $titular->locations()->create($request->validate([
            'address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]));

        return back()->with('success', 'Lokacija je dodana.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return back()->with('success', 'Lokacija je obrisana.');
    }
}
