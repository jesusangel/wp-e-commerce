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

    $dst_img = ImageCreateTrueColor($width,$height);
    if($imagetype[2] == IMAGETYPE_PNG)
      {
      ImageAlphaBlending($dst_img, false);
      imagesavealpha($dst_img,true);
      }
      else if($imagetype[2] == IMAGETYPE_GIF)
      {
      ImageAlphaBlending($dst_img, true);
      $background = imagecolorallocate($dst_img, 0, 0, 0);
      ImageColorTransparent($dst_img, $background); // make the new temp image all transparent
      }

    ImageCopyResampled($dst_img,$src_img,0,0,0,0,$width,$height,$source_w,$source_h);
    header("Content-type: image/png");
    ImagePNG($dst_img);
    exit();
    }
?>
