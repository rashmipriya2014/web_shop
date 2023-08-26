# Web Shop API - An order management feature
#### Here will find an API's where we can do the following things
- Get all orders
- Place order
- Delete order
- Can add product to order
- Pay to order
### Import csv data
- configure the url where your csv file is located in .env file
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/13e8595f-d1c6-41ae-a59a-9fa71c82761d)

#### To import data run following command in terminal
php artisan import:data

#### An additional table order_product has been created . To migrate run follwing command
php artisan migrate

-----------------
## Features
### Get all orders
* Webhook - <base_url>/api/orders
* Method - GET
* Response 
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/3e0e0e5c-e24e-4559-abe9-23b76b23f650)

### Place order
* Webhook - <base_url>/api/make-order
* Method - POST
* Request 
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/724d50e9-871c-4e64-855b-0970d7bfc916)
* Response
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/fb5fd25b-271b-4c6e-8dac-d498d046d8b0)

### Delete order
* Webhook - <base_url>/api/make-order
* Method - POST
![image](https://github.com/rashmipriya2014/web_shop/assets/36446909/68994852-cf0d-4ad1-a827-22c5a42ac0e6)

### Add product to order
* Webhook - <base_url>/api/orders/{order_id}/add
* Method - POST
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/3b45a88a-4e33-4fd9-a631-5f81595364f5)

### Pay to order
* Webhook - <base_url>/api/orders/{order_id}/pay
* Method - POST
![image](https://github.com/rashmipriya2014/js-pagination/assets/36446909/8a675c9f-af5c-4e1a-9ff2-43756cbf8c93)