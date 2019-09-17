/**
 * Created by lyon 2017/3/9.
 * Notes 增加、更新干线班次
 */
$(function() {
    var maxELe = 25;
    var FieldCount = 1;
    $("#AddMoreFileBox").click(function() {
        var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;

       /* if(eleLeng == 1){
            $("#InputsWrapper").find("input").val("");
        }*/

        if(FieldCount < maxELe){
            FieldCount = FieldCount + 1;
            var ele = '<div class="zhongliangtext" style="height: 45px;"><label class="col-sm-2 col-xs-12 control-label zhongliangfanwei">重量范围：</label><span class="col-sm-10 col-xs-12" ><i class="col-sm-2 col-lg-3 col-xs-3"><input type="text" name="mytext1[]" class="inputos form-control row" id="field1_'+ FieldCount +'" /></i><p class="col-sm-2 col-lg-1 col-xs-3">KG 到</p><i class="col-sm-2 col-lg-3 col-xs-3"><input type="text" name="mytext2[]" class="inputos form-control" id="field2_'+ FieldCount +'" /></i><p class="col-sm-1 col-lg-1 col-xs-3">KG</p><p class="col-sm-2 col-lg-1 col-xs-3">价格</p><i class="col-sm-2 col-lg-2 col-xs-7"><input type="text" name="mytext3[]" class="inputos form-control" id="field3_'+ FieldCount +'" placeholder="请填写￥/KG" /></i><a href="#" class="removeclass col-sm-1 col-lg-1 col-xs-2">×</a></span></div>';
            $("#InputsWrapper").append(ele);
        }else{
            return false;
        }
    });

    $("body").on("click",".removeclass", function(e){
        var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
        if( eleLeng > 1 ) {
           $(this).parents('.zhongliangtext').remove();
            FieldCount--; //decrement textbox
        }
        return false;
    })

    $("#ResidualWeight").blur(function(){

        var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
        var val = $(this).val();

        if(eleLeng == 1){
            $("#field2_1").val(val);
        }
    });

    $("#Price").blur(function(){

        var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
        var val = $(this).val();

        if(eleLeng == 1){
            $("#field3_1").val(val);
        }
    });
});
