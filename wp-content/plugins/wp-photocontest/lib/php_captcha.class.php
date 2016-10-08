<?php
class phpCaptcha {

	var $RandomStr;
	var $ResultStr;
	var $NewImage;
	var $fileImagePath;
	var $baseimagecapthca;
	var $ttfFontsFile;
	
	function phpCaptcha(){
		// md5 to generate the random string
		$this->RandomStr = md5(microtime());
		//trim 5 digit
		$this->ResultStr = substr($this->RandomStr,0,5);
		$this->fileImagePath = "_c_".substr(md5(microtime()),0,7).".jpg";
		$this->baseimagecapthca = dirname(__FILE__)."/img.jpg";
		$this->ttfFontsFile = dirname(__FILE__)."/Impact.ttf";
	}
	
	function getResultStr(){
		return $this->ResultStr;
	}
	
	function create($path)
	{
		//image create by existing image and as back ground
		$this->NewImage =imagecreatefromjpeg($this->baseimagecapthca); 
		
		$LineColor = imagecolorallocate($this->NewImage,233,239,239);//line color 
		$LineColor2 = imagecolorallocate($this->NewImage,120,230,339);//line color 
		$LineColor3 = imagecolorallocate($this->NewImage,300,200,100);//line color 
		$TextColor = imagecolorallocate($this->NewImage, 255, 255, 255);//text color-white
		
		for ($i=0;$i<10;$i++){
			imageline($this->NewImage,rand(1,80),rand(1,100),rand(1,100),rand(1,100),$LineColor);//create line 1 on image 
			imageline($this->NewImage,rand(1,80),rand(1,100),rand(1,100),rand(1,100),$LineColor);//create line 2 on image 
			imageline($this->NewImage,rand(1,80),rand(1,100),rand(1,100),rand(1,100),$LineColor2);//create line 3 on image 
			imageline($this->NewImage,rand(1,80),rand(1,100),rand(1,100),rand(1,100),$LineColor3);//create line 3 on image 
		}
		//imageloadfont($this->bdfFontsFile);
		imagestring($this->NewImage, 5, 10, 5, $this->ResultStr, $TextColor);// Draw a random string horizontally 
		//imagefttext($this->NewImage, 12  , 30  , 10  , 5  , $TextColor  , $this->pdfFontsFile  , $this->ResultStr  );
		// out out the image
		//header("Content-type: image/jpeg"); 
		//Output image to browser
		imagejpeg($this->NewImage,dirname(__FILE__)."/../$path/".$this->fileImagePath);
		return "$path/".$this->fileImagePath;
	} 
}
?>
