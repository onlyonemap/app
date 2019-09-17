/**
 * Created by ly on 2017/3/22.
 * Notes: 折线图
 * https: http://echarts.baidu.com/tutorial.html
 */
$(document).ready(function() {
    var d = new Date();
    var year = d.getFullYear();
    var month = d.getMonth() + 1;
    if(month.toString.length == 1){
        month = "0"+month;
    }
    var CustomersType = 1; // 类型 1 客户 2 承运商

    // 初始化图表1
    var chartOne = echarts.init(document.getElementById('chartOne'));
    // 初始化图表2
    var chartTwo = echarts.init(document.getElementById('chartTwo'));
    // 初始化图表3
    var chartFive = echarts.init(document.getElementById('chartFive'));
    // 初始化图表4
    var chartSix = echarts.init(document.getElementById('chartSix'));
    // 初始化图表5
    var chartThree = echarts.init(document.getElementById('chartThree'));
    // 初始化图表6
    var chartFour = echarts.init(document.getElementById('chartFour'));
    // 初始化图表7
   var chartSeven = echarts.init(document.getElementById('chartSeven'));

     // 1 获取注册客户、线下客户、撮合客户、总注册客户月数据
    var getCustomersMonthURL = '/admin/charts/ClientMonthNumber';
    // 1 获取注册客户、线下客户、撮合客户、总注册客户年数据
    var getCustomersYearURL = '/admin/charts/ClientYearNumber';

    // 2 获取承运商数量月数据接口
    var getCarrierMonthURL = '/admin/charts/carrierMonthNumber';
    // 2 获取承运商数量年数据接口
    var getCarrierYearURL = '/admin/charts/carrierYearNumber';

    // 检索客户
    var CustomersSearch = '/admin/charts/CustomersSearch';
    // 检索干线承运商
    var carrierSearch = '/admin/charts/carrierSearch';
    //检索提货承运商
    var pickSearch = '/admin/charts/pickSearch';
    // 检索客户班次
    var CustomersShiftSearch = '/admin/charts/CustomersShiftSearch';
    // 检索承运商班次
    var carrierShiftSearch = '/admin/charts/carrierShiftSearch';

    // 3客户年,默认显示当年货物重量最多的那位客户
    var PlatformGoodsYearWeight = '/admin/charts/PlatformGoodsYearWeight';
    // 3客户月数据（带对应检索条件）
    var PlatformGoodsMonthWeight = '/admin/charts/PlatformGoodsMonthWeight';
    // 3承运商年数据（具体承运商）
    var PlatformCarrierGoodsYearWeight = '/admin/charts/CarrierGoodsYearWeight';

    // 3承运商月数据 （带对应检索条件）
    var PlatformCarrierGoodsMonthWeight = '/admin/charts/CarrierGoodsMonthWeight';
    // 3提货承运商年数据（具体承运商）
    var PlatformPickGoodsYearWeight = '/admin/charts/PickGoodsYearWeight';
     // 3提货承运商月数据（具体承运商）
    var PlatformPickGoodsMonthWeight = '/admin/charts/PickGoodsMonthWeight';

    // 4客户年,默认显示当年货物总订单数
    var PlatformCustomersYearOrders = '/admin/charts/ClientYearOrders'; 
    // 4客户月订单数据（带对应检索条件）
    var PlatformCustomersMonthOrders = '/admin/charts/ClientMonthOrders';  
    // 4干线承运商订单年数据（具体承运商）
    var PlatformCarrierYearOrders = '/admin/charts/CarrierYearOrders'; 
    // 4干线承运商月订单数据 （带对应检索条件）
    var PlatformCarrierGoodsMonthOrders = '/admin/charts/CarrierMonthOrders'; 
     // 4提货承运商订单年数据（具体承运商）
    var PlatformPickCarrierYearOrders = '/admin/charts/PickCarrierYearOrders'; 
    // 4提货承运商月订单数据 （带对应检索条件）
    var PlatformPickCarrierGoodsMonthOrders = '/admin/charts/PickCarrierMonthOrders'; 

    // 5 获取营运干线数量月数据
    var getshiftMonthURL = '/admin/charts/shiftMonthNumber';
    // 5 获取营运干线数量年数据
    var getshiftYearURL = '/admin/charts/shiftYearNumber';
    // 6 客户托运货物分布图
    var CustomersGoodsDistri = '/admin/charts/distributionofgoods';   
    // 6 获取出发城市的ID
	var CustomersGoodsDistriCity = '/admin/charts/getcityiid';  	
    // 7 获取平台月度发货量前十接口
   var getPlatformMonthWeightURL = '/admin/charts/toptenstatistics';

    // 指定图表1的配置项和数据
    var optionOne = {
        title:{
            text: '客户数量',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['总客户','注册客户','线下客户','撮合客户']
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
                data : ['2016','2017','2018','2019']
            }
        ],
        yAxis : [
            {
                type : 'value',
                name : '数量'

            }
        ],
        series : [
            {
                name:'总客户',
                type:'line',
                data:[320, 332, 301, 334]
            },
            {
                name:'注册客户',
                type:'line',
                data:[390, 330, 320, 100]
            },
            {
                name:'线下客户',
                type:'line',
                data:[334, 390, 330, 320]
            },
            {
                name:'撮合客户',
                type:'line',
                data:[320, 100, 50, 20]
            }
        ]
    };
    // 指定图表2的配置项和数据
    var optionTwo = {
        title:{
            text: '承运商数量',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['承运商数量']
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
                name : '数量',
                type : 'value'
            }
        ],
        series : [
            {
                name:'承运商数量',
                type:'line',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            }
        ]
    };
    // 指定图表3的配置项和数据
    var optionFive = {
        title:{
            text: '平台承运货物重量',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['承运货重']
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
                name : '重量',
                type : 'value'
            }
        ],
        series : [
            {
                name:'承运货重',
                type:'line',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            }
        ]
    };
    // 指定图表4的配置项和数据
    var optionSix = {
        title:{
            text: '平台承运订单数',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['承运订单']
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
                name : '数量',
                type : 'value'
            }
        ],
        series : [
            {
                name:'承运订单',
                type:'line',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            }
        ]
    };
    // 指定图表5的配置项和数据
    var optionThree = {
        title:{
            text: '营运干线数量走势图',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['数量']
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
                name : '数量',
                type : 'value'
            }
        ],
        series : [
            {
                name:'数量',
                type:'line',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20, 300, 500]
            }
        ]
    };
    // 指定图表6的配置项和数据
    var optionFour = {
        title:{
            text: '托运货物分布图',
            top: '10',
            x: 'center'
        },
        legend: {
            orient: 'horizontal',
            top: '40',
            textStyle: {
                color: '#333'
            },
            data: ['北京','上海','郑州','广州','苏州','其他']
        },
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        series : [
            {
                name: '托运货物',
                type: 'pie',
                radius : '55%',
                center: ['50%', '60%'],
                data:[
                    {value:335, name:'北京'},
                    {value:310, name:'上海'},
                    {value:234, name:'郑州'},
                    {value:135, name:'广州'},
                    {value:150, name:'苏州'},
                    {value:250, name:'其他'}
                ],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ],
        color: ['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3']
    };
    // 指定图表7的配置项和数据
    var optionSeven = {
        title:{
            text: '平台月度发货量前十统计图',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['发货量']
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
                name: '1月',
                type : 'category',
                data : ['赤途','公司2','公司3','公司4','公司5','公司6','公司7','公司8','公司9','公司10']
            }
        ],
        yAxis : [
            {
                name: '发货量',
                type : 'value'
            }
        ],
        series : [
            {
                name:'发货量',
                type:'bar',
                data:[320, 332, 301, 334, 390, 330, 320, 100, 50, 20]
            }
        ]
    };

    // 1 获取注册客户、线下客户、撮合客户、总注册客户年数据
    function getCustomersYearData() {
        $.post(getCustomersYearURL,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            var arrD4 = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD1[index] = parseInt(value.count); //年份总注册客户
                arrD2[index] = parseInt(value.count0); //注册客户
                arrD3[index] = parseInt(value.count1); //线下客户
                arrD4[index] = parseInt(value.count2); //撮合客户
            });
            optionOne['xAxis'][0].data = arrX;
            optionOne['series'][0].data = arrD1;
            optionOne['series'][1].data = arrD2;
            optionOne['series'][2].data = arrD3;
            optionOne['series'][3].data = arrD4;
            chartOne.setOption(optionOne,{
                notMerge: true
            });
        })
    }
    // 1 获取注册客户、线下客户、撮合客户、总注册客户月数据
    function getCustomersMonthData(data) {
        $.post(getCustomersMonthURL,{"year":data},function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            var arrD4 = new  Array();

            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD1[index] = parseInt(value.count); //月份总注册客户
                arrD2[index] = parseInt(value.count0); //注册客户
                arrD3[index] = parseInt(value.count1); //线下客户
                arrD4[index] = parseInt(value.count2); //撮合客户
            });
            optionOne['xAxis'][0].data = arrX;
            optionOne['series'][0].data = arrD1;
            optionOne['series'][1].data = arrD2;
            optionOne['series'][2].data = arrD3;
            optionOne['series'][3].data = arrD4;
            chartOne.setOption(optionOne,{
                notMerge: true
            });
        })
    }

   // 2 获取承运商数量年数据
    function getCarrierYearData() {
        $.post(getCarrierYearURL,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD[index] = parseInt(value.count);
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD;
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }
     // 2 获取承运商数量月数据
    function getCarrierMonthData(data) {
        $.post(getCarrierMonthURL,{"year":data},function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD[index] = parseInt(value.count);
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD;
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }

    // 获取客户ID
    function getCustomersData(url,data,dom){
        $.get(url,{"term":data},function(response){
            var resObj = JSON.parse(response);
            console.log(resObj);
            var id = resObj[0].id;
            dom.attr("data-id",id);

            var type = dom.parents(".ibox-content").find(".customersType").val();
            type = parseInt(type);
            var ele = dom.parents(".ibox-content").find(".routes");
            if(type == 1){
                getCustomersShiftData(CustomersShiftSearch,id,ele);
            }else if(type == 2){
                getCustomersShiftData(carrierShiftSearch,id,ele);
            }
        });
    }
    // 获取线路
    function getCustomersShiftData(url,id,ele) {
        $.post(url, {"id":id}, function(response) {
            var resObj = JSON.parse(response);
            var optionEle = '<option value="">---选填线路---</option>';
            $.each(resObj,function(index,value){
                optionEle += '<option value="'+value.shiftID+'">'+value.shiftName+'</option>';
            });
            ele.html(optionEle);
        });
    }
    // 3获取平台货重年数据
    function getPlatformGoodsYearWeight(url,CustomerID, line){
        var data = {"CustomerID":CustomerID, "lineID":line};
        $.post(url,data,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD[index] = parseInt(value.weight);
            });
            optionFive['xAxis'][0].data = arrX;
            optionFive['series'][0].data = arrD;
            chartFive.setOption(optionFive,{
                notMerge: true
            });
            console.log(resObj);

        });
    }
    // 3获取平台货重月数据
    function getPlatformGoodsMonthWeight(url,CustomerID,  line, year){
        var data = {"CustomerID":CustomerID, "lineID":line, "year": year};
        $.post(url,data,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD[index] = parseInt(value.count);
            });
            optionFive['xAxis'][0].data = arrX;
            optionFive['series'][0].data = arrD;
            chartFive.setOption(optionFive,{
                notMerge: true
            });
            console.log(resObj);
        });
    }

    // 4获取平台订单数年数据
    function getPlatformCustomersYearOrders(url,CustomerID, type, line){
        var data = {"CustomerID":CustomerID,"coldType":type, "lineID":line};
        $.post(url,data,function(response){
            var resObj = JSON.parse(response);
            console.log(resObj);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD[index] = parseInt(value.Number);
            });
            optionSix['xAxis'][0].data = arrX;
            optionSix['series'][0].data = arrD;
            console.log(optionSix);
            chartSix.setOption(optionSix,{
                notMerge: true
            });
            console.log(resObj);

        });
    }
    // 4获取平台订单数月数据
    function getPlatformCustomersMonthOrders(url,CustomerID, type, line, year){
        var data = {"CustomerID":CustomerID,"coldType":type, "lineID":line, "year": year};
        $.post(url,data,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD[index] = parseInt(value.Number);
            });
            optionSix['xAxis'][0].data = arrX;
            optionSix['series'][0].data = arrD;
            chartSix.setOption(optionSix,{
                notMerge: true
            });
            console.log(resObj);
        });
    }

    // 5 获取营运干线年数据
    function getshiftYearData() {
        $.post(getshiftYearURL, function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD[index] = parseInt(value.count);
            });
            optionThree['xAxis'][0].data = arrX;
            optionThree['series'][0].data = arrD;
            chartThree.setOption(optionThree,{
                notMerge: true
            });
        })
    }
    // 5 获取营运干线月数据
    function getshiftMonthData(data) {
        $.post(getshiftMonthURL,{"year":data},function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD[index] = parseInt(value.count);
            });
            optionThree['xAxis'][0].data = arrX;
            optionThree['series'][0].data = arrD;
            chartThree.setOption(optionThree,{
                notMerge: true
            });
        })
    }
    
    // 6 获取城市
	function getCustomersGoodsDistriCity(CustomerID) {
		$.post(CustomersGoodsDistriCity,{"CustomerID":CustomerID},function(response) {
			var resObj = JSON.parse(response);
			console.log(resObj);
			var optionEle = '<option value="">---选填始发地---</option>';
			$.each(resObj,function(index,value){
                optionEle += '<option value="'+value.shifaID+'">'+value.shifaName+'</option>';
            });
            $("#startAddress").html(optionEle);
		});
	}
	// 6 获取托运货物数据
	function getCustomersGoodsData(CustomerID,city) {
		$.post(CustomersGoodsDistri,{"CustomerID":CustomerID,"shifaID":city},function(response){
            var resObj = JSON.parse(response);
            console.log(resObj);
            var arrX = new  Array();
            var arrD = new  Array();
            $.each(resObj,function(index,value){
                arrX[index] = {"name":value.CityName,"value":value.weight};
                arrD[index] = value.CityName;
            });
            optionFour['series'][0].data = arrX;
            optionFour['legend'].data = arrD;
            chartFour.setOption(optionFour,{
                notMerge: true
            });
        })
	}
    
    // 7 获取平台月度发货量前十数据
    function getPlatformMonthWeightData(year,month) {
        $.post(getPlatformMonthWeightURL,{"year":year,"month":month},function(response){
            var resObj = JSON.parse(response);
            if(resObj.length == 0){
                var xAxisName = month + '月';
            }else{
                var xAxisName = resObj[0].months + '月';
            }

            var arrX = new  Array();
            var arrD = new  Array();
            
            $.each(resObj,function(index,value){
                arrX[index] = value.customerName;
                arrD[index] = parseInt(value.weight);
            });
            optionSeven['xAxis'][0].name = xAxisName;
            optionSeven['xAxis'][0].data = arrX;
            optionSeven['series'][0].data = arrD;
            chartSeven.setOption(optionSeven,{
                notMerge: true
            });
        })
    }

    // 1 获取注册客户、线下客户、撮合客户、总注册客户月数据
    getCustomersYearData();
    // 2 获取承运商数量年数据
    getCarrierYearData();

    // 3 获取平台货重年数据
    getPlatformGoodsYearWeight(PlatformGoodsYearWeight);
    // 4 获取平台订单数年数据
    getPlatformCustomersYearOrders(PlatformCustomersYearOrders);

    // 5 获取营运干线年数据
    getshiftYearData();
    
    // 6 获取城市
	getCustomersGoodsDistriCity();
	// 6  托运货物数据
	getCustomersGoodsData();
    
    // 7 获取平台月度发货量前十数据
   getPlatformMonthWeightData(year,month);

    // 窗口改变时 图表 大小改变
    $(window).resize(function() {
        chartOne.resize({ width:'auto'});
        chartTwo.resize({ width:'auto'});
        chartThree.resize({ width:'auto'});
        chartFour.resize({ width:'auto'});
        chartFive.resize({ width:'auto'});
        chartSix.resize({ width:'auto'});
        chartSeven.resize({ width:'auto'});
    });

    // 图表1切换
    chartOne.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getCustomersYearData();
        }else{
            getCustomersMonthData(name);
        }
    });
    // 图表2切换
    chartTwo.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getCarrierYearData();
        }else{
            getCarrierMonthData(name);
        }
    });
    // 图表3切换
    chartFive.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        var d = this._dom.id;
        var customersType = $("#"+d).parents(".ibox-content").find(".customersType").val();
        customersType = parseInt(customersType);
        var CustomerID = $("#chartFive").parents(".ibox-content").find(".carrierSearch").attr("data-id");
        //var type = $("#chartFive").parents(".ibox-content").find(".type").val(); 
        var line = $("#chartFive").parents(".ibox-content").find(".routes").val();
        if(bool>=0){
            var url = PlatformGoodsYearWeight;
            if(customersType == 1){
                url = PlatformGoodsYearWeight;
            }else if(customersType == 2){
                url = PlatformCarrierGoodsYearWeight;
            }else if(customersType == 3){
                url = PlatformPickGoodsYearWeight;
                line='';
            }
            getPlatformGoodsYearWeight(url,CustomerID,line);
        }else{
            var url = PlatformGoodsMonthWeight;
            if(customersType == 1){
                url = PlatformGoodsMonthWeight;
            }else if(customersType == 2){
                url = PlatformCarrierGoodsMonthWeight;
            }else if(customersType == 3){
                url = PlatformPickGoodsMonthWeight;
                line = '';
            }
            getPlatformGoodsMonthWeight(url,CustomerID,line,name)
        }
    });
    // 图表4切换
    chartSix.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        var d = this._dom.id;
        var customersType = $("#"+d).parents(".ibox-content").find(".customersType").val();
        customersType = parseInt(customersType);
        var CustomerID = $("#"+d).parents(".ibox-content").find(".carrierSearch").attr("data-id");
        var type = $("#"+d).parents(".ibox-content").find(".type").val(); 
        var line = $("#"+d).parents(".ibox-content").find(".routes").val();
        if(bool>=0){
            var url = PlatformCustomersYearOrders;
            if(customersType == 1){
                url = PlatformCustomersYearOrders;
            }else if(customersType == 2){
                url = PlatformCarrierYearOrders;
            }else if(customersType == 3){
                url = PlatformPickCarrierYearOrders;
                line = '';
            }
            getPlatformCustomersYearOrders(url,CustomerID,type,line);
        }else{
            var url = PlatformCustomersMonthOrders;
            if(customersType == 1){
                url = PlatformCustomersMonthOrders;
            }else if(customersType == 2){
                url = PlatformCarrierGoodsMonthOrders;
            }else if(customersType == 3){
                url = PlatformPickCarrierGoodsMonthOrders;
                line = '';
            }
            getPlatformCustomersMonthOrders(url,CustomerID,type,line,name)
        }
    });
    // 图表5切换
    chartThree.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getshiftYearData();
        }else{
            getshiftMonthData(name)
        }
    });	
	
    // 判断选择的客户类型
    $(".customersType").change(function(){
        CustomersType = parseInt($(this).val());
        if(CustomersType == 1){
            $( ".carrierSearch" ).autocomplete({
                minLength: 1,
                source: CustomersSearch,
                select: function( event, ui ) {
                    var CustomersId = ui.item.id;
                    event.target.dataset.id = CustomersId;
                }
            });
        }else if(CustomersType == 2){
            $( ".carrierSearch" ).autocomplete({
                minLength: 1,
                source: carrierSearch,
                select: function( event, ui ) {
                    var CustomersId = ui.item.id;
                    event.target.dataset.id = CustomersId;
                }
            });
        }else if(CustomersType == 3){
            $( ".carrierSearch" ).autocomplete({
                minLength: 1,
                source: pickSearch,
                select: function( event, ui ) {
                    var CustomersId = ui.item.id;
                    event.target.dataset.id = CustomersId;
                }
            });
        }
    });
	
    // 获取用户Id
    $(".carrierSearch").blur(function() {
        var CustomersType = $(this).parents(".ibox-content").find(".customersType").val();
        CustomersType = parseInt(CustomersType);
        var term = $(this).val();
        var dom = $(this);
        if(CustomersType == 1){
            getCustomersData(CustomersSearch,term,dom);
        }else if(CustomersType == 2){
            getCustomersData(carrierSearch,term,dom);
        }else if(CustomersType == 3){

            getCustomersData(pickSearch,term,dom);
        }
    });
    $("#inputCustomers").blur(function() {
        var self = $(this);
        var term = self.val();
        $.get(CustomersSearch,{"term":term},function(response){
        	var resObj = JSON.parse(response);
        	var eleId = resObj[0].id;
        	self.attr("data-id",eleId);
            getCustomersGoodsDistriCity(eleId);
        });
    });
    $(".customersType").change(function(){
        var index =  $(this).val();
        if(index == 3){
            $(this).parents(".ibox-content").find(".routes").hide(); 
        }else{
            $(this).parents(".ibox-content").find(".routes").show(); 
        }
        
    })
    // 自动补全数据
    $(".carrierSearch").autocomplete({
        minLength: 1,
        source: CustomersSearch,
        select: function( event, ui ) {
            var CustomersId = ui.item.id;
            event.target.dataset.id = CustomersId;
            var EleId = event.target.id;
            var CustomersType = $("#"+EleId).parents(".ibox-content").find(".customersType").val();
            CustomersType = parseInt(CustomersType);
            
            var dom = $("#"+EleId).parents(".ibox-content").find(".routes");
            if(CustomersType == 1){
                getCustomersShiftData(CustomersShiftSearch,CustomersId,dom);
            }else if(CustomersType == 2){
                getCustomersShiftData(carrierShiftSearch,CustomersId,dom);
            }
            
        }
    });
	$("#inputCustomers").autocomplete({
        minLength: 1,
        source: CustomersSearch,
        select: function( event, ui ) {
            var CustomersId = ui.item.id;
            event.target.dataset.id = CustomersId;
            getCustomersGoodsDistriCity(CustomersId);
        }
    });

	
    // 图3点击生成图表
    $(".search-btn-weight").click(function() {
        var customersType = $(this).parents(".ibox-content").find(".customersType").val();
        customersType = parseInt(customersType);
        var CustomerID = $(this).parents(".ibox-content").find(".carrierSearch").attr("data-id");
       // var type = $(this).parents(".ibox-content").find(".type").val(); 
        var line = $(this).parents(".ibox-content").find(".routes").val();
        var url = PlatformGoodsYearWeight;
        if(customersType == 1){
            url = PlatformGoodsYearWeight;
        }else if(customersType == 2){
            url = PlatformCarrierGoodsYearWeight;
        }else if(customersType == 3){
            url = PlatformPickGoodsYearWeight;
            line='';
        }

        getPlatformGoodsYearWeight(url,CustomerID,line);
    });
    // 点击生成图表
    $(".search-btn-order").click(function() {
        var customersType = $(this).parents(".ibox-content").find(".customersType").val();
        customersType = parseInt(customersType);
        var CustomerID = $(this).parents(".ibox-content").find(".carrierSearch").attr("data-id");
        var type = $(this).parents(".ibox-content").find(".type").val(); 
        var line = $(this).parents(".ibox-content").find(".routes").val();
        var url = PlatformCustomersYearOrders;
        if(customersType == 1){
            url = PlatformCustomersYearOrders;
        }else if(customersType == 2){
            url = PlatformCarrierYearOrders;
        }else if(customersType == 3){
            url = PlatformPickCarrierYearOrders;
            line='';
        }
        getPlatformCustomersYearOrders(url,CustomerID,type,line);
    });
	// 点击生成分布图
	$(".btn-chart").click(function() {
		var cityId = $("#startAddress").val();
		var CustomerID = $("#inputCustomers").attr("data-id");
		getCustomersGoodsData(CustomerID,cityId);
	});
    // 日期选择器配置
    $(".date-view-two").datepicker({
        language: "zh-CN",
        autoclose: true,//选中之后自动隐藏日期选择框
        clearBtn: true,//清除按钮
        todayBtn: true,//今日按钮
        maxViewMode: 1, // 视图模式的最大限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        minViewMode: 1, // 视图模式的最小限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        format: "yyyy-mm", //日期格式
    });
    // 日期改变时事件
    $(".date-view-two").datepicker().on('changeDate',function(e){
        var date = e.date;
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        if(month.toString.length == 1){
            month = "0"+month;
        }
        getPlatformMonthWeightData(year,month);
    });
    
});