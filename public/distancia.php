<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Reverse Geocoding</title>
    <!--<style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #floating-panel {
        position: absolute;
        top: 10px;
        left: 25%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
      #floating-panel {
        position: absolute;
        top: 5px;
        left: 50%;
        margin-left: -180px;
        width: 350px;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
      }
      #latlng {
        width: 225px;
      }
    </style> -->
  </head>
  <body>
  <?php $lat = -11.381746666667;
  $lng = -40.002633333333; ?>
    <div id="floating-panel">
      <input id="latlng" type="text" value="<?php echo $lat ?>,<?php echo $lng ?>">
      <input id="submit" type="button" value="Reverse Geocode">
	  <input id="address" type="text">
    </div>
    <div id="map"></div>
    <script>      
		//var x = document.getElementById("latlng");
		//x.style.display = "none";
      function geocodeLatLng() {	
		var geocoder = new google.maps.Geocoder;
        var input = document.getElementById('latlng').value; "-11.381746666667,-40.002633333333"
        var latlngStr = input.split(',', 2);
        var latlng = {lat: parseFloat(latlngStr[0]), lng: parseFloat(latlngStr[1])};
        geocoder.geocode({'location': latlng}, function(results, status) {
          if (status === 'OK') {
            if (results[0]) {
			  document.getElementById('address').value = results[0].formatted_address;
            } else {
              document.getElementById('address').value = "Não encontrado"
            }
          } else {
            document.getElementById('address').value = "Falha no Geocode"
          }
        });
		geocodeLatLng2;
		
      }
    </script>
	<script> 
	
	function geocodeLatLng2() {
   // Client Side
	document.write("teste");
      var zipcode = $('44695-000').val();
	document.write(zipcode);
      Meteor.call('getLocationbyZipGoogleAPI', zipcode, function(error, result){
          if(error){
              console.log('error',error.reason);
          } else {
            var apidata = JSON.parse(result.content);
            var longname = apidata.results[0].address_components[3].long_name;
            var longaddress = apidata.results[0].formatted_address;
            var finaladdress = longaddress+', '+longname;
			document.getElementById('address').value = finaladdress;
			document.write(finaladdress);
          }
      });
	  }

      // Server Method to Call API

      'getLocationbyZipGoogleAPI': function(zip_code){
          // do checks
          var apiurl = 'http://maps.googleapis.com/maps/api/geocode/json?address='+zip_code+'&sensor=true';
          var result = HTTP.get( apiurl );
          return result;
      }
	  </script>
	
    <script 
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZ-pszuNy18ZMFBD-yf2vm1wAsothpD38&callback=geocodeLatLng">
    </script>	
  </body>
</html>


<!--// /* <!DOCTYPE html>
// <html>
  // <head>
    // <title>Passar Variável Javascript para PHP</title>
	// <script type="text/javascript">
		// var variaveljs = 'Mauricio Programador'; 
	// </script>
    // <style>
      // #right-panel {
        // font-family: 'Roboto','sans-serif';
        // line-height: 30px;
        // padding-left: 10px;
      // }

      // #right-panel select, #right-panel input {
        // font-size: 15px;
      // }

      // #right-panel select {
        // width: 100%;
      // }

      // #right-panel i {
        // font-size: 12px;
      // }
      // html, body {
        // height: 100%;
        // margin: 0;
        // padding: 0;
      // }
      // #map {
        // height: 100%;
        // width: 50%;
      // }
      // #right-panel {
        // float: right;
        // width: 48%;
        // padding-left: 2%;
      // }
      // #output {
        // font-size: 11px;
      // }
    // </style>
  // </head>
  // <body>
    // <div id="right-panel">
      // <div id="inputs">
        // <pre>
// var origin1 = {lat: -12.209793333333, lng: -38.969271111111};
// var destinationA = 'Capim Grosso,Bahia';
        // </pre>
      // </div>
      // <div>
        // <strong>Results</strong>
		// ?php 
		// $variavelphp = "<script>document.write(results[1].distance.text)</script>";
		// echo "Olá $variavelphp"; 
		// ?>
      // </div>
      // <div id="output">	</div>
    // </div>
    // <div id="map"></div>
    // <script>
      // function initMap() {
        // var bounds = new google.maps.LatLngBounds;
        // var markersArray = [];

        // var origin1 = {lat: -12.209793333333, lng: -38.969271111111};        
        // var destinationA = 'Capim Grosso,Bahia';

        // var destinationIcon = 'https://chart.googleapis.com/chart?' +
            // 'chst=d_map_pin_letter&chld=D|FF0000|000000';
        // var originIcon = 'https://chart.googleapis.com/chart?' +
            // 'chst=d_map_pin_letter&chld=O|FFFF00|000000';
        // var map = new google.maps.Map(document.getElementById('map'), {
          // center: {lat: -12.209793333333, lng: -38.969271111111},
          // zoom: 10
        // });
        // var geocoder = new google.maps.Geocoder;

        // var service = new google.maps.DistanceMatrixService;
        // service.getDistanceMatrix({
          // origins: [origin1],
          // destinations: [destinationA],
          // travelMode: 'DRIVING',
          // unitSystem: google.maps.UnitSystem.METRIC,
          // avoidHighways: false,
          // avoidTolls: false
        // }, function(response, status) {
          // if (status !== 'OK') {
            // alert('Error was: ' + status);
          // } else {
            // var originList = response.originAddresses;
            // var destinationList = response.destinationAddresses;
            // var outputDiv = document.getElementById('output');
            // outputDiv.innerHTML = '';
            // deleteMarkers(markersArray);

            // var showGeocodedAddressOnMap = function(asDestination) {
              // var icon = asDestination ? destinationIcon : originIcon;
              // return function(results, status) {
                // if (status === 'OK') {
                  // map.fitBounds(bounds.extend(results[0].geometry.location));
                  // markersArray.push(new google.maps.Marker({
                    // map: map,
                    // position: results[0].geometry.location,
                    // icon: icon
                  // }));
                // } else {
                  // alert('Geocode was not successful due to: ' + status);
                // }
              // };
            // };

            // for (var i = 0; i < originList.length; i++) {
              // var results = response.rows[i].elements;
              // geocoder.geocode({'address': originList[i]},
                  // showGeocodedAddressOnMap(false));
              // for (var j = 0; j < results.length; j++) {
                // geocoder.geocode({'address': destinationList[j]},
                    // showGeocodedAddressOnMap(true));
                // outputDiv.innerHTML += originList[i] + ' to ' + destinationList[j] +
                    // ': ' + results[j].distance.text + ' in ' +
                    // results[j].duration.text + '<br>';
              // }
            // }
          // }
        // });
      // }

      // function deleteMarkers(markersArray) {
        // for (var i = 0; i < markersArray.length; i++) {
          // markersArray[i].setMap(null);
        // }
        // markersArray = [];
      // }
    // </script>
    // <script async defer
    // src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCGObA2RXyXnOvHWkaussgUpygQNdwj5F0&callback=initMap">
    // </script>
	
  // </body>
// </html> */-->