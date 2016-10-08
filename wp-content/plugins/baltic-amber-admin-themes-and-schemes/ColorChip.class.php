<?php
/**
ColorChip - A PHP class for manipulating color.

Copyright (C) 2002  Andy Chase
<achase@greyledge.net>
http://andy.greyledge.net

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/** A constant used to indicate that RGB color information is to be used by 
 *certain methods. */
define('CC_RGB', 'RGB');
/** A constant used to indicate that HSV color information is to be used by 
 *certain methods. */
define('CC_HSV', 'HSV');
/** A constant used to indicate that Hexadecimal color information is to be
 *used by certain methods. */
define('CC_HEX', 'HEX');
/** The CC_WEBSAFE_ALT constant is used by the getWebSafeDither and 
 *getNearestWebSafeComponent method. 
 *@see getWebSafeDither,getNearestWebSafeComponent 
 */
define('CC_WEBSAFE_ALT', 'WEBSAFE_ALT');

/**
 *ColorChip is a class for working with color in a more convenient manner 
 *than the basic RGB and Hexadecimal triplets often encountered in programming, 
 *especially web-oriented programming.  ColorChip allows you to define a color 
 *using RGB, HSV (Hue, Saturation, Value) or a Hexadecimal string.  ColorChip 
 *objects have properties for all three of these color models, as well as
 *methods for adjusting both RGB and HSV values - these methods automatically
 *update all of the object's properties.  Additional methods exist for getting
 *color compliments, triads, and the nearest websafe color.
 *@author Andy Chase <achase@greyledge.net> 
 *@link http://andy.greyledge.net
 *@copyright 2002
 */
class ColorChip {

  /** The object's Hue property.  Value can be 0-360, or boolean FALSE for
   * no hue (Grayscale) 
   *@var int
   */
  var $h; 
  /** The object's Saturation property.  Value can be 0-100.
   *@var int
   */
  var $s; 
  /** The object's Value (Brightness) property. Value can be 0-100. 
   *@var int
   */
  var $v; 
  /** The object's Red property.  Value can be 0-255. 
   *@var int
   */
  var $r; 
  /** The object's Green property.  Value can be 0-255. 
   *@var int
   */
  var $g; 
  /** The object's Blue property.  Value can be 0-255. 
   *@var int
   */
  var $b; 
  /** The object's Hex property, formatted as an HTML-style triplet; 
   *E.G., White would be stored as 'FFFFFF' 
   *@var string
   */
  var $hex; 

  /** The class constructor.  
   *Arguments one, two, and three are typically the R,G, and B values of 
   *the color chip being defined.  An optional fourth parameter indicates what
   *types of values are being passed.  Default is RGB, but HSV and Hexadecimal 
   *definitions are accepted too.
   *@return void
   *@param [int|string] $arg1 
   *@param [int|null] $arg2
   *@param [int|null] $arg3
   *@param string $type The type of color data being passed to the constructor.
   *This should be one of the constants CC_RGB, CC_HSV, or CC_HEX.  
   *Default is CC_RGB (Where $arg1 is R, $arg2 is G, and $arg3 is B.) 
   *If $type is set to CC_HEX, then $arg1 should contain the full hex triplet 
   *(EG 'FFFFFF'), while $arg2 and $arg3 can be null.
   *@access public
   */
  function ColorChip($arg1, $arg2, $arg3, $type = CC_RGB){
    switch($type){
    case CC_HSV:
      $this->h = $arg1;
      $this->s = $arg2;
      $this->v = $arg3;
      $this->_updateAllColorInfo(CC_HSV);
      break;

    case CC_RGB:
      $this->r = $arg1;
      $this->g = $arg2;
      $this->b = $arg3;
      $this->_updateAllColorInfo(CC_RGB);
      break;

    case CC_HEX:
      $this->hex = $arg1;
      $this->_updateAllColorInfo(CC_HEX);
      break;
    }

  }


  //-- Public Methods: -------------------------------------------------------------------------

  /** Adjusts the object's r property by $amount
   *@param int $amount The amount by which to adjust the object's red component.
   *@return void
   *@access public
   */
  function adjRed($amount){
    $this->r += round($amount);
    $this->_forceRange(0, 255, $this->r);
    $this->_updateAllColorInfo(CC_RGB);

  }

  /** Adjusts the object's r property by $amount
   *@param int $amount The amount by which to adjust the object's green component.
   *@return void
   *@access public
  */
  function adjGreen($amount){
    $this->g += round($amount);
    $this->_forceRange(0, 255, $this->g);
    $this->_updateAllColorInfo(CC_RGB);
  }

