<?php

namespace Modules\NsCustomFields\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFieldsTable extends Migration
{
    public function up()
    {
        Schema::table('nexopos_users_attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('nexopos_users_attributes', 'custom_fields')) {
                $table->json('custom_fields')->nullable();
            }
            // Cleanup old columns if they exist
            if (Schema::hasColumn('nexopos_users_attributes', 'custom_field_1')) {
                $table->dropColumn('custom_field_1');
            }
            if (Schema::hasColumn('nexopos_users_attributes', 'custom_field_2')) {
                $table->dropColumn('custom_field_2');
            }
        });
    }

    public function down()
    {
        Schema::table('nexopos_users_attributes', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
}
