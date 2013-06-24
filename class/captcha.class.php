<?php
/*
 version:1.0
 #http://www.hujuntao.com/ php
 #http://www.tipstricks.org/ asp&asp.net
*/
session_start();
class captcha {
	var $codetype=1;#0:Numbers 1:Chars and numbers 2:Word lists 3:math
	var $codelength=6;
	var $randtopmargin=false;
	var $border=true;
	var $noiseeffect=1;#0[none], 1[sketch], 2[random lines of text color], 3[Random lines of backcolor color over text (Recommed maximum noiseline=4)]
	var $noiseline = 6; #Low values make easy OCR, high values decrease readability
    var $noisepoint=true;
	var $angle = 20;
    var $sessionname = "CAPTCHA"; #Where store your secure code
	var $width=130;
	var $height=40;
	var $backcolor=array("#fffedf","#EBF8FF","#FFF2FF");
    var $forecolor=array("#17850c","#12679D","#CD2D00");
    var $noisecolor=array("#17850c","#12679D","#CD2D00");
	var $font='font/verdana.ttf';
	var $data="data/en.php";
	var $size=15;
	
	function display() {
		if(function_exists('imagecreatetruecolor') && function_exists('imagecolorallocate') && function_exists('imagecolorresolvealpha') &&
			function_exists('imagefilledellipse') && function_exists('imagettftext') && function_exists('imagettfbbox') &&
			function_exists('imageline') && function_exists('imagefontwidth') && function_exists('imagefontheight') && (function_exists('imagepng') || function_exists('imagejpeg'))) {
			$this->image();
		}
		
	}
	private function image() {
		$this->im = imagecreatetruecolor($this->width, $this->height);
		$r = mt_rand(0,count($this->backcolor)-1);
		
		//背景
		$backcolor = $this->backcolor($r);
		$this->background($backcolor);
		
        //干扰线
		$noisecolor = $this->noisecolor($r);
		if($this->noiseeffect ==2 && $this->noiseline != 0){
            $this->noiseline($noisecolor,$this->noiseline);
		}
		
		//文字
        $fontcolor = $this->fontcolor($r);
		$font = $this->font;
		$size = $this->size;
		$text = "";
		if($this->codetype==2){
			$text = $this->randomword();
		}else if($this->codetype==3){
			$text = $this->randommath();
		}else{
			for ($i = 0; $i < $this->codelength; $i++) {
			   $text = $text.$this->randomstring();
			}
		}
		
		//session
        $this->font($font,$size,$fontcolor,$text);
		
		//干扰线
		if($this->noiseeffect!=0 && $this->noiseeffect!=2 && $this->noiseline != 0){
            $this->noiseline($this->noiseeffect==1 ? $noisecolor : $backcolor,$this->noiseline);
		}
		
		//在背景上随机的生成干扰点
		if($this->noisepoint){
	       $this->noisepoint($noisecolor);
		}
		if($this->border){
			$this->border($noisecolor);
		}
		//echo $text;
		//exit();
		if(function_exists('imagepng')) {
			header('Content-type: image/png');
			imagepng($this->im);
		} else {
			header('Content-type: image/jpeg');
			imagejpeg($this->im, '', 100);
		}
		imagedestroy($this->im);
		
	}
	
	private function utf8_str_split($str, $split_len = 1)
	{
		if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
			return FALSE;
	 
		$len = mb_strlen($str, 'UTF-8');
		if ($len <= $split_len)
			return array($str);
	 
		preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
	 
		return $ar[0];
	}
	
	private function backcolor($i=0) {
		return $this->hex2rgb($this->backcolor[$i]);
		
	}
	
	private function noisecolor($i=0){
		return $this->hex2rgb($this->noisecolor[$i]);
	}
	
	private function fontcolor($i=0){
		return $this->hex2rgb($this->forecolor[$i]);
	}
	
	private function  randcolor(){
		$len = count($this->backcolor)-1;
		return rand(0,$len);
	}
	
    private function hex2rgb ($hex)
	{
		$hex = preg_replace("/[^0-9A-Fa-f]/", '', $hex);
		if (strlen($hex) === 3) {
			$rgb = array(
				hexdec($hex[0]),
				hexdec($hex[1]),
				hexdec($hex[2]));
		} elseif (strlen($hex) === 6) {
			$rgb = array(
				hexdec(substr($hex, 0, 2)),
				hexdec(substr($hex, 2, 2)),
				hexdec(substr($hex, 4, 2)));
		} else {
			$rgb = false;
		}
	
		return $rgb;
	}
	private function background($color){
		$backcolor=imagecolorallocate($this->im,$color[0],$color[1],$color[2]);
		imagefill($this->im, 0, 0, $backcolor);
	}
	private function border($color){
		$bordercolor =imagecolorresolvealpha($this->im,$color[0],$color[1],$color[2],110);
        imagerectangle($this->im, 0, 0, $this->width-1, $this->height-1, $bordercolor);
	}
	private function noiseline($color,$line=3){
		$noisecolor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);
		for ($i = 0; $i < $line; $i++) {
				imageline($this->im, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $noisecolor);
			}
		
	}
	private function noisepoint($color,$point=6){
		$noisecolor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);
		for( $i=0; $i<$point; $i++ ) {
			imagefilledellipse($this->im, mt_rand(0,$this->width),mt_rand(0,$this->height), 2, 3, $noisecolor);
		}
	}
	private function font($font,$size,$color,$text){
		$fontcolor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);
		if($this->randtopmargin){
			$len=mb_strlen($text,'utf8');
			$width=imagefontwidth($size)*($len==strlen($text)?1:2)+5;
			$height=imagefontheight($size);
			$x=($this->width-$width*$len)/2;
			$y=$height+($this->height-$height)/2;
			$arr = $this->utf8_str_split($text);
			for($i=0;$i<$len;$i++){
				imagettftext($this->im, $size, mt_rand(0,$this->angle), $x+$width*$i,mt_rand($y-3,$y+3), $fontcolor, $font, $arr[$i]);
			}
		}else{
			$textbox = imagettfbbox($size, 0, $font, $text);
			$x=($this->width - $textbox[4])/2;
			$y=($this->height - $textbox[5])/2;
			imagettftext($this->im, $size, 0, $x,$y, $fontcolor, $font, $text);
		}
	}
	private function randomstring($type=1,$len=1) {
        if($type == 1) {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		}else if($type == 3){
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		}else if($type == 0){
                $chars = '0123456789';
        }
		$string = "";

		while (strlen($string) < $len){
			$string .= substr($chars, (mt_rand() % strlen($chars)), 1);
		}
		$this->session($string);
		return $string;
    }
	
	private function randomword(){
		require($this->data);
		$string=$words[mt_rand(0,count($words))];
		$this->session($string);
		return $string;
	}
	private function randommath(){
		$num1=mt_rand(0,20);
		$num2=mt_rand(0,20);
		$math=mt_rand(0,2);
		if( $math === 0 ) {
            $string = "$num1+$num2=?";
			$this->session($num1+$num2);
        }else if( $math === 1 ) {
            $string = "$num1-$num2=?";
			$this->session($num1-$num2);
        }else{
            $string = "$num1*$num2=?";
			$this->session($num1*$num2);
        }
		return $string;
	}
	private function session($tr){
		$_SESSION[$this->sessionname]=md5($tr);
	}
}
?>