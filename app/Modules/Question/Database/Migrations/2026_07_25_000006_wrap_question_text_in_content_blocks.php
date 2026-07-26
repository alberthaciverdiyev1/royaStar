<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE questions SET question = jsonb_build_array(jsonb_build_object('type', 'text', 'content', question::text)) WHERE question IS NOT NULL AND question::text != '' AND question::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET variant_a = jsonb_build_array(jsonb_build_object('type', 'text', 'content', variant_a::text)) WHERE variant_a IS NOT NULL AND variant_a::text != '' AND variant_a::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET variant_b = jsonb_build_array(jsonb_build_object('type', 'text', 'content', variant_b::text)) WHERE variant_b IS NOT NULL AND variant_b::text != '' AND variant_b::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET variant_c = jsonb_build_array(jsonb_build_object('type', 'text', 'content', variant_c::text)) WHERE variant_c IS NOT NULL AND variant_c::text != '' AND variant_c::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET variant_d = jsonb_build_array(jsonb_build_object('type', 'text', 'content', variant_d::text)) WHERE variant_d IS NOT NULL AND variant_d::text != '' AND variant_d::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET variant_e = jsonb_build_array(jsonb_build_object('type', 'text', 'content', variant_e::text)) WHERE variant_e IS NOT NULL AND variant_e::text != '' AND variant_e::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET open_answer = jsonb_build_array(jsonb_build_object('type', 'text', 'content', open_answer::text)) WHERE open_answer IS NOT NULL AND open_answer::text != '' AND open_answer::text NOT LIKE '[%'");
        DB::statement("UPDATE questions SET explanation = jsonb_build_array(jsonb_build_object('type', 'text', 'content', explanation::text)) WHERE explanation IS NOT NULL AND explanation::text != '' AND explanation::text NOT LIKE '[%'");
    }

    public function down(): void
    {
        // Not reversible — data transformation only
    }
};
