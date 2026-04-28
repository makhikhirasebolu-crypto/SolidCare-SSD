<?php

namespace App\Counselling\Models;

class EmergencyCounsellingModel
{
    public function __construct(
        protected string $identifier,
        protected string $label,
        protected string $provider = 'OpenAI',
        protected int $maxOutputTokens = 350,
    ) {
    }

    public static function fromConfig(): self
    {
        $identifier = trim((string) config('services.openai.emergency_counselling_model', 'gpt-5.4-mini'));
        $label = trim((string) config('services.openai.emergency_counselling_model_label', 'Student Support Chat'));

        return new self(
            $identifier !== '' ? $identifier : 'gpt-5.4-mini',
            $label !== '' ? $label : 'Student Support Chat',
        );
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function maxOutputTokens(): int
    {
        return $this->maxOutputTokens;
    }

    public function description(): string
    {
        return 'Available inside Student Chat Board for calm, practical support before a booked counselling session.';
    }

    public function instructions(): string
    {
        return 'You are SolidCare student support chat for university students. '
            . 'Provide calm, supportive, non-judgmental guidance in clear everyday language. '
            . 'Focus on practical help for exam stress, workload, relationships, money worries, and feeling overwhelmed. '
            . 'Keep replies concise, usually 4 to 6 sentences, give concrete next steps when possible, and ask at most one follow-up question. '
            . 'Do not diagnose conditions, do not provide medical or legal advice, and do not claim to be a human counsellor. '
            . 'If the student mentions suicide, self-harm, harming others, abuse in progress, overdose, severe panic, or immediate danger, clearly and calmly tell them to contact local emergency services, campus security, the clinic, or a trusted adult right now and seek in-person help immediately. '
            . 'When the concern needs sustained human follow-up, encourage the student to use Book Session for a counsellor appointment.';
    }

    public function buildPayload(string $message, array $history = []): array
    {
        return [
            'model' => $this->identifier(),
            'instructions' => $this->instructions(),
            'input' => array_merge($history, [[
                'role' => 'user',
                'content' => trim($message),
            ]]),
            'max_output_tokens' => $this->maxOutputTokens(),
        ];
    }
}
