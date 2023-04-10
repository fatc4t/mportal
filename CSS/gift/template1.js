
//始め時:設定します：
//追加ボタンは無効されます。
document.getElementById("add").setAttribute("disabled", true);
//追加ボタンに関数を追加します。
document.getElementById("add").addEventListener("blur", after_add);
function after_add(e){
    
    elem_add = document.getElementById("add");
    //console.log(elem_add);
    ref = elem_add.value;
    elem_add.addEventListener("blur", after_add);
    
    elemt_id = "change_nm_"+ref;
    document.getElementById(elemt_id).setAttribute("disabled", true);   
    elemt_id = "point_nm_"+ref;
    document.getElementById(elemt_id).setAttribute("disabled", true);    
}
//会社名
elem   = document.getElementById("sup");
function showdata(e){
    spath       = window.location.href;
    spath       = spath.split('?'); 
    spath       = spath[0]+'?param=Gift/show&org_id='+e.target.value;
    window.location.replace(spath);             
}
var lab = document.createElement( 'label' );
lab.for = "comp_mn";
lab.innerHTML = "店舗名";
elem.appendChild(lab);
var select = document.createElement( 'select' );
select.addEventListener("change", showdata);
select.id = "comp_mn";
opt=document.createElement("option");
opt.value   = 0;
opt.text    = "";
select.options.add(opt,null);
for( var j=0; j < A_other['org_detail'].length; j++){
    opt=document.createElement("option");
    opt.value   = A_other['org_detail'][j].organization_id;
    opt.text    = A_other['org_detail'][j].organization_name;
    select.options.add(opt,null);
}
if(A_other['org_id']){
    select.value = A_other['org_id'];
}
elem.appendChild(select);

//メセージの所を作ります。
 elem   = document.getElementById("table_template").parentNode;
 //console.log(elem);
  var t = document.createElement("P");
  t.id = 'message_box';
  t.innerHTML="";
  elem.appendChild(t); 
  elem_kbn_nm = document.getElementsByName("change_nm");
  for(i=0;i<elem_kbn_nm.length;i++){
      elem_kbn_nm[i].setAttribute("disabled", true);
  }
  elem_kbn_nm = document.getElementsByName("point_nm");
  for(i=0;i<elem_kbn_nm.length;i++){
      elem_kbn_nm[i].setAttribute("disabled", true);
  }  
  
    elem_prate = document.getElementsByName("disc_money");
    for(i=0;i<elem_prate.length-1;i++){ 
       elem_prate[i].value = numberWithCommas(elem_prate[i].value);
   }
    
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
    new_data[new_data.length]= ndata;
//新しいデータはupd_dataにあるをチェックします。
    line    = f_check_primary_key(e.target.value,upd_data);   
    if(line !=="" ){
        //ある場合（upd_dataにデータを消します）
        upd_data.splice(line, 1);
    }
//新しいデータはdel_dataにあるをチェックします。    
    line    = f_check_primary_key(e.target.value,del_data);   
    if(line !=="" ){
        //ある場合（del_dataにデータを消します）
        del_data.splice(line, 1);
    } 
    return 'ok';
}

