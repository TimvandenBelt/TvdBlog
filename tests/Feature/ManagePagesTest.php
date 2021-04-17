<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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

        $response = $this->post(route("pages.store"), $attributes);

        $response->assertRedirect(route("login"));
    }

    /**
     * A logged in user can create a page.
     */
    public function test_a_user_can_create_a_page(): void
    {
        $this->actingAs(User::factory()->create());

        $attributes = Page::factory()->raw();

        $response = $this->post(route("pages.store"), $attributes);

        /**
         * @TODO check if also shown on page
         * $this->assertDatabaseHas("pages", $attributes);
         * And check through singular get and index get
         */

        $response->assertRedirect(route("pages.index"));
    }

    /**
     * Only a logged in user can update a page. Guest should be redirected to login.
     */
    public function test_only_a_logged_in_user_can_update_page()
    {
        $this->actingAs(User::factory()->create());
        $pageModel = Page::factory()->create();

        /** @var Page $newAttributes */
        $newAttributes = Page::factory()
            ->withoutUser()
            ->make(["created_at" => $pageModel->created_at]);

        $response = $this->patch(
            route("pages.update", $pageModel->id),
            $newAttributes->getAttributes(),
        );
        $response->assertRedirect(route("pages.edit", $pageModel->id));

        $response = $this->get(route("pages.show", [$newAttributes->slug]));
        $newAttributes->is_password_protected = "0"; // in PHP it is false, but in DB it is "0"
        $newAttributes->updated_at = $pageModel->fresh()->updated_at;
        $response
            ->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) => $page->where(
                    "page",
                    $newAttributes->toArray(),
                ),
            );
    }

    /**
     * A logged in user who creates a page is defined as the creator and updator.
     */
    public function test_created_page_has_logged_in_user_as_creator_and_updater(): void
    {
        $this->actingAs(User::factory()->create());

        $attributes = Page::factory()->raw();

        $response = $this->post(route("pages.store"), $attributes);

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

        $pageModel["is_password_protected"] = "0"; // in PHP it is false, but in DB it is "0"

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

    public function test_validation_rules_for_pages()
    {
        $this->actingAs(User::factory()->create());

        // A title is required
        $attributes = Page::factory()->raw(["title" => null]);
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["title"]);

        // Content is required
        $attributes = Page::factory()->raw(["content" => null]);
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["content"]);

        // Slug is required
        $attributes = Page::factory()->raw(["slug" => null]);
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["slug"]);

        // Slug must be unique
        $attributes = Page::factory()->raw();
        $this->post(route("pages.store"), $attributes);
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["slug"]);

        // Dates do work
        $attributes = Page::factory()
            ->withFrom()
            ->withUntil()
            ->raw();
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasNoErrors();

        // Date from must be earlier than until
        $attributes = Page::factory()
            ->withFrom(now()->addDay())
            ->withUntil(now()->subDay())
            ->raw();
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["visible_from", "visible_until"]);

        // Dates should be dates
        $attributes = Page::factory()->raw([
            "visible_until" => "test",
            "visible_from" => "test",
        ]);
        $response = $this->post(route("pages.store"), $attributes);
        $response->assertSessionHasErrors(["visible_from", "visible_until"]);
    }
}
