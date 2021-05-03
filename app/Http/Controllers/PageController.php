<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public function store(Request $request)
    {
        $attributes = $this->validateRequest($request);

        Page::create($attributes);

        return Redirect::route("pages.index");
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        // Verify if visitor is allowed to view the page due to draft or private status.
        Gate::authorize("view", $page);

        // Check if visitor is allowed to view the page if password protected.
        if (Gate::denies("view_password_protected", $page)) {
            return Redirect::route("pages.guarded", $page->slug);
        }

        return Inertia::render("Page/Show", [
            "page" => $page->only(
                "title",
                "content",
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
        return Inertia::render("Page/Edit", [
            "page" => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $attributes = $this->validateRequest($request);

        $page->update($attributes);

        return Redirect::route("pages.edit", $page->id);
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

    /*
     * Validation for creating or updating a page.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            "title" => ["required", "string"],
            "content" => ["required", "string"],
            "slug" => ["required", "unique:pages", "string"],
            "is_draft" => ["sometimes", "nullable", "boolean"],
            "is_private" => ["sometimes", "nullable", "boolean"],
            "visible_from" => [
                "sometimes",
                "nullable",
                "date",
                "before:visible_until",
            ],
            "visible_until" => [
                "sometimes",
                "nullable",
                "date",
                "after:visible_from",
            ],
            "is_password_protected" => ["sometimes", "nullable", "boolean"],
            "password" => [
                "nullable",
                "min:3",
                Rule::requiredIf(
                    $request->input("is_password_protected") === true,
                ),
            ],
        ]);
    }

    /**
     * Page is guarded by a password and visitor needs to login.
     * GET
     */
    public function guarded(Page $page)
    {
        if (Gate::allows("view_password_protected", $page)) {
            return Redirect::route("pages.show", $page->slug);
        }

        return Inertia::render("Page/Guarded", [
            "page" => $page->only("title", "updated_at", "created_at"),
        ]);
    }

    /**
     * Visitor tries to access a page by submitting the password.
     * POST
     * @throws ValidationException
     */
    public function enter(Request $request, Page $page)
    {
        $attributes = $request->validate(["password" => ["required"]]);
        $password = $attributes["password"];

        // Wrong password
        if (!Hash::check($password, $page->password)) {
            throw ValidationException::withMessages([
                "password" => "Wrong password!",
            ]); // @TODO localize
        }

        // Correct password
        $page->authorizeVisitorForPasswordProtected();

        session()->flash("flash-message", "Successfuly accessed the page!"); // @TODO localize & move to page model
        session()->flash("flash-type", "success");

        return Redirect::route("pages.show", $page->slug);
    }
}
