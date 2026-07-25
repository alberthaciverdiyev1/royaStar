<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentService
{
    public function list(int $perPage): LengthAwarePaginator { return Student::with(['user', 'grade', 'city'])->paginate($perPage); }
    public function find(int $id) { return Student::with(['user', 'grade', 'city'])->findOrFail($id); }
    public function create(array $data) { return Student::create($data); }
    public function update(Student $student, array $data) { $student->update($data); return $student->fresh(); }
    public function delete(Student $student) { $student->delete(); }
}
