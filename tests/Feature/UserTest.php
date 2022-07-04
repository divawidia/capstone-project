<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $roleAdmin = Role::factory()->create(['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin']);
        $roleDoctor = Role::factory()->create(['name' => 'doctor', 'display_name' => 'Doctor', 'description' => 'Doctor']);
        $roleUser = Role::factory()->create(['name' => 'user', 'display_name' => 'User', 'description' => 'User']);
        $this->userAdmin = User::factory()->create();
        $this->userAdmin->roles()->attach($roleAdmin);

        Sanctum::actingAs(
            $this->userAdmin,
            ['*']
        );

        $this->userDoctor = User::factory()->create();
        $this->userDoctor->roles()->attach($roleDoctor);

        $this->userUser = User::factory()->create();
        $this->userUser->roles()->attach($roleUser);
    }

    public function test_admin_fetch_all_doctor_data()
    {
        $response = $this->getJson(route('admin.doctorList'))
            ->assertOk();

        $this->assertEquals(1, $this->count($response->json()));
    }

    public function test_admin_fetch_one_of_doctor_data()
    {
        $response = $this->getJson(route('admin.doctorList', $this->userDoctor->id))
            ->assertOk();

        $this->assertEquals($response[0]['name'], $this->userDoctor->name);
    }

    public function test_admin_can_update_doctor_status()
    {
        $this->patchJson(route('admin.updateStatusDoctor', $this->userDoctor->id), ['status' => 'active'])
            ->assertOk();

        $this->assertDatabaseHas('users', ['status' => 'active']);
    }

    public function test_admin_cannot_update_user_data()
    {
        $this->patchJson(route('admin.updateStatusDoctor', $this->userUser->id), ['status' => 'active'])
            ->assertUnauthorized()
            ->assertJson(['message' => 'User is not doctor']);
    }
}
