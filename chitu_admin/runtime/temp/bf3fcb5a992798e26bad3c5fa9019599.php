<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:69:"D:\WWW\chitu_admin\public/../application/admin\view\auth\setting.html";i:1563342896;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.css">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="ibox">
        <div class="ibox-title">
            <h5>栏目列表</h5>
            <div class="ibox-tools">
                <a class="btn btn-primary radius" onclick="system_category_add('添加权限组','<?php echo url('admin/auth/addauth'); ?>')" href="javascript:;"><span class="glyphicon glyphicon-plus"></span> 添加栏目</a>
            </div>
        </div>
        <div class="ibox-content">
            <div class="row m-b-sm m-t-sm">
                <div class="col-md-1">
                    <a href="/admin/auth/setting" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                </div>
            </div>

            <table class="footable table table-stripped toggle-arrow-tiny" >
                <thead>
                <tr>
                    <th class="text-left" data-sortable="true"><input type="checkbox" name="" value=""></th>
                    <th class="text-left" data-sortable="true">ID</th>
                    <th class="text-left" data-sortable="true">用户组</th>
                    <th class="text-left" data-sortable="true">描述</th>
                    <th class="text-left" data-sortable="true">授权</th>
                    <th class="text-left" data-sortable="true">状态</th>
                    <th class="text-left" data-sortable="true">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td class="text-left"><input type="checkbox" name="" class="system" value="<?php echo $vo['qid']; ?>"></td>
                    <td class="text-left"><?php echo $vo['qid']; ?></td>
                    <td class="text-left"><?php echo $vo['title']; ?></td>
                    <td class="text-left"><?php echo $vo['describe']; ?></td>
                    <td class="text-left"><a href="<?php echo url('admin/auth/authxr'); ?>?qid=<?php echo $vo['qid']; ?>">授权访问</a></td>
                    <td class="text-left"><?php if($vo['status']==0): ?><span class="label label-success radius">正常</span>
                        <?php else: ?>
                        <span class="label label-defaunt radius">禁用</span><?php endif; ?></td>
                    <td class="text-left">
                        <?php if($vo['status']==0): ?>
                        <a style="text-decoration:none" onClick="updatestatus(1,'dd','qid',<?php echo $vo['qid']; ?>)" href="javascript:;" title="禁用"><button class="btn btn-danger">禁用</button></a>
                        <?php else: ?>
                        <a style="text-decoration:none" onClick="updatestatus(0,'dd','qid',<?php echo $vo['qid']; ?>)" href="javascript:;" title="启用"><button class="btn btn-primary">启用</button></a>
                        <?php endif; ?>
                        <a style="text-decoration:none" class="ml-5" onClick="product_edit('','<?php echo url('admin/auth/authedit'); ?>?qid=<?php echo $vo['qid']; ?>','<?php echo $vo['qid']; ?>')" href="javascript:;" title="修改">
                            <button class="btn btn-warning">修改</button>
                        </a>
                        <a style="text-decoration:none" class="ml-5" onClick="datadel('dd',<?php echo $vo['qid']; ?>)" href="javascript:;" title="删除">
                            <button class="btn btn-danger">删除</button>
                        </a>
                    </td>


                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                </tbody>
            </table>
            <?php echo $list->render(); ?>
        </div>
    </div>
</div>

</body>
</html>
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.js"></script>
<script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/layer.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/laypage.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js//h-ui.admin/js/H-ui.admin.js"></script>
<script>
    $(document).ready(function() {
        // 初始话表格
        $('.footable').bootstrapTable();
    });
    function system_category_add(title,url,w,h){
        layer_show(title,url,w,h);
    }
    function updatestatus(status,all,zhujian,id) {
        if(status == 0){
            var msg = '启用'
        }else if(status==1){
            var msg = '禁用';
        }
        layer.confirm('确认要'+msg+'吗？',function(index) {
            if (all == 'all') {
                var classArr = $('.quan');
                var str = ''
                $.each(classArr, function (key, value) {
                    if (classArr[key].checked) {
                        str += classArr[key].value + ','
                    }
                })
                var data = {
                    id: str,
                    status: status,
                    model: 'quanxian',
                    zhujian: zhujian,
                    msg:msg
                }
            } else if (all == 'dd') {
                var data = {
                    id: id + ',',
                    status: status,
                    model: 'quanxian',
                    zhujian: zhujian,
                    msg:msg
                }
            }
            $.ajax({
                url: '<?php echo url("admin/auth/updatestatus"); ?>',
                data: data,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    console.log(res)
                    if (res.result == 'success') {
                        layer.msg(res.msg, {icon: 1, time: 1200});
                        setTimeout(function () {
                            window.location.reload()
                        }, 2100)
                    } else if (res.result == 'error') {
                        layer.msg(res.msg, {icon: 2, time: 1200});
                    }
                }
            })
        })
    }
    function datadel(all,id) {
        layer.confirm('确认要删除吗？', function (index) {
            if (all == 'all') {
                var classArr = $('.quan');
                var str = ''
                $.each(classArr, function (key, value) {
                    if (classArr[key].checked) {
                        str += classArr[key].value + ','
                    }
                })
                var data = {
                    id: str,
                    model: ['quanxian']
                }
            } else if (all == 'dd') {
                var data = {
                    id: id + ',',
                    model: ['quanxian']
                }
            }
            $.ajax({
                url: '<?php echo url("admin/auth/delete"); ?>',
                data: data,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    if (res.result == 'success') {
                        layer.msg(res.msg, {icon: 1, time: 1200});
                        setTimeout(function () {
                            window.location.reload()
                        }, 2100)
                    } else if (res.result == 'error') {
                        layer.msg(res.msg, {icon: 2, time: 1200});
                    }
                }
            })
        })
    }
    function product_edit(title,url,id){
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }
</script>