


var nn=1;
var key=0;
var aF = new Array();
aF[0] = "progid:DXImageTransform.Microsoft.Barn(function=30, duration=1)";
aF[1] = "progid:DXImageTransform.Microsoft.Blinds(direction='down', duration=1)";
aF[2] = "progid:dximagetransform.microsoft.wipe(gradientsize=1.0,wipestyle=4, motion=forward, duration=2)";
aF[3] = "progid:DXImageTransform.Microsoft.CheckerBoard(duration=1, direction='left')";
aF[4] = "progid:DXImageTransform.Microsoft.Iris(duration=1)";
aF[5] = "progid:DXImageTransform.Microsoft.Slide(bands=20, duration=1)";
aF[6] = "progid:DXImageTransform.Microsoft.Spiral(duration=1, GridSizeX=25, GridSizeY=25)"; 
aF[7] = "progid:DXImageTransform.Microsoft.Strips(duration=1)";
aF[8] = "progid:DXImageTransform.Microsoft.Wheel(duration=1,spokes=10)";
aF[9] = "progid:DXImageTransform.Microsoft.Zigzag(duration=1, GridSizeX=25, GridSizeY=25)"; 
aF[10] = "progid:DXImageTransform.Microsoft.Fade(duration=1)";

function change_img()
{
fn = parseInt(Math.random()*aF.length);
document.getElementById("pic").style.filter = aF[fn];
if(key==0){key=1;}
else if(document.all)
{document.getElementById("pic").filters[0].Apply();document.getElementById("pic").filters[0].Play(duration=2);}
eval('document.getElementById("pic").src=img'+nn+'.src');
eval('document.getElementById("url").href=url'+nn+'.src');
for (var i=1;i<=counts;i++){document.getElementById("xxjdjj"+i).className='axx';}
document.getElementById("xxjdjj"+nn).className='bxx';
nn++;if(nn>counts){nn=1;}
tt=setTimeout('change_img()',4000);}
function changeimg(n){nn=n;window.clearInterval(tt);change_img();}
document.write('<style>');
document.write('.axx{padding:1px 7px;border-left:#cccccc 1px solid;}');
document.write('a.axx:link,a.axx:visited{text-decoration:none;color:#fff;line-height:12px;font:9px sans-serif;background-color:#666;}');
document.write('a.axx:active,a.axx:hover{text-decoration:none;color:#fff;line-height:12px;font:9px sans-serif;background-color:#999;}');
document.write('.bxx{padding:1px 7px;border-left:#cccccc 1px solid;}');
document.write('a.bxx:link,a.bxx:visited{text-decoration:none;color:#fff;line-height:12px;font:9px sans-serif;background-color:#D34600;}');
document.write('a.bxx:active,a.bxx:hover{text-decoration:none;color:#fff;line-height:12px;font:9px sans-serif;background-color:#D34600;}');
document.write('</style>');
document.write('<div style="width:'+widths+'px;height:'+heights+'px;overflow:hidden;text-overflow:clip;">');
document.write('<div><a id="url"><img id="pic" style="border:0px;filter:progid:dximagetransform.microsoft.wipe(gradientsize=1.0,wipestyle=4, motion=forward)" width='+widths+' height='+heights+' /></a></div>');
document.write('<div style="filter:alpha(style=1,opacity=10,finishOpacity=80);background: #888888;width:100%-2px;text-align:right;top:-12px;position:relative;margin:1px;height:12px;padding:0px;margin:0px;border:0px;">');
for(var i=1;i<counts+1;i++){document.write('<a href="javascript:changeimg('+i+');" id="xxjdjj'+i+'" class="axx" target="_self">'+i+'</a>');}
document.write('</div></div>');
change_img();
