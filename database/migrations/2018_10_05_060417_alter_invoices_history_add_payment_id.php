<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInvoicesHistoryAddPaymentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices_history', function($column){
            $column->integer('payment_id')->nullable()->unsigned()->comment('Foreign key to payment_history primary key'); 
            $column->decimal('discount')->nullable()->comment('Discount provided on payment'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices_history', function($column){
            $column->dropColumn('payment_id'); 
            $column->dropColumn('discount'); 
        });
    }
}
