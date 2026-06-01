<?php

namespace App\Http\Controllers;

use App\Counselling\Models\EmergencyCounsellingModel;
use App\Counselling\Support\DatasetEmergencyCounsellingResponder;
use App\Mail\AccommodationStatusUpdated;
use App\Mail\CounsellingSessionScheduled;
use App\Mail\CheckoutApproved;
use App\Mail\CheckoutRejected;
use App\Mail\CheckoutRequestSubmitted;
use App\Models\AccommodationApplication;
use App\Models\AccommodationMessage;
use App\Models\AccommodationPayment;
use App\Models\AccommodationRoom;
use App\Models\Admin;
use App\Models\ClinicStockComment;
use App\Models\ClinicStockItem;
use App\Models\ClinicStockReceipt;
use App\Models\ClinicStockUsage;
use App\Models\CounsellingBooking;
use App\Models\ReferralComment;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use App\Models\YearLeader;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() || Auth::guard('admin')->check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->merge([
            'email' => $this->normalizeLoginIdentifier($request->input('email')),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $webCredentials = $this->credentialsForWebLogin($credentials['email'], $credentials['password']);
        $suggestedEmail = $this->isEmailIdentifier($credentials['email'])
            ? $this->suggestEmailCorrection($credentials['email'])
            : null;

        if ($this->attemptGuardLogin('web', $webCredentials)) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();
            return $this->redirectWebUserAfterLogin($user);
        }

        if ($suggestedEmail && $this->attemptGuardLogin('web', [
            'email' => $suggestedEmail,
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();

            return $this->redirectWebUserAfterLogin($user)
                ->with('status', 'Signed in using the corrected email address.');
        }

        if ($this->isEmailIdentifier($credentials['email']) && $this->attemptGuardLogin('admin', [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        if ($suggestedEmail && $this->attemptGuardLogin('admin', [
            'email' => $suggestedEmail,
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('status', 'Signed in using the corrected email address.');
        }

        $response = back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => __('The provided credentials do not match our records.'),
            ]);

        if ($suggestedEmail) {
            $response->with('login_email_suggestion', $suggestedEmail);
        }

        return $response;
    }

    protected function attemptGuardLogin(string $guard, array $credentials): bool
    {
        try {
            return Auth::guard($guard)->attempt($credentials);
        } catch (\RuntimeException $e) {
            if ($this->repairPlainPassword($guard, $credentials)) {
                return Auth::guard($guard)->attempt($credentials);
            }
        }

        return false;
    }

    protected function repairPlainPassword(string $guard, array $credentials): bool
    {
        $password = $credentials['password'] ?? null;

        if (! is_string($password) || $password === '') {
            return false;
        }

        $query = $guard === 'admin' ? Admin::query() : User::query();

        foreach ($credentials as $field => $value) {
            if ($field === 'password') {
                continue;
            }

            $query->where($field, $value);
        }

        /** @var Admin|User|null $user */
        $user = $query->first();

        if (! $user) {
            return false;
        }

        if ($user->getAuthPassword() === $password) {
            $user->password = Hash::make($password);
            $user->save();

            return true;
        }

        return false;
    }

    public function showRegister()
    {
        if (Auth::check() || Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => $this->normalizeLoginIdentifier($request->input('email')),
            'student_id' => $this->normalizeStudentId($request->input('student_id')),
            'id_number' => $this->normalizeNationalId($request->input('id_number')),
        ]);

        if ($this->isAdminRegistrationEmail($request->input('email'))) {
            return $this->registerAdminFromStudentPortal($request);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->emailAlreadyRegistered($value)) {
                        $fail('This email address is already registered.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'student_type' => ['required', 'in:continuing,new'],
            'student_id' => [
                'required_if:student_type,continuing',
                'nullable',
                'string',
                'regex:/^901\d{6}$/',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->studentIdAlreadyRegistered($value)) {
                        $fail('This student number is already registered.');
                    }
                },
            ],
            'id_number' => [
                'required_if:student_type,new',
                'nullable',
                'string',
                'regex:/^\d{13}$/',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->nationalIdAlreadyRegistered($value)) {
                        $fail('This national ID is already registered.');
                    }
                },
            ],
            'disability' => ['required', 'in:yes,no'],
            'disability_details' => ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules, [
            'student_id.regex' => 'Student number must start with 901 and be exactly 9 digits.',
            'id_number.regex' => 'National ID must contain exactly 13 digits.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'student_type' => $data['student_type'],
            'student_id' => $data['student_id'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'disability' => $data['disability'],
            'disability_details' => $data['disability_details'] ?? null,
            'password_temporary' => false,
            'temporary_password_expires_at' => null,
        ]);

        Student::create([
            'user_id' => $user->id,
            'student_type' => $data['student_type'],
            'student_id' => $data['student_id'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'disability' => $data['disability'],
            'disability_details' => $data['disability_details'] ?? null,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return view('auth.verification-link', [
            'user' => $user,
            'verificationUrl' => $this->studentVerificationUrl($user),
            'status' => 'Account created successfully. Click the button below to verify your account.',
        ]);
    }

    public function showEmailVerificationNotice()
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verification-link', [
            'user' => $user,
            'verificationUrl' => $this->studentVerificationUrl($user),
            'status' => session('status'),
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));
        $hash = (string) $request->route('hash');

        if (! $user || ! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            $user = $this->findStudentByVerificationHash($hash);
        }

        if (! $user) {
            Log::warning('Email verification link could not be matched to a student.', [
                'route_user_id' => $request->route('id'),
                'route_hash' => $hash,
            ]);

            $target = Auth::guard('web')->check()
                ? route('verification.notice')
                : route('login');

            return redirect($target)
                ->with('error', 'This verification link is no longer valid. Please open the verification page to generate a fresh link.');
        }

        if ($user->hasVerifiedEmail()) {
            Auth::guard('admin')->logout();
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('status', 'Your email address is already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Auth::guard('admin')->logout();
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Email verified successfully. Welcome to SolidCare SSD.');
    }

    protected function findStudentByVerificationHash(string $hash): ?User
    {
        $matches = User::query()
            ->where('role', 'student')
            ->whereNull('email_verified_at')
            ->whereNotNull('email')
            ->get(['id', 'email', 'email_verified_at', 'role'])
            ->filter(fn (User $user) => hash_equals($hash, sha1($user->getEmailForVerification())));

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public function resendEmailVerification(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verification-link', [
            'user' => $user,
            'verificationUrl' => $this->studentVerificationUrl($user),
            'status' => 'A fresh verification link is ready. Click the button below to verify your account.',
        ]);
    }

    protected function registerAdminFromStudentPortal(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Admin::count() >= 2) {
                        $fail('The system already has the maximum number of admin accounts.');
                    }

                    if (! $this->isAdminRegistrationEmail($value)) {
                        $fail('Admin email must use the name.surname@limkokwing.ac.ls format.');
                    }

                    if ($this->emailAlreadyRegistered($value)) {
                        $fail('This email address is already registered.');
                    }

                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('status', 'Admin account created successfully. Welcome to SolidCare SSD.');
    }

    public function showCreateUser()
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        return view('admin.users.create');
    }

    public function storeAdminUser(Request $request)
    {
        if (! Auth::guard('admin')->check()) {
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->emailAlreadyRegistered($value)) {
                        $fail('This email address already exists in the system.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:executive,ssd_assistant_1,ssd_assistant_2,psychologist,senior_nurse_officer,warden,yearleader'],
            'faculty' => ['required_if:role,yearleader', 'nullable', 'string', 'max:255', Rule::in($facultyLabels)],
            'class' => ['required_if:role,yearleader', 'nullable', 'string', 'max:255', Rule::in($programmeLabels)],
            'year' => ['required_if:role,yearleader', 'nullable', 'string', 'max:50', Rule::in($this->yearLeaderYearOptionsForProgramme($request->input('class')))],
        ]);

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

        return redirect()
            ->route('dashboard', ['create_user' => 1])
            ->with('success', 'User created successfully. Temporary password: ' . $temporaryPassword . ' (expires in 2 days).');
    }

    public function deleteAdminUser(User $user)
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        $memberName = $user->name;
        $isStudent = $user->role === 'student';
        $user->delete();

        return redirect()
            ->route('dashboard', ['members_report' => 1])
            ->with('success', $memberName . ($isStudent
                ? ' has been deleted from the student system.'
                : ' has been removed and can no longer access the system.'));
    }

    public function deleteSystemAdmin(Admin $admin)
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        if (Auth::guard('admin')->id() === $admin->id) {
            return redirect()
                ->route('dashboard', ['members_report' => 1])
                ->with('error', 'You cannot delete the admin account you are currently using.');
        }

        $adminName = $admin->name;
        $admin->delete();

        return redirect()
            ->route('dashboard', ['members_report' => 1])
            ->with('success', $adminName . ' has been removed and can no longer access the system.');
    }

    public function reissueTemporaryPassword(User $user)
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        if ($user->role === 'student') {
            return redirect()
                ->route('dashboard', ['members_report' => 1])
                ->with('error', 'Temporary passwords can only be reissued for staff members.');
        }

        if (! $user->password_temporary || ! $user->temporary_password_expires_at || $user->temporary_password_expires_at->isPast()) {
            return redirect()
                ->route('dashboard', ['members_report' => 1])
                ->with('error', 'This member does not have an active temporary password to reissue.');
        }

        $temporaryPassword = 'Temp-' . Str::random(10);

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
            'password_temporary' => true,
            'temporary_password_expires_at' => Carbon::now()->addDays(2),
            'temporary_password_plain' => $temporaryPassword,
        ])->save();

        return redirect()
            ->route('dashboard', ['members_report' => 1])
            ->with('success', 'Temporary password reissued for ' . $user->name . ': ' . $temporaryPassword . ' (expires in 2 days).');
    }

    public function showTemporaryPasswordForm()
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->password_temporary) {
            return redirect()->route('home');
        }

        return view('auth.password-temp');
    }

    public function updateTemporaryPassword(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->password_temporary) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->password_temporary = false;
        $user->temporary_password_expires_at = null;
        $user->temporary_password_plain = null;
        $user->save();

        return redirect()->route('home')->with('status', 'Password updated successfully.');
    }

    protected function redirectWebUserAfterLogin(User $user)
    {
        if ($user->password_temporary && $user->temporary_password_expires_at && now()->greaterThan($user->temporary_password_expires_at)) {
            return redirect()->route('password.temporary');
        }

        if ($user->role === 'student' && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('home'));
    }

    protected function studentVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
            false
        );
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

    protected function credentialsForWebLogin(string $identifier, string $password): array
    {
        if ($this->isEmailIdentifier($identifier)) {
            return [
                'email' => $identifier,
                'password' => $password,
            ];
        }

        return [
            'student_id' => $identifier,
            'password' => $password,
        ];
    }

    protected function isEmailIdentifier(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function suggestEmailCorrection(string $email): ?string
    {
        if (! str_contains($email, '@')) {
            return null;
        }

        [$localPart, $domain] = explode('@', $email, 2);

        $suggestedDomain = [
            'gamil.com' => 'gmail.com',
            'gmail.con' => 'gmail.com',
            'gmial.com' => 'gmail.com',
            'gnail.com' => 'gmail.com',
            'hotnail.com' => 'hotmail.com',
            'outlok.com' => 'outlook.com',
            'outllok.com' => 'outlook.com',
            'yaho.com' => 'yahoo.com',
            'yhoo.com' => 'yahoo.com',
        ][Str::lower($domain)] ?? null;

        if (! $suggestedDomain) {
            return null;
        }

        return $localPart . '@' . $suggestedDomain;
    }

    public function dashboard(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $showCreateUserForm = $request->boolean('create_user') || $request->session()->has('errors');
            $showMembersReport = $request->boolean('members_report');
            $members = collect();

            if ($showMembersReport) {
                $systemUsers = User::query()
                    ->latest()
                    ->get([
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'role',
                        'student_type',
                        'student_id',
                        'id_number',
                        'password_temporary',
                        'temporary_password_expires_at',
                        'temporary_password_plain',
                        'created_at',
                    ])
                    ->each(function (User $user) {
                        $user->setAttribute('is_system_admin', false);
                        $user->setAttribute('is_student_account', $user->role === 'student');
                    });

                $adminMembers = Admin::query()
                    ->latest()
                    ->get(['id', 'name', 'email', 'created_at'])
                    ->each(function (Admin $admin) {
                        $admin->setAttribute('role', 'admin');
                        $admin->setAttribute('password_temporary', false);
                        $admin->setAttribute('temporary_password_expires_at', null);
                        $admin->setAttribute('temporary_password_plain', null);
                        $admin->setAttribute('is_system_admin', true);
                        $admin->setAttribute('is_student_account', false);
                        $admin->setAttribute('email_verified_at', $admin->email_verified_at);
                        $admin->setAttribute('student_type', null);
                        $admin->setAttribute('student_id', null);
                        $admin->setAttribute('id_number', null);
                    });

                $members = $systemUsers
                    ->concat($adminMembers)
                    ->sortByDesc('created_at')
                    ->values();
            }

            return view('admin.dashboard', compact('showCreateUserForm', 'showMembersReport', 'members'));
        }

        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role === 'student' && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return view('dashboard');
    }

    public function clinic(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canAccessClinic($user)) {
            return redirect()->route('home')->with('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
        }

        return $this->renderClinicPage($user, $request);
    }

    public function studentClinic()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canAccessClinic($user)) {
            return redirect()->route('home')->with('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
        }

        return redirect()->route('clinic');
    }

    public function storeStudentClinic(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canAccessClinic($user)) {
            return redirect()->route('home')->with('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
        }

        return redirect()->route('clinic');
    }

    public function counselling(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($this->canManageCounselling($user)) {
            [
                'reportType' => $reportType,
                'reportYear' => $reportYear,
                'reportMonth' => $reportMonth,
                'reportSemester' => $reportSemester,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reportLabel' => $reportLabel,
            ] = $this->reportFiltersFromRequest($request);

            $bookings = CounsellingBooking::with('user')
                ->latest()
                ->get();

            $reportBookings = $reportType === 'general'
                ? $bookings
                : $this->counsellingReportQuery($reportType, $startDate, $endDate)
                    ->with('user')
                    ->latest()
                    ->get();

            return view('counselling.psychologist', compact(
                'user',
                'bookings',
                'reportBookings',
                'reportType',
                'reportYear',
                'reportMonth',
                'reportSemester',
                'reportLabel'
            ));
        }

        if (! $this->canAccessStudentCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only continuing students can access counselling services.');
        }

        $emergencyCounsellingModel = EmergencyCounsellingModel::fromConfig();
        $datasetResponder = app(DatasetEmergencyCounsellingResponder::class);
        $apiKey = config('services.openai.api_key');
        $emergencyChatMeta = is_string($apiKey) && trim($apiKey) !== ''
            ? [
                'model' => $emergencyCounsellingModel->identifier(),
                'model_label' => $emergencyCounsellingModel->label(),
                'model_provider' => $emergencyCounsellingModel->provider(),
                'model_description' => $emergencyCounsellingModel->description(),
            ]
            : $datasetResponder->meta();
        $bookings = CounsellingBooking::where('user_id', $user->id)
            ->latest()
            ->get();
        $unavailableSessionSlots = $this->unavailableCounsellingSessionSlots();

        return view('counselling.student', compact('user', 'bookings', 'emergencyChatMeta', 'unavailableSessionSlots'));
    }

    public function storeCounsellingBooking(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canAccessStudentCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only continuing students can access counselling services.');
        }

        $counsellingReasonOptions = [
            'Grief and loss',
            'Sexual assault',
            'Mental illness',
            'Relationships',
            'Family dynamics',
            'Depression and anxiety',
            'Academic issue',
        ];

        $data = $request->validate([
            'campus' => ['required', Rule::in(['MP campus', 'Main campus'])],
            'sex' => ['required', 'string', 'max:50'],
            'reason' => ['required', Rule::in($counsellingReasonOptions)],
            'programme' => ['required', 'string', 'max:255'],
            'year_of_study' => ['required', 'string', 'max:50'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:16:30'],
        ]);

        $preferredSessionStart = Carbon::parse($data['preferred_date'] . ' ' . $data['preferred_time'])->seconds(0);
        $preferredSessionEnd = $preferredSessionStart->copy()->addMinutes(75);
        $workdayStart = $preferredSessionStart->copy()->setTime(8, 0);
        $workdayEnd = $preferredSessionStart->copy()->setTime(16, 30);

        if ($preferredSessionStart->isPast()) {
            return back()
                ->withErrors([
                    'preferred_time' => 'Choose an available future counselling session time.',
                ])
                ->withInput();
        }

        if ($preferredSessionEnd->gt($workdayEnd)) {
            return back()
                ->withErrors([
                    'preferred_time' => 'Choose a counselling session that ends before Limkokwing closes at 16:30.',
                ])
                ->withInput();
        }

        if (! in_array($preferredSessionStart->format('H:i'), $this->counsellingAppointmentSlotStarts(), true) || $preferredSessionStart->lt($workdayStart)) {
            return back()
                ->withErrors([
                    'preferred_time' => 'Choose one of the available counselling session slots.',
                ])
                ->withInput();
        }

        if ($this->counsellingBookingSlotConflicts($preferredSessionStart, $data['campus'])) {
            return back()
                ->withErrors([
                    'preferred_time' => 'That counselling session time is already booked. Choose another available slot.',
                ])
                ->withInput();
        }

        CounsellingBooking::create([
            'user_id' => $user->id,
            'student_name' => $user->name,
            'student_identity_number' => $user->student_id ?: $user->id_number,
            'campus' => $data['campus'],
            'sex' => $data['sex'],
            'reason' => $data['reason'],
            'programme' => $data['programme'],
            'year_of_study' => $data['year_of_study'],
            'preferred_date' => $data['preferred_date'],
            'preferred_time' => $data['preferred_time'],
            'status' => 'pending',
        ]);

        return redirect()->route('counselling')->with('success', 'Counselling session booked successfully.');
    }

    public function updateCounsellingBooking(Request $request, CounsellingBooking $booking)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canManageCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only counselling managers can manage counselling appointments.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in($this->counsellingBookingStatuses())],
            'appointment_date' => ['nullable', 'date', 'after_or_equal:today'],
            'counsellor_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointmentDate = $data['appointment_date'] ?? null;
        $resolvedAppointmentDate = blank($appointmentDate)
            ? $booking->appointment_date
            : Carbon::parse($appointmentDate)->seconds(0);
        $resolvedStatus = $data['status'];

        if ($resolvedAppointmentDate && $resolvedStatus === 'pending') {
            $resolvedStatus = 'scheduled';
        }

        if ($resolvedAppointmentDate) {
            $slotStart = $resolvedAppointmentDate->copy();
            $slotEnd = $slotStart->copy()->addMinutes(75);
            $workdayStart = $slotStart->copy()->setTime(8, 0);
            $workdayEnd = $slotStart->copy()->setTime(16, 30);

            if ($slotStart->isPast()) {
                return back()
                    ->withErrors([
                        'appointment_date' => 'The appointment date field must be a date after or equal to today.',
                    ])
                    ->withInput();
            }

            $validSlotStarts = $this->counsellingAppointmentSlotStarts();

            if (! in_array($slotStart->format('H:i'), $validSlotStarts, true) || $slotStart->lt($workdayStart) || $slotEnd->gt($workdayEnd)) {
                return back()
                    ->withErrors([
                        'appointment_date' => 'Choose one of the available appointment slots. Each session lasts 1 hour and 15 minutes.',
                    ])
                    ->withInput();
            }
        }

        if ($resolvedStatus === 'cancelled') {
            $resolvedAppointmentDate = null;
        }

        if (in_array($resolvedStatus, ['scheduled', 'attended'], true) && ! $resolvedAppointmentDate) {
            return back()
                ->withErrors([
                    'appointment_date' => 'Set an appointment date and time before marking this booking as scheduled or attended.',
                ])
                ->withInput();
        }

        if ($resolvedAppointmentDate) {
            $hasConflict = $this->counsellingBookingSlotConflicts($resolvedAppointmentDate, $booking->campus, $booking->id);

            if ($hasConflict) {
                return back()
                    ->withErrors([
                        'appointment_date' => 'That appointment session overlaps with another booking. Choose another slot.',
                    ])
                    ->withInput();
            }
        }

        $booking->update([
            'status' => $resolvedStatus,
            'appointment_date' => $resolvedAppointmentDate,
            'counsellor_notes' => $data['counsellor_notes'] ?? null,
        ]);

        if ($resolvedStatus === 'scheduled' && ($booking->wasChanged('status') || $booking->wasChanged('appointment_date'))) {
            $this->notifyStudentOfScheduledCounsellingSession($booking->fresh('user'));
        }

        return redirect()->route('counselling')->with('success', 'Counselling appointment updated successfully.');
    }

    public function emergencyCounsellingReply(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return response()->json([
                'message' => 'Please sign in to continue.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canAccessStudentCounselling($user)) {
            return response()->json([
                'message' => 'Only continuing students can access counselling services.',
            ], 403);
        }

        $emergencyCounsellingModel = EmergencyCounsellingModel::fromConfig();
        $datasetResponder = app(DatasetEmergencyCounsellingResponder::class);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'messages' => ['nullable', 'array', 'max:10'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.content' => ['required_with:messages', 'string', 'max:2000'],
        ]);

        $history = collect($data['messages'] ?? [])
            ->take(-8)
            ->map(function (array $message) {
                return [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ];
            })
            ->values()
            ->all();

        $apiKey = config('services.openai.api_key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        $payload = $emergencyCounsellingModel->buildPayload($data['message'], $history);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', $payload)
                ->throw()
                ->json();
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        $reply = $this->extractEmergencyCounsellingReply((array) $response);
        if (! $reply) {
            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        return response()->json([
            'reply' => $reply,
            'model' => $payload['model'],
            'model_label' => $emergencyCounsellingModel->label(),
            'model_provider' => $emergencyCounsellingModel->provider(),
            'model_description' => $emergencyCounsellingModel->description(),
        ]);
    }

    public function storeClinicStock(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'stock_entries' => ['required', 'array', 'min:1'],
            'stock_entries.*.medicine_name' => ['required', 'string', 'max:255'],
            'stock_entries.*.quantity_received' => ['required', 'integer', 'min:0'],
            'stock_entries.*.expiry_date' => ['required', 'date', 'after_or_equal:today'],
            'stock_entries.*.dosage_form' => ['nullable', 'string', 'max:255'],
            'stock_entries.*.important_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['stock_entries'] as $entry) {
            $existingItem = ClinicStockItem::whereRaw('LOWER(medicine_name) = ?', [strtolower($entry['medicine_name'])])->first();
            $receivedQuantity = (int) $entry['quantity_received'];
            $expiryDate = Carbon::parse($entry['expiry_date'])->toDateString();
            $dosageForm = $this->normalizeOptionalText($entry['dosage_form'] ?? null);
            $importantNotes = $this->normalizeOptionalText($entry['important_notes'] ?? null);

            if ($existingItem) {
                $openingStock = $existingItem->opening_stock;
                $quantityReceived = $existingItem->quantity_received + $receivedQuantity;
                $quantityIssued = $existingItem->quantity_issued;

                $existingItem->update([
                    'opening_stock' => $openingStock,
                    'quantity_received' => $quantityReceived,
                    'quantity_issued' => $quantityIssued,
                    'expiry_date' => $this->resolveClinicStockItemExpiryDate($existingItem, $expiryDate),
                    'dosage_form' => $dosageForm ?? $existingItem->dosage_form,
                    'important_notes' => $importantNotes ?? $existingItem->important_notes,
                    'confirmed_at' => null,
                    'confirmed_by_user_id' => null,
                    'status' => $this->resolveClinicStockStatus(
                        $openingStock + $quantityReceived - $quantityIssued,
                        $openingStock + $quantityReceived
                    ),
                ]);

                $this->recordClinicStockReceipt($existingItem, $user, $receivedQuantity, $expiryDate, $dosageForm, $importantNotes);
                continue;
            }

            $entry['status'] = $this->resolveClinicStockStatus(
                $entry['quantity_received'],
                $entry['quantity_received']
            );
            $entry['opening_stock'] = 0;
            $entry['quantity_issued'] = 0;
            $entry['expiry_date'] = $expiryDate;
            $entry['dosage_form'] = $dosageForm;
            $entry['important_notes'] = $importantNotes;

            $createdItem = ClinicStockItem::create($entry);
            $this->recordClinicStockReceipt($createdItem, $user, $receivedQuantity, $expiryDate, $dosageForm, $importantNotes);
        }

        return $this->redirectToClinicPanel($request, 'add-stock')
            ->with('success', count($data['stock_entries']) . ' stock item(s) added successfully.');
    }

    public function accommodation(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canAccessAccommodation($user)) {
            return redirect()
                ->route('home')
                ->with('error', 'Accommodation access is restricted to students and authorized accommodation staff.');
        }

        $application = AccommodationApplication::with(['room', 'requestedRoom', 'previousRoom', 'admissionProcessedBy', 'reallocationApprovedBy', 'roomReallocatedBy'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if ($user->role === 'executive') {
            $this->ensureDefaultAccommodationRoomsExist();

            $pendingAdmissionsCount = AccommodationApplication::where('status', 'pending')->count();
            $checkoutApprovalsCount = AccommodationApplication::where('status', 'checkout_requested')->count();
            $availableRoomsCount = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->get()
                ->filter(function ($room) {
                    return $room->occupied_beds < $room->capacity;
                })
                ->count();

            return view('accommodation.executive', compact(
                'user',
                'pendingAdmissionsCount',
                'checkoutApprovalsCount',
                'availableRoomsCount'
            ));
        }

        if ($this->canManageAccommodationRooms($user)) {
            $this->ensureDefaultAccommodationRoomsExist();

            $admittedApplications = AccommodationApplication::with(['user', 'room', 'previousRoom', 'admissionProcessedBy', 'reallocationApprovedBy', 'roomReallocatedBy', 'paymentConfirmedBy'])
                ->whereIn('status', $this->occupyingAccommodationStatuses())
                ->leftJoin('accommodation_rooms', 'accommodation_applications.accommodation_room_id', '=', 'accommodation_rooms.id')
                ->select('accommodation_applications.*')
                ->orderBy('accommodation_rooms.block_name')
                ->orderBy('accommodation_rooms.room_number')
                ->orderBy('accommodation_applications.full_name')
                ->orderBy('accommodation_applications.id')
                ->get();

            $reallocationRequests = AccommodationApplication::with(['user', 'room', 'requestedRoom', 'admissionProcessedBy'])
                ->where('reallocation_status', 'pending')
                ->latest('reallocation_requested_at')
                ->latest('id')
                ->get();

            $rooms = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->orderBy('block_name')
                ->orderBy('room_number')
                ->get()
                ->groupBy('block_name');

            $availableRooms = $this->availableAccommodationRooms();
            $accommodationMessages = Schema::hasTable('accommodation_messages')
                ? AccommodationMessage::with(['user', 'replies.user'])
                    ->whereNull('parent_id')
                    ->latest()
                    ->limit(25)
                    ->get()
                    ->reverse()
                    ->values()
                : collect();

            $paymentReportApplication = $this->findAccommodationPaymentReportApplication(
                $user,
                $request->query('payment_full_name')
            );
            $paymentReportPayment = $paymentReportApplication?->latestPayment;
            $confirmedPaymentReports = $this->confirmedAccommodationPaymentReports()->get();
            $monthlyRentAmount = $this->accommodationMonthlyRentAmount();
            $monthlyRentPaymentMethodLabel = $this->accommodationMonthlyRentPaymentMethodLabel();

            return view('accommodation.warden', compact('user', 'admittedApplications', 'rooms', 'availableRooms', 'reallocationRequests', 'accommodationMessages', 'paymentReportApplication', 'paymentReportPayment', 'confirmedPaymentReports', 'monthlyRentAmount', 'monthlyRentPaymentMethodLabel'));
        }

        $availableRooms = $application
            && in_array($application->status, $this->reallocationEligibleStatuses(), true)
            ? $this->availableAccommodationRooms($application->id)
                ->reject(fn (AccommodationRoom $room) => (int) $room->id === (int) $application->accommodation_room_id)
                ->values()
            : collect();

        return view('accommodation.student', compact('user', 'application', 'availableRooms'));
    }

    public function pendingAdmissions()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canManageAccommodationAdmissions($user)) {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $applications = AccommodationApplication::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $decidedApplications = AccommodationApplication::with(['user', 'room', 'admissionProcessedBy'])
            ->whereIn('status', ['admitted', 'conditional', 'rejected'])
            ->latest('updated_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $availableRooms = AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get()
            ->filter(function ($room) {
                return $room->occupied_beds < $room->capacity;
            })
            ->values();

        return view('accommodation.pending-admissions', compact('user', 'applications', 'availableRooms', 'decidedApplications'));
    }

    public function roomManagement()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canViewAccommodationRooms($user)) {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $rooms = AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get()
            ->groupBy('block_name');

        return view('accommodation.rooms', compact('user', 'rooms'));
    }

    public function accommodationReport(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canGenerateAccommodationReport($user)) {
            return redirect()->route('accommodation')->with('error', 'Only executive, warden, and SSD Assistant 2 can view accommodation reports.');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        return view('accommodation.report', array_merge(
            ['user' => $user],
            $this->buildAccommodationReportData(null, $request->query('year'))
        ));
    }

    public function downloadAccommodationReport(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canGenerateAccommodationReport($user)) {
            return redirect()->route('accommodation')->with('error', 'Only executive, warden, and SSD Assistant 2 can download accommodation reports.');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $reportData = $this->buildAccommodationReportData(null, $request->query('year'));
        $currentIntake = $reportData['currentIntake'];
        $selectedYear = $reportData['selectedYear'];
        $availableIntakes = $reportData['availableIntakes'];

        if (! $selectedYear) {
            return redirect()
                ->route('accommodation.report')
                ->with('error', 'No yearly accommodation report is available yet.');
        }

        $applications = $reportData['applications'];
        $pendingApplications = $reportData['pendingApplications'];
        $checkoutRequests = $reportData['checkoutRequests'];
        $statusSummary = $reportData['statusSummary'];
        $rooms = $reportData['rooms'];
        $availableRoomsCount = $reportData['availableRoomsCount'];
        $occupiedRoomsCount = $reportData['occupiedRoomsCount'];
        $occupiedBedsCount = $reportData['occupiedBedsCount'];
        $availableBedsCount = $reportData['availableBedsCount'];
        $totalCapacity = $reportData['totalCapacity'];

        $fileName = 'accommodation-report-' . $selectedYear . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use (
            $applications,
            $availableIntakes,
            $currentIntake,
            $selectedYear,
            $availableBedsCount,
            $availableRoomsCount,
            $checkoutRequests,
            $occupiedBedsCount,
            $occupiedRoomsCount,
            $pendingApplications,
            $rooms,
            $statusSummary,
            $totalCapacity,
            $user
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Accommodation Management Report']);
            fputcsv($handle, ['Selected Year', $selectedYear]);
            fputcsv($handle, ['Latest Intake In Year', $currentIntake ?? 'Not available']);
            fputcsv($handle, ['Intakes In Scope', $availableIntakes->implode(', ') ?: 'Not available']);
            fputcsv($handle, ['Generated By', $user->name ?: 'Executive']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Dashboard Summary']);
            fputcsv($handle, ['Pending Admissions', $pendingApplications->count()]);
            fputcsv($handle, ['Checkout Approvals', $checkoutRequests->count()]);
            fputcsv($handle, ['Available Rooms', $availableRoomsCount]);
            fputcsv($handle, ['Occupied Rooms', $occupiedRoomsCount]);
            fputcsv($handle, ['Occupied Beds', $occupiedBedsCount]);
            fputcsv($handle, ['Available Beds', $availableBedsCount]);
            fputcsv($handle, ['Total Room Capacity', $totalCapacity]);
            fputcsv($handle, ['Total Applications', $applications->count()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Application Status Breakdown']);
            fputcsv($handle, ['Status', 'Count']);
            if ($statusSummary->isEmpty()) {
                fputcsv($handle, ['No accommodation applications found.', '']);
            } else {
                foreach ($statusSummary as $status => $count) {
                    fputcsv($handle, [$status, $count]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Selected Year Applications']);
            fputcsv($handle, ['Student Name', 'Student ID', 'Faculty', 'Programme', 'Status', 'Current Room', 'Admission Done By', 'Previous Room', 'Requested Room', 'Reallocation Status', 'Accepted By', 'Reallocated By', 'Reallocation Reason', 'Check-In Date', 'Updated']);
            if ($applications->isEmpty()) {
                fputcsv($handle, ['No accommodation applications found for this year.', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
            } else {
                foreach ($applications as $application) {
                    fputcsv($handle, [
                        $application->full_name,
                        $application->student_id ?: optional($application->user)->student_id ?: '',
                        $application->faculty,
                        $application->programme,
                        Str::headline($application->status),
                        $this->formatAccommodationRoomLabel($application->room) ?? 'Not assigned',
                        $this->formatAccommodationUserLabel($application->admissionProcessedBy),
                        $this->formatAccommodationRoomLabel($application->previousRoom) ?? 'Not recorded',
                        $this->formatAccommodationRoomLabel($application->requestedRoom) ?? 'Not selected',
                        $application->reallocation_status ? Str::headline($application->reallocation_status) : 'No reallocation',
                        $this->formatAccommodationUserLabel($application->reallocationApprovedBy),
                        $this->formatAccommodationUserLabel($application->roomReallocatedBy),
                        $application->reallocation_reason ?: 'Not provided',
                        optional($application->check_in_date)->format('Y-m-d') ?? '',
                        optional($application->updated_at)->format('Y-m-d H:i:s') ?? optional($application->created_at)->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Pending Admissions']);
            fputcsv($handle, ['Student Name', 'Student ID', 'Application Email', 'Account Email', 'Faculty', 'Programme', 'Check-In Date', 'Submitted']);
            if ($pendingApplications->isEmpty()) {
                fputcsv($handle, ['No pending admissions.', '', '', '', '', '', '', '']);
            } else {
                foreach ($pendingApplications as $application) {
                    fputcsv($handle, [
                        $application->full_name,
                        $application->student_id ?: optional($application->user)->student_id ?: '',
                        $application->email,
                        optional($application->user)->email ?? '',
                        $application->faculty,
                        $application->programme,
                        optional($application->check_in_date)->format('Y-m-d') ?? '',
                        optional($application->created_at)->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Checkout Requests']);
            fputcsv($handle, ['Student Name', 'Student ID', 'Room', 'Checkout Date', 'Requested At', 'Reason']);
            if ($checkoutRequests->isEmpty()) {
                fputcsv($handle, ['No checkout requests awaiting approval.', '', '', '', '', '']);
            } else {
                foreach ($checkoutRequests as $application) {
                    fputcsv($handle, [
                        $application->full_name,
                        $application->student_id ?: optional($application->user)->student_id ?: '',
                        $this->formatAccommodationRoomLabel($application->room) ?? 'Not assigned',
                        optional($application->checkout_date)->format('Y-m-d') ?? '',
                        optional($application->checkout_requested_at)->format('Y-m-d H:i:s')
                            ?? optional($application->updated_at)->format('Y-m-d H:i:s')
                            ?? '',
                        $application->checkout_reason ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Room Availability']);
            fputcsv($handle, ['Block', 'Room Number', 'Capacity', 'Occupied Beds', 'Available Beds', 'Occupancy Status']);
            foreach ($rooms as $room) {
                $availableBeds = max(0, $room->capacity - $room->occupied_beds);

                fputcsv($handle, [
                    $room->block_name,
                    str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT),
                    $room->capacity,
                    $room->occupied_beds,
                    $availableBeds,
                    $availableBeds > 0 ? 'Available' : 'Full',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function storeRoom(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $data = $request->validate([
            'block_name' => ['required', 'string', 'max:20'],
            'room_number' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $blockName = strtoupper(trim($data['block_name']));

        AccommodationRoom::firstOrCreate(
            [
                'block_name' => $blockName,
                'room_number' => $data['room_number'],
            ],
            [
                'capacity' => 4,
            ]
        );

        return redirect()->route('student.accommodation.rooms')->with('success', 'Room created successfully in block ' . $blockName . '.');
    }

    public function seedDefaultRooms()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        return redirect()->route('student.accommodation.rooms')->with('success', 'Default room blocks AG and AF have been created with 15 rooms each.');
    }

    public function accommodationPaymentReport()
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canConfirmAccommodationPayments($user)) {
            return redirect()
                ->route('accommodation')
                ->with('error', 'Only SSD Assistant 2 can confirm accommodation payments.');
        }

        $applications = $this->admittedAccommodationPaymentApplications()
            ->latest('payment_confirmed_at')
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $confirmedPaymentsCount = $this->confirmedAccommodationPaymentReports()->count();
        $confirmedPaymentReports = $this->confirmedAccommodationPaymentReports()->get();

        $unpaidRentReports = $this->unpaidAccommodationRentReports($applications);
        $pendingPaymentsCount = $unpaidRentReports->count();
        $monthlyRentAmount = $this->accommodationMonthlyRentAmount();
        $monthlyRentPaymentMethodLabel = $this->accommodationMonthlyRentPaymentMethodLabel();

        return view('accommodation.payment-report', compact(
            'user',
            'applications',
            'confirmedPaymentReports',
            'confirmedPaymentsCount',
            'pendingPaymentsCount',
            'unpaidRentReports',
            'monthlyRentAmount',
            'monthlyRentPaymentMethodLabel'
        ));
    }

    public function confirmAccommodationPayment(Request $request, AccommodationApplication $application)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canConfirmAccommodationPayments($user)) {
            return redirect()
                ->route('accommodation')
                ->with('error', 'Only SSD Assistant 2 can confirm accommodation payments.');
        }

        $application = $this->admittedAccommodationPaymentApplications()
            ->where('accommodation_applications.id', $application->id)
            ->first();

        if (! $application) {
            return redirect()
                ->route('accommodation.payment-report')
                ->with('error', 'Only admitted accommodation students can be confirmed from this report.');
        }

        $data = $request->validate([
            'payment_receipt_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('accommodation_payments', 'receipt_number'),
                Rule::unique('accommodation_applications', 'payment_receipt_number')->ignore($application->id),
            ],
            'payment_month' => ['required', 'date_format:Y-m'],
        ]);

        $paymentMonth = $this->formatAccommodationPaymentMonth($data['payment_month']);

        if ($this->accommodationPaymentExistsForMonth($application, $paymentMonth)) {
            return redirect()
                ->route('accommodation.payment-report')
                ->withInput($request->only('payment_receipt_number', 'payment_month'))
                ->with('error', 'A payment for ' . Carbon::parse($paymentMonth)->format('F Y') . ' has already been recorded for ' . $application->full_name . '.');
        }

        $this->recordAccommodationMonthlyPayment(
            $application,
            trim($data['payment_receipt_number']),
            $paymentMonth,
            $user
        );

        return redirect()
            ->route('accommodation.payment-report')
            ->with('success', 'Payment receipt confirmed for ' . $application->full_name . '.');
    }

    public function confirmAccommodationPaymentByFullName(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canConfirmAccommodationPayments($user)) {
            return redirect()
                ->route('accommodation')
                ->with('error', 'Only SSD Assistant 2 can confirm accommodation payments.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'payment_receipt_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('accommodation_payments', 'receipt_number'),
                Rule::unique('accommodation_applications', 'payment_receipt_number'),
            ],
            'payment_month' => ['required', 'date_format:Y-m'],
        ]);

        $fullName = trim($data['full_name']);
        $application = $this->admittedAccommodationApplicationForFullName($fullName);

        if (! $application) {
            return redirect()
                ->route('accommodation')
                ->withInput($request->only('full_name'))
                ->with('error', 'No admitted accommodation student was found for Full Name ' . $fullName . '.');
        }

        $paymentMonth = $this->formatAccommodationPaymentMonth($data['payment_month']);

        if ($this->accommodationPaymentExistsForMonth($application, $paymentMonth)) {
            return redirect()
                ->route('accommodation')
                ->withInput($request->only('full_name', 'payment_receipt_number', 'payment_month'))
                ->with('error', 'A payment for ' . Carbon::parse($paymentMonth)->format('F Y') . ' has already been recorded for ' . $application->full_name . '.');
        }

        $this->recordAccommodationMonthlyPayment(
            $application,
            trim($data['payment_receipt_number']),
            $paymentMonth,
            $user
        );

        return redirect()
            ->route('accommodation', ['payment_full_name' => $fullName])
            ->with('success', 'Payment receipt confirmed for ' . $application->full_name . '.');
    }

    protected function ensureDefaultAccommodationRoomsExist(): void
    {
        if (AccommodationRoom::exists()) {
            return;
        }

        foreach (['AG', 'AF'] as $blockName) {
            for ($roomNumber = 1; $roomNumber <= 15; $roomNumber++) {
                AccommodationRoom::firstOrCreate(
                    [
                        'block_name' => $blockName,
                        'room_number' => $roomNumber,
                    ],
                    [
                        'capacity' => 4,
                    ]
                );
            }
        }
    }

    public function updateAdmissionStatus(Request $request, AccommodationApplication $application)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $data = $request->validate([
            'status' => ['required', 'in:admitted,rejected,conditional'],
            'accommodation_room_id' => ['nullable', 'exists:accommodation_rooms,id'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $roomId = $data['accommodation_room_id'] ?? null;

        if ($data['status'] === 'admitted') {
            if (! $roomId) {
                return redirect()
                    ->route('student.accommodation.pending')
                    ->withErrors(['accommodation_room_id' => 'Please select a room before approving the student.'])
                    ->withInput();
            }

            $room = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->findOrFail($roomId);

            if ($room->occupied_beds >= $room->capacity) {
                return redirect()
                    ->route('student.accommodation.pending')
                    ->withErrors(['accommodation_room_id' => 'The selected room is already full. Please choose another room.'])
                    ->withInput();
            }
        }

        if ($data['status'] === 'rejected' && blank($data['rejection_reason'] ?? null)) {
            return redirect()
                ->route('student.accommodation.pending')
                ->withErrors(['rejection_reason' => 'Please provide a reason when rejecting the application.'])
                ->withInput();
        }

        $updatePayload = [
            'status' => $data['status'],
            'accommodation_room_id' => $data['status'] === 'admitted' ? $roomId : null,
        ];

        if (Schema::hasColumn('accommodation_applications', 'admission_processed_by_user_id')) {
            $updatePayload['admission_processed_by_user_id'] = $user->id;
        }

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $updatePayload['rejection_reason'] = $data['status'] === 'rejected'
                ? trim((string) ($data['rejection_reason'] ?? ''))
                : null;
        }

        $application->update($updatePayload);

        $emailStatus = $this->sendAccommodationStatusEmail($application);

        return redirect()
            ->route('student.accommodation.pending')
            ->with('success', 'Application status updated to ' . ucfirst($data['status']) . '.')
            ->with('accommodation_email_status', $emailStatus['message']);
    }

    public function resendAccommodationStatusEmail(AccommodationApplication $application)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        if (! in_array($application->status, ['admitted', 'conditional', 'rejected'], true)) {
            return redirect()
                ->route('student.accommodation.pending')
                ->with('error', 'Only admitted, conditional, or rejected applications can have status emails resent.');
        }

        $emailStatus = $this->sendAccommodationStatusEmail($application);

        return redirect()
            ->route('student.accommodation.pending')
            ->with($emailStatus['success'] ? 'success' : 'error', $emailStatus['success']
                ? 'Accommodation status email resent.'
                : 'Accommodation status email could not be resent.')
            ->with('accommodation_email_status', $emailStatus['message']);
    }

    public function applyAccommodation()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            return redirect()->route('accommodation');
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit accommodation applications.');
        }

        $existing = $this->findExistingAccommodationApplication(
            $user,
            $this->normalizeNationalId($user->id_number)
        );

        if ($existing) {
            return redirect()
                ->route('accommodation')
                ->with('error', $this->duplicateAccommodationApplicationMessage($existing, $user));
        }

        return view('accommodation.apply', compact('user'));
    }

    public function checkoutAccommodation()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            $applicationsQuery = AccommodationApplication::with(['user', 'room'])
                ->where('status', 'checkout_requested');

            if (Schema::hasColumn('accommodation_applications', 'checkout_requested_at')) {
                $applicationsQuery->orderByDesc('checkout_requested_at');
            } else {
                $applicationsQuery->latest('updated_at');
            }

            $applications = $applicationsQuery
                ->latest('id')
                ->get();

            return view('accommodation.checkout-approvals', compact('user', 'applications'));
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit checkout requests.');
        }

        $application = AccommodationApplication::where('user_id', $user->id)
            ->whereIn('status', $this->checkoutEligibleStatuses())
            ->latest()
            ->first();

        if (! $application) {
            return redirect()->route('accommodation')->with('error', 'Checkout is available only after your accommodation has been admitted.');
        }

        return view('accommodation.checkout', compact('user', 'application'));
    }

    public function storeAccommodation(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            return redirect()->route('accommodation');
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit accommodation applications.');
        }

        $request->merge([
            'email' => is_string($request->input('email'))
                ? Str::lower(trim($request->input('email')))
                : $request->input('email'),
            'national_id' => $this->normalizeNationalId($request->input('national_id')),
        ]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'national_id' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'marital_status' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:100'],
            'nationality_other' => ['nullable', 'string', 'max:100', 'required_if:nationality,Other'],
            'gender' => ['required', 'in:female'],
            'age' => ['required', 'integer', 'min:16', 'max:120'],
            'faculty' => ['required', 'string', 'max:100'],
            'programme' => ['required', 'string', 'max:255'],
            'intake' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'string', 'max:100'],
            'check_in_date' => ['required', 'date'],
            'district' => ['required', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255', 'required_unless:district,Other'],
            'foreign_country' => ['nullable', 'string', 'max:255', 'required_if:district,Other'],
            'foreign_physical_address' => ['nullable', 'string', 'max:1000', 'required_if:district,Other'],
            'next_of_kin_name' => ['required', 'string', 'max:255'],
            'next_of_kin_relationship' => ['required', 'string', 'max:100'],
            'next_of_kin_contact' => ['required', 'string', 'max:100'],
            'special_conditions_remark' => ['nullable', 'string', 'max:2000'],
            'has_physical_disability' => ['required', 'boolean'],
            'physical_disability_details' => ['nullable', 'string', 'max:1000', 'required_if:has_physical_disability,1'],
            'has_high_blood_pressure' => ['required', 'boolean'],
            'has_diabetes' => ['required', 'boolean'],
            'has_asthma' => ['required', 'boolean'],
            'chronic_illness_other' => ['nullable', 'string', 'max:1000'],
            'on_chronic_treatment' => ['required', 'boolean'],
            'treatment_frequency' => ['nullable', 'string', 'max:1000', 'required_if:on_chronic_treatment,1'],
        ]);

        foreach ([
            'has_physical_disability',
            'has_high_blood_pressure',
            'has_diabetes',
            'has_asthma',
            'on_chronic_treatment',
        ] as $booleanField) {
            $data[$booleanField] = $request->boolean($booleanField);
        }

        if ($data['nationality'] === 'Other') {
            $data['nationality'] = $data['nationality_other'];
        }

        if (! $data['has_physical_disability']) {
            $data['physical_disability_details'] = null;
        }

        if (! $data['on_chronic_treatment']) {
            $data['treatment_frequency'] = null;
        }

        $address = $data['district'] === 'Other'
            ? trim((string) ($data['foreign_physical_address'] ?? ''))
            : trim((string) ($data['village'] ?? ''));

        $existing = $this->findExistingAccommodationApplication($user, $data['national_id']);

        if ($existing) {
            return redirect()
                ->route('accommodation')
                ->with('error', $this->duplicateAccommodationApplicationMessage($existing, $user));
        }

        $applicationPayload = [
            'user_id' => $user->id,
            'full_name' => $data['full_name'],
            'student_id' => $user->student_id,
            'contact_number' => $data['contact_number'],
            'national_id' => $data['national_id'],
            'email' => $data['email'],
            'marital_status' => $data['marital_status'],
            'nationality' => $data['nationality'],
            'gender' => $data['gender'],
            'age' => $data['age'],
            'faculty' => $data['faculty'],
            'programme' => $data['programme'],
            'intake' => $data['intake'],
            'semester' => $data['semester'],
            'check_in_date' => $data['check_in_date'],
            'address' => $address,
            'status' => 'pending',
        ];

        foreach ([
            'student_id',
            'gender',
            'intake',
            'semester',
            'district',
            'village',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_contact',
            'special_conditions_remark',
            'has_physical_disability',
            'physical_disability_details',
            'has_high_blood_pressure',
            'has_diabetes',
            'has_asthma',
            'chronic_illness_other',
            'on_chronic_treatment',
            'treatment_frequency',
        ] as $column) {
            if (Schema::hasColumn('accommodation_applications', $column)) {
                $applicationPayload[$column] = $data[$column] ?? null;
            }
        }

        AccommodationApplication::create($applicationPayload);

        return redirect()->route('accommodation')->with('success', 'Accommodation application submitted successfully.');
    }

    public function storeCheckout(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit checkout requests.');
        }

        $application = AccommodationApplication::where('user_id', $user->id)
            ->whereIn('status', $this->checkoutEligibleStatuses())
            ->latest()
            ->first();

        if (! $application) {
            return redirect()->route('accommodation')->with('error', 'Checkout is available only after your accommodation has been admitted.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:100'],
            'checkout_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $checkoutPayload = [
            'status' => 'checkout_requested',
        ];

        if (Schema::hasColumn('accommodation_applications', 'checkout_date')) {
            $checkoutPayload['checkout_date'] = $data['checkout_date'];
        }

        if (Schema::hasColumn('accommodation_applications', 'checkout_reason')) {
            $checkoutPayload['checkout_reason'] = $data['reason'];
        }

        if (Schema::hasColumn('accommodation_applications', 'checkout_requested_at')) {
            $checkoutPayload['checkout_requested_at'] = now();
        }

        if (Schema::hasColumn('accommodation_applications', 'checked_out_at')) {
            $checkoutPayload['checked_out_at'] = null;
        }

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $checkoutPayload['rejection_reason'] = null;
        }

        $application->update($checkoutPayload);

        $checkoutEmail = $application->email ?: optional($application->user)->email;

        if ($checkoutEmail) {
            try {
                Mail::to($checkoutEmail)->send(new CheckoutRequestSubmitted($application));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('accommodation')->with('success', 'Checkout request submitted successfully.');
    }

    public function updateCheckoutStatus(Request $request, AccommodationApplication $application)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        if ($application->status !== 'checkout_requested') {
            return redirect()
                ->route('student.accommodation.checkout')
                ->with('error', 'Only pending checkout requests can be processed.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'rejected' && blank($data['rejection_reason'] ?? null)) {
            return redirect()
                ->route('student.accommodation.checkout')
                ->withErrors(['rejection_reason' => 'Please provide a reason when rejecting the checkout request.'])
                ->withInput();
        }

        $application->loadMissing(['user', 'room']);
        $previousRoomLabel = $this->formatAccommodationRoomLabel($application->room);

        $approvalPayload = [
            'status' => $data['decision'] === 'approved' ? 'checked_out' : 'checkout_rejected',
        ];

        if ($data['decision'] === 'approved') {
            $approvalPayload['accommodation_room_id'] = null;
        }

        if (Schema::hasColumn('accommodation_applications', 'checked_out_at')) {
            $approvalPayload['checked_out_at'] = $data['decision'] === 'approved' ? now() : null;
        }

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $approvalPayload['rejection_reason'] = $data['decision'] === 'rejected'
                ? trim((string) ($data['rejection_reason'] ?? ''))
                : null;
        }

        $application->update($approvalPayload);

        $checkoutApprovalEmail = $application->email ?: optional($application->user)->email;

        if ($checkoutApprovalEmail) {
            try {
                $application->refresh()->loadMissing(['user', 'room']);

                $mailable = $data['decision'] === 'approved'
                    ? new CheckoutApproved($application, $previousRoomLabel)
                    : new CheckoutRejected($application);

                Mail::to($checkoutApprovalEmail)->send($mailable);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('student.accommodation.checkout')
            ->with(
                'success',
                $data['decision'] === 'approved'
                    ? 'Checkout approved successfully and the allocated room has been released.'
                    : 'Checkout request rejected successfully.'
            );
    }

    public function storeRoomReallocationRequest(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can request room reallocation.');
        }

        $application = AccommodationApplication::with('room')
            ->where('user_id', $user->id)
            ->whereIn('status', $this->reallocationEligibleStatuses())
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (! $application || ! $application->accommodation_room_id) {
            return redirect()->route('accommodation')->with('error', 'Room reallocation is available only after you have an allocated room.');
        }

        if ($application->reallocation_status === 'pending') {
            return redirect()->route('accommodation')->with('error', 'You already have a room reallocation request awaiting warden approval.');
        }

        $data = $request->validate([
            'requested_accommodation_room_id' => ['required', 'exists:accommodation_rooms,id'],
            'reallocation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $requestedRoomId = (int) $data['requested_accommodation_room_id'];

        if ($requestedRoomId === (int) $application->accommodation_room_id) {
            return redirect()
                ->route('accommodation')
                ->withErrors(['requested_accommodation_room_id' => 'Please choose a different room for reallocation.'])
                ->withInput();
        }

        $room = $this->availableAccommodationRooms($application->id)
            ->firstWhere('id', $requestedRoomId);

        if (! $room) {
            return redirect()
                ->route('accommodation')
                ->withErrors(['requested_accommodation_room_id' => 'The selected room is already full. Please choose another room.'])
                ->withInput();
        }

        $application->update([
            'requested_accommodation_room_id' => $requestedRoomId,
            'previous_accommodation_room_id' => null,
            'reallocation_status' => 'pending',
            'reallocation_reason' => trim((string) ($data['reallocation_reason'] ?? '')) ?: null,
            'reallocation_requested_at' => now(),
            'reallocation_decided_at' => null,
            'reallocation_approved_by_user_id' => null,
            'room_reallocated_by_user_id' => null,
        ]);

        return redirect()->route('accommodation')->with('success', 'Room reallocation request submitted to the warden.');
    }

    public function reallocateRoom(Request $request, AccommodationApplication $application)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canManageAccommodationRooms($user)) {
            return redirect()->route('accommodation');
        }

        if (! in_array($application->status, $this->reallocationEligibleStatuses(), true)) {
            return redirect()->route('accommodation')->with('error', 'Only currently admitted students can be reallocated.');
        }

        $data = $request->validate([
            'accommodation_room_id' => ['required', 'exists:accommodation_rooms,id'],
            'reallocation_reason' => ['required', 'string', 'max:1000'],
        ]);

        $roomId = (int) $data['accommodation_room_id'];

        if ($roomId === (int) $application->accommodation_room_id) {
            return redirect()->route('accommodation')->with('error', 'The student is already allocated to that room.');
        }

        $room = $this->availableAccommodationRooms($application->id)
            ->firstWhere('id', $roomId);

        if (! $room) {
            return redirect()
                ->route('accommodation')
                ->withErrors(['accommodation_room_id' => 'The selected room is already full. Please choose another room.'])
                ->withInput();
        }

        $application->update([
            'accommodation_room_id' => $roomId,
            'previous_accommodation_room_id' => $application->accommodation_room_id,
            'requested_accommodation_room_id' => null,
            'reallocation_status' => 'direct',
            'reallocation_reason' => trim((string) $data['reallocation_reason']),
            'reallocation_requested_at' => null,
            'reallocation_decided_at' => now(),
            'reallocation_approved_by_user_id' => null,
            'room_reallocated_by_user_id' => $user->id,
        ]);

        return redirect()->route('accommodation')->with('success', 'Student room reallocated successfully.');
    }

    public function deleteAccommodationApplication(AccommodationApplication $application)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canManageAccommodationRooms($user)) {
            return redirect()->route('accommodation');
        }

        if ($application->status !== 'admitted') {
            return redirect()->route('accommodation')->with('error', 'Only admitted students can be deleted from this table.');
        }

        $studentName = $application->full_name;
        $studentUser = $application->user;

        if ($studentUser && $studentUser->role === 'student') {
            $studentUser->delete();
        } else {
            $application->delete();
        }

        return redirect()->route('accommodation')->with('success', $studentName . ' deleted from admitted students.');
    }

    public function storeAccommodationMessage(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canUseAccommodationCommunication($user)) {
            return redirect()->route('accommodation')->with('error', 'Only the warden and SSD Assistant 2 can use accommodation communication.');
        }

        if (! Schema::hasTable('accommodation_messages')) {
            return redirect()
                ->route('accommodation')
                ->with('error', 'Accommodation communication is not ready yet. Please run the latest database migrations.');
        }

        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:accommodation_messages,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        AccommodationMessage::create([
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'message' => $data['message'],
        ]);

        return redirect()
            ->route('accommodation')
            ->with('success', ($data['parent_id'] ?? null) ? 'Accommodation reply sent successfully.' : 'Accommodation message sent successfully.');
    }

    public function updateRoomReallocationStatus(Request $request, AccommodationApplication $application)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (! $this->canManageAccommodationRooms($user)) {
            return redirect()->route('accommodation');
        }

        if ($application->reallocation_status !== 'pending' || ! $application->requested_accommodation_room_id) {
            return redirect()->route('accommodation')->with('error', 'Only pending room reallocation requests can be processed.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $payload = [
            'reallocation_status' => $data['decision'],
            'reallocation_decided_at' => now(),
        ];

        if ($data['decision'] === 'approved') {
            $room = $this->availableAccommodationRooms($application->id)
                ->firstWhere('id', (int) $application->requested_accommodation_room_id);

            if (! $room) {
                return redirect()
                    ->route('accommodation')
                    ->withErrors(['requested_accommodation_room_id' => 'The requested room is now full. Reject this request or reallocate the student to another available room.'])
                    ->withInput();
            }

            $payload['accommodation_room_id'] = $application->requested_accommodation_room_id;
            $payload['previous_accommodation_room_id'] = $application->accommodation_room_id;
            $payload['requested_accommodation_room_id'] = null;
            $payload['reallocation_requested_at'] = null;
            $payload['reallocation_approved_by_user_id'] = $user->id;
            $payload['room_reallocated_by_user_id'] = $user->id;
        }

        $application->update($payload);

        return redirect()
            ->route('accommodation')
            ->with('success', $data['decision'] === 'approved'
                ? 'Room reallocation request approved successfully.'
                : 'Room reallocation request rejected successfully.');
    }

    public function updateClinicStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'opening_stock' => ['required', 'integer', 'min:0'],
            'quantity_received' => ['required', 'integer', 'min:0'],
            'quantity_issued' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date', 'after_or_equal:today'],
            'dosage_form' => ['nullable', 'string', 'max:255'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:pending_review,in_stock,low_stock,out_of_stock'],
        ]);

        $previousQuantityReceived = $item->quantity_received;
        $balance = $data['opening_stock'] + $data['quantity_received'] - $data['quantity_issued'];
        $dosageForm = $this->normalizeOptionalText($data['dosage_form'] ?? null);
        $importantNotes = $this->normalizeOptionalText($data['important_notes'] ?? null);

        $item->update([
            'opening_stock' => $data['opening_stock'],
            'quantity_received' => $data['quantity_received'],
            'quantity_issued' => $data['quantity_issued'],
            'expiry_date' => Carbon::parse($data['expiry_date'])->toDateString(),
            'dosage_form' => $dosageForm,
            'important_notes' => $importantNotes,
            'confirmed_at' => $data['quantity_received'] > 0 ? null : $item->confirmed_at,
            'confirmed_by_user_id' => $data['quantity_received'] > 0 ? null : $item->confirmed_by_user_id,
            'status' => $this->resolveClinicStockStatus($balance, $data['opening_stock'] + $data['quantity_received']),
        ]);

        $newlyRecordedReceived = $data['quantity_received'] - $previousQuantityReceived;
        if ($newlyRecordedReceived > 0) {
            $this->recordClinicStockReceipt($item->fresh(), $user, $newlyRecordedReceived, Carbon::parse($data['expiry_date'])->toDateString(), $dosageForm, $importantNotes);
        }

        return $this->redirectToClinicPanel($request, 'stock-details')
            ->with('success', $item->medicine_name . ' stock updated successfully.');
    }

    public function confirmClinicStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('home');
        }

        $newOpeningStock = $item->opening_stock + $item->quantity_received;

        $item->update([
            'opening_stock' => $newOpeningStock,
            'quantity_received' => 0,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
            'status' => $this->resolveClinicStockStatus($newOpeningStock - $item->quantity_issued, $newOpeningStock),
        ]);

        return $this->redirectToClinicPanel($request, 'stock-details')
            ->with('success', $item->medicine_name . ' stock confirmed successfully.');
    }

    public function deleteClinicStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! in_array($user->role, ['senior_nurse_officer', 'executive'], true)) {
            return redirect()->route('home');
        }

        $medicineName = $item->medicine_name;
        $item->delete();

        return $this->redirectToClinicPanel($request, 'stock-details')
            ->with('success', $medicineName . ' has been deleted from stock.');
    }

    public function commentClinicStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! in_array($user->role, ['senior_nurse_officer', 'executive'], true)) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:clinic_stock_comments,id'],
        ]);

        ClinicStockComment::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'message' => $data['message'],
        ]);

        return $this->redirectToClinicPanel($request, 'stock-details')
            ->with('success', 'Comment saved for ' . $item->medicine_name . '.');
    }

    public function storeClinicStockUsage(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'clinic_stock_item_id' => ['required', 'exists:clinic_stock_items,id'],
            'student_id' => ['required', 'string', 'max:100'],
            'quantity_issued' => ['required', 'integer', 'min:1'],
            'diagnosis' => ['required', 'string', 'max:2000'],
        ]);

        $item = ClinicStockItem::findOrFail($data['clinic_stock_item_id']);
        $nextIssued = $item->quantity_issued + $data['quantity_issued'];

        if ($nextIssued > ($item->opening_stock + $item->quantity_received)) {
            return $this->redirectToClinicPanel($request, 'stock-usage')
                ->withErrors([
                    'quantity_issued' => 'Issued quantity cannot exceed available stock for ' . $item->medicine_name . '.',
                ])
                ->withInput();
        }

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'student_id' => $data['student_id'],
            'quantity_issued' => $data['quantity_issued'],
            'diagnosis' => $data['diagnosis'],
            'usage_date' => now()->toDateString(),
        ]);

        $this->syncClinicStockItemIssued($item, $nextIssued);

        return $this->redirectToClinicPanel($request, 'stock-usage')
            ->with('success', 'Stock usage recorded for ' . $item->medicine_name . '.');
    }

    public function updateClinicStockUsage(Request $request, ClinicStockUsage $usage)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'clinic_stock_item_id' => ['required', 'exists:clinic_stock_items,id'],
            'student_id' => ['required', 'string', 'max:100'],
            'quantity_issued' => ['required', 'integer', 'min:1'],
            'diagnosis' => ['required', 'string', 'max:2000'],
        ]);

        $oldItem = ClinicStockItem::findOrFail($usage->clinic_stock_item_id);
        $newItem = ClinicStockItem::findOrFail($data['clinic_stock_item_id']);

        if ($oldItem->id === $newItem->id) {
            $nextIssued = max(0, $oldItem->quantity_issued - $usage->quantity_issued + $data['quantity_issued']);

            if ($nextIssued > ($oldItem->opening_stock + $oldItem->quantity_received)) {
                return $this->redirectToClinicPanel($request, 'stock-usage')
                    ->withErrors([
                        'quantity_issued' => 'Updated issued quantity cannot exceed available stock for ' . $oldItem->medicine_name . '.',
                    ])
                    ->withInput();
            }

            $usage->update([
                'student_id' => $data['student_id'],
                'quantity_issued' => $data['quantity_issued'],
                'diagnosis' => $data['diagnosis'],
            ]);

            $this->syncClinicStockItemIssued($oldItem, $nextIssued);

            return $this->redirectToClinicPanel($request, 'stock-usage')
                ->with('success', 'Stock usage updated successfully.');
        }

        $oldItemNextIssued = max(0, $oldItem->quantity_issued - $usage->quantity_issued);
        $newItemNextIssued = $newItem->quantity_issued + $data['quantity_issued'];

        if ($newItemNextIssued > ($newItem->opening_stock + $newItem->quantity_received)) {
            return $this->redirectToClinicPanel($request, 'stock-usage')
                ->withErrors([
                    'quantity_issued' => 'Updated issued quantity cannot exceed available stock for ' . $newItem->medicine_name . '.',
                ])
                ->withInput();
        }

        $usage->update([
            'clinic_stock_item_id' => $newItem->id,
            'student_id' => $data['student_id'],
            'quantity_issued' => $data['quantity_issued'],
            'diagnosis' => $data['diagnosis'],
        ]);

        $this->syncClinicStockItemIssued($oldItem, $oldItemNextIssued);
        $this->syncClinicStockItemIssued($newItem, $newItemNextIssued);

        return $this->redirectToClinicPanel($request, 'stock-usage')
            ->with('success', 'Stock usage updated successfully.');
    }

    public function deleteClinicStockUsage(Request $request, ClinicStockUsage $usage)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $item = ClinicStockItem::findOrFail($usage->clinic_stock_item_id);
        $nextIssued = max(0, $item->quantity_issued - $usage->quantity_issued);

        $usage->delete();
        $this->syncClinicStockItemIssued($item, $nextIssued);

        return $this->redirectToClinicPanel($request, 'stock-usage')
            ->with('success', 'Stock usage deleted successfully.');
    }

    public function downloadClinicReport(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canGenerateClinicReport($user)) {
            return redirect()->route('clinic')->with('error', 'Only executive and senior nurse officer can download clinic reports.');
        }

        [
            'reportType' => $reportType,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'reportWeek' => $reportWeek,
            'reportStartDate' => $reportStartDate,
            'reportEndDate' => $reportEndDate,
            'reportSemester' => $reportSemester,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportLabel' => $reportLabel,
        ] = $this->reportFiltersFromRequest($request);

        $stockUsagesQuery = ClinicStockUsage::with(['stockItem']);
        $stockReceiptsQuery = ClinicStockReceipt::query()
            ->join('clinic_stock_items', 'clinic_stock_receipts.clinic_stock_item_id', '=', 'clinic_stock_items.id')
            ->select('clinic_stock_receipts.*', 'clinic_stock_items.medicine_name as stock_medicine_name');

        if ($reportType !== 'general') {
            $stockUsagesQuery->whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()]);
            $stockReceiptsQuery->whereBetween('clinic_stock_receipts.received_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        $stockUsages = $stockUsagesQuery->latest()->get();
        $stockReceipts = $stockReceiptsQuery
            ->latest('clinic_stock_receipts.received_date')
            ->latest('clinic_stock_receipts.id')
            ->get();
        $downloadStockItems = ClinicStockItem::with('firstReceipt')
            ->orderBy('medicine_name')
            ->get();
        $this->refreshClinicStockStatuses($downloadStockItems);
        $reportStockItems = $this->clinicReportAvailableStockItems($downloadStockItems, $reportType);
        $diseaseUsageGroups = $this->groupClinicUsagesByDisease($stockUsages);
        $diseaseBreakdown = $this->summarizeClinicDiseaseBreakdown($stockUsages);

        $fileName = 'clinic-stock-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($stockReceipts, $reportStockItems, $diseaseUsageGroups, $diseaseBreakdown, $reportLabel) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Report Period', $reportLabel]);
            fputcsv($handle, []);
            fputcsv($handle, ['Disease Category Summary']);
            fputcsv($handle, ['Disease', 'Cases', 'Quantity Used', 'Medicines Used', 'Latest Date']);

            if ($diseaseBreakdown->isEmpty()) {
                fputcsv($handle, ['No disease categories recorded for this report period.']);
            } else {
                foreach ($diseaseBreakdown as $disease) {
                    fputcsv($handle, [
                        $disease['label'],
                        $disease['case_count'],
                        $disease['quantity_issued'],
                        implode(', ', $disease['medicine_names']),
                        $disease['latest_used_at']?->format('Y-m-d') ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Stock Received Report']);
            fputcsv($handle, ['Medicine/Item Name', 'Quantity Received', 'Date Received', 'Expiry Date', 'Dosage/Form', 'Important Notes']);

            foreach ($stockReceipts as $receipt) {
                fputcsv($handle, [
                    $receipt->stock_medicine_name ?? 'Unknown',
                    $receipt->quantity_received,
                    optional($receipt->received_date)->format('Y-m-d') ?? optional($receipt->created_at)->format('Y-m-d') ?? '',
                    optional($receipt->expiry_date)->format('Y-m-d') ?? '',
                    $receipt->dosage_form ?? '',
                    $receipt->important_notes ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Available Stock']);
            fputcsv($handle, ['Medicine/Item Name', 'Available Stock', 'Date Entered', 'Expiry Date', 'Dosage/Form', 'Important Notes', 'Status']);

            if ($reportStockItems->isEmpty()) {
                fputcsv($handle, ['No available stock found for this report.']);
            } else {
                foreach ($reportStockItems as $item) {
                    fputcsv($handle, [
                        $item->medicine_name ?: 'Unknown',
                        $item->balance,
                        optional($item->firstReceipt?->received_date ?? $item->created_at)->format('Y-m-d') ?? '',
                        optional($item->expiry_date)->format('Y-m-d') ?? '',
                        $item->dosage_form ?? '',
                        $item->important_notes ?? '',
                        Str::headline(str_replace('_', ' ', $item->status)),
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Stock Used by Disease']);

            if ($diseaseUsageGroups->isEmpty()) {
                fputcsv($handle, ['No stock usage records exist for the selected period.']);
            } else {
                foreach ($diseaseUsageGroups as $diseaseGroup) {
                    fputcsv($handle, [
                        'Disease',
                        $diseaseGroup['label'],
                        'Cases',
                        $diseaseGroup['case_count'],
                        'Quantity Used',
                        $diseaseGroup['quantity_issued'],
                    ]);
                    fputcsv($handle, [
                        'Medicines Used',
                        implode(', ', $diseaseGroup['medicine_names']),
                        'Latest Date',
                        $diseaseGroup['latest_used_at']?->format('Y-m-d') ?? '',
                    ]);
                    fputcsv($handle, ['Medicine', 'Student ID', 'Quantity Used', 'Date Used']);

                    foreach ($diseaseGroup['usages'] as $usage) {
                        fputcsv($handle, [
                            optional($usage->stockItem)->medicine_name ?? 'Unknown',
                            $usage->student_id,
                            $usage->quantity_issued,
                            optional($usage->usage_date)->format('Y-m-d') ?? optional($usage->created_at)->format('Y-m-d') ?? '',
                        ]);
                    }

                    fputcsv($handle, []);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadCounsellingReport(Request $request)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $this->canManageCounselling($user)) {
            return redirect()->route('counselling')->with('error', 'Only counselling managers can download counselling reports.');
        }

        [
            'reportType' => $reportType,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'reportWeek' => $reportWeek,
            'reportStartDate' => $reportStartDate,
            'reportEndDate' => $reportEndDate,
            'reportSemester' => $reportSemester,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportLabel' => $reportLabel,
        ] = $this->reportFiltersFromRequest($request);

        $reportBookings = $this->counsellingReportQuery($reportType, $startDate, $endDate)
            ->with('user')
            ->latest()
            ->get();

        $statusSummary = $reportBookings->countBy(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status));
        $attendedBookings = $reportBookings
            ->filter(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status) === 'attended')
            ->sortByDesc(fn (CounsellingBooking $booking) => optional($booking->appointment_date ?? $booking->updated_at ?? $booking->created_at)->timestamp ?? 0)
            ->values();
        $pendingBookings = $reportBookings
            ->filter(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status) === 'pending')
            ->sortBy(fn (CounsellingBooking $booking) => optional($booking->preferred_date ?? $booking->created_at)->timestamp ?? PHP_INT_MAX)
            ->values();

        $fileName = 'counselling-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use (
            $attendedBookings,
            $pendingBookings,
            $reportBookings,
            $reportLabel,
            $statusSummary,
            $user
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Report Period', $reportLabel]);
            fputcsv($handle, ['Generated By', $user->name ?: 'Psychologist']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Requests', $reportBookings->count()]);
            fputcsv($handle, ['Pending Sessions', $statusSummary->get('pending', 0)]);
            fputcsv($handle, ['Scheduled Sessions', $statusSummary->get('scheduled', 0)]);
            fputcsv($handle, ['Students Who Have Gone For Counselling', $statusSummary->get('attended', 0)]);
            fputcsv($handle, ['Cancelled Sessions', $statusSummary->get('cancelled', 0)]);
            fputcsv($handle, []);

            fputcsv($handle, ['Students Who Have Gone For Counselling']);
            fputcsv($handle, ['Student Name', 'Identity Number', 'Campus', 'Programme', 'Year of Study', 'Session Date', 'Requested On', 'Counsellor Notes']);

            if ($attendedBookings->isEmpty()) {
                fputcsv($handle, ['No attended counselling sessions found for this report period.']);
            } else {
                foreach ($attendedBookings as $booking) {
                    fputcsv($handle, [
                        $booking->student_name,
                        $booking->student_identity_number ?: '-',
                        $booking->campus ?: '-',
                        $booking->programme ?: '-',
                        $booking->year_of_study ?: '-',
                        $booking->appointment_date?->format('Y-m-d H:i') ?: '-',
                        $booking->created_at?->format('Y-m-d H:i') ?: '-',
                        $this->flattenCsvCell($booking->counsellor_notes) ?: '-',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Pending Sessions']);
            fputcsv($handle, ['Student Name', 'Identity Number', 'Campus', 'Programme', 'Year of Study', 'Preferred Session', 'Requested On', 'Reason']);

            if ($pendingBookings->isEmpty()) {
                fputcsv($handle, ['No pending counselling sessions found for this report period.']);
            } else {
                foreach ($pendingBookings as $booking) {
                    $preferredSession = trim(implode(' ', array_filter([
                        $booking->preferred_date?->format('Y-m-d'),
                        $booking->preferred_time ? 'at ' . $booking->preferred_time : null,
                    ])));

                    fputcsv($handle, [
                        $booking->student_name,
                        $booking->student_identity_number ?: '-',
                        $booking->campus ?: '-',
                        $booking->programme ?: '-',
                        $booking->year_of_study ?: '-',
                        $preferredSession !== '' ? $preferredSession : '-',
                        $booking->created_at?->format('Y-m-d H:i') ?: '-',
                        $this->flattenCsvCell($booking->reason) ?: '-',
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function sendAccommodationStatusEmail(AccommodationApplication $application): array
    {
        $application->loadMissing(['room', 'user']);

        $recipients = $this->accommodationStatusRecipients($application);
        $statusLabel = Str::headline($application->status);

        if ($recipients === []) {
            return [
                'success' => false,
                'message' => 'Accommodation status updated to ' . $statusLabel . ', but no email address was found for the student.',
            ];
        }

        $submittedRecipients = [];
        $failedRecipients = [];
        $messageIds = [];

        foreach ($recipients as $recipient) {
            try {
                $sentMessage = Mail::to($recipient)->send(new AccommodationStatusUpdated($application));

                $submittedRecipients[] = $recipient;

                if ($sentMessage?->getMessageId()) {
                    $messageIds[] = $sentMessage->getMessageId();
                }
            } catch (\Throwable $exception) {
                report($exception);
                $failedRecipients[] = $recipient;
            }
        }

        Log::info('Accommodation status email processed.', [
            'application_id' => $application->id,
            'status' => $application->status,
            'mailer' => config('mail.default'),
            'submitted_recipients' => $submittedRecipients,
            'failed_recipients' => $failedRecipients,
            'message_ids' => $messageIds,
        ]);

        if ($submittedRecipients === []) {
            return [
                'success' => false,
                'message' => 'Accommodation status updated to ' . $statusLabel . ', but the email could not be handed off to the mail service for ' . $this->formatEmailRecipientList($recipients) . '.',
            ];
        }

        $message = 'Accommodation status updated to ' . $statusLabel . '. Email sent to ' . $this->formatEmailRecipientList($submittedRecipients) . '.';

        if ($messageIds !== []) {
            $message .= ' Reference ID ' . (count($messageIds) === 1 ? 'is ' : 's are ') . $this->formatEmailRecipientList($messageIds) . '.';
        }

        if ($failedRecipients !== []) {
            $message .= ' Delivery attempt failed for ' . $this->formatEmailRecipientList($failedRecipients) . '.';
        }

        return [
            'success' => $failedRecipients === [],
            'message' => $message,
        ];
    }

    protected function accommodationStatusRecipients(AccommodationApplication $application): array
    {
        $recipients = [];

        foreach ([$application->email, optional($application->user)->email] as $email) {
            $email = is_string($email) ? trim($email) : '';

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $recipients[Str::lower($email)] = $email;
        }

        return array_values($recipients);
    }

    protected function formatEmailRecipientList(array $recipients): string
    {
        $recipients = array_values($recipients);
        $count = count($recipients);

        if ($count === 0) {
            return 'no recipients';
        }

        if ($count === 1) {
            return $recipients[0];
        }

        if ($count === 2) {
            return $recipients[0] . ' and ' . $recipients[1];
        }

        $lastRecipient = array_pop($recipients);

        return implode(', ', $recipients) . ', and ' . $lastRecipient;
    }

    protected function occupyingAccommodationStatuses(): array
    {
        return ['admitted', 'checkout_requested', 'checkout_rejected'];
    }

    protected function checkoutEligibleStatuses(): array
    {
        return ['admitted', 'checkout_rejected'];
    }

    protected function canManageAccommodationRooms(User $user): bool
    {
        return in_array($user->role, ['warden', 'ssd_assistant_2'], true);
    }

    protected function canUseAccommodationCommunication(User $user): bool
    {
        return in_array($user->role, ['warden', 'ssd_assistant_2'], true);
    }

    protected function canAccessAccommodation(User $user): bool
    {
        return ($user->role === 'student' && $user->student_type === 'new')
            || in_array($user->role, ['executive', 'warden', 'ssd_assistant_2'], true);
    }

    protected function canViewAccommodationRooms(User $user): bool
    {
        return in_array($user->role, ['executive', 'warden', 'ssd_assistant_2'], true);
    }

    protected function canManageAccommodationAdmissions(User $user): bool
    {
        return in_array($user->role, ['executive', 'warden', 'ssd_assistant_2'], true);
    }

    protected function canGenerateAccommodationReport(User $user): bool
    {
        return in_array($user->role, ['executive', 'warden', 'ssd_assistant_2'], true);
    }

    protected function canConfirmAccommodationPayments(User $user): bool
    {
        return $user->role === 'ssd_assistant_2';
    }

    protected function makaungAdmittedAccommodationApplications()
    {
        return AccommodationApplication::with(['user', 'room', 'paymentConfirmedBy'])
            ->where('status', 'admitted')
            ->whereHas('room', function ($query) {
                $query->whereRaw('LOWER(block_name) = ?', ['makaung']);
            });
    }

    protected function admittedAccommodationPaymentApplications()
    {
        return AccommodationApplication::with(['user', 'room', 'paymentConfirmedBy', 'latestPayment.confirmedBy', 'payments'])
            ->where('status', 'admitted');
    }

    protected function confirmedAccommodationPaymentReports()
    {
        return AccommodationPayment::with(['application.user', 'application.room', 'confirmedBy'])
            ->where('status', 'confirmed')
            ->whereHas('application', fn ($query) => $query->where('status', 'admitted'))
            ->latest('confirmed_at')
            ->latest('id');
    }

    protected function findAccommodationPaymentReportApplication(User $user, mixed $fullName): ?AccommodationApplication
    {
        if (! $this->canConfirmAccommodationPayments($user) || ! is_string($fullName) || trim($fullName) === '') {
            return null;
        }

        return $this->admittedAccommodationApplicationForFullName($fullName);
    }

    protected function admittedAccommodationApplicationForFullName(string $fullName): ?AccommodationApplication
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return null;
        }

        return AccommodationApplication::with(['user', 'room', 'admissionProcessedBy', 'paymentConfirmedBy', 'latestPayment.confirmedBy'])
            ->where('status', 'admitted')
            ->where(function ($query) use ($fullName) {
                $query->whereRaw('LOWER(TRIM(full_name)) = ?', [Str::lower($fullName)])
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($fullName)]));
            })
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }

    protected function reallocationEligibleStatuses(): array
    {
        return ['admitted', 'checkout_rejected'];
    }

    protected function availableAccommodationRooms(?int $ignoreApplicationId = null)
    {
        return AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) use ($ignoreApplicationId) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());

                    if ($ignoreApplicationId) {
                        $query->where('id', '!=', $ignoreApplicationId);
                    }
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get()
            ->filter(fn (AccommodationRoom $room) => $room->occupied_beds < $room->capacity)
            ->values();
    }

    protected function findExistingAccommodationApplication(User $user, ?string $nationalId = null): ?AccommodationApplication
    {
        return AccommodationApplication::query()
            ->where(function ($query) use ($user, $nationalId) {
                $query->where('user_id', $user->id);

                if (filled($nationalId)) {
                    $query->orWhere('national_id', $nationalId);
                }
            })
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }

    protected function duplicateAccommodationApplicationMessage(AccommodationApplication $application, User $user): string
    {
        return $application->user_id === $user->id
            ? 'You already have an accommodation application on file.'
            : 'An accommodation application already exists for this national ID.';
    }

    protected function normalizeNationalId(mixed $nationalId): ?string
    {
        if (! is_string($nationalId)) {
            return null;
        }

        $nationalId = Str::upper(trim($nationalId));

        return $nationalId !== '' ? $nationalId : null;
    }

    protected function normalizeStudentId(mixed $studentId): ?string
    {
        if (! is_string($studentId)) {
            return null;
        }

        $studentId = trim($studentId);

        return $studentId !== '' ? $studentId : null;
    }

    protected function formatAccommodationRoomLabel(?AccommodationRoom $room): ?string
    {
        if (! $room) {
            return null;
        }

        return $room->block_name . '-' . str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT);
    }

    protected function formatAccommodationUserLabel(?User $user): string
    {
        if (! $user) {
            return 'Not recorded';
        }

        return trim(($user->name ?: $user->email) . ' (' . Str::headline($user->role) . ')');
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

    protected function resolveAccommodationReportIntake(?string $requestedIntake = null, $availableIntakes = null): ?string
    {
        $availableIntakes = $availableIntakes ?? $this->availableAccommodationReportIntakes();
        $requestedIntake = is_string($requestedIntake) ? trim($requestedIntake) : '';

        if ($availableIntakes->isEmpty()) {
            return null;
        }

        if ($requestedIntake !== '' && $availableIntakes->contains($requestedIntake)) {
            return $requestedIntake;
        }

        return $availableIntakes->first();
    }

    protected function availableAccommodationReportIntakes()
    {
        $recentApplications = AccommodationApplication::query()
            ->whereNotNull('intake')
            ->latest('updated_at')
            ->latest('id')
            ->get()
            ->values();

        $currentApplication = $recentApplications
            ->first(fn (AccommodationApplication $application) => filled(trim((string) $application->intake)));

        if (! $currentApplication) {
            return collect();
        }

        $currentIntakeYear = $this->accommodationApplicationReportYear($currentApplication) ?? now()->year;

        return $recentApplications
            ->filter(function (AccommodationApplication $application) use ($currentApplication, $currentIntakeYear) {
                if ($application->is($currentApplication)) {
                    return true;
                }

                $intakeYear = $this->accommodationApplicationReportYear($application);

                return $intakeYear !== null
                    && $intakeYear >= ($currentIntakeYear - 3)
                    && $intakeYear <= $currentIntakeYear;
            })
            ->map(fn (AccommodationApplication $application) => trim((string) $application->intake))
            ->filter()
            ->unique()
            ->values();
    }

    protected function availableAccommodationReportYears($availableIntakes = null)
    {
        return AccommodationApplication::query()
            ->whereNotNull('intake')
            ->latest('updated_at')
            ->latest('id')
            ->get()
            ->filter(fn (AccommodationApplication $application) => filled(trim((string) $application->intake)))
            ->map(fn (AccommodationApplication $application) => $this->accommodationApplicationReportYear($application))
            ->filter()
            ->unique()
            ->values();
    }

    protected function resolveAccommodationReportYear(?string $requestedYear = null, $availableYears = null): ?int
    {
        $availableYears = $availableYears ?? $this->availableAccommodationReportYears();
        $requestedYear = is_string($requestedYear) ? trim($requestedYear) : '';

        if ($availableYears->isEmpty()) {
            return null;
        }

        if ($requestedYear !== '' && $availableYears->contains((int) $requestedYear)) {
            return (int) $requestedYear;
        }

        return $availableYears->first();
    }

    protected function extractAccommodationIntakeYear(?string $intake): ?int
    {
        if (! is_string($intake) || trim($intake) === '') {
            return null;
        }

        if (! preg_match('/\b((?:19|20)\d{2})\b/', $intake, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    protected function accommodationApplicationReportYear(AccommodationApplication $application): ?int
    {
        return $this->extractAccommodationIntakeYear($application->intake)
            ?? $application->check_in_date?->year
            ?? $application->created_at?->year;
    }

    protected function buildAccommodationReportData(?string $requestedIntake = null, ?string $requestedYear = null): array
    {
        $allAvailableIntakes = $this->availableAccommodationReportIntakes();
        $availableYears = $this->availableAccommodationReportYears($allAvailableIntakes);
        $selectedYear = $this->resolveAccommodationReportYear($requestedYear, $availableYears);

        $availableIntakes = $selectedYear
            ? AccommodationApplication::query()
                ->whereNotNull('intake')
                ->latest('updated_at')
                ->latest('id')
                ->get()
                ->filter(fn (AccommodationApplication $application) => $this->accommodationApplicationReportYear($application) === $selectedYear)
                ->map(fn (AccommodationApplication $application) => trim((string) $application->intake))
                ->filter()
                ->unique()
                ->values()
            : collect();

        $currentIntake = $this->resolveAccommodationReportIntake($requestedIntake, $availableIntakes);

        $applications = $availableIntakes->isNotEmpty()
            ? AccommodationApplication::with([
                'user',
            'room',
            'admissionProcessedBy',
            'requestedRoom',
            'previousRoom',
            'reallocationApprovedBy',
                'roomReallocatedBy',
            ])
                ->whereNotNull('intake')
                ->latest('updated_at')
                ->latest('id')
                ->get()
                ->filter(fn (AccommodationApplication $application) => $this->accommodationApplicationReportYear($application) === $selectedYear)
                ->values()
            : collect();

        $intakeOccupancyByRoom = $applications
            ->whereIn('status', $this->occupyingAccommodationStatuses())
            ->filter(fn ($application) => filled($application->accommodation_room_id))
            ->countBy('accommodation_room_id');

        $rooms = AccommodationRoom::query()
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get();

        $rooms->transform(function (AccommodationRoom $room) use ($intakeOccupancyByRoom) {
            $room->occupied_beds = (int) ($intakeOccupancyByRoom[$room->id] ?? 0);

            return $room;
        });

        $pendingApplications = $applications
            ->where('status', 'pending')
            ->values();

        $checkoutRequests = $applications
            ->where('status', 'checkout_requested')
            ->values();

        $activeResidents = $applications
            ->whereIn('status', $this->occupyingAccommodationStatuses())
            ->values();

        $statusSummary = $applications
            ->countBy(fn ($application) => Str::headline($application->status))
            ->sortKeys();

        $availableRooms = $rooms
            ->filter(fn (AccommodationRoom $room) => $room->occupied_beds < $room->capacity)
            ->values();

        $occupiedRoomsCount = $rooms
            ->filter(fn (AccommodationRoom $room) => $room->occupied_beds > 0)
            ->count();

        $occupiedBedsCount = $rooms->sum('occupied_beds');
        $totalCapacity = $rooms->sum('capacity');

        return [
            'currentIntake' => $currentIntake,
            'selectedYear' => $selectedYear,
            'availableIntakes' => $availableIntakes,
            'availableYears' => $availableYears,
            'applications' => $applications,
            'pendingApplications' => $pendingApplications,
            'checkoutRequests' => $checkoutRequests,
            'activeResidents' => $activeResidents,
            'statusSummary' => $statusSummary,
            'rooms' => $rooms,
            'availableRooms' => $availableRooms,
            'availableRoomsCount' => $availableRooms->count(),
            'occupiedRoomsCount' => $occupiedRoomsCount,
            'occupiedBedsCount' => $occupiedBedsCount,
            'availableBedsCount' => max(0, $totalCapacity - $occupiedBedsCount),
            'totalCapacity' => $totalCapacity,
        ];
    }

    protected function accommodationApplicationFee(): float
    {
        return 105.00;
    }

    protected function accommodationMonthlyRentAmount(): float
    {
        return 500.00;
    }

    protected function accommodationRentSystemStartMonth(): Carbon
    {
        return Carbon::create(2025, 8, 1)->startOfMonth();
    }

    protected function unpaidAccommodationRentReports($applications)
    {
        $monthlyRentAmount = $this->accommodationMonthlyRentAmount();

        return $applications
            ->map(function (AccommodationApplication $application) use ($monthlyRentAmount) {
                $unpaidMonths = $this->unpaidAccommodationRentMonths($application);

                if ($unpaidMonths->isEmpty()) {
                    return null;
                }

                return [
                    'application' => $application,
                    'months_count' => $unpaidMonths->count(),
                    'months_label' => $this->formatAccommodationRentMonthsLabel($unpaidMonths),
                    'amount_due' => $unpaidMonths->count() * $monthlyRentAmount,
                ];
            })
            ->filter()
            ->values();
    }

    protected function unpaidAccommodationRentMonths(AccommodationApplication $application)
    {
        $startMonth = $this->accommodationRentSystemStartMonth();

        if ($application->check_in_date && $application->check_in_date->copy()->startOfMonth()->greaterThan($startMonth)) {
            $startMonth = $application->check_in_date->copy()->startOfMonth();
        }

        $endMonth = now()->startOfMonth();

        if ($startMonth->greaterThan($endMonth)) {
            return collect();
        }

        $paidMonths = $application->payments
            ->where('status', 'confirmed')
            ->map(fn (AccommodationPayment $payment) => $payment->payment_month?->copy()->startOfMonth()->format('Y-m'))
            ->filter()
            ->unique()
            ->values();

        $months = collect();
        $cursor = $startMonth->copy();

        while ($cursor->lessThanOrEqualTo($endMonth)) {
            if (! $paidMonths->contains($cursor->format('Y-m'))) {
                $months->push($cursor->copy());
            }

            $cursor->addMonth();
        }

        return $months;
    }

    protected function formatAccommodationRentMonthsLabel($months): string
    {
        if ($months->count() === 1) {
            return $months->first()->format('F Y');
        }

        return $months
            ->map(fn (Carbon $month) => $month->format('F Y'))
            ->join(', ') . ' (' . $months->count() . ' months)';
    }

    protected function accommodationMonthlyRentPaymentMethod(): string
    {
        return 'standard_lesotho_bank';
    }

    protected function accommodationMonthlyRentPaymentMethodLabel(): string
    {
        return 'Standard Lesotho Bank';
    }

    protected function accommodationPaymentExistsForMonth(AccommodationApplication $application, string $paymentMonth): bool
    {
        return AccommodationPayment::query()
            ->where('accommodation_application_id', $application->id)
            ->whereDate('payment_month', $paymentMonth)
            ->exists();
    }

    protected function recordAccommodationMonthlyPayment(
        AccommodationApplication $application,
        string $receiptNumber,
        string $paymentMonth,
        User $confirmedBy
    ): AccommodationPayment {
        $payment = AccommodationPayment::create([
            'accommodation_application_id' => $application->id,
            'receipt_number' => $receiptNumber,
            'payment_month' => $paymentMonth,
            'amount' => $this->accommodationMonthlyRentAmount(),
            'method' => $this->accommodationMonthlyRentPaymentMethod(),
            'status' => 'confirmed',
            'confirmed_by_user_id' => $confirmedBy->id,
            'confirmed_at' => now(),
        ]);

        $application->update([
            'payment_receipt_number' => $payment->receipt_number,
            'payment_amount' => $payment->amount,
            'payment_month' => $payment->payment_month,
            'payment_method' => $payment->method,
            'payment_status' => $payment->status,
            'payment_confirmed_by_user_id' => $confirmedBy->id,
            'payment_confirmed_at' => $payment->confirmed_at,
            'paid_at' => $application->paid_at ?? now(),
        ]);

        return $payment;
    }

    protected function formatAccommodationPaymentMonth(string $paymentMonth): string
    {
        return Carbon::createFromFormat('Y-m', $paymentMonth)
            ->startOfMonth()
            ->toDateString();
    }

    protected function renderClinicPage(User $user, Request $request)
    {
        $reportGenerated = $request->boolean('report_generated') || $request->query->has('report_type');

        [
            'reportType' => $reportType,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'reportWeek' => $reportWeek,
            'reportStartDate' => $reportStartDate,
            'reportEndDate' => $reportEndDate,
            'reportSemester' => $reportSemester,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportLabel' => $reportLabel,
        ] = $this->reportFiltersFromRequest($request);

        $stockItems = ClinicStockItem::with([
                'confirmer',
                'firstReceipt',
                'comments.user',
                'comments.replies.user',
            ])
            ->orderBy('medicine_name')
            ->get();
        $this->refreshClinicStockStatuses($stockItems);
        $reportStockItems = $this->clinicReportAvailableStockItems($stockItems, $reportType);

        $stockUsagesQuery = ClinicStockUsage::with(['stockItem']);
        $stockReceiptsQuery = ClinicStockReceipt::query()
            ->join('clinic_stock_items', 'clinic_stock_receipts.clinic_stock_item_id', '=', 'clinic_stock_items.id')
            ->select('clinic_stock_receipts.*', 'clinic_stock_items.medicine_name as stock_medicine_name');

        if ($reportType !== 'general') {
            $stockUsagesQuery->whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()]);
            $stockReceiptsQuery->whereBetween('clinic_stock_receipts.received_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        $stockUsages = $stockUsagesQuery
            ->latest()
            ->get();

        $stockReceipts = $stockReceiptsQuery
            ->latest('clinic_stock_receipts.received_date')
            ->latest('clinic_stock_receipts.id')
            ->get();
        $diseaseUsageGroups = $this->groupClinicUsagesByDisease($stockUsages);
        $diseaseBreakdown = $this->summarizeClinicDiseaseBreakdown($stockUsages);

        return view('clinic.executive', compact(
            'user',
            'stockItems',
            'reportStockItems',
            'stockUsages',
            'stockReceipts',
            'diseaseUsageGroups',
            'diseaseBreakdown',
            'reportGenerated',
            'reportType',
            'reportYear',
            'reportMonth',
            'reportWeek',
            'reportStartDate',
            'reportEndDate',
            'reportSemester',
            'reportLabel'
        ));
    }

    protected function redirectToClinicPanel(Request $request, string $defaultPanel)
    {
        $panel = $request->input('clinic_panel', $defaultPanel);

        if ($panel === 'report') {
            return redirect()->route('clinic', [
                'report_generated' => 1,
                'report_type' => $request->input('report_type', 'general'),
                'report_year' => $request->input('report_year', now()->year),
                'report_month' => $request->input('report_month', now()->month),
                'report_week' => $request->input('report_week', now()->weekOfYear),
                'report_start_date' => $request->input('report_start_date', now()->startOfWeek()->toDateString()),
                'report_end_date' => $request->input('report_end_date', now()->startOfWeek()->addDays(6)->toDateString()),
                'report_semester' => $request->input('report_semester', 1),
            ])->with('clinic_panel', 'report');
        }

        return redirect()->route('clinic')->with('clinic_panel', $panel);
    }

    protected function ensureDefaultClinicStockExists(): void
    {
        if (ClinicStockItem::exists()) {
            return;
        }

        foreach ([
            ['medicine_name' => 'Medicine 0', 'opening_stock' => 0, 'quantity_received' => 0, 'quantity_issued' => 0, 'status' => 'out_of_stock'],
            ['medicine_name' => 'Paracetamol', 'opening_stock' => 120, 'quantity_received' => 30, 'quantity_issued' => 40, 'status' => 'in_stock'],
            ['medicine_name' => 'Amoxicillin', 'opening_stock' => 50, 'quantity_received' => 0, 'quantity_issued' => 22, 'status' => 'low_stock'],
        ] as $stockItem) {
            ClinicStockItem::create($stockItem);
        }
    }

    protected function clinicReportAvailableStockItems($stockItems, string $reportType)
    {
        if (! in_array($reportType, ['month', 'semester'], true)) {
            return $stockItems;
        }

        return $stockItems
            ->filter(fn (ClinicStockItem $item) => $item->balance > 0)
            ->values();
    }

    protected function refreshClinicStockStatuses($stockItems): void
    {
        foreach ($stockItems as $item) {
            $status = $this->resolveClinicStockStatusForItem($item);

            if ($item->status === $status) {
                continue;
            }

            $item->forceFill(['status' => $status])->save();
            $item->status = $status;
        }
    }

    protected function resolveClinicStockStatusForItem(ClinicStockItem $item): string
    {
        return $this->resolveClinicStockStatus(
            $item->balance,
            $item->opening_stock + $item->quantity_received
        );
    }

    protected function resolveClinicStockStatus(int $balance, int $totalStock): string
    {
        if ($balance <= 0 || $totalStock <= 0) {
            return 'out_of_stock';
        }

        if (($balance / $totalStock) < 0.41) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    protected function syncClinicStockItemIssued(ClinicStockItem $item, int $quantityIssued): void
    {
        $safeIssued = max(0, $quantityIssued);

        $item->update([
            'quantity_issued' => $safeIssued,
            'status' => $this->resolveClinicStockStatus(
                $item->opening_stock + $item->quantity_received - $safeIssued,
                $item->opening_stock + $item->quantity_received
            ),
        ]);
    }

    protected function recordClinicStockReceipt(
        ClinicStockItem $item,
        User $user,
        int $quantityReceived,
        string $expiryDate,
        ?string $dosageForm = null,
        ?string $importantNotes = null
    ): void
    {
        if ($quantityReceived <= 0) {
            return;
        }

        ClinicStockReceipt::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'quantity_received' => $quantityReceived,
            'received_date' => now()->toDateString(),
            'expiry_date' => $expiryDate,
            'dosage_form' => $dosageForm,
            'important_notes' => $importantNotes,
        ]);
    }

    protected function normalizeOptionalText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    protected function resolveClinicStockItemExpiryDate(ClinicStockItem $item, string $incomingExpiryDate): string
    {
        $currentExpiryDate = $item->expiry_date?->toDateString();

        if (! $currentExpiryDate) {
            return $incomingExpiryDate;
        }

        return Carbon::parse($currentExpiryDate)->lte(Carbon::parse($incomingExpiryDate))
            ? $currentExpiryDate
            : $incomingExpiryDate;
    }

    protected function canAccessStudentCounselling(User $user): bool
    {
        return $user->role === 'student' && $user->student_type === 'continuing';
    }

    protected function notifyStudentOfScheduledCounsellingSession(CounsellingBooking $booking): void
    {
        $studentEmail = optional($booking->user)->email;

        if (! is_string($studentEmail) || ! filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Counselling session scheduled without a valid student email.', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);

            return;
        }

        try {
            Mail::to($studentEmail)->send(new CounsellingSessionScheduled($booking));
        } catch (\Throwable $exception) {
            report($exception);

            Log::warning('Counselling session notification email could not be sent.', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'student_email' => $studentEmail,
            ]);
        }
    }

    protected function nationalIdAlreadyRegistered(mixed $nationalId): bool
    {
        $normalizedNationalId = $this->normalizeNationalId($nationalId);

        if (! $normalizedNationalId) {
            return false;
        }

        $needle = Str::lower($normalizedNationalId);

        return User::query()
            ->whereNotNull('id_number')
            ->whereRaw('LOWER(TRIM(id_number)) = ?', [$needle])
            ->exists()
            || Student::query()
                ->whereNotNull('id_number')
                ->whereRaw('LOWER(TRIM(id_number)) = ?', [$needle])
                ->exists();
    }

    protected function studentIdAlreadyRegistered(mixed $studentId): bool
    {
        $normalizedStudentId = $this->normalizeStudentId($studentId);

        if (! $normalizedStudentId) {
            return false;
        }

        return User::query()
            ->whereNotNull('student_id')
            ->whereRaw('TRIM(student_id) = ?', [$normalizedStudentId])
            ->exists()
            || Student::query()
                ->whereNotNull('student_id')
                ->whereRaw('TRIM(student_id) = ?', [$normalizedStudentId])
                ->exists();
    }

    protected function emailAlreadyRegistered(mixed $email): bool
    {
        if (! is_string($email)) {
            return false;
        }

        $normalizedEmail = $this->normalizeLoginIdentifier($email);

        if (! filled($normalizedEmail)) {
            return false;
        }

        return User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->exists()
            || Admin::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
                ->exists();
    }

    protected function isAdminRegistrationEmail(mixed $email): bool
    {
        if (! is_string($email)) {
            return false;
        }

        return preg_match('/^[a-z]+\\.[a-z]+@limkokwing\\.ac\\.ls$/', $this->normalizeLoginIdentifier($email)) === 1;
    }

    protected function canAccessClinic(User $user): bool
    {
        return in_array($user->role, ['executive', 'ssd_assistant_1', 'ssd_assistant_2', 'senior_nurse_officer'], true);
    }

    protected function canGenerateClinicReport(User $user): bool
    {
        return in_array($user->role, ['executive', 'senior_nurse_officer'], true);
    }

    protected function canManageCounselling(User $user): bool
    {
        return in_array($user->role, ['psychologist', 'executive'], true);
    }

    protected function normalizeCounsellingStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'approved' => 'scheduled',
            'completed' => 'attended',
            default => strtolower((string) $status),
        };
    }

    protected function counsellingBookingStatuses(): array
    {
        return ['pending', 'scheduled', 'attended', 'cancelled'];
    }

    protected function counsellingAppointmentSlotStarts(): array
    {
        $starts = [];
        $minutes = 8 * 60;
        $closingMinutes = (16 * 60) + 30;
        $sessionMinutes = 75;
        $interval = 80;

        while (($minutes + $sessionMinutes) <= $closingMinutes) {
            $starts[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
            $minutes += $interval;
        }

        return $starts;
    }

    protected function unavailableCounsellingSessionSlots(): array
    {
        return CounsellingBooking::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNotNull('appointment_date')
                    ->orWhere(function ($pendingQuery) {
                        $pendingQuery
                            ->where('status', 'pending')
                            ->whereNotNull('preferred_date')
                            ->whereNotNull('preferred_time');
                    });
            })
            ->get(['id', 'campus', 'status', 'appointment_date', 'preferred_date', 'preferred_time'])
            ->flatMap(function (CounsellingBooking $booking) {
                return $this->counsellingBusySlotStarts($booking);
            })
            ->unique(fn (array $slot) => $slot['campus'] . '|' . $slot['value'])
            ->values()
            ->all();
    }

    protected function counsellingBookingSlotConflicts(Carbon $slotStart, ?string $campus, ?int $ignoreBookingId = null): bool
    {
        $slotEnd = $slotStart->copy()->addMinutes(75);

        return CounsellingBooking::query()
            ->where('status', '!=', 'cancelled')
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->where(function ($query) use ($campus) {
                $query->where('campus', $campus)
                    ->orWhereNull('campus');
            })
            ->where(function ($query) {
                $query->whereNotNull('appointment_date')
                    ->orWhere(function ($pendingQuery) {
                        $pendingQuery
                            ->where('status', 'pending')
                            ->whereNotNull('preferred_date')
                            ->whereNotNull('preferred_time');
                    });
            })
            ->get(['id', 'campus', 'status', 'appointment_date', 'preferred_date', 'preferred_time'])
            ->flatMap(function (CounsellingBooking $booking) {
                return $this->counsellingBusySlotStarts($booking);
            })
            ->contains(function (array $busySlot) use ($slotStart, $slotEnd) {
                $busyStart = Carbon::parse($busySlot['value']);
                $busyEnd = Carbon::parse($busySlot['end']);

                return $slotStart->lt($busyEnd) && $busyStart->lt($slotEnd);
            });
    }

    protected function counsellingBusySlotStarts(CounsellingBooking $booking): array
    {
        $starts = [];

        if ($booking->appointment_date) {
            $starts[] = Carbon::parse($booking->appointment_date)->seconds(0);
        } elseif ($booking->status === 'pending' && $booking->preferred_date && $booking->preferred_time) {
            $starts[] = Carbon::parse($booking->preferred_date->format('Y-m-d') . ' ' . $booking->preferred_time)->seconds(0);
        }

        return collect($starts)
            ->map(fn (Carbon $start) => [
                'campus' => (string) $booking->campus,
                'value' => $start->format('Y-m-d\TH:i'),
                'end' => $start->copy()->addMinutes(75)->format('Y-m-d\TH:i'),
            ])
            ->all();
    }

    protected function extractEmergencyCounsellingReply(array $response): ?string
    {
        $outputText = $response['output_text'] ?? null;
        if (is_string($outputText) && trim($outputText) !== '') {
            return trim($outputText);
        }

        $texts = [];

        foreach ($response['output'] ?? [] as $outputItem) {
            if (($outputItem['type'] ?? null) !== 'message') {
                continue;
            }

            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (($contentItem['type'] ?? null) === 'output_text' && is_string($contentItem['text'] ?? null)) {
                    $texts[] = trim($contentItem['text']);
                }
            }
        }

        $reply = trim(implode("\n\n", array_filter($texts)));

        return $reply !== '' ? $reply : null;
    }

    protected function reportFiltersFromRequest(Request $request): array
    {
        $reportType = in_array($request->query('report_type'), ['general', 'semester', 'month', 'week', 'year'], true)
            ? $request->query('report_type')
            : 'general';
        $reportYear = (int) ($request->query('report_year', now()->year));
        $reportYear = $reportYear >= 2000 && $reportYear <= 2100 ? $reportYear : (int) now()->year;
        $reportMonth = (int) ($request->query('report_month', now()->month));
        $reportMonth = $reportMonth >= 1 && $reportMonth <= 12 ? $reportMonth : (int) now()->month;
        $reportWeek = (int) ($request->query('report_week', now()->weekOfYear));
        $reportWeek = $reportWeek >= 1 && $reportWeek <= 53 ? $reportWeek : (int) now()->weekOfYear;
        $defaultWeekStart = now()->startOfWeek();
        $reportStartDate = $this->parseClinicReportDate(
            $request->query('report_start_date'),
            $defaultWeekStart
        );
        $reportEndDate = $this->parseClinicReportDate(
            $request->query('report_end_date'),
            $reportStartDate->copy()->addDays(6)
        );

        if ($reportStartDate->diffInDays($reportEndDate, false) !== 6) {
            $reportEndDate = $reportStartDate->copy()->addDays(6);
        }

        $reportSemester = (int) ($request->query('report_semester', 1));
        $reportSemester = in_array($reportSemester, [1, 2], true) ? $reportSemester : 1;

        [$startDate, $endDate, $reportLabel] = $this->resolveReportDateRange(
            $reportType,
            $reportYear,
            $reportMonth,
            $reportWeek,
            $reportStartDate,
            $reportEndDate,
            $reportSemester
        );

        return compact(
            'reportType',
            'reportYear',
            'reportMonth',
            'reportWeek',
            'reportStartDate',
            'reportEndDate',
            'reportSemester',
            'startDate',
            'endDate',
            'reportLabel'
        );
    }

    protected function resolveReportDateRange(
        string $reportType,
        int $reportYear,
        int $reportMonth,
        int $reportWeek,
        Carbon $reportStartDate,
        Carbon $reportEndDate,
        int $reportSemester
    ): array {
        if ($reportType === 'general') {
            $startDate = Carbon::create(2000, 1, 1)->startOfDay();
            $endDate = Carbon::create(2100, 12, 31)->endOfDay();

            return [$startDate, $endDate, 'General Report (All Records)'];
        }

        if ($reportType === 'year') {
            $startDate = Carbon::create($reportYear, 1, 1)->startOfDay();
            $endDate = Carbon::create($reportYear, 12, 31)->endOfDay();

            return [$startDate, $endDate, 'Year ' . $reportYear];
        }

        if ($reportType === 'month') {
            $startDate = Carbon::create($reportYear, $reportMonth, 1)->startOfDay();
            $endDate = (clone $startDate)->endOfMonth()->endOfDay();

            return [$startDate, $endDate, $startDate->format('F Y')];
        }

        if ($reportType === 'week') {
            $startDate = $reportStartDate->copy()->startOfDay();
            $endDate = $reportEndDate->copy()->endOfDay();

            return [$startDate, $endDate, 'Weekly Report (' . $startDate->format('M j') . ' - ' . $endDate->format('M j, Y') . ')'];
        }

        $startMonth = $reportSemester === 1 ? 8 : 1;
        $endMonth = $reportSemester === 1 ? 12 : 5;
        $startDate = Carbon::create($reportYear, $startMonth, 1)->startOfDay();
        $endDate = Carbon::create($reportYear, $endMonth, 1)->endOfMonth()->endOfDay();

        return [$startDate, $endDate, 'Semester ' . $reportSemester . ' (' . $reportYear . ')'];
    }

    protected function parseClinicReportDate(mixed $date, Carbon $fallback): Carbon
    {
        if (! is_string($date) || trim($date) === '') {
            return $fallback->copy()->startOfDay();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable $exception) {
            return $fallback->copy()->startOfDay();
        }
    }

    protected function counsellingReportQuery(string $reportType, Carbon $startDate, Carbon $endDate)
    {
        return CounsellingBooking::query()
            ->when($reportType !== 'general', function ($query) use ($startDate, $endDate) {
                $query->where(function ($reportQuery) use ($startDate, $endDate) {
                    $reportQuery
                        ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                        ->orWhereBetween('appointment_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
                });
            });
    }

    protected function flattenCsvCell(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', trim($value));

        return $normalized !== '' ? $normalized : null;
    }

    protected function groupClinicUsagesByDisease($stockUsages): \Illuminate\Support\Collection
    {
        return $stockUsages
            ->groupBy(fn ($usage) => $this->clinicDiagnosisKey($usage->diagnosis))
            ->map(function ($rows) {
                $sortedRows = $rows
                    ->sortByDesc(fn ($usage) => $this->clinicUsageMoment($usage)?->timestamp ?? 0)
                    ->values();
                $sample = $sortedRows->first();
                $label = $this->clinicDiagnosisLabel($sample?->diagnosis);
                $medicineNames = $sortedRows
                    ->map(fn ($usage) => optional($usage->stockItem)->medicine_name)
                    ->filter(fn ($name) => is_string($name) && trim($name) !== '')
                    ->map(fn ($name) => trim($name))
                    ->unique()
                    ->values();
                $latestUsedAt = $sortedRows
                    ->map(fn ($usage) => $this->clinicUsageMoment($usage))
                    ->filter()
                    ->first();

                return [
                    'label' => $label,
                    'case_count' => (int) $sortedRows->count(),
                    'quantity_issued' => (int) $sortedRows->sum('quantity_issued'),
                    'medicine_names' => $medicineNames->all(),
                    'latest_used_at' => $latestUsedAt,
                    'usages' => $sortedRows,
                ];
            })
            ->sort(function (array $left, array $right) {
                $caseComparison = $right['case_count'] <=> $left['case_count'];

                if ($caseComparison !== 0) {
                    return $caseComparison;
                }

                $quantityComparison = $right['quantity_issued'] <=> $left['quantity_issued'];

                if ($quantityComparison !== 0) {
                    return $quantityComparison;
                }

                return strcmp($left['label'], $right['label']);
            })
            ->values();
    }

    protected function summarizeClinicDiseaseBreakdown($stockUsages): \Illuminate\Support\Collection
    {
        return $this->groupClinicUsagesByDisease($stockUsages)
            ->map(fn (array $group) => [
                'label' => $group['label'],
                'case_count' => $group['case_count'],
                'quantity_issued' => $group['quantity_issued'],
                'medicine_names' => $group['medicine_names'],
                'latest_used_at' => $group['latest_used_at'],
            ])
            ->values();
    }

    protected function clinicUsageMoment(ClinicStockUsage $usage): ?Carbon
    {
        return Carbon::make($usage->usage_date ?? $usage->created_at);
    }

    protected function clinicDiagnosisKey(mixed $diagnosis): string
    {
        $label = $this->clinicDiagnosisLabel($diagnosis);

        return $label === 'Unspecified Diagnosis'
            ? '__unspecified__'
            : Str::lower($label);
    }

    protected function clinicDiagnosisLabel(mixed $diagnosis): string
    {
        if (! is_string($diagnosis)) {
            return 'Unspecified Diagnosis';
        }

        $diagnosis = trim(Str::squish($diagnosis));

        return $diagnosis !== '' ? $diagnosis : 'Unspecified Diagnosis';
    }
}
