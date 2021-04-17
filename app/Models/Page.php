<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Page
 * @package App\Models
 * Represents a page on the website.
 */
class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "title",
        "content",
        "is_draft",
        "is_private",
        "is_password_protected",
        "password",
        "created_by",
        "updated_by",
        "slug",
        "visible_from",
        "visible_until",
    ];

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * The attributes that are hidden.
     */
    protected $visible = [
        "title",
        "content",
        "is_password_protected",
        "updated_at",
        "created_at",
        "created_by",
        "updated_by",
    ];

    /**
     * Run these when the model is booted.
     * On creation, autoset updated and created by.
     * On update, autoset updated by.
     */
    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            $page->autosetUpdatedBy();
            $page->autosetCreatedBy();
        });
        static::updating(function (Page $page) {
            $page->autosetUpdatedBy();
        });
    }

    /**
     * Will autoset the updated_by to logged in user.
     */
    private function autosetUpdatedBy(): void
    {
        $this->updated_by_id = auth()->id();
    }

    /**
     * Will autoset the created_by to logged in user.
     */
    private function autosetCreatedBy(): void
    {
        $this->created_by_id = auth()->id();
    }

    /**
     * The relation with a user who created the page.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by_id");
    }

    /**
     * The relation with a user who updated the page last.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by_id");
    }
}
