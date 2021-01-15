@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    Привіт, {{$user->name}}!<br>
    Ваш пароль скинуто. Використовуйте новий пароль: <br>
    <b>
        {{$new_password}}
    </b>
    <br>
    В цілях безпеки радимо змінити його в профілі.

@endsection