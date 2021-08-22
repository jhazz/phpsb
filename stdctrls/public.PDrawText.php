<?php
class stdctrls_PDrawText
{
function LoadXML($args) {
  $__=&$GLOBALS['_STRINGS']['_'];
  extract(param_extract(array(
    ControlID=>'int'
    ),$args));
  $result="";
  if (!$ControlID) { print "No control ID defined!"; return false; }
  $qc=DBQuery ("SELECT PropertiesStr FROM jsb_PageControls WHERE JSBPageControlID=$ControlID");
  if (!$qc) { return false; }
  $props=explode_properties($qc->Top->PropertiesStr);
  $Text=$props['Text'];
  return array(XML=>$Text);
  }

  function PrintXML($args) {
  extract(param_extract(array(
    Text=>'string'
    ),$args));

  return array(XML=>urldecode($Text));
  }


function printSwfDetector()
  {
  global $cfg;
  $this->scriptSwfDetectorPrinted=true;
  ?><script>
  var TDRawText_loadersrc="<? print $cfg['PublicURL']."/stdctrls/titleloader.swf"; ?>"
  function TDrawText_swf(swf_src,swf_w,swf_h,img_src,img_w,img_h,text,bgcolor)
    {
    var id=Math.random()*100;
    var s='<object id="'+id+'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'+swf_w+'" height="'+swf_h+'"> \
    <param name="movie" value="'+TDRawText_loadersrc+'?header='+text+'&swf='+swf_src+'"/>\
    <param name="quality" value="best" />\
    <param name="wmode" value="transparent" />\
    <param name="loop" value="false" />\
    <param name="menu" value="false" /> \
    <param name="scale" value="noscale" />\
    <param name="salign" value="lt" />\
    <param name="wmode" value="transparent" />\
    <param name="bgcolor" value="'+bgcolor+'" />\
    <embed name= "'+id+'" src="'+TDRawText_loadersrc+'?header='+text+'&swf='+swf_src+'"\
      wmode="transparent"\
      quality="best"\
      type="application/x-shockwave-flash"\
      width="'+swf_w+'"\
      height="'+swf_h+'"\
      scale="noscale"\
      salign="lt"\
      bgcolor="'+bgcolor+'"\
      loop="false"\
      menu="false"/>\
    </object>';
    document.write(s);
    }
  </script>

  <?

  }

function GetDrawText($args)
  {
  # RenderAsText
  # DrawingFont
  # Text
  # Cropping

  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $cfg, $_THEME_NAME,$_THEME;
  $JSB_ThemeFile_Time=$_THEME['Uptime'];

  extract ($args);

  if (!$RenderAsText) {
    $RenderAsText=$cfg['Settings']['stdctrls']['DrawTextDisable'];
    }

  $ThemesPath=$_THEME['ThemePath'];
  $style=&$_THEME['DrawingFonts'][$DrawingFont];

  if (!$style) {
    return array(Error=>"Drawing text style not found",Details=>"StyleID: $DrawingFont");
    }
    
  if ($style['sprintfHTML']){
  	return array(Text=>sprintf($style['sprintfHTML'],$Text));
  }

  if (($RenderAsText)||(!$style['Script']))
    {
    $TextCSSStyle=$style['TextCSSStyle'];
    list($t,$c)=get_css_pair($TextCSSStyle,'p');
    if ($Align) {$Align=" align='$Align'";}
    return array(Text=>"<$t$c$Align>$Text</$t>");
    }

  $script=explode ("\n",$style[Script]);
  if (!$script)
    {
    return array(Error=>"Style script not found",Details=>$style[Caption]);
    }

  $texthash=md5($JSB_ThemeFile_Time.$Text.$DrawingFont.$_THEME_NAME);
  $tmppath=$cfg['TempPath'];
  $tmpurl= $cfg['TempURL'];
  $ImgURL=false;

  $exts=array('.jpg','.gif','.png');
  foreach ($exts as $ext)
    {
    $OutputFile=$tmppath.'/'.$texthash.$ext;
    if (file_exists($OutputFile))
      {
      $size=@getimagesize($OutputFile);
      if (!$size) {continue;}
      $ImgURL=$tmpurl.'/'.$texthash.$ext;
      }
    }

  if (!$ImgURL)
    {

    $ImgURL=$tmpurl.'/'.$texthash;
    $OutputFile=$tmppath.'/'.$texthash;

    $drawn=$im=false;
    $swftext="";

    foreach ($script as $line)
      {
      $line=trim($line);
      if (substr($line,0,1)=='#') {continue;}
      $parts=explode (' ',$line);
      $op=$parts[0];
      $args=false;
      for ($i=1;$i<count($parts);$i++)
        {
        list ($k,$v)=explode (':',$parts[$i],2);
        $args[$k]=$v;
        }
      $op=strtolower($op);

      switch ($op)
        {
        case "blending":
          imagesavealpha($im,true);
          $c=imagecolorallocatealpha($image, 255, 255, 255, 0);
          imagefilledrectangle($im,0,0,$w,$h,$c);
          break;
        case "transparent":
          if (!is_resource($im)) {return array(Error=>"Theme font style script error",Details=>"$op try to use empty image resource");}
          list ($r,$g,$b)=sscanf ($args[color],"#%02x%02x%02x");
          $c=imagecolorallocate($im,$r,$g,$b);
          imagecolortransparent($im,$c);
          break;
        case "createfromgif":
        case "createfrompng":
        case "createfromjpeg":
          $f=basename($args[src]);
          $ffile=$ThemesPath.'/'.$f;
          if (!file_exists($ffile))
            {return array(Error=>"Unable to find source image",Details=>$f);}
          switch ($op)
            {
            case "createfromgif": $im=@imagecreatefromgif($ffile); break;
            case "createfromjpeg": $im=@imagecreatefromjpeg($ffile); break;
            case "createfrompng": $im=@imagecreatefrompng($ffile); break;
            }
          if (!$im)
            {return array(Error=>"Unable to load image",Details=>$ffile);}
          $info=getimagesize($ffile);
          $w=$info[0]; $h=$info[1];
          break;

        case "drawimage":
          $f=basename($args['src']);
          $ffile=$ThemesPath.'/'.$f;
          if (!file_exists($ffile))
            {return array(Error=>"Unable to find overdrawing image",Details=>$f);}
          $parts=pathinfo($f);
          switch ($parts['extension'])
            {
            case 'jpg': case 'jpeg':$im2=@imagecreatefromjpeg($ffile); break;
            case 'gif': $im2=@imagecreatefromgif($ffile); break;
            case 'png': $im2=@imagecreatefrompng($ffile); break;
            }
          if (!$im2)
            {return array(Error=>"Unable to load image",Details=>$ffile);}

          $dst_x=intval($args['dst_x']);
          $dst_y=intval($args['dst_y']);
          $src_x=intval($args['src_x']);
          $src_y=intval($args['src_y']);
          if (isset($args[src_w]))
            {
            $src_w=intval($args['src_w']);
            $src_h=intval($args['src_h']);
            }
          else
            {
            $finfo=getimagesize($ffile);
            $src_w=$finfo[0];
            $src_h=$finfo[1];
            }
          $opacity=(isset($args['opacity'])) ? intval($args['opacity']) : 100;
          imagecopymerge ($im,$im2,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h,$opacity);

          break;
        case "createbox256":
        case "createbox":
          $w=intval($args['width']);
          $h=intval($args['height']);
          if ((function_exists("imagecreatetruecolor"))&&($op!='createbox256'))
            {$im = @imagecreatetruecolor ($w,$h);}
          else
            {$im = @imagecreate ($w,$h);}
          break;


        case "createttfbox256":
        case "createttfbox":
          $fontsize=intval($args['fontsize']);
          $angle=intval($args['angle']);
          $font=basename($args['font']);
          $maxwidth=intval($args['maxwidth']);
          $UText=$Text;
          if ($args['uppercase']) $UText=mb_strtoupper($UText,"utf-8");
          $charset="utf-8";
          if ($args['fontcharset'])  {$charset=$args['fontcharset']; $UText=mb_convert_encoding($Text,$charset,"utf-8");}
          if ($args['unicode']) {$UText=mb_convert_encoding($Text,"HTML-ENTITIES",$charset);  }

          $ffile=$ThemesPath.'/'.$font;
          if (!file_exists($ffile))
            {return array(Error=>"Unable to find TTF font",Details=>$font);}

          $width_add=intval($args[width_add]);
          $height_add=intval($args[height_add]);

          if ($angle)
            {
            $box=imagettfbbox ($fontsize,$angle,$ffile,$UText);
            $maxx=$maxy=$miny=$minx=false;
            for ($i=0;$i<4;$i++)
              {
              $x=$box[$i*2]; $y=$box[$i*2+1];
              if (($maxx===false)||($x>$maxx)) $maxx=$x;
              if (($maxy===false)||($y>$maxy)) $maxy=$y;
              if (($minx===false)||($x<$minx)) $minx=$x;
              if (($miny===false)||($y<$miny)) $miny=$y;
              }
            $w=$maxx-$minx+$width_add;
            $h=$maxy-$miny+$height_add;
            }
          else
            {
            $box2=imagettfbbox ($fontsize,0,$ffile,'Wq');
            if ((!$box2) || ($box2[7]==-1) || ($box2[1]==-1))
              {
//                return array(Error=>"Unable to create 'Wq' text using this TTF font",Details=>$ffile);
              }
            $box=imagettfbbox ($fontsize,0,$ffile,$UText);
            if ((!$box) || ($box[7]==-1))
              {
              return array(Error=>"Unable to create text box for text '$UText' using this TTF font",Details=>$ffile);
              }
            $h=($box2[1]-$box2[7])+$height_add;
            $w=($box[2]-$box1[0])+$width_add;
            }
          if ((function_exists("imagecreatetruecolor"))&&($op!='createttfbox256'))
            {$im = @imagecreatetruecolor ($w,$h);
            if (!is_resource($im)) {return array(Error=>"Function call error imagecreatetruecolor ($w,$h)",Details=>$im);}
            }
          else
            {$im = @imagecreate ($w,$h);
            if (!is_resource($im)) {return array(Error=>"Function call error imagecreate ($w,$h)",Details=>$im);}
            }
          break;

        case "background":
          if (!is_resource($im)) {return array(Error=>"Theme font style script error",Details=>"Function '$op' try to use empty image resource [$im]");}
          list ($r,$g,$b)=sscanf ($args[color],"#%02x%02x%02x");
          $c=imagecolorallocate($im,$r,$g,$b);
          imagefilledrectangle($im,0,0,$w,$h,$c);
          break;

        case "ttftext":
          if (!is_resource($im)) {return array(Error=>"Theme font style script error",Details=>"Function '$op' try to use empty image resource [$im]");}
          $fontsize=intval($args[fontsize]);
          $angle=intval($args[angle]);
          $font=basename($args[font]);
          $color=$args[color];
          list ($r,$g,$b)=sscanf ($color,"#%02x%02x%02x");

          $UText=$Text;
          if ($args['uppercase']) $UText=mb_strtoupper($UText,"utf-8");
          $charset="utf-8";
          if ($args['fontcharset'])  {$charset=$args['fontcharset']; $UText=mb_convert_encoding($Text,$charset,"utf-8");}
          if ($args['unicode']) {$UText=mb_convert_encoding($Text,"HTML-ENTITIES",$charset);  }

          $ffile=$ThemesPath.'/'.$font;
          if (!file_exists($ffile))
            {return array(Error=>"Unable to find TTF font",Details=>$font);}

          if (isset($args[y]))
            {
            $x=intval($args[x]);
            $y=intval($args[y]);
            }
          else
            {
            $box1=imagettfbbox ($fontsize,0,$ffile,'q');
            $box2=imagettfbbox ($fontsize,0,$ffile,'a');


            $box=imagettfbbox ($fontsize,0,$ffile,$UText);
            $baseline_shift=($box1[1]-$box1[7])-($box2[1]-$box2[7]);
            $x=0;
            $y=$h-$baseline_shift;

            if ($args['align'])
              {
              switch ($args['align'])
                {
                case 'right':
                  $x=$w-($box[2]-$box[0]);
                  $shift=intval($args['rightmargin']);
                  if ($shift) $x-=$shift;
                  break;
                case 'center':
                  $x=($w-$box[2]+$box[0])>>1;
                  break;
                }
              }
            $shift=intval($args['bottommargin']);        if ($shift) $y-=$shift;
            $shift=intval($args['leftmargin']);        if ($shift) $x+=$shift;
            $shift=intval($args['topmargin']);        if ($shift) $y+=$shift;
            $shift=intval ($args['shiftx']); if ($shift) $x+=$shift;
            $shift=intval ($args['shifty']); if ($shift) $y+=$shift;
            }

          $ink = imagecolorallocate($im, $r,$g,$b);
          imagettftext($im, $fontsize, $angle, $x, $y, $ink, $ffile,$UText);
          break;
        case "output":
          switch ($args[filetype])
            {
            case "jpeg": case "jpg":
              if (!(imagetypes() & IMG_JPG))
                {return array(Error=>"No JPG output support at the hosting!");}
              $q=intval($args[quality]);
              if (!$q) {$q=75;}
              $OutputFile.='.jpg'; $ImgURL.='.jpg';
              imagejpeg($im,$OutputFile,$q);
              imagedestroy($im);
              chmod ($OutputFile,0766);
              break;
            case "gif":
              if (!(imagetypes() & IMG_GIF))
                {return array(Error=>"No GIF output supported at the hosting!");}
              $OutputFile.='.gif'; $ImgURL.='.gif';
              imagegif($im,$OutputFile);
              imagedestroy($im);
              chmod ($OutputFile,0766);
              break;
            case "png":
              if (!(imagetypes() & IMG_PNG))
                {return array(Error=>"No PNG output support at the hosting!");}
              $OutputFile.='.png'; $ImgURL.='.png';
              imagepng($im,$OutputFile);
              imagedestroy($im);
              chmod ($OutputFile,0766);
              break;
            }
          $im=false; # 'output' could be used only once
          $drawn=true;
          $size=@getimagesize($OutputFile);
          break;
        }
      if ($drawn) {break;} # if image was drawn. stop the script
      }

    }

  if (($style['swf_src'])&&(!$GLOBALS['_FLASHDISABLED']))
    {
    if (!$this->scriptSwfDetectorPrinted)
      {
      $this->printSwfDetector();
      }
    $f=basename($style['swf_src']);
    $ffile=$_THEME['SkinPath'].'/'.$f;


    if (!file_exists($ffile))
      {return array(Error=>"Unable to find SWF in the public template folder",Details=>$ffile);}
    $swfsize=@getimagesize($ffile);
    $swf_src=$_THEME['SkinURL'].'/'.$f;
    $hText=urlencode($Text);
    $bgcolor=$style['swf_bgcolor'];
    if (!$bgcolor) {$bgcolor="#ffffff";}
    return array(Text=>"<script>TDrawText_swf ('$swf_src',$swfsize[0],$swfsize[1],'$ImgURL',$size[0],$size[1],'$hText','$bgcolor');</script>");
    }
  else
    {
    if ($size)
      {
      return 
        ($Cropping)?
        array(Text=>"<table width='100%'><tr><td style='background:url($ImgURL);background-repeat:no-repeat;'><img src='$_THEME[SkinURL]/$_THEME[Spacer]' alt='$Text' width='30' height='$size[1]'></td></tr></table>"):
      	array(Text=>"<img src='$ImgURL' $size[3] alt='$Text'>");
#        if ($size[2]==3) {print " style=\"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$ImgURL', enabled=true, sizingMethod='scale')\"";}
      }
    else
      {
      return array(Error=>"Error size of image",Details=>"StyleID: $DrawingFont, Text:'$Text'");
      }
    }
  }

}
