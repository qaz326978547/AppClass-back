<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleAccountToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 30)->nullable(); // 使用 google_id 一致
            $table->index(['google_id'], 'user_g_idx');
            $table->string('avatar')->nullable()->after('password');
            $table->string('provider_name')->nullable()->after('avatar');
            $table->string('provider_token')->nullable()->after('provider_name');
            $table->timestamp('last_login_at')->nullable()->after('provider_token');
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

            $table->dropIndex('user_g_idx');
            $table->dropColumn('google_account');
            $table->dropColumn('avatar');
            $table->dropColumn('provider_name');
            $table->dropColumn('provider_token');
            $table->dropColumn('last_login_at');
        });
    }
}