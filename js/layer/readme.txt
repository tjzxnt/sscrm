地址:http://layer.layui.com/api.html#layer.full

本文档主要针对layer1.9及以上版本，如果您项目中使用的是1.9之前的版本，请前往1.8.5文档页。

我们提到的基础参数主要指调用方法时用到的配置项，如：layer.open({content: ''})layer.msg('', {time: 3})等，其中的content和time即是基础参数，以键值形式存在，基础参数可合理应用于任何层类型中，您不需要所有都去配置，大多数都是可选的。而其中的layer.open、layer.msg就是内置方法。但我们通常还会在别的js文件中扩展一些层，比如layer.prompt()即是我们的扩展方法，扩展的js文件默认不会加载，通常需进行layer.config({extend: 'extend/layer.ext.js'})后才可使用，而关于其中的extend下面也有讲解。
基础参数

type - 基本层类型

    类型：Number，默认：0

    layer提供了5种层类型。可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）。 若你采用layer.open({type: 1})方式调用，则type为必填项（信息框除外）
title - 标题

    类型：String/Array/Boolean，默认：'信息'

    title支持三种类型的值，若你传入的是普通的字符串，如title :'我是标题'，那么只会改变标题文本；若你还需要自定义标题区域样式，那么你可以title: ['文本', 'font-size:18px;']，数组第二项可以写任意css样式；如果你不想显示标题栏，你可以title: false
content - 内容

    类型：String/DOM/Array，默认：''

    content可传入的值是灵活多变的，不仅可以传入普通的html内容，还可以指定DOM，更可以随着type的不同而不同。譬如：

    /!*
     如果是页面层
     */
    layer.open({
      type: 1, 
      content: '传入任意的文本或html' //这里content是一个普通的String
    });
    layer.open({
      type: 1,
      content: $('#id') //这里content是一个DOM
    });
    //Ajax获取
    $.post('url', {}, function(str){
      layer.open({
        type: 1,
        content: str //注意，如果str是object，那么需要字符拼接。
      });
    });

    /!*
     如果是iframe层
     */
    layer.open({
      type: 2, 
      content: 'http://sentsin.com' //这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
    }); 

    /!*
     如果是用layer.open执行tips层
     */
    layer.open({
      type: 4,
      content: ['内容', '#id'] //数组第二项即吸附元素选择器或者DOM
    });        
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19
        20
        21
        22
        23
        24
        25
        26
        27
        28
        29
        30
        31
        32
        33
        34
        35
        36
        37
        38
        39
        40
        41
        42

    对应代码说明
    laycode - v1.1
