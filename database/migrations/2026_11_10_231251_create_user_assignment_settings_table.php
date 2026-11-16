
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUserAssignmentSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('user_assignment_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('last_admin_id')->nullable();
            $table->timestamps();
        });

        DB::table('user_assignment_settings')->insert([
            'id' => 1,
            'last_admin_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('user_assignment_settings');
    }
}
