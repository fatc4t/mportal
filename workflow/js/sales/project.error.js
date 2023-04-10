$(document).ready(function() {
    $("#dataForm").validate({
        rules : {
		s_prj_rank: {
    			required: function() { return ($('#s_prj_status').val() !=  '4' && $('#s_prj_status').val() != '5'); }
		},
		kubun: {
			required: true
		},
		s_prj_name: {
    			required: function() { return ($('#s_prj_status').val() !=  '4' && $('#s_prj_status').val() != '5'); }
		},
		s_prj_jyuchu_sichu_date: {
    			required: function() { return ($('#s_prj_status').val() ==  '2' || $('#s_prj_status').val() == '3'); }
		},
		s_prj_chumon_date: {
    			required: function() { return ($('#s_prj_status').val() ==  '2'); }
		},
		s_prj_mikomi_jyuchu_date: {
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') && $('#kubun').val() != '8'); }
		},
		s_prj_mikomi_kakin_date: {
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_mikomi_shoki_price: {
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') && $('#kubun').val() != '8'); }
		},
		s_prj_jyuchu_shoki_date: {
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') && $('#kubun').val() != '8'); }
		},
		p_i1: { //–{•”‰Û‹à
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i2: { //ID”
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_jyuchu_kakin_date: { //‰Û‹à¿‹ŠJn—\’è
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i3: { //“X•Ü”
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i4: { //’P‰¿
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i5: { //“X•Ü‰Û‹à
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_mikomi_kakin_price: { //‰Û‹à‡Œv
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_siire_shoki_price: { //‰Šúx•¥
		},
		k_siire_shoki_siharai_date: { //‰Šúx•¥—\’è
    			required: function() { return ($('#s_prj_siire_shoki_price').val() > '0'); }
		},
		s_prj_siire_kakin_price: { //Œp‘±x•¥
		},
		k_siire_kakin_siharai_date: { //Œp‘±x•¥ŠJn—\’è
    			required: function() { return ($('#s_prj_siire_kakin_price').val() > '0'); }
		},
		s_prj_jyuchu_shoki_price: { //‰Šú”„ã
    			required: function() { return ($('#s_prj_status').val() == '2' && $('#kubun').val() != '8'); }
		},
		s_prj_jyuchu_kakin_price: { //‰Û‹à‡Œv
    			required: function() { return ($('#s_prj_status').val() == '2' &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		}
	},
        messages: {
		s_prj_rank: {
			required: "‚È‚ñ‚©‘I‚×"
		},
		kubun: {
			required: "‘I‚×‚Á‚Ä"
		},
		s_prj_name: {
                	required: "•’Ê‚È‚ñ‚©“ü‚ê‚é‚¾‚ë",
                	minlength: $.format("{0}•¶šˆÈã“ü‚ê‚ë")
		},
		s_prj_jyuchu_sichu_date: {
                	required: "–¢“ü—Í"
		},
		s_prj_chumon_date: {
                	required: "ó’‚µ‚½‚ñ‚Å‚µ‚åH"
		},
		s_prj_mikomi_jyuchu_date: {
    			required: "“ü‚ê‚Ä‚æ"
		},
		s_prj_mikomi_kakin_date: {
    			required: "‚Í‚ŸEE"
		},
		s_prj_mikomi_shoki_price: {
    			required: "‚¨‚¢‚¨‚¢"
		},
		s_prj_jyuchu_shoki_date: {
    			required: "‚à‚¨I"
		},
		p_i1: { //–{•”‰Û‹à
			required: "–¢"
		},
		p_i2: { //ID”
			required: "–¢"
		},
		s_prj_jyuchu_kakin_date: { //‰Û‹à¿‹ŠJn—\’è
			required: "–¢"
		},
		p_i3: { //“X•Ü”
			required: "–¢"
		},
		p_i4: { //’P‰¿
			required: "–¢"
		},
		p_i5: { //“X•Ü‰Û‹à
			required: "–¢"
		},
		s_prj_mikomi_kakin_price: { //‰Û‹à‡Œv
			required: "–¢"
		},
		s_prj_siire_shoki_price: { //‰Šúx•¥
		},
		k_siire_shoki_siharai_date: { //‰Šúx•¥—\’è
			required: "ƒNƒ\"
		},
		s_prj_siire_kakin_price: { //Œp‘±x•¥
		},
		k_siire_kakin_siharai_date: { //Œp‘±x•¥ŠJn—\’è
			required: "ƒ^ƒR"
		},
		s_prj_jyuchu_shoki_price: { //‰Šú”„ã
			required: "ƒAƒz"
		},
		s_prj_jyuchu_kakin_price: { //‰Û‹à‡Œv
			required: "ƒoƒJ"
		}
        }
    });
});
