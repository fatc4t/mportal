
//始め時:設定します：
//追加ボタンは無効されます。



//メセージの所を作ります。
 elem   = document.getElementById("table_template").parentNode;
 //console.log(elem);
  var t = document.createElement("P");
  t.id = 'message_box';
  t.innerHTML="";
  elem.appendChild(t); 

    
function numberWithCommas(x) {
   var parts = x.toString().split(".");
   parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
   return parts[0];
}
//　関数：　追加ボタンを押す時管理します。
function T_addrow(e){
//新しいデータを取得します。
    ndata={};
    for(i=0;i<A_key.length;i++){
        elemt_id        = A_key[i]+"_"+e.target.value;        
        ndata[A_key[i]] = document.getElementById(elemt_id).value;
    }
//working_dataに新しいデータを入ります。
    working_data[working_data.length] = ndata;
    elemt_cd = "acct_in_out_cd_"+ref;   
    id = document.getElementById(elemt_cd).value;
    line = f_check_db_data(id);
    if(line >= 0){
        if(!upd_data[aio_kbn]){
            upd_data[aio_kbn]=[];
        }
        upd_data[aio_kbn][upd_data[aio_kbn].length] = ndata;  
    }else{
        if(!new_data[aio_kbn]){
            new_data[aio_kbn]=[];
        }        
        new_data[aio_kbn][new_data[aio_kbn].length]= ndata;
    //新しいデータはupd_data[aio_kbn]にあるをチェックします。
        if(upd_data[aio_kbn]){
            line    = f_check_primary_key(e.target.value,upd_data[aio_kbn]);   
            if(line !=="" ){
                //ある場合（upd_data[aio_kbn]にデータを消します）
                upd_data[aio_kbn].splice(line, 1);
            }
        }        
    }

//新しいデータはdel_data[aio_kbn]にあるをチェックします。
    if(del_data[aio_kbn]){
        line    = f_check_primary_key(e.target.value,del_data[aio_kbn]);   
        if(line !=="" ){
            //ある場合（del_data[aio_kbn]にデータを消します）
            del_data[aio_kbn].splice(line, 1);
        } 
    }
    return 'ok';
}

//　関数：　削除ボタンを押す時管理します。
function T_delrow(e){
//主キー名のデータを取得します。
    if(!del_data[aio_kbn]){
        del_data[aio_kbn] = [];
    }
    for(i=0;i<A_pr_key_id.length;i++){       
        var res_position=del_data[aio_kbn].length;
        var delkey=A_pr_key_id[i];
        var val = working_data[e.target.value][delkey];

        del_data[aio_kbn][res_position]= {};
        del_data[aio_kbn][res_position][delkey] = val;
        delete working_data[e.target.value];
        //消すデータはupd_data[aio_kbn]にあるをチェックします。
        if(upd_data[aio_kbn]){
            line    = f_check_primary_key(e.target.value,upd_data[aio_kbn]);   
            if(line !=="" ){
                //ある場合（upd_data[aio_kbn]にデータを消します）
                upd_data[aio_kbn].splice(line, 1);
            }
        }
        //消すデータはdel_data[aio_kbn]にあるをチェックします。
        if(new_data[aio_kbn]){
            line    = f_check_primary_key(e.target.value,new_data[aio_kbn]);
            if(line !=="" ){
                //ある場合（del_data[aio_kbn]にデータを消します）
                new_data[aio_kbn].splice(line, 1);
            }
        }
        return 'ok';
    }
}

