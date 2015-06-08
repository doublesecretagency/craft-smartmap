
var prefix = '#types-SmartMap_Address-';
var $layoutTable = $('#types-SmartMap_Address-layout-table');

var fieldtypeSettings = {
	blueprint: function () {
		$('.layout-table-row').each(function () {
			var subfield = $(this).attr('id').replace(/^types-SmartMap_Address-layout-table-/, '');
			fieldtypeSettings.subfieldEnabled(subfield, $(this));
			fieldtypeSettings.subfieldWidth(subfield, $(this));
		});
	},
	subfieldEnabled: function (subfield, $el) {
		var $ltRow = $(prefix + 'layout-table-' + subfield);
		var $bpField = $(prefix + 'blueprint-' + subfield);
		var checked  = $el.find('.layout-table-enable input').is(':checked');
		if (checked) {
			$ltRow.removeClass('disabled');
			$bpField.show();
		} else {
			$ltRow.addClass('disabled');
			$bpField.hide();
		}
	},
	subfieldWidth: function (subfield, $el) {
		var $ltWidth = $(prefix + 'layout-table-' + subfield + ' .layout-table-width input');
		var $bpField = $(prefix + 'blueprint-' + subfield);
		var width = $ltWidth.val();
		if (!width || isNaN(width) || (width > 100)) {
			width = 100;
		} else if (width < 10) {
			width = 10;
		}
		$ltWidth.val(width);
		$bpField.css({
			'float': 'left',
			'width': (width - 1) + '%',
			'margin-left': '1%'
		});
	}
}

$layoutTable.on('change', '.layout-table-enable input', function () {
	fieldtypeSettings.blueprint();
});

$layoutTable.on('change', '.layout-table-width input', function () {
	fieldtypeSettings.blueprint();
});

$(function () {
	fieldtypeSettings.blueprint();
});