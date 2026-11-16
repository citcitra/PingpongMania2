<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSequenceToTrainingSessionsTable extends Migration
{
    public function up()
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->text('sequence')->nullable()->after('direction');
        });
    }

    public function down()
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('sequence');
        });
    }
}
