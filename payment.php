<?php
    if ($_POST['tokenId']) {

        require_once('vendor/autoload.php');

        //stripe secret key or revoke key
        $stripeSecret = 'sk_test_51H5Pc4K0PVU97suHFJIlHvoNnToDsKClKAdyjsEnlIFpstWM2YfHZRz47ICRPquQGIZWFTVSeCQ2c3lpBStqS5Fp00wwdrytEV';

        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($stripeSecret);

        // Get the payment token ID submitted by the form:
        $token = $_POST['tokenId'];

        // Charge the user's card:
        $charge = \Stripe\Charge::create(array(
            "amount" => $_POST['amount'],
            "currency" => "usd",
            "source" => $token,
        ));

        // after successfull payment, you can store payment related information into your database

        $data = array('success' => 1, 'data' => $charge);

        $chargeID = $charge->id;

//        $customerId = \Stripe\Charge::retrieve($charge->id);
//        echo $customerId;



        header('Location: sales.php?pay=' . $chargeID);
    }

