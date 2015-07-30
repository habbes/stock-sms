<?php

require_once '../Lib.php';

while(true)
{

    echo "\n\nPreparing Broadcast...\n";
    $stocks = Stock::findAll();

    $recipients = Subscriber::findAll();

    $msg = "==Stocks Update==\n";
    foreach($stocks as $stock){
        $msg .= $stock->company.":".$stock->price."\n";
    }

    echo "Message is:\n$msg\n";

    foreach($recipients as $r){
        SMS::send($r->phone, $msg);
        echo "Sent to {$r->phone}\n";
    }

    echo "Send Complete. About to sleep...\n";
    echo "Sleeping...";

    //wait for 60
    sleep(30);
}
