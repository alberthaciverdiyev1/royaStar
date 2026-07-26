<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE questions ALTER COLUMN question TYPE TEXT USING (question->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN question SET NOT NULL");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_a TYPE TEXT USING (variant_a->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_b TYPE TEXT USING (variant_b->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_c TYPE TEXT USING (variant_c->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_d TYPE TEXT USING (variant_d->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_e TYPE TEXT USING (variant_e->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN open_answer TYPE TEXT USING (open_answer->>'az')");
            DB::statement("ALTER TABLE questions ALTER COLUMN explanation TYPE TEXT USING (explanation->>'az')");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE questions ALTER COLUMN question TYPE JSON USING to_json(question)");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_a TYPE JSON USING to_json(variant_a)");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_b TYPE JSON USING to_json(variant_b)");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_c TYPE JSON USING to_json(variant_c)");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_d TYPE JSON USING to_json(variant_d)");
            DB::statement("ALTER TABLE questions ALTER COLUMN variant_e TYPE JSON USING to_json(variant_e)");
            DB::statement("ALTER TABLE questions ALTER COLUMN open_answer TYPE JSON USING to_json(open_answer)");
            DB::statement("ALTER TABLE questions ALTER COLUMN explanation TYPE JSON USING to_json(explanation)");
        }
    }
};