skin - 样式类名

    类型：String，默认：''

    skin不仅允许你传入layer内置的样式class名，还可以传入您自定义的class名。这是一个很好的切入点，意味着你可以借助skin轻松完成不同的风格定制。目前layer内置的skin有：layui-layer-lanlayui-layer-molv，未来我们还会选择性地内置更多，但更推荐您自己来定义。以下是一个自定义风格的简单例子

            
    //单个使用
    layer.open({
      skin: 'demo-class'
    });

    //全局使用。即所有弹出层都默认采用，但是单个配置skin的优先级更高
    layer.config({
      skin: 'demo-class'
    })

    //CSS 
    body .demo-class .layui-layer-title{background:#c00; color:#fff; border: none;}
    body .demo-class .layui-layer-btn{border-top:1px solid #E9E7E7}
    body .demo-class .layui-layer-btn a{background:#333;}
    body .demo-class .layui-layer-btn .layui-layer-btn1{background:#999;}
    …
    加上body是为了保证优先级。你可以借助Chrome调试工具，定义更多样式控制层更多的区域。    
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19
        20
        21
        22
        23

    对应代码说明
    laycode - v1.1

    你也可以去查看layer皮肤制作说明
area - 宽高

    类型：String/Array，默认：'auto'

    在默认状态下，layer是宽高都自适应的，但当你只想定义宽度时，你可以area: '500px'，高度仍然是自适应的。当你宽高都要定义时，你可以area: ['500px', '300px']
offset - 坐标

    类型：String/Array，默认：'auto'

    默认垂直水平居中。但当你只想定义top时，你可以offset: '100px'。当您top、left都要定义时，你可以offset: ['100px', '200px']。除此之外，你还可以定义offset: 'rb'，表示右下角。其它的特殊坐标，你可以自己计算赋值。
icon - 图标。信息框和加载层的私有参数

    类型：Number，默认：-1（信息框）/0（加载层）

    信息框默认不显示图标。当你想显示图标时，默认皮肤可以传入0-6如果是加载层，可以传入0-2。如：

    //eg1
    layer.alert('酷毙了', {icon: 1});

    //eg2
    layer.msg('不开心。。', {icon: 5});

    //eg3
    layer.load(1); //风格1的加载
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11

    对应代码说明
    laycode - v1.1
btn - 按钮

    类型：String/Array，默认：'确认'

    信息框模式时，btn默认是一个确认按钮，其它层类型则默认不显示，加载层和tips层则无效。当您只想自定义一个按钮时，你可以btn: '我知道了'，当你要定义两个按钮时，你可以btn: ['yes', 'no']。当然，你也可以定义更多按钮，比如：btn: ['按钮1', '按钮2', '按钮3', …]，按钮1和按钮2的回调分别是yes和cancel，而从按钮3开始，则回调为btn3: function(){}，以此类推。如：

    //eg1       
    layer.confirm('纳尼？', {
      btn: ['按钮一', '按钮二', '按钮三'] //可以无限个按钮
      ,btn3: function(index, layero){
        //按钮【按钮三】的回调
      }
    }, function(index, layero){
      //按钮【按钮一】的回调
    }, function(index){
      //按钮【按钮二】的回调
    });

    //eg2
    layer.open({
      content: 'test'
      ,btn: ['按钮一', '按钮二', '按钮三']
      ,yes: function(index, layero){ //或者使用btn1
        //按钮【按钮一】的回调
      },cancel: function(index){ //或者使用btn2
        //按钮【按钮二】的回调
      },btn3: function(index, layero){
        //按钮【按钮三】的回调
      }
    });
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19
        20
        21
        22
        23
        24
        25
        26
        27
        28
        29
        30

    对应代码说明
    laycode - v1.1
closeBtn - 关闭按钮

    类型：String/Boolean，默认：1

    layer提供了两种风格的关闭按钮，可通过配置1和2来展示，如果不显示，则closeBtn: 0
shade - 遮罩

    类型：String/Array/Boolean，默认：0.3

    即弹层外区域。默认是0.3透明度的黑色背景（'#000'）。如果你想定义别的颜色，可以shade: [0.8, '#393D49']；如果你不想显示遮罩，可以shade: 0
shadeClose - 是否点击遮罩关闭

    类型：Boolean，默认：false

    如果你的shade是存在的，那么你可以设定shadeClose来控制点击弹层外区域关闭。
time - 自动关闭所需毫秒

    类型：Number，默认：0

    默认不会自动关闭。当你想自动关闭时，可以time: 5000，即代表5秒后自动关闭，注意单位是毫秒（1秒=1000毫秒）
id - 用于控制弹层唯一标识

    类型：String，默认：空字符

    设置该值后，不管是什么类型的层，都只允许同时弹出一个。一般用于页面层和iframe层模式
shift - 动画

    类型：Number，默认：0

    从1.9开始，我们的出场动画全部采用CSS3。这意味着除了ie6-9，其它所有浏览器都是支持的。目前shift可支持的动画类型有0-6
maxmin - 最大最小化。

    类型：Boolean，默认：false

    该参数值对type:1和type:2有效。默认不显示最大小化按钮。需要显示配置maxmin: true即可
fix - 固定

    类型：Boolean，默认：true

    即鼠标滚动时，层是否固定在可视区域。如果不想，设置fix: false即可
scrollbar - 是否允许浏览器出现滚动条

    类型：Boolean，默认：true

    默认允许浏览器滚动，如果设定scrollbar: false，则屏蔽
maxWidth - 最大宽度

    类型：，默认：360

    当area: 'auto'时，maxWidth的设定才有效。
zIndex - 层叠顺序

    类型：，默认：19891014（贤心生日 0.0）

    一般用于解决和其它组件的层叠冲突。
move - 触发拖动的元素

    类型：String/DOM/Boolean，默认：'.layui-layer-title'

    默认是触发标题区域拖拽。如果你想单独定义，指向元素的选择器或者DOM即可。如move: '.mine-move'。你还配置设定move: false来禁止拖拽
moveType - 拖拽风格

    类型：Number，默认：0

    默认的拖拽风格正如你所见到的，会有个过度的透明框。但是如果你不喜欢，你可以设定moveType: 1切换到传统的拖拽模式
moveOut - 是否允许拖拽到窗口外

    类型：Boolean，默认：false

    默认只能在窗口内拖拽，如果你想让拖到窗外，那么设定moveOut: true即可
moveEnd - 拖动完毕后的回调方法

    类型：Function，默认：null

    默认不会触发moveEnd，如果你需要，设定moveEnd: function(){}即可。
tips - tips方向和颜色

    类型：Number/Array，默认：2

    tips层的私有参数。支持上右下左四个方向，通过1-4进行方向设定。如tips: 3则表示在元素的下面出现。有时你还可能会定义一些颜色，可以设定tips: [1, '#c00']
tipsMore - 是否允许多个tips

    类型：Boolean，默认：false

    允许多个意味着不会销毁之前的tips层。通过tipsMore: true开启
success - 层弹出后的成功回调方法

    类型：Function，默认：null

    当你需要在层创建完毕时即执行一些语句，可以通过该回调。success会携带两个参数，分别是当前层DOM当前层索引。如：

    layer.open({
      content: '测试回调',
      success: function(layero, index){
        console.log(layero, index);
      }
    });        
            

        1
        2
        3
        4
        5
        6
        7
        8

    对应代码说明
    laycode - v1.1
yes - 确定按钮回调方法

    类型：Function，默认：null

    该回调携带两个参数，分别为当前层索引、当前层DOM对象。如：

    layer.open({
      content: '测试回调',
      yes: function(index, layero){
        //do something
        layer.close(index); //如果设定了yes回调，需进行手工关闭
      }
    });        
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10

    对应代码说明
    laycode - v1.1
cancel - 取消和关闭按钮触发的回调

    类型：Function，默认：null

    该回调同样只携带当前层索引一个参数，无需进行手工关闭。如果不想关闭，return false即可，如 cancel: function(index){ return false; } 则不会关闭；
end - 层销毁后触发的回调

    类型：Function，默认：null

    无论是确认还是取消，只要层被销毁了，end都会执行，不携带任何参数。
full/min/restore -分别代表最大化、最小化、还原 后触发的回调

    类型：Function，默认：null

    携带一个参数，即当前层DOM

内置方法

layer.config(options) - 初始化全局配置

    这是一个可以重要也可以不重要的方法，重要的是，它的权利真的很大，尤其是在模块化加载layer时，你会发现你必须要用到它。它不仅可以配置一些诸如路径、加载的模块，甚至还可以决定整个弹层的默认参数。而说它不重要，是因为多数情况下，你会发现，你似乎不是那么十分需要它。但你真的需要认识一下这位伙计。

    如果您是采用seajs或者requirejs加载layer，你需要执行该方法来完成初始化的配置。比如：

    layer.config({
      path: '/res/layer/' //layer.js所在的目录，可以是绝对目录，也可以是相对目录
    });
    //这样的话，layer就会去加载一些它所需要的配件，比如css等。  
    //当然，你即便不用seajs或者requirejs，也可以通过上述方式设定路径             
            

        1
        2
        3
        4
        5
        6
        7

    对应代码说明
    laycode - v1.1

    如果你是采用<script src="?a.js&layer.js">这种合并的方式引入layer，那么您需要在script标签上加一个自定义属性merge="true"。如：

    <script src="?a.js&layer.js" merge="true">

    这样的话，layer就不会去自动去获取路径，但你需要通过以下方式来完成初始化的配置
    layer.config({
      path: '/res/layer/' //layer.js所在的目录，可以是绝对目录，也可以是相对目录
    });
            

        1
        2
        3
        4
        5
        6
        7
        8

    对应代码说明
    laycode - v1.1

    但layer.config的作用远不止上述这样。它还可以配置layer的拓展模块，以及默认的基础参数，如：

    layer.config({
      extend: 'extend/layer.ext.js', //注意，目录是相对layer.js根目录。如果加载多个，则 [a.js, b.js, …]
      shift: 1, //默认动画风格
      skin: 'layui-layer-molv' //默认皮肤
      …
    });

    //除此之外，extend还允许你加载css。这对于layer的第三方皮肤有极大的帮助，如：
    layer.config({
      extend: [
        'extend/layer.ext.js',
        'skin/layer-ext-myskin/style.css' //layer-ext-myskin即是你拓展的皮肤文件
      ]
    });

    //扩展css的规范：您必须在你扩展中的css文件加上这段
    html #layui_layer_skinlayer-ext-myskinstylecss{display:none; position: absolute; width:1989px;}
    规则就是：html #layui_layer_skin{文件夹名}{文件名}css
    skin名以文件夹名为标准。
          
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19
        20
        21
        22
        23
        24
        25

    对应代码说明
    laycode - v1.1
layer.ready(path, callback) - 初始化就绪

    由于我们的layer内置了轻量级加载器，所以你根本不需要单独引入css等文件。但是加载总是需要过程的。当你在页面一打开就要执行弹层时，layer.ready()会是一个不错的帮手。它也可以做一些layer.config可以做的事，比如指向layer.js所在目录。但是如果你已经通过layer.config配置了path，你在使用layer.ready时，是不需要path的，如：

    //页面一打开就执行弹层
    layer.ready(function(){
      layer.msg('很高兴一开场就见到你');
    });      
            

        1
        2
        3
        4
        5
        6

    对应代码说明
    laycode - v1.1

    我是华丽的酱油：介绍完上面两位引导者，接下来我们真正的主角闪亮登场了。此处应有掌声 ^,^
layer.open(options) - 原始核心方法

    基本上是露脸率最高的方法，不管是使用哪种方式创建层，都是走layer.open()，创建任何类型的弹层都会返回一个当前层索引，上述的options即是基础参数，另外，该文档统一采用options作为基础参数的标识例子：

    var index = layer.open({
      content: 'test'
    });
    //拿到的index是一个重要的凭据，它是诸如layer.close(index)等方法的必传参数。       
            

        1
        2
        3
        4
        5
        6

    对应代码说明
    laycode - v1.1

    噢，请等等，上面这位主角的介绍篇幅怎么看怎么都觉得跟它的地位不符，作者在文档中只给了它如此可怜的一块地？？这是因为，它真的已经大众得不能再大众了，你真正需要了解的，是它的内部器官，即上面一大篇幅介绍的各种基础参数。 ←_←
layer.alert(content, options, yes) - 普通信息框

    它的弹出似乎显得有些高调，一般用于对用户造成比较强烈的关注，类似系统alert，但却比alert更灵便。它的参数是自动向左补齐的。通过第二个参数，可以设定各种你所需要的基础参数，但如果你不需要的话，直接写回调即可。如

    //eg1
    layer.alert('只想简单的提示');        
    //eg2
    layer.alert('加了个图标', {icon: 1}); //这时如果你也还想执行yes回调，可以放在第三个参数中。
    //eg3
    layer.alert('有了回调', function(index){
      //do something
      
      layer.close(index);
    });       
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13

    对应代码说明
    laycode - v1.1
layer.confirm(content, options, yes, cancel) - 询问框

    类似系统confirm，但却远胜confirm，另外它不是和系统的confirm一样阻塞你需要把交互的语句放在回调体中。同样的，它的参数也是自动补齐的。

    //eg1
    layer.confirm('is not?', {icon: 3, title:'提示'}, function(index){
      //do something
      
      layer.close(index);
    });
    //eg2
    layer.confirm('is not?', function(index){
      //do something
      
      layer.close(index);
    });       
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16

    对应代码说明
    laycode - v1.1
layer.msg(content, options, end) - 提示框

    我在源码中有了相对较大的篇幅来定制了这个msg，目的是想将其打造成露脸率最高的提示框。而事实上我的确也在大量地使用它。因为它简单，而且足够得自觉，它不仅占据很少的面积，而且默认还会3秒后自动消失所有这一切都决定了我对msg的爱。因此我赋予了她许多可能在外形方面，它坚持简陋的变化，在作用方面，他坚持零用户操作。而且它的参数也是机会自动补齐的。

    //eg1
    layer.msg('只想弱弱提示');
    //eg2
    layer.msg('有表情地提示', {icon: 6}); 
    //eg3
    layer.msg('关闭后想做些什么', function(){
      //do something
    }); 
    //eg
    layer.msg('同上', {
      icon: 1,
      time: 2000 //2秒关闭（如果不配置，默认是3秒）
    }, function(){
      //do something
    });   
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19

    对应代码说明
    laycode - v1.1
layer.load(icon, options) - 加载层

    type:3的深度定制。load并不需要你传太多的参数，但如果你不喜欢默认的加载风格，你还有选择空间。icon支持传入0-2如果是0，无需传。另外特别注意一点：load默认是不会自动关闭的，因为你一般会在ajax回调体中关闭它。

    //eg1
    var index = layer.load();
    //eg2
    var index = layer.load(1); //换了种风格
    //eg3
    var index = layer.load(2, {time: 10*1000}); //又换了种风格，并且设定最长等待10秒 

    //关闭
    layer.close(index);     
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12

    对应代码说明
    laycode - v1.1
layer.tips(content, follow, options) - tips层

    type:4的深度定制。也是我本人比较喜欢的一个层类型，因为它拥有和msg一样的低调和自觉，而且会智能定位，即灵活地判断它应该出现在哪边。默认是在元素右边弹出

    //eg1
    layer.tips('只想提示地精准些', '#id');
    //eg 2
    $('#id').on('click', function(){
      var that = this;
      layer.tips('只想提示地精准些', that); //在元素的事件回调体中，follow直接赋予this即可
    });
    //eg 3
    layer.tips('在上面', '#id', {
      tips: 1
    });
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14

    对应代码说明
    laycode - v1.1

上面主要是一些弹层的调用方式，而下面介绍的是一些辅助性的方法
layer.close(index) - 关闭特定层

    关于它似乎没有太多介绍的必要，唯一让你疑惑的，可能就是这个index了吧
    事实上它非常容易得到。

    //当你想关闭当前页的某个层时
    var index = layer.open();
    var index = layer.alert();
    var index = layer.load();
    var index = layer.tips();
    //正如你看到的，每一种弹层调用方式，都会返回一个index
    layer.close(index); //此时你只需要把获得的index，轻轻地赋予layer.close即可

    //当你在iframe页面关闭自身时
    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
    parent.layer.close(index); //再执行关闭   
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14

    对应代码说明
    laycode - v1.1
layer.closeAll(type) - 关闭所有层

    如果你很懒，你不想去获取index你只想关闭。那么closeAll真的可以帮上你。如果你不指向层类型的话，它会销毁掉当前页所有的layer层。当然，如果你只想关闭某个类型的层，那么你可以

    layer.closeAll(); //疯狂模式，关闭所有层
    layer.closeAll('dialog'); //关闭信息框
    layer.closeAll('page'); //关闭所有页面层
    layer.closeAll('iframe'); //关闭所有的iframe层
    layer.closeAll('loading'); //关闭加载层
    layer.closeAll('tips'); //关闭所有的tips层    
            

        1
        2
        3
        4
        5
        6
        7
        8

    对应代码说明
    laycode - v1.1
layer.style(index, cssStyle) - 重新定义层的样式

    cssStyle允许你传入任意的css属性

    //重新给指定层设定width、top等
    layer.style(index, {
      width: '1000px',
      top: '10px'
    });       
            

        1
        2
        3
        4
        5
        6
        7

    对应代码说明
    laycode - v1.1
layer.title(title, index) - 改变层的标题

    使用方式：layer.title('标题变了', index)
layer.getChildFrame(selector, index) - 获取iframe页的DOM

    当你试图在当前页获取iframe页的DOM元素时，你可以用此方法。selector即iframe页的选择器

    layer.open({
      type: 2,
      content: 'test/iframe.html',
      success: function(layero, index){
        var body = layer.getChildFrame('body', index);
        var iframeWin = window[layero.find('iframe')[0]['name']]; //得到iframe页的窗口对象，执行iframe页的方法：iframeWin.method();
        console.log(body.html()) //得到iframe页的body内容
        body.find('input').val('Hi，我是从父页来的')
      }
    });       
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13

    对应代码说明
    laycode - v1.1
layer.getFrameIndex(windowName) - 获取特定iframe层的索引

    此方法一般用于在iframe页关闭自身时用到。

    //假设这是iframe页
    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
    parent.layer.close(index); //再执行关闭        
            

        1
        2
        3
        4
        5

    对应代码说明
    laycode - v1.1
layer.iframeAuto(index) - 指定iframe层自适应

    调用该方法时，iframe层的高度会重新进行适应
layer.iframeSrc(index, url) - //重置特定iframe url

    似乎不怎么常用的样子。使用方式：layer.iframeSrc(index, 'http://sentsin.com')
layer.setTop(layero) -置顶当前窗口

    非常强大的一个方法，虽然一般很少用。但是当你的页面有很多很多layer窗口，你需要像Window窗体那样，点击某个窗口，该窗体就置顶在上面，那么setTop可以来轻松实现。它采用巧妙的逻辑，以使这种置顶的性能达到最优

    //通过这种方式弹出的层，每当它被选择，就会置顶。
    layer.open({
      type: 2,
      shade: false,
      area: '500px',
      maxmin: true,
      content: 'http://www.layui.com',
      zIndex: layer.zIndex, //重点1
      success: function(layero){
        layer.setTop(layero); //重点2
      }
    });     
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16

    对应代码说明
    laycode - v1.1
layer.full()、layer.min()、layer.restore() - 手工执行最大小化

    （这三个酱油又一次被并列 ==。）一般用于在自定义元素上触发最大化、最小化和全屏。

扩展方法

请注意，扩展方法依赖于layer.ext.js，默认不会加载，必须进行以下配置才可使用：

layer.config({
  extend: 'extend/layer.ext.js'
});     
    

    1
    2
    3
    4
    5

对应代码说明
laycode - v1.1

layer.prompt(options, yes) - 输入层

    prompt的参数也是向前补齐的。options不仅可支持传入基础参数，还可以传入prompt专用的属性。当然，也可以不传。yes携带value 表单值index 索引elem 表单元素

    //prompt层新定制的成员如下
    {
      formType: 1, //输入框类型，支持0（文本）默认1（密码）2（多行文本）
      value: '', //初始时的值，默认空字符
      maxlength: 140, //可输入文本的最大长度，默认500
    }

    //例子1
    layer.prompt(function(value, index, elem){
      alert(value); //得到value
      layer.close(index);
    });
    //例子2
    layer.prompt({
      formType: 2,
      value: '初始值',
      title: '请输入值'
    }, function(value, index, elem){
      alert(value); //得到value
      layer.close(index);
    });
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17
        18
        19
        20
        21
        22
        23
        24
        25
        26

    对应代码说明
    laycode - v1.1
layer.tab(options) - tab层

    tab层只单独定制了一个成员，即tab: []，这个好像没有什么可介绍的，简单粗暴看例子

    layer.tab({
      area: ['600px', '300px'],
      tab: [{
        title: 'TAB1', 
        content: '内容1'
      }, {
        title: 'TAB2', 
        content: '内容2'
      }, {
        title: 'TAB3', 
        content: '内容3'
      }]
    });        
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16
        17

    对应代码说明
    laycode - v1.1
layer.photos(options) - 相册层

    相册层，也可以称之为图片查看器。它的出场动画从layer内置的动画类型中随机展现。photos支持传入json和直接读取页面图片两种方式。如果是json传入，如下：

    $.getJSON('/jquery/layer/test/photos.json', function(json){
      layer.photos({
        photos: json
      });
    }); 

    //而返回的json需严格按照如下格式：

    {
      "title": "", //相册标题
      "id": 123, //相册id
      "start": 0, //初始显示的图片序号，默认0
      "data": [   //相册包含的图片，数组格式
        {
          "alt": "图片名",
          "pid": 666, //图片id
          "src": "", //原图地址
          "thumb": "" //缩略图地址
        }
      ]
    }

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16

    对应代码说明
    laycode - v1.1

            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11

    对应代码说明
    laycode - v1.1

    如果是直接从页面中获取图片，那么需要指向图片的父容器，并且你的img可以设定一些规定的属性（但不是必须的）。

    //HTML示例
    <div id="layer-photos-demo" class="layer-photos-demo">
      <img layer-pid="图片id，可以不写" layer-src="大图地址" src="缩略图" alt="图片名">
      <img layer-pid="图片id，可以不写" layer-src="大图地址" src="缩略图" alt="图片名">
    </div>

    //调用示例
    layer.ready(function(){ //为了layer.ext.js加载完毕再执行
      layer.photos({
        photos: '#layer-photos-demo'
      });
    });   
            

        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        16

    对应代码说明
    laycode - v1.1

    看看一个实例呗：
    layer宣传图 我入互联网这五年 微摄影 三清山 国足 劲草

    第二种方式的图片查看器显然更加简单，因为无需像第一种那样返回规定的json，但是他们还是有各自的应用场景的，你可以按照你的需求进行选择。另外，photos还有个tab回调，切换图片时触发。

    layer.photos({
      photos: json/选择器,
      tab: function(pic, layero){
        console.log(pic) //当前图片的一些信息
      }
    });
            

        1
        2
        3
        4
        5
        6
        7
        8

    对应代码说明
    laycode - v1.1

合理地设定基础参数，合理地选择内置方法，合理地运用拓展方法，合理的心态，合理地阅读，只要一切都在合理的前提下，你才会感知到layer许许多多令人愉悦的地方，她真的是否如你所愿，取决于你对她了解的深远。愿layer能给你的web开发带来一段美妙的旅程。相信这一版的API文档，也会给你带来全新的便捷。别忘了在线调试。