<?php

namespace Tests\Unit\Models;

use App\Models\Fakultet;
use App\Models\Mobilnost;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilnostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_mobilnost()
    {
        $student = Student::factory()->create();
        $fakultet = Fakultet::factory()->create();

        $mobilnost = Mobilnost::factory()->create([
            'student_id' => $student->id,
            'fakultet_id' => $fakultet->id,
            'tip_mobilnosti' => 'erasmus',
        ]);

        $this->assertDatabaseHas('mobilnosti', [
            'id' => $mobilnost->id,
            'student_id' => $student->id,
            'fakultet_id' => $fakultet->id,
            'tip_mobilnosti' => 'erasmus',
        ]);

        $this->assertInstanceOf(Student::class, $mobilnost->student);
        $this->assertInstanceOf(Fakultet::class, $mobilnost->fakultet);
    }
}
