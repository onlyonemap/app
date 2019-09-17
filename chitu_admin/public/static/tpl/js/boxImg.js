/**
 * Created by TF on 2018/1/15.
 */
var len = $("img[modal='rotate']").length;
var arrPic = new Array(); //定义一个数组
for (var i = 0; i < len; i++) {
    arrPic[i] = $("img[modal='rotate']").eq(i).prop("src"); //将所有img路径存储到数组中
}

$("img[modal='rotate']").each(function () {
    $(this).on("click", function () {
        //给body添加弹出层的html
        $("body").append("<div class=\"mask-layer\">" +
            "   <div class=\"mask-layer-black\"></div>" +
            "   <div class=\"mask-layer-container\">" +
            "       <div class=\"mask-layer-imgbox auto-img-center\"></div>" +
            "       <div class=\"mask-layer-container-operate\">" +
            "           <button class=\"mask-prev btn-default-styles lr_l\" style=\"float: left\"><img src=\"/static/tpl/img/left.png\"></button>" +
            "           <button class=\"mask-out btn-default-styles handle\"><img src=\"/static/tpl/img/enlarge.png\"></button>" +
            "           <button class=\"mask-in btn-default-styles handle\"><img src=\"/static/tpl/img/narrow.png\"></button>" +
            "           <button class=\"mask-clockwise btn-default-styles handle\"><img src=\"/static/tpl/img/clockwise.png\"></button>" +
            "           <button class=\"mask-counterclockwise btn-default-styles handle\"><img src=\"/static/tpl/img/anticlockwise.png\"></button>" +
            "           <button class=\"mask-close btn-default-styles close\"><img src=\"/static/tpl/img/close.png\"></button>" +
            "           <button class=\"mask-next btn-default-styles lr_r\" style=\"float: right\"><img src=\"/static/tpl/img/right.png\"></button>" +
            "       </div>" +
            "   </div>" +
            "</div>"
        );

        var $this = $(this);
        var img_index = $this.index(); //获取点击的索引值
        var num = img_index;

        function showImg() {
            $(".mask-layer-imgbox").append("<p><img src=\"\" alt=\"\"></p>");
            $(".mask-layer-imgbox img").prop("src", arrPic[num]); //给弹出框的Img赋值

            //图片居中显示
            var box_width = $(".auto-img-center").width(); //图片盒子宽度
            var box_height = $(".auto-img-center").height();//图片高度高度
            var initial_width = $(".auto-img-center img").width();//初始图片宽度
            var initial_height = $(".auto-img-center img").height();//初始图片高度
            if (initial_width > initial_height) {
                $(".auto-img-center img").css("width", "400px");
                var last_imgHeight = $(".auto-img-center img").height() / 2;
                $(".auto-img-center img").css("marginTop", "0px");
				$(".mask-layer-imgbox img").parent().css({
					"transformOrigin":"center "+last_imgHeight+"px",
					"-moz-transformOrigin":"center "+last_imgHeight+"px",
					"-o-transformOrigin":"center "+last_imgHeight+"px",
					"-weblit-transformOrigin":"center "+last_imgHeight+"px",
					"-ms-transformOrigin":"center "+last_imgHeight+"px"
				});
            } else {
                $(".auto-img-center img").css("height", "400px");
                var last_imgWidth = $(".auto-img-center img").width();
                $(".auto-img-center img").css("margin-left","0px");
				$(".mask-layer-imgbox img").parent().css({
					"transformOrigin":"center 200px",
					"-moz-transformOrigin":"center 200px",
					"-o-transformOrigin":"center 200px",
					"-weblit-transformOrigin":"center 200px",
					"-ms-transformOrigin":"center 200px"
				});
            }

            //图片拖拽
            var $div_img = $(".mask-layer-imgbox p");
            //绑定鼠标左键按住事件
            $div_img.bind("mousedown", function (event) {
                event.preventDefault && event.preventDefault(); //去掉图片拖动响应
                //获取需要拖动节点的坐标
                var offset_x = $(this)[0].offsetLeft;//x坐标
                var offset_y = $(this)[0].offsetTop;//y坐标
                //获取当前鼠标的坐标
                var mouse_x = event.pageX;
                var mouse_y = event.pageY;
                //绑定拖动事件
                //由于拖动时，可能鼠标会移出元素，所以应该使用全局（document）元素
                $(".mask-layer-imgbox").bind("mousemove", function (ev) {
                    // 计算鼠标移动了的位置
                    var _x = ev.pageX - mouse_x;
                    var _y = ev.pageY - mouse_y;
                    //设置移动后的元素坐标
                    var now_x = (offset_x + _x ) + "px";
                    var now_y = (offset_y + _y ) + "px";
                    //改变目标元素的位置
                    $div_img.css({
                        top: now_y,
                        left: now_x
                    });
                });
            });
            //当鼠标左键松开，接触事件绑定
            $(".mask-layer-imgbox").bind("mouseup", function () {
                $(this).unbind("mousemove");
            });

            //缩放
            var zoom_n = 1;
            $(".mask-out").click(function () {
                zoom_n += 0.5;
                $(".mask-layer-imgbox img").css({
                    "transform": "scale(" + zoom_n + ")",
                    "-moz-transform": "scale(" + zoom_n + ")",
                    "-ms-transform": "scale(" + zoom_n + ")",
                    "-o-transform": "scale(" + zoom_n + ")",
                    "-webkit-": "scale(" + zoom_n + ")"
                });
            });
            $(".mask-in").click(function () {
                zoom_n -= 0.5;
                console.log(zoom_n)
                if (zoom_n <= 0.5) {
                    zoom_n = 0.5;
                    $(".mask-layer-imgbox img").css({
                        "transform":"scale(.5)",
                        "-moz-transform":"scale(.5)",
                        "-ms-transform":"scale(.5)",
                        "-o-transform":"scale(.5)",
                        "-webkit-transform":"scale(.5)"
                    });
                } else {
                    $(".mask-layer-imgbox img").css({
                        "transform": "scale(" + zoom_n + ")",
                        "-moz-transform": "scale(" + zoom_n + ")",
                        "-ms-transform": "scale(" + zoom_n + ")",
                        "-o-transform": "scale(" + zoom_n + ")",
                        "-webkit-transform": "scale(" + zoom_n + ")"
                    });
                }
            });
			
			
			//旋转
			var spin_n = 0 ;  //记录长按旋转的角度
			var click_n = 0 ; //记录点击旋转的角度
			var round,time;   //round:定时调用   time：获取旋转按钮点击时事件
			function gettimeNow(){  
				var time = new Date();
				return time.getTime()
			}
			
			/*
			 * element；将要绑定事件的对象
			 * num_continue：小角度旋转量
			 * num_gap：  大角度旋转量
			 */
			function addRoundEvent(element,num_continue,num_gap){
				element.mousedown(function(){
					time = gettimeNow();     //获取开始时间
					var a = true;
					round = setInterval(function(){      //长按匀速旋转
						if(a){
							a= false;
							spin_n -= num_continue;
						}
						spin_n += num_continue;     
						$(".mask-layer-imgbox img").parent().css({
							"transform":"rotate("+ spin_n +"deg)",
							"-moz-transform":"rotate("+ spin_n +"deg)",
							"-ms-transform":"rotate("+ spin_n +"deg)",
							"-o-transform":"rotate("+ spin_n +"deg)",
							"-webkit-transform":"rotate("+ spin_n +"deg)"
						});
					},50);
				});
				element.mouseup(function(){
					var endtime = gettimeNow();   //获取结束时间
					if((endtime-time)<150){       //单次点击旋转按钮将会进行 大角度 旋转
						click_n += num_gap;
						$(".mask-layer-imgbox img").parent().css({
							"transform":"rotate("+ click_n +"deg)",
							"-moz-transform":"rotate("+ click_n +"deg)",
							"-ms-transform":"rotate("+ click_n +"deg)",
							"-o-transform":"rotate("+ click_n +"deg)",
							"-webkit-transform":"rotate("+ click_n +"deg)"
						});
						spin_n = click_n;         //大角度旋转后，将角度位置赋给匀速旋转
					}
					clearInterval(round);         //松开鼠标销毁定时调用
				});	
				element.mouseleave(function(){
					clearInterval(round);         //鼠标移出元素销毁定时调用
				});					
			}
			
			var elem1 = $(".mask-clockwise");
			addRoundEvent(elem1,1,90);
			var elem2 = $(".mask-counterclockwise");
			addRoundEvent(elem2,-1,-90);
			
			
			
			
			
            //关闭
            $(".mask-close").click(function () {
                $(".mask-layer").remove();
            });
            $(".mask-layer-black").click(function () {
                $(".mask-layer").remove();
            });
        }
        showImg();

        //下一张
        $(".mask-next").on("click", function () {
            $(".mask-layer-imgbox p img").remove();
            num++;
            if (num == len) {
                num = 0;
            }
            showImg();
        });
        //上一张
        $(".mask-prev").on("click", function () {
            $(".mask-layer-imgbox p img").remove();
            num--;
            if (num == -1) {
                num = len - 1;
            }
            showImg();
        });
    })
});