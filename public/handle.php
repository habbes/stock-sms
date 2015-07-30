<?php

require_once "../App.php";


$request = Request::any('request');
header('Content-Type:application/json');

function error($msg)
{
    http_response_code(500);
    die(json_encode(['error'=>$msg]));

}

try{
    switch($request)
    {
        case 'stock':
            $stock = new Stock();
            $stock->company = Request::any('company');
            $stock->price = Request::any('price');
            if(!$stock->company || !$stock->price)
                error('Invalid Input');
            $stock->save();
            $subscribers = Subscription::subscribers($stock);
            foreach($subscribers as $sub){
                $msg = "Stock Update\n{$stock->company}: {$stock->price}";
                $sub->send($msg);
            }
            echo json_encode($stock);
            break;
        case 'stocks':
            $stocks = Stock::findAll();
            echo json_encode($stocks);
            break;
        case 'delete':
            $company = Request::any('company');
            $stock = Stock::find($company);
            $stock->delete();
            echo json_encode($stock);
            break;

        case 'subscribers':
            $subscribers = Subscriber::findAll();
            echo json_encode($subscribers);
            break;
        case 'send':
            $msg = Request::any('message');
            $recs = Subscriber::findAll();
            foreach($recs as $r){
                SMS::send($r->phone, $msg);
            }
            echo json_encode(["success"=>true]);
            break;


    }
}
catch(Exception $e){
    http_response_code(500);
    echo $e->getMessage();
}
