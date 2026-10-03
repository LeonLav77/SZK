<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('redemptions')
            ->when($request->q, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Korisnik je spremljen.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Ne možeš obrisati sam sebe.');
        }

        $user->tokens()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Korisnik je obrisan.');
    }
}
