<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('traderId');
            $table->integer('partnerId');
            $table->integer('workflowId');
            $table->string('poNumber');
            $table->string('soNumber');
            $table->string('invoiceNumber');            
            $table->enum('status', ['RC','ET','IC','SH','IS','PD','CN','CM','ER','BO','RR','SW','OD','NS','RJ','PSC','SLC','DC','CLSD','CMPL','PSN','SLN','PI']);			
			$table->enum('sent_to_seller',['Y','N'])->default('N');
			$table->enum('errored',['Y','N'])->default('N');
			$table->string('errorDescription');
			$table->enum('shipped',['Y','N'])->default('N');
			$table->text('comment');
			$table->string('packingSlipFileName');
			$table->string('shippingLabelFileName');
			$table->string('shipmentPriority');
			$table->string('shipmentCarrier');
			$table->integer('sellerPartner');
			$table->string('poDate');
			$table->string('shipDate');
			$table->string('ediTrasactionSetControlNumber');
			$table->string('ediTransactionSetIdentifierCode');
			$table->string('ediTransactionType');
			$table->string('generateShippingLabel');
			$table->string('rawOrder');
			$table->string('hasOutofStockItems');
			$table->string('shippingLabel');
			$table->string('packingSlip');
			$table->integer('genCartonLabel');
			$table->integer('error_number');
			$table->string('shipByDate');
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
        Schema::dropIfExists('new_orders');
    }
}
