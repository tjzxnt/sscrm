<?php

class ZTag{
	
	public function zxflv($media, $images, $width = 600, $height = 358){
		import("Common.php");
		$this->zxflv_media = $media;
		$this->zxflv_images = $images;
		$this->zxflv_height = $height;
		$this->zxflv_width = $width;
		$this->flvid = Common::randomkeys(3);
		$flv = $this->fetch($GLOBALS['spConfig']['view']['config']['template_dir'].'/system/zxflv.html');
		return $flv;
	}
}
?>