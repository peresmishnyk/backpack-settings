<?php

namespace DummyNamespace;

use App\Http\Requests\Settings\DummyClassRequest;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Peresmishnyk\BackpackSettings\Http\Controllers\SettingsController;

/**
 * Class DummyClassCrudController
 * @package App\Http\Controllers\Admin\Settings
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DummyClassSettingsController extends SettingsController
{
    // ToDo: define settings key
    protected $key = 'dummy_class';

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setEntityNameStrings('dummy_class', 'DummyTable');
        CRUD::setValidation(DummyClassRequest::class);

        // Will be available as Settings::get('dummy_class.email')
        CRUD::addField([
            'name' => 'email',
            'type' => 'text',
        ]);

        // Will be available as Settings::get('dummy_class.options')
        CRUD::addField([   // Table
            'name' => 'options',
            'cast' => 'array',  // Custom property cast
            'label' => 'Options',
            'type' => 'table',
            'entity_singular' => 'option', // used on the "Add X" button
            'columns' => [
                'name' => 'Name',
                'desc' => 'Description',
                'price' => 'Price'
            ],
            'max' => 5, // maximum rows allowed in the table
            'min' => 0, // minimum rows allowed in the table
        ]);

        // Will be available as Settings::get('dummy_class.status')
        CRUD::addField([
            'name' => 'status',
            'type' => 'checkbox',
            'cast' => 'boolean'     // Custom property cast
        ]);
    }
}

