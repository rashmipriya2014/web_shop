<?php

namespace App\Http\Controllers\Api;

use DB;
use Log;
use Exception;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    const UN_PAID = 0;
    const PAID = 1;
    /**
     * Get orders / order 
     * @param order_id optional   
     * 
     * @return json 
     */
    public function getOrders() {
        $columns = [
            'orders.ID as order_id',
            'orders.customer as customer_id',
            'c.FirstName LastName as customer_name',
            'op.product_id as product_id',
            'p.productname as product_name',
            'p.price as price',
            'orders.payed as payed',
            'op.created_at as product_added_on'
        ];
        $qry = $this->getOrderQry($columns);
        $orders =  $qry->get();       

        $data = [];
        foreach($orders as $order) {
            $data[$order->order_id]['order_id'] = $order->order_id;
            $data[$order->order_id]['customer_id'] = $order->customer_id;
            $data[$order->order_id]['customer_name'] = $order->customer_name;
            $data[$order->order_id]['payed'] = $order->payed;
            $data[$order->order_id]['products'][] = [
                'product_id' => $order->product_id,
                'price' => $order->price,
                'product_added_on' => $order->product_added_on,
            ];            
        }      

        return response()->json($data);
    }

    /**
     * get order details
     * 
     * @param columns 
     * @param order_id
     * 
     * @retunn Qry
     */

    private function getOrderQry($columns = [], $order_id = 0) {  
        $qry = Order::leftJoin('order_products as op', 'op.order_id' , 'orders.ID')
            ->leftJoin('products as p', 'p.ID', 'op.product_id')
            ->leftJoin('customer as c', 'c.ID', 'orders.customer');      
        
        if($order_id) {
            $qry->where('orders.ID', $order_id);
        }   

        return $qry->select($columns);
    }

    /** make order 
     * @param request - Instance of request
     * 
     * @return json
    */
    public function makeOrder(Request $request) {
        $product_ids = $request->product_ids ?? [];
        $customer_id = $request->customer_id ?? [];
        try {
            if(empty($product_ids) || empty($customer_id) ) {
                throw new Exception('Invalid parameters');
            }
            DB::beginTransaction();

            ## Insert into orders
            $order_id = Order::insertGetId([
                'customer' => $customer_id ,
                'payed' => self::UN_PAID,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

            Log::info('order created', ['order_id' => $order_id]);
            ## Insert into order_products
            $order_products = [];
            foreach($product_ids as $prod_id) {
                $order_products[] = [
                    'order_id' => $order_id,
                    'product_id' => $prod_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ];
            }

            OrderProduct::insert($order_products);

            DB::commit();

            $res = [
                'status' => 200,
                'message' => 'Order placed successfully',
                'order_id' => $order_id
            ];
        }
        catch(Exception $e) {
            DB::rollback();
            Log::error( $e->getMessage());
            $res = [
                'status' => 500,
                'message' => $e->getMessage(),
                'order_id' => $order_id
            ];
        }

        return response()->json($res);           
    }

    /** Delete order
     * @param request - Instance of request
     * 
     * @return json
     */
    public function deleteOrder(Request $request) {
        $order_id = $request->order_id ?? '';
        try {
            if(!$order_id) {
                throw new Exception('Invalid parameters');
            }
            DB::beginTransaction();
            
            ## delete from order products
            OrderProduct::where('order_id', $order_id)->delete();

            ## delete from orders
            $order_id = Order::where('ID', $order_id)->delete();          

            DB::commit();

            $res = [
                'status' => 200,
                'message' => 'Order deleted successfully',
            ];
        }
        catch(Exception $e) {
            DB::rollback();
            Log::error( $e->getMessage());
            $res = [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($res);   
    }

    /**
     * Add product to order
     * @param request - Instance of request 
     * @param id - order id
     * 
     * @return json
     */
    public function addProductToOrder(Request $request, $id) {
        $product_id = $request->product_id ?? 0;
        try {
            $order = Order::where('ID', $id)->first();
            $product = Product::where('ID', $product_id)->first();

            ## Validation
            if(!$order) {
                throw new Exception('Invalid order id');
            }
            if(!$product) {
                throw new Exception('Invalid product id');
            }
            if($order->payed == self::PAID) {
                throw new Exception('Cannot add product to the order anymore. The order payment has been done');
            }

            ## add product to order                  
            OrderProduct::create([
                'order_id' => $id,
                'product_id' => $product_id
            ]);
            $res = ['product_id' => $product_id];
        }
        catch(Exception $e) {
            Log::error( $e->getMessage());
            $res = [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($res);
    }

    /**
     * pay to order
     */
    public function pay($id) {
        $columns = [
            'orders.ID as order_id',
            'Email Address as email',
            DB::raw("SUM(price) as total_amt")
        ];
        $qry = $this->getOrderQry($columns, $id);
        $order =  $qry->groupby('orders.ID', 'Email Address')->first();
        Log::info('order'.$order );

        #validate 
        if(!$order) {
            return response()->json(['message' => 'Invalid input']);
        }
        $data = [
            'order_id' => $id,
            'customer_email' => $order->email,
            'value' => $order->total_amt,
        ];

        ## pay with superpay
        $response = $this->payWithSuperPay($data);       

        ## response
        Log::info('super pay response '. $response );
        $res = json_decode($response);
        if($res->message == 'Payment Successful') {
            ## update to order table
            Order::where('ID',$id)->update(['payed' => self::PAID]);
            Log::info(' order payed', ['order_id' => $id]);
        }
        echo $response;
    }

    /**
     * Pay with superpay
     */

    public function payWithSuperPay($data) {
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => env('SUPERPAY_URL'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

}
