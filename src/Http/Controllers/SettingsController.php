<?php

namespace Peresmishnyk\BackpackSettings\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;
use Peresmishnyk\BackpackSettings\Exceptions\ConfigurationException;
use Peresmishnyk\BackpackSettings\Models\SettingsModel;

abstract class SettingsController extends CrudController
{
    use UpdateOperation;

    protected $key;
    protected $extras_casts = [];

    public function __construct()
    {
        if (!isset($this->key)) {
            throw new ConfigurationException(static::class . ' must declare protected property \'key\'');
        }

        return parent::__construct();
    }

    protected function setupDefaults()
    {
        CRUD::setModel(SettingsModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/' . \Settings::config('route_prefix'));

        parent::setupDefaults();
    }

    protected function setupConfigurationForCurrentOperation()
    {
        parent::setupConfigurationForCurrentOperation();

        foreach ($this->crud->getFields() as $field_name => $field_config) {
            // Make all field fake
            $this->crud->modifyField($field_name, ['fake' => true]);
            // Get extra casts
            if (isset($field_config['cast'])) {
                $this->extras_casts[$field_name] = $field_config['cast'];
            }
        };

        $this->crud->addField([
            'name' => 'extras_casts',
            'type' => 'hidden',
            'value' => json_encode($this->extras_casts)
        ]);

        // Merge extra casts to model
        $this->crud->model->mergeCasts($this->extras_casts ?? []);
    }


    /**
     * Define which routes are needed for this operation.
     *
     * @param string $name Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupUpdateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment . '/' . $routeName . '/edit', [
            'as' => 'settings.' . $routeName . '.edit',
            'uses' => $controller . '@editAdapter',
            'operation' => 'update',
        ]);

        Route::put($segment . '/' . $routeName, [
            'as' => 'settings.' . $routeName . '.update',
            'uses' => $controller . '@update',
            'operation' => 'update',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function editAdapter()
    {
        $model = $this->crud->getModel()->find($this->key);
        $model->mergeCasts($model->extras_casts);
        $this->crud->entry = $model->withFakes();
        return $this->edit($this->key);
    }

}
