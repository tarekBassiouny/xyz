<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('surveys', 'admin', 'analytics');

it('returns survey analytics', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();
    $question = SurveyQuestion::factory()->rating()->for($survey)->create();

    $center = Center::factory()->create();
    $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id]);

    $response = SurveyResponse::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => $student->id,
        'center_id' => $center->id,
    ]);

    SurveyAnswer::factory()->forRating(5)->create([
        'survey_response_id' => $response->id,
        'survey_question_id' => $question->id,
    ]);

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'data' => [
                'survey_id',
                'total_responses',
                'completion_rate',
                'questions' => [
                    '*' => [
                        'question_id',
                        'question',
                        'type',
                        'total_answers',
                    ],
                ],
            ],
        ]);
});

it('returns correct rating average', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();
    $question = SurveyQuestion::factory()->rating()->for($survey)->create();

    $center = Center::factory()->create();

    // Create 3 responses with ratings 3, 4, 5
    foreach ([3, 4, 5] as $rating) {
        $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id]);
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $student->id,
            'center_id' => $center->id,
        ]);
        SurveyAnswer::factory()->forRating($rating)->create([
            'survey_response_id' => $response->id,
            'survey_question_id' => $question->id,
        ]);
    }

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk();
    $questionData = $apiResponse->json('data.questions.0');
    expect($questionData['total_answers'])->toBe(3);
    expect((float) $questionData['average'])->toBe(4.0); // (3+4+5)/3 = 4
});

it('returns correct single choice distribution', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();
    $question = SurveyQuestion::factory()->singleChoice()->for($survey)->create();

    $option1 = SurveyQuestionOption::factory()->create([
        'survey_question_id' => $question->id,
        'option_translations' => ['en' => 'Option A', 'ar' => 'خيار أ'],
    ]);
    $option2 = SurveyQuestionOption::factory()->create([
        'survey_question_id' => $question->id,
        'option_translations' => ['en' => 'Option B', 'ar' => 'خيار ب'],
    ]);

    $center = Center::factory()->create();

    // 2 votes for option1, 1 vote for option2
    foreach ([1, 1, 2] as $index => $optionIndex) {
        $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id]);
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $student->id,
            'center_id' => $center->id,
        ]);
        SurveyAnswer::factory()->forSingleChoice($optionIndex === 1 ? $option1->id : $option2->id)->create([
            'survey_response_id' => $response->id,
            'survey_question_id' => $question->id,
        ]);
    }

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk();
    $questionData = $apiResponse->json('data.questions.0');
    expect($questionData['total_answers'])->toBe(3);
});

it('returns yes/no distribution', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();
    $question = SurveyQuestion::factory()->yesNo()->for($survey)->create();

    $center = Center::factory()->create();

    // 2 yes, 1 no
    foreach ([true, true, false] as $answer) {
        $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id]);
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $student->id,
            'center_id' => $center->id,
        ]);
        SurveyAnswer::factory()->forYesNo($answer)->create([
            'survey_response_id' => $response->id,
            'survey_question_id' => $question->id,
        ]);
    }

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk();
    $questionData = $apiResponse->json('data.questions.0');
    expect($questionData['distribution']['yes'])->toBe(2);
    expect($questionData['distribution']['no'])->toBe(1);
});

it('returns sample text answers', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();
    $question = SurveyQuestion::factory()->text()->for($survey)->create();

    $center = Center::factory()->create();
    $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id]);
    $response = SurveyResponse::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => $student->id,
        'center_id' => $center->id,
    ]);
    SurveyAnswer::factory()->forText('Great course!')->create([
        'survey_response_id' => $response->id,
        'survey_question_id' => $question->id,
    ]);

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk();
    $questionData = $apiResponse->json('data.questions.0');
    expect($questionData['sample_answers'])->toContain('Great course!');
});

it('returns zero responses for new survey', function (): void {
    $this->asAdmin();
    $survey = Survey::factory()->system()->create();

    $apiResponse = $this->getJson("/api/v1/admin/surveys/{$survey->id}/analytics", $this->adminHeaders());

    $apiResponse->assertOk()
        ->assertJsonPath('data.total_responses', 0)
        ->assertJsonPath('data.completion_rate', 0);
});
