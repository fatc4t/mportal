/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function Ishome()
{
	if (params=location.search.split('?')[1] != null)
	{
		paramsNoF = param.split('&');
		len = paramsNoF.length;
		for (var i = 0; i < len ; i++)
		{
		 if (paramsNoF[i].split('=')[0] == 'home' && (paramsNoF[i].split('=')[1] == 1 ))
			{
			 return true;
			}
		}
	}
}


function makeTableHTML(TbodyId) {
// \****/ Need declare Asc = new Array(); before the table (best in the beginin of body or head)/****\
// Determine type of entries: array of element or array

	if (Array.isArray(Entries) || typeof(Entries) === 'object')
	{
		this['Asc_' + TbodyId] = new Array;
		var Tables  = '<div  class="scrollingtable"><div><div><table border=1 id="T_test"><thead >';
		if (Array.isArray(Entries[0]))
		{
//		 var Titles = new Array();
		 if (Titles != null ){
			 for(var i=0; i < Entries[0].length; i++)
			 {
				Titles[i] = Entries[0][i] ;
			 }
		 }
		 Tables += titletable(Titles,TbodyId);
		 Tables += '</thead><tbody id="' + TbodyId + '"　>';
		 Tables += ArrayTable (Entries,PageNo);
		}
		else 
		{
			
         if (Titles != null )Titles  = Object.keys(Entries[0]); 
		 Titles_trans = JSON.parse(JSON.stringify(Titles));
		 if (jp_title != null)
		 {
			for(var i=0; i < jp_title.length; i++)
			{
				for(var j=0; j < Titles_trans.length; j++)
				{
					if ( Titles_trans[j] == jp_title[i]['field_name'])
					Titles_trans[j]= jp_title[i]['nihongo'];
				}
			}
	
		 }
		 //console.log(Titles[0]);
		 Tables += titletable(Titles_trans,TbodyId,Titles);

		 Tables += '</thead><tbody id="' + TbodyId + '" >';

		 Tables += ObjectTable (Entries);

		}
		Tables += "</tbody></table></div></div></div>";
	}

	for(var i=0; i < Titles.length; i++)
	{
		this['Asc_' + TbodyId][i]= 1;
	}
	
	return Tables;
}

function titletable (myArray,TbodyId,Titles)
{
				
	result  = '<tr>';
	for(var i=0; i<myArray.length; i++)
		{
			result += '<th onclick=\'sort_table(' + '"'+TbodyId+'", "' + Titles[i] + '", ' + 'Asc_' + TbodyId + '[' + i + '], ' + i +')\'><div label="'+myArray[i]+'"></div></th>';
        }	
	result += '<th class="scrollbarhead"></th></tr>';

	return result;
}

function ArrayTable (myArray)
{
	result = '';
    for(var i=1; i<myArray.length; i++) {
        result += "<tr>";
        for(var j=0; j < myArray[i].length; j++){
            result += "<td>"+myArray[i][j]+"</td>";
        }
    result += "</tr>";
    }
   

    return result;
}


function ObjectTable (myObject, t_titles)
{
	Size = displayRecordCnt;
	if (t_titles == null) t_titles = Titles;
	
	var j;
	var i = (displayPageNo - 1) * Size;
	var end = i + Size; 
	result = '';
		if (end > myObject.length) end = myObject.length;

//		console.log ('myObject: '+ myObject);
//	console.log (Titles[0]) ;
    for(i; i < end ; i++) 
	{
        result += "<tr>";

        for(var j=0; j<t_titles.length; j++)
		 {
//		console.log('key= ' + Titles[i] + ' // myObject= ' + myObject[i][Titles[j]]);
            result += "<td>"+myObject[i][t_titles[j]]+"</td>";
         }
        result += "</tr>";
    }
    return result;
}


function sort_table(tbody, col, asc, col_n){


	displayPageNo = 1;
	Show_manage();
	myObject = Entries.sort(compareValues( col, asc));
	
	Tables  = ObjectTable (myObject);

	rlen = Titles.length;
	// Rewrite the tbody
    document.getElementById(tbody).innerHTML = Tables; 
	// Fixe sort to ascendent
	for(i = 0; i < rlen; i++)this['Asc_' + tbody][i]=1;
	// For the column change type short () asc => desc or desc => asc).
	this['Asc_' + tbody][col_n] *= -1*asc;
}

