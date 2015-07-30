<?php

require_once "../Lib.php";


?>
<!DOCTYPE html>
<html>
    <head>
        <title>Stock</title>
        <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                padding-top:90px;
                background-color:#354547;
                font-family:segoe ui;
            }

            .content {
                border-radius:3px;
                padding:20px;
                background-color:white;
            }

            .page-title {
                margin-top:0px;
            }

            h3 {
                margin-top:0px;
            }


        </style>
    </head>
<body ng-app="app">

    <div class="container-fluid" id="main" ng-controller="MainController">
        <div class="row">
            <div class="content col-sm-10 col-sm-offset-1 ">
                <div class="nav col-sm-3">
                    <ul class="nav nav-pills nav-stacked">
                      <li role="presentation" ng-class="{active: page=='edit'}" ng-click="setPage('edit')"><a href="#">Stocks</a></li>
                      <li role="presentation" ng-class="{active: page=='subscribers'}" ng-click="setPage('subscribers')"><a href="#">Subscribers</a></li>
                    </ul>
                </div>
                <div class="page col-sm-9">
                    <h2 class="page-title">{{pageTitle}}</h1>
                    <div class="page-content" ng-show="page=='edit'">
                        <div class="col-sm-4 pull-right">
                            <h3>Add/Edit</h3>
                            <form class="form" name="stockForm">
                                <div class="form-group">
                                    <input class="form-control" type="text" name="company" ng-model="stockCompany" placeholder="Company" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="text" name="price" ng-model="stockPrice" placeholder="Price" required>
                                </div>
                                <div class="form-group col-sm-6">
                                    <button class="btn btn-success form-control" ng-click="saveStock()">Save</button>
                                </div>
                                <div class="form-group col-sm-6">
                                    <button class="btn btn-danger form-control" ng-click="deleteStock()">Delete</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-8">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Stock Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="stock in stocks" ng-click="activeStock(stock)">
                                        <td>{{stock.company}}</td>
                                        <td>{{stock.price}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="page-content" ng-show="page=='subscribers'">
                        <div class="col-sm-4 pull-right">
                            <h3>Broadcast Message</h3>
                            <form class="form" name="messageForm">
                                <div class="form-group">
                                    <textarea class="form-control" rows="5" ng-model="message" placeholder="Enter your message" required></textarea>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-success form-control" ng-click="sendMessage()">Send</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-8">
                            <table class="table">
                                <thead>
                                    <tr><th>Phone</th><th>Stocks</th></tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="subscriber in subscribers">
                                        <td>{{subscriber.phone}}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/angular/angular.min.js"></script>
<script src="bower_components/underscore/underscore-min.js"></script>
<script src="app.js"></script>
</body>
</html>
