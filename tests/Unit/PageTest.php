<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A page can be created.
     * @return void
     */
    public function test_can_create_a_page()
    {
        $this->actingAs(User::factory()->create());
        $attributes = Page::factory()->raw();
        /** @var Page $page */
        $page = Page::create($attributes);
        $this->assertDatabaseHas("pages", $page->getAttributes());
    }

    /**
     * A page can be soft deleted.
     * @return void
     */
    public function test_can_safely_delete_a_page()
    {
        $this->actingAs(User::factory()->create());
        $page = Page::factory()->create();
        $page->delete();
        $this->assertSoftDeleted($page);
    }

    /**
     * A page can autoset a user as updated and created by
     * @return void
     */
    public function test_page_can_autoset_updated_and_created_by_on_update()
    {
        $user1 = User::factory()->create(); // The user who will create the page.
        $user2 = User::factory()->create(); // The user who will updated the page.

        // Test creating the page
        $this->actingAs($user1);
        /** @var Page $page */
        $page = Page::factory([
            "created_by_id" => null,
            "updated_by_id" => null,
        ])->create();
        $this->assertEquals(
            $page->created_by->id,
            $user1->id,
            "created_by is not equal to the user who created the page",
        );

        // Now test updating the page.
        $this->actingAs($user2);
        $page->update(Page::factory()->raw());
        $this->assertEquals(
            $page->updated_by->id,
            $user2->id,
            "updated_by does not match the latest user",
        );
    }

    /**
     * A page can have relations with a user
     * @return void
     */
    public function test_created_by_returns_belongsTo_relation()
    {
        $this->actingAs(User::factory()->create()); // Need to have a user loged in due to created_by and updated_by functionality.
        /** @var Page $page */
        $page = Page::factory()->create();

        // See if the function returns a BelongsTo relation.
        $this->assertInstanceOf(
            BelongsTo::class,
            $page->created_by(),
            "created_by does not return a BelongsTo relation",
        );
        $this->assertInstanceOf(
            BelongsTo::class,
            $page->updated_by(),
            "updated_by does not return a BelongsTo relation",
        );

        // See if the property returns an object of User.
        $this->assertInstanceOf(
            User::class,
            $page->created_by,
            "created_by is not an instance of User",
        );
        $this->assertInstanceOf(
            User::class,
            $page->updated_by,
            "updated_by is not an instance of User",
        );
    }
}