  /** Adjusts the object's b property by $amount
   *@param int $amount The amount by which to adjust the object's blue component.
   *@return void
   *@access public
  */
  function adjBlue($amount){
    $this->b += round($amount);
    $this->_forceRange(0, 255, $this->b);
    $this->_updateAllColorInfo(CC_RGB);
  }


  /** Adjusts the object's h property by $amount
   *@param int $amount The amount to adjust the object's hue. Values above
   *360 or below 0 will be wrapped around to the other end of the scale.  
   *@return void
   *@access public
  */
  function adjHue($amount){
    $this->h += round($amount);
    if($this->h < 0){
      $this->h += 360;
    }
    if($this->h > 360){
      $this->h -= 360;
    }

    $this->_updateAllColorInfo(CC_HSV);

  }
  /** Adjusts the object's s property by $amount
   *@param int $amount The amount by which to adjust the object's saturation.
   *@return void
   *@access public
  */
  function adjSaturation($amount){
    $this->s += round($amount);
    $this->_forceRange(0, 100, $this->s);

    $this->_updateAllColorInfo(CC_HSV);

  }
  /** Adjusts the object's v property by $amount
   *@param int $amount The amount by which to adjust the object's value.
   *@return void
   *@access public
  */
  function adjValue($amount){
    $this->v += round($amount);
    $this->_forceRange(0, 100, $this->v);
    $this->_updateAllColorInfo(CC_HSV);

  }

  /** Returns a new ColorChip object with identical h,s,v,r,g, b and 
   *hex properties.
   *@return ColorChip 
   *@access public
   */
  function clonenew() {
    $newColor = new ColorChip($this->r, $this->g, $this->b, CC_RGB);
    return $newColor;
  }



  /** Returns a new ColorChip object containing the complimentary color 
   *to the current
   *@return ColorChip The complimentary color
   *@access public
  */
  function getComplementary(){
    $nColor = $this->clonenew();
    $nColor->adjHue(180);
    return $nColor;
  }


  /** Returns an array containing two ColorChip objects which form 
   *a color triad with the original.
   *@return array An array containing two ColorChip objects forming a color triad with the original.
   *@access public
  */
  function getTriad(){
    $n1 = $this->clonenew();
    $n1->adjHue(-120);
    $n2 = $this->clonenew();
    $n2->adjHue(120);
    return array($n1, $n2);
  }


  /** Returns a new ColorChip object containing the nearest web safe color
   *to the original.
   *@return ColorChip A new ColorChip object with RGB properties set to the nearest web safe color.
   *@access public
  */

  function getNearestWebSafe(){
    $newCol = $this->clonenew();
    $newCol->r = ColorChip::getNearestWebSafeComponent($newCol->r);
    $newCol->g = ColorChip::getNearestWebSafeComponent($newCol->g);
    $newCol->b = ColorChip::getNearestWebSafeComponent($newCol->b);
    $newCol->_updateHsv();
    $newCol->_updateHex();
    return $newCol;
  }


  /** Takes a value from 0 - 255 and calculates the nearest web-safe value.  
   *If $which is set to CC_WEBSAFE_ALT, the second-closest web safe value will
   *be returned; this functionality is made available to calculate web-safe
   *dithering. $which is empty by default.
   *@param int $rgbComponent A value from 0-255 that you want to find the nearest web safe value of.
   *@param string $which Which websafe match to return; by default, the method returns the closest websafe match, but if $which is set to CC_WEBSAFE_ALT, the method will return the second closest match; this is useful when attempting to approximate a color by dithering two web-safe colors.
   *@return int The nearest websafe value to $rgbComponent (Will be one of: 0, 51, 102, 153, 204, 255)
   *@access public
   *@see getWebSafeDither
  */
  function getNearestWebSafeComponent($rgbComponent, $which = ''){
    $safe = array(0, .2, .4, .6, .8. 1);
    $pairs = array(
		   array(0, .2),
		   array(.2, .4),
		   array(.4, .6),
		   array(.6, .8),
		   array(.8, 1)
		   );
    $comp = $rgbComponent / 255; //Get RGB component percentage value
    
    if(!in_array($comp, $safe)){
      foreach($pairs as $pair){
	if($pair[0] < $comp && $comp < $pair[1]){ //If the component is between the current pair of websafe percentages:
	  if($comp - $pair[0] > $pair[1] - $comp){
	    if($which == CC_WEBSAFE_ALT){ //If the alternative websafe color is desired:
	      $comp = $pair[0];
	    }else{
	      //Component is closer to high end of web safe pair; set it to match
	      $comp = $pair[1];
	    }
	  }else{
	    if($which == CC_WEBSAFE_ALT){//If the alternative websafe color is desired:
	      $comp = $pair[1];
	    }else{
	      //Component is closer to low end of web safe pair; set it to match
	      $comp = $pair[0]; 
	    }
	  }
	}
      }
    }//Component is already web safe; leave it alone.

    return $comp * 255;
    
  }

