$(function () {
	/* Function t filter the events by parameters */
	// Function when the submit button in the form is pressed
	$("#filterForm").on("submit", function (filter) {
		filter.preventDefault(); // Prevent that the form is send in default form

		var formData = $(this).serialize(); // This will create the arguments by the data in the form

		$.ajax({
			url: "/api/events/filter", // ajax call to the endpoint of the API
			type: "GET",
			data: formData, // It sends the data as arguments
			success: function (response) { // If it is a success call it will do the next
				$("#eventResults").empty(); // It will clear previous results in the div

				if (response.length === 0) { // If the response contains 0 values it will show that it weren't found anything
					$("#eventResults").html('<div class="col-12"><div class="alert alert-info text-center">No events were found with the selected filters.</div></div>');
					scrollToResults();
					return;
				}

				// This iterate every event and make a card with its information
				$.each(response, function (index, event) {
					var eventCard = `
			<div class="col-12 col-sm-12 col-md-6 col-xl-4 mb-3">
			<div class="card h-100 shadow-sm">
				<div class="position-relative">
				<img class="card-img-top" src="/static/img/events/${event.image}" alt="${event.name}"  style="min-height: 280px; object-fit: cover;"/>
				<span class="position-absolute top-0 end-0 m-2 px-2 py-1 rounded-pill fs-6" style="background-color: #19c6db; color: #324b4f;">${event.type.name}</span>
				</div>
				<div class="card-body d-flex flex-column">
				<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
					<span class="px-2 py-1 rounded-2 mb-1" style="background-color: #005c83; color: white;">
  						<i class="fa-solid fa-calendar-days me-1"></i> 
  						${(() => {
								try { // This will format the date give it by the API in one more readable for the user
									if (!event.date) return "Date not available";
									const dateString = event.date.split(" ").slice(0, 4).join(" ");
									return dateString;
								} catch (e) {
									console.error("Error processing the date:", e, event.date);
									return "Date error";
								}
							})()}
					</span>
					<span class="px-2 py-1 rounded-3 fw-bold" style="background-color: #348774; color: white;">
              			<i class="fa-solid fa-money-bill me-2"></i>$${event.budget} MXN
            		</span>
				</div>
				<h3 class="card-title h5 fw-bold">${event.name}</h3>
				<p class="card-text text-muted mb-3"><i class="fas fa-map-marker-alt me-1"></i>${event.location ? event.location.address : ""}</p>
				<p class="card-text">${event.description}</p>
				<div class="d-flex justify-content-between mt-auto">
					<span class="text-muted"><i class="fas fa-clock me-1"></i>${event.time || ""}</span>
					<a href="/event/${event.id}" class="btn btn-sm" style="border-color: #005c83; color: #005c83;">View details</a>
				</div>
				</div>
			</div>
			</div>
		`;

					$("#eventResults").append(eventCard); // This add the card of the each event to the div
				});

				scrollToResults(); // This will scroll the page to the results
			},
			error: function (error) { // In case that an error ocurrs it will shown an error
				console.error("Error when filtering events:", error);
				$("#eventResults").html('<div class="col-12"><div class="alert alert-danger">An error occurred while searching for events. Please try again.</div></div>');
				// También desplazar en caso de error
				scrollToResults();
			},
		});
	});

	// Function to scroll
	function scrollToResults() {
		setTimeout(function () {
			var filterDiv = $("#event-filters");
			$("html, body").animate(
				{
					scrollTop: filterDiv.offset().top,
				},
				500
			); // Animation of 500ms for smooth scrolling
		}, 100);
	}
});
