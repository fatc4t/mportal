//document.getElementById("add").setAttribute("disabled", true);

function T_addrow(e){
//    //console.log(e);
    ndata={};
    for(i=0;i<A_key.length;i++){
        elemt_id        = A_key[i]+"_"+e.target.value;
        //ndata[A_key[i]] = document.getElementById(elemt_id).value;
        if (document.getElementById(elemt_id).type === 'checkbox'){
            if (document.getElementById(elemt_id).checked === false){
                ndata[A_key[i]] = '';
            }
            else{
                ndata[A_key[i]] = document.getElementById(elemt_id).value;
            }
        }
        else{
            ndata[A_key[i]] = document.getElementById(elemt_id).value;
        }
    }
    working_data[working_data.length] = ndata;
    new_data[new_data.length]= ndata;
    line    = f_check_primary_key(e.target.value,upd_data);   
    if(line !=="" ){
        upd_data.splice(line, 1);
    }
    line    = f_check_primary_key(e.target.value,del_data);   
    if(line !=="" ){
        del_data.splice(line, 1);
    } 
    return 'ok';
}

function T_delrow(e){
    for(i=0;i<A_pr_key_id.length;i++){
//        //console.log(del_data.length);
//        //console.log(A_pr_key_id[i]);
//        //console.log(e.target.value);
//        //console.log(working_data[e.target.value][A_pr_key_id[i]]);
        var res_position=del_data.length;
        var delkey=A_pr_key_id[i];
        var val = working_data[e.target.value][delkey];
        del_data[res_position]= {};
        del_data[res_position][delkey] = val;
        delete working_data[e.target.value];
        
        line    = f_check_primary_key(e.target.value,upd_data);   
//        //console.log("linenb upd:"+line);
        if(line !=="" ){
            upd_data.splice(line, 1);
        }
        line    = f_check_primary_key(e.target.value,new_data);
//        //console.log("linenb new:"+line);
        if(line !=="" ){
            new_data.splice(line, 1);
        }
        return 'ok';
    }
}

