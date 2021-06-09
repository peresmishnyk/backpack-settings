<?php

namespace Peresmishnyk\BackpackSettings\Models;

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

    protected $fillable = ['key','extras','extras_casts'];
    protected $fakeColumns = ['extras'];

    protected $casts = [
        'extras' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::retrieved(function($model){
            $model->setCasts(json_decode($model->extras_casts, JSON_OBJECT_AS_ARRAY) ?? []);
            //dump($model->getAttributes());
        });
    }


    public static function find($id)
    {
        // Need refresh because after creating new model primary key equals 0. May be eloquent bug
        return self::firstOrCreate(['key' => $id])->refresh();
    }

    public function setCasts(array $casts){
        $this->casts = array_merge($this->casts, $casts);
        dump($this->casts);
    }



}
