<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Role;
use App\Models\Review;

class ReviewTest extends TestCase
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
         $roleDoctor = Role::factory()->create(['name' => 'doctor', 'display_name' => 'Doctor', 'description' => 'Doctor']);
         $roleUser = Role::factory()->create(['name' => 'user', 'display_name' => 'User', 'description' => 'User']);

         $this->userUser = User::factory()->create();
         $this->userUser->roles()->attach($roleUser);

         $this->userUser_2 = User::factory()->create();
         $this->userUser_2->roles()->attach($roleUser);

         Sanctum::actingAs(
             $this->userUser,
             ['*']
         );

         $this->userDoctor = User::factory()->create();
         $this->userDoctor->roles()->attach($roleDoctor);
     }

    public function test_get_review_page()
    {
        $response = $this->get(route('review.index'));

        $response->assertStatus(200);
    }

    public function test_get_review_list_doctor_correct_input()
    {

        $review_1 = Review::factory()->create(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);
        $review_2 = Review::factory()->create(['user_id' => $this->userUser_2->id,'doctor_id' => $this->userDoctor->id]);


        $response = $this->getJson(route('review.reviewList', ['id' =>  $this->userDoctor->id]))->assertOk();

        $this->assertEquals(1,$this->count($response->json()));
    }

    public function test_add_review()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->userUser->id,
            'nama_user' => $this->userUser->name,
            'tanggal' => '2022-12-12',
            'deskripsi' => 'test case',
            'start_time' => '10:10:10',
            'end_time' => '10:10:10',
            'status' => 'done',
            'user_dokter_id' => $this->userDoctor->id,
            'nama_dokter' => $this->userDoctor->name,
        ]);
        $review_1 = Review::factory()->make(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);

        $response = $this->postJson(route('review.addReview', ['id' => $this->userDoctor->id]),
            [
                'comment' => $review_1->comment,
                'total_rating' => $review_1->total_rating,
        ])->assertCreated()->json();

        $this->assertEquals($review_1->comment, $response['data']['comment']);
        $this->assertDatabaseHas('reviews', ['comment' => $review_1->comment]);
    }

    public function test_edit_review()
    {
        $review_1 = Review::factory()->create(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);

        $response = $this->patchJson(route('review.editReview', ['id' => $this->userDoctor->id, 'id_review' => $review_1->id]),
            ['comment' => 'updated comment', 'total_rating' => 3])
            ->assertCreated();

        $this->assertDatabaseHas('reviews', ['comment' => 'updated comment', 'total_rating' => 3]);
    }

    public function test_delete_review()
    {
        $review_1 = Review::factory()->create(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);

        $this->deleteJson(route('review.destroyReview', ['id' => $this->userDoctor->id, 'id_review' => $review_1->id]))
            ->assertOk();

        $this->assertDatabaseMissing('reviews', ['comment' => $review_1->comment]);
    }

    public function test_get_review_list_doctor_wrong_input()
    {
        $response = $this->getJson(route('review.reviewList', ['id' => 1]))->assertStatus(404);

        $this->assertEquals(1, $this->count($response->json()));
    }

    public function test_get_specific_review_user_correct_input()
    {
        $review_1 = Review::factory()->create(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);

        $response = $this->getJson(route('review.specificReview', ['id' => $this->userDoctor->id, 'id_review' => $review_1->id]))->assertOk();

        $this->assertEquals(1, $this->count($response->json()));
    }

    public function test_get_specific_review_user_wrong_input()
    {
        $response = $this->getJson(route('review.specificReview', ['id' => 1, 'id_review' => 2]))->assertStatus(404);

        $this->assertEquals(1, $this->count($response->json()));
    }

    public function test_get_review_doctor_star_correct_input()
    {

        $review_1 = Review::factory()->create(['user_id' => $this->userUser->id, 'doctor_id' => $this->userDoctor->id]);
        $review_2 = Review::factory()->create(['user_id' => $this->userUser_2->id,'doctor_id' => $this->userDoctor->id]);

        $response = $this->getJson(route('review.reviewStar', ['id' => $this->userDoctor->id]))->assertOk();

        $this->assertEquals(1, $this->count($response->json()));
    }

}
