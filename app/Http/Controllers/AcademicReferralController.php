<?php
// app/Http/Controllers/AcademicReferralController.php

namespace App\Http\Controllers;

use App\Models\AccommodationApplication;
use App\Models\StudentReferral;
use App\Models\ReferralComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AcademicReferralController extends Controller
{
    private function ssdStaffRoles(): array
    {
        return ['executive', 'ssd_assistant_1', 'ssd_assistant_2'];
    }

    private function supportDeskRoles(): array
    {
        return array_merge(['yearleader'], $this->ssdStaffRoles());
    }

    private function canManageSupportDesk(string $role): bool
    {
        return in_array($role, $this->supportDeskRoles(), true);
    }

    private function canHandleReferredStudents(string $role): bool
    {
        return in_array($role, $this->ssdStaffRoles(), true);
    }

    private function referralQueryForUser(User $user)
    {
        return StudentReferral::with(['referrer', 'student', 'comments.user', 'comments.replies.user']);
    }

    private function canAccessReferral(User $user, StudentReferral $referral): bool
    {
        return $this->canHandleReferredStudents($user->role)
            || ($user->role === 'yearleader' && (int) $referral->referred_by === (int) $user->id);
    }

    private function buildStudentProfile(User $student): array
    {
        $parts = preg_split('/\s+/', trim($student->name));
        $accommodationProfile = AccommodationApplication::query()
            ->where('user_id', $student->id)
            ->latest('id')
            ->first(['faculty', 'programme', 'contact_number']);

        return [
            'id' => $student->id,
            'name' => $student->name,
            'first_name' => $parts[0] ?? $student->name,
            'surname' => count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '',
            'student_identity_number' => $student->student_id ?: $student->id_number ?: '',
            'email' => $student->email,
            'faculty' => $accommodationProfile->faculty ?? '',
            'programme' => $accommodationProfile->programme ?? '',
            'contact_number' => $accommodationProfile->contact_number ?? '',
        ];
    }

    private function studentDirectory(): Collection
    {
        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_id', 'id_number']);

        if ($students->isEmpty()) {
            return collect();
        }

        $accommodationProfiles = AccommodationApplication::query()
            ->whereIn('user_id', $students->pluck('id'))
            ->orderByDesc('id')
            ->get(['user_id', 'faculty', 'programme', 'contact_number'])
            ->unique('user_id')
            ->keyBy('user_id');

        return $students->map(function (User $student) use ($accommodationProfiles) {
            $parts = preg_split('/\s+/', trim($student->name));
            $accommodationProfile = $accommodationProfiles->get($student->id);

            return [
                'id' => $student->id,
                'name' => $student->name,
                'first_name' => $parts[0] ?? $student->name,
                'surname' => count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '',
                'student_identity_number' => $student->student_id ?: $student->id_number ?: '',
                'email' => $student->email,
                'faculty' => $accommodationProfile->faculty ?? '',
                'programme' => $accommodationProfile->programme ?? '',
                'contact_number' => $accommodationProfile->contact_number ?? '',
            ];
        })->values();
    }

    private function resolveReferredStudent(array $validated): ?User
    {
        $identityNumber = trim((string) ($validated['student_identity_number'] ?? ''));
        $fullName = trim(implode(' ', array_filter([
            trim((string) ($validated['student_first_name'] ?? '')),
            trim((string) ($validated['student_surname'] ?? '')),
        ])));

        if (! empty($validated['student_user_id'])) {
            $studentFromHiddenField = User::query()
                ->where('role', 'student')
                ->find($validated['student_user_id']);

            if ($studentFromHiddenField) {
                $matchesIdentity = $identityNumber !== ''
                    && in_array($identityNumber, array_filter([
                        $studentFromHiddenField->student_id,
                        $studentFromHiddenField->id_number,
                    ]), true);
                $matchesName = $fullName !== '' && strcasecmp(trim($studentFromHiddenField->name), $fullName) === 0;

                if ($matchesIdentity || $matchesName) {
                    return $studentFromHiddenField;
                }
            }
        }

        if ($identityNumber !== '') {
            $studentFromIdentity = User::query()
                ->where('role', 'student')
                ->where(function ($query) use ($identityNumber) {
                    $query->where('student_id', $identityNumber)
                        ->orWhere('id_number', $identityNumber);
                })
                ->first();

            if ($studentFromIdentity) {
                return $studentFromIdentity;
            }
        }

        if ($fullName !== '') {
            return User::query()
                ->where('role', 'student')
                ->where('name', $fullName)
                ->first();
        }

        return null;
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('yearLeader');

        if (! $this->canManageSupportDesk($user->role)) {
            return redirect()->route('home')->with('error', 'Only Year Leaders, Executive, SSD Assistant 1, and SSD Assistant 2 can access Academic Supports.');
        }

        $canRefer = $user->role === 'yearleader';
        $canManageReferrals = $this->canManageSupportDesk($user->role);
        $canUpdateStatus = $this->canHandleReferredStudents($user->role);
        $canSubmitAbsence = false;
        $canComment = $canManageReferrals;
        $studentDirectory = $canRefer ? $this->studentDirectory() : collect();
        $yearLeaderProfile = $user->yearLeader;
        $referrals = $this->referralQueryForUser($user)
            ->latest()
            ->get();
        $studentProfilesById = $referrals
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->mapWithKeys(fn (User $student) => [$student->id => $this->buildStudentProfile($student)]);
        
        return view('academic.referrals', compact('referrals', 'canRefer', 'canManageReferrals', 'canUpdateStatus', 'canSubmitAbsence', 'canComment', 'studentDirectory', 'studentProfilesById', 'yearLeaderProfile', 'user'));
    }

    public function generateReport(Request $request)
    {
        $user = Auth::user();
        
        if (! $this->canManageSupportDesk($user->role)) {
            return redirect()->route('home')->with('error', 'Only Year Leaders, Executive, SSD Assistant 1, and SSD Assistant 2 can access Academic Supports.');
        }

        $reportType = $request->get('type', 'general');
        $year = $request->get('year', now()->year);
        
        $referrals = $this->referralQueryForUser($user)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        // Calculate statistics based on report type
        $totalReferrals = $referrals->count();
        $pendingReferrals = $referrals->filter(fn ($r) => $r->status === 'pending');
        $reviewedReferrals = $referrals->filter(fn ($r) => $r->status === 'reviewed');
        $resolvedReferrals = $referrals->filter(fn ($r) => $r->status === 'resolved');
        
        $criticalPriority = $referrals->filter(fn ($r) => $r->priority === 'Critical');
        $urgentPriority = $referrals->filter(fn ($r) => $r->priority === 'Urgent');
        $normalPriority = $referrals->filter(fn ($r) => $r->priority === 'Normal');

        // Group by month
        $monthlyData = $referrals->groupBy(fn ($r) => $r->created_at->format('F'))
            ->map(fn ($group) => [
                'total' => $group->count(),
                'pending' => $group->filter(fn ($r) => $r->status === 'pending')->count(),
                'resolved' => $group->filter(fn ($r) => $r->status === 'resolved')->count(),
            ]);

        // Group by year leader/referrer
        $referrerData = $referrals->groupBy(fn ($r) => $r->referrer->name ?? 'Unknown')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        // Group by priority
        $priorityData = [
            'Critical' => $criticalPriority->count(),
            'Urgent' => $urgentPriority->count(),
            'Normal' => $normalPriority->count(),
        ];

        // Status breakdown
        $statusData = [
            'Pending' => $pendingReferrals->count(),
            'Reviewed' => $reviewedReferrals->count(),
            'Resolved' => $resolvedReferrals->count(),
        ];

        return view('academic.report', compact(
            'referrals', 'reportType', 'year',
            'totalReferrals', 'pendingReferrals', 'reviewedReferrals', 'resolvedReferrals',
            'criticalPriority', 'urgentPriority', 'normalPriority',
            'monthlyData', 'referrerData', 'priorityData', 'statusData',
            'user'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'yearleader') {
            return back()->with('error', 'Only Year Leaders can create referrals. Executive and SSD assistants can follow up on existing student cases.');
        }
        
        $validated = $request->validate([
            'student_user_id' => ['nullable', 'integer'],
            'student_first_name' => 'required|string|max:255',
            'student_surname' => 'required|string|max:255',
            'class_name' => 'nullable|string|max:255',
            'programme' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:50',
            'student_identity_number' => 'required|string|max:100',
            'year_of_study' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:100',
            'faculty' => 'nullable|string|max:255',
            'year_leader_name' => 'required|string|max:255',
            'principal_lecturer' => 'nullable|string|max:255',
            'fmg' => 'nullable|string|max:255',
            'priority' => 'required|in:Urgent,Normal',
            'reasons_for_referral' => 'required|string',
            'problem_identified_when' => 'required|string|max:255',
            'action_taken' => 'required|string',
            'referring_lecturer_email' => 'nullable|email|max:255',
            'extension' => 'nullable|string|max:100',
            'signature_name' => 'nullable|string|max:255',
            'referral_date' => 'required|date',
            'group_students_details' => 'nullable|string',
            'group_reasons_for_referral' => 'nullable|string',
            'group_problem_identified_when' => 'nullable|string|max:255',
            'group_action_taken' => 'nullable|string',
            'group_referring_lecturer_name' => 'nullable|string|max:255',
            'group_signature_name' => 'nullable|string|max:255',
            'group_referral_date' => 'nullable|date',
        ]);

        $selectedStudent = $this->resolveReferredStudent($validated);
        $studentProfile = $selectedStudent ? $this->buildStudentProfile($selectedStudent) : [
            'id' => null,
            'name' => trim($validated['student_first_name'] . ' ' . $validated['student_surname']),
            'first_name' => $validated['student_first_name'],
            'surname' => $validated['student_surname'],
            'student_identity_number' => $validated['student_identity_number'],
            'email' => null,
            'faculty' => '',
            'programme' => '',
            'contact_number' => '',
        ];

        StudentReferral::create([
            'student_user_id' => $selectedStudent?->id,
            'student_name' => $studentProfile['name'],
            'student_id' => $studentProfile['student_identity_number'] ?: $validated['student_identity_number'],
            'programme' => ($validated['programme'] ?? null) ?: $studentProfile['programme'] ?: null,
            'reason' => $validated['reasons_for_referral'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'referred_by' => $user->id,
            'entry_type' => 'referral',
            'yearleader_referral_form' => array_merge($validated, [
                'student_record_name' => $studentProfile['name'],
                'student_record_email' => $studentProfile['email'],
                'student_first_name' => $validated['student_first_name'] ?: $studentProfile['first_name'],
                'student_surname' => $validated['student_surname'] ?: $studentProfile['surname'],
                'student_identity_number' => $validated['student_identity_number'] ?: $studentProfile['student_identity_number'],
                'faculty' => ($validated['faculty'] ?? null) ?: $studentProfile['faculty'],
                'programme' => ($validated['programme'] ?? null) ?: $studentProfile['programme'],
                'contact_number' => ($validated['contact_number'] ?? null) ?: $studentProfile['contact_number'],
            ]),
        ]);

        return back()->with('success', 'Student referral submitted successfully!');
    }

    public function updateStatus(Request $request, StudentReferral $referral)
    {
        $user = Auth::user();
        
        if (! $this->canHandleReferredStudents($user->role)) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Only SSD staff can review or resolve referred students.'], 403);
            }

            return back()->with('error', 'Only SSD staff can review or resolve referred students.');
        }
        
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved'
        ]);
        
        $referral->update(['status' => $request->status]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $referral->status]);
        }
        
        return back()->with('success', 'Referral status updated.');
    }

    public function saveAttendanceSheet(Request $request, StudentReferral $referral)
    {
        $user = Auth::user();

        if (! $this->canHandleReferredStudents($user->role)) {
            return back()->with('error', 'Only SSD staff can complete the student attendance form.');
        }

        $validated = $request->validate([
            'student_first_name' => 'required|string|max:255',
            'student_surname' => 'nullable|string|max:255',
            'class_name' => 'nullable|string|max:255',
            'programme' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:50',
            'student_identity_number' => 'required|string|max:100',
            'faculty' => 'nullable|string|max:255',
            'year_leader_name' => 'nullable|string|max:255',
            'principal_lecturer' => 'nullable|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:100',
            'feedback' => 'required|string',
            'group_problems' => 'nullable|string',
            'group_students_feedback' => 'nullable|string',
            'action_taken' => 'required|string',
            'plan_of_action' => 'nullable|string',
            'ssd_officer_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'attended_on' => 'required|date',
            'signature_name' => 'nullable|string|max:255',
        ]);

        $referral->update([
            'ssd_attendance_form' => $validated,
            'ssd_attended_by' => $user->id,
            'ssd_attended_at' => now(),
            'status' => $referral->status === 'pending' ? 'reviewed' : $referral->status,
        ]);

        return back()->with('success', 'SSD attendance form saved successfully.');
    }

    public function addComment(Request $request, StudentReferral $referral)
    {
        $user = Auth::user();
        
        if (! $this->canManageSupportDesk($user->role)) {
            return back()->with('error', 'Only Year Leaders, Executive, SSD Assistant 1, and SSD Assistant 2 can reply to student support entries.');
        }

        if (! $this->canAccessReferral($user, $referral)) {
            return back()->with('error', 'You can only reply to students you referred.');
        }
        
        $request->validate([
            'message' => 'required|string',
            'parent_id' => 'nullable|exists:referral_comments,id'
        ]);
        
        ReferralComment::create([
            'student_referral_id' => $referral->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'parent_id' => $request->parent_id,
        ]);
        
        return back()->with('success', 'Comment added successfully.');
    }
}
