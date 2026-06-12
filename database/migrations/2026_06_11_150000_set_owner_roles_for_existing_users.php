<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update any users without a role to be 'owner'
        // (This handles existing users from before the role system was implemented)
        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'owner']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'owner')
            ->update(['role' => null]);
    }
};
