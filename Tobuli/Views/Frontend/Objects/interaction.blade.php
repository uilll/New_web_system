@extends('Frontend.Layouts.modal_interaction')
@section('title')
    <i class="icon alert"></i> {{ $ocorrency->text_title }}
@stop
@section('body')
        <div id="interaction_action">
    
            {!!Form::open(['route' => 'objects.interaction_action', 'method' => 'POST'])!!}
                {!!Form::hidden('id',$ocorrency->id)!!}     
                <span id="Título"> Seu Veículo <font style="color: red"> {{ $ocorrency->vehicle_model }} </font> de placa <font style="color: red">{{ $ocorrency->plate_number }}</font> está <font style="color: red">{{ $ocorrency->text_1 }}</font>. SEU VEÍCULO ESTÁ NA LOCALIZAÇÃO DE ACORDO COM O RASTREADOR?</span> 
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
                                            Sim
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
                                            Não
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
                                            Não sei informar
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
                <br>
                </br>
                </br>
            {!!Form::close()!!}
        </div>
@stop