function T_f_change(e){
   //console.log(e);
    ref = e.target.id.split("_");
    ref = ref[ref.length-1];
    
//    if(e.target.name === "area_nm" && e.target.value.replace(/\s/g,'') === ""){
//        alert("※空白は禁止されています");
//        if(ref === document.getElementById("add").value){
//            document.getElementById(e.target.id).focus();
//            document.getElementById("add").setAttribute("disabled", true);
//        }else{
//            document.getElementById(e.target.id).value = working_data[ref][e.target.name];
//        }
//        return;
//    }
    var arrCols = ["type_nm", "type_kn", "priv_class_nm", "priv_class_kn", "prod_t_nm", "prod_k_nm", "setprod_amount", "constitu_amount", "order_date_str", "order_date_end"];
    if( arrCols.indexOf(e.target.name) !== -1){
        if(e.target.value.replace(/\s/g,'') === ""){
            alert("※空白は禁止されています");
            if(ref === document.getElementById("add").value){
                document.getElementById(e.target.id).focus();
                document.getElementById("add").setAttribute("disabled", true);
            }else{
                document.getElementById(e.target.id).value = working_data[ref][e.target.name];
            }
            return;
        }
    }

//check data's format 
    f_check_formt_data(e);
//check primary keys are filled and unic (button add)
    if(pr_key_list.indexOf(e.target.name) !== -1){
//        //console.log("here1");
        add_show    = "";   
        cur_val     = [];
        for(i=0;i<A_pr_key_id.length;i++){
            elem_id = A_pr_key_id[i]+"_"+ref;
            if(document.getElementById(elem_id).value){
                cur_val[A_pr_key_id[i]] = document.getElementById(elem_id).value;
                add_show=1;
            }
            //else { break; }
            else {
                alert("※空白は禁止されています");
                if(ref === document.getElementById("add").value){
                    document.getElementById(e.target.id).focus();
                    document.getElementById("add").setAttribute("disabled", true);
                }else{
                    document.getElementById(e.target.id).value = working_data[ref][e.target.name];
                }
                return;
            }
        }
//        //console.log("here2");
        if(add_show === 1){
            same_key="";
//           //console.log("here3");
            for(i=0;i<working_data.length;i++){
                if(working_data[i]){
//                    //console.log("here4");
                    for(j=0;j<A_pr_key_id.length;j++){
                        if(working_data[i][A_pr_key_id[j]] !== cur_val[A_pr_key_id[j]]){
                            same_key="";
//                            //console.log("here1");("ici");
                            break;
                        }
                        else{
                            same_key=1;
                        }
                    }
                    if (same_key ===1){
                        
                        alert("※コードは"+(i+1)+"行目に使っています。");
                        if(ref === document.getElementById("add").value){
                            document.getElementById(e.target.id).value = "";
                            document.getElementById(e.target.id).focus();
                            document.getElementById("add").setAttribute("disabled", true);
                        }
                        else{
                            document.getElementById(e.target.id).value = working_data[ref][e.target.name];
                        }
                        return;
                    }
                }
            }
            
            if (e.target.name === "prod_cd"){
                // 商品マスタ存在チェック
                orgn_id = document.getElementById("cmb_ORGANIZATION_ID").value;
                prod_cd = document.getElementById(e.target.id).value;
                var exists  = false;
                for(i=0;i<A_pmst0201_sd.length;i++){
                    if(A_pmst0201_sd[i]["organization_id"] === Number(orgn_id) && A_pmst0201_sd[i]["prod_cd"] === prod_cd){
                        if (destination === "Mst5102"){
                            // 商品入数構成マスタ
                            document.getElementById("prod_kn_rk_"+ref).value        = A_pmst0201_sd[i]["prod_kn_rk"];
                            document.getElementById("prod_capa_nm_"+ref).value      = A_pmst0201_sd[i]["prod_capa_nm"];
                            document.getElementById("saleprice_"+ref).value         = A_pmst0201_sd[i]["saleprice"];
                            document.getElementById("costprice_"+ref).value         = A_pmst0201_sd[i]["costprice"];
                            document.getElementById("setprod_amount_"+ref).value    = "0";
                        }
                        else if (destination === "Mst5301"){
                            // バンドルマスタ
                            document.getElementById("prod_nm_"+ref).value           = A_pmst0201_sd[i]["prod_nm"];
                            document.getElementById("prod_capa_nm_"+ref).value      = A_pmst0201_sd[i]["prod_capa_nm"];
                            document.getElementById("saleprice_"+ref).value         = A_pmst0201_sd[i]["saleprice"];
                            document.getElementById("constitu_amount_"+ref).value   = "0";
                            document.getElementById("saleprice_all_"+ref).value     = "0";
                        }
                        else if (destination === "Mst5201"){
                            // ミックスマッチマスタ
                            document.getElementById("prod_nm_"+ref).value           = A_pmst0201_sd[i]["prod_nm"];
                        }
                        else if (destination === "Mst1401"){
                            // 期間原価マスタ
                            document.getElementById("prod_nm_"+ref).value           = A_pmst0201_sd[i]["prod_nm"];
                            // 枠外の発注期間に値が入力されていたら発注期間セルのデフォルト値として設定
                            if (document.getElementById("txt_ORD_DATE_STR").value !== ""){
                                document.getElementById("order_date_str_"+ref).value = document.getElementById("txt_ORD_DATE_STR").value;
                            }
                            if (document.getElementById("txt_ORD_DATE_END").value !== ""){
                                document.getElementById("order_date_end_"+ref).value = document.getElementById("txt_ORD_DATE_END").value;
                            }
                        }
                        exists = true;
                        break;
                    }
                }
                if (exists === false){
                    alert("※商品コードがマスタにありません。");
                    if(ref === document.getElementById("add").value){
                        document.getElementById(e.target.id).value = "";
                        document.getElementById(e.target.id).focus();
                        document.getElementById("add").setAttribute("disabled", true);
                    }
                    else{
                        document.getElementById(e.target.id).value = working_data[ref][e.target.name];
                    }
                    return
                }
            }
            
//            el_id = "area_nm_"+ref;
//            area_nm_val = document.getElementById(el_id).value;
//            if(area_nm_val !== "" ){
//                document.getElementById("add").removeAttribute("disabled");
//            }
            //if (ref===document.getElementById("add").value){
            if (document.getElementById("add") && ref===document.getElementById("add").value){
                if (e.target.name === "type_cd"){
                    nm_val = document.getElementById("type_nm_"+ref).value;
                    kn_val = document.getElementById("type_kn_"+ref).value;
                    if (nm_val !== "" && kn_val !== ""){
                        document.getElementById("add").removeAttribute("disabled");
                    }
                }
                else if (e.target.name === "priv_class_cd"){
                    nm_val = document.getElementById("priv_class_nm_"+ref).value;
                    kn_val = document.getElementById("priv_class_kn_"+ref).value;
                    if (nm_val !== "" && kn_val !== ""){
                        document.getElementById("add").removeAttribute("disabled");
                    }
                }
                else if (e.target.name === "jicfs_class_cd"){
                    document.getElementById("add").removeAttribute("disabled");
                }
                else if (e.target.name === "maker_cd"){
                    document.getElementById("add").removeAttribute("disabled");
                }
                else if (e.target.name === "prod_t_cd"){
                    nm_val = document.getElementById("prod_t_nm_"+ref).value;
                    if (nm_val !== ""){
                        document.getElementById("add").removeAttribute("disabled");
                    }
                }
                else if (e.target.name === "prod_k_cd"){
                    nm_val = document.getElementById("prod_k_nm_"+ref).value;
                    if (nm_val !== ""){
                        document.getElementById("add").removeAttribute("disabled");
                    }
                }
                else if (e.target.name === "prod_cd"){
                    if (destination === "Mst1401"){
                        // 期間原価マスタ
                        sdt_val = document.getElementById("order_date_str_"+ref).value;
                        edt_val = document.getElementById("order_date_end_"+ref).value;
                        if (sdt_val !== "" && edt_val !== ""){
                            document.getElementById("add").removeAttribute("disabled");
                        }
                    }
                    else{
                        amt_val = "";
                        if (destination === "Mst5102"){
                            // 商品入数構成マスタ
                            amt_val = document.getElementById("setprod_amount_"+ref).value;
                        }
                        else if (destination === "Mst5301"){
                            // バンドルマスタ
                            amt_val = document.getElementById("constitu_amount_"+ref).value;
                        }
                        else if (destination === "Mst5201"){
                            // ミックスマッチマスタ
                            amt_val = "1";  // dummy input
                        }
                        else if (destination === "Mst1401"){
                            // 期間原価マスタ
                            amt_val = "1";  // dummy input
                        }
                        if (amt_val !== ""){
                            document.getElementById("add").removeAttribute("disabled");
                        }
                    }
                }
            }
        }
        else{
           document.getElementById("add").setAttribute("disabled", true); 
        }
    }
    //else if(e.target.name === "area_nm" ){
    else if(e.target.name === "type_nm" || e.target.name === "type_kn" ||
            e.target.name === "priv_class_nm" || e.target.name === "priv_class_kn" ||
            e.target.name === "prod_t_nm" ||
            e.target.name === "prod_k_nm" ||
            e.target.name === "setprod_amount" ||
            e.target.name === "constitu_amount" ||
            e.target.name === "order_date_str" || e.target.name === "order_date_end"){
        if( e.target.value !== ""){
            add_val = document.getElementById("add").value;
            if(ref == add_val){
               //console.log(e);
               var  add_attr = 0;
                for(i=0;i<A_pr_key_id.length;i++){
                    elem_id = A_pr_key_id[i]+"_"+ref; 
                    if(!document.getElementById(elem_id).value){
                        add_attr = 1;
                        break;
                    }
                }
                // 主キー以外の未入力不可項目チェック
                if (add_attr === 0) {
                    if ((e.target.name === "type_nm"        && !document.getElementById("type_kn_"+ref).value) ||
                        (e.target.name === "type_kn"        && !document.getElementById("type_nm_"+ref).value) ||
                        (e.target.name === "priv_class_nm"  && !document.getElementById("priv_class_nm_"+ref).value) ||
                        (e.target.name === "priv_class_kn"  && !document.getElementById("priv_class_kn_"+ref).value) ||
                        (e.target.name === "prod_t_nm"      && !document.getElementById("prod_t_nm_"+ref).value) ||
                        (e.target.name === "prod_k_nm"      && !document.getElementById("prod_k_nm_"+ref).value) ||
                        (e.target.name === "setprod_amount" && !document.getElementById("setprod_amount_"+ref).value) ||
                        (e.target.name === "constitu_amount" && !document.getElementById("constitu_amount_"+ref).value) ||
                        (e.target.name === "order_date_str" && !document.getElementById("order_date_str_"+ref).value && e.target.name === "order_date_end" && !document.getElementById("order_date_end_"+ref).value)){
                        add_attr = 1;
                    }
                }
                if(add_attr === 0){
                    document.getElementById("add").removeAttribute("disabled");
                }
                else{
                    document.getElementById("add").setAttribute("disabled", true);
                }            
            }
            
            // バンドルマスタ　構成数
            if (e.target.name === "constitu_amount"){
                // 売価合計更新
                var saleprice = document.getElementById("saleprice_"+ref).value.replace( /,/g , "" );
                var amount = document.getElementById(e.target.id).value;
                var numData = Number(saleprice * amount);
                document.getElementById("saleprice_all_"+ref).value = numData.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            
        }else{
            document.getElementById("add").setAttribute("disabled", true);
        }
    }

    // checkbox
    if (e.target.name === "order_object"){
        cbxres = e.target.checked;
        checkboxlst = document.getElementsByName('order_object');
        for(i=0;i < checkboxlst.length; i++){
            checkboxlst[i].checked= false;
        }
        document.getElementById(e.target.id).checked = cbxres;
    }
    
//check new data and update if need
    line = f_check_primary_key(ref,new_data);
    if(line !=="" ){
        //new_data[line][e.target.name] = e.target.value;
        if (e.target.name === "order_object"){
            for(i=0;i<new_data.length;i++){
                new_data[i][e.target.name] = "";
            }
            if (cbxres === false){
                new_data[line][e.target.name] = "";
            }
            else{
                new_data[line][e.target.name] = e.target.value;
            }
        }
        else{
            new_data[line][e.target.name] = e.target.value;
        }
        return;
    }     
//update data
//    //console.log(ref+" // "+working_data.length);
    if(working_data.length > ref){
    //case not new line
//        if(pr_key_list.indexOf(e.target.name) !== -1){
//            //change primary key
//            //delete old id
//            for(i=0;i<A_pr_key_id.length;i++){
//                var res_position=del_data.length;
//                var delkey=A_pr_key_id[i];
//                var val = working_data[ref][delkey];
//                del_data[res_position]= {};
//                del_data[res_position][delkey] = val; 
//            }
//            //insert new id
//            ndata={};
//            for(i=0;i<A_key.length;i++){
//                elemt_id        = A_key[i]+"_"+ref;
//                val             = document.getElementById(elemt_id).value;
//                ndata[A_key[i]] = document.getElementById(elemt_id).value;
//            }
//            working_data[working_data.length] = ndata;
//            new_data[new_data.length]= ndata;
//            
//            //remove from upd_data
//            if(upd_data.length > 0){
//                line    = f_check_primary_key(ref,upd_data);   
//                if(line !=="" ){
//                    upd_data.splice(line, 1);
//                }
//            }
//        }
//        else{
//            //normal update
//            upddata={};
//            for(i=0;i<A_pr_key_id.length;i++){
//                elemt_id        = A_pr_key_id[i]+"_"+ref;
//                upddata[A_pr_key_id[i]] = document.getElementById(elemt_id).value;
//            }
//            upddata[e.target.name] = e.target.value;
//            line = f_check_primary_key(ref,working_data);
//            working_data[line][e.target.name] = e.target.value;
//            upd_data[upd_data.length]= upddata; 
//        }
        upddata={};
        for(i=0;i<A_pr_key_id.length;i++){
            elemt_id        = A_pr_key_id[i]+"_"+ref;
            //upddata[A_pr_key_id[i]] = document.getElementById(elemt_id).value;
            upddata['updkey_'+A_pr_key_id[i]] = working_data[ref][A_pr_key_id[i]];
        }
        //upddata[e.target.name] = e.target.value;
        if (document.getElementById(e.target.id).type === "checkbox"){
            if (e.target.checked === false){
                upddata[e.target.name] = "";
            }
            else{
                upddata[e.target.name] = e.target.value;
            }
        }
        else{
            upddata[e.target.name] = e.target.value;
        }
        //line = f_check_primary_key(ref,working_data);
        //working_data[line][e.target.name] = e.target.value;
        working_data[ref][e.target.name] = e.target.value;
        upd_data[upd_data.length]= upddata; 

        return;
    }
    if(addrow == 0){
        document.getElementById("add").setAttribute("disabled", true);
    }     
    //console.log(e);
}

function T_f_blur(e){
    //console.log(e);
   // f_check_data(e);
}

function T_f_keydown(e){
//console.log(e);
//console.log("ici");
   if(e.key == "/" || e.key == "," || e.key == "'" || e.key == '"' || e.key == "\\"){
//       //console.log("new key "+e.key);         
       return false;       
   }
   
//    if(e.target.name === "area_cd"){
//        elem=document.getElementById(e.target.id);
//        if(elem.value.length > 4){
//            elem.value = elem.value.substr(0,3);
//        }
//        //console.log("av: "+elem.value);
//        if(isNaN(elem.value)&&elem.value!==""){
//            //console.log("ap: "+elem.value);
//            alert("数のみ");
//            elem.value="";
//        }
//    }
    if (e.target.name === "type_cd" || e.target.name === "priv_class_cd" || e.target.name === "jicfs_class_cd" || e.target.name === "maker_cd" || e.target.name === "prod_t_cd" || e.target.name === "prod_k_cd" || e.target.name === "prod_cd" || e.target.name === "setprod_amount" || e.target.name === "constitu_amount" || e.target.name === "peri_costprice" || e.target.name === "order_lot"){
        // Num, Arrow, BackSpace(8), Tab(9), Delete(46)
        //if (!(e.key.match(/[0-9]/) || (37 <= e.keyCode && e.keyCode <= 40) || e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 46)){
        //    return false;
        //}
        if (e.target.name === "peri_costprice"){
            pattern = /[0-9.]/;
        }
        else {
            pattern = /[0-9]/;
        }
        if (!(e.key.match(pattern) || (37 <= e.keyCode && e.keyCode <= 40) || e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 46)){
            return false;
        }
    }
}

function T_f_keypress(e){
//console.log(e);
    var maxdigit = 0;
    // 商品部門分類コード
    if(e.target.name === "type_cd"){
        maxdigit = 2;
    }
    // 自社分類コード
    else if(e.target.name === "priv_class_cd"){
        maxdigit = 4;
    }
    // JICFS分類コード
    else if(e.target.name === "jicfs_class_cd"){
        maxdigit = 6;
    }
    // メーカーコード
    else if(e.target.name === "maker_cd"){
        maxdigit = 7;
    }
    // 商品分類コード
    else if(e.target.name === "prod_t_cd"){
        maxdigit = 4;
    }
    // 商品区分コード
    else if(e.target.name === "prod_k_cd"){
        maxdigit = 5;
    }
    // セット商品コード
    else if(e.target.name === "prod_cd"){
        maxdigit = 13;
    }
    // 商品入数構成マスタ　入数
    else if(e.target.name === "setprod_amount"){
        maxdigit = 2;
    }
    // バンドルマスタ　構成数
    else if(e.target.name === "constitu_amount"){
        maxdigit = 3;
    }
    // 期間原価マスタ　発注ロット
    else if(e.target.name === "order_lot"){
        maxdigit = 6;
    }
    
    if(maxdigit > 0){
        elem=document.getElementById(e.target.id);
        if (elem.value.length > maxdigit - 1){
            elem.value = elem.value.substr(0, maxdigit - 1);
        }
    }
}

function T_f_input(e){
    var arrCols = ["type_nm", "type_kn", "priv_class_nm", "priv_class_kn", "prod_t_nm", "prod_k_nm", "setprod_amount", "constitu_amount", "order_date_str", "order_date_end"];
    if( arrCols.indexOf(e.target.name) !== -1){
        if(e.target.value.length > 40){
            return false;
        }
    }    
    if(e.data){
        if(e.data.indexOf("￥") !== -1 || e.data.indexOf("/") !== -1){
           elem = document.getElementById(e.target.id);
           elem.value = elem.value.slice(0,-1);
        }
    }
}

function T_send_result(){
    var data_send  = [];
    var new_data_o = {};
    var del_data_o = {};
    var upd_data_o = {};
    var elem_add = document.getElementById("add");
//    var cd_id = "area_cd_"+elem_add.value;
//    var nm_id = "area_nm_"+elem_add.value;
//    if(document.getElementById(cd_id).value !== "" && document.getElementById(nm_id).value !== "" ){
//        alert("最後の行のデータが送れません。（行が追加されませんでした）");
//    }
    // 追加行に値を入力し[追加]ボタンを押す前に[決定]ボタンを押したので破棄されますという警告
    var allinput = document.getElementsByTagName("input");
    for(var i=0;i < allinput.length;i++){
        //if (allinput[i].id.lastIndexOf("_"+elem_add.value) >= 0){
        if (elem_add && allinput[i].id.lastIndexOf("_"+elem_add.value) >= 0){
            //if (document.getElementById(allinput[i].id).value !== ""){
            //if (document.getElementById(allinput[i].id).type !== 'checkbox' && document.getElementById(allinput[i].id).value !== ""){
            if (document.getElementById(allinput[i].id).type !== 'checkbox' && allinput[i].id !== 'peri_cost_cd_'+elem_add.value && document.getElementById(allinput[i].id).value !== ""){
                //alert("最後の行のデータが送れません。（行が追加されませんでした）");
                alert("※追加行のデータは送られません。");
                break;
            }
        }
    }
// 追加行に全く何も入力されていなければアラートを出す必要はない
// 仮に追加行に何か入力されていたら前段のチェックでアラートを出すので事足りるはず
// よって一旦コメントアウトする
//    if(elem_add.disabled){
//        alert("最後の行のデータが送れません。（コードか名は空ですから）");
//    }
/*
    // 行削除した結果、del_dataはあってもworking_data(画面に表示されているデータ)がない場合は送信不可とする
    if (Object.keys(working_data).length === 0){
        alert("※明細が入力されていません。");
        return;
    }
*/  
    for(i=0;i<new_data.length;i++){
        for(j=0;j<A_pr_key_id.length;j++){
            if(!(/^\d/.test(new_data[i][A_pr_key_id[j]]))){
                continue;
            }
        }
        new_data_o['"'+i+'"'] = new_data[i];
    }
    for(i=0;i<del_data.length;i++){
        for(j=0;j<A_pr_key_id.length;j++){
            if(!(/^\d/.test(del_data[i][A_pr_key_id[j]]))){
                continue;
            }
        }
        del_data_o['"'+i+'"'] = del_data[i];
    }
    for(i=0;i<upd_data.length;i++){
        for(j=0;j<A_pr_key_id.length;j++){
            if(!(/^\d/.test(upd_data[i][A_pr_key_id[j]]))){
                continue;
            }
            if(!(/^\d/.test('updkey_'+upd_data[i][A_pr_key_id[j]]))){
                continue;
            }
        }
        upd_data_o['"'+i+'"'] = upd_data[i];
    }
    //console.log(new_data_o);
    if(new_data.length > 0){
    data_send["new_data"] = JSON.stringify(new_data_o);}
    if(del_data.length > 0){
    data_send["del_data"] = JSON.stringify(del_data_o);}
    if(upd_data.length > 0){
    data_send["upd_data"] = JSON.stringify(upd_data_o);}
    data_send["organization_id"]    = document.getElementById("cmb_ORGANIZATION_ID").value;
    if (destination === "Mst0801"){
        // 商品分類マスタ
        data_send["prod_t_type"]    = document.getElementById("cmb_PROD_T_TYPE").value;
        data_send["prod_t_cd1"]     = document.getElementById("cmb_PROD_T_CD1").value;
        data_send["prod_t_cd2"]     = document.getElementById("cmb_PROD_T_CD2").value;
        data_send["prod_t_cd3"]     = document.getElementById("cmb_PROD_T_CD3").value;
    }
    if (destination === "Mst0901"){
        // 商品区分マスタ
        data_send["prod_k_type"]    = document.getElementById("cmb_PROD_K_TYPE").value;
        data_send["prod_k_type_nm"] = document.getElementById("txt_PROD_K_TYPE").value;
    }
    if (destination === "Mst5102"){
        // 商品入数構成マスタ
        data_send["setprod_cd"]     = document.getElementById("txt_SETPROD_CD").value;
    }
    if (destination === "Mst5301"){
        // バンドルマスタ
        data_send["bundle_cd"]      = document.getElementById("txt_BUNDLE_CD").value;
        data_send["expira_str"]     = document.getElementById("txt_EXPIRA_STR").value;
        data_send["expira_end"]     = document.getElementById("txt_EXPIRA_END").value;
        data_send["sale_price"]     = document.getElementById("txt_SALE_PRICE").value;
    }
    if (destination === "Mst5201"){
        // ミックスマッチマスタ
        data_send["mixmatch_cd"]    = document.getElementById("txt_MIXMATCH_CD").value;
        data_send["expira_str"]     = document.getElementById("txt_EXPIRA_STR").value;
        data_send["expira_end"]     = document.getElementById("txt_EXPIRA_END").value;
        data_send["unit_amoun1"]    = document.getElementById("txt_UNIT_AMOUN1").value;
        data_send["unit_money1"]    = document.getElementById("txt_UNIT_MONEY1").value;
        data_send["unit_amoun2"]    = document.getElementById("txt_UNIT_AMOUN2").value;
        data_send["unit_money2"]    = document.getElementById("txt_UNIT_MONEY2").value;
        data_send["unit_amoun3"]    = document.getElementById("txt_UNIT_AMOUN3").value;
        data_send["unit_money3"]    = document.getElementById("txt_UNIT_MONEY3").value;
    }
    if (destination === "Mst1301"){
        // 特売マスタ
        data_send["sale_plan_cd"]       = document.getElementById("txt_SALE_PLAN_CD").value;
        data_send["sale_plan_nm"]       = document.getElementById("txt_SALE_PLAN_NM").value;
        data_send["sale_plan_str_dt"]   = document.getElementById("txt_SALE_PLAN_STR_DT").value;
        data_send["sale_plan_end_dt"]   = document.getElementById("txt_SALE_PLAN_END_DT").value;
    }
    if (destination === "Mst1401"){
        // 期間原価マスタ
        data_send["supp_cd"]        = document.getElementById("txt_SUPP_CD").value;
    }
    
//    spath       = 'index.php?param='+controller+'/changeinput';
    spath       = 'index.php?param='+destination+'/changeinput';
//    //console.log(spath);
    setDataForAjax( data_send, spath ,'ajaxScreenUpdate' );
}

//function reset_data(){
function T_reset_data(){
    working_data = JSON.parse(data.slice(1,-1));
    new_data    = [];
    upd_data    = [];
    del_data    = [];
    tbody_crea(working_data);
}

function f_check_formt_data(e){
//    //console.log("check_data:"+e.target.name);
//    if(e.target.name == "area_cd" && e.target.value !== ""){
//        document.getElementById(e.target.id).value=("0000" + e.target.value).slice(-4);
//    }
    if(e.target.name == "type_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("00" + e.target.value).slice(-2);
    }
    else if(e.target.name == "priv_class_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("0000" + e.target.value).slice(-4);
    }
    else if(e.target.name == "jicfs_class_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("000000" + e.target.value).slice(-6);
    }
    else if(e.target.name == "maker_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("0000000" + e.target.value).slice(-7);
    }
    else if(e.target.name == "prod_t_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("0000" + e.target.value).slice(-4);
    }
    else if(e.target.name == "prod_k_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("00000" + e.target.value).slice(-5);
    }
    else if(e.target.name == "prod_cd" && e.target.value !== ""){
        document.getElementById(e.target.id).value=("0000000000000" + e.target.value).slice(-13);
    }
}

function f_check_primary_key(ref,a_work){
    result = "";
    for(i=0;i<a_work.length;i++){
       if(a_work[i]){
           for(j=0;j<A_pr_key_id.length;j++){
               elem_id = A_pr_key_id[j]+"_"+ref
               if(a_work[i][A_pr_key_id[j]] !== document.getElementById(elem_id).value){
                   same_key = "";
                   break;
               }
               else{
                   same_key = 1;
               }
           }
           if (same_key === 1){
               result   = i;
               break;
           }
       }
   }
   return result;
}

