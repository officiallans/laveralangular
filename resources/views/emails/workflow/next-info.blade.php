@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    На {{$date}} заплановано {{$type}}.<br>
    Підтвердіть його після виходу на роботу.
@endsection