function compareValues(key, order) {
  return function(a, b) {
    if(!a.hasOwnProperty(key) || 
       !b.hasOwnProperty(key)) {
  	  return 0; 
    }
	if (Number(a[key]) && Number(b[key]))
	{
		varA = Number(a[key]).toLocaleString('jp', {minimumIntegerDigits:21, useGrouping:false});
		varB = Number(b[key]).toLocaleString('jp', {minimumIntegerDigits:21, useGrouping:false});

	}
	else
	{
		varA = (typeof a[key] === 'string') ? 
		  a[key].toUpperCase() : a[key];
		varB = (typeof b[key] === 'string') ? 
		  b[key].toUpperCase() : b[key];
	} 
//console.log(varA + ' // ' + varB);
    let comparison = 0;
    if (varA > varB) {
      comparison = 1;
    } else if (varA < varB) {
      comparison = -1;
    }
    return (
      (comparison * order)
    );
  };
}

function CreatSelect (Data, SelectName)
{
	Keys = '';
//	alert (typeof(x[0]));
	if (!Array.isArray(Data)) return false;
	if (Array.isArray(Data[0])) Data = Data[0];
	else if (typeof(Data[0]) === 'object')
	{
		Key = Object.keys(Data);
	}

	Select = '';
	Select += '<select id="' + SelectName + '" name="' + SelectName + '">';
	Select += '<option value=""></option>';
	if (Keys != '')
		{
	for(var i=0; i < Data.length ; i++)
	 {
		Select += '<option value="' + Data[i][key]  + '">' + Data[i][key]  + '</option>';
	 }
	
	}
	else
	{ 
		for(var i=0; i < Data.length ; i++)
		 {
			Select += '<option value="' + Data[i]  + '">' + Data[i]  + '</option>';
		 }
	}
	Select += '</select>';

	 return Select;
}

function Unic_data (Source,attr_name)
{
	temp = {}
// Store each of the elements in an object keyed of of the name field.  If there is a collision (the name already exists) then it is just replaced with the most recent one.
for (var i = 0; i < Source.length; i++) {
	var_name = Source[i][attr_name]
    temp[var_name] = Source[i];
//	console.log('temp[' + var_name + '] = ' + temp[var_name] );
}
// Reset the array in varjson
result = [];
// Push each of the values back into the array.
for (var o in temp) {
    result.push(o);
}
//	console.log('result='+result);
	return result;
}


/** @Con : [Id,Value]
*/
function Search_dat (T_data,Con)
{
   var W_T_data = T_data;
	if (Array.isArray(Con))
	{
//		if (Con.length == 2)	{Entries = JSON.parse(JSON.stringify(dataAll));return Entries;}
		for(var i=0; i < Con.length; i++)
		{
			var Rlen=W_T_data.length;

			if (Con[i][0] == 'conn_date_start' || Con[i][0] == 'conn_date_end')
			{

				//test.setDate(test.getDate() + 1);
				key='conn_date';
				if (Con[i+1][0] == 'conn_date_end')
				{
					if (Con[i][1] <= Con[i+1][1]) {start = Con[i][1]; end = Con[i+1][1]}
					else {start = Con[i+1][1]; end = Con[i][1]}
					i++;
				}
				else
				{
					start = Con[i][1]; end = new Date().toLocaleDateString();
				}
			 	d_start = new Date(start);
				d_end	 = new Date(end);
				d_end.setDate(d_end.getDate() + 1)
				d_end.setHours(0)

				
			 for (var j=0; j <Rlen; j++)
			 {
				
				d_data = new Date(W_T_data[j][key]);
				if (d_start >= d_data || d_end <= d_data)
				 {
					W_T_data.splice(j, 1);
					--j;
					--Rlen;
				 }
			 }

			}
			else
			{
			 for (var j=0; j <Rlen; j++)
			 {
//				console.log('j= ' + j + ' / Rlen= ' + Rlen);
				if (Con[i][1] != W_T_data[j][Con[i][0]])
				{
					W_T_data.splice(j, 1);
					--j;
					--Rlen;
				}
			 }
			}
		}
	}

return W_T_data;	
}


function Send_querry (data, target, method)
{

		method = method || "get";
		xhttp = new XMLHttpRequest();
                //console.log(method);
		xhttp.onreadystatechange=function() 
		{
                    //console.log('xhttp.readyState:'+xhttp.readyState+'   xhttp.status:'+xhttp.status);
			if (xhttp.readyState === 4)
			 {   //if complete

				if(xhttp.status === 200)
				 {  //check if "OK" (200)

					result1=this.response;
					//console.log('result1='+result1);
//					return result1;
                                        //return xhttp.responseText;
				 } 
				else 
				 {
				 }
			 } 
		}
		url = target+data;
		if (method == "get")
		{
//			console.log(url);
		xhttp.open("GET", url, false);
		}
		else xhttp.open("POST", target, false);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(data);


}

function downloadURI(uri, name) 
{
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    link.click();
}


