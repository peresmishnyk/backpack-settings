<?php

namespace Peresmishnyk\BackpackSettings\Models;

use App\Models\Article;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class SettingsModel extends Model
{
    use CrudTrait;

    protected $table = 'settings';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = ['key', 'extras', 'extras_casts'];
    protected $fakeColumns = ['extras'];

    protected $casts = [
        'extras' => 'array',
        'extras_casts' => 'array',
    ];

    protected $attributes = [
        'extras_casts' => '[]',
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            \Settings::refresh();
        });
    }

    public static function find($id, $columns = ['*'])
    {
        // Need refresh because after creating new model primary key equals 0. May be eloquent bug
        return self::firstOrCreate(['key' => $id])->refresh();
    }

    public static function findOrFail($id, $columns = ['*'])
    {
        return self::find($id, $columns);
    }
}
