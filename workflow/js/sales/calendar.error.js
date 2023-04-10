$(document).ready(function() {
    $("#dataForm").validate({
        rules : {
		houmon_s1: {
    			required: function() { return ($('#houmonbi').val() != "" && $('#houmon_s1').val() == ""); }
		},
		houmon_e1: {
    			required: function() { return ($('#houmonbi').val() != "" && $('#houmon_e1').val() == ""); }
		},
		houmon_s2: {
    			required: function() { return ($('#houmonbi').val() != "" && $('#houmon_s2').val() == ""); }
		},
		houmon_e2: {
    			required: function() { return ($('#houmonbi').val() != "" && $('#houmon_e2').val() == ""); }
		}
	},
        messages: {
		houmon_s1: {
			required: "“ü‚ê‚Ä"
		},
		houmon_e1: {
			required: "“ü‚ê‚Ä"
		},
		houmon_s2: {
			required: "“ü‚ê‚Ä"
		},
		houmon_e2: {
			required: "“ü‚ê‚Ä"
		}
        }
    });
});
