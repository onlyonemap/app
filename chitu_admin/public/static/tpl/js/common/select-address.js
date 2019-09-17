$(document).ready(function () {
	var element = ''; //
    /**
     * @description:  定义函数，获取数据库的省份数据  
     * @param index {string} 父级地址库id
     * @param element {string} 数据展示的class
     */
    function getData(index,ele){
        element = ele;
        // 每次往select节点写入option前先将原有的option节点清掉
        ele.html('');
        // 定义url  
        var url = "/admin/common/getaddress";
        // 定义参数  
        var data={id:index};  
        // 调用ajax 进行交互  
        $.ajax({
        	url: url,
        	type: 'POST',
        	dataType: 'json',
        	data: data,
        })
        .done(function(response) {
        	updataEle(response);
        })
        .fail(function() {
        	console.log("error");
        })
        .always(function() {
        	console.log("complete");
        });
    }
    /**
     * @description:  定义函数，更新select  
     * @param xhr {string} 返回对应的省市区数据
     */
    function updataEle(xhr){
        //将服务器端返回的jason格式的字符串转化为对象  
        var obj = xhr; 

        var options = '<option value="0">--不限--</option>';

        //在此将jason数组对象的下表为id的作为option的value值，将下表为name的值作为文本节点追加给  
        for(var i=0;i<obj.length;i++){  
            options += '<option value="'+obj[i].id+'">'+obj[i].name+'</option>';
        }  

        element.html(options);
    }
    // 页面加载调用初始化省
    getData(0,$('.pro'));
    // 省份改变
    $('body').on('change','.pro',function(){
        var id = $(this).val();
        element = $(this).parents('.selectAddress').find('.city');
        getData(id,element);
    });
    // 城市改变
    $('body').on('change','.city',function(){
        var id = $(this).val();
        element = $(this).parents('.selectAddress').find('.area');
        getData(id,element);
    });
})