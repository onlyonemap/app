function form_sub() 
{ 
if(form1.ShiftNumber.value=='')
{ 
alert("请填写班次号！"); 
return false; 
} 
if(form1.key.value=='') 
{ 
alert("请填选承运公司名称，注意是填选！！"); 
return false; 
} 

if(form1.Price.value=='') 
{ 
alert("请填写干线每千克的价格！"); 
return false; 
} 

if(form1.Eprice.value=='') 
{ 
alert("请填写干线每立方米的价格！"); 
return false; 
} 

if(document.getElementById("sheng").value=='0')  
{ 
alert("请选择起始仓地址所在省份！"); 
return false; 
} 

if(document.getElementById("shi").value=='0')  
{ 
alert("请选择起始仓地址所在城市！"); 
return false; 
}

if(document.getElementById("xian").value=='0')  
{ 
alert("请选择起始发仓地址所在区！"); 
return false; 
}

if(document.getElementById("sheng1").value=='0')  
{ 
alert("请选择终点仓地址所在省份！"); 
return false; 
} 

if(document.getElementById("shi1").value=='0')  
{ 
alert("请选择终点仓地址所在城市！"); 
return false; 
}

if(document.getElementById("xian1").value=='0')  
{ 
alert("请选择终点仓地址所在区！"); 
return false; 
}
}




function delconfirm(){
if(!confirm("确认要删除本班次么?")){
return false;
}else{
return true;
}
}



function delconfirmb(){
if(!confirm("确认还原本班次么?")){
return false;
}else{
return true;
}
}