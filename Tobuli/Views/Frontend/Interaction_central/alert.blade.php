@extends('Frontend.Layouts.modal_alert')
@section('title')
    <i class="icon-fa fa {{$icon}}"></i> {{$title}}
@stop
@section('body')
        <div id="anchor" class="container" style="width: 100%; min-width: 100%">
                    </br>
                    </br>
                        <table style="width: 100%; min-width: 100%">
                            <td style="width: 100%; min-width: 100%" align="center">
                                <span><h3>{{$body}}</h3></span>
                            </td> 
                        </table>
                    </br>
        </div>
@stop