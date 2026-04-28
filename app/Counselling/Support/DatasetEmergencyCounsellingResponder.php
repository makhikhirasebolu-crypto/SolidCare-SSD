<?php

namespace App\Counselling\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ZipArchive;

class DatasetEmergencyCounsellingResponder
{
    protected const MODEL_ID = 'student-support-chat-v2';
    protected const MODEL_LABEL = 'Student Support Chat';
    protected const MODEL_PROVIDER = 'SolidCare Local';
    protected const MODEL_DESCRIPTION = 'Conversation-based support for study pressure, workload, relationships, finances, and wellbeing.';
    protected const CRISIS_RESPONSE = 'If you might act on thoughts of harming yourself or someone else, or you are unsafe right now, please stop chatting and contact campus security, local emergency services, the clinic, or a trusted adult immediately. If possible, move toward another person right now. Once you are safe, you can come back here or use Book Session for follow-up support.';
    protected const REQUIRED_COLUMNS = ['User_Input', 'Intent', 'Context', 'AI_Response', 'Next_Action'];

    public function meta(): array
    {
        return [
            'model' => self::MODEL_ID,
            'model_label' => self::MODEL_LABEL,
            'model_provider' => self::MODEL_PROVIDER,
            'model_description' => self::MODEL_DESCRIPTION,
        ];
    }

    public function respond(string $message, array $history = []): array
    {
        $message = trim($message);
        $history = $this->normalizeHistory($history);

        if ($this->looksLikeImmediateDanger($message)) {
            return $this->payload(self::CRISIS_RESPONSE);
        }

        if ($this->isGreeting($message) && $this->recentUserMessages($history, 1) === []) {
            return $this->payload(
                'Hi. I am here to help you think this through. Tell me what feels hardest right now: exams, workload, relationships, money, or just feeling overwhelmed.'
            );
        }

        $bestMatch = $this->findBestMatch($message, $history);
        $intent = $this->inferIntent($message, $history, $bestMatch);
        $reply = $this->buildConversationalReply($message, $history, $intent, $bestMatch);

        return $this->payload($reply);
    }

    protected function payload(string $reply): array
    {
        return array_merge($this->meta(), [
            'reply' => $this->cleanText($reply),
        ]);
    }

    protected function buildConversationalReply(string $message, array $history, string $intent, ?array $bestMatch): string
    {
        $normalizedMessage = $this->normalize($message);
        $lastAssistant = $this->lastMessageByRole($history, 'assistant') ?? '';

        if ($this->isAffirmative($normalizedMessage)) {
            if ($this->promptedForPlanner($lastAssistant)) {
                return $this->buildWeeklyPlannerReply();
            }

            if ($this->promptedForPomodoro($lastAssistant)) {
                return $this->buildPomodoroReply();
            }

            if ($this->promptedForDraft($lastAssistant)) {
                return $this->buildDraftReply($intent);
            }

            return $this->buildAffirmativeFollowUp($intent);
        }

        if ($this->mentionsPastPerformance($normalizedMessage) || $this->mentionsFailedAssessments($normalizedMessage)) {
            return $this->buildPastPerformanceReply();
        }

        if ($this->mentionsPlanner($normalizedMessage) || ($this->isWalkthroughRequest($normalizedMessage) && $intent === 'academic')) {
            return $this->buildWeeklyPlannerReply();
        }

        if ($this->mentionsNotesHelp($normalizedMessage)) {
            return $this->buildNotesReply();
        }

        if ($this->wantsDetailedAdvice($normalizedMessage)) {
            return $this->buildDetailedReply($intent);
        }

        return match ($intent) {
            'project' => $this->buildProjectReply($message),
            'group' => $this->buildGroupWorkReply(),
            'academic' => $this->buildAcademicReply($message, $bestMatch),
            'relationship' => $this->buildRelationshipReply(),
            'financial' => $this->buildFinancialReply(),
            'low_mood' => $this->buildLowMoodReply(),
            'stress' => $this->buildStressReply(),
            default => $bestMatch !== null
                ? $this->buildReplyFromSample($bestMatch)
                : 'I can help with exam pressure, workload, relationships, money worries, or feeling overwhelmed. Tell me a bit more about what has been toughest this week, and I will give you practical next steps.',
        };
    }

