<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZnectPartnerShippingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::dropIfExists('znect_partner_shipping_details');
        Schema::create('znect_partner_shipping_details', function (Blueprint $table) {
            //$table->bigIncrements('id');
            $table->integer('trader_id');
            $table->integer('partner_id');
            $table->string('shipping_type');
            $table->string('priority');
            $table->string('carrier');
            $table->string('shippingCarrier');
            $table->enum('packaging',['INDIVIDUAL']);
            $table->string('shipComplete');
            $table->string('zipCode');
            $table->string('senderAccountNumber');
            $table->string('accountNumber');
            $table->string('meterNumber');
            $table->string('username');
            $table->string('accountKey');
            $table->string('accountPassword');
            $table->string('fromFirstName');
            $table->string('fromLastName');
            $table->string('fromStreet1');
            $table->string('fromStreet2');
            $table->string('fromStreet3');
            $table->string('fromCity');
            $table->string('fromState');
            $table->string('fromZip');
            $table->string('fromCountry');
            $table->string('fromPhoneNumber');
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
        Schema::dropIfExists('znect_partner_shipping_details');
    }
}
