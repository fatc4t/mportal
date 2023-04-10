
var elem_input = document.getElementsByTagName('INPUT');
for (i=0;i<elem_input.length;i++ ){
    elem_row = elem_input[i];
    if(elem_row.type === 'text'){
        elem_row.addEventListener("input", check_size_string);
    }
}


function check_size_string(e){
    if(e.target.maxlength){
        kanjisize  = (new TextEncoder('utf-8').encode(e.target.value.replace(/[^\u3000-\u303f\u3040-\u309f\u30a0-\u30ff\uff00-\uff9f\u4e00-\u9faf\u3400-\u4dbf]/g,''))).length*2/3;
        romajisize = e.target.value.replace(/[\u3000-\u303f\u3040-\u309f\u30a0-\u30ff\uff00-\uff9f\u4e00-\u9faf\u3400-\u4dbf]/g,'').length;
        if( (kanjisize+romajisize) > e.target.maxlength){
            document.getElementById(e.target.id).value = e.target.value.slice(0,-1);
        }
    }
}
function add_listener_checksize(elem_id){
    document.getElementById(elem_id).addEventListener("input", check_size_string);
}