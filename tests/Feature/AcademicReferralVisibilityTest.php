<?php

namespace Tests\Feature;

use App\Models\ReferralComment;
use App\Models\StudentReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicReferralVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_yearleader_sees_all_referred_students_on_referral_desk(): void
    {
        [$yearLeader, $otherYearLeader] = $this->createYearLeaders();

        $ownReferral = $this->createReferral($yearLeader, 'Own Referred Student');
        $otherReferral = $this->createReferral($otherYearLeader, 'Other Referred Student');

        $response = $this->actingAs($yearLeader)->get(route('academic.referrals'));

        $response
            ->assertOk()
            ->assertSee($ownReferral->student_name)
            ->assertSee($otherReferral->student_name)
            ->assertSee(route('academic.referrals.comment', $ownReferral), false)
            ->assertDontSee(route('academic.referrals.comment', $otherReferral), false);
    }

    public function test_yearleader_report_contains_all_referred_students(): void
    {
        [$yearLeader, $otherYearLeader] = $this->createYearLeaders();

        $ownReferral = $this->createReferral($yearLeader, 'Own Report Student');
        $otherReferral = $this->createReferral($otherYearLeader, 'Other Report Student');

        $response = $this->actingAs($yearLeader)->get(route('academic.referrals.report', [
            'year' => now()->year,
        ]));

        $response
            ->assertOk()
            ->assertSee($ownReferral->student_name)
            ->assertSee($otherReferral->student_name);
    }

    public function test_yearleader_cannot_comment_on_another_yearleaders_referral(): void
    {
        [$yearLeader, $otherYearLeader] = $this->createYearLeaders();
        $otherReferral = $this->createReferral($otherYearLeader, 'Other Comment Student');

        $response = $this->actingAs($yearLeader)
            ->from(route('academic.referrals'))
            ->post(route('academic.referrals.comment', $otherReferral), [
                'message' => 'This should not be allowed.',
            ]);

        $response
            ->assertRedirect(route('academic.referrals'))
            ->assertSessionHas('error', 'You can only reply to students you referred.');

        $this->assertDatabaseMissing('referral_comments', [
            'student_referral_id' => $otherReferral->id,
            'user_id' => $yearLeader->id,
            'message' => 'This should not be allowed.',
        ]);
    }

    public function test_yearleader_can_comment_on_own_referral(): void
    {
        [$yearLeader] = $this->createYearLeaders();
        $ownReferral = $this->createReferral($yearLeader, 'Own Comment Student');

        $response = $this->actingAs($yearLeader)
            ->from(route('academic.referrals'))
            ->post(route('academic.referrals.comment', $ownReferral), [
                'message' => 'Following up with SSD.',
            ]);

        $response
            ->assertRedirect(route('academic.referrals'))
            ->assertSessionHas('success', 'Comment added successfully.');

        $this->assertDatabaseHas('referral_comments', [
            'student_referral_id' => $ownReferral->id,
            'user_id' => $yearLeader->id,
            'message' => 'Following up with SSD.',
        ]);
    }

    public function test_yearleader_selects_referral_priority_when_creating_referral(): void
    {
        [$yearLeader] = $this->createYearLeaders();

        $response = $this->actingAs($yearLeader)
            ->from(route('academic.referrals'))
            ->post(route('academic.referrals.store'), [
                'student_first_name' => 'Urgent',
                'student_surname' => 'Student',
                'student_identity_number' => 'urgent-student-001',
                'year_leader_name' => $yearLeader->name,
                'priority' => 'Urgent',
                'reasons_for_referral' => 'Needs urgent academic support.',
                'problem_identified_when' => 'Today',
                'action_taken' => 'Spoke with the student.',
                'referral_date' => now()->toDateString(),
            ]);

        $response
            ->assertRedirect(route('academic.referrals'))
            ->assertSessionHas('success', 'Student referral submitted successfully!');

        $this->assertDatabaseHas('student_referrals', [
            'student_name' => 'Urgent Student',
            'student_id' => 'urgent-student-001',
            'priority' => 'Urgent',
            'referred_by' => $yearLeader->id,
        ]);
    }

    private function createYearLeaders(): array
    {
        return [
            User::create([
                'name' => 'Year Leader One',
                'email' => 'yearleader.one@example.com',
                'password' => 'password123',
                'role' => 'yearleader',
            ]),
            User::create([
                'name' => 'Year Leader Two',
                'email' => 'yearleader.two@example.com',
                'password' => 'password123',
                'role' => 'yearleader',
            ]),
        ];
    }

    private function createReferral(User $referrer, string $studentName): StudentReferral
    {
        return StudentReferral::create([
            'student_name' => $studentName,
            'student_id' => str_replace(' ', '-', strtolower($studentName)),
            'programme' => 'BSc (Hons) in Information Technology',
            'entry_type' => 'referral',
            'reason' => 'Academic support needed.',
            'priority' => 'Normal',
            'status' => 'pending',
            'referred_by' => $referrer->id,
            'yearleader_referral_form' => [
                'student_first_name' => strtok($studentName, ' '),
                'student_surname' => trim(strstr($studentName, ' ') ?: ''),
            ],
        ]);
    }
}
