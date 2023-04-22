@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" width="100%">

    <div class="panel-heading"> 
        <div class="panel-title"><i class="icon logs"></i> Pesquisar Recebimentos Vencidos</div>
			<div class="panel-form">
				<div class="form-group search">
					{!! Form::text('search_phrase', null, ['id' => 'search_receita', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
					<span id='search_menu' style="display:none">monitoring</span>
				</div>
				<a href="javascript:" data-modal="monitorings_info"
					data-url="{{ route('admin.chips.import_filter') }}">
					
				</a>
				
				
				
			</div>
	</div>
<div width="100%">
   <table style="width:100%">
   <h3>RELAÇÃO DE CREDITO</h3>
   
      <th width="10%"> cliente </th>
	  <th>LIQUIDADO</th>
	  <th> VALOR </th>
	  <th width="100xp">DATA DE VENCIMENTO</th>
	  <th> VALOR PAGO </th>
	 <th>OBSERVAÇOES</th>

	 <?php
		$collor = true;
		?>
	@foreach ($response as $credito)
		<?php
			$collor = !$collor;  
		?>
		<tr style="background-color: {{$collor ? 'white' : 'grey'}}; color: {{$collor ? 'black' : 'white'}}">
		   <td>{{$credito->nome_cliente}}</td>
		   <td>{{$credito->liquidado_rec}}</td>
		   <td>{{$credito->valor_rec}}</td>
		   <td>{{ date( 'd/m/Y' , strtotime($credito->vencimento_rec))}}</td>
		   <td>{{$credito->valor_pago}}</td>
		   <td>{{$credito->observacoes_rec}}</td>
		</tr>
	
	@endforeach
	
	


</table>




</div>		  
		

</div>


@stop