<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Fakultet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakultetTooltipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_text_tooltip_when_text_exists()
    {
        $fakultet = Fakultet::factory()->create([
            'uputstvo_za_ocjene' => 'Ovo je uputstvo',
            'uputstvo_file' => null,
        ]);

        $tooltip = $fakultet->uputstvo_file ?? $fakultet->uputstvo_za_ocjene;

        $this->assertEquals('Ovo je uputstvo', $tooltip);
    }

    /** @test */
    public function it_returns_file_tooltip_when_file_exists()
    {
        $fakultet = Fakultet::factory()->create([
            'uputstvo_za_ocjene' => null,
            'uputstvo_file' => 'fakulteti/uputstvo.pdf',
        ]);

        $tooltip = $fakultet->uputstvo_file ?? $fakultet->uputstvo_za_ocjene;

        $this->assertEquals('fakulteti/uputstvo.pdf', $tooltip);
    }

    /** @test */
    public function it_returns_null_when_no_tooltip()
    {
        $fakultet = Fakultet::factory()->create([
            'uputstvo_za_ocjene' => null,
            'uputstvo_file' => null,
        ]);

        $tooltip = $fakultet->uputstvo_file ?? $fakultet->uputstvo_za_ocjene;

        $this->assertNull($tooltip);
    }
}
