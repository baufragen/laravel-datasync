<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSyncLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sync_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->boolean('successful')
                ->comment('Was the syncing successful?');

            $table->string('model')
                ->comment('Which model type was synced?');

            $table->bigInteger('identifier')
                ->comment('Which model was synced exactly?');

            $table->string('action')
                ->comment('What action was supposed to happen?');

            $table->string('connection')
                ->comment('To which connection did the sync happen?');

            $table->text('payload')
                ->nullable()
                ->comment('Encrypted payload for unsuccessful syncs');

            $table->text('response')
                ->nullable()
                ->comment('Error response for unsuccessful syncs');

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
        Schema::dropIfExists('data_sync_logs');
    }
}
