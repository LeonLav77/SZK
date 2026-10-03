<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['settings' => Setting::values()]);
    }

    public function update(Request $request)
    {
        Setting::store($request->validate([
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_instagram' => ['nullable', 'string', 'max:255'],
            'contact_link' => ['nullable', 'url', 'max:255'],
            'countdown_seconds' => ['required', 'integer', 'between:10,600'],
        ]));

        return back()->with('success', 'Postavke su spremljene.');
    }
}
