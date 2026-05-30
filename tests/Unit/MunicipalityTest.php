<?php

namespace Tests\Unit;

use App\Models\Municipality;
use PHPUnit\Framework\TestCase;

class MunicipalityTest extends TestCase
{
    public function test_latrinidad_variants_normalize_to_la_trinidad(): void
    {
        $this->assertSame('LA TRINIDAD', Municipality::normalizeLocationName('Latrinidad'));
        $this->assertSame('LA TRINIDAD', Municipality::normalizeLocationName('LA-TRINIDAD'));
        $this->assertSame('LA TRINIDAD', Municipality::normalizeLocationName('la_trinidad'));
    }

    public function test_la_trinidad_location_scope_includes_aliases_and_barangays(): void
    {
        $locations = Municipality::locationNamesForMunicipality('Latrinidad');

        $this->assertContains('LA TRINIDAD', $locations);
        $this->assertContains('LATRINIDAD', $locations);
        $this->assertContains('BECKEL', $locations);
    }
}
