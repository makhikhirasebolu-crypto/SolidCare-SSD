<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\YearLeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    public function showCreateUser()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        $request->merge([
            'email' => $this->normalizeLoginIdentifier($request->input('email')),
        ]);

        $facultyLabels = collect(config('limkokwing.faculties', []))
            ->pluck('label')
            ->all();
        $programmeLabels = collect(config('limkokwing.faculties', []))
            ->flatMap(fn (array $faculty) => $faculty['programmes'] ?? [])
            ->all();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:executive,ssd_assistant_1,ssd_assistant_2,psychologist,senior_nurse_officer,warden,yearleader'],
            'faculty' => ['required_if:role,yearleader', 'nullable', 'string', 'max:255', Rule::in($facultyLabels)],
            'class' => ['required_if:role,yearleader', 'nullable', 'string', 'max:255', Rule::in($programmeLabels)],
            'year' => ['required_if:role,yearleader', 'nullable', 'string', 'max:50', Rule::in($this->yearLeaderYearOptionsForProgramme($request->input('class')))],
        ];

        $data = $request->validate($rules);
        $temporaryPassword = $data['password'];

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'role' => $data['role'],
            'password_temporary' => true,
            'temporary_password_expires_at' => Carbon::now()->addDays(2),
            'temporary_password_plain' => $temporaryPassword,
        ]);

        if ($data['role'] === 'yearleader') {
            YearLeader::create([
                'user_id' => $user->id,
                'faculty' => $data['faculty'],
                'class' => $data['class'],
                'year' => $data['year'],
            ]);
        }

        return redirect()->route('admin.users.create')->with('success', 'User created successfully. Temporary password: ' . $temporaryPassword . ' (expires in 2 days).');
    }

    protected function normalizeLoginIdentifier(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($this->isEmailIdentifier($value)) {
            return Str::lower($value);
        }

        return $value;
    }

    protected function isEmailIdentifier(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function yearLeaderYearOptionsForProgramme(mixed $programme): array
    {
        if (! is_string($programme) || trim($programme) === '') {
            return [];
        }

        $programme = Str::lower($programme);

        $yearCount = match (true) {
            str_contains($programme, 'certificate') => 2,
            str_contains($programme, 'diploma') || str_contains($programme, 'associate') => 3,
            default => 4,
        };

        return collect(range(1, $yearCount))
            ->map(fn (int $year) => (string) $year)
            ->all();
    }
}
