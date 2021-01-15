@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    Привіт, {{$user['name']}}!<br>
    У вас залишилось {{$user['diff']}} днів відпустки.
@endsection