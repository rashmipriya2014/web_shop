<?php

namespace App\Console\Commands;

use DB;
use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ImportData extends Command
{
    CONST TABLE_CUSTOMER = 'Customer';
    CONST TABLE_PRODUCTS = 'Products';
    CONST TABLE_ORDERS = 'Orders';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data to data base';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        /** Create Customer table and import data */
        $url = env('CUSTOMER_CSV_URL');
        $this->readAndImport($url, Self::TABLE_CUSTOMER);
       
        /** Create Products table and import data */
        $url = env('PRODUCT_CSV_URL');
        $this->readAndImport($url, Self::TABLE_PRODUCTS);

        /** Create Orders table  */
        $this->createTable(Self::TABLE_ORDERS);
    }

    /**
     * Read files and import
     * @param  url
     * @param table_name
     */
    private function readAndImport($url, $table_name) {        
        $file = fopen($url,"r");    
        $this->createTableAndImportData($file, $table_name);        
        fclose($file);
    }

    /**
     * Create table and import data from file
     * @param file
     * @param table_name
     */
    private function createTableAndImportData($file, $table_name) {
        $row = 0;  
        while(! feof($file)) {
            $data = fgetcsv($file);
            $col_data = []; 

            if($row ==  0) {
                /** create table their columns */
                $this->createTable($table_name);
            }
            else {
                foreach($data as $key => $val) {                
                    /** arrange data */
                    if($key == 0) {
                        continue;
                    }
                    $col_data = $this->arrangeData($table_name, $val, $col_data, $key);
                }
            }           

            if($row > 0) {
                $col_data['created_at'] = date("Y-m-d H:i:s");
                $col_data['updated_at'] = date("Y-m-d H:i:s");
                /**Insert data */
                $insert_data = DB::table($table_name)->insert($col_data);               
            }
            ++$row;
        } 
        if(isset($insert_data)) {
            Log::info($row-1 . ' records imported to table '. $table_name);
        }       
    }

    /** Arrange data 
     * @param table_name
     * @param col_data
     * 
     */
    private function arrangeData($table_name, $val, $col_data, $key) {        
        if($table_name ==  Self::TABLE_CUSTOMER) {
            $indexing = ['ID', 'Title', 'Email Address', 'FirstName LastName', 'registered_since' , 'phone' ];           
        }
        else if($table_name ==  Self::TABLE_PRODUCTS) {
            $indexing = ['ID', 'productname', 'price'];    
        }

        $col_data[$indexing[$key]] = $val;
        return $col_data;
    }

    /**
     * Create table
     * @param table_name
     */
    private function createTable($table_name) {
        if($table_name ==  Self::TABLE_CUSTOMER) {
            $this->createCustomerTable();
        }
        else if($table_name ==  Self::TABLE_PRODUCTS) {
            $this->createProductTable();
        }
        else if($table_name ==  Self::TABLE_ORDERS) {
            $this->createOrdersTable();
        }
    }

    /**
     * Create customer table
     */
    private function createCustomerTable() {
        Schema::dropIfExists(Self::TABLE_CUSTOMER);
        if (!Schema::hasTable(Self::TABLE_CUSTOMER)) {
            Schema::create(Self::TABLE_CUSTOMER, function (Blueprint $table) {
                $table->increments('ID');
                $table->text('Title');
                $table->text('Email Address');
                $table->text('FirstName LastName');
                $table->text('registered_since');
                $table->text('phone');
                $table->timestamps();
            });
            Log::info('Table ' . Self::TABLE_CUSTOMER .' created successfully');
        }
        else {
            Log::notice(Self::TABLE_CUSTOMER .' table already exist');
        }
    }

    /**
     * Create products table
     */
    private function createProductTable() {
        Schema::dropIfExists(Self::TABLE_PRODUCTS);
        if (!Schema::hasTable(Self::TABLE_PRODUCTS)) {
            Schema::create(Self::TABLE_PRODUCTS, function (Blueprint $table) {
                $table->increments('ID');
                $table->text('productname');
                $table->float('price', 8, 2);
                $table->timestamps();
            });
            Log::info('Table ' . Self::TABLE_PRODUCTS .' created successfully');
        }
        else {
            Log::notice(Self::TABLE_PRODUCTS .' table already exist');
        }
    }

    /**
     * Create orders table
     */
    private function createOrdersTable() {
        if (!Schema::hasTable(Self::TABLE_ORDERS)) {
            Schema::create(Self::TABLE_ORDERS, function (Blueprint $table) {
                $table->increments('ID');
                $table->integer('customer');
                $table->integer('payed');
                $table->timestamps();
            });
            Log::info('Table ' . Self::TABLE_ORDERS .' created successfully');
        }
        else {
            Log::notice(Self::TABLE_ORDERS .' table already exist');
        }
    }
    
}
