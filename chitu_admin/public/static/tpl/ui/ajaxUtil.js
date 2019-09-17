function get(url1,params,methodName){  
  /* 
  ！！！！！！！！！！！！！！这个函数是关键，这个函数包括初始化对象，请求的路径等 
 ajax使用的基本步骤： 
      
 1、初始化ajax引擎 
 2、封装url（设定要请求的路径） 
 3、打开ajax引擎（同步方式、异步的方式；本次传输使用get还是post） 
 4、将要请求的信息通过引擎发送到服务器进行处理 
 5、监听服务器返回给ajax引擎的处理状态 
 6、判断是否交互完毕，如果交互完毕则取出返回的数 
  */    
  //初始化ajax引擎  
  var xhr = new XMLHttpRequest();//这种方式只针对ie浏览器，并且ie6以下还有问题。  
  //重组URL的值，将请求路径和获取的参数一并传过去  
  //在这Math.random（）的防止缓存重复  
  var url=url1+"?"+params+"&r="+Math.random();  
       
 //alert(url);  
        
  //打开引擎  
  xhr.open("get",url,true);   //readyState=1  
        
  //发送请求  
  xhr.send(null);   //readyState=2  
        
  //监听readyState值的改变，每次改变都会执行下面额函数    
  xhr.onreadystatechange=function (){  
             
      //如果等于4，表明交互完毕 ，我们可以取出服务器返回的内容  
      if(xhr.readyState==4){  
                   
    //动态调用方法，为什么说是动态呢？方法的名称是个变量methodName  
            methodName(xhr);             
      }  
             
  }  
}  
       
//$$（）方法用于方便取出 id="id" 的对象  
function $$(id){    
    return document.getElementById(id);  
}