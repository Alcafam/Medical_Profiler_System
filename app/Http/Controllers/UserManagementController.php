<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('station:id,name')
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => UserRole::cases(),
            'stations' => Station::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedUser($request);

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'station_id' => $data['station_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
            'stations' => Station::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedUser($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->station_id = $data['station_id'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedUser(Request $request, ?User $user = null): array
    {
        $isEncoder = $request->input('role') === UserRole::Encoder->value;

        if (! $isEncoder) {
            $request->merge(['station_id' => null]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                $user
                    ? Rule::unique('users', 'email')->ignore($user->id)
                    : Rule::unique('users', 'email'),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'station_id' => [
                Rule::requiredIf($isEncoder),
                'nullable',
                'exists:stations,id',
            ],
            'is_active' => ['nullable', 'boolean'],
        ];

        return $request->validate($rules);
    }
}
