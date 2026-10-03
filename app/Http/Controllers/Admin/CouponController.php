<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Edition;
use App\Models\Titular;
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
        $edition->coupons()->create($this->validated($request));

        return redirect()->route('admin.editions.show', $edition)->with('success', 'Kupon je dodan.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', $this->formData($coupon->edition, $coupon));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request));

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
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'titular_id' => ['required', 'exists:titulars,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'discount' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'code' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'url', 'max:255'],
            'valid_until' => ['nullable', 'date'],
            'visual' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['requires_login'] = $request->boolean('requires_login');
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] ??= 0;

        if ($request->hasFile('visual')) {
            $data['visual_path'] = $request->file('visual')->store('visuals', 'public');
        } elseif ($request->boolean('remove_visual')) {
            $data['visual_path'] = null;
        }

        return Arr::except($data, ['visual']);
    }
}
