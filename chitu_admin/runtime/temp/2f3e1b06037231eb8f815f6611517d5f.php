<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\setting\menuindex.html";i:1519958842;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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



<body class="gray-bg">
    <div class="wrapper wrapper-content  animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5> 
                            <span>权限管理</span> 
                            <a href="/admin/setting/menuindex"><i class="fa fa-refresh"></i></a>
                        </h5>
                        <div class="ibox-tools">
                            <a class="dropdown-toggle"  href="javascript:;" onclick="add()" style="color:#000">
                                <i class="fa fa-wrench"></i> 添加权限
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        
                        
                        <div class="clients-list">
                            
                            <div class="tab-content">
                                <div id="tab-1" class="tab-pane active">
                                    <div class="full-height-scroll">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>菜单名</th>
                                                        <th>链接</th>
                                                        <th class="text-center">操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <!--<tr>
                                                    <td></td>
                                                    <th></th>
                                                    <td class="text-center">
                                                     <a href="/admin/advertisement/updatecate?id=" class="btn btn-info"><i class="fa fa-paste"></i>编辑</a>
                                                       
                                                    <a href="/admin/advertisement/delcate?id=" class="btn btn-info" ><i class="fa fa-paste"></i>删除</a>    
                                                    </td>
                                                    </tr>-->
                                                   <?php if(is_array($rule_data) || $rule_data instanceof \think\Collection || $rule_data instanceof \think\Paginator): if( count($rule_data)==0 ) : echo "" ;else: foreach($rule_data as $key=>$v): ?>
                                                    <tr>
                                                       
                                                        <td><?php echo $v['_name']; ?></td>
                                                        <td><?php echo $v['name']; ?></td>
                                                        <td> 
                                                            <a href="javascript:;" navId="<?php echo $v['id']; ?>" navType="<?php echo $v['ertype']; ?>" navName="<?php echo $v['name']; ?>" onclick="add_child(this)">添加子权限</a> 
                                                            | 
                                                            <a href="javascript:;" navId="<?php echo $v['id']; ?>" navName="<?php echo $v['name']; ?>" navTitle="<?php echo $v['title']; ?>" navType="<?php echo $v['ertype']; ?>" onclick="edit(this)">修改</a>
                                                             | 
                                                             <a href="javascript:if(confirm('确定删除？'))location='/admin/setting/delmenu?id=<?php echo $v['id']; ?>'">删除</a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                              
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
     <div class="modal fade" id="bjy-add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"> 
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                    <h4 class="modal-title" id="myModalLabel"> 添加权限</h4>
                </div>
                <div class="modal-body">
                    <form id="bjy-form" class="form-inline" action="/admin/setting/addmenu" method="post">
                    <input type="hidden" name="pid" value="0">
                    <input type="hidden" name="action" value="add">
                    <table class="table table-striped table-bordered table-hover table-condensed">
                        <tr>
                            <th width="12%">权限名：</th>
                            <td> 
                                <input class="input-medium" type="text" name="title">
                            </td>
                        </tr>
                        <tr>
                            <th width="12%">权限类型：</th>
                            <td> 
                                <select class="input-medium" id="ertype" name="ertype">
                                    <option value="1">总后台</option>
                                    <option value="2">承运商后台</option>
                                    <option value="3">项目客户后台</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>连接：</th>
                            <td> 
                                <input class="input-medium" type="text" name="name"> 输入模块/控制器/方法即可 例如 admin/nav/index</td>
                        </tr>
                        <!--<tr>
                            <th>图标：</th>
                            <td> <input class="input-medium" type="text" name="ico"> font-awesome图标 输入fa fa- 后边的即可</td>
                        </tr>-->
                        <tr>
                            <th></th>
                            <td> <input class="btn btn-success" type="submit" value="添加"></td>
                        </tr>
                    </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="bjy-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"> 
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                    <h4 class="modal-title" id="myModalLabel"> 修改权限</h4>
                </div>
                <div class="modal-body">
                    <form id="bjy-form" class="form-inline" action="/admin/setting/addmenu" method="post"> 
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="action" value="update">
                        <table class="table table-striped table-bordered table-hover table-condensed">
                            <tr>
                                <th width="12%">权限名：</th>
                                <td> <input class="input-medium" type="text" name="title"></td>
                            </tr>
                            <tr>
                                <th width="12%">权限类型：</th>
                                <td> 
                                    <select class="input-medium" id="ertype_edit" name="ertype">
                                        <option value="1">总后台</option>
                                        <option value="2">承运商后台</option>
                                        <option value="3">项目客户后台</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>连接：</th>
                                <td> <input class="input-medium" type="text" name="name"> 输入模块/控制器/方法即可 例如 Admin/Nav/index</td>
                            </tr>
                            <tr>
                                <th></th>
                                <td> <input class="btn btn-success" type="submit" value="修改"></td>
                                </tr>
                        </table>
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
    // 添加菜单
    function add(){
        $("input[name='name'],input[name='title']").val('');
        $("input[name='pid']").val(0);
        $('#bjy-add').modal('show');
    }
     // 添加子菜单
    function add_child(obj){
        var navId=$(obj).attr('navId');
        var navType=$(obj).attr('navType');
        $("input[name='pid']").val(navId);
        $("#ertype").find("option[value="+navType+"]").attr("selected",true);
        $("input[name='name']").val('');
        $("input[name='title']").val('');
        $('#bjy-add').modal('show');
    }
    // 修改菜单
    function edit(obj){
        var navId=$(obj).attr('navId');
        var navName=$(obj).attr('navName');
        var navTitle=$(obj).attr('navTitle');
        var navType=$(obj).attr('navType');
        $("input[name='id']").val(navId);
        $("input[name='name']").val(navName);
        $("input[name='title']").val(navTitle);
        $("#ertype_edit").find("option[value="+navType+"]").attr("selected",true);
        $('#bjy-edit').modal('show');
    }
    </script>

   
    

</body>

</html>
