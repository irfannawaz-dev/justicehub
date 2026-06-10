<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseRecord;
use Illuminate\Http\Request;

class ClientLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate([
            'cnic' => 'nullable|string|size:13',
            'phone' => 'nullable|string|min:10|max:15',
        ]);

        $cnic = $request->input('cnic');
        $phone = $request->input('phone');

        if (! $cnic && ! $phone) {
            return response()->json(['found' => false]);
        }

        $case = null;

        // Search by CNIC hash (blind index)
        if ($cnic) {
            $hash = hash('sha256', $cnic);
            $case = CaseRecord::where('cnic_hash', $hash)
                ->orderByDesc('created_at')
                ->first();
        }

        // Fallback to phone search
        if (! $case && $phone) {
            $case = CaseRecord::where('primary_contact', $phone)
                ->orderByDesc('created_at')
                ->first();
        }

        if (! $case) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'repeat' => true,
            'case_uid' => $case->case_uid,
            'client' => [
                'fullName' => $case->name,
                'fatherHusbandName' => $case->father_husband_name,
                'gender' => $case->gender,
                'genderOther' => $case->gender_other,
                'age' => $case->age,
                'cnic' => $case->cnic,
                'maritalStatus' => $case->marital_status,
                'religion' => $case->religion,
                'educationLevel' => $case->education_level,
                'occupation' => $case->occupation,
                'monthlyIncome' => $case->income_bracket,
                'disabilityStatus' => $case->disability_status,
                'primaryContact' => $case->primary_contact,
                'alternativeContact' => $case->alternative_contact,
                'fullAddress' => $case->full_address,
                'unionCouncil' => $case->union_council,
                'tehsil' => $case->tehsil,
                'district' => $case->district,
                'preferredLanguage' => $case->language,
            ],
        ]);
    }
}
