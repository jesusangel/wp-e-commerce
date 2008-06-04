<?php
  $imagetype = @getimagesize($imagepath);
  switch($imagetype[2])
    {
    case IMAGETYPE_JPEG:
    //$extension = ".jpg";
    $src_img = imagecreatefromjpeg($imagepath);
    $pass_imgtype = true;
    break;

    case IMAGETYPE_GIF:
    //$extension = ".gif";
    $src_img = imagecreatefromgif($imagepath);
    $pass_imgtype = true;
    break;

    case IMAGETYPE_PNG:
    //$extension = ".png";
    $src_img = imagecreatefrompng($imagepath);/*
    ImageAlphaBlending($src_img, false);
    imagesavealpha($src_img,true);*/
    $pass_imgtype = true;
    break;

    default:
    $pass_imgtype = false;
    break;
    }

  if($pass_imgtype === true)
    {
    $source_w = imagesx($src_img);
    $source_h = imagesy($src_img);

 //Temp dimensions to crop image properly
    $temp_w = $width;
    $temp_h = $height;
    if ($height < $width ) {
       $temp_h = ($width / $source_w) * $source_h;
    } else {
       $temp_w = ($height / $source_h) * $source_w;
    }

    // Create temp resized image
    $temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
    $bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
    ImageFilledRectangle( $temp_img, 0, 0, $temp_w, $temp_h, $bgcolor );
    ImageAlphaBlending( $temp_img, TRUE );

    ImageCopyResampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );

    $dst_img = ImageCreateTrueColor($width,$height);
    $bgcolor = ImageColorAllocate( $dst_img, 255, 255, 255 );
    ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $bgcolor );
    ImageAlphaBlending($dst_img, TRUE );

    // X & Y Offset to crop image properly
    $w1 = ($temp_w/2) - ($width/2);
    $h1 = ($temp_h/2) - ($height/2);
   
    
    ImageCopyResampled( $dst_img, $temp_img, 0, 0, $w1, $h1, $width, $height, $width, $height );
    
    if($imagetype[2] == IMAGETYPE_PNG) {
      imagesavealpha($dst_img,true);
      ImageAlphaBlending($dst_img, false);
    }

    header("Content-type: image/png");
    ImagePNG($dst_img);
    exit();
    }
?>