    protected function inferIntent(string $message, array $history, ?array $bestMatch): string
    {
        $conversation = $this->normalize($this->conversationText($message, $history));

        if ($this->mentionsProjectScope($conversation)) {
            return 'project';
        }

        if ($this->mentionsGroupWork($conversation)) {
            return 'group';
        }

        if ($this->mentionsAcademicConcern($conversation)) {
            return 'academic';
        }

        if ($this->mentionsRelationshipConcern($conversation)) {
            return 'relationship';
        }

        if ($this->mentionsFinancialConcern($conversation)) {
            return 'financial';
        }

        if ($this->mentionsLowMood($conversation)) {
            return 'low_mood';
        }

        if ($this->mentionsStressConcern($conversation)) {
            return 'stress';
        }

        if ($bestMatch === null) {
            return 'general';
        }

        return match (Str::lower((string) ($bestMatch['intent'] ?? ''))) {
            'academic' => 'academic',
            'relationship' => 'relationship',
            'financial' => 'financial',
            'depression' => 'low_mood',
            'stress' => 'stress',
            default => 'general',
        };
    }

    protected function buildAcademicReply(string $message, ?array $bestMatch): string
    {
        $normalizedMessage = $this->normalize($message);

        if ($this->mentionsPastPerformance($normalizedMessage) || $this->mentionsFailedAssessments($normalizedMessage)) {
            return $this->buildPastPerformanceReply();
        }

        if ($this->mentionsPlanner($normalizedMessage)) {
            return $this->buildWeeklyPlannerReply();
        }

        if ($this->mentionsNotesHelp($normalizedMessage)) {
            return $this->buildNotesReply();
        }

        if ($this->mentionsExamSpecificConcern($normalizedMessage)) {
            return 'I hear you. Final exams can feel heavy, especially when confidence is low. Start with the hardest module first, list the topics you feel weakest in, and give one weak topic a focused 45-minute session today instead of trying to cover everything at once. After that, do one timed question or a short past-paper section so you can see exactly where you lose marks. If you want, send me your modules and exam dates and I will help you build a revision plan.';
        }

        if ($bestMatch !== null) {
            return $this->buildReplyFromSample($bestMatch);
        }

        return 'Let us make this academic pressure more manageable. Pick one module, list the next deadline or exam date, and tell me the topic you feel least ready for. From there, I can help you turn it into a realistic study plan.';
    }

    protected function buildProjectReply(string $message): string
    {
        if ($this->mentionsPlanner($this->normalize($message))) {
            return $this->buildWeeklyPlannerReply();
        }

        return 'That sounds like scope creep, and it can swallow the time you need for other modules. Freeze the project at what is required to pass before you do anything extra: write down the current tasks, mark them as must-do or optional, and push new requests into a later list instead of acting on them immediately. Then protect fixed study blocks this week for your other subjects so the project does not consume everything. If you want, I can help you build a weekly timetable or draft a message to your supervisor.';
    }

    protected function buildGroupWorkReply(): string
    {
        return 'That is frustrating, and you should not have to carry the whole group. Keep a written record of tasks, deadlines, and who responded, then send one clear message assigning responsibilities with a firm cutoff time. If nobody contributes, escalate early to the lecturer with the evidence instead of waiting until submission week. At the same time, protect your own study hours so the group issue does not damage your individual modules. If you want, I can help you draft the message.';
    }

    protected function buildRelationshipReply(): string
    {
        return 'That sounds draining. Try to name the exact pattern that is hurting you most: poor communication, pressure, conflict, or lack of support. Once you know the main issue, focus on one calm conversation about that single point instead of every frustration at once. If a boundary is needed, make it clear and specific rather than vague. If you want, tell me what happened and I will help you plan what to say next.';
    }

