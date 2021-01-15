@extends('emails.base')

@section('header')
    <h1>
        {{$name}}<br>
        <small>Звіт за {{date('d.m.Y')}}</small>
    </h1>
@endsection

@section('content')
    @foreach ($users as $id => $user)
        @if ($id > 0)
            <hr>
        @endif
        <h2>{{ $user['name'] }}</h2>
        <div>
            @forelse ($user['latest_reports'] as $day => $report_types)
                @foreach ($report_types as $type => $reports)
                    <h3>
                        {{$typeTranslate[$type]}}
                        @if ($type === 'planned') на @endif
                        {{date('d.m.Y',strtotime($day))}}</h3>
                    <ol>
                        @forelse ($reports as $report)
                            <li>
                                {{$report['name']}}
                                @if ($report['comment'])
                                    <br> Коментарій:
                                    <i>{{$report['comment']}}</i>
                                @endif
                            </li>
                        @empty
                            Нема звітів
                        @endforelse
                    </ol>
                @endforeach
            @empty
                Нема звітів
            @endforelse
        </div>
    @endforeach
@endsection