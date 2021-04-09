<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("pages", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string("title");
            $table->mediumText("content");
            $table->boolean("is_draft")->default(true);
            $table->boolean("is_private")->default(true);
            $table->boolean("is_password_protected")->default(false);
            $table->string("password")->nullable();
            $table->dateTime("visible_from")->nullable();
            $table->dateTime("visible_until")->nullable();
            $table->foreignId("created_by")->constrained("users");
            $table->foreignId("updated_by")->constrained("users");
            $table
                ->string("slug")
                ->unique()
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("pages");
    }
}
