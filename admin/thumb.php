<?php

function imageflip ($image, $mode) {
  $w = imagesx ($image);
  $h = imagesy ($image);
  $flipped = imagecreate ($w, $h);
  if ($mode == 'y')
    {
      for ($y = 0; $y < $h; $y++)
	imagecopy ($flipped, $image, 0, $y, 0, $h - $y - 1, $w, 1);
    }
  else
    {
      for ($x = 0; $x < $w; $x++)
	imagecopy ($flipped, $image, $x, 0, $w - $x - 1, 0, 1, $h);
    }
  return $flipped;
}

function imagerotate90 ($in, $angle = 0) {
  $w = imagesx ($image);
  $h = imagesy ($image);
  $angle %= 360;
  if ($angle < 0)
    $angle += 360;

  if ($w == $h || $angle = 0 || $angle == 180)
    return imagerotate ($in, $angle, 0);

  $size = ($w > $h ? $w : $h);

  // create a square image the size of the largest side of our src image
  if (($tmp = imagecreatetruecolor ($size, $size)) == false)
    return false;

  // copy our src image to tmp where we will rotate and then copy that to $out
  imagecopy ($tmp, $in, 0, 0, 0, 0, $w, $h);
  $tmp2 = imagerotate ($tmp, $angle, 0);
  imagedestroy ($tmp);

  // exchange sides
  if (($out = imagecreatetruecolor ($h, $w)) == false)
    return false;

  // copy tmp2 to $out
  imagecopy ($out, $tmp2, 0, 0, ($angle == 270 ? abs ($w - $h) : 0), 0, $h, $w);
  imagedestroy ($tmp2);
  return $out;
}

function resize ($filename, $saveTo, $maxx, $maxy, $q) {
  $exif = exif_read_data ($filename);
  if (is_array ($exif) && isset ($exif['Orientation']))
    {
      $flip = false; $rotate = 0;
      switch ($exif['Orientation'])
        {
	case 1: break;
	case 2: $flip = 'x'; break;
	case 3: $rotate = 180; break;
	case 4: $flip = 'y'; break;
	case 5: $flip = 'y'; $rotate = 270; break;
	case 6: $rotate = 270; break;
	case 7: $flip = 'x'; $rotate = 270; break;
	case 8: $rotate = 90; break;
	}
    }

  $source = imagecreatefromjpeg ($filename);
  $width = imagesx($source);
  $height = imagesy($source);
  
  if (($rotate % 180) == 90)
    {
      $t = $maxx;
      $maxx = $maxy;
      $maxy = $t;
    }

  $newWidth = $width;
  $newHeight = $height;
  if ($newWidth > $maxx)
    {
      $newWidth = $maxx;
      $newHeight = $height / ($width / $maxx);
    }
  if ($newHeight > $maxy)
    {
      $newWidth = $width / ($height / $maxy);
      $newHeight = $maxy;
    }

  $thumb = imagecreatetruecolor ($newWidth, $newHeight);
  imagecopyresampled ($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

  if (0 && $flip !== false)
    {
      $new = imageflip ($thumb, $flip);
      imagedestroy ($thumb);
      $thumb = $new;
    }

  if ($rotate != 0)
    {
      $new = imagerotate90 ($thumb, $rotate);
      imagedestroy ($thumb);
      $thumb = $new;
    }

  imagejpeg ($thumb, $saveTo, $q);
  imagedestroy ($thumb);
  imagedestroy ($source);
}

class ThumbnailField extends FileField {
  function upload_file ($src, $dirname, $name) {
    $thumbdir = dirname ($dirname . 'thumb/' . $name);
    if (!file_exists ($thumbdir))
      mkdir ($thumbdir,0755);

    resize ($src, $dirname . $name, 900, 600, 85);
    resize ($src, $dirname . 'thumb/' . $name, 400, 250, 99);
  }

  function delete_file ($dirname, $name) {
    unlink ($dirname . $name);
    unlink ($dirname . 'thumb/' . $name);
  }
}

class LinkedThumbnailField extends LinkedFileField {
  function upload_file ($src, $dirname, $name) {
    $thumbdir = dirname ($dirname . 'thumb/' . $name);
    if (!file_exists ($thumbdir))
      mkdir ($thumbdir,0755);

    resize ($src, $dirname . $name, 900, 600, 85);
    resize ($src, $dirname . 'thumb/' . $name, 400, 250, 99);
  }

  function delete_file ($dirname, $name) {
    unlink ($dirname . $name);
    unlink ($dirname . 'thumb/' . $name);
  }
}

?>
