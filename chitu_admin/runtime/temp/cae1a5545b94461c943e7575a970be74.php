<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\carriers\addround.html";i:1528162648;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title>赤途后台管理系统</title>
<!--[if lt IE 9]>
<meta http-equiv="refresh" content="0;/static/tpl/ie.html" />
<![endif]-->

<link rel="shortcut icon" href="favicon.ico"> 
<link rel="icon" href="/static/tpl/img/favcion.png" type="image/x-icon" />
<!-- bootstrap -->
<link href="/static/tpl/css/bootstrap.min.css?v=3.3.6" rel="stylesheet" />
<!-- 字体文件 -->
<link href="/static/tpl/css/font-awesome.min.css?v=4.4.0" rel="stylesheet" />
<!-- 动画文件 -->
<!-- <link href="/static/tpl/css/animate.min.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/iCheck/custom.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/datapicker/datepicker3.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/footable/footable.core.css" rel="stylesheet" /> -->
<!--图片放大css-->
<link href="/static/tpl/css/boxImg.css" type="text/css" rel="stylesheet" />
<!-- 页面css -->
<link href="/static/tpl/css/style.min862f.css" rel="stylesheet" />


    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/plugins/layer/layer.css">
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <form class="form-horizontal m-t" id="commentForm" method="post" action="/admin/carriers/updateround">
                               
                            <div class="form-group selectAddress">
                                <label class="col-sm-2 control-label">提货城市</label>
                                <div class="col-sm-3">
                                    <select  class="form-control pro" name="tpro"></select>
                                </div>
                                <div class="col-sm-3">
                                    <select  class="form-control city" data-type="1" name="tcity"></select>
                                </div>
                                
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group  citylist">

                                
                            </div>
                            <div class=" info">
                                <div class=" info-item">
                                    <div class="form-group selectAddress">
                                        <label class="col-sm-2 control-label">周边提货城市</label>
                                        <div class="col-sm-2">
                                            <select  class="form-control pro" name="roundcity[0][ppro]"></select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select  class="form-control city" data-type="2" name="roundcity[0][pcity]"></select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select  class="form-control area"  name="roundcity[0][parea]"></select>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" class="btn btn-primary addInfo">添加</button>
                                        </div>  
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="comid" calss="comid" value="<?php echo $cid; ?>">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- jquery -->
    <script type="text/javascript" src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <!-- bootstarp -->
    <script type="text/javascript" src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- 遮罩提示 -->
    <script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <!-- jQuery Validation plugin javascript -->
    <script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
    <!-- 自动补全 -->
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>        
    <script type="text/javascript">
        $(document).ready(function () {
           
            var element = ''; //
            var companyid = "<?php echo $cid; ?>";
            /**
             * @description:  定义函数，获取数据库的省份数据  
             * @param index {string} 父级索引
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

                var options = '<option value="0">----请选择----</option>';

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
               // getRoundcityData(id,);
            });
            $('body').on('change','.city',function(){
                var id = $(this).val();
                element = $(this).parents('.selectAddress').find('.area');
                getData(id,element);
                var type = $(this).attr('data-type');
                if(type == 1) {
                    getRoundcityData(id,companyid,function(response) {
                        if (response !='') {
                                var str = '<div class="col-sm-10"><label class="col-sm-2 control-label">已选城市</label></div>';
                                for (var i=0;i< response.length; i++) {
                        
                                    str += '<div class="col-sm-10" id="cityinfo'+ i +'">'
                                    str +='<label class="col-sm-2 control-label"></label>'
                                    str += '<div class="col-sm-3">'
                                    str += '<input disabled="disabled" value="'+response[i].cityname+ response[i].areaname +'" class="form-control" type="text">'
                                    str +=  '<input type="hidden"  name="selcity['+ i +'][cityid]" value="'+response[i].cityid+'" >'
                                    str +=  '<input type="hidden"  name="selcity['+ i +'][areaid]" value="'+response[i].areaid+'" >'
                                    str += '</div>'

                                    str += '<div class="col-sm-2">'
                                    str +=  '<button data-toggle="dropdown" onclick=updriver('+ i +') value="" class="btn btn-default pull-right dropdown-toggle" o aria-expanded="false">删除</button>'
                                    str += '</div>'
                                    str += '</div>';
                                };
                                $( ".citylist" ).html(str);
                        }
                    });
                }
               
            });



           /**
             * 根据公司id获取下面的提货区域周边城市
             * @param  {number} companyid 承运公司id
             * @param  {number} pickid 提货城市id
             * @return {[type]}           [description]
             */
            function getRoundcityData(pickid,companyid,callback) {
                $.ajax({
                    url: '/admin/carriers/roundcity',
                    type: 'POST',
                    dataType: 'json',
                    data: {companyid: companyid, pickid:pickid },
                })
                .done(function(response) {
                   // driverlist = response.driverlist;
                    //carlist = response.carlist;
                    callback(response);
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
                
            }
            
            // 添加周边城市信息
            $('body').on('click','.addInfo',function(){
                var info = $(this).parents('.info');
                var len = info.find('.info-item').length;
                var str = '<div class="info-item">'
                        +'<div class="form-group selectAddress">'
                        +'<label class="col-sm-2 control-label"></label>'
                        +'<div class="col-sm-2">'
                        +'<select  class="form-control pro" name="roundcity['+ len +'][ppro]"></select>'
                        +'</div>'
                        +'<div class="col-sm-2">'
                        +'<select  class="form-control city" name="roundcity['+ len +'][pcity]"></select>'
                        +'</div>'
                         +'<div class="col-sm-2">'
                        +'<select  class="form-control area" name="roundcity['+ len +'][parea]"></select>'
                        +'</div>'
                        +'<div class="col-sm-2">'
                        +'<button type="button" class="btn btn-primary removeInfo">删除</button>'
                        +'</div>'  
                        +'</div>'
                        +'</div>';
                $(this).parents('.info').append(str);
                var index = $('.info-item').length-1;
                //点击添加城市选择初始化城市
                getData(0,$('.info-item').eq(index).find('.pro'));
            });
            // 移除周边城市信息
            $('body').on('click','.removeInfo',function(){
                $(this).parents('.info-item').remove();
            });
          
            // 定制线路表单必填、验证
            $("#commentForm").validate({

                // 数据提交
                submitHandler:function(form){
                    $.ajax({
                        url: '/admin/carriers/updateround',
                        type: 'POST',
                        dataType: 'json',
                        data: $('#commentForm').serialize(),
                    })
                    .done(function(response) {
                        console.log(typeof response);
                        console.log(response.code);
                        if(response.code){ // 提交成功
                            layer.msg(response.message);
                            //parent.layer.close(index);
                            parent.location.reload();
                            //window.history.back(-1);
                        }else{ // 提交失败
                            layer.msg(response.message);
                        }
                    })
                    .fail(function() {
                        console.log("error");
                    })
                    .always(function() {
                        console.log("complete");
                    });
                } 
            });
        });
    </script>
   
    <script type="text/javascript">
           
            function updriver(id){
                var num = $("input:text[id=cityinfo" + id + "]").length + 1;
                for (iii = 0; iii < num; iii++) {
                    $("#cityinfo" + id).remove();
                }
            }
    </script>

    
</body>
</html>
