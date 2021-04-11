<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
     *
     * @var array
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
    ];

    protected static function booted()
    {
        static::creating(function (Page $page) {
            $page->autoset_updated_by();
            $page->autoset_created_by();
        });
        static::updating(function (Page $page) {
            $page->autoset_updated_by();
        });
    }

    private function autoset_updated_by()
    {
        $this->created_by_id = auth()->id();
    }

    private function autoset_created_by()
    {
        $this->created_by_id = auth()->id();
    }

    public function created_by(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, "created_by_id");
    }

    public function updated_by(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by_id");
    }
}