    protected function buildFinancialReply(): string
    {
        return 'Money stress can make everything else feel harder. Start with the most urgent pressure first: fees, rent, food, or transport. Then check what support your university offers, such as bursaries, hardship funds, payment plans, or student support services. If you need to contact the finance office, keep the message short: explain the problem, the deadline, and the help you are asking for. If you want, tell me which cost is most urgent and I will help you draft that message.';
    }

    protected function buildLowMoodReply(): string
    {
        return 'I am sorry you are carrying that. When your mood is low, even small tasks can feel heavy, so keep the next step very small: drink some water, move away from isolation if you can, and choose one manageable task for the next hour, like showering, eating, or opening one page of notes. You do not need to solve everything tonight. If this feeling has been strong for days or is getting worse, Book Session would be a good next step so you can speak with a counsellor properly.';
    }

    protected function buildStressReply(): string
    {
        return 'I hear you. When stress piles up, the goal is to make the next step small enough to start. Write down the three things pressing on you most, then choose the one that matters most today and ignore the rest for the next 30 minutes. After that first block, reassess instead of trying to solve the whole week at once. If you want, send me the three things and I will help you decide what to do first.';
    }

    protected function buildPastPerformanceReply(): string
    {
        return 'That makes sense. Poor results in early assessments can knock your confidence, but they also show us where the repair work should go. Start with three things today: 1) list the two assessments you failed and the topics each one tested, 2) check the feedback or marking guide so you can see the exact gaps, and 3) choose one weak topic for a focused 45-minute repair session using your notes and one past-paper question. After that, we can turn the remaining weeks into a simple recovery plan by module. If you want, send me the modules and exam dates and I will help you structure the plan.';
    }

    protected function buildWeeklyPlannerReply(): string
    {
        return 'Yes. Use a simple weekly planner, not a perfect one. First, place your fixed commitments: classes, work, travel, sleep, and meals. Next, block two high-focus sessions for your hardest module during the times of day you concentrate best, then add one shorter session for review, notes, or coursework. Give each study block a specific outcome like "summarise topic 3" or "do two past-paper questions" rather than just writing "study". Leave one catch-up block near the weekend in case something slips. If you want, send me your modules and exam dates and I will help you map the week.';
    }

    protected function buildNotesReply(): string
    {
        return 'I can help you work directly from your notes. Try this method: 1) split each module into topics, 2) mark each topic green, amber, or red based on confidence, 3) turn the red topics into one-page summary sheets, 4) after each summary, answer one question from memory without looking, and 5) finish with a past-paper or tutorial question. That turns your notes into revision tools instead of pages to reread passively. If you want, tell me the module and I will help you convert one set of notes into a revision plan.';
    }

    protected function buildDetailedReply(string $intent): string
    {
        return match ($intent) {
            'academic', 'project', 'group' => 'Here is a more detailed approach. For exams, focus on high-yield topics first by checking past papers and lecturer emphasis. For poor results, compare your answers against the marking guide so you can tell whether the issue is content knowledge, exam technique, or time management. For workload, plan in 60 to 90 minute blocks with one clear outcome per block, and schedule short review sessions every two or three days so the material sticks. If group work is adding pressure, protect fixed study hours for your individual modules before giving extra time to the project. If you want, send me your modules, deadlines, and exam dates and I will help you turn that into a weekly plan.',
            'financial' => 'Here is a fuller way to handle the money side. Separate the problem into urgent costs, upcoming deadlines, and support options. Contact the university office that controls the most urgent issue first, ask clearly about payment plans or hardship funding, and keep copies of every email. If there are several costs at once, build a short list in order: what must be paid now, what can be delayed, and what support you can apply for this week. If you want, tell me the most urgent cost and I will help you write the message.',
            'relationship' => 'Here is a more detailed way to approach it. Start by writing down the exact behaviour that is hurting you and one example of when it happened. Decide what you want from the conversation: clarity, change, apology, or space. When you speak, keep it to one issue at a time, describe the impact on you, and say clearly what needs to change next. If you want, tell me what happened and I will help you phrase it.',
            default => 'Got it. You want practical advice, not just a few questions. Start by naming the one area causing the most strain right now, then break it into the next concrete steps you can take this week. Once the first step is clear, the rest usually becomes easier to plan. If you want, tell me the area and I will make the advice more specific.',
        };
    }

