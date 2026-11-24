<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('email');
            $table->dropColumn('email_verified_at');

            $table->integer('participant_id', false, true)->after('id');
            $table->string('username')->after('participant_id')->unique();
            $table->enum('status', ['voted', 'not voted'])->after('remember_token');

            $table->foreign('participant_id', 'user_participant_foreign_id')->references('id')->on('participants')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');

            $table->dropColumn('participant_id');
            $table->dropColumn('username');
            $table->dropColumn('status');

            $table->dropForeign('user_participant_foreign_id');
        });
    }
}
