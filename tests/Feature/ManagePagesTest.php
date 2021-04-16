<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\Assert;
use Tests\TestCase;

class ManagePagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A guest cannot create a page
     * @return void
     */
    public function test_a_guest_cannot_create_a_page(): void
    {
        $attributes = Page::factory()->raw();

        $response = $this->post("admin/pages", $attributes);

        $response->assertRedirect("login");
    }

    /**
     * A logged in user can create a page.
     */
    public function test_a_user_can_create_a_page(): void
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
     * A logged in user who creates a page is defined as the creator and updator.
     */
    public function test_created_page_has_logged_in_user_as_creator_and_updater(): void
    {
        $this->actingAs(User::factory()->create());

        $attributes = Page::factory()->raw();

        $response = $this->post(route("pages.create"), $attributes);

        /**
         * @TODO check if also shown on page
         * $this->assertDatabaseHas("pages", $attributes);
         * And check through singular get and index get
         */

        $response->assertRedirect(route("pages.index"));
    }

    /**
     * A page is viewable by a logged in user
     */
    public function test_a_page_is_viewable_by_logged_in_user(): void
    {
        $this->actingAs(User::factory()->create());
        /** @var Page $pageModel */
        $pageModel = Page::factory()->create();

        $response = $this->get(route("pages.show", [$pageModel["slug"]]));

        $pageModel["is_password_protected"] = "0"; // in PHP it is false, but in js it is "0"

        $response
            ->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) => $page->where("page", $pageModel),
            );
    }

    /**
     * A public published page is viewable by a guest
     */
    public function test_page_permissions_for_guests()
    {
        $this->actingAs(User::factory()->create());
        /** @var Page $pageModel */
        $pageModel = Page::factory()->create();
        $pageModelPublished = Page::factory()->create(["is_draft" => false]);
        $pageModelPublic = Page::factory()->create(["is_private" => false]);
        $pageModelPublicPublished = Page::factory()->create([
            "is_draft" => false,
            "is_private" => false,
        ]);

        \Auth::logout();

        // Test if a guest can view the page by default (should not, because it is a draft & private by default)
        $response = $this->get(route("pages.show", [$pageModel->slug]));
        $response->assertRedirect(route("login"));

        // Test if a guest can view a published page (should not, because it is private default)
        $response = $this->get(
            route("pages.show", [$pageModelPublished->slug]),
        );
        $response->assertRedirect(route("login"));

        // Test if a guest can view a public page (should not, because it is draft default)
        $response = $this->get(route("pages.show", [$pageModelPublic->slug]));
        $response->assertRedirect(route("login"));

        // Test if a guest can view a public & published page (should not, because it is draft default)
        $response = $this->get(
            route("pages.show", [$pageModelPublicPublished->slug]),
        );
        $pageModelPublicPublished["is_password_protected"] = "0"; // in PHP it is false, but in js it is "0"
        $pageModelPublicPublished["is_draft"] = "0"; // in PHP it is false, but in js it is "0"
        $pageModelPublicPublished["is_private"] = "0"; // in PHP it is false, but in js it is "0"

        $response
            ->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) => $page->where(
                    "page",
                    $pageModelPublicPublished,
                ),
            );
    }
}
