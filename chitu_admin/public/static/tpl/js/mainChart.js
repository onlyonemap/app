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
      // 初始化图表1
    var chartTwo = echarts.init(document.getElementById('chartTwo'));
    // 1 获取用户、司机提现月数据
    var getCustomersMonthURL = '/admin/charts/countapplytotalmoth';
    // 1 获取用户、司机提现年数据
    var getCustomersYearURL = '/admin/charts/countapplytotalyear';
// 2 获取用户、司机充值月数据
    var getPayMonthURL = '/admin/charts/countpaytotalmoth';
    // 2 获取用户、司机充值年数据
    var getPayYearURL = '/admin/charts/countpaytotalyear';
 // 指定图表1的配置项和数据
    var optionOne = {
        title:{
            text: '提现总额',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['总额','司机','货主']
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
                name:'总额',
                type:'line',
                data:[320, 332, 301, 334]
            },
            {
                name:'司机',
                type:'line',
                data:[320, 332, 301, 334]
            },
            {
                name:'货主',
                type:'line',
                data:[390, 330, 320, 100]
            }
          
        ]
    };
// 指定图表2的配置项和数据
    var optionTwo = {
        title:{
            text: '充值总额',
            left: 'center'
        },
        legend: {
            top: '30',
            data:['总额','司机','货主']
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
                name:'总额',
                type:'line',
                data:[320, 332, 301, 334]
            },
            {
                name:'司机',
                type:'line',
                data:[320, 332, 301, 334]
            },
            {
                name:'货主',
                type:'line',
                data:[390, 330, 320, 100]
            }
          
        ]
    };
    // 1 获取总额、用户、司机年数据
    function getCustomersYearData() {
        $.post(getCustomersYearURL,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD1[index] = parseInt(value.count); //获取总额
                arrD2[index] = parseInt(value.count0); //用户司机
                arrD3[index] = parseInt(value.count1); //司机客户
                
            });
            optionOne['xAxis'][0].data = arrX;
            optionOne['series'][0].data = arrD1;
            optionOne['series'][1].data = arrD2;
            optionOne['series'][2].data = arrD3;
           
            chartOne.setOption(optionOne,{
                notMerge: true
            });
        })
    }
     // 1 获取总额、用户、司机月数据
    function getCustomersMonthData(data) {
        $.post(getCustomersMonthURL,{"year":data},function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            

            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD1[index] = parseInt(value.count); //月份总额
                arrD2[index] = parseInt(value.count0); //司机
                arrD3[index] = parseInt(value.count1); //用户
               
            });
            optionOne['xAxis'][0].data = arrX;
            optionOne['series'][0].data = arrD1;
            optionOne['series'][1].data = arrD2;
            optionOne['series'][2].data = arrD3;
           
            chartOne.setOption(optionOne,{
                notMerge: true
            });
        })
    }
    
   // 1 获取总额、用户、司机年数据
    function getPayYearData() {
        $.post(getPayYearURL,function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            
            $.each(resObj,function(index,value){
                arrX[index] = value.years;
                arrD1[index] = parseInt(value.count); //获取总额
                arrD2[index] = parseInt(value.count0); //用户司机
                arrD3[index] = parseInt(value.count1); //司机客户
                
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD1;
            optionTwo['series'][1].data = arrD2;
            optionTwo['series'][2].data = arrD3;
           
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }
     // 1 获取总额、用户、司机月数据
    function getPayMonthData(data) {
        $.post(getPayMonthURL,{"year":data},function(response){
            var resObj = JSON.parse(response);
            var arrX = new  Array();
            var arrD1 = new  Array();
            var arrD2 = new  Array();
            var arrD3 = new  Array();
            

            $.each(resObj,function(index,value){
                arrX[index] = value.months + '月';
                arrD1[index] = parseInt(value.count); //月份总额
                arrD2[index] = parseInt(value.count0); //司机
                arrD3[index] = parseInt(value.count1); //用户
               
            });
            optionTwo['xAxis'][0].data = arrX;
            optionTwo['series'][0].data = arrD1;
            optionTwo['series'][1].data = arrD2;
            optionTwo['series'][2].data = arrD3;
           
            chartTwo.setOption(optionTwo,{
                notMerge: true
            });
        })
    }
    
	
    
    

    // 1 获取注册司机、货主、总额提现月数据
    getCustomersYearData();
    // 1 获取注册司机、货主、总额提现年数据
    getPayYearData();
    // 窗口改变时 图表 大小改变
    $(window).resize(function() {
       
        chartOne.resize({ width:'auto'});
        chartTwo.resize({ width:'auto'});
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
    // 图表1切换
    chartTwo.on("click",function(event){
        var name = event.name;
        var bool = name.indexOf('月');
        if(bool>=0){
            getPayYearData();
        }else{
            getPayMonthData(name);
        }
    });
    
   
    
});