<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEdiStatusNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::dropIfExists('edi_status_news');
        Schema::create('edi_status_news', function (Blueprint $table) {
            $table->integer('id');
			$table->integer('status_id');
			$table->integer('workflowId');
			$table->string('trader_id');
			$table->string('customer_id');
			$table->string('customer_po');
			$table->string('order_number');
			$table->enum('status', ['RC','ET','IC','SH','IS','PD','CN','CM','ER','BO','RR','SW','OD','NS','RJ','PSC','SLC','DC','CLSD','CMPL','PSN','SLN','PI']);
			$table->enum('acknowledged',['unacknowledged','acknowledged','rejected'])->default('unacknowledged');
			$table->enum('sent_to_seller',['Y','N'])->default('N');
			$table->enum('errored',['Y','N'])->default('N');
			$table->integer('error_number');
			$table->text('comment');
			$table->string('errorDescription');
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
        Schema::dropIfExists('edi_status_news');
    }
}
