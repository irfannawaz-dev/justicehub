<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackSurvey extends Model
{
    protected $fillable = [
        'survey_uid', 'case_id', 'hub_id', 'enumerator_name', 'consent',
        'visit_date', 'service_date', 'service_type', 'first_visit',
        'q11_access', 'q12_reception', 'q13_explanation', 'q14_waiting', 'q15_difficulty',
        'q16_listened', 'q17_comfortable', 'q18_understood', 'q19_fair_treatment',
        'q20_info_safety', 'q21_data_explained', 'q22_confidence', 'q23_complaint_info',
        'q24_advice_useful', 'q25_referral_clarity', 'q26_next_steps', 'q27_clarity',
        'q28_satisfaction', 'q29_resolution_help', 'q30_recommend', 'q31_trust',
        'q32_helpful_part', 'q33_improvement', 'q34_additional',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'consent'      => 'boolean',
            'first_visit'  => 'boolean',
            'visit_date'   => 'date',
            'service_date' => 'date',
            'meta'         => 'array',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function avgAccessWelcome(): ?float
    {
        $scores = array_filter([$this->q11_access, $this->q12_reception, $this->q13_explanation, $this->q14_waiting]);
        return count($scores) ? round(array_sum($scores) / count($scores), 1) : null;
    }

    public function avgOverall(): ?float
    {
        $scores = array_filter([$this->q11_access, $this->q12_reception, $this->q13_explanation, $this->q14_waiting, $this->q18_understood, $this->q22_confidence, $this->q28_satisfaction, $this->q31_trust]);
        return count($scores) ? round(array_sum($scores) / count($scores), 1) : null;
    }
}
