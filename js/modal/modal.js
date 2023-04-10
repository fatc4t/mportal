
// for range case:  
//                 case 1 input start end: finish id by  __start or __end. If not range don't finish id by start or end ! not implemented yet
//                        exemple: input id: proc_date__start proc_date__end
//                                 field   : proc_date
//                 case 2 normal input but search range : finish id by __rg  fieldname : base_id_start and base_id_end
//                        exemple: input id: sale_plan_dt__rg
//                                 field   : sale_plan_dt_start sale_plan_dt_end
// 
// unic_check : true  => only one checkbox can be checked;
//              false => many can be checked;
var table_size = 20;
var pagenb = [];
var table_keys=[];

var data_select     = [];
data_select["sect"] = "";
data_select["org"] = "";
data_select["prod"] = "";
data_select["supp"] = "";
data_select["area"] = "";
data_select["staff"] = "";
data_select["credit"] = "";
data_select["splan"] = "";
data_select["cust"] = "";
data_select["prodclass"] = "";
data_select["appoprod"]  = "";
var modal_working_data = [];
modal_working_data["sect"] = [];
modal_working_data["org"] = [];
modal_working_data["prod"] = [];
modal_working_data["supp"] = [];
modal_working_data["area"] = [];
modal_working_data["staff"] = [];
modal_working_data["credit"] = [];
modal_working_data["splan"] = [];
modal_working_data["cust"] = [];
modal_working_data["prodclass"] = [];
modal_working_data["appoprod"] = [];
var pagemaxnb = 0;

