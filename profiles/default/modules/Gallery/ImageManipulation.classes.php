<?php

interface ImageManipulation {

	public static function available();
	
	public static function mimeTypes();
	
	public function resizeImage($source, $destination, $width, $height, $crop);

}

class FakeImageManipulation implements ImageManipulation {

	public static function available(){
		return true;
	}
	
	public static function mimeTypes(){
		$types = array();

		$types[] = 'image/gif';
		$types[] = 'image/jpeg';
		$types[] = 'image/png';

		return $types;
	}
	
	public function resizeImage($source, $destination, $width, $height, $crop){
		copy($source, $destination);
	}

}

class GDImageManipulation implements ImageManipulation {

	public static function available(){
		return function_exists('gd_info');
	}
	
	public static function mimeTypes(){
		$types = array();
		
		if(function_exists('imagecreatefromgif'))
			$types[] = 'image/gif';
		
		if(function_exists('imagecreatefromjpeg'))
			$types[] = 'image/jpeg';
			
		if(function_exists('imagecreatefrompng'))
			$types[] = 'image/png';

		return $types;
	}

	public function resizeImage($source, $destination, $width, $height, $crop){
		
		$image_info = getimagesize($source);
		if(!$image_info){
			throw new ImageManipulationException('Could not load image '.$source.'.');
		}

		switch ($image_info['mime']) {
			case 'image/gif':
				$image = imagecreatefromgif($source);
				$type = 'image/gif';
				break;
			case 'image/jpeg':
				$image = imagecreatefromjpeg($source);
				$type = 'image/jpeg';
				break;
			case 'image/png':
				$image = imagecreatefrompng($source);
				$type = 'image/png';
				break;
			default:
				throw new ImageManipulationException("Unknown image format.");
		}

		$width_src = $image_info[0];
		$height_src = $image_info[1];

		if($width_src < $width && $height_src < $height){
			$resized = $image;
		}
		else if(!$crop){
			// resize only
			if($width_src > $width || $height_src > $height){
				if($width / $height < $width_src / $height_src){
					$percent = $width / $width_src;
				}
				else {
					$percent = $height/ $height_src;
				}
			}
			else{
				$percent = 1;
			}
				
			$width_calc = $width_src * $percent;
			$height_calc = $height_src * $percent;
				
			$resized = imagecreatetruecolor($width_calc, $height_calc);
				
			imagecopyresampled($resized, $image, 0, 0, 0, 0, $width_calc, $height_calc, $width_src, $height_src);
		}
		else{
			// resize and crop
			$off_w = 0;
			$off_h = 0;
				
			if($width / $height > $width_src / $height_src){
				$percent = $width / $width_src;
				$off_h = $height_src - ($height / $percent);
			}
			else {
				$percent = $height / $height_src;
				$off_w = $width_src - ($width / $percent);
			}
				
			$width_calc = $width_src * $percent;
			$height_calc = $height_src * $percent;
				
			$resized = imagecreatetruecolor($width, $height);
				
			imagecopyresampled($resized, $image, -($width_calc/2) + ($width/2), -($height_calc/2) + ($height/2), 0, 0, $width_calc, $height_calc, $width_src, $height_src);
		}

		switch ($type) {
			case 'image/gif':
				imagegif($resized, $destination);
				break;
			case 'image/jpeg':
				imagejpeg($resized, $destination);
				break;
			case 'image/png':
				imagepng($resized, $destination);
				break;
			default:
				throw new ImageManipulationException("Unknown image format.");
		}
	}
}


class ImageManipulationException extends Exception {

}

?>