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

    abstract protected function setupUpdateOperation();

    public function __construct()
    {
        if (!isset($this->key)) {
            throw new ConfigurationException(static::class . ' must declare protected property \'key\'');
        }
        return parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(SettingsModel::class);
//        CRUD::setRoute(config('backpack.base.route_prefix') . '/' . Settings::config('route_prefix'));
        CRUD::setRoute(config('backpack.base.route_prefix') . '/' . \Settings::config('route_prefix'));
        CRUD::setEntityNameStrings('настройки', 'настройки');
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupUpdateDefaults()
    {
        $this->crud->allowAccess('update');

//        dd($this->crud->getModel());

        $this->crud->operation('update', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();

            if ($this->crud->getModel()->translationEnabled()) {
                $this->crud->addField([
                    'name' => 'locale',
                    'type' => 'hidden',
                    'value' => request()->input('locale') ?? app()->getLocale(),
                ]);
            }

            $this->crud->setupDefaultSaveActions();
        });

        $this->crud->operation(['list', 'show'], function () {
            $this->crud->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        });
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
        Route::get($segment . '/' . $this->key . '/edit', [
            'as' => $routeName . '.edit',
            'uses' => $controller . '@editAdapter',
            'operation' => 'update',
        ]);

        Route::put($segment . '/' . $this->key, [
            'as' => $routeName . '.update',
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
        $this->crud->entry = $this->crud->getModel()->find($this->key);
        return $this->edit($this->key);
    }

    /**
     * Update the specified resource in the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        // update the row in the db
//        dd($this->crud->getStrippedSaveRequest());
        $item = $this->crud->update($this->key,
            $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


}
