<?php 

namespace CupNoodles\ContactForms;

use Admin\Models\Menus_model;
use System\Classes\BaseExtension;
use DB;
use Event;
use App;
use Igniter\Flame\Exception\ApplicationException;

/**
 * Butcher Extension Information File
 */
class Extension extends BaseExtension
{
    /**
     * Returns information about this extension.
     *
     * @return array
     */
    public function extensionMeta()
    {
        return [
            'name'        => 'ContactForms',
            'author'      => 'CupNoodles',
            'description' => 'Framework to allow multiple types of contact forms.',
            'icon'        => 'fa-envelope',
            'version'     => '1.0.0'
        ];
    }

    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {

     


    }


    public function registerFormWidgets()
    {

    }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'CupNoodles\ContactForms\Components\ContactForm' => [
                'code' => 'contactForm',
                'name' => 'lang:cupnoodles.contactforms::default.text_component_title',
                'description' => 'lang:cupnoodles.contactforms::default.text_component_desc',
            ]
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [

        ];
    }

    public function registerNavigation()
    {
        return [
            'marketing' => [
                'child' => [
                    'contactforms' => [
                        'priority' => 20,
                        'class' => 'pages',
                        'href' => admin_url('cupnoodles/contactforms/contactforms'),
                        'title' => lang('cupnoodles.contactforms::default.side_menu'),
                        'permission' => 'Admin.ContactForms'
                    ],
                ],
            ],
        ];
    }
}
