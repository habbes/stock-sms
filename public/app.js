angular.module('app',[])
.controller('MainController',['$scope', '$http',function($scope, $http){



    $scope.pages = {
        edit:{
            title:"Company Stock Prices",
        },
        subscribers:{
            title:"Subscribers",
        },
    };
  

    $scope.setPage = function(page){
        $scope.pageTitle = $scope.pages[page].title;
        $scope.page = page;
    };

    $scope.setPage('edit');

    $scope.stocks = [];

    $http.get('handle.php?request=stocks').success(function(data){
        $scope.stocks = data;
    });

    $scope.saveStock = function(){
        var cp = $scope.stockCompany;
        var price = $scope.stockPrice;
        if(!cp || !price) return;
        $http.get('handle.php?request=stock&company='+cp+'&price='+price).success(function(data){
            if(st = _.findWhere($scope.stocks, {company:data.company}))
                st.price = data.price;
            else
                $scope.stocks.push(data);

            $scope.stockCompany = '';
            $scope.stockPrice = '';
        });
    };

    $scope.deleteStock = function(){
        var cp = $scope.stockCompany;
        $http.get('handle.php?request=delete&company='+cp).success(function(data){
            $scope.stocks = _.reject($scope.stocks, function(s){return s.company == data.company});
            $scope.stockCompany = "";
            $scope.stockPrice = '';
        });
    };

    $scope.activeStock = function(stock){
        $scope.stockCompany = stock.company;
        $scope.stockPrice = stock.price;
    };

    $scope.message = "";
    $scope.subscribers = [];

    $scope.loadSubscribers = function(){
        $http.get('handle.php?request=subscribers').success(function(data){
            $scope.subscribers = data;
        });
    };

    // $scope.loadSubscribers();

    $scope.sendMessage = function(){
        if(!$scope.message) return;

        $http.get('handle.php?request=send&message='+$scope.message).success(function($data){
            $scope.message = "";
        });
    };

    var updateSubs = function(){
        $scope.loadSubscribers();
        setTimeout(updateSubs, 5000);
    };

    updateSubs();


}]);
