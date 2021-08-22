<?

if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }


class stdctrls_FFlash {

  function stdctrls_FFlash () {
    $this->CopyrightText="(c)2006 JhAZZ Site Builder. Flash frontend component";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
  }

  function Putswf ($args) {
    $src=$args['src'];
    $width=$args['width'];
    $height=$args['height'];
    if ($width && $height)
      {
      $size=" width='$width' height='$height'";
      }
    else {$size=@getimagesize($src); $size=$size[3]; }
    if ($size)
      {
      return array(XML=>"<swf $size src='$src'/>");
      }
  }
}
?>
