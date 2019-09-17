<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\company\updategan.html";i:1563854261;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <form class="form-horizontal m-t" id="commentForm" method="post" action="/admin/company/addmessage">
                    <div class="form-group" id="thlbb">
                        <label class="col-sm-2 control-label">提货区域列表</label>
                        <div class="col-sm-10" id="thlb">
                            <?php if(isset($list['ti'])): if(is_array($list['ti']) || $list['ti'] instanceof \think\Collection || $list['ti'] instanceof \think\Paginator): $k = 0; $__LIST__ = $list['ti'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tiid): $mod = ($k % 2 );++$k;?>
                            <div class="row">
                                <div class="col-sm-10">
                                    <input class="form-control"  disabled="disabled" value="<?php echo $tiid['provincename']; ?>   提货费用：<?php echo $tiid['price']; ?>  单点提货费用：<?php echo $tiid['rate']; ?> " type="text">
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-danger pull-right del-tlist" data-id="value" data-pid="<?php echo $tiid['tpid']; ?>" >删除</button>
                                </div>
                            </div> 
                            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </div>
                    </div>
                     
                    <div class="form-group selectAddress">
                        <label class="col-sm-2 control-label">提货服务区域 </label>
                        <div class="col-sm-3">
                            <select class="form-control pro" id="tpro" name="tpro"></select>
                        </div>
                        <div class="col-sm-3">
                            <select class="form-control city" id="tcity" name="tcity"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">提货基础价</label>
                        <div class="col-sm-3">
                            <input id="tihuoprice" name="tihuoprice"  type="text" class="form-control" 
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                            onafterpaste="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <label class="col-sm-2 control-label">多点提货递增费</label>
                        <div class="col-sm-3">
                            <input id="tihuodprice" name="tihuodprice"   type="text" class="form-control" 
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                            onafterpaste="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-primary pull-right add-tlist">添加</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group" id="pslb">
                        <label class="col-sm-2 control-label">配送区域列表</label>
                        <div class="col-sm-10 " id="pslbb">
                            <?php if(isset($list['pei'])): if(is_array($list['pei']) || $list['pei'] instanceof \think\Collection || $list['pei'] instanceof \think\Paginator): $k = 0; $__LIST__ = $list['pei'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$peiid): $mod = ($k % 2 );++$k;?>
                            <div class="row">
                                <div class='col-sm-12 col-lg-5'>
                                    <input disabled='disabled' value='<?php echo $peiid['province']; if(isset($peiid['can']) !=''): ?> （仓库地址：<?php if(is_array($peiid['can']) || $peiid['can'] instanceof \think\Collection || $peiid['can'] instanceof \think\Paginator): $i = 0; $__LIST__ = $peiid['can'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ku): $mod = ($i % 2 );++$i;?><?php echo $ku['can_address']; endforeach; endif; else: echo "" ;endif; ?>）<?php endif; ?>' class=form-control type='text'>
                                </div>
                                <div class='col-sm-12 col-lg-5'>
                                    <input disabled='disabled' value='配送费用：<?php echo $peiid['price']; ?> 单点配送费用：<?php echo $peiid['rate']; ?>' class=form-control type='text'>
                                </div>
                                <div class='col-sm-12 col-lg-2'> 
                                    <button class='btn btn-danger del-plist' data-pid="<?php echo $peiid['tpid']; ?>">删除</button>
                                </div>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group selectAddress">
                        <label class="col-sm-2 control-label">配送服务区域</label>
                        <div class="col-sm-3">
                            <select  class="form-control pro" id="ppro" name="ppro" ></select>
                        </div>
                        <div class="col-sm-3">
                            <select  class="form-control city" id="pcity" name="pcity" ></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">配送基础价</label>
                        <div class="col-sm-3">
                            <input name="peisongprice"  type="text" class="form-control" 
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                            onafterpaste="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <label class="col-sm-2 control-label">多点配送递增费</label>
                        <div class="col-sm-3">
                            <input name="peidongdprice"   type="text" class="form-control" 
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                            onafterpaste="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                    </div>
           
                    <div class="form-group selectAddress">
                        <label class="col-sm-2 control-label">配送仓区域</label>
                        <div class="col-sm-3">
                            <select class="form-control pro" id="canprovince" name="canprovince"></select>
                        </div>
                        <div class="col-sm-3">
                            <select class="form-control city" id="cancity" name="cancity"></select>
                        </div>
                         <div class="col-sm-3">
                            <select class="form-control area" id="canarea" name="canarea"></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">配送仓地址</label>
                        <div class="col-sm-8">
                            <input id="canaddinfo" name="canaddinfo"  placeholder="补全地址" type="text" class="form-control" >
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-primary pull-right add-plist">添加</button>
                        </div>
                    </div>
                    <input style="display:none;" name="TPlace" id="TPlace" type="text">
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <input type="hidden" value="<?php echo $list['cid']; ?>" name="cid" />
                            <input type="hidden" value="update" name="action" />
                            <button class="btn btn-primary" type="submit">保存内容</button>
                            <a class="btn btn-danger" onClick="javascript :history.back(-1);" style="width:82px">取消</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- 遮罩提示 -->
    <script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <!-- 自定义js -->
    <script src="/static/tpl/js/content.js?v=1.0.0"></script>
    <!-- jQuery Validation plugin javascript-->
    <script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
    <!-- iCheck -->
    <script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
    <!-- 地址三级联动 -->
    <script src="/static/tpl/js/common/select-address.js"></script>
    <script type="text/javascript">  
        // 添加提货服务区域
        var h_tcity_id = ''; // 城市id 用于判断是否重复添加
        var t_num = 0; // 提货点索引
        $('body').on('click', '.add-tlist', function(event) {
            var pro_id = $('#tpro option:selected').val();
            var pro_text = $('#tpro option:selected').text();
            var city_id = $('#tcity option:selected').val();
            var city_text = $('#tcity option:selected').text();
            var tiprice = $("input[name=tihuoprice]").val();
            var tiaddprice = $("input[name=tihuodprice]").val();
            if(city_id == h_tcity_id){
                alert('请勿重复添加');
                return false;
            };
            h_tcity_id = city_id;

            var str = '<div class="row">'
                    + '<div class="col-sm-10">'
                    + '<input class="form-control" disabled="disabled" value="'+pro_text+city_text+' 提货费用：'+tiprice+' 单点提货费用：'+tiaddprice+'" type="text"/>'

                    + '<input type="hidden" name="pickinfo[' + t_num + '][province]" value="' + city_id + '" />'
                    + '<input type="hidden" name="pickinfo[' + t_num + '][price]" value="' + tiprice + '" />'
                    + '<input type="hidden" name="pickinfo[' + t_num + '][rate]" value="' + tiaddprice + '" />'

                    + '</div>'            
                    + '<div class="col-sm-2">'                
                    + '<button class="btn btn-danger pull-right del-tlist-new" data-id="" data-pid="" >删除</button>'
                    + '</div>'
                    + '</div>';
            // 追加
            t_num = t_num+1;
            $("#thlb").append(str);
            event.preventDefault();
            /* Act on the event */
        });
        // 删除页面原有的提货区域
        $('body').on('click', '.del-tlist', function(event) {
            var id = $(this).attr('data-id');
            var pid = $(this).attr('data-pid');
            var self = $(this);
            if(pid == '') return false;
            $.post("/admin/company/deladd",{ajax:1,addid:pid},function(result){
                console.log('======成功删除提货区域======');
                self.parents('.row').remove();
            }); 
            event.preventDefault();
            /* Act on the event */
        });
        // 删除页面新添加的提货区域
        $('body').on('click', '.del-tlist-new', function(event) {
            t_num = t_num-1;
            $(this).parents('.row').remove();
            event.preventDefault();
            /* Act on the event */
        });

        // 添加配送服务区域
        var h_pcity_id = ''; // 城市id 用于判断是否重复添加
        var p_num = 0; // 提货点索引
        $('body').on('click', '.add-plist', function(event) {
            var pro_id = $('#ppro option:selected').val();
            var pro_text = $('#ppro option:selected').text();
            var city_id = $('#pcity option:selected').val();
            var city_text = $('#pcity option:selected').text();
            var piprice = $("input[name=peisongprice]").val();
            var piaddprice = $("input[name=peidongdprice]").val();
            var ck_pro_id = $("#canprovince option:selected").val();
            var ck_pro_text = $("#canprovince option:selected").text();
            var ck_city_id = $("#cancity option:selected").val();
            var ck_city_text = $("#cancity option:selected").text();
            var ck_area_id = $("#canarea option:selected").val();
            var ck_area_text = $("#canarea option:selected").text();
            var ck_address = $("#canaddinfo").val();

            if(city_id == h_pcity_id){
                alert('请勿重复添加');
                return false;
            };
            h_pcity_id = ck_area_id;

            var str = '<div class="row">'
                    + '<div class="col-sm-12 col-lg-5">'        
                    + '<input disabled="disabled" value="'+pro_text+city_text+'（仓库地址：'+ck_pro_text+ck_city_text+ck_area_text+' ）" class=form-control type="text">'
                    + '</div>'
                    + '<div class="col-sm-12 col-lg-5">'
                    + '<input disabled="disabled" value="配送费用：'+piprice+' 单点配送费用：'+piaddprice+'" class="form-control" type="text">'

                    + '<input  type="hidden"  name="infos[' + p_num + '][province]" value="' + city_id + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][price]" value="' + piprice + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][rate]" value="' + piaddprice + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][provinceid]" value="' + ck_pro_id + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][cityid]" value="' + ck_city_id + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][areaid]" value="' + ck_area_id + '" />'
                    + '<input  type="hidden"  name="infos[' + p_num + '][address]" value="' + ck_address + '"  />'

                    + '</div>'           
                    + '<div class="col-sm-12 col-lg-2">'           
                    + '<button class="btn btn-danger del-plist-new" data-pid="">删除</button>'
                    + '</div>'     
                    + '</div>';
            // 追加
            p_num = p_num+1;
            $("#pslbb").append(str);
            event.preventDefault();
            /* Act on the event */
        });
        // 删除页面原有的配送区域
        $('body').on('click', '.del-plist', function(event) {
            var pid = $(this).attr('data-pid');
            if(pid == '') return false;
            var self = $(this);
            $.post("/admin/company/deladd",{ajax:2,addid:pid},function(result){
                self.parents('.row').remove();
                console.log('======成功删除提货区域======');
            });
            event.preventDefault();
            /* Act on the event */
        });
        // 删除页面新添加的配送区域
        $('body').on('click', '.del-plist-new', function(event) {
            $(this).parents('.row').remove();
            event.preventDefault();
            /* Act on the event */
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
            $("#commentForm").validate({
                errorElement : 'span',
                errorClass : 'help-block',       
                rules : {},
                messages : {},
                //自定义错误消息放到哪里
                errorPlacement : function(error, element) {
                    element.next().remove();//删除显示图标
                    element.after('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
                    element.closest('.form-group').append(error);//显示错误消息提示
                },
                //给未通过验证的元素进行处理
                highlight : function(element) {
                    $(element).closest('.form-group').addClass('has-error has-feedback');
                },
                //验证通过的处理
                success : function(label) {
                    var el=label.closest('.form-group').find("input");
                    el.next().remove();//与errorPlacement相似
                    el.after('<span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>');
                    label.closest('.form-group').removeClass('has-error').addClass("has-feedback has-success");
                    label.remove();
                },
                // 数据提交
                submitHandler:function(form){
                    $.ajax({
                        url: '/admin/company/addmessage',
                        type: 'POST',
                        dataType: 'json',
                        data: $('#commentForm').serialize(),
                    })
                    .done(function(response) {                        
                        if(response.code){ // 提交成功
                            layer.msg(response.message);
                            parent.location.reload();
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
</body>
</html>
