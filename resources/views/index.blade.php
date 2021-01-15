<!doctype html>
<html ng-app="reporter">
<head>
    <title>ASD Reporter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{{$baseUrl}}">
    <script>
        var baseUrl = "{{$baseUrl}}";
    </script>
    @if (App::environment('production'))
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.1/angular.min.js"></script>
    @endif
    <script src="app/main.{{$stats->hash}}.js"></script>
</head>
<body ng-controller="ApplicationController as app">
<ui-view />
</body>
</html>