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

    public function test_yearleader_only_sees_students_they_referred_on_referral_desk(): void
    {
        [$yearLeader, $otherYearLeader] = $this->createYearLeaders();

        $ownReferral = $this->createReferral($yearLeader, 'Own Referred Student');
        $otherReferral = $this->createReferral($otherYearLeader, 'Other Referred Student');

        $response = $this->actingAs($yearLeader)->get(route('academic.referrals'));

        $response
            ->assertOk()
            ->assertSee($ownReferral->student_name)
            ->assertDontSee($otherReferral->student_name);
    }

    public function test_yearleader_report_only_contains_students_they_referred(): void
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
            ->assertDontSee($otherReferral->student_name);
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