//　関数：　入力ボックスに変更があった時管理します。
function T_f_change(e){
//行目を取得します。
    ref = e.target.id.split("_");
    ref = ref[ref.length-1];

    //主キーは空と地区名は変更した場合
    if((e.target.name === "acct_in_out_cd" || e.target.name === "acct_in_out_name") && e.target.value.replace(/\s/g,'') === ""){
        alert("※空白は禁止されています");
        if(ref === document.getElementById("add").value){
            //新しい行の場合
            document.getElementById("add").setAttribute("disabled", true);
        }else{
            //他の場合：前データが出せます
            document.getElementById(e.target.id).value = working_data[ref][e.target.name];
        }
        return;
    }

//データの形式を確認します。
    if(!f_check_formt_data(e)){
        return;
    }
//主キーが空で一意ではないか確認します。

    if(pr_key_list.indexOf(e.target.name) !== -1){
        if(e.target.value.replace(/\s/g,'') === ""){
            //空主キーの場合
            alert("※空白は禁止されています。");
            if(ref !== document.getElementById("add").value){
                document.getElementById(e.target.id).value = working_data[ref][e.target.name];
                return;
            } 
        }
        add_show    = "";   
        cur_val     = [];
        //主キー値を取得します。
        for(i=0;i<A_pr_key_id.length;i++){
            elem_id = A_pr_key_id[i]+"_"+ref;
            if(document.getElementById(elem_id).value){
                cur_val[A_pr_key_id[i]] = document.getElementById(elem_id).value;
                add_show=1;
            }
            else { break; }
        }
        
        if(add_show === 1){
        //主キーは空ない場合
        
        //同じ主キーあるを探します
            same_key="";
            for(i=0;i<working_data.length;i++){
                if(working_data[i]){
                    for(j=0;j<A_pr_key_id.length;j++){
                        if(working_data[i][A_pr_key_id[j]] !== cur_val[A_pr_key_id[j]]){
                            same_key="";
                            break;
                        }
                        else{
                            same_key=1;
                        }
                    }
                    if (same_key ===1){
                        document.getElementById("message_box").innerHTML = " ※コードは"+(i+1)+"行目に使っています";
                        if(ref === document.getElementById("add").value){
                            //console.log("ici2");
                            document.getElementById(e.target.id).value = "";
                            document.getElementById("add").setAttribute("disabled", true); 
                        }else{
                            document.getElementById(e.target.id).value = working_data[ref][e.target.name];
                        }
                        return;
                    }
                    else{
                        document.getElementById("message_box").innerHTML = "";
                    }
                }
            }
            
            // 追加ボタンを管理します。            
            el_id = "acct_in_out_name_"+ref;
            acct_in_out_name_val = document.getElementById(el_id).value;
            if(acct_in_out_name_val !== "" ){
                document.getElementById("add").removeAttribute("disabled");
            }
        }
        else{
           // 追加ボタンを管理します。
           document.getElementById("add").setAttribute("disabled", true); 
        }
    }
    if(e.target.name === "acct_in_out_name" ){
        //地区名の場合
        if( e.target.value !== ""){
            add_val = document.getElementById("add").value;
            if(ref == add_val){
               //新しい行の場合：追加ボタンを管理します。
               var  add_attr = 0;
                for(i=0;i<A_pr_key_id.length;i++){
                    elem_id = A_pr_key_id[i]+"_"+ref; 
                    if(!document.getElementById(elem_id).value){
                        add_attr = 1;
                        break;
                    }
                }
                if(add_attr === 0){
                    document.getElementById("add").removeAttribute("disabled");
                }
                else{
                    document.getElementById("add").setAttribute("disabled", true);
                }            
            }
        }else{
        document.getElementById("add").setAttribute("disabled", true);
        }
    }
  
//新しい行のデータは変更した場合
    if(new_data[aio_kbn]){
        line = f_check_primary_key(ref,new_data[aio_kbn]);
        if(line !=="" ){
            new_data[aio_kbn][line][e.target.name] = e.target.value;
            return;
        }
    }
//他の行のデータは変更した場合
    if(working_data.length > ref){
        if(pr_key_list.indexOf(e.target.name) !== -1){
            //主キーは変更した場合
            //まえ主キーデータを消します。
            for(i=0;i<A_pr_key_id.length;i++){
                if(del_data[aio_kbn]){
                    var res_position=del_data[aio_kbn].length;
                }else{
                    res_position = 0;
                }
                var delkey=A_pr_key_id[i];
                var val = working_data[ref][delkey];
                if(!del_data[aio_kbn]){
                    del_data[aio_kbn] = [];
                }
                del_data[aio_kbn][res_position]= {};
                del_data[aio_kbn][res_position][delkey] = val; 
            }
            //新し主キーデータを入ります。
            ndata={};
            for(i=0;i<A_key.length;i++){
                elemt_id        = A_key[i]+"_"+ref;
                val             = document.getElementById(elemt_id).value;
                ndata[A_key[i]] = document.getElementById(elemt_id).value;
            }
            working_data[ref] = ndata;
            if(new_data[aio_kbn]){
                new_ind = Object.keys(new_data[aio_kbn]).length;
                new_data[aio_kbn][new_ind] = ndata;
            }
            else{
                new_data[aio_kbn] = [];
                new_data[aio_kbn][0] = ndata;
            }
           
            
            //upd_data[aio_kbn]にまえ主キーデータを消します。
            if(upd_data[aio_kbn]){
                if(upd_data[aio_kbn].length > 0){
                    line    = f_check_primary_key(ref,upd_data[aio_kbn]);   
                    if(line !=="" ){
                        upd_data[aio_kbn].splice(line, 1);
                    }
                }
            }
        }else{
            //普通の場合：upddataに新しいデータを入ります。
            upddata={};
            for(i=0;i<A_pr_key_id.length;i++){
                elemt_id        = A_pr_key_id[i]+"_"+ref;
                upddata[A_pr_key_id[i]] = document.getElementById(elemt_id).value;
            }
            upddata[e.target.name] = e.target.value;
            line = f_check_primary_key(ref,working_data);
            working_data[line][e.target.name] = e.target.value;
            if(!upd_data[aio_kbn]){
                upd_data[aio_kbn] = [];
            }
            new_ind = upd_data[aio_kbn].length;
            upd_data[aio_kbn][new_ind]= upddata;
        }
        return;
    }
    
    //新しい行を作れない場合
    if(addrow == 0){
        document.getElementById("add").setAttribute("disabled", true);
    }     
}

