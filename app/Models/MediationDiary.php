<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediationDiary extends Model
{
    protected $table = 'mediation_diary';

    protected $fillable = ['case_id', 'session_date', 'next_session_date', 'what_happened', 'note_for_next_session', 'logged_by'];

    protected $casts = ['session_date' => 'date', 'next_session_date' => 'date'];

    public function case()
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }
}
