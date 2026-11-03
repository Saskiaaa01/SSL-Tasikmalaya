<?php

namespace Tests\Feature;

use App\Jobs\CheckDomainSslJob;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DomainCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_dashboard_and_domains_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('domains.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_domain_and_triggers_ssl_check_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('domains.store'), [
            'hostname' => 'diskominfo.tasikmalayakota.go.id',
            'port' => 443,
        ]);

        $response->assertRedirect(route('domains.index'));

        $this->assertDatabaseHas('domains', [
            'hostname' => 'diskominfo.tasikmalayakota.go.id',
            'port' => 443,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        Queue::assertPushed(CheckDomainSslJob::class, function ($job) {
            return $job->domain->hostname === 'diskominfo.tasikmalayakota.go.id';
        });
    }

    public function test_hostname_validation_rejects_invalid_domain_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('domains.store'), [
            'hostname' => 'invalid-domain-with-spaces and symbols!',
            'port' => 443,
        ]);

        $response->assertSessionHasErrors('hostname');
    }
}
