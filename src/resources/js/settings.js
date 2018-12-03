var $ipstackDiv = $('div#settings-smartmap-fields-ipstack');
var $maxmindDiv = $('div#settings-smartmap-fields-maxmind');

// Expand/collapse ipstack field
function ipstackExpandCollapse() {
     if ('ipstack' == $('input.smartmap-geolocation-radio:checked').val()) {
          $ipstackDiv.slideDown('fast');
     } else {
          $ipstackDiv.slideUp('fast');
     }
}

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
     ipstackExpandCollapse();
     maxmindExpandCollapse();
     // On change, expand/collapse
     $('input.smartmap-geolocation-radio').on('change', function () {
          ipstackExpandCollapse();
          maxmindExpandCollapse();
     });
});
