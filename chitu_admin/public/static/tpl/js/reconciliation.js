/**
 * Created by DELL1 on 2017/2/16.
 */

$(document).ready(function(){
    // 重量,体积,提货费,干线费,配送费,总额
    var weight,volume,thPrice,gxPrice,psPrice,totalPrice,newPrice;
    /*
     * 输入框的 checked 或 disabled 状态改变触发
     * */
    $(".i-checks").on('ifChanged', function(event){
        var totalWeight = 0; // 总重
        var totalVolume = 0; // 总体积
        var totalthPrice = 0; // 总提货费
        var totalgxPrice = 0; // 总干线费
        var totalpsPrice = 0; // 总配送费
        var totaltPrice = 0; // 总额
        var totalnewPrice = 0; // 交易额

        var checkedEle = $(".check-list:checked"); // 选中的input
        var checkedLength = $(".check-list:checked").length; // 选中的input的个数
        for(var i=0; i<checkedLength; i++){
            weight = checkedEle.eq(i).parents("tr").find(".weight").text(); // 重量
            weight = parseFloat(weight);
            totalWeight = totalWeight + weight;
            volume = checkedEle.eq(i).parents("tr").find(".volume").text(); // 体积
            volume = parseFloat(volume);
            totalVolume = totalVolume + volume;
            thPrice = checkedEle.eq(i).parents("tr").find(".thPrice").text(); // 提货费
            thPrice = parseFloat(thPrice);
            totalthPrice = totalthPrice + thPrice;
            gxPrice = checkedEle.eq(i).parents("tr").find(".gxPrice").text(); // 干线费
            gxPrice = parseFloat(gxPrice);
            totalgxPrice = totalgxPrice + gxPrice;
            psPrice = checkedEle.eq(i).parents("tr").find(".psPrice").text(); // 配送费
            psPrice = parseFloat(psPrice);
            totalpsPrice = totalpsPrice + psPrice;
            totalPrice = checkedEle.eq(i).parents("tr").find(".totalPrice").text(); // 总额
            totalPrice = parseFloat(totalPrice);
            totaltPrice = totaltPrice + totalPrice;
            newPrice = checkedEle.eq(i).parents("tr").find(".newPrice").text(); // 交易额
            newPrice = parseFloat(newPrice);
            totalnewPrice = totalnewPrice + newPrice;
        }

        $("#totalWeight").text(totalWeight.toFixed(2));
        $("#totalVolume").text(totalVolume.toFixed(2));
        $("#totalthPrice").text(totalthPrice.toFixed(2));
        $("#totalgxPrice").text(totalgxPrice.toFixed(2));
        $("#totalpsPrice").text(totalpsPrice.toFixed(2));
        $("#totaltPrice").text(totaltPrice.toFixed(2));
        $("#totalnewPrice").text(totalnewPrice.toFixed(2));
    });

    // iCheck 插件 全选 选中
    $('#allCheck').on('ifChecked', function(event){
        $(".check-list:visible").iCheck('check');
    });
    // iCheck 插件 全选 不选中
    $('#allCheck').on('ifUnchecked', function(event){
        $(".check-list:visible").iCheck('uncheck');
    });

    // 搜索时全部选中
   /* var searchStatus = search;
    if(searchStatus == 2){
        $(".i-checks").iCheck('check');
    }*/

    // 点击分页,所有记录全部选中，全选默认checked
    // $(".pagination").click(function(){
    //     var checkedLeng = $(".check-list:visible").length;
    //     for(var i=0; i<checkedLeng; i++){
    //         var checkBool = $(".check-list:visible")[i].checked;
    //         if(checkBool){
    //             $("#allCheck").iCheck('check');
    //         }else{
    //             $("#allCheck").iCheck('uncheck');
    //             return false;
    //         }
    //     }
    // });
});
