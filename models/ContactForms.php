<?php

namespace CupNoodles\ContactForms\Models;

use Model;

/**
 * UOM Model Class
 */
class ContactForms extends Model
{

    /**
     * @var string The database table name
     */
    protected $table = 'contact_forms';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'contact_form_id';

    public $relation = [];


}
