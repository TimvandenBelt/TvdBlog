<?php

use App\Http\Controllers\PageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/", function () {
    return Inertia::render("Welcome", [
        "canLogin" => Route::has("login"),
        "canRegister" => Route::has("register"),
        "laravelVersion" => Application::VERSION,
        "phpVersion" => PHP_VERSION,
    ]);
});

Route::middleware(["auth:sanctum", "verified"])
    ->get("/dashboard", function () {
        return Inertia::render("Dashboard");
    })
    ->name("dashboard");

/**
 * Admin section =======================
 */
Route::middleware(["auth:sanctum", "verified"])
    ->prefix("admin")
    ->group(function () {
        /**
         * START Pages section
         */
        Route::prefix("pages")->group(function () {
            Route::post("/", [PageController::class, "store"])->name(
                "pages.store",
            );
            Route::get("/", [PageController::class, "index"])->name(
                "pages.index",
            );
            Route::patch("/{page}", [PageController::class, "update"])->name(
                "pages.update",
            );

            Route::get("/{page}", [PageController::class, "edit"])->name(
                "pages.edit",
            );
            /**
             * END pages sections
             */
        });
    });

/**
 * Page wildcard, must be at bottom.
 */
Route::get("/{page:slug}/password", [PageController::class, "guarded"])->name(
    "pages.guarded",
);
Route::post("/{page:slug}/password", [PageController::class, "enter"])->name(
    "pages.enter",
);
Route::get("/{page:slug}", [PageController::class, "show"])->name("pages.show");