    protected function buildPomodoroReply(): string
    {
        return 'Here is a simple Pomodoro structure. Pick one task only, set a timer for 25 minutes, and work without checking your phone or switching tabs. When the timer ends, take a 5-minute break, then repeat that cycle four times before taking a longer break of 15 to 20 minutes. The key is to define the task clearly before you start, for example "revise topic 2" or "answer question 1". If you want, I can help you turn your next study session into Pomodoro blocks.';
    }

    protected function buildAffirmativeFollowUp(string $intent): string
    {
        return match ($intent) {
            'academic', 'project', 'group' => 'Good. Send me your modules, exam dates, or top three deadlines, and I will help you turn them into a practical study plan.',
            'financial' => 'Okay. Tell me which cost is most urgent right now, such as fees, rent, food, or transport, and I will help you decide the next step.',
            'relationship' => 'Okay. Tell me the main issue you want to handle first, and I will help you plan what to say or do next.',
            'low_mood', 'stress' => 'Okay. Tell me the one thing that feels heaviest right now, and I will help you make the next step smaller and clearer.',
            default => 'Okay. Tell me the main issue you want help with first, and I will make the next step specific.',
        };
    }

    protected function buildDraftReply(string $intent): string
    {
        return match ($intent) {
            'financial' => 'You can send something like this: "Hello, I am a continuing student and I am experiencing financial difficulty that is affecting my ability to meet my current payments. I would like to ask whether there is a payment plan, hardship support, or another option available. Please let me know what information you need from me." If you want, I can help you tailor it for fees, rent, or another specific cost.',
            'group' => 'You can send something like this: "Hi team, we need to finalise responsibilities today so the assignment does not fall behind. Please confirm your part and what you will submit by tomorrow at [time]. If I do not hear back, I will need to update the lecturer on the current contribution status so the work is tracked fairly." If you want, I can make it firmer or more polite.',
            default => 'You can send something like this: "Hello, I need some support with a study issue that is affecting my performance right now. I would appreciate a chance to discuss the problem and possible next steps. Please let me know when you are available." If you want, I can tailor it for a lecturer, supervisor, or student support office.',
        };
    }

    protected function buildReplyFromSample(array $sample): string
    {
        $reply = trim((string) ($sample['ai_response'] ?? ''));
        $intent = Str::lower((string) ($sample['intent'] ?? ''));
        $context = Str::lower((string) ($sample['context'] ?? ''));
        $nextAction = trim((string) ($sample['next_action'] ?? ''));

        if ($reply === '') {
            return 'Tell me a bit more about what is going on, and I will help you think through the next step.';
        }

        if (in_array($nextAction, ['Book_Session', 'Offer_Session'], true) && in_array($intent, ['depression', 'stress'], true) && in_array($context, ['severe', 'high', 'health'], true)) {
            return $reply . ' If this keeps feeling heavy, Book Session is there if you want proper follow-up support.';
        }

        return $reply;
    }

