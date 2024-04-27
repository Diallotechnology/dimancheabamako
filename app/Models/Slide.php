<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text_one', 'text_two', 'paragraph', 'image'];

    public function getImageAttribute(): string
    {
        return Storage::url($this->attributes['image']);
    }

    public function DocLink(): string
    {
        return Storage::url($this->attributes['image']);
    }
}
