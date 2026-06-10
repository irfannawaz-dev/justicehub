<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─────────────────────────────────────────────────────────────
        // Create all permissions (27 total)
        // ─────────────────────────────────────────────────────────────
        $permissions = [
            // Cases
            'cases.view',
            'cases.create',
            'cases.edit',
            'cases.delete',
            'cases.approve',

            // Complaints
            'complaints.view',
            'complaints.create',
            'complaints.resolve',
            'complaints.escalate',

            // Feedback
            'feedback.view',
            'feedback.create',

            // Outreach
            'outreach.view',
            'outreach.create',

            // Indicators
            'indicators.view',
            'indicators.edit',

            // Evidence
            'evidence.view',
            'evidence.create',
            'evidence.verify',

            // Staff
            'staff.view',
            'staff.edit',
            'staff.training.log',

            // Settings
            'settings.view',
            'settings.edit',

            // Reports
            'reports.view',
            'reports.export',

            // Admin
            'lookups.manage',
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ─────────────────────────────────────────────────────────────
        // Create roles and assign permissions from UserRole enum
        // ─────────────────────────────────────────────────────────────
        foreach (UserRole::cases() as $userRole) {
            $role = Role::firstOrCreate(['name' => $userRole->value]);
            $role->syncPermissions($userRole->permissions());
        }
    }
}