var select_id = "";
function modal_input_check(e){
    if(e.target.id.indexOf('tel') !== -1){
         e.target.value = e.target.value.replace(/[^0-9\-]/g,'');
    }else{
        e.target.value = e.target.value.replace(/\D/g, "");
    }
}
function modal_close(e){
    id_split = e.target.id.split("_");
    modal_id = id_split[0] + "_" + id_split[1];
    //console.log(id_split);
    if('gorogoro' === id_split[1]){
        return;
    }
    // pme
    if(select_id && document.getElementById(select_id).value === "select_id"){
        document.getElementById(select_id).options[0].selected=true;
    }
    ////console.log(modal_id);
    if(id_split[0] !== "modal"){
        return;
    }
    modal_elem = document.getElementById(modal_id);
    if (e.target == modal_elem) {
        document.getElementById(select_id).value = "empty";
        modal_elem.style.display = "none";
        document.getElementById(select_id).focus();
    }else if (e.target.id.indexOf("close") !== -1 ){
        document.getElementById(select_id).value = "empty";
        modal_elem.style.display = "none";
        document.getElementById(select_id).focus();
    }else if(e.target.id.indexOf("validate") !== -1 ) {
        modal_elem.style.display = "none";
        document.getElementById(select_id).focus();
    }
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(e){
        modal_close(e);
};
function modal_search(e){
    ////console.log("here");
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1];
    modal_working_data[id_split[1]] = [];
    elem_param_id = base_id + "_param";
    elem_param = document.getElementById(elem_param_id);
    inputlst = elem_param.getElementsByTagName("INPUT");
    temp_data = window["data_"+id_split[1]];
    pagenb[id_split[1]] = 0;
    ////console.log(inputlst);
    var count = 0;
    for(i=0;i<inputlst.length;i++){
        if(inputlst[i].value){
            count = 1;
            break;
        }
    }
    ////console.log(temp_data);
    if( count === 0 ){
        modal_working_data[id_split[1]] = temp_data;
    }else{
        w_data = modal_working_data[id_split[1]];
        var copy_data;
        var temp_data_key = Object.keys(temp_data);
        for(i=0;i<temp_data_key.length;i++){
            copy_data = 1;
            var loop_key = temp_data_key[i];
            loop_d_row = temp_data[loop_key]; 
            ////console.log(loop_d_row);
            for(j=0;j<inputlst.length;j++){
                if(copy_data === 0){
                    continue;
                }
                loop_input = inputlst[j];
                if(loop_input.value){
                    param_nm = loop_input.id.replace(base_id+"_","");
                    
                    if(typeof loop_d_row[param_nm] !== "undefined"  && (loop_d_row[param_nm] == "" || loop_d_row[param_nm] == null )&& loop_input.value){
                        copy_data = 0;
                        break;                        
                    }                    
                    // range case 2
                    
                    if(param_nm.indexOf('__rg') !== -1){                        
                        // check start range
                        param_nm = param_nm.replace('__rg','');                        
                        if(loop_d_row[param_nm+"_start"]){
                            if(loop_input.value.replace(/\D/g, "") < loop_d_row[param_nm+"_start"].replace(/\D/g, "")){
                                //console.log("here");
                                copy_data = 0;
                                break;                                
                            }
                        }else if(loop_input.value.replace(/\D/g, "") && !loop_d_row[param_nm+"_start"].replace(/\D/g, "")){
                            copy_data = 0;
                            break;                             
                        }
                        // check end range
                        if(loop_d_row[param_nm+"_end"]){
                            if(loop_input.value.replace(/\D/g, "") > loop_d_row[param_nm+"_end"].replace(/\D/g, "")){
                                copy_data = 0;
                                break;                                
                            }
                        } else if(loop_input.value.replace(/\D/g, "") && !loop_d_row[param_nm+"_end"].replace(/\D/g, "")){
                            copy_data = 0;
                            break;                             
                        }                      
                     // range case 1
                     
                    }else if(param_nm.indexOf('tel') !== -1){ 
                        if( loop_d_row[param_nm] && loop_d_row[param_nm].toString().replace(/\D/g, "").indexOf(loop_input.value.replace(/\D/g, "")) === -1){
                            copy_data = 0;
                            break;
                        } 
                    }else{
                        if( loop_d_row[param_nm] && loop_d_row[param_nm].toString().indexOf(loop_input.value) === -1){
                            copy_data = 0;
                            break;
                        }
                    }
                }
                
            }
            if(copy_data === 1){
                var ins_key = loop_key.split('#')[0];
                if(!w_data[ins_key]){
                    w_data[ins_key] = temp_data[ins_key];
                }
            }
        }
    }
    
    modal_datashowpage(pagenb[id_split[1]],e);
}
function modal_unselect(e){
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1];
    elem_tbody_id = base_id + "_table_body";
    elem_tbody = document.getElementById(elem_tbody_id);
    checkboxlst = elem_tbody.getElementsByTagName("INPUT");
    for(i=0;i<checkboxlst.length;i++){
        checkboxlst[i].checked = false;
        chbx_cd = "," + checkboxlst[i].value;
        data_select[id_split[1]] = data_select[id_split[1]].replace(chbx_cd,"");
    }
}
function modal_select(e){
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1];
    elem_tbody_id = base_id + "_table_body";
    elem_tbody = document.getElementById(elem_tbody_id);
    checkboxlst = elem_tbody.getElementsByTagName("INPUT");
    for(i=0;i<checkboxlst.length;i++){
        checkboxlst[i].checked = true;
        if(data_select[id_split[1]].indexOf(checkboxlst[i].value) === -1){
            chbx_cd = "," + checkboxlst[i].value;
            data_select[id_split[1]] = data_select[id_split[1]] + chbx_cd;
        }
    }    
}
function modal_befpage(e){
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1];
    
    pagenb[id_split[1]]--;
    if(pagenb[id_split[1]] < 0){  
        pagenb[id_split[1]] = 0;
    }
    modal_datashowpage(pagenb[id_split[1]],e);    
}
function modal_nextpage(e){
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1]; 
    pagenb[id_split[1]]++;
    if(pagenb[id_split[1]] < 0){  
        pagenb[id_split[1]] = 0;
    }
    modal_datashowpage(pagenb[id_split[1]],e);     
}
function modal_validate(e){
    ////console.log("here");
    id_split = e.target.id.split("_");
    base_id = id_split[0] + "_" + id_split[1];
    if("sect" === id_split[1]){
        sect_list = data_select[id_split[1]];
    }else if("org" === id_split[1]){
        org_list = data_select[id_split[1]];
    }else if("prod" === id_split[1]){
        prod_list = data_select[id_split[1]];
    }else if("supp" === id_split[1]){
        supp_list = data_select[id_split[1]];
    }else if("area" === id_split[1]){
        area_list = data_select[id_split[1]];
    }else if("staff" === id_split[1]){
        staff_list = data_select[id_split[1]];
    }else if("credit" === id_split[1]){
        credit_list = data_select[id_split[1]];        
    }else if("splan" === id_split[1]){
        splan_list = data_select[id_split[1]];
    }else if("cust" === id_split[1]){
        cust_list = data_select[id_split[1]];
    }else if("prodclass" === id_split[1]){
        prodclass_list = data_select[id_split[1]];
    }else if("appoprod" === id_split[1]){
        appoprod_list = data_select[id_split[1]];
    }     
    A_cd_list = data_select[id_split[1]].split(",");
    
    name_list = [];
    temp_data = window["data_"+id_split[1]];
    ////console.log(temp_data);
    ////console.log(A_cd_list);
    name_id = id_split[1]+"_nm";
    code_id = id_split[1]+"_cd";
    if(id_split[1] === "org" ){
        code_id = id_split[1]+"_id";
    }
    for(i=0;i<A_cd_list.length;i++){
        if(A_cd_list[i]){
            if(temp_data[A_cd_list[i]]){
                if(temp_data[A_cd_list[i]][name_id]){
                    name_list[A_cd_list[i]] = temp_data[A_cd_list[i]][name_id];
                }else{
                    name_list[A_cd_list[i]] = "";
                }
            }else{
                for(j=0;j<Object.keys(temp_data).length;j++){
                    loop_id = Object.keys(temp_data)[j];
                    if(loop_id.indexOf('#') !== -1){
                        continue;
                    }
                    if(temp_data[loop_id][code_id] == A_cd_list[i]){
                        if(temp_data[loop_id][name_id]){
                            name_list[A_cd_list[i]] = temp_data[loop_id][name_id];
                        }else{
                            name_list[A_cd_list[i]] = "";
                        }
                    }
                }
            }
        }
    }
    //////console.log(name_list);
    modal_create_select(select_id,name_list);
    modal_close(e); 
}
function modal_datashowpage(showpage,e){
    id_split = e.target.id.split("_");
    pagemaxnb = Math.ceil(Object.keys(modal_working_data[id_split[1]]).length/table_size)-1 ;
    if( pagemaxnb <= 0 ){
        pagemaxnb = 0;
    }
    if( showpage > pagemaxnb ){
        showpage = pagemaxnb;
        pagenb[id_split[1]] = showpage;
    }
    id_split = e.target.id.split("_");
    //console.log('here1 // pagemaxnb:'+pagemaxnb);
    modal_tbodydata(modal_working_data[id_split[1]],showpage*table_size,table_size,e);
}
function modal_selectdata(e){
    id_split = e.target.id.split("_");
    modal_id = id_split[0] + "_" + id_split[1];
    if(unic_check){
        data_select[id_split[1]] = "";
        elem_table_id = modal_id+"_table_body";
        elem_table = document.getElementById(elem_table_id);
        elem_input_lst = elem_table.getElementsByTagName('INPUT');
        for(i=0;i<elem_input_lst.length;i++){
            if( elem_input_lst[i].id !== e.target.id ){
                elem_input_lst[i].checked = false;
            }
        }
    }
    chbx_cd = "," + document.getElementById(e.target.id).value.split('#')[0];
    if(e.target.checked === true ){
        if(data_select[id_split[1]].indexOf(chbx_cd) === -1){
            data_select[id_split[1]] = data_select[id_split[1]] + chbx_cd;
        }
    }else{
        data_select[id_split[1]] = data_select[id_split[1]].replace(chbx_cd,"");
    }
}
function modal_tbodydata(data,start,nbRow,e){ 
    //console.log('here2');
    id_split = e.target.id.split("_");
    modal_id = id_split[0] + "_" + id_split[1];    
    keys = table_keys[id_split[1]];
    tbody_id = modal_id + "_table_body";
    ////console.log(tbody_id);
    tbod = document.getElementById(tbody_id);
    tbod.innerHTML = '';
	//20200213 montagne  sort start
    var data_key = Object.keys(data).sort();
	//20200213 montagne  sort end
    ////console.log(data);
    if(Object.keys(data).length > 0){ 
        if(keys.length <= 0){
			//20200213 montagne  sort start
            keys = Object.keys(data[0]).sort();            
			//20200213 montagne  sort end
        }
        ilength=Object.keys(data).length;
        if(nbRow > ilength-start){
            nbRow=ilength-start;                        
        }
        row_ind = 0;
        //console.log('data_key: '+ data_key);
        for(i=0;i < nbRow ; i++ ){ 
            var loop_key = data_key[start+i];          
            //console.log('loop_key:'+loop_key+' // nbRow:'+nbRow);
            if(loop_key.toString().indexOf('#') !== -1){
                //console.log(loop_key);
                nbRow = nbRow+1;
                if(nbRow >= ilength){
                    nbRow = ilength;
                }
                continue;
            }             
            var trow         = tbod.insertRow(row_ind);
            var ckbox        = trow.insertCell(0);
            var newckBox     = document.createElement('input');
            newckBox.type    = 'checkbox';
            newckBox.name    = 'modal_'+id_split[1]+'_ckbox_val';
            newckBox.id      = 'modal_'+id_split[1]+'_ckbox_' + i;
            newckBox.value   = data[loop_key][keys[0]];
            newckBox.onclick = modal_selectdata;
            ////console.log(data_select[id_split[1]]);
            ////console.log(data[start+i][keys[0]]);
            if(data_select[id_split[1]].indexOf(','+data[loop_key][keys[0]]) !== -1){
               newckBox.checked = true; 
            }
            ckbox.appendChild(newckBox);
            for(j=0;j < keys.length; j++){
                var newcell = trow.insertCell(j+1);
                newcell.innerHTML = data[loop_key][keys[j]];
            }
            row_ind++;
        }
    }else{
        alert("データがありません");
    }                
}
function modal_create_select(select_id,data){
    ////console.log("here1");
    ////console.log(data);
    ////console.log(select_id);
    elem_select = document.getElementById(select_id);
    elem_select.innerHTML = "";
    type = select_id.split("_")[0];
    ////console.log(type);
    //data_select_length = window["data_"+type].length;
    ////console.log("select_id:"+select_id+" / data.length:"+data.length+" / data_select_length:"+data_select_length);
//if(window['code_list']){
//    //console.log(code_list.length);
//    code_list = '';
//}else{
//    //console.log('empty');
//}
    ////console.log(data);
    data_key = Object.keys(data).sort();
    if(data_key.length !== 1){
        // get list of different ID
        if(window["data_"+type].length ===0){
            opt = document.createElement("option");
            opt.value   = "empty";
            opt.text    = "データがありません";
            elem_select.options.add(opt,null);            
            code_list = '';
            return;
        }
        var_id = Object.keys(window["data_"+type][Object.keys(window["data_"+type])[0]])[0];
        //list_diff_id = [...new Set(window["data_"+type].map(s => s[var_id]))];
        if(Object.keys(window["data_"+type])[0] === 0){
            list_diff_id = [...new Set(window["data_"+type].map(s => s[var_id]))];
        }else{
            list_diff_id = Object.keys(window["data_"+type]);
        }
        
        opt = document.createElement("option");
        opt.value   = "empty";
        opt.text    = "";
        if(data_key.length === 0){
            opt.text = "";
        }else if(data_key.length === list_diff_id.length || window['code_list'] && code_list.length === list_diff_id.length  ){
            opt.text    = "全選択";
        }else{
            opt.text    = "複数選択";
        }
        elem_select.options.add(opt,null);
    }
    
    var end_loop = data_key.length;
    if( end_loop > 5 ){
        end_loop = 5;
    }
    for(i=0;i<end_loop;i++){
        
        opt = document.createElement("option");
        opt.value   = data_key[i];
        opt.text    = data[data_key[i]];
        elem_select.options.add(opt,null);
    }
    if(Object.keys(data).length > 5){
        opt = document.createElement("option");
        opt.value   = "more";
        opt.text    = "...";
        elem_select.options.add(opt,null);        
    }
    opt_grp = document.createElement("optgroup");
    opt_grp.label = "---------------";
    opt = document.createElement("option");
    opt.value   = "select_id";
    opt.text    = "検索";
    opt_grp.appendChild(opt);
    elem_select.appendChild(opt_grp); 
    code_list = '';
}
function modal_open(e){
    select_id = e.target.id;
    id_split = e.target.id.split("_");
    modal_id = "modal_" + id_split[0];
    ////console.log(e.target.name);
    ////console.log(e.target.name+'_unic_check: '+window[e.target.name+'_unic_check']);
    ////console.log(modal_id);
    delete unic_check;
    if(window[e.target.name+'_unic_check']){
        unic_check = window[e.target.name+'_unic_check'];
    }
    elem_unselect_id = modal_id + "_unselect";
    elem_select_id = modal_id + "_select";    
    if( typeof(unic_check) === 'undefined' ){
        unic_check = false;
    } 
    if(unic_check){
        document.getElementById(elem_unselect_id).disabled = true;
        document.getElementById(elem_select_id).disabled = true;
    }else{
        document.getElementById(elem_unselect_id).disabled = false;
        document.getElementById(elem_select_id).disabled = false;        
    }
    document.getElementById(modal_id).style.display = "block";
    pagemaxnb = Math.ceil(modal_working_data[id_split[0]].length/table_size)-1 ;
    var ev = {};
    ev["target"] = {id: modal_id};
    modal_search(ev);
}
function modal_select_mgt(e){
    ////console.log(e);
    if( e.target.value === "select_id" ){
        modal_open(e);
    }
}
/**
 * データ更新
 */
