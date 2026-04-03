<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id', 20)->nullable()->unique()->after('id');
        });

        // Auto-generate employee_id for existing karyawan
        $counter = 1;
        User::where('role', 'karyawan')->orderBy('id')->each(function ($user) use (&$counter) {
            $user->update(['employee_id' => str_pad($counter++, 4, '0', STR_PAD_LEFT)]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });
    }
};
