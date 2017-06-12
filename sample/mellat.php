<?php

require_once '../vendor/autoload.php';
require_once 'constants.php';

use Hossein\Gateway as HG;

$Mellat = new HG\Mellat();
$Mellat->terminal = TERMINAL;
$Mellat->username = USERNAME;
$Mellat->password = PASSWORD;
$Mellat->order_id = ORDER_ID;
//Or instead of setting values individually
//$Mellat->init(TERMINAL, USERNAME, PASSWORD, ORDER_ID);


if(isset($_GET['callback']))
{
    try {

        $Mellat->handle_payment();

        echo '<h1 style="color:green">Payment Was Successful</h1>';

        echo '<b>Sale Reference ID: </b>' . $Mellat->sale_reference_id . '<br />';
        echo '<b>Sale Order ID: </b>' . $Mellat->sale_order_id . '<br />';
        echo '<b>Card Holder Info: </b>' . $Mellat->card_holder_info . '<br />';
        echo '<b>Card Holder Pan: </b>' . $Mellat->card_holder_pan . '<br />';

    } catch (HG\AllException $ae) {

        echo '<h1 style="color:red">Payment Was Unsuccessful</h1>';
        $Mellat->refund_payment();
        echo '<b>Result Code: </b>' . $Mellat->result_code . '<br />';
        echo HG\Language::get($ae->getMessage());

    }
}
else
{
    try {
        $Mellat->amount = AMOUNT; //1000 Rials, 100 Tomans
        $Mellat->callback = CALLBACK;

        //Or instead of setting values individually
        //$Mellat->init(TERMINAL, USERNAME, PASSWORD, ORDER_ID, AMOUNT, CALLBACK);

        $Mellat->start_payment();


    } catch (HG\AllException $ae) {

        echo '<b>Result Code: </b>' . $Mellat->result_code . '<br />';
        echo HG\Language::get($ae->getMessage());

    }
}