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
            ->for($user, "createdBy")
            ->for($user, "updatedBy")
            ->create();

        // See if the function returns a BelongsTo relation.
        $this->assertInstanceOf(
            HasMany::class,
            $user->updatedPages(),
            "updated_by does not return a BelongsTo relation",
        );
        $this->assertInstanceOf(
            HasMany::class,
            $user->createdPages(),
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
            $user->updatedPages->first(),
            "updated_by is not an instance of Page",
        );
        // See if the property returns an object of Page.
        $this->assertInstanceOf(
            Page::class,
            $user->createdPages->first(),
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
