$(function () {
  // Manejar el envío del formulario
  $("#filterForm").on("submit", function (filter) {
    filter.preventDefault(); // Evitar que el formulario se envíe de forma tradicional

    var formData = $(this).serialize();

    $.ajax({
      url: "/api/events/filter",
      type: "GET",
      data: formData,
      success: function (response) {
        $("#eventResults").empty();

        if (response.length === 0) {
          $("#eventResults").html('<div class="col-12"><div class="alert alert-info">No se encontraron eventos con los filtros seleccionados.</div></div>');
          return;
        }

        // Recorrer la lista de eventos y mostrarlos
        $.each(response, function (index, event) {
          var eventCard = `
			<div class="flex-grow-0" style="width: calc(33.333% - 1rem); min-height: 300px;">
			<div class="card h-100 shadow-sm">
				<div class="position-relative">
				<img class="card-img-top" src="${event.image}" alt="${event.name}" />
				<span class="position-absolute top-0 end-0 bg-warning text-dark m-2 px-2 py-1 rounded-pill fs-6">${event.type.name}</span>
				</div>
				<div class="card-body d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="bg-primary text-white px-2 py-1 rounded-2">
  						<i class="fa-solid fa-calendar-days me-1"></i> 
  						${new Date(event.date).toLocaleDateString("en-US", { weekday: "short", day: "2-digit", month: "short", year: "numeric" })}
					</span>
					<span class="text-danger fw-bold">$${event.budget} MXN</span>
				</div>
				<h3 class="card-title h5 fw-bold">${event.name}</h3>
				<p class="card-text text-muted mb-3"><i class="fas fa-map-marker-alt me-1"></i>${event.location ? event.location.address : ""}</p>
				<p class="card-text">${event.description}</p>
				<div class="d-flex justify-content-between mt-auto">
					<span class="text-muted"><i class="fas fa-clock me-1"></i>${event.time || ""}</span>
					<a href="/event/${event.id}" class="btn btn-sm btn-outline-primary">Ver detalles</a>
				</div>
				</div>
			</div>
			</div>
		`;

          $("#eventResults").append(eventCard);
        });
      },
      error: function (error) {
        console.error("Error al filtrar eventos:", error);
        $("#eventResults").html('<div class="col-12"><div class="alert alert-danger">Ocurrió un error al buscar eventos. Por favor, intenta nuevamente.</div></div>');
      },
    });
  });
});
