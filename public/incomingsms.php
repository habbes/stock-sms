<?php
require_once "../App.php";


//TODO: callback url for handling incoming messages
//should allow user to subscribe to stocks, unsubscribe from stocks
//subscribe to all, unsubscribe from all, list available, get update of a particular stock

$message = Request::any('message');
$phone = Request::any('phone');

$subscriber = Subscriber::findBy('phone', $phone);
if(!$subscriber){
    //register new subscriber
    $subscriber->phone = $phone;
    $subscriber->insert();
    $welcome = "Welcome to the Stock Services.\n";

    $subscriber->send($welcome);
}

$parts = explode(" ",$message, 1);
$command = $parts[0];
$argStr = count($parts)>1? $part[1] : "";
$args = explode(',',$argStr);
$reply = "";

switch($command){
    case '_stop':
        $subs = null;
        if(!$argStr){
            $subs = Subscription::findBy("phone", $subscriber->phone);
        }
        else {
            $vals = [$subscriber->phone;
            $q = "phone=? AND company IN (";
            $last = count($args) - 1;
            foreach($args as $i=>$company){
                $vals[] = $company;

                $q .= $i == $last? "?)":"?,";

            }
            $subs = Subscription::select($q, $vals);
        }
        foreach($subs as $sub){
            $sub->delete();
        }
        $reply = "You have been successfully unsubscribed from stock updates for the selected companies.";
        break;
    case '_leave':

        break;
    case '_follow':
        $errors = [];
        foreach($args as $company){
            $stock = Stock::findBy("company", $company);
            if(!$stock){
                $errors[] = $company;
                break;
            }
            $subscriber->subscribe($company);
        }
        if(count($errors) == 0){
            $reply = "You have been successfully subscribed to stock updates for the selected companies.";
        }
        else {
            $reply = "Subscriptions to the following companies failed: ". implode("\n",$errors);
        }
        break;

    case '_subs':
        $stocks = Subscription::stocks($subscriber);
        $reply = "You are subscribed to stock updates for:";
        foreach($stocks as $stock){
            $reply .= "\n{$stock->company}";
        }
        break;
    case '_help':
        break;
    case '_list':
        $stocks = Stock::findAll();
        $companies = [];
        foreach($stocks as $stock) $companies[] = $stock->company;
        $reply = "Stocks are available for the following companies: ".implode("\n",$companies);
        break;
    default:
        $companies = explode(',',$message);
        $reply = "Stocks";
        foreach($companies as $company){
            $reply .= "\n$company: ";
            $stock = Stock::findBy('company', $company);
            $reply .= $stock? $stock->price : "Not found!";
        }

}

if($reply){
    $subscriber->send($reply);
}
