<?php
$imagetype = @getimagesize($imagepath); //previously exif_imagetype()

if(file_exists($imagepath) && is_numeric($height) && is_numeric($width))
  {
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
    $src_img = imagecreatefrompng($imagepath);
    imagesavealpha($src_img,true);
    ImageAlphaBlending($src_img, false);
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

    $dst_img = ImageCreateTrueColor($width,$height);
    if($imagetype[2] == IMAGETYPE_PNG)
      {
      imagesavealpha($dst_img,true);
      ImageAlphaBlending($dst_img, false);
      }

    ImageCopyResampled($dst_img,$src_img,0,0,0,0,$width,$height,$source_w,$source_h);
    switch($imagetype[2])
      {
      case IMAGETYPE_JPEG:
      ImageJPEG($dst_img, $image_output, 75);
      break;

      case IMAGETYPE_GIF:
      if(function_exists("ImageGIF"))
        {
        @ImageGIF($dst_img, $image_output);
        }
        else
          {
          ImageAlphaBlending($dst_img, false);
          @ImagePNG($dst_img, $image_output);
          }
      break;

      case IMAGETYPE_PNG:
      @ImagePNG($dst_img, $image_output);
      break;
      }
    usleep(50000);  //wait 0.05 of of a second to process and save the new image
    imagedestroy($dst_img);
    }
  }
  else
    {
    move_uploaded_file($imagepath, ($imagedir.$_FILES['image']['name']));
    $image = $wpdb->escape($_FILES['image']['name']);
    }
?>
