<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_LEAD = 'lead';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_ALL = [
        self::ROLE_ADMIN,
        self::ROLE_LEAD,
        self::ROLE_EMPLOYEE
    ];

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', self::ROLE_ALL);
            $table->integer('form_id')->nullable();
            $table->json('assigned_form_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
