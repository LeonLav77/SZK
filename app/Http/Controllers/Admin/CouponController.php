<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Edition;
use App\Models\Titular;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CouponController extends Controller
{
    public function create(Edition $edition)
    {
        $coupon = new Coupon([
            'is_published' => true,
            'sort_order' => ($edition->coupons()->max('sort_order') ?? 0) + 10,
        ]);

        return view('admin.coupons.form', $this->formData($edition, $coupon));
    }

    public function store(Request $request, Edition $edition)
    {
        $data = $this->validated($request, new Coupon);
        $coupon = $edition->coupons()->create(Arr::except($data, 'categories'));
        $coupon->categories()->sync($data['categories']);

        return redirect()->route('admin.editions.show', $edition)->with('success', 'Kupon je dodan.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', $this->formData($coupon->edition, $coupon));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon);
        $coupon->update(Arr::except($data, 'categories'));
        $coupon->categories()->sync($data['categories']);

        return redirect()->route('admin.editions.show', $coupon->edition_id)->with('success', 'Kupon je spremljen.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.editions.show', $coupon->edition_id)->with('success', 'Kupon je obrisan.');
    }

    private function formData(Edition $edition, Coupon $coupon): array
    {
        return [
            'edition' => $edition,
            'coupon' => $coupon,
            'titulars' => Titular::orderBy('name')->get(),
            'categories' => Category::orderBy('sort_order')->get(),
            'checked' => collect(old('categories', $coupon->exists ? $coupon->categories->modelKeys() : []))->map(fn ($id) => (int) $id)->all(),
        ];
    }

    private function validated(Request $request, Coupon $coupon): array
    {
        $data = $request->validate([
            'titular_id' => ['required', 'exists:titulars,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'discount' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'code' => ['nullable', 'string', 'max:50'],
            // Mandatory, but an existing visual counts: editing only needs a file to replace it.
            'visual' => [$coupon->visual_path ? 'nullable' : 'required', 'image', 'max:20480'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        // is_published / requires_login aren't in the form for phase 1: the DB defaults
        // (published, visible to everyone) apply and existing values are left alone.
        $data['sort_order'] ??= 0;
        $data['categories'] ??= [];

        // Replace only, never remove: the visual is mandatory.
        $data['visual_path'] = $request->hasFile('visual')
            ? Uploads::store($request->file('visual'), 'visual', 'visuals')
            : $coupon->visual_path;

        return Arr::except($data, ['visual']);
    }
}
