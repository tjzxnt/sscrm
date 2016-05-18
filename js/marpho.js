// JavaScript Document
var r_w=20//快进长度
var r_w_s=10//快进步长
var dir=1
var speed=30
demo2.innerHTML=demo1.innerHTML
function Marquee(){//正常移动
	//alert(demo2.offsetWidth+"\n"+demo.scrollLeft)
	if (dir>0  && (demo2.offsetWidth-demo.scrollLeft)<=0) demo.scrollLeft=0
	if (dir<0 &&(demo.scrollLeft<=0)) demo.scrollLeft=demo2.offsetWidth
	demo.scrollLeft+=dir
	
	demo.onmouseover=function() {clearInterval(MyMar)}//暂停移动
	demo.onmouseout=function() {MyMar=setInterval(Marquee,speed)}//继续移动
}
function r_left(){if (dir=-1)dir=1}//换向左移
function r_right(){if (dir=1)dir=-1}//换向右移


var MyMar=setInterval(Marquee,speed)