//　関数：　削除ボタンを押す時管理します。
function T_delrow(e){
//主キー名のデータを取得します。
    for(i=0;i<A_pr_key_id.length;i++){
        var res_position=del_data.length;
        var delkey=A_pr_key_id[i];
        var val = working_data[e.target.value][delkey];
        del_data[res_position]= {};
        del_data[res_position][delkey] = val;  
        delete working_data[e.target.value];
        //消すデータはupd_dataにあるをチェックします。
        line    = f_check_primary_key(e.target.value,upd_data);   
        if(line !=="" ){
            //ある場合（upd_dataにデータを消します）
            upd_data.splice(line, 1);
        }
        //消すデータはdel_dataにあるをチェックします。
        line    = f_check_primary_key(e.target.value,new_data);
        if(line !=="" ){
            //ある場合（del_dataにデータを消します）
            new_data.splice(line, 1);
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
    if(e.target.name === "gift_certi_cd" && e.target.value.replace(/\s/g,'') === ""){
        alert("※※空白は禁止されています");
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
    f_check_formt_data(e);
//主キーが空で一意ではないか確認します。

    if(pr_key_list.indexOf(e.target.name) !== -1){
        if(e.target.value.replace(/\s/g,'') === ""){
            //空主キーの場合
            alert("※※空白は禁止されています。");
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
                        document.getElementById("message_box").innerHTML = " ※コードは"+(i+1)+"行目に使っています※";
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
            el_id = "gift_certi_cd_"+ref;
            gift_certi_cd_val = document.getElementById(el_id).value;
            if(gift_certi_cd_val !== "" ){
                document.getElementById("add").removeAttribute("disabled");
            }
        }
        else{
           // 追加ボタンを管理します。
           document.getElementById("add").setAttribute("disabled", true); 
        }
    }
    else if(e.target.name === "gift_certi_cd" ){
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
    line = f_check_primary_key(ref,new_data);
    if(line !=="" ){
        new_data[line][e.target.name] = e.target.value;
        return;
    }
    
//他の行のデータは変更した場合
    if(working_data.length > ref){
        if(pr_key_list.indexOf(e.target.name) !== -1){
            //主キーは変更した場合
            //まえ主キーデータを消します。
            for(i=0;i<A_pr_key_id.length;i++){
                var res_position=del_data.length;
                var delkey=A_pr_key_id[i];
                var val = working_data[ref][delkey];
                del_data[res_position]= {};
                del_data[res_position][delkey] = val; 
            }
            //新し主キーデータを入ります。
            ndata={};
            for(i=0;i<A_key.length;i++){
                elemt_id        = A_key[i]+"_"+ref;
                val             = document.getElementById(elemt_id).value;
                ndata[A_key[i]] = document.getElementById(elemt_id).value;
            }
            working_data[working_data.length] = ndata;
            new_data[new_data.length]= ndata;
            
            //upd_dataにまえ主キーデータを消します。
            if(upd_data.length > 0){
                line    = f_check_primary_key(ref,upd_data);   
                if(line !=="" ){
                    upd_data.splice(line, 1);
                }
            }
        }
        else{
            //普通の場合：upddataに新しいデータを入ります。
            upddata={};
            for(i=0;i<A_pr_key_id.length;i++){
                elemt_id        = A_pr_key_id[i]+"_"+ref;
                upddata[A_pr_key_id[i]] = document.getElementById(elemt_id).value;
            }
            upddata[e.target.name] = e.target.value;
            line = f_check_primary_key(ref,working_data);
            working_data[line][e.target.name] = e.target.value;
            upd_data[upd_data.length]= upddata; 
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
   if(e.target.name === "gift_certi_cd"){
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
    if(e.target.name === "gift_certi_cd" || e.target.name === "change_kbn" || e.target.name === "point_kbn" || e.target.name === "disc_money"){
            if( e.key === " " || 65 <= e.keyCode && e.keyCode <= 90  ){
            document.getElementById("message_box").innerHTML = " ※数値のみ入力できます";
            return false;
        }
        else{
            document.getElementById("message_box").innerHTML = "";
        }
    }
    if(e.target.name === "gift_certi_cd"){
        elem=document.getElementById(e.target.id);
        if(elem.value.length > 2){
            elem.value = elem.value.substr(0,1);
        }
    }
    else if(e.target.name === "disc_money"){
        elem=document.getElementById(e.target.id);
        //console.log(!isNaN(e.key));
        if(elem.value.length > 5){
            elem.value = elem.value.substr(0,3);
        }    
    }else if(e.target.name === "point_kbn"){
        elem=document.getElementById(e.target.id);
        elem.value = elem.value.substr(0,1);        
        ////console.log(e.key +"> 1 = " + (e.key > 1));
        if(e.key > 1){
            document.getElementById("message_box").innerHTML = " ※ 得点区分が不正です。【設定範囲:0～1】※";
            return false;
        }else{
            document.getElementById("message_box").innerHTML = "";
            ref = e.target.id.split("kbn_")[1];
            elem_id = "point_nm_"+ref; 
            if(e.key == 0){
                document.getElementById(elem_id).value = "得点除外しない";
            }else if(e.key == 1){
                document.getElementById(elem_id).value = "得点除外する";
            }
        }                
    }else if(e.target.name === "change_kbn"){
        elem=document.getElementById(e.target.id);
        elem.value = elem.value.substr(0,1);        
        ////console.log(e.key +"> 1 = " + (e.key > 1));
        if(e.key > 1){
            document.getElementById("message_box").innerHTML = " ※ お釣り区分が不正です。【設定範囲:0～1】※";
            return false;
        }else{
            document.getElementById("message_box").innerHTML = "";
            ref = e.target.id.split("kbn_")[1];
            elem_id = "change_nm_"+ref; 
            if(e.key == 0){
                document.getElementById(elem_id).value = "お釣無し";
            }else if(e.key == 1){
                document.getElementById(elem_id).value = "お釣有り";
            }
        }                
    }else if(e.target.name === "gift_certi_nm"){
        elem=document.getElementById(e.target.id);
        if(elem.value.length > 24){
            elem.value = elem.value.substr(0,23);
        }        
    }else if(e.target.name === "gift_certi_kn"){
        elem=document.getElementById(e.target.id);
        if(elem.value.length > 15){
            elem.value = elem.value.substr(0,14);
        }        
    }
}
//キーボードのキーを管理します。（日本のキーボードの場合）
function T_f_input(e){
////console.log(e);
    if(e.data){
        if(e.data.indexOf("￥") !== -1 || e.data.indexOf("/") !== -1){
           elem = document.getElementById(e.target.id);
           elem.value = elem.value.slice(0,-1);
        }
    }
    if(e.target.name === "gift_certi_cd"){    
        if(isNaN(e.data)&&e.data!==""){
           document.getElementById("message_box").innerHTML = "　※数値のみ入力できます";
           elem = document.getElementById(e.target.id);
           elem.value = "";
        }else{
            document.getElementById("message_box").innerHTML = "";
        }
        if(e.data && e.data.replace(/\s/g, "") === ""){
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
    var elem_add = document.getElementById("add");
    var cd_id = "gift_certi_cd_"+elem_add.value;
    var nm_id = "gift_certi_nm_"+elem_add.value;
    //メセージを出します。
    if(document.getElementById(cd_id).value !== "" && document.getElementById(nm_id).value !== "" ){
        alert("※最後の行のデータが送れません。（行が追加されませんでした）");
    }
    if(elem_add.disabled){
        alert("※最後の行のデータが送れません。（地区のコードか地区名は空ですから）");
    }
    //対象（object）に配列 (array)をコンバートします
    for(i=0;i<new_data.length;i++){
        new_data_o['"'+i+'"'] = new_data[i];
    }
    for(i=0;i<del_data.length;i++){
        del_data_o['"'+i+'"'] = del_data[i];
    }
    for(i=0;i<upd_data.length;i++){
        upd_data_o['"'+i+'"'] = upd_data[i];
    }
    
    //data_sendを作ります。
    if(new_data.length > 0){
    data_send["new_data"] = JSON.stringify(new_data_o);}
    if(del_data.length > 0){
    data_send["del_data"] = JSON.stringify(del_data_o);}
    if(upd_data.length > 0){    
    data_send["upd_data"] = JSON.stringify(upd_data_o);}
    //console.log(data_send);
    org_id = document.getElementById('comp_mn').value
    //data_sendを送ります。
    spath       = 'index.php?param='+controller+'/changeinput&org_id='+org_id;
    //console.log(spath);
    setDataForAjax( data_send, spath ,'ajaxScreenUpdate' );
}

//　関数：始めデータを出します。
function reset_data(){
    working_data = JSON.parse(data.slice(1,-1));
    new_data    = [];
    upd_data    = [];
    del_data    = [];
    tbody_crea(working_data);
}

//　関数：　地区コードをフォーマットします。
function f_check_formt_data(e){
    if(e.target.name == "gift_certi_cd" && e.target.value !== ""){
        gift_cd = ("0".repeat(A_other['gift_size']) + e.target.value).slice(-A_other['gift_size']);
        document.getElementById(e.target.id).value = gift_cd;
    }
    if(e.target.name == "disc_money" && e.target.value !== ""){
        document.getElementById(e.target.id).value = numberWithCommas(e.target.value);
    }
}

//　関数：　主キーあるをチェックします。
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
