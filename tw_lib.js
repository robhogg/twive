var params = null;

function initialise() {
	params = $.parseJSON($("#script-params").text());
	
	console.log(params.archive);

	init_chart();
}

function init_chart() {
	$(".chart_bar_container").bind("click", function(el) {
			var id = $(this).prop("id");

			try {
				date = id.match("([0-9]{4}-[0-9]{2}-[0-9]{2})-"
					+ "([0-9]{2}:[0-9]{2}:[0-9]{2})")
				if(params.chart.match("day")) {
					params.chart = params.chart.replace("day","week");
				} else {
					params.chart = params.chart.replace("week","day");
				}
			} catch(error) {
				console.log(error);
			}

			params.chartwe = date[1] + " " + date[2]; 
			qs = "archive=" + params.archive + "&chart=" + params.chart
				+ "&chartwe=" + encodeURIComponent(params.chartwe);
		 	url = params.dir + "/tw_chart.php?" + qs;

			console.log(url);

			$("#chart").load(url, function() {
				init_chart();
			});
	});
}
