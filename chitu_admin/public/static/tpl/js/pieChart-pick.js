/**
 * Created by ly on 2017/3/22.
 * Notes: 柱状图
 * https: http://echarts.baidu.com/tutorial.html
 */
$(document).ready(function() {
	var d = new Date();
    var year = d.getFullYear();
    var month = d.getMonth() + 1;
    if(month.toString.length == 1){
        month = "0"+month;
    }
    // 初始化图表2
    var chartTwo = echarts.init(document.getElementById('charttwo'));
    // 初始化图表3
    var chartThree = echarts.init(document.getElementById('chartThree'));
    
    // 1具体承运商年承接订单数量
    var percarrierYearNumbe = "/backstage/statistics/mentionorder";    
    // 1具体承运商月承接订单数量
    // var percarrierMonthNumber = '/admin/public/percarrierMonthNumber.php'; 
	var percarrierMonthNumber = '/backstage/statistics/mentionorderdel'; 
	// 2具体承运商年结订单重量
	var percarrierYearWeight = '/backstage/statistics/mentionweight';
	// 2具体承运商月接订单重量
    // var percarrierMonthWeight = '/admin/public/percarrierMonthWeight.php';
	var percarrierMonthWeight = '/backstage/statistics/mentionweightdel';

    // 指定图表2的配置项和数据
    var optionTwo = {
        title:{
            text: '承接订单数',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['订单数','总计']
        },
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            top: '100',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                data : ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'订单数',
                type:'bar',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            },
            {
                name:'总计',
                type:'bar',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 800]
            }
        ]
    };
    // 指定图表4的配置项和数据
    var optionThree = {
        title:{
            text: '承接重量',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['重量','总计']
        },
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            top: '100',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                data : ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'重量',
                type:'bar',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            },
            {
                name:'总计',
                type:'bar',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            }
        ]
    };
	
	// 1 获取承运商年承接订单数量
    function getPercarrierYearData() {
        $.post(percarrierYearNumbe,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD1[index] = parseInt(value.count); //年份总注册客户
                arrD2[index] = parseInt(value.count1); //注册客户
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD1;
            optionTwo['series'][1].data = arrD2;
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }
	// 1 获取承运商月承接订单数量
	function getPercarrierMonthData(year) {
		var data = {"year":year};
        $.post(percarrierMonthNumber,data,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months +"月";
                arrD1[index] = parseInt(value.count); //年份总注册客户
                arrD2[index] = parseInt(value.count0); //注册客户
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD1;
            optionTwo['series'][1].data = arrD2;
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }
	// 2 获取承运商年接订单重量
	function getcarrierYearWeightData() {
		$.post(percarrierYearWeight,function(response){
            var resObj = JSON.parse(response);
            
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD1[index] = parseInt(value.count); //年份总注册客户
                arrD2[index] = parseInt(value.count1); //注册客户
            });
            optionThree['xAxis'][0].data = arrX;
            optionThree['series'][0].data = arrD1;
            optionThree['series'][1].data = arrD2;
            chartThree.setOption(optionThree,{
                notMerge: true
            });
        })
	}
	// 2 获取承运商月接订单重量
	function getcarrierMonthWeightData(year) {
		var data = {"year":year};
        $.post(percarrierMonthWeight,data,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months +"月";
                arrD1[index] = parseInt(value.count); //年份总注册客户
                arrD2[index] = parseInt(value.count0); //注册客户
            });
            optionThree['xAxis'][0].data = arrX;
            optionThree['series'][0].data = arrD1;
            optionThree['series'][1].data = arrD2;
            chartThree.setOption(optionThree,{
                notMerge: true
            });
        })
	}

    // 1 获取承运商年承接订单数量
    getPercarrierYearData();
    // 2 获取承运商年接订单重量
    getcarrierYearWeightData();
    // 图表1切换
    chartTwo.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getPercarrierYearData();
        }else{
            getPercarrierMonthData(name);
        }
    });
    // 图表2切换
    chartThree.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getcarrierYearWeightData()
        }else{
            getcarrierMonthWeightData(name);
        }
    });
	// 生成图标3
	$(".btn-chart").click(function() {
		var year = $("#year").val();
		var month = $("#month").val();
		if(month.toString.length == 1){
	        month = "0"+month;
	    }
		getPercarriershiftYearWeight(year,month);
	});

    // 窗口改变时 图表 大小改变
    $(window).resize(function() {
        chartTwo.resize({ width:'auto'});
        chartThree.resize({ width:'auto'});
        chartFour.resize({ width:'auto'});
    });

    // 日期选择器配置
    $(".date-view-year").datepicker({
        language: "zh-CN",
        autoclose: true,//选中之后自动隐藏日期选择框
        clearBtn: true,//清除按钮
        todayBtn: true,//今日按钮
        maxViewMode: 2, // 视图模式的最大限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        minViewMode: 2, // 视图模式的最小限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        format: "yyyy", //日期格式
    });
    $(".date-view-month").datepicker({
        language: "zh-CN",
        autoclose: true,//选中之后自动隐藏日期选择框
        clearBtn: true,//清除按钮
        todayBtn: true,//今日按钮
        maxViewMode: 0, // 视图模式的最大限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        minViewMode: 1, // 视图模式的最小限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        format: "mm", //日期格式
    });

});