<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EditionController extends Controller
{
    public function index()
    {
        return view('admin.editions.index', [
            'editions' => Edition::withCount('coupons')->latest('id')->get(),
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

        $edition = Edition::create($this->validated($request));

        if ($request->filled('copy_from')) {
            $edition->copyCouponsFrom(Edition::find($request->copy_from));
        }

        return redirect()->route('admin.editions.show', $edition)->with('success', 'Izdanje je spremljeno.');
    }

    public function show(Edition $edition)
    {
        $edition->load(['coupons' => fn ($q) => $q->with(['titular', 'category'])->withCount('redemptions')]);

        return view('admin.editions.show', ['edition' => $edition]);
    }

    public function edit(Edition $edition)
    {
        return view('admin.editions.form', ['edition' => $edition]);
    }

    public function update(Request $request, Edition $edition)
    {
        $edition->update($this->validated($request));

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

    /** CSV of uses per coupon (semicolons + BOM so Excel opens it correctly). */
    public function export(Edition $edition)
    {
        $coupons = $edition->coupons()->with(['titular', 'category'])->withCount('redemptions')->get();

        return response()->streamDownload(function () use ($coupons) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Titular', 'Kupon', 'Popust', 'Kategorija', 'Korištenja'], ';');
            foreach ($coupons as $coupon) {
                fputcsv($out, [
                    $coupon->titular->name,
                    $coupon->title,
                    $coupon->discount,
                    $coupon->category?->name,
                    $coupon->redemptions_count,
                ], ';');
            }
            fclose($out);
        }, 'izdanje-'.$edition->id.'-statistika.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $request->file('pdf')->store('pdfs', 'public');
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        return Arr::except($data, ['pdf', 'cover']);
    }
}