    protected function normalizeHistory(array $history): array
    {
        $normalized = [];

        foreach ($history as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role = trim((string) ($message['role'] ?? ''));
            $content = trim((string) ($message['content'] ?? ''));
            if (! in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        return $normalized;
    }

    protected function recentUserMessages(array $history, int $limit): array
    {
        $messages = [];

        foreach (array_reverse($history) as $entry) {
            if (($entry['role'] ?? null) !== 'user') {
                continue;
            }

            $messages[] = (string) $entry['content'];
            if (count($messages) >= $limit) {
                break;
            }
        }

        return array_reverse($messages);
    }

    protected function lastMessageByRole(array $history, string $role): ?string
    {
        foreach (array_reverse($history) as $entry) {
            if (($entry['role'] ?? null) === $role) {
                return (string) $entry['content'];
            }
        }

        return null;
    }

    protected function conversationText(string $message, array $history): string
    {
        $parts = $this->recentUserMessages($history, 3);
        $lastAssistant = $this->lastMessageByRole($history, 'assistant');

        if ($lastAssistant !== null) {
            $parts[] = $lastAssistant;
        }

        $parts[] = $message;

        return implode(' ', $parts);
    }

    protected function findBestMatch(string $message, array $history = []): ?array
    {
        $queries = [trim($message)];
        $recentUserText = implode(' ', $this->recentUserMessages($history, 2));
        if ($recentUserText !== '') {
            $queries[] = trim($recentUserText . ' ' . $message);
        }

        $lastAssistant = $this->lastMessageByRole($history, 'assistant');
        if ($lastAssistant !== null) {
            $queries[] = trim($message . ' ' . $lastAssistant);
        }

        $bestMatch = null;
        $bestScore = 0.0;

        foreach ($queries as $query) {
            $normalizedQuery = $this->normalize($query);
            $queryTokens = $this->tokenize($query);
            if ($normalizedQuery === '' || $queryTokens === []) {
                continue;
            }

            foreach ($this->samples() as $sample) {
                $score = $this->scoreSample($normalizedQuery, $queryTokens, $sample);
                if ($score <= $bestScore) {
                    continue;
                }

                $bestScore = $score;
                $bestMatch = $sample;
            }
        }

        $threshold = $this->isShortFollowUp($message) ? 1.6 : 2.2;

        return $bestScore >= $threshold ? $bestMatch : null;
    }

    protected function scoreSample(string $normalizedMessage, array $messageTokens, array $sample): float
    {
        $sampleInput = $sample['normalized_input'];
        $sampleSearch = $sample['normalized_search'];
        $sampleTokens = $sample['tokens'];

        if ($normalizedMessage === $sampleInput) {
            return 1000.0;
        }

        $overlapCount = count(array_intersect($messageTokens, $sampleTokens));
        $unionCount = count(array_unique(array_merge($messageTokens, $sampleTokens)));
        $jaccard = $unionCount > 0 ? $overlapCount / $unionCount : 0.0;

        $score = ($overlapCount * 1.8) + ($jaccard * 12.0);

        if ($sampleInput !== '' && str_contains($normalizedMessage, $sampleInput)) {
            $score += 8.0;
        }

        if ($sampleInput !== '' && str_contains($sampleInput, $normalizedMessage)) {
            $score += 4.0;
        }

        if ($sampleSearch !== '' && str_contains($normalizedMessage, $sampleSearch)) {
            $score += 2.5;
        }

        if (($sample['context_normalized'] ?? '') !== '' && str_contains($normalizedMessage, $sample['context_normalized'])) {
            $score += 1.0;
        }

        return $score;
    }

    protected function isGreeting(string $message): bool
    {
        return in_array($this->normalize($message), ['hello', 'hey', 'hi', 'good morning', 'good afternoon', 'good evening'], true);
    }

    protected function isAffirmative(string $normalizedMessage): bool
    {
        return in_array($normalizedMessage, ['yes', 'yeah', 'yep', 'sure', 'okay', 'ok', 'please', 'yes please'], true);
    }

    protected function isShortFollowUp(string $message): bool
    {
        return count($this->tokenize($message)) <= 4;
    }

    protected function promptedForPlanner(string $lastAssistant): bool
    {
        $normalized = $this->normalize($lastAssistant);

        return $this->containsAny($normalized, ['study plan', 'weekly planner', 'weekly timetable', 'revision timetable', 'focused study plan', 'walk you through making a weekly planner']);
    }

    protected function promptedForPomodoro(string $lastAssistant): bool
    {
        return str_contains($this->normalize($lastAssistant), 'pomodoro');
    }

    protected function promptedForDraft(string $lastAssistant): bool
    {
        $normalized = $this->normalize($lastAssistant);

        return $this->containsAny($normalized, ['draft a message', 'draft a message to your teacher', 'draft an email', 'help you draft']);
    }

    protected function isWalkthroughRequest(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['walk me through', 'guide me through', 'show me how']);
    }

    protected function mentionsPlanner(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['planner', 'timetable', 'study plan', 'weekly plan', 'revision plan', 'schedule']);
    }

