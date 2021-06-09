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

    protected $fillable = ['key', 'value'];

//    protected $casts = ['value' => 'array'];

    public static function find($id)
    {
        return SettingsModel::firstOrCreate(['key' => $id]);
    }

    public function __get($key)
    {
        return parent::__get($key); // TODO: Change the autogenerated stub
    }

}
