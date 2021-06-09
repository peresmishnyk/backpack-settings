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

    public function __construct()
    {
        if (!isset($this->key)) {
            throw new ConfigurationException(static::class . ' must declare protected property \'key\'');
        }
        return parent::__construct();
    }

    protected function setupUpdateOperation(){
//        foreach ($this->crud->getAllFieldNames() as $field_name) {
//            $this->crud->modifyField($field_name, ['fake' => true]);
//        };

        CRUD::addField([
            'name' => 'extras_casts',
            'type' => 'textarea',
            'value' => json_encode($this->casts)
        ]);
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
        $this->crud->entry = $this->crud->getModel()->find($this->key)->withFakes();

        dump($this->crud->getFields());
        // Make all fields fake

        dump($this->crud->entry->options);

        //$this->crud->entry->setCasts($this->casts);


        // Set additional cast ???
//        $this->crud->entry->setCasts(['options' => 'array']);
//        $this->crud->entry->extras_casts = $this->casts;
//        dd($this->crud->entry->options);
//        dump($this->crud->entry->getAttributes());
        return $this->edit($this->key);
    }


    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        //dd($this->crud->getFields());

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

}
