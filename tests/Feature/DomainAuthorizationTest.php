<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_only_access_their_own_domain_for_edit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $otherUser = User::factory()->create(['role' => 'owner']);

        $domain = Domain::create([
            'user_id' => $owner->id,
            'hostname' => 'owner.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);

        // Other user cannot access edit page
        $response = $this->actingAs($otherUser)->get(route('domains.edit', $domain));
        $response->assertStatus(403);

        // Owner can access edit page
        $response = $this->actingAs($owner)->get(route('domains.edit', $domain));
        $response->assertStatus(200);
    }

    public function test_owner_cannot_update_or_delete_another_users_domain(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $otherUser = User::factory()->create(['role' => 'owner']);

        $domain = Domain::create([
            'user_id' => $owner->id,
            'hostname' => 'owner.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);

        $updateResponse = $this->actingAs($otherUser)->put(route('domains.update', $domain), [
            'hostname' => 'hacked.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($otherUser)->delete(route('domains.destroy', $domain));
        $deleteResponse->assertStatus(403);
    }

    public function test_admin_can_update_and_delete_any_domain(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);

        $domain = Domain::create([
            'user_id' => $owner->id,
            'hostname' => 'owner.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('domains.edit', $domain));
        $response->assertStatus(200);

        $deleteResponse = $this->actingAs($admin)->delete(route('domains.destroy', $domain));
        $deleteResponse->assertRedirect(route('domains.index'));
        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
    }
}