  /** Returns an array containing the two web safe colors closest to the
   *actual color represented by the current ColorChip.
   *@return array An array containing the two ColorChip objects of the closest web safe colors to the original
   *@see RenderWebSafeDither
   *@access public
   */
  function getWebSafeDither(){
    $color1 = $this->getNearestWebSafe();
    $color2 = $this->clonenew();
    $color2->r = ColorChip::getNearestWebSafeComponent($color2->r, CC_WEBSAFE_ALT);
    $color2->g = ColorChip::getNearestWebSafeComponent($color2->g, CC_WEBSAFE_ALT);
    $color2->b = ColorChip::getNearestWebSafeComponent($color2->b, CC_WEBSAFE_ALT);
    $color2->_updateHsv();
    $color2->_updateHex();
    return array($color1, $color2);
  }



  /** Saves a 2 pixel by two pixel dither of the two nearest web-safe colors
   *in the filename specified. If no filename is given, the filename defaults 
   *to 'xxxxxx_websafe.png'; in other words, white would be saved as 
   *'ffffff_websafe.png'.  Requires that PHP be compiled with GD and PNG support.
   *@param string filename The name of the file to save the dither pattern as.
   *@return string $filename The name of the file the background was saved as.
   *@access public
  */
  function renderWebSafeDither($filename = ''){
    if($filename == ''){
      $filename = strtolower($this->hex) . '_websafe.png';
    }
    $safe = $this->getWebSafeDither();
    $im = imagecreate(2,2);

    $col1 = imagecolorallocate($im, $safe[0]->r, $safe[0]->g, $safe[0]->b);
    $col2 = imagecolorallocate($im, $safe[1]->r, $safe[1]->g, $safe[1]->b);
    imageSetPixel($im, 0, 0, $col1);
    imageSetPixel($im, 1, 1, $col1);
    imageSetPixel($im, 0, 1, $col2);
    imageSetPixel($im, 1, 0, $col2);
    imagepng($im, $filename);
    return $filename;
  }


  /** Sets new values for the ColorChip's hue, saturation, and value properties and
   *automatically updates the r,g,b and hex values accordingly.
   *@return void
   *@param int $newH The new hue
   *@param int $newS The new saturation
   *@param int $newV The new value (brightness)
   *@access public
 */
  function setHsv($newH, $newS, $newV){
    $this->h = $newH;
    $this->s = $newS;
    $this->v = $newV;
    $this->_updateAllColorInfo(CC_HSV);
  }

  /** Sets new values for the ColorChip's red, green, and blue properties and
   *automatically updates the h,s,v and hex values accordingly.
   *@return void
   *@param int $newR The new red
   *@param int $newG The new green
   *@param int $newB The new blue
   *@access public
  */
  function setRgb($newR, $newG, $newB){
    $this->r = $newR;
    $this->g = $newG;
    $this->b = $newB;
    $this->_updateAllColorInfo(CC_RGB);
  }

  /** Sets a new value for the ColorChip's hex property and automatically 
   *updates the h, s, v, r, g, and b values accordingly.
   *@return void
   *@param int $newHex
   *@access public
  */
  function setHex($newHex){
    $this->hex = $newHex;
    $this->_updateAllColorInfo(CC_HEX);
  }


  //-- Private Methods: ------------------------------------------------------------------------

  /** Forces $property to the range specified by $min and $max
   *@return void
   *@param int $min The minimum value for $property Default is 0.
   *@param int $max The maximum value for $property Default is 255.
   *@param int $property The property to force into range. Note that $property is passed by reference.
   *@access private
  */
  function _forceRange($min = 0, $max = 255, &$property){
    if($property < $min){
      $property = $min;
    }
    if($property > $max){
      $property = $max;
    }
  }


