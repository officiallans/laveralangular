@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    <p>
        Заповніть звіти про виконану роботу та заплануйте завдання на наступний робочий день.
    </p>
    <p style="text-align: right">
        <a style="background-color: #A23B72; color: #EAEAEA; padding: 10px; text-decoration: none;"
           href="{{Config::get('app.url') . '/reports/form'}}">Створити звіт</a>
    </p>
    <p style="color: #777; text-align: center">
        <small>
            Надсилання нагадувань можна налаштувати у вашому <a style="color: inherit" href="{{Config::get('app.url') . '/profile/index'}}">профілі</a>
        </small>
    </p>
@endsection