//blurの時管理
function T_f_blur(e){
   //もし地区コードのINPUTとmessage_boxのメセージあったら、message_boxのメセージを消します。
   if(e.target.name === "acct_in_out_cd"){
       if( document.getElementById("message_box").innerHTML !== ""){
            document.getElementById(e.target.id).focus();
       }
   }
}

//キーボードのキーを管理します。（普通のキーボードの場合）
function T_f_keydown(e){

   if(e.key == "/" || e.key == "'" || e.key == '"' || e.key == "\\"){     
       return false;       
   }
    if(e.target.name === "acct_in_out_cd" ){
            if( e.key === " " || 65 <= e.keyCode && e.keyCode <= 90  ){
            document.getElementById("message_box").innerHTML = " ※数値のみ入力できます";
            return false;
        }
        else{
            document.getElementById("message_box").innerHTML = "";
        }
    }
    if(e.target.name === "acct_in_out_cd"){
        elem=document.getElementById(e.target.id);
        if(elem.value.length > 1){
            elem.value = elem.value.substr(0,2);
        }       
    }else if(e.target.name === "acct_in_out_name"){
        elem=document.getElementById(e.target.id);
        if(elem.value.length > 9){
            elem.value = elem.value.substr(0,10);
        }        
    }
}
//キーボードのキーを管理します。（日本のキーボードの場合）
function T_f_input(e){
//console.log(e);
    if(e.data){
        if(e.data.indexOf("￥") !== -1 || e.data.indexOf("/") !== -1){
           elem = document.getElementById(e.target.id);
           elem.value = elem.value.slice(0,-1);
        }
    }
    if(e.target.name === "acct_in_out_cd"){
        
        if(isNaN(e.data)&&e.data!==""){
           document.getElementById("message_box").innerHTML = "　※数値のみ入力できます";
           elem = document.getElementById(e.target.id);
           elem.value = "";
        }else if( Number(elem.value) <= 0 || Number(elem.value) > 98){
                elem.value = "";
                document.getElementById("message_box").innerHTML = "　※入出金コードが不正です。【設定範囲:01～98】";            
        }else{            
            document.getElementById("message_box").innerHTML = "";
        }
        if(e.data && e.data.replace(/\s/g, "") === "" ){
           elem = document.getElementById(e.target.id);
           elem.value = "";
        }          
    }
}

