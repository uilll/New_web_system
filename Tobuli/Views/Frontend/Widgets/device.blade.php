<div class="widget widget-device">
    <div class="widget-heading">
        <div class="widget-title">
            <div class="pull-right">
                <span data-device="status"></span> <span data-device="status-text"></span>
            </div>
            <i class="icon device"></i>
            <span data-device="name"></span>
        </div>
    </div>
    <div class="widget-body">
        <table class="table">
            <tbody>
			@if ( Auth::User()->isManager() || Auth::User()->isAdmin())                        
				<tr>
					<td>Proprietário:</td>
					<td><span data-device="object_owner"></span></td> 
				</tr>
				<tr>
					<td>Rastreador:</td>
					<td><span data-device="registration_number"></span></td> 
				</tr>
				<tr>
					<td>IMEI:</td>
					<td><span data-device="imei"></span></td>
				</tr> 
				<tr>
					<td>Chip:</td>
					<td><span data-device="sim_number"></span></td>
				</tr> 					 
				<tr>
					<td style="vertical-align: top">Notas Adicionais:</td>
					<td style="white-space: pre-line"><span data-device="additional_notes"></span></td> 
				</tr>				
			@endif
            <tr>
                <td>{{ trans('front.address') }}:</td>
                <td>
                    <span class="pull-right p-relative"><span data-device="preview"></span></span>
                    <span data-device="address"></span>
                </td>
            </tr>
            <tr>
                <td>{{ trans('front.time') }}:</td>
                <td><span data-device="time"></span></td>
            </tr>
			<tr>
                <td>{{ trans('front.stop_duration') }}:</td>
                <td><span data-device="stop_duration"></span></td>
            </tr>
            <tr>
                <td>{{ trans('front.plate') }}:</td>
                <td><span data-device="plate_number"></span></td>
            </tr>
            <tr>
                <td>{{ trans('front.driver') }}:</td>
                <td><span data-device="driver"></span></td>
            </tr>			
            </tbody>
        </table>
    </div>
</div>