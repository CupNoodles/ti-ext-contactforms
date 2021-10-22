<?php

namespace CupNoodles\ContactForms\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddContactForms extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('contact_forms')) {
            Schema::create('contact_forms', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('contact_form_id');
                $table->string('name');
                $table->string('slug');
                $table->string('email');
                $table->string('subject');
                $table->text('form');
            });
        }


    }

    public function down()
    {

        Schema::dropIfExists('contact_forms');

    }

}
