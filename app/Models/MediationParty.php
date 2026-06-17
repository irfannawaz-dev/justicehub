<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediationParty extends Model
{
    protected $table = 'mediation_parties';

    protected $fillable = ['case_id', 'name', 'role', 'phone', 'note', 'consent_status'];

    public function case()
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function isAgreed(): bool
    {
        return $this->consent_status === 'agreed';
    }
}
