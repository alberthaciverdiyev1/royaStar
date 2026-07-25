<?php

namespace App\Modules\Student\Models;

use App\Traits\SerializesDates;
use App\Modules\Student\Models\Student;
use Illuminate\Database\Eloquent\Model;

class StudentActivity extends Model
{
    use SerializesDates;

    protected $connection = 'pgsql_activity';

    protected $fillable = ['student_id', 'type', 'reference_type', 'reference_id', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
