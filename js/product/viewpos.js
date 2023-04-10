    // グローバル変数（一時的に保存しておく）を宣言
    var _return_value = "";
    function checkNumInput(obj, maxlen, decFlag = false){
        // 変数の定義
        var txt_obj = obj.value;
        var text_length = txt_obj.length;
        var pattern;
        if (decFlag === true) {
            // 半角数字と小数点の入力を許可する
            pattern = /^[0-9\.]+$/;
        }
        else {
            // 半角数字のみ入力を許可する
            pattern = /^[0-9]+$/;
        }
        // 入力した文字が半角数字かどうかチェック
        if (txt_obj.match(pattern)){
            // 文字数チェック
            if(text_length > maxlen){
                obj.value = _return_value;
            }
            else {
                _return_value = obj.value;
            }
        }
        else {
            // 入力した文字が半角数字ではないとき
            if(text_length === 0){
                _return_value = "";
            }
            else {
                obj.value = _return_value;
            }
        }
    }

    // グローバル変数（一時的に保存しておく）を宣言
    var _date_return_value = "";
    function checkDateInput(obj, maxlen){
        // 変数の定義
        var txt_obj = obj.value;
        var text_length = txt_obj.length;
        var pattern = /^[0-9\/]+$/;
        // 入力した文字が半角数字かどうかチェック
        if (txt_obj.match(pattern)){
            // 文字数チェック
            if(text_length > maxlen){
                obj.value = _date_return_value;
            }
            else {
                _date_return_value = obj.value;
            }
        }
        else {
            // 入力した文字が半角数字ではないとき
            if(text_length == 0){
                _date_return_value = "";
            }
            else {
                obj.value = _date_return_value;
            }
        }
    }
    
    function checkNumFormat(obj, decFlag = false){
        // 変数の定義
        var txt_obj = obj.value;
        var txt_id = obj.id;
        var pattern;

        // グローバル変数初期化
        resetGlobalNumReturnValue();

        // 未入力時は0を返却
        if (txt_obj === '') {
            if (decFlag === true) {
                obj.value = '0.00';
            }
            else {
                obj.value = '0';
            }
            return true;
        }

        if (decFlag === true) {
            if (txt_id === 'prod_tax') {
                // 税区分は整数部3桁以下+小数部2桁以下の入力を許可する
                pattern = /^([0-9]{0,3})(\.[0-9]{1,2})?$/;
            }
            else {
                // その他は整数部7桁以下+小数部2桁以下の入力を許可する
                pattern = /^([0-9]{0,7})(\.[0-9]{1,2})?$/;
            }
        }
        else {
            // 整数部7桁以下(小数なし)の入力を許可する
            pattern = /^([0-9]{0,7})?$/;
        }
        // チェック
        if (txt_obj.match(pattern)){
            // フォーマット
            if (decFlag === true){
                obj.value = String(number_format(Number(txt_obj), 2));
            }
            else {
                obj.value = String(number_format(Number(txt_obj), 0));
            }
            return true;
        }
        else {
            // 不正なフォーマット
            obj.focus();    // フォーカス移動させない
            return false;
        }
    }

    function checkDateFormat(obj){
        // 変数の定義
        var txt_obj = obj.value;

        // グローバル変数初期化
        resetGlobalDateReturnValue();

        if (txt_obj === ''){
            return true;
        }
        // 年/月/日の形式のみ許容する
        if(!txt_obj.match(/^\d{4}\/\d{1,2}\/\d{1,2}$/)){
            return false;
        }
        date = new Date(txt_obj);
        if(date.getFullYear()  != txt_obj.split("/")[0] 
            || date.getMonth() != txt_obj.split("/")[1] - 1 
            || date.getDate()  != txt_obj.split("/")[2]){
            return false;
        }
        var format = 'YYYY/MM/DD';
        format = format.replace(/YYYY/g, date.getFullYear());
        format = format.replace(/MM/g, ('0' + (date.getMonth() + 1)).slice(-2));
        format = format.replace(/DD/g, ('0' + date.getDate()).slice(-2));
        obj.value = format;
        return true;
    }

    function zeroExtension(obj, length, forceFlag = false){
        // 変数の定義
        var txt_obj = obj.value;
        var text_length = txt_obj.length;

        // グローバル変数初期化
        resetGlobalNumReturnValue();

        // forceFlag：未入力でもゼロ埋めするフラグ
        if (forceFlag === true || text_length > 0) {
            obj.value = ('00000000000000000000' + txt_obj).slice(-length);
        }
    }
    
    function commaRemove(obj){
        var txt_obj = obj.value;
        obj.value = txt_obj.replace(/,/g, '');
    }
    
    /**
     * 数値をカンマ区切りに変換する
     */
    function number_format(num, decimals){
        //小数点以下の表示桁数
        var _decimals = decimals | 0;

        //指定桁以下を切り捨てた数値
        var _shift = Math.pow(10, _decimals);
        var _floor = Math.floor(num * _shift) / _shift;

        //整数部と小数部に分ける
        var _integerPart = Math.floor(_floor);
        var _decimalPart = (_floor.toString().split('.').length > 1) ? _floor.toString().split('.')[1] : '';

        //整数部にカンマを付与
        var _num = Math.abs(_integerPart).toString().split(/(?=(?:\d{3})+$)/).join();

        //小数部を付与
        if (_decimals > 0) {
            var zeroStr = '';
            for (var i = 0; i < _decimals; i ++) zeroStr += '0';
//            _num += '.' + (zeroStr + _decimalPart).slice(-_decimals);
            _num += '.' + (_decimalPart + zeroStr).substr(0, _decimals);
        }

        //負の記号を付与して返却
        return (num < 0) ? ('-' + _num) : _num;
    }

    function resetGlobalNumReturnValue(){
        _return_value = "";
    }
    
    function resetGlobalDateReturnValue(){
        _date_return_value = "";
    }
    