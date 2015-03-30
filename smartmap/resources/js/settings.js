
var $maxmindInput = $('input#settings-smartmap-service-maxmind');
var $maxmindDiv   = $('div#settings-smartmap-fields-maxmind');

// Expand/collapse MaxMind field
function maxmindExpandCollapse() {
	if ($maxmindInput.is(':checked')) {
		$maxmindDiv.slideDown('fast');
	} else {
		$maxmindDiv.slideUp('fast');
	}
}

// On change, expand/collapse
$maxmindInput.on('change', function () {
	maxmindExpandCollapse();
});

// On page load, expand/collapse
$(function () {
	maxmindExpandCollapse();
});