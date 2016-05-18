function VideoPlayer(id, model) {
    this.Width = 243;
    this.Height = 147;
    this.TextList = ["memo"];
    this.VideoList;
    this.PlayerUrl = "flv/vcastr2.swf";
    this.PlayerDemo = id;
    this.PlayModel = model;
}
VideoPlayer.prototype = {
    Play: function(n, url, width, height) {
        this.VideoList = [url];
        if (width != undefined)
            this.Width = width;
        if (height != undefined)
            this.Height = height;
        if (n >= this.VideoList.length) return;
        this.InitConfig();
        var _p = '\v' == 'v' ? '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" \
                        id="flvPlayer" width="{$Width}" height="{$Height}">\
                        <param name="movie" value="{$Player}">\
                        <param name="quality" value="high">\
                        <param name="menu" value="false">\
                        <param name=wmode value="opaque">\
                        <param name="FlashVars" value="vcastr_file={$File}&vcastr_title={$Text}&vcastr_config={$Config}">\
                      </object>' :
                      '<embed src="{$Player}" wmode="opaque" FlashVars="vcastr_file={$File}&vcastr_title={$Text}&vcastr_config={$Config}" menu="false" quality="high" \
                         width="{$Width}" height="{$Height}" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
        var _v = _p.replace(/\{\$Player\}/ig, this.PlayerUrl)
                        .replace(/\{\$Width\}/ig, this.Width)
                        .replace(/\{\$Height\}/ig, this.Height)
                        .replace(/\{\$Config\}/ig, this.Config)
                        .replace(/\{\$File\}/ig, this.GetList('VideoList', n));
        document.getElementById(this.PlayerDemo).innerHTML = _v;
    },
    InitConfig: function() {
        if (!this.PlayModel) {
            this.Config = "0:手动播放";
            this.PlayModel = true;
        } else {
            this.Config = "1:自动播放";
        }
        this.Config += "|1:连续播放|100:默认音量|0:控制栏位置|2:控制栏显示|0x000033:主体颜色|60:主体透明度|0x66ff00:光晕颜色|0xffffff:图标颜色|0xffffff:文字颜色|:logo文字|:logo地址|:结束swf地址";
    },
    GetList: function(type, n) {
        if (n == -1) return '';
        var l = new Array();
        for (var i = 0; i < this[type].length; i++) l.push(this[type][i]);
        var t = l[0]; l[0] = l[n]; l[n] = t;
        return l.toString().replace(/,/ig, "|");
    }
};
function SetTxtSel(n) {
    var links = document.getElementById('videoLinks').getElementsByTagName('a');
    for (var i = 0; i < links.length; i++) {
        links[i].className = i == n ? 'aorange' : '';
    }
}
var flv = new VideoPlayer("videoDemo", true);