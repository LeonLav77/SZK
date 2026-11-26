<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EditionController extends Controller
{
    public function index()
    {
        return view('admin.editions.index', [
            // Active edition first, then newest.
            'editions' => Edition::withCount(['coupons', 'redemptions'])
                ->orderByRaw("status = 'active' desc")->latest('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.editions.form', [
            'edition' => new Edition,
            'editions' => Edition::latest('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['copy_from' => ['nullable', 'exists:editions,id']]);

        $edition = Edition::create($this->validated($request, new Edition));

        if ($request->filled('copy_from')) {
            $edition->copyCouponsFrom(Edition::find($request->copy_from));
        }

        return redirect()->route('admin.editions.show', $edition)->with('success', 'Izdanje je spremljeno.');
    }

    public function show(Edition $edition)
    {
        $edition->load(['coupons' => fn ($q) => $q->with(['titular', 'categories'])->withCount('redemptions')]);

        return view('admin.editions.show', [
            'edition' => $edition,
            'redemptionCount' => $edition->redemptions()->count(),
            'weekCount' => $edition->redemptions()->where('redemptions.created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    public function edit(Edition $edition)
    {
        return view('admin.editions.form', ['edition' => $edition]);
    }

    public function update(Request $request, Edition $edition)
    {
        $edition->update($this->validated($request, $edition));

        return redirect()->route('admin.editions.show', $edition)->with('success', 'Promjene su spremljene.');
    }

    public function destroy(Edition $edition)
    {
        if ($edition->isActive()) {
            return back()->with('error', 'Aktivno izdanje se ne može obrisati. Prvo aktiviraj neko drugo.');
        }

        $edition->delete();

        return redirect()->route('admin.editions.index')->with('success', 'Izdanje je obrisano.');
    }

    public function activate(Edition $edition)
    {
        $edition->activate();

        return back()->with('success', "Izdanje \"{$edition->name}\" je sada aktivno i vidljivo studentima.");
    }

    private function validated(Request $request, Edition $edition): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'cover' => ['nullable', 'image', 'max:20480'],
        ]);

        $data['pdf_path'] = Uploads::sync($request, 'pdf', 'pdfs', $edition->pdf_path);
        $data['cover_path'] = Uploads::sync($request, 'cover', 'covers', $edition->cover_path);

        return Arr::except($data, ['pdf', 'cover']);
    }
}
