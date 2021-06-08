<?php

namespace Peresmishnyk\BackpackSettings\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;
use Peresmishnyk\BackpackSettings\Exceptions\ConfigurationException;
use Peresmishnyk\BackpackSettings\Models\SettingsModel;

abstract class SettingsController extends CrudController
{
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/settings');
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
     * Load routes for all operations.
     * Allow developers to load extra routes by creating a method that looks like setupOperationNameRoutes.
     *
     * @param string $segment Name of the current entity (singular).
     * @param string $routeName Route name prefix (ends with .).
     * @param string $controller Name of the current controller.
     */
    public function setupRoutes($segment, $routeName, $controller)
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Routes(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup' . $methodName . 'Routes'}($segment, $routeName, $controller);
            }
        }
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
            Route::get($segment . '/'.$this->key.'/edit', [
                'as' => $routeName . '.edit',
                'uses' => $controller . '@edit',
                'operation' => 'update',
            ]);

            Route::put($segment . '/'.$this->key, [
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
    public function edit()
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->key;
//        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $model = $this->crud->getModel();
        $entry = $model->firstOrCreate([$model->getKeyName() => $this->key]);
        $this->data['entry'] = $entry;
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;

        $this->data['id'] = $id;
        $this->data['breadcrumbs'] = [];


        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
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
