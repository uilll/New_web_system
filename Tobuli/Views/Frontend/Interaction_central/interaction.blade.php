@extends('Frontend.Layouts.modal_interaction_central')
@section('title')
    <i class="icon alert"></i> {{ $text_title }}
@stop
@section('body')
        <div id="interaction_action">
    
            {!!Form::open(['route' => $route, 'method' => 'POST'])!!}
                {!!Form::hidden('id',$var_id)!!}     
                @if($questions)
                <span id="text_body"> {{ $text_title }}</span> 
                </br>
                </br>
                
                    <div class="tab-content">
                        <div class="form-check">
                            <div class="row">
                                <table>
                                    <tr>
                                        <td>
                                            <div class="col-lg-1">
                                                <input class="form-check-input" type="radio" name="cause" id="exampleRadios1" value="1" required="required">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12">
                                                <label class="form-check-label" for="exampleRadios1">
                                                
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="form-check">
                            <div class="row">
                                <table>
                                        <td>
                                            <div class="col-lg-1">
                                                <input class="form-check-input" type="radio" name="cause" id="exampleRadios2" value="2" required="required">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12">
                                                <label class="form-check-label" for="exampleRadios3">
                                                
                                                </label>
                                            </div>
                                        </td>
                                </table>
                            </div>
                        </div>
                        <div class="form-check">
                            <div class="row">
                                <table>
                                    <tr>
                                        <td>
                                            <div class="col-lg-1">
                                                <input class="form-check-input" type="radio" name="cause" id="exampleRadios3" value="3" required="required">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12">
                                                <label class="form-check-label" for="exampleRadios2">
                                                
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="form-check">
                            
                        </div>
                    </div>
                @else
                    <span id="text_body"> {{ $text_body }}</span> 
                    </br>
                    </br>
                @endif
                <br>
                <div class="tab-content">
                    {!! Form::label('deadline', 'Me lembre de novo em:') !!}
                    {!! Form::select('deadline', ['7'=>'7 dias', '14'=>'14 dias','30'=>'30 dias','60'=>'60 dias'], null, ['class' => '']) !!}
                </div>
                </br>
                </br>
            {!!Form::close()!!}
        </div>
@stop
