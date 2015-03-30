<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTimetableTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('timetable', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->time('start_time');
			$table->time('end_time');
			$table->integer('classes_id')->index('timetable_ibfk_3');
			$table->integer('subject_id')->index('subject_id');
			$table->integer('users_id')->index('users_id');
			$table->dateTime('deleted_at');
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
		Schema::drop('timetable');
	}

}
