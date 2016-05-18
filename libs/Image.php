<?php
 /**
 * filename:   Image.php
 * @author :   zy<zhangyan1984715@sina.com>
 * @copyright :Copyright 2008 
 * @license:   version 1.0
 * description:图片类，支持上传jpg|gif|png格式的图片上传，生成缩略图， 配合jquery的jquery.imgareaselect-0.5.1.js插件直接生成剪裁后的图片。
 */
class Image{

	 private $original_image;					//原始上传的图片
     private $thumb_image;                      //生成的缩略图
     private $file_mime;                         //原始图片的后缀名
     public  $width;							//原始图片的宽度
     public  $height;	                        //原始图片的高度        
     
    /**
	 * 构造函数 
	 * 作用：									   得到原始图片全路径及其宽度、高度扩展名
	 * 
	 * @param  string  $original_image_location    原始图的全路径
	 * @return boolean                             null
	*/
     
     public function open($original_image_location)
     {
     	$this->original_image = $original_image_location; 	
     	chmod($this->original_image, 0777);
		$this->width  = $this->getWidth($this->original_image);
		$this->height = $this->getHeight($this->original_image);
		$info = getimagesize($this->original_image);       
		$this->file_mime = image_type_to_mime_type($info[2]);
     }
          
     /**
	 * 按照要求大小，算出缩放的比例，并生成缩略图
	 *
	 * @param  string $thumb_image 生成缩略图的全路径
	 * @param  string $max_width   生成的缩略图的最大宽度
	 * @param  string $max_height  生成的缩略图的最大高度
	 * @return boolean             是否上传成功
	 */
	
