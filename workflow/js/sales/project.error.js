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
		p_i1: { //�{���ۋ�
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i2: { //ID��
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_jyuchu_kakin_date: { //�ۋ������J�n�\��
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i3: { //�X�ܐ�
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i4: { //�P��
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		p_i5: { //�X�܉ۋ�
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_mikomi_kakin_price: { //�ۋ����v
    			required: function() { return (($('#s_prj_status').val() == '1' || $('#s_prj_status').val() == '2') &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		},
		s_prj_siire_shoki_price: { //�����x��
		},
		k_siire_shoki_siharai_date: { //�����x���\��
    			required: function() { return ($('#s_prj_siire_shoki_price').val() > '0'); }
		},
		s_prj_siire_kakin_price: { //�p���x��
		},
		k_siire_kakin_siharai_date: { //�p���x���J�n�\��
    			required: function() { return ($('#s_prj_siire_kakin_price').val() > '0'); }
		},
		s_prj_jyuchu_shoki_price: { //��������
    			required: function() { return ($('#s_prj_status').val() == '2' && $('#kubun').val() != '8'); }
		},
		s_prj_jyuchu_kakin_price: { //�ۋ����v
    			required: function() { return ($('#s_prj_status').val() == '2' &&
						      ($('#kubun').val() == '1' || $('#kubun').val() == '2')); }
		}
	},
        messages: {
		s_prj_rank: {
			required: "�Ȃ񂩑I��"
		},
		kubun: {
			required: "�I�ׂ���"
		},
		s_prj_name: {
                	required: "���ʂȂ񂩓���邾��",
                	minlength: $.format("{0}�����ȏ�����")
		},
		s_prj_jyuchu_sichu_date: {
                	required: "������"
		},
		s_prj_chumon_date: {
                	required: "�󒍂�����ł���H"
		},
		s_prj_mikomi_jyuchu_date: {
    			required: "����Ă�"
		},
		s_prj_mikomi_kakin_date: {
    			required: "�͂��E�E"
		},
		s_prj_mikomi_shoki_price: {
    			required: "��������"
		},
		s_prj_jyuchu_shoki_date: {
    			required: "�����I"
		},
		p_i1: { //�{���ۋ�
			required: "��"
		},
		p_i2: { //ID��
			required: "��"
		},
		s_prj_jyuchu_kakin_date: { //�ۋ������J�n�\��
			required: "��"
		},
		p_i3: { //�X�ܐ�
			required: "��"
		},
		p_i4: { //�P��
			required: "��"
		},
		p_i5: { //�X�܉ۋ�
			required: "��"
		},
		s_prj_mikomi_kakin_price: { //�ۋ����v
			required: "��"
		},
		s_prj_siire_shoki_price: { //�����x��
		},
		k_siire_shoki_siharai_date: { //�����x���\��
			required: "�N�\"
		},
		s_prj_siire_kakin_price: { //�p���x��
		},
		k_siire_kakin_siharai_date: { //�p���x���J�n�\��
			required: "�^�R"
		},
		s_prj_jyuchu_shoki_price: { //��������
			required: "�A�z"
		},
		s_prj_jyuchu_kakin_price: { //�ۋ����v
			required: "�o�J"
		}
        }
    });
});
