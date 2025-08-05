<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete all existing roles and permissions with 'api' guard
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('roles')->where('guard_name', 'api')->delete();
        DB::table('permissions')->where('guard_name', 'api')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert guard_name from 'web' to 'api' in roles table
        DB::table('roles')->where('guard_name', 'web')->update(['guard_name' => 'api']);
        
        // Revert guard_name from 'web' to 'api' in permissions table
        DB::table('permissions')->where('guard_name', 'web')->update(['guard_name' => 'api']);
    }
};
