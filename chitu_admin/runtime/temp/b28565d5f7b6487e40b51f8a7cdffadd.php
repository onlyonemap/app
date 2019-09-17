<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:68:"D:\WWW\chitu_admin\public/../application/admin\view\auth\authxr.html";i:1563881762;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


<style>
    p{
        padding: 0;
        padding-top:10px;
        margin: 0;
    }
    ul>.one{
        background-color: rgba(238,238,238,0.98);
        padding: 5px 15px;
    ;
    }
    ul li{
        display: inline-block;
    }
    .tosubmit{
        margin-left: 30%;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content  animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>
                        <span>用户组管理</span>
                        <a href="<?php echo url('admin/auth/authxr'); ?>?qid=<?php echo \think\Request::instance()->get('qid'); ?>"><i class="fa fa-refresh"></i></a>
                    </h5>
<!--                    <div class="ibox-tools">-->
<!--                        <a class="dropdown-toggle"  href="javascript:;" onclick="add()" style="color:#000">-->
<!--                            <i class="fa fa-wrench"></i> 添加用户组-->
<!--                        </a>-->
<!--                    </div>-->
                </div>
                <form id="quan" class="form-horizontal m-t">
                    <input type="hidden" value="<?php echo \think\Request::instance()->get('qid'); ?>" name="qid">
                    <?php if(is_array($system) || $system instanceof \think\Collection || $system instanceof \think\Paginator): $i = 0; $__LIST__ = $system;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <ul class=""><p class="one"><input type="checkbox" value="<?php echo $vo->name; ?>" <?php if(is_numeric(strpos($quanxianstr,$vo->name))): ?> checked="checked" <?php endif; ?> name="quanxian[]" onclick="xuanzhong(this,<?php echo $vo->id; ?>)"><?php echo $vo->title; ?></p>
                        <li><?php if($vo->getSon($vo->id)): if(is_array($vo->getSon($vo->id)) || $vo->getSon($vo->id) instanceof \think\Collection || $vo->getSon($vo->id) instanceof \think\Paginator): $i = 0; $__LIST__ = $vo->getSon($vo->id);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i;?>
                            <ul class="">
                                <p><input class="t" <?php if(is_numeric(strpos($quanxianstr,$voo->name))): ?>} checked="checked" <?php endif; ?>  type="checkbox" value="<?php echo $voo->name; ?>" name="quanxian[]" onclick="xuanzhong(this,<?php echo $vo->id; ?>)"><?php echo $voo->title; ?></p>
                                <?php if($voo->getSon($voo->id)): if(is_array($vo->getSon($voo->id)) || $vo->getSon($voo->id) instanceof \think\Collection || $vo->getSon($voo->id) instanceof \think\Paginator): $i = 0; $__LIST__ = $vo->getSon($voo->id);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vooo): $mod = ($i % 2 );++$i;?>
                                <ul style="display: inline-block" class="">
                                    <li><input class="t<?php echo $vo->id; ?> t<?php echo $voo->id; ?>" <?php if(is_numeric(strpos($quanxianstr,$vooo->name))): ?> checked="checked" <?php endif; ?> type="checkbox" value="<?php echo $vooo->name; ?>" name="quanxian[]"><?php echo $vooo->title; ?></li>
                                </ul>
                                <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </ul>
                            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </li>
                    </ul>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <button class="btn btn-primary tosubmit" type="button" value="" onclick="quanxianadd()">提交</button>
                </form>
            </div>
        </div>

    </div>
</div>


<!-- 全局js -->
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>

<script src="/static/tpl/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- 自定义js -->
<script src="/static/tpl/js/content.js?v=1.0.0"></script>
<script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
<script>
    function checkAll(obj){
        $(obj).parents('.b-group').eq(0).find("input[type='checkbox']").prop('checked', $(obj).prop('checked'));
    }

    function xuanzhong(hh,sid) {
        var clk = $('.t'+sid)
        if(hh.checked){
            $.each(clk,function (index,value) {
                clk.eq(index).prop('checked', true)
            })
        }else{
            $.each(clk,function (index,value) {
                clk.eq(index).prop('checked', false)
            })
        }
    }
    function quanxianadd() {
        $.ajax({
            url:"<?php echo url('admin/auth/tosaveauth'); ?>",
            data:$('#quan').serialize(),
            type:'post',
            dataType:'json',
            success: function (res) {
                console.log(res)
                if(res.result=='success'){
                    layer.msg(res.msg,{icon:1,time:1200});
                    setTimeout(function () {
                        window.location.href="<?php echo url('admin/auth/setting'); ?>"
                    },2100)
                }else if(res.result=='error'){
                    layer.msg(res.msg,{icon:2,time:1200});
                }
            }
        })
    }
</script>




</body>

</html>
