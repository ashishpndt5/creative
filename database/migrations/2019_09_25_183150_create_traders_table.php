<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traders', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('name');
			$table->string('email_address');
			$table->string('erp_adapter');
			$table->string('erp_domain');
			$table->string('erp_path');
			$table->string('erp_user');
			$table->string('erp_password');
			$table->string('edi_interchange_id');
			$table->string('edi_interchange_qualifier');
			$table->string('duns_number');
			$table->string('q_qrder_database_url');
			$table->string('q_qrder_database_user_name');
			$table->string('q_qrder_database_password');
			$table->string('q_qrder_database_name');
			$table->string('phone_number');
			$table->string('order_email_address');
			$table->string('street1');
			$table->string('street2');
			$table->string('street3');
			$table->string('city');
			$table->string('state');
			$table->string('zip');
			$table->string('country');
			$table->string('defaultWarehouseID');
			$table->string('packingSlipGenTiming');
			$table->string('clientId');
			$table->string('clientSecret');
			$table->string('redirectUri');
			$table->string('accessToken');
			$table->string('folderId');
			$table->string('erpAdapterVersion');
			$table->string('vatCheckRequired');
			$table->string('addressCheckRequired');
			$table->string('AS2Id');
			$table->string('venderIdOrderFile');
			$table->string('checkSku');
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
        Schema::dropIfExists('traders');
    }
}
