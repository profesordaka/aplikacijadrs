<?php

namespace Tests\Unit\Models;

use App\Models\NivoStudija;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_student()
    {
        $nivoStudija = NivoStudija::factory()->create();

        $student = Student::factory()->create([
            'nivo_studija_id' => $nivoStudija->id,
            'email' => 'student@test.com',
        ]);

        $this->assertDatabaseHas('studenti', [
            'id' => $student->id,
            'email' => 'student@test.com',
            'nivo_studija_id' => $nivoStudija->id,
        ]);

        $this->assertInstanceOf(NivoStudija::class, $student->nivoStudija);
    }
}
