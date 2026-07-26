<?php

namespace Database\Seeders;

use App\Modules\Grade\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            '1-ci sinif', '2-ci sinif', '3-cü sinif', '4-cü sinif',
            '5-ci sinif', '6-cı sinif', '7-ci sinif', '8-ci sinif',
            '9-cu sinif', '10-cu sinif', '11-ci sinif',
        ];

        foreach ($grades as $name) {
            Grade::create(['name' => $name]);
        }
    }
}
