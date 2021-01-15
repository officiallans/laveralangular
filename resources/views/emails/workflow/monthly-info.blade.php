@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    Привіт, {{$user['name']}}!<br>
    <table border="1" cellspacing="0" cellpadding="10" width="100%"
           style="margin-top: 10px; border-collapse: collapse;">
        <thead>
        <tr>
            <th colspan="3">Інформація про робочі процеси протягом {{$start_month->format('d.m.Y')}}
                - {{$end_month->format('d.m.Y')}}</th>
        </tr>
        </thead>
        @if (count($user['workflow']))
            <tbody>
            @foreach($user['workflow'] as $workflow)
                <tr style="@if (!$workflow->confirmed) background: indianred; @endif">
                    <th width="200">{{ trans('workflow.type.'.$workflow['type']) }}</th>
                    <td>
                        {{date('d.m.Y',strtotime($workflow->start_at))}}
                        @if ($workflow->end_at)
                            - {{date('d.m.Y',strtotime($workflow->end_at))}}
                        @endif
                    </td>
                    <td>
                        {{$workflow->smartDuration}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        @else
            <tfoot>
            <tr>
                <td>
                    Відсутні робочі процеси
                </td>
            </tr>
            </tfoot>
        @endif
    </table>
    <table border="1" cellspacing="0" cellpadding="10" width="100%"
           style="margin-top: 10px; border-collapse: collapse;">
        <thead>
        <tr>
            <th colspan="2">Інформація про баланс</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th width="200">Різниця</th>
            <td>{{$user['info']['balance']}}</td>
        </tr>
        <tr>
            <th>Відпрацювань</th>
            <td>{{$user['info']['working_off']}}</td>
        </tr>
        <tr>
            <th>Відгулів</th>
            <td>{{$user['info']['time_off']}}</td>
        </tr>
        <tr>
            <th>Лікарняних</th>
            <td>{{$user['info']['sick_leave']}}</td>
        </tr>
        <tr>
            <th>Відпусток</th>
            <td>{{$user['info']['vacation']}}</td>
        </tr>
        </tbody>
    </table>
@endsection