//関数：サーバにデータを送ります。
function T_send_result(){
    var data_send  = [];
    var new_data_o = {};
    var del_data_o = {};
    var upd_data_o = {};
    //console.log("here");
    if(!org_id){
        return;
    }
    var elem_add = document.getElementById("add");
    var cd_id = "acct_in_out_cd_"+elem_add.value;
    var nm_id = "acct_in_out_name_"+elem_add.value;
    //メセージを出します。
    if(document.getElementById(cd_id).value !== "" && document.getElementById(nm_id).value !== "" ){
        alert("※最後の行のデータが送れません。（行が追加されませんでした）");
    }
    //対象（object）に配列 (array)をコンバートします
    for(j=1;j<4;j++){

        ind = '"'+j+'"';
        if(new_data[j] && new_data[j].length > 0){
            new_data_o[ind] = {};
            for(i=0;i<new_data[j].length;i++){
                if(new_data[j][i]){new_data_o[ind]['"'+i+'"'] = new_data[j][i];}
            }
        }
        if(del_data[j] && del_data[j].length > 0){
            //console.log(del_data[j].length);
            del_data_o[ind] = {};
            for(i=0;i<del_data[j].length;i++){
                if(del_data[j][i]){del_data_o[ind]['"'+i+'"'] = del_data[j][i];}
            }
        }
        if(upd_data[j] && upd_data[j].length > 0){
            upd_data_o[ind] = {};
            for(i=0;i<upd_data[j].length;i++){
                if(upd_data[j][i]){upd_data_o[ind]['"'+i+'"'] = upd_data[j][i];}
            }
        }
    }

    //console.log(del_data_o);
    //data_sendを作ります。
    if(Object.keys(new_data_o).length > 0){
        //console.log("here");
        data_send["new_data"] = JSON.stringify(new_data_o);}
    if(Object.keys(del_data_o).length > 0){
        data_send["del_data"] = JSON.stringify(del_data_o);}
    if(Object.keys(upd_data_o).length > 0){    
        data_send["upd_data"] = JSON.stringify(upd_data_o);}
    
    //console.log("datasend: ");
    //console.log(data_send);
    org_id = document.getElementById('comp_nm').value
    //data_sendを送ります。
    spath       = 'index.php?param='+controller+'/changeinput&org_id='+org_id;
    //console.log(spath);
    setDataForAjax( data_send, spath ,'ajaxScreenUpdate' );
}

//　関数：始めデータを出します。
function reset_data(){
    if(!org_id){
        return;
    }    
    data = mst6401;
    //aio_kbn = "1";
    working_data = JSON.parse(data.slice(1,-1))[aio_kbn];
    new_data    = [];
    upd_data    = [];
    del_data    = [];
    
    tbody_crea(working_data);
}

//　関数：　地区コードをフォーマットします。
function f_check_formt_data(e){
    if(e.target.name === "acct_in_out_cd" && e.target.value !== ""){
            acct_in_out_cd = ("00" + e.target.value).slice(-2);
            document.getElementById(e.target.id).value = acct_in_out_cd;
    }
    return true;
}

//　関数：　主キーあるをチェックします。
function f_check_primary_key(ref,a_work){
    //console.log(a_work);
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

function f_check_db_data(id){
    if(mst6401_ref[aio_kbn]){
        for(i=0;i < mst6401_ref[aio_kbn].length;i++){
            if(mst6401_ref[aio_kbn][i]["acct_in_out_cd"] && id === mst6401_ref[aio_kbn][i]["acct_in_out_cd"]){
                return i;
            }   
        }
    }
    return -1;
}
