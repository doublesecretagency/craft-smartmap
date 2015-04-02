
var $maxmindDiv = $('div#settings-smartmap-fields-maxmind');

// Expand/collapse MaxMind field
function maxmindExpandCollapse() {
	if ('maxmind' == $('input.smartmap-geolocation-radio:checked').val()) {
		$maxmindDiv.slideDown('fast');
	} else {
		$maxmindDiv.slideUp('fast');
	}
}

$(function () {
	// On page load, expand/collapse
	maxmindExpandCollapse();
	// On change, expand/collapse
	$('input.smartmap-geolocation-radio').on('change', function () {
		maxmindExpandCollapse();
	});
});