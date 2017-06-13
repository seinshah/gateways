####[See Documentation With Better UX Here](https://hos69shah.github.io/gateways/)

# Composer Plugin To Support Following Gateways  

+ Mellat (Behpardakht)

**Also other gateways will be implemented soon, or feel free to do it on your own and merge it**  
  
  
---  
  
 
## Package Installation

1. **Downloading Package:**  
`php composer.phar require hos69shah/gateways`  

2. **Require Package In Project**  
If you already require global `vendor/autoload.php` step this part, otherwise:  
`require_once 'path/to/vendor/autoload.php';`  

3. **Using Namespace**  
Then you must use my namespace to access classes simply:  
`use Hossein\Gateway as HG;`  


## Mellat Documentation  
[See Live Usage Here](https://sattva.ir/plugin-test/sample/mellat.php)  

When you done with above steps, you can simply use package to connect to Mellat gateway using your credential info supplied by Behpardakht.  
  
#### Starting Payment  
First of all create an instantiate of Mellat class:  
`$mellat = new HG\Mellat();`  

Then you should supply some variables and credentials to start payment as below:  
```
$mellat->terminal = 'Your Terminal ID';
$mellat->username = 'Your Username';
$mellat->password = 'Your Password';
$mellat->order_id = mt_rand(0, 1000); //Unique number, Should be store somewhere to use it in callback
$mellat->amount = 1000; //Rials means 100 Toman
$mellat->callback = 'http://your-domain.com?specific_data_to_distinguish_request';
```

All variables are set now. **Remember if any of these variables didn't set, you'll encounter an exception before starting payment.**  

Now all you need to do is starting payment:  

```
try{
    $mellat->start_payment();
    
}catch(HG\AllException $ae){
    echo '<b>Result Code: </b>' . $mellat->result_code . '<br />';
    echo HG\Language::get($ae->getMessage());
    
}
```

**Note 1**  
You should use try-catch block for this plugin as it will through an exception in case of failure.  

**Note 2**  
You should catch all exception through `AllException` type.  

**Note 3**  
We can use `Language` class to translate exception messages as we see above.  
You can use it's static function `get($index)` to get human readable message.  
To get more information just look at `src/Language.php` and it's `lang_fa()` function to see indexes and messages. Also feel free to change message as you want or try to add extra language to it.  


#### Starting Callback  
When payment done or failed in bank side, user will redirect to address you provided before through `$mellat->callback`.  

Now assume that here is a page we consider as callback. First of all we should provide and set some credential limiter than previous:  

```
$mellat = new HG\Mellat();
$mellat->terminal = 'Your Terminal ID';
$mellat->username = 'Your Username';
$mellat->password = 'Your Password';
$mellat->order_id = 'Order ID'; //This is an order ID you supplied starting payment, you stored it and you can fetch it according to your callback parameters
```  

After these you should call request handler and wait for its response:  

```
try {
    $mellat->handle_payment();

    echo '<h1 style="color:green">Payment Was Successful</h1>';

    echo '<b>Sale Reference ID: </b>' . $mellat->sale_reference_id . '<br />';
    echo '<b>Sale Order ID: </b>' . $mellat->sale_order_id . '<br />';
    echo '<b>Card Holder Info: </b>' . $mellat->card_holder_info . '<br />';
    echo '<b>Card Holder Pan: </b>' . $mellat->card_holder_pan . '<br />';

} catch (HG\AllException $ae) {

    echo '<h1 style="color:red">Payment Was Unsuccessful</h1>';
    $mellat->refund_payment();
    echo '<b>Result Code: </b>' . $mellat->result_code . '<br />';
    echo HG\Language::get($ae->getMessage());

}
```

**Note 4**  
Same as note 1 to 3, read them carefully.  

**Note 5**  
If anything failed you can call `$mellat->refund_payment()` to return fee to use bank account if his/her balance decreased. Also this is optional and bank will do it automatically after some minutes.  

#### Properties  
All variables are accessible but not necessarily settable 

**$mode**  
Settable/Accessible
When everything is ok to redirect to bank page you have a few option to handle situation:  
+ direct-immediate:(default), Immediately redirect user to bank page.  
+ direct-delay: Transfer user to bank page after `$direct_delay` second.  
+ indirect: Get a string as `start_payment()` result contains javascript function to create and submit form to post reference id to bank.  
+ information: Get an array contains `index_name`, `index_value` and `traget`, you should redirect user to `target` through posting `target_value` with name `index_name` to `target`.  
 
**$direct_delay**  
Settable/Accessible  
Number of second when you are in `direct-delay` mode. (default: 3)  

**$terminal**  
Settable/Accessible  
Terminal ID provided by Behpardakht  

**$username**  
Settable/Accessible  
Username provided by Behpardakht  

**Password**  
Settable/Accessible  
Password provided by Behpardakht  

**$order_id**  
Settable/Accessible  
Unique ID for each transaction. You should generate and store this value in your database to use it during handling callback.  

**$amount**  
Settable/Accessible  
Amount of transaction fee that should be set in Rials and more than 0.  

**$callback**  
Settable/Accessible  
A link that user will be redirected from bank. you should set it as you can get user information from it and fetch transaction order ID from.  

**$reference_id**  
Just Accessible  
A string that will be generated on payment start for specific transaction 
 
**$result_code**  
Just Accessible  
A code that returned by bank after each step. Almost always 0 means success.  

**$sale_order_id**  
Just Accessible  
Long integer returned by bank contains an ID that should be store for further consistency.  

**$sale_reference_id**  
Just Accessible  
Long integer returned by bank contains an ID that should be store for further consistency.  

**$card_holder_info**  
Just Accessible  
...  

**$card_holder_pan**  
Just Accessible  
Contains first 6 digit and last 4 digit of card number that user use to pay transaction.  


#### Methods  

**init**(`$terminal`, `$username`, `$password`, `$order`, `$amount`, `$callback`, `$additional`)  
All are optional.  
Use this function to set all variables at one instead of individually.  

**start_payment()**  
To begin transaction and redirect user to bank. Use it after initializing variables and credentials.  
+ Will echo form and post it automatically.  
+ Will return javascript function that will create and submit a form.  
+ Will return an array to transfer user manually  
+ Will throw an AllException in case of failure
See `$mode` description for more information.  

**handle_payment**  
To handle payment after returning from bank. Use it on callback page and call it after initializing variables and credentials.  
+ Will set bank variables
+ Will throw an AllException in case of failure  

**refund_payment**  
To return fee to user account sooner. Use this function is optional and it should be used on catch block only.  
