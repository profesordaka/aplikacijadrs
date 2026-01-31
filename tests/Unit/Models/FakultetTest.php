<?php

namespace Tests\Unit\Models;

use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakultetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_fakultet()
    {
        $univerzitet = Univerzitet::factory()->create();

        $fakultet = Fakultet::factory()->create([
            'univerzitet_id' => $univerzitet->id,
            'naziv' => 'FIT',
        ]);

        $this->assertDatabaseHas('fakulteti', [
            'id' => $fakultet->id,
            'naziv' => 'FIT',
            'univerzitet_id' => $univerzitet->id,
        ]);

        $this->assertInstanceOf(Univerzitet::class, $fakultet->univerzitet);
    }
}
