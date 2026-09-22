<?php

namespace Tests\Unit;

use App\Models\CaseRecord;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class CaseRecordCnicEncryptionTest extends TestCase
{
    public function test_it_reads_serialized_encrypted_cnics(): void
    {
        $case = $this->caseWithRawCnic(encrypt('41304-2749679-8'));

        $this->assertSame('41304-2749679-8', $case->cnic);
    }

    public function test_it_reads_string_encrypted_cnics_from_the_importer(): void
    {
        $case = $this->caseWithRawCnic(Crypt::encryptString('41304-2749679-8'));

        $this->assertSame('41304-2749679-8', $case->cnic);
    }

    public function test_it_reads_legacy_plaintext_cnics(): void
    {
        $case = $this->caseWithRawCnic('41304-2749679-8');

        $this->assertSame('41304-2749679-8', $case->cnic);
    }

    public function test_it_does_not_expose_an_unreadable_encrypted_payload(): void
    {
        $payload = base64_encode(json_encode([
            'iv' => base64_encode(random_bytes(16)),
            'value' => 'unreadable',
            'mac' => str_repeat('0', 64),
            'tag' => '',
        ], JSON_THROW_ON_ERROR));

        $case = $this->caseWithRawCnic($payload);

        $this->assertNull($case->cnic);
    }

    public function test_new_cnics_are_stored_encrypted_and_read_back_normally(): void
    {
        $case = new CaseRecord();
        $case->cnic = '41304-2749679-8';

        $this->assertNotSame('41304-2749679-8', $case->getRawOriginal('cnic'));
        $this->assertSame('41304-2749679-8', $case->cnic);
    }

    private function caseWithRawCnic(string $value): CaseRecord
    {
        $case = new CaseRecord();
        $case->setRawAttributes(['cnic' => $value]);

        return $case;
    }
}