    protected function mentionsNotesHelp(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['notes', 'revise from my notes', 'inline with my notes', 'revision notes']);
    }

    protected function mentionsPastPerformance(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['past performance', 'bad performance', 'poor performance', 'low marks', 'low grades']);
    }

    protected function mentionsFailedAssessments(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['failed both', 'failed both of them', 'failed 2 assessments', 'failed two assessments', 'i am failing', 'failing my modules', 'failed my assessments']);
    }

    protected function wantsDetailedAdvice(string $normalizedMessage): bool
    {
        return $this->containsAny($normalizedMessage, ['detailed advice', 'broader', 'go deeper', 'more detail', 'more detailed', 'not just questions', 'actionable advice']);
    }

    protected function mentionsProjectScope(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['scope creep', 'project keeps expanding', 'project keeps having scope creep', 'freeze changes', 'supervisor', 'other subjects']);
    }

    protected function mentionsGroupWork(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['group members', 'group assignment', 'group project', 'members do not participate', 'nobody contributes', 'carry the whole group']);
    }

    protected function mentionsAcademicConcern(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['exam', 'exams', 'final', 'finals', 'module', 'modules', 'assessment', 'assessments', 'studying', 'study', 'coursework', 'revision', 'notes', 'professor', 'lecturer', 'subject']);
    }

    protected function mentionsExamSpecificConcern(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['final exam', 'final exams', 'exam', 'exams', 'assessment', 'assessments', 'failing my modules', 'afraid i ll fail']);
    }

    protected function mentionsRelationshipConcern(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['relationship', 'partner', 'breakup', 'friends', 'friend', 'argue', 'alone in my relationship']);
    }

    protected function mentionsFinancialConcern(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['money', 'fees', 'rent', 'expenses', 'financial', 'bursary', 'hardship fund', 'tuition']);
    }

    protected function mentionsLowMood(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['hopeless', 'empty', 'down all the time', 'feel down', 'depressed', 'worthless']);
    }

    protected function mentionsStressConcern(string $normalizedText): bool
    {
        return $this->containsAny($normalizedText, ['stress', 'stressed', 'pressure', 'overwhelmed', 'confused', 'anxious', 'anxiety']);
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function samples(): array
    {
        $datasetPath = $this->resolveDatasetPath();
        if ($datasetPath === null) {
            return [];
        }

        $signature = md5($datasetPath . '|' . (string) @filesize($datasetPath) . '|' . (string) @filemtime($datasetPath));

        return Cache::rememberForever('counselling-dataset-samples:' . $signature, function () use ($datasetPath) {
            $extension = Str::lower(pathinfo($datasetPath, PATHINFO_EXTENSION));

            if ($extension === 'csv') {
                return $this->parseCsvDataset($datasetPath);
            }

            if ($extension === 'xlsx' && class_exists(ZipArchive::class)) {
                return $this->parseXlsxDataset($datasetPath);
            }

            return [];
        });
    }

    protected function resolveDatasetPath(): ?string
    {
        $preferredPaths = [
            storage_path('app/training-datasets/counselling.csv'),
            storage_path('app/training-datasets/counselling_training_15000.xlsx'),
        ];

        foreach ($preferredPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function parseCsvDataset(string $datasetPath): array
    {
        $handle = @fopen($datasetPath, 'rb');
        if ($handle === false) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        if ($rows === []) {
            return [];
        }

        $headerIndex = null;
        $headers = [];
        foreach ($rows as $index => $row) {
            $trimmedRow = array_map(static fn ($value) => trim((string) $value), $row);
            if (count(array_intersect(self::REQUIRED_COLUMNS, $trimmedRow)) === count(self::REQUIRED_COLUMNS)) {
                $headerIndex = $index;
                $headers = $trimmedRow;
                break;
            }
        }

        if ($headerIndex === null || $headers === []) {
            return [];
        }

        $samples = [];
        for ($index = $headerIndex + 1, $rowCount = count($rows); $index < $rowCount; $index++) {
            $row = $rows[$index];
            if (! is_array($row) || count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $record = [];
            foreach ($headers as $position => $header) {
                if ($header === '') {
                    continue;
                }

                $record[$header] = trim((string) ($row[$position] ?? ''));
            }

            $sample = $this->sampleFromRecord($record);
            if ($sample !== null) {
                $samples[] = $sample;
            }
        }

        return $samples;
    }

    protected function parseXlsxDataset(string $datasetPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($datasetPath) !== true) {
            return [];
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheetPath = $this->firstSheetPath($zip);
        if ($sheetPath === null) {
            $zip->close();

            return [];
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            return [];
        }

        $sheetDocument = $this->loadXmlDocument($sheetXml);
        if ($sheetDocument === null) {
            return [];
        }

        $xpath = new DOMXPath($sheetDocument);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = $xpath->query('//main:sheetData/main:row');
        if ($rows === false || $rows->length === 0) {
            return [];
        }

        $headersByColumn = [];
        $samples = [];

        foreach ($rows as $rowIndex => $rowNode) {
            if (! $rowNode instanceof DOMElement) {
                continue;
            }

            $rowValues = [];
            foreach ($xpath->query('main:c', $rowNode) ?: [] as $cellNode) {
                if (! $cellNode instanceof DOMElement) {
                    continue;
                }

                $reference = (string) $cellNode->getAttribute('r');
                $column = preg_replace('/\d+/', '', $reference);
                if (! is_string($column) || $column === '') {
                    continue;
                }

                $rowValues[$column] = $this->readCellValue($xpath, $cellNode, $sharedStrings);
            }

            if ($rowIndex === 0) {
                foreach ($rowValues as $column => $value) {
                    if ($value !== '') {
                        $headersByColumn[$column] = $value;
                    }
                }
                continue;
            }

            $record = [];
            foreach ($headersByColumn as $column => $header) {
                $record[$header] = trim((string) ($rowValues[$column] ?? ''));
            }

            $sample = $this->sampleFromRecord($record);
            if ($sample !== null) {
                $samples[] = $sample;
            }
        }

        return $samples;
    }

    protected function sampleFromRecord(array $record): ?array
    {
        $userInput = trim((string) ($record['User_Input'] ?? ''));
        $aiResponse = trim((string) ($record['AI_Response'] ?? ''));
        if ($userInput === '' || $aiResponse === '') {
            return null;
        }

        $context = trim((string) ($record['Context'] ?? ''));
        $searchText = trim($userInput . ' ' . $context);

        return [
            'user_input' => $userInput,
            'intent' => trim((string) ($record['Intent'] ?? '')),
            'context' => $context,
            'ai_response' => $aiResponse,
            'next_action' => trim((string) ($record['Next_Action'] ?? '')),
            'normalized_input' => $this->normalize($userInput),
            'normalized_search' => $this->normalize($searchText),
            'context_normalized' => $this->normalize($context),
            'tokens' => $this->tokenize($searchText),
        ];
    }

    protected function firstSheetPath(ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! is_string($workbookXml) || ! is_string($relationshipsXml)) {
            return null;
        }

        $workbookDocument = $this->loadXmlDocument($workbookXml);
        $relationshipsDocument = $this->loadXmlDocument($relationshipsXml);
        if ($workbookDocument === null || $relationshipsDocument === null) {
            return null;
        }

        $workbookXpath = new DOMXPath($workbookDocument);
        $workbookXpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbookXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $sheetNode = $workbookXpath->query('//main:sheets/main:sheet')->item(0);
        if (! $sheetNode instanceof DOMElement) {
            return null;
        }

        $relationshipId = $sheetNode->getAttributeNS(
            'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
            'id'
        );
        if ($relationshipId === '') {
            return null;
        }

        $relationshipsXpath = new DOMXPath($relationshipsDocument);
        $relationshipsXpath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

        foreach ($relationshipsXpath->query('//rel:Relationship') ?: [] as $relationshipNode) {
            if (! $relationshipNode instanceof DOMElement) {
                continue;
            }

            if ($relationshipNode->getAttribute('Id') !== $relationshipId) {
                continue;
            }

            $target = ltrim($relationshipNode->getAttribute('Target'), '/');
            if ($target === '') {
                return null;
            }

            return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
        }

        return null;
    }

    protected function sharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if (! is_string($sharedStringsXml) || $sharedStringsXml === '') {
            return [];
        }

        $document = $this->loadXmlDocument($sharedStringsXml);
        if ($document === null) {
            return [];
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $values = [];
        foreach ($xpath->query('//main:si') ?: [] as $sharedItemNode) {
            if (! $sharedItemNode instanceof DOMElement) {
                continue;
            }

            $textParts = [];
            foreach ($xpath->query('.//main:t', $sharedItemNode) ?: [] as $textNode) {
                $textParts[] = $textNode->textContent;
            }

            $values[] = implode('', $textParts);
        }

        return $values;
    }

    protected function readCellValue(DOMXPath $xpath, DOMElement $cellNode, array $sharedStrings): string
    {
        $cellType = $cellNode->getAttribute('t');
        if ($cellType === 'inlineStr') {
            $parts = [];
            foreach ($xpath->query('.//main:t', $cellNode) ?: [] as $textNode) {
                $parts[] = $textNode->textContent;
            }

            return trim(implode('', $parts));
        }

        $valueNode = $xpath->query('main:v', $cellNode)->item(0);
        if (! $valueNode) {
            return '';
        }

        $value = trim($valueNode->textContent);
        if ($value === '') {
            return '';
        }

        if ($cellType === 's') {
            $index = (int) $value;

            return trim((string) ($sharedStrings[$index] ?? ''));
        }

        return $value;
    }

    protected function loadXmlDocument(string $xml): ?DOMDocument
    {
        $document = new DOMDocument();

        return $document->loadXML($xml, LIBXML_NONET) ? $document : null;
    }

    protected function normalize(string $text): string
    {
        $text = $this->cleanText($text);
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN\s]+/u', ' ', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    protected function tokenize(string $text): array
    {
        $normalized = $this->normalize($text);
        if ($normalized === '') {
            return [];
        }

        $stopWords = [
            'a', 'am', 'an', 'and', 'are', 'at', 'be', 'but', 'can', 'do', 'feel',
            'for', 'from', 'i', 'im', 'in', 'is', 'it', 'just', 'me', 'my', 'of',
            'on', 'or', 'so', 'that', 'the', 'to', 'what', 'with', 'you',
        ];

        $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($tokens)) {
            return [];
        }

        $filtered = array_values(array_filter($tokens, function (string $token) use ($stopWords) {
            return ! in_array($token, $stopWords, true) && Str::length($token) > 1;
        }));

        return array_values(array_unique($filtered));
    }

    protected function cleanText(string $text): string
    {
        $text = str_replace(
            ['â€“', 'â€”', 'â€‘', 'â€™', 'â€œ', 'â€\x9d', 'â€¦', 'Â', '**'],
            ['-', '-', '-', "'", '"', '"', '...', '', ''],
            $text
        );
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    protected function looksLikeImmediateDanger(string $message): bool
    {
        $normalized = $this->normalize($message);
        if ($normalized === '') {
            return false;
        }

        $dangerSignals = [
            'abuse',
            'can t breathe',
            'cut myself',
            'end my life',
            'harm myself',
            'harm someone',
            'immediate danger',
            'kill myself',
            'kill someone',
            'overdose',
            'panic attack',
            'rape',
            'self harm',
            'suicide',
            'want to die',
        ];

        return $this->containsAny($normalized, $dangerSignals);
    }
}