  /** Converts $number to hexadecimal, adding leading 0 if $number is less than 16
   *@return string
   *@param int $number The number to be converted to hexadecimal
   *@access private
 */ 
  function _toHex($number){
    $hex = '';
    if($number < 16){
      $hex = '0';
    }
    $hex .= strtoupper(base_convert($number, 10, 16));
    return $hex;
  }


  /** Updates all object color information based on the color value specified by
   *the $useInfo parameter.
   *@param string $useInfo
   *@access private
   */
  
  function _updateAllColorInfo($useInfo){
    switch($useInfo){
    case CC_HSV: //Update RGB and Hex values using the contents of h,s, and v
      $this->_updateRgb();
      $this->_updateHex();
      break;
      
    case CC_RGB: //Update HSV and Hex values using the contents of R,G, and B
      $this->_updateHex();
      $this->_updateHsv();
      break;
      
    case CC_HEX: //Update RGB and HSV values using the contents of Hex
      $this->_updateRgb(CC_HEX);
      $this->_updateHsv();
      break;
    }
  }


  /** Updates the object's r, g, and b properties. if $useInfo is CC_HSV, uses 
   *the object's HSV values, if $useInfo is CC_HEX, uses the object's hex property.
   *@return void
   *@access private
   */
  function _updateRgb($useInfo = CC_HSV){

    switch($useInfo){

    case CC_HSV:

      //Get decimal values (Between 0 and 1) for Hue and Saturation
 
      $value = $this->v / 100;
      $saturation = $this->s / 100;
      
      if($this->h == false){
	//Undefined hue means grey.  Adjust value to RGB 0-255 range and return
	$value = floor($value * 255);
	$this->r = $value;
	$this->g = $value;
	$this->b = $value;
      }else{

	$hue = $this->h / 60; //Get local value of hue, ranging from 1-6
	$i = floor($hue);
	$f = $hue - $i;
	if(!($i & 1)){ //(If $i is even)
	  $f = 1 - $f;
	}

	$m = $value * (1 - $saturation);
	$n = $value * (1 - $saturation * $f);


	//Adjust $value, $m, and $n to 0-255 scale

	$value = floor(255 * $value);
	$m = floor(255 * $m);
	$n = floor(255 * $n);

	switch($i){
	case 6: 
	case 0: 
	  $this->r = $value;
	  $this->g = $n;
	  $this->b = $m;
	  break;
	case 1: 
	  $this->r = $n;
	  $this->g = $value;
	  $this->b = $m;
	  break;
	case 2: 
	  $this->r = $m;
	  $this->g = $value;
	  $this->b = $n;
	  break;
	case 3:
	  $this->r = $m;
	  $this->g = $n;
	  $this->b = $value;
	  break;
	case 4:
	  $this->r = $n;
	  $this->g = $m;
	  $this->b = $value;
	  break;
	case 5: 
	  $this->r = $value;
	  $this->g = $m;
	  $this->b = $n;
	  break;
	}
      }
      
      break;

    case CC_HEX:

      $this->r = base_convert(substr($this->hex,0,2), 16, 10);
      $this->g = base_convert(substr($this->hex,2,2), 16, 10);
      $this->b = base_convert(substr($this->hex,4,2), 16, 10);

      break;

    }
  }



  /** Updates the h, s, and v object properties based on the object's r, g, and b values.
   *@return void
   *@access private
   */
  function _updateHsv(){
    $max = max($this->r,$this->g,$this->b);
    $min = min($this->r,$this->g,$this->b);
    $delta = $max-$min;
    $this->v = round(($max / 255) * 100);
    if($max != 0){
      $this->s = round($delta/$max * 100);
    }else{
      $this->s = 0;
    }

    if($this->s == 0){
      $this->h = false;
    }else{
      if($this->r == $max){
	$this->h = ($this->g - $this->b) / $delta;
      }elseif($this->g == $max){
	$this->h = 2 + ($this->b - $this->r) / $delta;
      }elseif($this->b == $max){
	$this->h = 4 + ($this->r - $this->g) / $delta;
      }
      $this->h = round($this->h * 60);
      if($this->h > 360){
	$this->h = 360;
      }
      if($this->h < 0){
	$this->h += 360;
      }
    }
  }

  /** Updates the object's hex property based on its r, g, and b values. 
   *@return void
   *@access private
   */
  function _updateHex(){
    $this->hex = $this->_toHex($this->r) . $this->_toHex($this->g) . $this->_toHex($this->b);
  }
}

?>
