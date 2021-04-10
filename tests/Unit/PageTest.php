<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A page can be created.
     *
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
     * A page can be created.
     *
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
     * A page can autoset a user as updated_by
     *
     * @return void
     */
    public function test_page_can_autoset_created_and_updated_by_on_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        /** @var Page $page */
        $page = Page::factory([
            "created_by" => null,
            "updated_by" => null,
        ])->create();
        $this->assertEquals($page->created_by, $user->id);
    }

    /**
     * A page can autoset a user as updated_by
     *
     * @return void
     */
    public function test_page_can_autoset_updated_by_on_update()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);
        /** @var Page $page */
        $page = Page::factory()->create();
        $this->actingAs($user2);
        $page->update(Page::factory()->raw());
        $this->assertEquals(
            $page->updated_by,
            $user2->id,
            "updated_by does not match the latest user",
        );
    }
}
