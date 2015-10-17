<!DOCTYPE html>
<html>
<head>
    <script src="http://maps.google.com/maps/api/js?sensor=false&v=3&libraries=geometry"></script>
</head>
<body>
    <p id="demo"></p>
    <button onclick="getLocation()" id="demo">get current location</button>
    <button onclick="console.log(distance + ' Metres')">get ditance from current location to other location</button>
    
    <script>
        var lat1 = 13.8129621;//13.8129621, 100.7174850
        var longt1 = 100.7174850;//13.817134, 100.744176
        var lat2 = 13.817134;
        var longt2 = 100.744176;
        var latLngA = new google.maps.LatLng(lat1, longt1);
        //var latLngB = new google.maps.LatLng(40.778721618334295, -73.96648406982422);
        var latLngB = new google.maps.LatLng(lat2, longt2);
        var distance = google.maps.geometry.spherical.computeDistanceBetween(latLngA, latLngB);
        var x=document.getElementById("demo");

        function getLocation(){
            if (navigator.geolocation){
                navigator.geolocation.getCurrentPosition(showPosition);
            }
            else{
                x.innerHTML="Geolocation is not supported by this browser.";}
            }

        function showPosition(position){
            lat = position.coords.latitude;
            longt = position.coords.longitude;
            x.innerHTML="Latitude: " + lat + "<br>Longitude: " + longt; 
        }

        function getLocation() {
        	  navigator.geolocation.getCurrentPosition(
        	            function(position) {
        	                var latLngA = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
        	                var latLngB = new google.maps.LatLng(40.778721618334295, -73.96648406982422);
        	                var distance = google.maps.geometry.spherical.computeDistanceBetween(latLngA, latLngB);
        	                alert(distance + " Metres");//In metres
        	            },
        	            function() {
        	                alert("geolocation not supported!!");
        	            }
        	    );
        	}


    	/*
    	
    
    
    
    
  var rad = function(x) {
  return x * Math.PI / 180;
	};

	var p1 = 13.7353711;
	var p2 = 100.6353455;

	var lat;
    var longt;
    var latLngA = new google.maps.LatLng(lat, longt);
    var latLngB = new google.maps.LatLng(40.778721618334295, -73.96648406982422);
    var distance = google.maps.geometry.spherical.computeDistanceBetween(latLngA, latLngB);
    
    
	var getDistance = function(p1, p2) {
	  var R = 6378137; // Earth’s mean radius in meter
	  var dLat = rad(p2.lat() - p1.lat());
	  var dLong = rad(p2.lng() - p1.lng());
	  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
	    Math.cos(rad(p1.lat())) * Math.cos(rad(p2.lat())) *
	    Math.sin(dLong / 2) * Math.sin(dLong / 2);
	  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	  var d = R * c;
	  return d; // returns the distance in meter
	};
	console.log('xxx');
	var tddest = getDistance(p1, p2);
	console.log('ans' , tddest);
    	
    	
    	
    	*/
    </script>
</body>
</html>