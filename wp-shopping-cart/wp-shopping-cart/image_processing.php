<?php
function image_processing($image_input, $image_output, $width = null, $height = null,$imagefield='') {
global $wpdb;
	/*
	* this handles all resizing of images that results in a file being saved, if no width and height is supplied, then it just copies the image
	*/
	$imagetype = getimagesize($image_input);
	if(file_exists($image_input) && is_numeric($height) && is_numeric($width) && function_exists('imagecreatefrompng')) {
		switch($imagetype[2]) {
			case IMAGETYPE_JPEG:
			//$extension = ".jpg";
			$src_img = imagecreatefromjpeg($image_input);
			$pass_imgtype = true;
			break;
	
			case IMAGETYPE_GIF:
			//$extension = ".gif";
			$src_img = imagecreatefromgif($image_input);
			$pass_imgtype = true;
			break;
	
			case IMAGETYPE_PNG:
			//$extension = ".png";
			$src_img = imagecreatefrompng($image_input);
			imagesavealpha($src_img,true);
			ImageAlphaBlending($src_img, false);
			$pass_imgtype = true;
			break;
	
			default:
			move_uploaded_file($image_input, ($imagedir.basename($_FILES[$imagefield]['name'])));
			$image = $wpdb->escape(basename($_FILES[$imagefield]['name']));
			return true;
			exit();
			break;
		}
	
		if($pass_imgtype === true) {
			$source_w = imagesx($src_img);
			$source_h = imagesy($src_img);
			
			//Temp dimensions to crop image properly
			$temp_w = $width;
			$temp_h = $height;
			if ( $source_w < $source_h ) {
				$temp_h = ($width / $source_w) * $source_h;
			} else {
				$temp_w = ($height / $source_h) * $source_w;
			}
	
			// Create temp resized image
			$temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
			$bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
			ImageFilledRectangle( $temp_img, 0, 0, $width, $height, $white );
			ImageAlphaBlending( $temp_img, TRUE );
	
			// resize keeping the perspective
			ImageCopyResampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );
	
			$dst_img = ImageCreateTrueColor($width,$height);
			$white = ImageColorAllocate( $dst_img, 255, 255, 255 );
			ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $white );
			ImageAlphaBlending($dst_img, TRUE );
	
			// X & Y Offset to crop image properly
			$w1 = ($temp_w/2) - ($width/2);
			$h1 = ($temp_h/2) - ($height/2);
			
			if($imagetype[2] == IMAGETYPE_PNG) {
				imagesavealpha($dst_img,true);
				ImageAlphaBlending($dst_img, false);
			}
				
				
			// Final thumbnail cropped from the center out.
			ImageCopyResampled( $dst_img, $temp_img, 0, 0, $w1, $h1, $width, $height, $width, $height );
	
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				if(@ ImageJPEG($dst_img, $image_output, 75) == false) { return false; }
				break;
	
				case IMAGETYPE_GIF:
				if(function_exists("ImageGIF")) {
					if(@ ImageGIF($dst_img, $image_output) == false) { return false; }
				} else {
					ImageAlphaBlending($dst_img, false);
					if(@ ImagePNG($dst_img, $image_output) == false) { return false; }
				}
				break;
	
				case IMAGETYPE_PNG:
				if(@ ImagePNG($dst_img, $image_output) == false) { return false; }
				break;
			}
			usleep(50000);  //wait 0.05 of of a second to process and save the new image
			imagedestroy($dst_img);
			//$image_output
			
			$stat = stat( dirname( $image_output ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $image_output, $perms );
			return true;
		}
	} else {
		move_uploaded_file($_FILES[$imagefield]['tmp_name'], $image_output);
		$image = $wpdb->escape(basename($_FILES[$imagefield]['name']));
		return $image;
	}
	return false;
}
?>
