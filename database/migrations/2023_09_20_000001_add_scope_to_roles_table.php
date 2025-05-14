<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('scope')->default(Role::SCOPE_GLOBAL)->after('level');
        });
        
        // Update existing roles with appropriate scope
        $this->updateExistingRoles();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
    
    /**
     * Update existing roles with appropriate scope values
     */
    private function updateExistingRoles(): void
    {
        $globalRoles = ['admin', 'director', 'deputy-director'];
        $departmentRoles = ['department-head', 'deputy-department-head', 'staff'];
        
        // Update global roles
        DB::table('roles')
            ->whereIn('slug', $globalRoles)
            ->update(['scope' => Role::SCOPE_GLOBAL]);
        
        // Update department-specific roles
        DB::table('roles')
            ->whereIn('slug', $departmentRoles)
            ->update(['scope' => Role::SCOPE_DEPARTMENT]);
    }
}; 