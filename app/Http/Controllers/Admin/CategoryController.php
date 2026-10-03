<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('coupons')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return back()->with('success', 'Kategorija je dodana.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request));

        return redirect()->route('admin.categories.index')->with('success', 'Kategorija je spremljena.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Kategorija je obrisana. Njeni kuponi su ostali, samo bez kategorije.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
