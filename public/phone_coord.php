<script>
	var d = new Date();
	d.setTime(d.getTime() + (0.0006944445*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	var retorno="current+location";
	function getLocation() {
		
		if (navigator.geolocation) {
			retorno = navigator.geolocation.getCurrentPosition(function (position) {		
			retorno = position.coords.latitude + "," + position.coords.longitude;
			document.cookie = "coord_phone="+retorno+ ";" + expires; 
			
		}, showError);
		} else { 
			retorno = "current+location";
		}
		
		
	}

	
	function showError(error) {
		switch(error.code) {
			case error.PERMISSION_DENIED:
				retorno =  "User denied the request for Geolocation."
				break;
			case error.POSITION_UNAVAILABLE:
				retorno =  "Location information is unavailable."
				break;
			case error.TIMEOUT:
				retorno =  "The request to get user location timed out."
				break;
			case error.UNKNOWN_ERROR:
				retorno =  "An unknown error occurred."
				break;
		}
	}
	
//console.log(retorno);
	getLocation();
</script>
<?php
    //$retorno =  '<script>document.write(getCookie(coord_phone));</script>';
    //echo $retorno;
    //return $retorno;
?>


