/**
 * 
 * JS For the main lander page
 * 
 */

// Add the map to front page
 var map = L.map('map').setView([60.180502, 24.951542], 11);
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
     attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
 }).addTo(map);

 // Get the Events
 fetch('/api/public/events', {
  headers: {
		'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
	},
 }).then(function (response) {
	// Set markers
  response.json().then(events => {
    events.forEach(element => {
      L.marker([element.latitude, element.longitude]).addTo(map)
      .bindPopup('<h2 class="map-title">'+ element.happening_name +'<h2><p class="map-text">' + element.happening_information + '</p></br>'+element.happening_starts+' - '+element.happening_ends);
    });
  })
}).catch(function (err) {
	// There was an error
	console.warn('Something went wrong.', err);
});
