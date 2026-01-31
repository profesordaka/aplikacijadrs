<?php

namespace Tests\Unit\Models;

use App\Models\Fakultet;
use App\Models\MappingRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MappingRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_mapping_request()
    {
        $student = Student::factory()->create();
        $fakultet = Fakultet::factory()->create();
        $professor = User::factory()->create();

        $mappingRequest = MappingRequest::factory()->create([
            'student_id' => $student->id,
            'fakultet_id' => $fakultet->id,
            'professor_id' => $professor->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('mapping_requests', [
            'id' => $mappingRequest->id,
            'student_id' => $student->id,
            'fakultet_id' => $fakultet->id,
            'professor_id' => $professor->id,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Student::class, $mappingRequest->student);
        $this->assertInstanceOf(Fakultet::class, $mappingRequest->fakultet);
        $this->assertInstanceOf(User::class, $mappingRequest->professor);
    }
}
