
function setFieldBehavior(service) {
	$('input#settings-smartmap-service-'+service).on('change', function (e) { // fix line
		if ($(this).is(':checked')) {
			$('div#settings-smartmap-fields-'+service).slideDown('fast');
		} else {
			$('div#settings-smartmap-fields-'+service).slideUp('fast');
		}
	});
}

function checkFieldValue(service) {
	if ($('input#settings-smartmap-service-'+service).is(':checked')) {
		$('div#settings-smartmap-fields-'+service).slideDown('fast');
	} else {
		$('div#settings-smartmap-fields-'+service).slideUp('fast');
	}
}

setFieldBehavior('google');
setFieldBehavior('maxmind');

checkFieldValue('google');
checkFieldValue('maxmind');