function f_setDataForAjax( data, path, replacementArea, async )
{
    // 書き換えエリアの指定なし
    if ( replacementArea == null)
    {
        // 一覧エリアの書き換え
        replacementArea = 'jquery-replace-ajax';
    }
    
    // 同期処理指定なし
    if ( async == null)
    {
        // 一覧エリアの書き換え
        async = true;
    }

    // リプレースエリアが、全画面更新時の時
 
    if( replacementArea == 'ajaxScreenUpdate' && -1 != path.indexOf("csvoutput") )
    {
        // submit用のFROMを作成
        var ajaxForm = document.createElement("form");
        
        for ( var paraName in data )
        {
            var q = document.createElement('input');
            q.type = 'hidden';
            q.name = paraName;
            q.value = data[paraName];
            ajaxForm.appendChild(q);
        }
        
        // データ更新
        ajaxForm.method = 'POST'; // method(GET or POST)を設定する
        ajaxForm.action = path; // action(遷移先URL)を設定する
        
        document.body.appendChild(ajaxForm);
        ajaxForm.submit(); // submit する
        return true;
    }

    // リプレースエリアが検索エリアの場合
    if( replacementArea == 'jquery-replace-ajax' && -1 != path.indexOf("search") )
    {
        document.getElementById("search_dialog").textContent = '検索中・・・';
        $("#search_dialog").dialog('open');
    }

    /**
     * Ajax通信メソッド
     * @param type  : HTTP通信の種類
     * @param url   : リクエスト送信先のURL
     * @param data  : サーバに送信する値
     */
    $.ajax({
        type: "POST",
        url: path,
        data: data,
        async: async,
        /**
         * Ajax通信が成功した場合に呼び出されるメソッド
         */
        success: function( data, dataType )
        {
            // successのブロック内は、Ajax通信が成功した場合に呼び出される
            // PHPから返ってきたデータの表示
            if( -1 != path.indexOf("show") || -1 != path.indexOf("print") || -1 != path.indexOf("lumpDayRetouch") || -1 != path.indexOf("lumpApproval") )
            {
                // 新規表示し直しの場合
                document.write(data);
                document.close();
            }
            else if( -1 == data.indexOf(replacementArea) )
            {
                // セキュリティエラーではない（ログイン画面のロゴの画像指定があるかで判断）
                if( -1 == data.indexOf("main-logo.png") )
                {
                    // データの更新に失敗
                    document.getElementById("dialog").textContent = data;
                    $("#dialog").dialog('open');
                }
                else
                {
                    // セキュリティエラーの場合、ログイン画面へ遷移する
                    document.write(data);
                    document.close();
                }
            }
            else
            {
                // 検索以外の更新である
//                if( -1 == path.indexOf("search") )
                if( -1 == path.indexOf("search") )
                {
                    if( 0 < path.indexOf("addInput") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    }                    
                    if( 0 < path.indexOf("csvoutput") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    }
                    if( 0 < data.indexOf('ajaxScreenUpdate') )
                    {
                        document.getElementById("dialog_redirect").textContent='データを更新しました。';
                        $("#dialog_redirect").dialog('open');
                        return;
                    }
                    else
                    {
                        document.getElementById("dialog").textContent='データを更新しました。';
                        $("#dialog").dialog('open');
                    }
                }

                // 検索ダイアログを閉じる
                $("#search_dialog").dialog('close');
                

                $('#add').prop('disabled', false);
                var areaName = "#" + replacementArea;
                jQuery( areaName ).html( data );
            }
        },
        /**
         * Ajax通信が失敗した場合に呼び出されるメソッド
         */
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            //通常はここでtextStatusやerrorThrownの値を見て処理を切り分けるか、単純に通信に失敗した際の処理を記述します。

            var errMsg = 'Error : ' + errorThrown;
            //エラーメッセージの表示
            document.getElementById("dialog").textContent = errMsg;
            $("#dialog").dialog('open');
        }
    });
}