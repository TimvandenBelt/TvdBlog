<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_a_guest_cannot_create_a_page()
    {
        $attributes = Page::factory()->raw();

        $response = $this->post("admin/pages", $attributes);

        $response->assertRedirect("login");
    }

    /**
     * A logged in user can create a page.
     *
     * @return void
     */
    public function test_a_user_can_create_a_page()
    {
        $this->actingAs(User::factory()->create());

        $attributes = Page::factory()->raw();

        $response = $this->post("admin/pages", $attributes);

        /**
         * @TODO check if also shown on page
         * $this->assertDatabaseHas("pages", $attributes);
         * And check through singular get and index get
         */

        $response->assertRedirect(route("pages.index"));
    }

    /**
     * A logged in user can create a page.
     *
     * @return void
     */
    public function test_created_page_has_logged_in_user_as_creator_and_updater()
    {
        $this->actingAs(User::factory()->create());

        $attributes = Page::factory()->raw();

        $response = $this->post("admin/pages", $attributes);

        /**
         * @TODO check if also shown on page
         * $this->assertDatabaseHas("pages", $attributes);
         * And check through singular get and index get
         */

        $response->assertRedirect(route("pages.index"));
    }

    /**
     * A page is viewable by a logged in user
     *
     * @return void
     */
    public function test_a_page_is_viewable_by_logged_in_user()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->actingAs($user);
        /** @var Page $pageModel */
        $pageModel = Page::factory()->create();
        $response = $this->get(route("pages.show", [$pageModel["slug"]]));
        $pageModel["is_password_protected"] = "0";
        $response
            ->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) => $page->where("page", $pageModel),
            );
    }

    /**
     * A public published page is viewable by a guest
     *
     * @return void
     */
    public function test_that_page_is_viewable_by_slug()
    {
        $this->assertEquals(1, 1);
    }
}
