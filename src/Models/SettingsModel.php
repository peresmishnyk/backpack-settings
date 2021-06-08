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

    public static function find($id)
    {
        return self::firstOrCreate(['key' => $id]);
    }

    public function getAttribute($name)
    {
        return parent::getAttribute($name);
    }

}
