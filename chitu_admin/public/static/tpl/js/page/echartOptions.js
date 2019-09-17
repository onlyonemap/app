var options = {
	bar: { // 柱状图
		title: { // 标题
			show: false,
	        x: 'center',
	        text: 'ECharts例子个数统计',
	        subtext: 'Rainbow bar example',
	    },
	    toolbox: { // 工具箱
	        show: false,
	        feature: {
	            dataView: {show: true, readOnly: false},
	            restore: {show: true},
	            saveAsImage: {show: true}
	        }
	    },
	    tooltip: { // 提示框
	        trigger: 'item'
	    },
	    legend: {
	    	show: true,
	    	orient: 'horizontal', // 'vertical'
	        x: 'right', // 'center' | 'left' | {number},
	        y: 'top', // 'center' | 'bottom' | {number}
	        padding: 10,    // [5, 10, 15, 20]
	        itemGap: 20,
	        data: ['数据统计','数据统计1']
	    },
	    calculable: true,
	    grid: {
	        borderWidth: 0,
	        y: 80,
	        y2: 60
	    },
	    xAxis: [ // x轴
	        {
	            type: 'category',
	            show: true,
	            data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
	        }
	    ],
	    yAxis: [ // y轴
	        {
	            type: 'value',
	            show: true
	        }
	    ],
	    series: [
	        {
	            name: '数据统计',
	            type: 'bar',
	            data: [12,21,10,4,12,5,6,5,25,23,7,8]
	        },
	        {
	            name: '数据统计1',
	            type: 'bar',
	            data: [12,21,10,4,12,5,6,5,25,23,7,8]
	        }
	    ]
	},
	pie: { // 饼图
		title : {
			show: false,
	        text: '赤途冷链测试数据',
	        subtext: '纯属虚构',
	        x:'center'
	    },
	    tooltip : {
	        trigger: 'item',
	        formatter: "{a} <br/>{b} : {c} ({d}%)"
	    },
	    legend: {
	        orient : 'vertical',
	        x : 'right',
	        data:['直接访问','邮件营销','联盟广告','视频广告','搜索引擎']
	    },
	    calculable : true,
	    series : [
	        {
	            name:'订单数量',
	            type:'pie',
	            radius : '40%',
	            center: ['50%', '60%'],
	            data:[
	                {value:335, name:'直接访问'},
	                {value:310, name:'邮件营销'},
	                {value:234, name:'联盟广告'},
	                {value:135, name:'视频广告'},
	                {value:1548, name:'搜索引擎'}
	            ]
	        }
	    ]
	},
	pie2: { // 饼图
		title : {
			show: false,
	        text: '赤途冷链测试数据',
	        subtext: '纯属虚构',
	        x:'center'
	    },
	    tooltip : {
	        trigger: 'item',
	        formatter: "{a} <br/>{b} : {c} ({d}%)"
	    },
	    legend: {
	        orient : 'vertical',
	        x : 'right',
	        data:['直接访问','邮件营销','联盟广告','视频广告','搜索引擎']
	    },
	    calculable : true,
	    series : [
	        {
	            name:'订单数量',
	            type:'pie',
	            radius : '40%',
	            center: ['50%', '60%'],
	            data:[
	                {value:335, name:'直接访问'},
	                {value:310, name:'邮件营销'},
	                {value:234, name:'联盟广告'},
	                {value:135, name:'视频广告'},
	                {value:1548, name:'搜索引擎'}
	            ]
	        }
	    ]
	}
}