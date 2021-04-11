<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A user can have relations with a page.
     */
    public function test_relations_with_pages()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Page::factory()
            ->count(3)
            ->for($user, "created_by")
            ->for($user, "updated_by")
            ->create();

        // See if the function returns a BelongsTo relation.
        $this->assertInstanceOf(
            HasMany::class,
            $user->updated_pages(),
            "updated_by does not return a BelongsTo relation",
        );
        $this->assertInstanceOf(
            HasMany::class,
            $user->created_pages(),
            "updated_by does not return a BelongsTo relation",
        );
        $this->assertInstanceOf(
            HasMany::class,
            $user->pages(),
            "created_by does not return a BelongsTo relation",
        );

        // See if the property returns an object of Page.
        $this->assertInstanceOf(
            Page::class,
            $user->updated_pages->first(),
            "updated_by is not an instance of Page",
        );
        // See if the property returns an object of Page.
        $this->assertInstanceOf(
            Page::class,
            $user->created_pages->first(),
            "created_by is not an instance of Page",
        );
        // See if the property returns an object of Page.
        $this->assertInstanceOf(
            Page::class,
            $user->pages->first(),
            "pages is not an instance of Page",
        );
    }
}
