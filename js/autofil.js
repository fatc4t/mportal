function creat_listener(){
    // autofil 
    if(document.getElementById("sect_cd")){document.getElementById("sect_cd").addEventListener("change", f_change);}
    //if(document.getElementById("modal_sect_sect_cd")){document.getElementById("modal_sect_sect_cd").addEventListener("change", f_change);}
    if(document.getElementById("sect_k")){document.getElementById("sect_k").addEventListener("change", f_change);}
    if(document.getElementById("sect_s")){document.getElementById("sect_s").addEventListener("change", f_change);}
    if(document.getElementById("sectCdK")){document.getElementById("sectCdK").addEventListener("change", f_change);}
    if(document.getElementById("sectCdS")){document.getElementById("sectCdS").addEventListener("change", f_change);}
    if(document.getElementById("sect_cd_start")){document.getElementById("sect_cd_start").addEventListener("change", f_change);}
    if(document.getElementById("sect_cd_end")){document.getElementById("sect_cd_end").addEventListener("change", f_change);}
    if(document.getElementById("maker_cd")){document.getElementById("maker_cd").addEventListener("change", f_change);}
    if(document.getElementById("supp_cd_k")){document.getElementById("supp_cd_k").addEventListener("change", f_change);}
    if(document.getElementById("supp_cd_s")){document.getElementById("supp_cd_s").addEventListener("change", f_change);}
    if(document.getElementById("supp_cd_start")){document.getElementById("supp_cd_start").addEventListener("change", f_change);}
    if(document.getElementById("supp_cd_end")){document.getElementById("supp_cd_end").addEventListener("change", f_change);}
    if(document.getElementById("supp_cd")){document.getElementById("supp_cd").addEventListener("change", f_change);}
    if(document.getElementById("prod_cd")){document.getElementById("prod_cd").addEventListener("change", f_change);}
    if(document.getElementById("prod_k")){document.getElementById("prod_k").addEventListener("change", f_change);}
    if(document.getElementById("prod_s")){document.getElementById("prod_s").addEventListener("change", f_change);}
    if(document.getElementById("prod_cd_start")){document.getElementById("prod_cd_start").addEventListener("change", f_change);}
    if(document.getElementById("prod_cd_end")){document.getElementById("prod_cd_end").addEventListener("change", f_change);}
    if(document.getElementById("start_code")){document.getElementById("start_code").addEventListener("change", f_change);}
    if(document.getElementById("end_code")){document.getElementById("end_code").addEventListener("change", f_change);}
    if(document.getElementById("prod_cd1")){document.getElementById("prod_cd1").addEventListener("change", f_change);}
    if(document.getElementById("prod_cd2")){document.getElementById("prod_cd2").addEventListener("change", f_change);}
    if(document.getElementById("staff_cd")){document.getElementById("staff_cd").addEventListener("change", f_change);}
    if(document.getElementById("staff_cd_start")){document.getElementById("staff_cd_start").addEventListener("change", f_change);}
    if(document.getElementById("staff_cd_end")){document.getElementById("staff_cd_end").addEventListener("change", f_change);}
    if(document.getElementById("staff_s")){document.getElementById("staff_s").addEventListener("change", f_change);}
    if(document.getElementById("staff_k")){document.getElementById("staff_k").addEventListener("change", f_change);}    
    if(document.getElementById("cust_cd")){document.getElementById("cust_cd").addEventListener("change", f_change);}
    if(document.getElementById("start_cust_cd")){document.getElementById("start_cust_cd").addEventListener("change", f_change);}
    if(document.getElementById("end_cust_cd")){document.getElementById("end_cust_cd").addEventListener("change", f_change);}
    if(document.getElementById("credit_cd")){document.getElementById("credit_cd").addEventListener("change", f_change);}   
    if(document.getElementById("sale_cd_k")){document.getElementById("sale_cd_k").addEventListener("change", f_change);}
    if(document.getElementById("sale_cd_s")){document.getElementById("sale_cd_s").addEventListener("change", f_change);}
    
    // check date
    if(document.getElementById("start_date")){document.getElementById("start_date").addEventListener("change", f_date);}
    if(document.getElementById("end_date")){document.getElementById("end_date").addEventListener("change", f_date);}
    if(document.getElementById("saled_date")){document.getElementById("saled_date").addEventListener("change", f_date);}
    if(document.getElementById("ord_date")){document.getElementById("ord_date").addEventListener("change", f_date);}
    if(document.getElementById("saled_day")){document.getElementById("saled_day").addEventListener("change", f_date);}
    if(document.getElementById("start_dateM")){document.getElementById("start_dateM").addEventListener("change", f_date);}
    if(document.getElementById("end_dateM")){document.getElementById("end_dateM").addEventListener("change", f_date);}
    if(document.getElementById("arr_date")){document.getElementById("arr_date").addEventListener("change", f_date);}
    if(document.getElementById("modal_splan_splan_dt__rg")){document.getElementById("modal_splan_splan_dt__rg").addEventListener("change", f_date);}
    //sort tbody
    if(typeof asc !== 'undefined' && typeof sort_col_list !== 'undefined'){
        t_col_list = sort_col_list.split(',');
        // t_col_list[0]とt_col_list[t_col_list.length-1]は空です。
        elem_thead = document.getElementById('header_h');
        elem_th = elem_thead.getElementsByTagName('th'); 
        for(i=1;i<t_col_list[t_col_list.length-1];i++){
            //add listner
            elem_th[t_col_list[i]].addEventListener("click", table_sort(i));
            // add sort mark depending on asc
            if(asc === 0){
                elem_th[t_col_list[i]].innerHTML = '<u>'+elem_th[t_col_list[i]].innerHTML+'</u>';
            }else if(asc === 1){
                elem_th[t_col_list[i]].innerHTML = '<u>'+elem_th[t_col_list[i]].innerHTML+'</u>▲';
            }else if(asc === -1){
                elem_th[t_col_list[i]].innerHTML = '<u>'+elem_th[t_col_list[i]].innerHTML+'</u>▼';
            }  
        }
    }     
    
}
function f_change(e){
    autofil(e);
}
function autofil(e){
    if(e.target.value === ""){
        return;
    }
    if("sect_cd,sect_k,sect_s,sectCdK,sectCdS,sect_cd_start,sect_cd_end".indexOf(e.target.id) !== -1){
        size=A_Sizelist["sectsize"];
    }else if("maker_cd".indexOf(e.target.id) !== -1){
        size=A_Sizelist["makersize"];
    }else if("supp_cd,supp_cd_k,supp_cd_s,supp_cd_start,supp_cd_end,sale_cd_k,sale_cd_s".indexOf(e.target.id) !== -1){
        size=A_Sizelist["suppsize"];
    }else if("prod_cd,prod_k,prod_s,prod_cd_start,prod_cd_end,start_code,end_code,prod_cd1,prod_cd2".indexOf(e.target.id) !== -1){
        size=A_Sizelist["prodsize"];
    }else if("staff_cd,staff_cd_start,staff_cd_end,staff_s,staff_k".indexOf(e.target.id) !== -1){
        size=A_Sizelist["staffsize"];
    }else if("cust_cd,start_cust_cd,end_cust_cd".indexOf(e.target.id) !== -1){
        size=A_Sizelist["custsize"];
    }else if("credit_cd".indexOf(e.target.id) !== -1){
        size=A_Sizelist["creditsize"];
    }
    //remove not number character
    val=e.target.value.replace(/\D/g, "");
    document.getElementById(e.target.id).value = ("0".repeat(size) + val).slice(-size);
    //test start < end
    if(e.target.id.indexOf("start") !== -1 || e.target.id.indexOf("_k") !== -1 || e.target.id.indexOf("K") !== -1 || e.target.id.indexOf("1") !== -1){
        end_id=e.target.id.replace("_k","_s").replace("K","S").replace("start","end").replace("1","2");
        if(document.getElementById(end_id)){
            val_end = document.getElementById(end_id).value;
            if(Number(val) > Number(val_end) && val_end !== ""){
                //error
                document.getElementById(e.target.id).value="";
                alert("※コードが不正です。開始コードが終了コードを超えています。");
                document.getElementById(e.target.id).focus();
                return;
            }
        }
    }
    if(e.target.id.indexOf("end") !== -1 || e.target.id.indexOf("_s") !== -1 || e.target.id.indexOf("S") !== -1 || e.target.id.indexOf("2") !== -1){
        //console.log("here");
        start_id=e.target.id.replace("_s","_k").replace("S","K").replace("end","start").replace("2","1");
       // //console.log("start_id:"+start_id);
        if(document.getElementById(start_id)){
            start_val=document.getElementById(start_id).value;
            //console.log(start_val+'/'+val);
            if(Number(val) < Number(start_val)){
                //error
                document.getElementById(e.target.id).value="";
                document.getElementById(e.target.id).focus();
                alert("※コードが不正です。開始コードが終了コードを超えています。");
                document.getElementById(e.target.id).focus();
                return;
            } 
        }
    }     
}
function f_date(e){
    check_date(e);
}
function check_date(e){
    c_date = [];
    //console.log(e);
    val = e.target.value;
    today=new Date();
//console.log('check_date e.value: '+e.target.value);
    if(val === ""){
        alert("※営業期間を入力してください。");
        document.getElementById(e.target.id).focus();        
        return;
    }
    if("start_dateM" === e.target.id || "end_dateM" === e.target.id){
        // 年月
        if(val.length === 4){
            if(isNaN(val)){
                //   YY/M
                c_date = val.split('/');
                if(c_date[0].length !== 2){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;
                }else{
                    if(c_date[0] <= 50){
                        c_date[0] = +20+c_date[0];
                    }else{
                        c_date[0] = +19+c_date[0];
                    }
                    c_date[1]=("0"+ c_date[1]);
                    val=c_date[0]+'/'+c_date[1];
                    
                }
            }else{
                // YYMM
                c_date[0]=val.substr(0,2);
                c_date[1]=val.substr(2,2);
                if(Number(c_date[1])>12 || c_date[1] == "00"){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                         
                }                
                if(c_date[0] <= 50){
                    c_date[0] = +20+c_date[0];
                }else{
                    c_date[0] = +19+c_date[0];
                }
                if(c_date[1] == "00" || c_date[1] > 12){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;
                }
                val=c_date[0]+'/'+c_date[1];
            }
        }else if(val.length === 3){
            if(isNaN(val)){
                // not a date
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                        
            }else{
                // YYM
                c_date[0]=val.substr(0,2);
                c_date[1]=val.substr(2,1);
                if(c_date[0] <= 50){
                    c_date[0] = +20+c_date[0];
                }else{
                    c_date[0] = +19+c_date[0];
                }
                if(c_date[1] == "0"){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;
                }
                c_date[1]=("0"+ c_date[1]);
                val=c_date[0]+'/'+c_date[1];                        
            }
        }else if(val.length === 5){
            if(isNaN(val)){
                c_date = val.split('/');
                if(Number(c_date[1])>12 || c_date[1] == "00"){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                         
                }
                // YY/MM
                if(Number(c_date[1])>12 || c_date[1] == "00"){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                         
                }
                if(c_date[0] <= 50){
                    c_date[0] = +20+c_date[0];
                }else{
                    c_date[0] = +19+c_date[0];
                }
                val=c_date[0]+'/'+c_date[1];
            }else{
                // YYYYM
                c_date[0]=val.substr(0,4);
                c_date[1]=val.substr(4,1);
                c_date[1]=("0"+ c_date[1]);
                val=c_date[0]+'/'+c_date[1];
            }
        }else if(val.length === 6){
            if(isNaN(val)){
                // YYYY/M
                c_date = val.split('/');
                //console.log(val);
                //console.log(c_date);
                if(c_date[0].length !== 4){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                         
                }
                c_date[1] = '0'+c_date[1];
                val=c_date[0]+'/'+c_date[1];
            }else{
            // YYYYMM
                c_date[0]=val.substr(0,4);
                c_date[1]=val.substr(4,2);
                if(Number(c_date[1])>12 || c_date[1] == "00"){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                         
                }
                val=c_date[0]+'/'+c_date[1];
            }
        }else if(val.length === 7){
            if(val.split('/')[0].length !== 4 || Number(val.split('/')[1]) > 12){
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                      
            }
            // YYYY/MM
        }else{
            document.getElementById(e.target.id).value="";
            alert("※日付形式ではありません。");
            document.getElementById(e.target.id).focus();
            return;                 
        }
        document.getElementById(e.target.id).value=val;
    }else{
        //　年月日
        if(val.length === 3){
            if(!isNaN(val)){
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                         
            }
            // M/D
            c_date = val.split('/');
            if(c_date[0].length !== 1){
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                             
            }
            c_date[2]= "0"+c_date[1];
            c_date[1]= "0"+c_date[0];
            c_date[0]=today.getFullYear();
            val=c_date[0]+'/'+c_date[1]+'/'+c_date[2];
        }else if(val.length === 4){
            if(isNaN(val)){
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                         
            }
            // YYMD
            c_date[0]=val.substr(0,2);
            c_date[1]=val.substr(2,1);
            c_date[2]=val.substr(3,1);
            if(c_date[0] <= 50){
                c_date[0] = +20+c_date[0];
            }else{
                c_date[0] = +19+c_date[0];
            }                    
            c_date[1]= "0"+c_date[1];
            c_date[2]= "0"+c_date[2];
            val=c_date[0]+'/'+c_date[1]+'/'+c_date[2];
        }else if(val.length === 6){
            if(isNaN(val)){
                c_date = val.split('/');
                if(c_date[0].length !== 2){
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                             
                }
            // YY/M/D
                if(c_date[0] <= 50){
                    c_date[0] = +20+c_date[0];
                }else{
                    c_date[0] = +19+c_date[0];
                }
                c_date[1]= "0"+c_date[1];
                c_date[2]= "0"+c_date[2];
                val=c_date[0]+'/'+c_date[1]+'/'+c_date[2];                    
            }else{
            // YYMMDD
                c_date[0]=val.substr(0,2);
                c_date[1]=val.substr(2,2);
                c_date[2]=val.substr(4,2);
                if(c_date[0] <= 50){
                    c_date[0] = +20+c_date[0];
                }else{
                    c_date[0] = +19+c_date[0];
                }                    
                val=c_date[0]+'/'+c_date[1]+'/'+c_date[2];
            }
        }else if(val.length === 8){
            if(isNaN(val)){
                c_date = val.split('/');
                if(c_date[0].length === 4){
                // YYYY/M/D
                    c_date[1]= '0'+c_date[1];
                    c_date[2]= '0'+c_date[2]; 
                    val=c_date[0]+'/'+c_date[1]+'/'+c_date[2]; 
                }else if(c_date[0].length !== 2){
                // YY/MM/DD
                    if(c_date[0] <= 50){
                        c_date[0] = +20+c_date[0];
                    }else{
                        c_date[0] = +19+c_date[0];
                    }
                    val=c_date[0]+'/'+c_date[1]+'/'+c_date[2]; 
                }else{
                    document.getElementById(e.target.id).value="";
                    alert("※日付形式ではありません。");
                    document.getElementById(e.target.id).focus();
                    return;                    
                }
            }else{
            // YYYYMMDD
                c_date[0]=val.substr(0,4);
                c_date[1]=val.substr(4,2);
                c_date[2]=val.substr(6,2);                    
                val=c_date[0]+'/'+c_date[1]+'/'+c_date[2];
            }
        }else if(val.length === 10){
            if( val.split('/')[0].length !== 4){
                document.getElementById(e.target.id).value="";
                alert("※日付形式ではありません。");
                document.getElementById(e.target.id).focus();
                return;                        
            }
        }else{
            document.getElementById(e.target.id).value="";
            alert("※日付形式ではありません。");
            document.getElementById(e.target.id).focus();
            return;
        }
        // test if val is a date
        //console.log(val);
        test_date = new Date(val);
        if(test_date.toString() === "Invalid Date"){
            document.getElementById(e.target.id).value="";
            alert("※日付形式ではありません。");
            document.getElementById(e.target.id).focus();
            return;                    
        }
        document.getElementById(e.target.id).value=val;
    }
    // check start < end
    if(e.target.id.indexOf("start_date") !== -1){
        end_id=e.target.id.replace("start","end");
        //console.log('endval='+document.getElementById(end_id).value+'#' )
        if(!document.getElementById(end_id) || document.getElementById(end_id).value.replace(/\s/g, "") === ""){
            return;
        }
        val_end=document.getElementById(end_id).value.replace(/\//g,"");
        //console.log(val.replace(/\//g,"")+"#"+val_end);
        if(val.replace(/\//g,"") > val_end){
            //error
            document.getElementById(e.target.id).value="";
            alert("※期間指定が不正です。開始日が終了日を超えています。");
            document.getElementById(e.target.id).focus();
        }
    }else if(e.target.id.indexOf("end_date") !== -1){
        
        start_id=e.target.id.replace("end","start");
        if(!document.getElementById(start_id) || document.getElementById(start_id).value.replace(/\s/g, "") === ""){
            return;
        }        
        val_start=document.getElementById(start_id).value.replace(/\//g,"");
        //console.log(val.replace(/\//g,"")+"#"+val_start);
        if(val.replace(/\//g,"") < val_start){
            //error
            document.getElementById(e.target.id).value="";
            alert("※期間指定が不正です。開始日が終了日を超えています。");
            document.getElementById(e.target.id).focus();
        }        
    }
}


//test sort function

function table_sort(rownb){
    if(true){
        //normal sort
        sort_tbody(rownb);
    }else{
        // other sort
        data_send=[]
        send_asc=[];
        tasc = asc[rownb];
        if(tasc === 0 ){tasc = 1;}
        for( i in asc ){
            send_asc['"'+i+'"']=0;
        }
        send_asc['"'+rownb+'"'] = tasc; 
        data_send['asc'] = JSON.stringify(send_asc);
        elem_cond = document.getElementById('serchTable');
        elem_input = elem_cond.getElementsByTagName('INPUT');
        elem_select = elem_cond.getElementsByTagName('SELECT');
        for(i=0;i<elem_input.length;i++){
            elem=elem_input[i];
            if( elem.hidden || elem.disabled || !elem.value ){
               continue; 
            }else{
                if(elem.type === 'checkbox' && !elem.checked){
                    continue;
                }
                data_send[elem.name] = elem.value;
            }
        }
        for(i=0;i<elem_select.length;i++){
            elem=elem_select[i];
            if(!elem.hidden && !elem.disabled && elem.value){
                data_send[elem.name] = elem.value;
            }
        }
        controller = document.getElementById('codeName').value;
        spath      = 'index.php?param='+controller;
        setDataForAjax( data_send, spath ,'ajaxScreenUpdate' );        
        
    }
}

function sort_tbody(rownb){
    if(!asc || !sort_col_list){
        return;
    }   
    if( sort_col_list.indexOf(','+rownb+',') === -1 ){
        return;
    }
    tasc = asc[rownb];
    elem_tbody = [];
    elem_tr = [];
    row_end = 1;
    var rows, switching, i, x, y, shouldSwitch;
    if(document.getElementById('data')){
        elem_tbody=document.getElementById('data');
    }else{
        //other case
    }
    elem_tr = elem_tbody.getElementsByTagName('TR');
    if(elem_tr[elem_tr.length-1].indexOf('合計') !== -1 ){
        row_end = 2;
    }
    switching = true;
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = elem_tr;
        /*Loop through all table rows (except the
        first, which contains table headers):*/
        for (i = 0; i < (rows.length - row_end); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
            one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[rownb];
            y = rows[i + 1].getElementsByTagName("TD")[rownb];
            //check if the two rows should switch place:
            if( tasc >= 0 ){
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }else{
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }              
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
    elem_thead = document.getElementById('header_h');
    elem_th = elem_thead.getElementsByTagName('th');
    elem_thead.innerHTML = elem_thead.innerHTML.replace('▲','').replace('▼','');
    if(asc[rownb] === 0 ){
        elem_th[rownb].innerHTML = elem_th[rownb].innerHTML+'▲';
    }else if( asc[rownb] > 0 ){
  	elem_th[rownb].innerHTML = elem_th[rownb].innerHTML+'▲';
    }else{
        elem_th[rownb].innerHTML = elem_th[rownb].innerHTML+'▼';
    }
    if(tasc === 0 ){tasc = 1;}
    for( i in asc ){
        asc[i]=0;
    }
    asc[rownb] = -1 * tasc;    
}