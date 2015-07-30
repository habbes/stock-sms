<?php

require_once "../Lib.php";

while(true)
{

    echo "\n\nFetching Messages...\n";
    $smss = SMS::fetch();


    foreach($smss as $sms){
        echo "Received Message From: {$sms->sender} at {$sms->time}:\n";
        echo "$sms->message\n\n";

        $sub = new Subscriber();
        $sub->phone = $sms->sender;
        $sub->save();

        $msg = $sms->message;

        switch(strtolower($msg)){
            case '_stop':
                $reply = "You will no longer receive our stock updates. Thank you for using our service.";
                $sub->delete();
                echo "Subscriber deleted...\n";
                break;
            default:
                $company = $msg;
                $stock = Stock::find($company);

                if(!$stock){
                    $reply = "Dear Subscriber, the specified company '$company' was not found.";
                }
                else {
                    $reply = "Stock Price for $company: {$stock->price}";
                }
        }
        
        SMS::send($sms->sender, $reply);

        echo "Sent Reply to {$sms->sender}:\n$reply\n";

        SMS::delete($sms);
        echo "Message deleted\n\n";
    }


    //wait for 5 seconds
    echo "Sleeping...";
    sleep(5);
}
