document.addEventListener("DOMContentLoaded", function () { /* This add the mapbox token that uses MapKick to work*/
    const mapboxToken = document.getElementById("mapbox-token").getAttribute("data-token");
    mapboxgl.accessToken = mapboxToken;
});
