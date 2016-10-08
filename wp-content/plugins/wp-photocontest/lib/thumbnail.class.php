<?php

  /************************************************************\
  *
  *    Basic Thumbnail Generator Copyright 2007 Derek Harvey
  *		 www.lotsofcode.com
  *
  *    This file is part of Basic Thumbnail Generator.
  *
  *    Basic Thumbnail Generator is free software; you can redistribute it and/or modify
  *    it under the terms of the GNU General Public License as published by
  *    the Free Software Foundation; either version 2 of the License, or
  *    (at your option) any later version.
  *
  *    Basic Thumbnail Generator is distributed in the hope that it will be useful,
  *    but WITHOUT ANY WARRANTY; without even the implied warranty of
  *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *    GNU General Public License for more details.
  *
  *    You should have received a copy of the GNU General Public License
  *    along with Basic Thumbnail Generator; if not, write to the Free Software
  *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  *
  *
  \************************************************************/

	class thumbnail
	{
		var $sourceFile; // We use this file to create the thumbnail
		var $originalFilename; // We use this to get the extension of the filename
		var $destinationDirectory; // The Directory in question
		var $destinationDirectoryFilename; // The destination filename
		
		var $createImageFunction = '';
		var $outputImageFunction = '';
		
		var $failed = false;
		var $ratio = false;
		
		function generate($sourceFile = "", $originalFilename = "", $destinationDirectory = "", $destinationDirectoryFilename = "", $width = -1, $height = -1, $crop = -1, $crop_width = -1, $crop_height = -1)
		{
			if (!empty($sourceFile))
				$this->sourceFile = $sourceFile;
			
			if (!empty($originalFilename))
				$this->originalFilename = $originalFilename;
			
			if (!empty($destinationDirectory))
				$this->destinationDirectory = $destinationDirectory;
			
			if (!empty($destinationDirectoryFilename))
				$this->destinationDirectoryFilename = $destinationDirectoryFilename;
			
			if (!empty($width))
				$this->width = $width;
			
			if (!empty($height))
				$this->height = $height;
			
			$extension_array = explode('.', $this->originalFilename);
			$this->extension = array_pop($extension_array);
				
			switch ($this->extension)
			{
				case 'gif' :
					$createImageFunction = 'imagecreatefromgif';
					$outputImageFunction = 'imagegif';
				  break;
				
				case 'png' : case 'PNG':
					$createImageFunction = 'imagecreatefrompng';
					$outputImageFunction = 'imagepng';
				  break;
				
				case 'bmp' : case 'BMP':
					$createImageFunction = 'imagecreatefromwbmp';
					$outputImageFunction = 'imagewbmp';
				  break;
				
				case 'jpg': case 'jpeg': case 'JPG': case 'JPEG':
					$createImageFunction = 'imagecreatefromjpeg';
					$outputImageFunction = 'imagejpeg';
				  break;
				
				default : 
					exit("Sorry: The format '{$this->extension}' is unsupported");
				  break;
			}
				
			$this->img  = $createImageFunction($this->sourceFile);
			
			list($this->org_width, $this->org_height) = getimagesize($this->sourceFile);
			
	
			if ($this->height == -1)
			{
				$this->height = round($this->org_height * $this->width / $this->org_width);
			}
			
			if ($this->width == -1)
			{
				$this->width = round($this->org_width * $this->height / $this->org_height);
			}	 
			
			$this->xoffset = 0;
			$this->yoffset = 0;
			
			$this->img_new = imagecreatetruecolor($this->width, $this->height);	
			
			if ($this->img_new)
			{
				imagecopyresampled($this->img_new, $this->img, 0, 0, $this->xoffset, $this->yoffset, $this->width, $this->height, $this->org_width, $this->org_height);
				
				list($this->newFilename) = explode('.', $this->destinationDirectoryFilename);
				
				$this->fullDestination = ($this->destinationDirectory.'/'.$this->newFilename.'.'.$this->extension);
				
				$outputImageFunction($this->img_new, $this->fullDestination);
				
				if ($crop > 0)
				{
					$this->img_new = $this->CroppedThumbnail($this->fullDestination,$crop_width,$crop_height,$createImageFunction);
					$outputImageFunction($this->img_new, $this->fullDestination);					
				}				
			}
			else
			{
				$this->failed = true;
			}
			
			if ($this->failed == false)
			{
				return $this->fullDestination;
			}			
		}
	
		function CroppedThumbnail($imgSrc,$thumbnail_width,$thumbnail_height,$createImageFunction) { //$imgSrc is a FILE - Returns an image resource.
			//getting the image dimensions 
			list($width_orig, $height_orig) = getimagesize($imgSrc);  

			$myImage = $createImageFunction($imgSrc);
			$ratio_orig = $width_orig/$height_orig;
		   
			if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
			   $new_height = $thumbnail_width/$ratio_orig;
			   $new_width = $thumbnail_width;
			} else {
			   $new_width = $thumbnail_height*$ratio_orig;
			   $new_height = $thumbnail_height;
			}
		   
			$x_mid = $new_width/2;  //horizontal middle
			$y_mid = $new_height/2; //vertical middle
		   
			$process = imagecreatetruecolor(round($new_width), round($new_height));
		   
			imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
			$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
			imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
		
			imagedestroy($process);
			imagedestroy($myImage);
			return $thumb;
		}
}	
?>
