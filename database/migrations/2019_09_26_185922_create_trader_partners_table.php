<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTraderPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trader_partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('trader_id')->unsigned();
			$table->foreign('trader_id')->references('id')->on('traders')->onDelete('cascade');
			$table->string('partnerId');
			$table->string('partnerName');
			$table->string('emailAddress');
			$table->string('ediInterchangeID');
			$table->string('ediQualifier');
			$table->string('traderInterchangeID');
			$table->string('traderIDQualifier');
			$table->string('ediX12Version');
			$table->string('ediGSIndustryIdentifierCode');
			$table->string('fieldSeperator');
			$table->string('ediSegmentSeperator');
			$table->string('ediLocationCode');
			$table->string('partnerType');
			$table->string('warehouseID');
			$table->string('sacValue');
			$table->string('netTerms');
			$table->string('termDiscountDays');
			$table->string('tradeInterface');
			$table->string('getOrderAdapter');
			$table->string('packingSlipAdapter');
			$table->string('shippingLabelAdapter');
			$table->string('cartonLabelAdapter');
			$table->string('genShippingLabel');
			$table->string('vendorId');
			$table->string('coupon');
			$table->string('shippingItemSKU');
			$table->string('shippingItemPrice');
			$table->string('downloadProtocol');
			$table->string('downloadURL');
			$table->string('downloadUserID');
			$table->string('downloadPassword');
			$table->string('downloadFolder');
			$table->string('uploadProtocol');
			$table->string('uploadURL');
			$table->string('uploadUserID');
			$table->string('uploadPassword');
			$table->string('uploadFolder');
			$table->string('uploadInventoryFolder');
			$table->string('uploadInvoiceFolder');
			$table->string('ftpType');
			$table->string('emailFormat');
			$table->string('authenticationFIle');
			$table->integer('customerProfileId');
			$table->string('customerPaymentProfileId');
			$table->string('shipmentNAL');
			$table->string('initialNST');
			$table->string('merchant_id');
			$table->string('marketplace_id');
			$table->string('accessToken');
			$table->string('accessTokenSecret');
			$table->string('messageTransfer');
			$table->string('sendIncrementalInventory');
			$table->string('customPrice');
			$table->string('partnerEdiVersion');
			$table->string('skuChangeCase');
			$table->string('invoiceCreate');
			$table->string('vatNumber');
			$table->string('validatePrice');
			$table->string('ftpActive');
			$table->string('erpCustomerCreate');
			$table->string('sendOrderConfirmationEmail');
			$table->string('overrideInventoryParameters');
			$table->string('shippingTerms');
			$table->string('AS2Id');
			$table->string('updateAmazonShippingTemplate');
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
        Schema::dropIfExists('trader_partners');
    }
}