	 public function thumbImage($thumb_image, $max_width, $max_height)
	 {  
		$scale = 1;
	 	if ($this->width > $max_width || $this->height > $max_height)
		{
			if(($this->width/$this->height) <= $max_width/$max_height)
			{
				$scale = $max_height/$this->height;
			}
			else
			{
				$scale = $max_width/$this->width;
			}
		}
		$newImageWidth = ceil($this->width * $scale);
		$newImageHeight = ceil($this->height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight); //返回一个图像标识符，代表了一幅大小为 x_size 和 y_size 的黑色图像。 
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			$source = imagecreatefromjpeg($this->original_image);//返回一图像标识符，代表了从给定的文件名取得的图像。 
		}
		else if ($this->file_mime == 'image/gif')
		{
			$source = imagecreatefromgif($this->original_image);
		}
		else
		{
			$source = imagecreatefrompng($this->original_image);		
		}
		imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$this->width,$this->height); //重采样拷贝部分图像并调整大小
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			imagejpeg($newImage,$thumb_image,90); //以 JPEG 格式将图像输出到浏览器或文件
		}
		else if ($this->file_mime == 'image/gif')
		{
			imagegif($newImage,$thumb_image); //以 gif 格式将图像输出到浏览器或文件
		}
		else 
		{
			imagepng($newImage,$thumb_image); //以 png 格式将图像输出到浏览器或文件			
		}
		chmod($thumb_image, 0777); //改变文件模式:0777代表每个人都能够读取、写入、和执行。
		return $thumb_image;
	 }
    
	
    /**
	 * 配合jquery的jquery.imgareaselect-0.5.1.js插件直接生成剪裁后的图片
	 * 
	 * @param  string  $thumb_image      要生成的剪裁后的图像
	 * @param  int     $thumb_width      要生成的剪裁后的图像的宽度
	 * @param  int     $width            从原图上裁剪后图片宽度
	 * @param  int     $heght            从原图上裁剪后图片高度
	 * @param  int     $start_width      从原图上裁剪开始x轴的位置
	 * @param  int     $start_height     从原图上裁剪开始y轴的位置
	 * @return boolean 					 是否生成成功
	*/
    
    function resizeThumbnailImage($thumb_image, $thumb_width, $width, $height, $start_x, $start_y)
    {  
    	$scale = $thumb_width / $width;
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			$source = imagecreatefromjpeg($this->original_image);
		}
		else if ($this->file_mime == 'image/gif')
		{
			$source = imagecreatefromgif($this->original_image);
		}
		else
		{
			$source = imagecreatefrompng($this->original_image);		
		}
		imagecopyresampled($newImage,$source,0,0,$start_x,$start_y,$newImageWidth,$newImageHeight,$width,$height);
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			imagejpeg($newImage,$thumb_image,90); //以 JPEG 格式将图像输出到浏览器或文件
		}
		else if ($this->file_mime == 'image/gif')
		{
			imagegif($newImage,$thumb_image); //以 gif 格式将图像输出到浏览器或文件
		}
		else
		{
			imagepng($newImage,$thumb_image); //以 png 格式将图像输出到浏览器或文件			
		}
		chmod($thumb_image, 0777);
		return $thumb_image;
    }
       
    /**
	 * 得到原始图像的高度
	 *
	 * @param  string image 原始图像
	 * @return int 			返回图像的高度
	*/
    
    function getHeight($image) 
    {
		$sizes = getimagesize($image);
		$height = $sizes[1];
		return $height;
    }

    
    /**
	 * 得到原始图像的宽度
	 *
	 * @param  string image 原始图像
	 * @return int 			返回图像的宽
	*/
    
	function getWidth($image) 
	{
		$sizes = getimagesize($image);
		$width = $sizes[0];
		return $width;
	}
	/**
	 * 生成带底色的缩略图
	 *
	 * @param array $file 文件数组
	 * @param string $newFile 新文件名
	 * @param int $width 缩略图宽度
	 * @param int $height 缩略图高度
	 * @param boolean $zoom 是否缩放
	 * @param boolean $big 是否放大
	 * @param string $bgColor 缩略图背景色 例:FF0000
	 * @param int $minwidth 需要放大的最小宽度
	 * @param int $minheight 需要放大最小高度
	 * @param int $maxwidth 放大的最大宽度
	 * @param int $maxheight 放大的最大宽度
	 * @param int $quality 缩略图质量 (1-100) gif图片无效
	 * @return boolean 是否成功
	 */
	public function resizeImage($thumb_image, $width, $height, $zoom=false, $reverseWH=0, $big=false, $minwidth=435, $minheight=300, $maxwidth=960, $maxheight=660, $bgColor = '000000', $quality = 90)
	{
		$newImage = imagecreatetruecolor($width, $height); //返回一个图像标识符，代表了一幅大小为 x_size 和 y_size 的黑色图像。 
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			$source = imagecreatefromjpeg($this->original_image);//返回一图像标识符，代表了从给定的文件名取得的图像。 
		}
		else if ($this->file_mime == 'image/gif')
		{
			$source = imagecreatefromgif($this->original_image);
		}
		else
		{
			$source = imagecreatefrompng($this->original_image);		
		}
	
		$yw = imagesx($source);
		$yh = imagesy($source);
		
		if($reverseWH) 
		{
			if (($width/$height >= 1 && $yw/$yh < 1) || ($width/$height < 1 && $yw/$yh >= 1) ) 
			{
				$width = $width / 2;
				$reverseWH = 2;
			}
		}
		
		if($zoom || ($yw > $width || $yh > $height) ) 
		{
			$bi = $yw > $yh ? $yw / $width : $yh / $height;
			$w = $yw/$bi;
			$h = $yh/$bi;
		}else if($yw > $yh && $big){
			if($yw < $minwidth){
				$bi = $maxwidth / $yw;
				$w = $maxwidth;
				$h = $yh * $bi;				
			}else if($yh < $minheight){
				$bi = $maxheight / $yh;
				$w = $yw * $bi;
				$h = $maxheight;				
			}else{
				$w = $yw;
				$h = $yh;
			}
		}
		else 
		{
			$w = $yw;
			$h = $yh;
		}
		
		
		$x = ($width-$w)/2;
		$y = ($height-$h)/2;

		$img = ImageCreateTrueColor($width, $height);
		$r = hexdec(substr($bgColor, 0, 2));
		$g = hexdec(substr($bgColor, 2, 2));
		$b = hexdec(substr($bgColor, 4, 2));
		$color = imagecolorallocate($img, $r, $g, $b);
		imagefilledrectangle($img, 0, 0, $width, $height, $color);
		imagecopyresampled($img, $source, $x, $y, 0, 0, $w, $h, $yw, $yh);
		if($this->file_mime == 'image/pjpeg' || $this->file_mime == 'image/jpeg')
		{
			imagejpeg($img, $thumb_image, $quality); //以 JPEG 格式将图像输出到浏览器或文件
		}
		else if ($this->file_mime == 'image/gif')
		{
			imagegif($img, $thumb_image); //以 gif 格式将图像输出到浏览器或文件
		}
		else 
		{
			imagepng($img, $thumb_image); //以 png 格式将图像输出到浏览器或文件			
		}
		chmod($thumb_image, 0777); //改变文件模式:0777代表每个人都能够读取、写入、和执行。
		return $thumb_image;
	}
	
	
	function addWaterMark($filename, $warterconfig)
	{
		if($warterconfig['allowWaterMark'] == 1)
		{
			//文字水印
			$result = $this->imageWaterMark($filename, 9, "", $warterconfig['waterMarkText'], 12, "#FFFFFF", $warterconfig['watertextroot']);  
		}
		else if($warterconfig['allowWaterMark'] == 2)
		{
			//图片水印   
			$result = $this->imageWaterMark($filename, 9, $warterconfig['waterMarkPic']); 
		}
		return $result;
	}
	
	/*    
	* 功能：PHP图片水印 (水印支持图片或文字)    
	* 参数：    
	*     $groundImage   背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式；    
	*     $waterPos     水印位置，有10种状态，0为随机位置；    
	*                 1为顶端居左，2为顶端居中，3为顶端居右；    
	*                 4为中部居左，5为中部居中，6为中部居右；    
	*                 7为底端居左，8为底端居中，9为底端居右；    
	*     $waterImage     图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；    
	*     $waterText     文字水印，即把文字作为为水印，支持ASCII码，中文需用UTF8编码；    
	*     $textFont     文字大小，像素点数；    
	*     $textColor     文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；    
	*    
	* 注意：Support GD 2.0，Support FreeType、GIF Read、GIF Create、JPG 、PNG    
	*     $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。    
	*     当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。      
	*/    
	function imageWaterMark($groundImage, $waterPos=0, $waterImage="", $waterText="", $textFont=12, $textColor="#FF0000", $fontfile="")
	{     
	  $isWaterImage = FALSE;     
	  $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";     
	
	  //读取水印文件     
	  if(!empty($waterImage) && file_exists($waterImage))     
	  {     
		$isWaterImage = TRUE;     
		$water_info = getimagesize($waterImage); 
		$water_w   = $water_info[0];//取得水印图片的宽
		$water_h   = $water_info[1];//取得水印图片的高
	
		switch($water_info[2])//取得水印图片的格式
		{     
			case 1:$water_im = imagecreatefromgif($waterImage);break;     
			case 2:$water_im = imagecreatefromjpeg($waterImage);break;     
			case 3:$water_im = imagecreatefrompng($waterImage);break;     
			default: return $formatMsg;     
		}
	  }     
	
	  //读取背景图片     
	  if(!empty($groundImage) && file_exists($groundImage))     
	  {     
		$ground_info = getimagesize($groundImage);     
		$ground_w   = $ground_info[0];//取得背景图片的宽     
		$ground_h   = $ground_info[1];//取得背景图片的高     
		switch($ground_info[2])//取得背景图片的格式     
		{     
			case 1:$ground_im = imagecreatefromgif($groundImage);break;     
			case 2:$ground_im = imagecreatefromjpeg($groundImage);break;     
			case 3:$ground_im = imagecreatefrompng($groundImage);break;     
			default:die($formatMsg);     
		}     
	  }     
	  else    
	  {     
		die("需要加水印的图片不存在！");     
	  }     
	  //水印位置     
	  if($isWaterImage)//图片水印     
	  {     
		$w = $water_w;     
		$h = $water_h;     
		$label = "图片的";     
	  }     
	  else//文字水印     
	  {     
		$temp = imagettfbbox($textFont,0, $fontfile, $waterText);//取得使用 TrueType 字体的文本的范围     
		$w = $temp[2] - $temp[6];     
		$h = $temp[3] - $temp[7];     
		unset($temp);     
		$label = "文字区域";     
	  }     
	  if( ($ground_w<$w) || ($ground_h<$h) )     
	  {     
		//echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";     
		return;     
	  }     
	  switch($waterPos)     
	  {     
		case 0://随机     
			$posX = rand(0,($ground_w - $w));     
			$posY = rand(0,($ground_h - $h));     
			break;     
		case 1://1为顶端居左     
			$posX = 0;     
			$posY = 0;     
			break;     
		case 2://2为顶端居中     
			$posX = ($ground_w - $w) / 2;     
			$posY = 0;     
			break;     
		case 3://3为顶端居右     
			$posX = $ground_w - $w;     
			$posY = 0;     
			break;     
		case 4://4为中部居左     
			$posX = 0;     
			$posY = ($ground_h - $h) / 2;     
			break;     
		case 5://5为中部居中     
			$posX = ($ground_w - $w) / 2;     
			$posY = ($ground_h - $h) / 2;     
			break;     
		case 6://6为中部居右     
			$posX = $ground_w - $w;     
			$posY = ($ground_h - $h) / 2;     
			break;     
		case 7://7为底端居左     
			$posX = 0;     
			$posY = $ground_h - $h;     
			break;     
		case 8://8为底端居中     
			$posX = ($ground_w - $w) / 2;     
			$posY = $ground_h - $h;     
			break;     
		case 9://9为底端居右     
			$posX = $ground_w - $w;     
			$posY = $ground_h - $h;     
			break;     
		default://随机     
			$posX = rand(0,($ground_w - $w));     
			$posY = rand(0,($ground_h - $h));     
			break;       
	  }     
	  //设定图像的混色模式     
	  imagealphablending($ground_im, true);     
	  if($isWaterImage)//图片水印     
	  {
	  	if($water_info[2] == 3){
	  		imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//拷贝水印到目标文件
	  	}else{
	  		imagecopymerge($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h, 50);
	  	}
	  }     
	  else//文字水印     
	  {     
		if( !empty($textColor) && (strlen($textColor)==7) )     
		{     
			$R = hexdec(substr($textColor,1,2));     
			$G = hexdec(substr($textColor,3,2));     
			$B = hexdec(substr($textColor,5));
		}     
		else    
		{     
			//die("水印文字颜色格式不正确！");
			$R = 0;
			$G = 0;
			$B = 0;		
		}     
		$color = imagecolorallocate($ground_im, $R, $G, $B);
		$color1 = imagecolorallocate($ground_im, 255-$R, 255-$G, 255-$B);
		imagettftext($ground_im, $textFont, 0, $posX+1, $posY+1, $color1, $fontfile , $waterText); 
		imagettftext($ground_im, $textFont, 0, $posX, $posY, $color, $fontfile , $waterText); 
	  }     
	  //生成水印后的图片     
	  @unlink($groundImage);     
	  switch($ground_info[2])//取得背景图片的格式     
	  {     
		case 1:imagegif($ground_im,$groundImage);break;     
		case 2:imagejpeg($ground_im,$groundImage);break;     
		case 3:imagepng($ground_im,$groundImage);break;     
		default:die($errorMsg);     
	  }     
	  //释放内存     
	  if(isset($water_info)) unset($water_info);     
	  if(isset($water_im)) imagedestroy($water_im);     
	  unset($ground_info);     
	  imagedestroy($ground_im);     
	}

}
?>

