<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class NodeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($admin);
    }

    private function actingAsUser(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        Sanctum::actingAs($user);
    }
    public function test_admin_can_create_corporation(): void
    {
        $this->actingAsAdmin();

        $res = $this->postJson('/api/nodes', [
            'name' => 'Corp A',
            'type' => 'Corporation',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.name', 'Corp A')
            ->assertJsonPath('data.type', 'Corporation');
    }
     public function test_non_admin_gets_403_with_policy_message(): void
    {
        $this->actingAsUser();

        $res = $this->postJson('/api/nodes', [
            'name' => 'Corp B',
            'type' => 'Corporation',
        ]);

        $res->assertStatus(403);

    }

    public function test_building_requires_zip_code(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', [
            'name' => 'Corp',
            'type' => 'Corporation',
        ])->json('data');

        // Missing zip_code (required_if) -> 422
        $res = $this->postJson('/api/nodes', [
            'name' => 'B1',
            'type' => 'Building',
            'parent_id' => $corp['id'],
            // 'zip_code' => '12345'
        ]);

        $res->assertStatus(422);
    }

     public function test_property_must_be_under_building(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', [
            'name' => 'Corp',
            'type' => 'Corporation',
        ])->json('data');

        $res = $this->postJson('/api/nodes', [
            'name' => 'Unit 1A',
            'type' => 'Property',
            'parent_id' => $corp['id'],
            'monthly_rent' => 1500.00,
        ]);

        $res->assertStatus(422);
    }

     public function test_only_one_active_tenancy_period_per_property(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', ['name' => 'Corp','type' => 'Corporation'])->json('data');

        $bldg = $this->postJson('/api/nodes', [
            'name' => 'B1','type' => 'Building','parent_id' => $corp['id'],
            'zip_code' => '12345',
        ])->json('data');

        $prop = $this->postJson('/api/nodes', [
            'name' => 'U101','type' => 'Property','parent_id' => $bldg['id'],
            'monthly_rent' => 1200.00,
        ])->json('data');

        $r1 = $this->postJson('/api/nodes', [
            'name' => 'T-2025-01','type' => 'Tenancy Period',
            'parent_id' => $prop['id'],'tenancy_active' => true,
        ]);
        $r1->assertStatus(201);

        $r2 = $this->postJson('/api/nodes', [
            'name' => 'T-2025-02','type' => 'Tenancy Period',
            'parent_id' => $prop['id'],'tenancy_active' => true,
        ]);
        $r2->assertStatus(422);
    }

      public function test_tenant_limit_max_4(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', ['name' => 'Corp','type' => 'Corporation'])->json('data');
        $bldg = $this->postJson('/api/nodes', [
            'name' => 'B1','type' => 'Building','parent_id' => $corp['id'],'zip_code' => '12345',
        ])->json('data');
        $prop = $this->postJson('/api/nodes', [
            'name' => 'U101','type' => 'Property','parent_id' => $bldg['id'],'monthly_rent' => 1200.00,
        ])->json('data');
        $tenancy = $this->postJson('/api/nodes', [
            'name' => 'T-2025','type' => 'Tenancy Period','parent_id' => $prop['id'],'tenancy_active' => true,
        ])->json('data');

        foreach ([1,2,3,4] as $i) {
            $this->postJson('/api/nodes', [
                'name' => "Tenant {$i}", 'type' => 'Tenant',
                'parent_id' => $tenancy['id'], 'move_in_date' => '2025-01-0'.$i,
            ])->assertStatus(201);
        }

        $this->postJson('/api/nodes', [
            'name' => 'Tenant 5','type' => 'Tenant',
            'parent_id' => $tenancy['id'], 'move_in_date' => '2025-01-05',
        ])->assertStatus(422);
    }
     public function test_get_children(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', ['name' => 'Corp','type' => 'Corporation'])->json('data');
        $this->postJson('/api/nodes', ['name' => 'B1','type' => 'Building','parent_id' => $corp['id'],'zip_code' => '11111']);


        $res = $this->getJson("/api/nodes/{$corp['id']}/children");

        $res->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_update_parent_validation(): void
    {
        $this->actingAsAdmin();

        $corp = $this->postJson('/api/nodes', ['name' => 'Corp','type' => 'Corporation'])->json('data');
        $bldg = $this->postJson('/api/nodes', ['name' => 'B1','type' => 'Building','parent_id' => $corp['id'],'zip_code' => '12345'])->json('data');
        $prop = $this->postJson('/api/nodes', ['name' => 'U101','type' => 'Property','parent_id' => $bldg['id'],'monthly_rent' => 1200.00])->json('data');

        $this->putJson("/api/nodes/{$prop['id']}/change-parent", [
            'parent_id' => $corp['id'], 'name' => $prop['name'], 'type' => $prop['type'], 'monthly_rent' => 1200.00,
        ])->assertStatus(422);

        $this->putJson("/api/nodes/{$prop['id']}/change-parent", [
            'parent_id' => $bldg['id'], 'name' => $prop['name'], 'type' => $prop['type'], 'monthly_rent' => 1200.00,
        ])->assertOk();
    }


}
