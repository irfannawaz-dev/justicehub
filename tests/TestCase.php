<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\Hub;
use App\Models\User;
use Database\Seeders\HubSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HubSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Role factory helpers — create + actingAs in one call
    // ─────────────────────────────────────────────────────────────

    protected function asHead(): User
    {
        $user = User::factory()->create(['role' => UserRole::Head, 'hub_id' => null]);
        $user->assignRole('head');
        $this->actingAs($user);
        return $user;
    }

    protected function asHubAdmin(?string $hubId = null): User
    {
        $hubId = $hubId ?? Hub::first()->id;
        $user  = User::factory()->create(['role' => UserRole::HubAdmin, 'hub_id' => $hubId]);
        $user->assignRole('hub-admin');
        $this->actingAs($user);
        return $user;
    }

    protected function asDataEntry(?string $hubId = null): User
    {
        $hubId = $hubId ?? Hub::first()->id;
        $user  = User::factory()->create(['role' => UserRole::DataEntry, 'hub_id' => $hubId]);
        $user->assignRole('data-entry');
        $this->actingAs($user);
        return $user;
    }

    protected function asMELead(): User
    {
        $user = User::factory()->create(['role' => UserRole::MELead, 'hub_id' => null]);
        $user->assignRole('me-lead');
        $this->actingAs($user);
        return $user;
    }

    protected function asComplaintInvestigator(?string $hubId = null): User
    {
        $hubId = $hubId ?? Hub::first()->id;
        $user  = User::factory()->create(['role' => UserRole::ComplaintInvestigator, 'hub_id' => $hubId]);
        $user->assignRole('complaint-investigator');
        $this->actingAs($user);
        return $user;
    }

    protected function asViewer(?string $hubId = null): User
    {
        $user = User::factory()->create(['role' => UserRole::Viewer, 'hub_id' => $hubId]);
        $user->assignRole('viewer');
        $this->actingAs($user);
        return $user;
    }
}
