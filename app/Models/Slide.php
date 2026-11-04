<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $text_one
 * @property string $text_two
 * @property string $paragraph
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\SlideFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Slide newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereParagraph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereTextOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereTextTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class Slide extends Model
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
