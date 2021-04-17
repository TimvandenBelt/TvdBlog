<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //    public function index()
    //    {
    //        //
    //    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //    public function create()
    //    {
    //        //
    //    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $attributes = $request->validate([
            "title" => ["required"],
            "content" => ["required"],
            "slug" => ["required", "unique:pages"],
            "is_draft" => ["nullable","boolean"],
            "is_private" => ["nullable", "boolean"],
            "visible_from" => ["nullable", "date"],
            "visible_until" => ["nullable", "date"],
        ]);

        Page::create($attributes);

        return Redirect::route("pages.index");
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page): \Response|\Illuminate\Http\Response|\Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        if ($page->is_draft || $page->is_private) {
            if (!Auth::check()) {
                return Redirect::route("login");
            }
        }

        return Inertia::render("Page/Show", [
            "page" => $page->only(
                "title",
                "content",
                "is_password_protected",
                "updated_at",
                "created_at",
            ),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response|\Inertia\Response
     */
        public function edit(Page $page)
        {
            //
        }

    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, Page $page): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
        {
            $attributes = $request->validate([
                "title" => "required",
                "content" => "required",
                "slug" => "required",
            ]);

            $page->update($attributes);

            return Redirect::route('pages.edit', $page->id);
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    //    public function destroy(Page $page)
    //    {
    //        //
    //    }
}
