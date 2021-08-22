<?
class img_IImage
{
var $CopyrightText="(c)2007 PHPSB. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/img";
var $ComponentVersion="1.0";

var $_formatsRead=array();

function MakeThumbnailHtml($args)
  {
  global $_THEME;
  extract(param_extract(array(
    ImageHtml=>'data',
    CaptionHtml=>'data',
    ImageStyle=>'string',
    Align=>'string'
    ),$args));

  if ($Align) {$AlignStr=" align='$ImgAlign' ";}
  if ($ImageStyle)
    {
    $Style=$_THEME['ImageStyles'][$ImageStyle];
    list ($Caption_tag,$Caption_CSS)=get_css_pair ($Style['CSS_Caption'],"span");
    if ($Style)
      {
      if (isset($Style['HTML']))
        {
        return preg_replace(array("'#IMAGE#'i","'#CAPTION#'i","'#ALIGN#'i"),
                            array($ImageHtml,$CaptionHtml,$AlignStr),$Style['HTML']);
        }
      elseif ($Style['CSS_Cell'])
        {
        list ($Border_tag,$Border_class)=get_css_pair ($Style['CSS_Cell'],"td");
        if ($CaptionHtml) $CaptionHtml="<$Caption_tag $Caption_CSS>$CaptionHtml</$Caption_tag>";
        return "<table $AlignStr cellpadding='0' cellspacing='0' border='0'><tr><$Border_tag $Border_class>$ImageHtml$CaptionHtml</td></tr></table>";
        }
      }
    }
  else
    {
    if ($CaptionHtml) $CaptionHtml="<div style='font-size:10px'>$CaptionHtml</div>";
    return "<table $AlignStr cellpadding='0' cellspacing='0' border='0'><tr><td align='center'>$ImageHtml$CaptionHtml</td></tr></table>";
    }
  }

function View($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    TnFormatNo=>"int",
    ImgID=>'int',
    ShowCaption=>"int",
    EditMode=>"int",
    ImageStyle=>'string',
    qimg=>"object",
    ReplaceEmptyImg=>"string",
    BackgroundColor=>"string",
    OnClickURL=>'string',
    ),$args));

  global $cfg,$_THEME;
  $_=&$GLOBALS['_STRINGS']['img'];

  $Caption_tag="span";
  $Border_tag="td";

  if ($BindTo)
    {
    $BindToInfo=BindPathInfo($BindTo);
    if ($BindToInfo->SubID) $ImgID=$BindToInfo->SubID;
    }

  if ($ImgID)
    {
    if ($qimg)
      {
      $imgdoc=$qimg->Rows[$ImgID];
      }
    else
      {
      $qimg=DBQuery ("SELECT BindTo,ImgID,Caption,Filenames FROM img_Documents WHERE ImgID=$ImgID");
      $imgdoc=$qimg->Top;
      if (!$qimg)
        {
        return;
        }
      }

    $BindTo=$imgdoc->BindTo;
    $BindToInfo=BindPathInfo($BindTo);
    }
  elseif ($BindTo)
    {
    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo)
      {
      return array(Error=>$_['ERROR_BINDTO'],Details=>$BindTo);
      }
    if (!$qimg)
      {
      $qimg=DBQuery ("SELECT ImgID,Caption,Filenames,BindTo FROM img_Documents WHERE BindTo='$BindTo' ORDER BY ImgID DESC LIMIT 0,1");
#      $qimg->Dump();
      }
    $imgdoc=$qimg->Top;
    }
  else {
    return array(Error=>$_['ERROR_BINDTO'],Details=>$BindTo);
    }
  $r=$this->_getFormatInfo(array(BindTo=>$BindTo));
  if ($r['Error'])
    {
    return array(Error=>$r['Error'],Details=>$r['Details']);
    }
  $qf=&$r['qf'];
  $CanHasURL=$r['CanHasURL'];
  if (($EditMode)&&($_GET['viewformatno'])) {$TnFormatNo=$_GET['viewformatno'];}
  $Format=$qf->Rows[$TnFormatNo];


  $ImgInfo=false;

  if ($qimg)
    {


    if ($EditMode)
      {
      $select="";
      if ($qf)
        {foreach ($qf->Rows as $vTnFormatNo=>$row)
         {
         if ($vTnFormatNo==0) continue;
         $sel=($TnFormatNo==$vTnFormatNo)?"checked":"";
         $select.="<tr><td><input onClick='selecttn.submit();' type='radio' name='viewformatno' value='$vTnFormatNo' $sel></td><td class='tiny'>".langstr_get($row->Caption)."</td></tr>";
         }
        } else {print_developer_warning("Undefined image formats");}
      if ($select) $select="<table cellpadding=0 cellspacing=0>$select</table>";
      if ($select) {
        unset ($GLOBALS['Args']['viewformatno']);
        $select="<form style='padding:0;margin:0;' name='selecttn' method='get'>
        <b class='tiny'>$_[SELECT_THUMBNAIL_TO_VIEW]:</b>
        <input type='hidden' name='ArgsStr' value='".$_ENV->Serialize($GLOBALS['Args'])."'>$select</form>";
        }
      }

    $imgfolder='img/'.$BindToInfo->Folder;
    if ($ShowCaption) {
    	$Tn_Caption=nl2br(langstr_get($imgdoc->Caption));
    } else $Tn_Caption="";
    
    $Filenames=$_ENV->Unserialize($imgdoc->Filenames);
    $ImgFileName=basename($Filenames[$TnFormatNo]);
    $ImgURL= $cfg['FilesURL']. '/'.$imgfolder.$ImgFileName;
    $ImgFile=$cfg['FilesPath'].'/'.$imgfolder.$ImgFileName;
    if (($ImgFileName) &&  (is_file($ImgFile))) $ImgInfo=@getimagesize($ImgFile);

    if ($ImgInfo)
      {
      $Tn_Tag="<img border='0' src='$ImgURL' $ImgInfo[3]/>";
      if ($EditMode)
        {
        $url=ActionURL("img.IImage.Edit.f",array(ImgID=>$imgdoc->ImgID,BindTo=>$imgdoc->BindTo));
        $Tn_Tag="<a href='javascript:;'
          onClick=\"W.openModal({url:'$url',reloadOnOk:1,Title:'$_[IMGCONTAINER_EDITCONTENT]'});\">$Tn_Tag</a>";
        }
      elseif ($OnClickURL)
        {
        $Tn_Tag="<a href='$OnClickURL'>$img</a>";
        }

      if ($ImageStyle)
        {
        $Style=$_THEME['ImageStyles'][$ImageStyle];
        if ($Style)
          {
          if (isset($Style['HTML']))
            {
            $Tn_Tag=preg_replace(array("'#IMAGE#'i","'#CAPTION#'i"),array($Tn_Tag,$Tn_Caption),$Style['HTML']);
            }
          elseif ($Style['CSS_Cell'])
            {
            if ($ShowCaption && $Tn_Caption)
              {
              list ($Caption_tag,$Caption_CSS)=get_css_pair ($Style['CSS_Caption'],"p");
              $Tn_Caption="<$Caption_tag $Caption_CSS>$Tn_Caption</$Caption_tag>";
              }
            list ($Border_tag,$Border_class)=get_css_pair ($Style['CSS_Cell'],"td");
            $Tn_Tag="<table cellpadding='0' border='0' cellspacing='0' border='0'><tr><$Border_tag $Border_class>$Tn_Tag</td></tr></table>$Tn_Caption";
            }
          }
        }
      else
        {
        if ($Tn_Caption) $Tn_Tag.="<p>$Tn_Caption</p>";
        }
      print $Tn_Tag;


      if ($select)
        {
        print "$select";
        }
      }
    }

  if (!$ImgInfo)
    {
    if ($EditMode)
      {
      $addimgscript="W.openModal({url:\"".ActionURL("img.IImage.Edit.f",array(BindTo=>$BindTo))."\",reloadOnOk:1})";
      $s1="<table><tr><$Border_tag $Border_CSS><a href='javascript:;' onClick='$addimgscript'>
      <img border='0' src='$cfg[PublicURL]/img/dummy_img.gif' width='$Format->Width' height='$Format->Height'/></a></$Border_tag></td></tr></table>";
      $s1.="<$Caption_tag $Caption_CSS>$_[IMAGE_DUMMYCAPTION]</$Caption_tag>";
      print $s1;
      }
    else
      {
      if ($ReplaceEmptyImg)
        {
        global $_THEME_NAME;
        $u="$cfg[SkinsURL]/$_THEME_NAME/$ReplaceEmptyImg";
        $p="$cfg[SkinsPath]/$_THEME_NAME/$ReplaceEmptyImg";
        if (is_file($p))
          {
          $sz=@getimagesize($p);
          print "<img $sz[3] src='$u'>";
          }
        }
      }
    }
  }


function Regenerate($args)
  {
  extract(param_extract(array(
    BindTo=>"string",  # regenerates all images bound via BindTo
    ImgID=>"int",      # if ImgID defined: regenerate only one image that bound via BindTo
    FormatID=>"int",   # if FormatID defined: regenerate all images of this format
    OnlyTnFormatNo=>"int", # shrink regeneration of FormatID to only one TnFormatNo
    InFrame=>"int",
    Offset=>"int",
    ),$args));


  global $cfg,$_THEME_NAME;
  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;
  $_ENV->UnlockTwicePost();
  $_ENV->DropCache();

  if ((!$InFrame)&&(!$ImgID))  # do not show progressive regenerating iframe
    {
    ?>
    <center>
    <br>
    <? $_ENV->PutProgress("p1","Images are regenerating"); ?>
    <div id='msg' align='center'></div>
    <script>
    var ErrorsDetected=false;
    Progress_Start("p1");
    W.setErrorHandler(onError);
    function onError(error,details)
      {
      showFrame();
      ErrorsDetected=true;
      document.getElementById('msg').innerHTML="<font color='red'>Error: <b>"+error+"</b></font><br>"+details;
      }
    function showFrame()
      {
      document.getElementById('framecontainer').style.display='block';
      W.setSize(500,460);
      }
    function continueIteration(jump)
      {
      Progress_Continue("p1");
      ErrorsDetected=false;
      document.getElementById('f1').src=jump;
      document.getElementById('msg').innerHTML='';
      }
    function updateInfo (offset,total,complete,jump)
      {
      if ((complete)&&(!ErrorsDetected)) {W.modalResult("ok");return;}
      var s=offset+"/"+total+" images regenerated";
      Progress_NewPos('p1',Math.round(offset/total*100),s);

      if (ErrorsDetected)
        {
        Progress_Pause("p1");
        showFrame();
        s="<br><font color='red'>Errors found!";
        if (!complete) s+="<br><input type='button' class='button' value='Continue' onClick='continueIteration(\""+jump+"\")'></font>";
        document.getElementById('msg').innerHTML=s;
        }
      if (!ErrorsDetected) {continueIteration(jump);}

//        document.getElementById('msg').innerHTML+="<br>I wish jump to<br>"+jump;
      }
    </script>
    <?
    print "<br>
      <div id='framecontainer' style='displ ay:none; text-align:center; width:100%'>
      <iframe id='f1' width='450' height='250' src='".ActionURL("img.IImage.Regenerate.b",
      array(BindTo=>$BindTo,
      ImgID=>$ImgID,
      FormatID=>$FormatID,
      OnlyTnFormatNo=>$OnlyTnFormatNo,
      InFrame=>1,
      Offset=>$Offset))."'></iframe></div>";
    return;
    }

  print "<font style='font-size:10px'>";
  $EndingTime=time()+3;

  $r=$this->_getFormatInfo(array(BindTo=>$BindTo,FormatID=>$FormatID));
  if ($r['Error'])
    {
    return array(Error=>$r['Error'],Details=>$r['Details']);
    }

  $BindToInfo=BindPathInfo($BindTo);
  $BindToID=$BindToInfo->ID;
  $qf=&$r['qf'];
  $FormatID=$r['FormatID'];
  if ($BindTo)
    {
    if ($BindToID) {
      $criteria=" BindTo='$BindToInfo->Folder$BindToID'";
      }
    else
      {
      $criteria=" BindTo LIKE '$BindToInfo->Folder%'";
      }
    if ($ImgID) {$criteria="ImgID=".intval($ImgID)." AND $criteria";}
    }
  elseif ($FormatID)
    {
    $criteria="FormatID=$FormatID";
    }

  if (!$criteria)
    {
    return array(Error=>"Not enough arguments in calling IImage.Regenerate");
    }
  $s="SELECT COUNT(*) AS RowCount FROM img_Documents WHERE $criteria";
#    print_error ($s);
  $qcount=DBQuery ($s);
  $Total=$qcount->Top->RowCount;

  $L=" LIMIT $Offset,100";
  $s="SELECT * FROM img_Documents WHERE $criteria ORDER BY ImgID $L";
#    print_error ($s);
  $qimg=DBQuery ($s,"ImgID");

  if ($qimg)
    {
    foreach ($qimg->Rows as $aImgID=>$ImgDoc)
      {
      $Offset++;
      $aBindTo=$ImgDoc->BindTo;
      $BindToInfo=BindPathInfo($aBindTo);
      $TargetPath=$cfg['FilesPath'].'/img/'.$BindToInfo->Class;
      if ($BindToInfo->Context) {$TargetPath.='/'.$BindToInfo->Context;}
      $Focuses=explode ("f",$ImgDoc->Focuses);
      $Filenames=($ImgDoc->Filenames)?$_ENV->Unserialize($ImgDoc->Filenames):"";
      if (substr($Filenames[0],-4)=='.swf'){
        continue;
        }

      $SrcFileName=$TargetPath.'/'.basename($Filenames[0]);

      # remove old thumbnails if not only one format specified!!
      if (($Filenames)&&(!$OnlyTnFormatNo))
        {
        for ($i=1;$i<count($Filenames);$i++)
          {
          if (strpos($Filenames[$i],'.img.')!==false) continue;
          $f=$TargetPath.'/'.basename($Filenames[$i]);
          if (($f)&&(is_file($f))) {unlink ($f);}
          }
        }

      list ($Img_w,$Img_h,$Img_type,$Img_attr)=@getimagesize ($SrcFileName);
      switch ($Img_type)
        {
        case 1: $im=@imagecreatefromgif ($SrcFileName); break;
        case 2: $im=@imagecreatefromjpeg($SrcFileName); break;
        case 3: $im=@imagecreatefrompng ($SrcFileName); break;
        }

      foreach ($qf->Rows as $TnFormatNo=>$TnFormat)
        {
        if ($TnFormatNo==0)
          {continue;}
        if (($TnFormat->ResizeOption=='img')&&(strpos($Filenames[$TnFormatNo],'.img.')!==false))
          {continue;} # if thumbnail was uploaded

        if (($OnlyTnFormatNo)&&($TnFormatNo!=$OnlyTnFormatNo)) {continue;}
        $Tn_w=$TnFormat->Width;
        $Tn_h=$TnFormat->Height;
        $FocusX   =intval($Focuses[($TnFormatNo-1)*3]);
        $FocusY   =intval($Focuses[($TnFormatNo-1)*3+1]);
        $FocusSize=intval($Focuses[($TnFormatNo-1)*3+2]); if (!$FocusSize) {$FocusSize=100;}

        $src_w=$Img_w; $src_h=$Img_h;
        $aspect=$Img_w/$Img_h;
        $dst_w=$TnFormat->Width; $dst_h=$TnFormat->Height;
        $tnaspect=$dst_w/$dst_h;
        $dst_x=$dst_y=$src_x=$src_y=0;

        # Use JPG format if GIF not supported
        if (($TnFormat->ImgType==1)&&(!(imagetypes()&IMG_GIF ))) $TnFormat->ImgType=2;
        # Use JPG format if PNG not supported
        if (($TnFormat->ImgType==3)&&(!(imagetypes()&IMG_PNG ))) $TnFormat->ImgType=2;
        # Use GIF format if JPG not supported
        if (($TnFormat->ImgType==3)&&(!(imagetypes()&IMG_JPG ))) $TnFormat->ImgType=1;

        switch ($TnFormat->ResizeOption)
          {
          case 'color':
            if ($tnaspect>$aspect)
              {
              # Превьюшка шире по горизонтали чем исходное изображение
              $dst_h=$TnFormat->Height/($FocusSize/100);  # ($FocusSize/100) = 0.01 .... 1
              $dst_w=$dst_h*$aspect;
              $dst_x=($TnFormat->Width -$dst_w)*(($FocusX+50)/100);
              $dst_y=($TnFormat->Height-$dst_h)*(($FocusY+50)/100);
              }
            else
              {
              # Превьюшка выше по вертикали чем исходное изображение
              $dst_w=$TnFormat->Width/($FocusSize/100);  # ($FocusSize/100) = 0.01 .... 1
              $dst_h=$dst_w/$aspect;
              $dst_x=($TnFormat->Width -$dst_w)*(($FocusX+50)/100);
              $dst_y=($TnFormat->Height-$dst_h)*(($FocusY+50)/100);
              }
            break;

          case 'crop':
          # Crop thumbnail downto [TnWidth x TnHeight]
            if ($tnaspect>$aspect)
              {
              # Превьюшка шире по горизонтали чем исходное изображение
              # значит обрезаем по вертикали и смотрим valign
              $src_w=floor($Img_w*$FocusSize/100);
              $src_h=floor($src_w/$tnaspect);
              }
            else
              {
              # Превьюшка выше по вертикали чем исходное изображение
              $src_h=floor($Img_h*$FocusSize/100);
              $src_w=floor($src_h*$tnaspect);
              }
            $src_x=floor(($Img_w-$src_w)*(($FocusX+50)/100));
            $src_y=floor(($Img_h-$src_h)*(($FocusY+50)/100));
          break;

        case 'fit': case 'img':
          $dst_w=$src_w;
          $dst_h=$src_h;
          if ($dst_w>$TnFormat->Width)
            {
            $dst_w=$TnFormat->Width;
            $dst_h=floor($dst_w / $aspect);
            }
          if ($dst_h>  $TnFormat->Height)
            {
            $dst_h=$TnFormat->Height;
            $dst_w=floor($dst_h * $aspect);
            }
          $TnFormat->Height=$dst_h;
          $TnFormat->Width=$dst_w;
          break;

        case 'expand':
          if ($tnaspect>$aspect)
            {
            $TnFormat->Width=$dst_w=floor($dst_h * $aspect);
            }
          else
            {
            $TnFormat->Height=$dst_h=floor($dst_w / $aspect);
            }
          break;
        }

        if (function_exists("imagecreatetruecolor") && ($TnFormat->ImgType!=1))
             $im2=@imagecreatetruecolor ($TnFormat->Width,$TnFormat->Height);
        else $im2=@imagecreate ($TnFormat->Width,$TnFormat->Height);

        if ($TnFormat->BgColor)
          {
          $rgb=sscanf($TnFormat->BgColor,"#%02x%02x%02x");
          $res_bgcolor=imagecolorallocate($im2,$rgb[0],$rgb[1],$rgb[2]);
          imagefilledrectangle($im2, 0, 0, $TnFormat->Width,$TnFormat->Height , $res_bgcolor);
          }


        if (function_exists("imagecopyresampled"))
             imagecopyresampled ($im2,$im,$dst_x,$dst_y, $src_x,$src_y, $dst_w, $dst_h, $src_w, $src_h);
        else imagecopyresized   ($im2,$im,$dst_x,$dst_y, $src_x,$src_y, $dst_w, $dst_h, $src_w, $src_h);

        if ($TnFormat->Watermark)
          {
          $Opacity=$TnFormat->WatermarkOpacity;
          if (!$Opacity) {$Opacity=100;}
          if (isset($this->WatermarksLoaded[$TnFormat->Watermark]))
            {
            $imw=$this->WatermarksLoaded[$TnFormat->Watermark];
            $winfo=$this->WatermarksSizes[$TnFormat->Watermark];
            }
          else
            {
            $filename=$cfg['ThemesPath'].'/'.$_THEME_NAME.'/'.$TnFormat->Watermark;
            if (file_exists($filename))
              {
              $winfo=@getimagesize($filename);
              $imw=false;
              switch ($winfo[2])
                {
                case 1: $imw=imagecreatefromgif ($filename);  break;
                case 2: $imw=imagecreatefromjpeg($filename); break;
                case 3: $imw=imagecreatefrompng ($filename);
                }
              $this->WatermarksLoaded[$TnFormat->Watermark]=$imw;
              $this->WatermarksSizes[$TnFormat->Watermark]=$winfo;
              }
              else {print_error("Watermark image file not found","$filename");}
            }
          if ($imw) imagecopy($im2,$imw,$TnFormat->Width-$winfo[0],$TnFormat->Height-$winfo[1],0,0,$winfo[0],$winfo[1]);
          }

        switch ($TnFormat->ImgType)
          {
          case 1:
            $DestTnFile=$BindToInfo->ID.'_'.$TnFormatNo.'_'.substr(md5(uniqid(rand())),0,7).'.gif';
            $f=$TargetPath.'/'.$DestImageFile;
            trace ("gif: $f");
            $ok=imagegif ($im2,$f); break;
          case 3:
            $DestTnFile=$BindToInfo->ID.'_'.$TnFormatNo.'_'.substr(md5(uniqid(rand())),0,7).'.png';
            $f=$TargetPath.'/'.$DestImageFile;
            trace("png: $f");
            $ok=imagepng ($im2,$f); break;
          default:
            $DestTnFile=$BindToInfo->ID.'_'.$TnFormatNo.'_'.substr(md5(uniqid(rand())),0,7).'.jpg';
            $f=$TargetPath.'/'.$DestTnFile;
            trace ("jpg: $f");
            $ok=imagejpeg($im2,$f,$TnFormat->Quality);
          }

        imagedestroy($im2);
        if (!$ok)
          {
          print_error("Unable to create image file",$f);
          }

        if (($OnlyTnFormatNo)&&($Filenames[$TnFormatNo]))
          { # if we regenerate only one format
          $old=$TargetPath.'/'.basename($Filenames[$TnFormatNo]);
          trace ("unlink ($old)");
          if (($f)&&(is_file($old))) {unlink ($old);}
          }
        $Filenames[$TnFormatNo]=$DestTnFile;
        chmod ($f,$filemode);
        }
      imagedestroy($im);
      trace ($Filenames,2);
      $Filenames=$_ENV->Serialize($Filenames);
      trace ($Filenames,2);
      DBExec ("UPDATE img_Documents SET Filenames='$Filenames' WHERE ImgID=$aImgID AND BindTo='$aBindTo'");
      if (time()>$EndingTime) break;
      } # end of loop over images

    $errfound=0;

    if (!$ImgID)
      {
      if ($Offset<$Total)
        {
        $url=ActionURL("img.IImage.Regenerate.b",
          array(BindTo=>$BindTo,ImgID=>$ImgID,FormatID=>$FormatID,OnlyTnFormatNo=>$OnlyTnFormatNo,
          InFrame=>1,Offset=>$Offset));
        print "<hr><a href='$url'>Continue</a><script>parent.updateInfo($Offset,$Total,0,'$url');</script>";
        return;
        }
      else
        {
        print "<script>parent.updateInfo ($Total,$Total,1); </script>";
        }
      }
    }
  }


function Remove_BoundToObject($args)
  {
  $this->Delete($args);
  }

function Delete($args)
  {
  extract(param_extract(array(
    ID=>"string",
    BindTo=>"string",  # used by Remove_BoundToObject
    img_selected=>"int_checkboxes",
    ),$args));
  global $cfg;


  if ($img_selected)
    {
    $criteria="ImgID IN (".implode (",",array_keys($img_selected)).") ";
    }
  elseif ($ID)
    {
    $criteria="ImgID=$ID ";
    }
  elseif ($BindTo)
    {
    $criteria="BindTo='$BindTo'";
    }
  else
    {
    return array(ModalResult=>true);
    }

  $qimg=DBQuery ("SELECT ImgID,Filenames,BindTo FROM img_Documents WHERE $criteria","ImgID");
  if ($qimg)
    {
    foreach ($qimg->Rows as $ImgID=>$ImgDoc)
      {
      # Remove image and thumbnail
      $BindToInfo=BindPathInfo($ImgDoc->BindTo);
      if (!$BindToInfo) {continue;}
      $TargetPath=$cfg['FilesPath'].'/img/'.$BindToInfo->Folder;
      $OldNames=explode_properties($ImgDoc->Filenames);
      if ($OldNames)
        {
        foreach ($OldNames as $Filename)
          {
          $f=$TargetPath.basename($Filename);
          if (is_file($f))
            {
            if (unlink ($f)) trace ("Unlink '$f'");
            else print_error ("Cannot unlink image","'$f'");
            }
          }
        }
      $s="DELETE FROM img_Documents WHERE ImgID=$ImgID";
      DBExec ($s);
      }
    }
  return array(ModalResult=>'ok');
  }


function Edit($args)
  {
  extract(param_extract(array(
    Focuses=>"string",
    ImgID=>"int",
    BindTo=>"string",
    ),$args));

  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['img'];
  $_ENV->UnlockTwicePost();


  global $cfg,$_THEME;

  if ($ImgID) {
    print "<script>PU.on('resize',onImageLoad);</script>";
    $_ENV->SetWindowOptions (array(Width=>800,Height=>650));
  } else {
    $_ENV->SetWindowOptions (array(Width=>500,Height=>200));
    }

  print "<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'><tr><td>";
  $ImageCaption="";
  if ((!$BindTo)&&($ImgID)) {
    $qi=DBQuery("SELECT BindTo FROM img_Documents WHERE ImgID=$ImgID");
    if ($qi) {$BindTo=$qi->Top->BindTo;}
    }
  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo)
    {
    return array(Error=>$_['TIMAGE_BAD_BINDING']);
    }
  $BindToID=intval($BindToInfo->ID);
  $ImageFolder='img/'.$BindToInfo->Folder; # has ending slash!

  $r=$this->_getFormatInfo(array(BindTo=>$BindTo));
  if ($r['Error']) return $r;
  $FormatID=$r['FormatID'];
  $qf=&$r['qf'];
  $NoCaption=$qf->Rows[0]->NoCaption;
  $CanHasURL=$r['CanHasURL'];
  $ss="";
  $FormatsValue="";
  $checkset=false;
  $i=0;
  foreach ($qf->Rows as $TnFormatNo=>$Format)
    {
    if ($TnFormatNo==0) {continue;}
    $s2=$s=$c="";  if (!$checkset) {$c="checked"; $checkset=true;}
    $s2=" onMouseOver='checkOver(this)' onMouseDown='checkSelect($i)' onMouseOut='checkOut(this)'";
    $s="<input type='radio' onClick='DragMode=\"init\";body_OnMouseMove();' name='selectFormat' value='$TnFormatNo' $c>$TnFormatNo:";
    $ss.="<th $s2>".langstr_get($Format->Caption)." ($Format->ResizeOption)<br>$s$Format->Width x $Format->Height</th>";
    if ($FormatsValue) $FormatsValue.="x";
    $FormatsValue.=$Format->Width.'x'.$Format->Height.'x'.$Format->ResizeOption;
    $i++;
    }
  // $MaxFormatID .eq. $FormatID
  $ss="<table width='100%' border=0>
      <tr><th colspan='5'>$_[TIMAGE_C_THUMBNAILDIMENSIONS]:</th></tr>
      <tr>$ss</tr></table>";
?>
<script>
function callBack_FileUploaded(mr)
  {
  location.href=mr.ForwardTo;
  }
function OnFileSelected()
  {
  ifrm.target=W.openModal({callback:'callBack_FileUploaded'});
  if (ifrm.btn1) ifrm.btn1.disabled=true;
  if (ifrm.btn2) ifrm.btn2.disabled=true;
  ifrm.submit();
  }
function DoUploadFormWoWindow()
  {
  if (ifrm.btn1) ifrm.btn1.disabled=true;
  if (ifrm.btn2) ifrm.btn2.disabled=true;
  ifrm.submit();
  }
</script>
  <center>

<?

  $_ENV->OpenForm(array(Name=>'ifrm',Enctype=>'multipart/form-data',Style=>"clear",HideSubmit=>1,
    Action=>ActionURL('img.IImage.AcceptEdit.b')));
  $_ENV->PutFormField(array(Name=>"BindTo",Type=>"hidden",Value=>$BindTo));
  $_ENV->PutFormField(array(Name=>"ImgID",Type=>"hidden",Value=>$ImgID));

  $VarFilenames="false";
  if ($ImgID)
    {
    $q=DBQuery ("SELECT * FROM img_Documents WHERE ImgID=$ImgID AND BindTo='$BindTo'");
    if ($q)
      {
      $imgdoc=$q->Top;
      $Filenames=$_ENV->Unserialize($imgdoc->Filenames);
      $Img_file=$cfg['FilesPath'].'/'.$ImageFolder.$Filenames[0];
      $Img_url= $cfg['FilesURL']. '/'.$ImageFolder.$Filenames[0];
      $VarFilenames="";
      foreach ($Filenames as $FileNo=>$Filename)
        {
        $VarFilenames.=(($VarFilenames)?",":"")."$FileNo:'$Filename'";
        }
      if ($VarFilenames)
        {
        $VarFilenames='{'.$VarFilenames.'}';
        }
      else
        {
        $VarFilenames="false";
        }
      }

    if ($Img_file && (file_exists($Img_file)))
      {
/* <input type='hidden' name='Focuses'   value='<? print $Focuses; ?>'>
<input type='hidden' name='ImgWidth'  value='<? print $w; ?>'>
<input type='hidden' name='ImgHeight' value='<? print $h; ?>'>
<input type='hidden' name='TnFormats' value='<? print $FormatsValue; ?>'><br>
*/


      $Focuses=$imgdoc->Focuses;
      $ImageCaption=$imgdoc->Caption;
      $OrderNo=intval($imgdoc->OrderNo);
      $Tn_info=getimagesize($Img_file);
      if (!$Tn_info)
        {
        print "Unable to aqcuire image info [$Img_file]";
        }
      $w=$Tn_info[0]; $h=$Tn_info[1];
      $aspect=$w/$h;
      if ($w>480) {$w=480; $h=round($w/$aspect);}
      if ($h>300) {$h=300; $w=round($h*$aspect);}
      $_ENV->PutFormField(array(Name=>"Focuses",Type=>"hidden",Value=>$Focuses));
      $_ENV->PutFormField(array(Name=>"ImgWidth",Type=>"hidden",Value=>$w));
      $_ENV->PutFormField(array(Name=>"ImgHeight",Type=>"hidden",Value=>$h));
      $_ENV->PutFormField(array(Name=>"TnFormats",Type=>"hidden",Value=>$FormatsValue));

      print "
        <div style='position:absolute; cursor:move;' id='focus'><img src='$cfg[PublicURL]/img/focus.gif' width='32' height='32'></div>
        <div style='position:absolute; cursor:nw-resize;' id='crop_tl'><img src='$cfg[PublicURL]/img/crop_tl.gif' width='16' height='16'></div>
        <div style='position:absolute; cursor:ne-resize;' id='crop_tr'><img src='$cfg[PublicURL]/img/crop_tr.gif' width='16' height='16'></div>
        <div style='position:absolute; cursor:se-resize;' id='crop_br'><img src='$cfg[PublicURL]/img/crop_br.gif' width='16' height='16'></div>
        <div style='position:absolute; cursor:sw-resize;' id='crop_bl'><img src='$cfg[PublicURL]/img/crop_bl.gif' width='16' height='16'></div>
        ";
?>
<script>
var DragMode='disabled',StartDragX,StartDragY,StartFocusX,StartFocusY,StartFocusSize;
var dom = (document.getElementById)?true:false;
var SizeOption;
var ImagesURL='<? print "$cfg[FilesURL]/$ImageFolder"; ?>';
var DummyURL='<? print "$cfg[PublicURL]/img/dummy_img.gif" ?>';
var Filenames=<? print $VarFilenames; ?>;
document.onmousedown=body_OnMouseDown;
document.onmousemove=body_OnMouseMove;
document.onmouseup=body_OnMouseUp;
document.ondragstart=rfalse;
function rfalse() {event.returnValue = false;}
function OnTnFileSelected()
  {
  tnfrm.target=W.openModal({reloadOnOk:1});
  tnfrm.submit();
  }

function checkOver(cell)
  {
  cell.oldbg=cell.style.backgroundColor;
  cell.style.backgroundColor='#ffdd88';
  }
function checkOut(cell)
  {
  cell.style.backgroundColor=cell.oldbg;
  }
function checkSelect(checkNo)
  {
  ifrm.selectFormat[checkNo].click();
  }
function body_OnMouseDown(e)
  {
  if (!e) {try{if (event)e=event;}catch(z){}}
  if (!e) {return;}
  if (DragMode=='disabled') {return;}

  var el=(document.all)?e.srcElement:e.target;
  var p=(document.all)?el.parentElement:el.parentNode;
  var s="crop_tl|crop_tr|crop_bl|crop_br|focus";
  if ((p.id) && (s.indexOf(p.id)!=-1))
    {
    DragMode=p.id;
    StartDragX=e.screenX;
    StartDragY=e.screenY;

    var formatSelected=0,i;
    for (i=0;i<ifrm.selectFormat.length;i++) { if (ifrm.selectFormat[i].checked) {break;}}
    var fa=ifrm.Focuses.value.split("f");
    StartFocusX=Int(fa[i*3]);
    StartFocusY=Int(fa[i*3+1]);
    StartFocusSize=Int(fa[i*3+2]);
    }
  if (!document.all) {return false;}
  e.returnValue=false;
  return e;
  }

function body_OnMouseUp()
  {
  if (DragMode=='disabled') {return;}
  DragMode=false;
  }
function Int(v) {v=parseInt(v); return (v)?v:0;}

function ShowUploadTnForm(show,cw,ch,TnFileSrc,formatSelected)
  {
  var imgsrc,imgsrcobj;
  imgvis=(show)?"hidden":"visible";
  formvis=(show)?"visible":"hidden";
  P$.find('UploadTnForm').style.visibility=formvis;
  P$.find('source_image_div').style.visibility=imgvis;

  if (show)
    {
    Tn_Tag="";
    imgsrc=(TnFileSrc)?(ImagesURL+TnFileSrc):DummyURL;
    if (imgsrc.substr(imgsrc.length-4,4)=='.swf')
      {
      Tn_Tag="<OBJECT classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width="+cw+" height="+ch+">"
      +"<PARAM NAME=movie VALUE='"+imgsrc+"'><PARAM NAME=quality VALUE=best><PARAM NAME=wmode VALUE=opaque>"
      +"<EMBED src='"+imgsrc+"' quality=best wmode=opaque width="+cw+" height="+ch+" TYPE='application/x-shockwave-flash'></EMBED>"
      +"</OBJECT>";
      }
    else
      {
      Tn_Tag="<img src='"+imgsrc+"' />";
      }
    P$.find('divtn').innerHTML="<table border='1' cellspacing='0' cellpadding='1'><tr><td bgcolor='#808080'>"+Tn_Tag+'</td></tr></table><br/>Recommended thumbnail size:<b>'+cw+'x'+ch+'</b> pixels';
    tnfrm.TnFormatNo.value=formatSelected;
    }
  }
function ShowCropper(show)
  {
  show=(show)?"visible":"hidden";
  P$.find('crop_tl').style.visibility=show;
  P$.find('crop_tr').style.visibility=show;
  P$.find('crop_bl').style.visibility=show;
  P$.find('crop_br').style.visibility=show;
  P$.find('focus').style.visibility=show;
  }
function body_OnMouseMove(e)
  {
  if (!e) {try{if (event)e=event;}catch(z){}}

  if (DragMode=='disabled') {return;}
  if (!DragMode) {return;}
  if (DragMode=='init') {DragMode=false;} //else {if ((e)&&(!e.button)) {DragMode=false;}}

  var si=P$.find('source_image'),formatSelected=0,formats=ifrm.TnFormats.value.split("x"),i,v,s,formatCount;
  var iw,ih,boxw,boxh, cx,cy, dsize=false,dx,dy,prevfsize,img_aspect;
  if (si)
    {
    img_aspect=si.width/si.height;
    }
  iw=ifrm.ImgWidth.value;
  ih=ifrm.ImgHeight.value;

  var formatCount=formats.length/3;

  for (i=0;i<formatCount;i++) { if (ifrm.selectFormat.length) if (ifrm.selectFormat[i].checked) {break;}}
  if (i==formatCount) {i=0;}
  formatSelected=i;

  var cw=Int(formats[i*3]),ch=Int(formats[i*3+1]),tn_aspect=cw/ch,fa=ifrm.Focuses.value.split("f"),
    fx=Int(fa[i*3]),fy=Int(fa[i*3+1]),fsize=Int(fa[i*3+2]);
  SizeOption=formats[i*3+2];
  if (!fsize) {fsize=100;}

  if (DragMode)
    {
    dx=e.screenX-StartDragX;
    dy=e.screenY-StartDragY;
    }

  if (DragMode=='crop_tl') dsize=(dx<dy)?-dy:-dx;
  if (DragMode=='crop_tr') dsize=(-dx>dy)?dx:-dy;
  if (DragMode=='crop_br') dsize=(dx<dy)?dx:dy;
  if (DragMode=='crop_bl') dsize=(dx>-dy)?-dx:dy;

  boxw=iw*fsize/100; boxh=boxw/tn_aspect;
  if (SizeOption=='img')
    {
    if (Filenames) TnFileSrc=Filenames[formatSelected+1];
    ShowCropper(false);
    ShowUploadTnForm(true,cw,ch,TnFileSrc,formatSelected+1);
    return;
    }

  if (!si)
    {
    ShowCropper(false);
    ShowUploadTnForm(false);
    return;
    }
  ShowUploadTnForm(false);

  if (SizeOption=='fit')
    {
    ShowCropper(false);
    return;
    }

  ShowCropper(true);
  if (dsize)
    {
    prevfsize=fsize;
    fsize=Int(StartFocusSize+(dsize/iw*100));
    fsize=(fsize<1)?1:(fsize>100)?100:fsize;
    if (img_aspect>tn_aspect) {boxh=ih*fsize/100; boxw=boxh*tn_aspect;}
      else {boxw=iw*fsize/100; boxh=boxw/tn_aspect;}
    if (boxw<20) {boxw=20; fsize=prevfsize;}
    if (boxh<20) {boxh=20; fsize=prevfsize;}
    }


  if (SizeOption=='crop')
    {
    if (img_aspect>tn_aspect) {boxh=ih*fsize/100; boxw=boxh*tn_aspect;} else {boxw=iw*fsize/100; boxh=boxw/tn_aspect;}
    }
  if (SizeOption=='color')
    {
    if (img_aspect>tn_aspect)  {boxw=iw*fsize/100; boxh=boxw/tn_aspect;} else {boxh=ih*fsize/100; boxw=boxh*tn_aspect;}
    }

  if (DragMode=='focus')
    {
    if (iw!=boxw)
      {
      fx=StartFocusX+parseInt((iw-boxw+dx)/(iw-boxw)*100-100);
      fx=(fx<-50)?-50:(fx>50)?50:fx;
      }
    if (ih!=boxh)
      {
      fy=StartFocusY+parseInt((ih-boxh+dy)/(ih-boxh)*100-100);
      fy=(fy<-50)?-50:(fy>50)?50:fy;
      }
    }

  for (i=0;i<formatCount;i++)
    {
    fa[i*3]=Int(fa[i*3]); fa[i*3+1]=Int(fa[i*3+1]); v=Int(fa[i*3+2]); if (!v) v=100; fa[i*3+2]=v;
    }
  i=formatSelected; fa[i*3]=fx; fa[i*3+1]=fy; fa[i*3+2]=fsize;

  s="";
  for (i=0;i<formatCount;i++)
    {
    if (s!="") s+="f";
    s+=""+fa[i*3]+"f"+fa[i*3+1]+"f"+fa[i*3+2];
    }

  ifrm.Focuses.value=s;
  cx=si.offsetLeft+si.offsetParent.offsetLeft+((fx+50)/100)*(iw-boxw)+boxw/2;
  cy=si.offsetTop+si.offsetParent.offsetTop +((fy+50)/100)*(ih-boxh)+boxh/2;

  P$.find('crop_tl').style.left=cx-boxw/2; P$.find('crop_tl').style.top=cy-boxh/2;
  P$.find('crop_tr').style.left=cx+boxw/2-16; P$.find('crop_tr').style.top=cy-boxh/2;
  P$.find('crop_bl').style.left=cx-boxw/2; P$.find('crop_bl').style.top=cy+boxh/2-16;
  P$.find('crop_br').style.left=cx+boxw/2-16; P$.find('crop_br').style.top=cy+boxh/2-16;
  P$.find('focus').style.left=cx-16; P$.find('focus').style.top=cy-16;
  }

function onImageLoad()
  {
  DragMode='init';
  body_OnMouseMove();
  DragMode=false;
  }

</script>
<?
      print "<div id='source_image_div' style='width:100%;'>";
      if (substr($Img_url,-4)=='.swf')
        {
        $_ENV->PutSwf(array(Id=>'source_image',SWF=>$Img_url,Width=>$w,Height=>$h));
        }
      else
        {
        print "<img id='source_image' src='$Img_url' width='$w' height='$h' ismap>";
        }

      print "</div><br/>";
      }
    } # if ImgID
  else
    {
    $ss="";
    $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM img_Documents WHERE BindTo='$BindTo'");
    $OrderNo=intval($q->Top->MaxOrderNo)+10;
    $_ENV->PutFormField(array(Name=>"OrderNo",Type=>"hidden",Value=>$OrderNo));
    }

  print "</td></tr><tr><td>$ss";
  print "<center><table width='100%' border='0' cellpadding='10'><tr valign='top'><td class='bgdown'>";
  if ($ImgID) print $_['IIMAGE_CLICK_BROWSE_TO_UPDATE']; else print $_['IIMAGE_CLICK_BROWSE_TOADDNEW'];
  print "<table><tr><td class='button'>max=";
  print get_post_max_size();
  
  print "<input type='file' onChange='OnFileSelected();' class='button' name='newimg' style='border:none; height:15px; width:20px' ></td></tr></table><br>";

  if ($ImgID)
    {
    $_ENV->PutFormField(array(Name=>"Caption",Value=>$ImageCaption,
      Style=>'vertical',Caption=>$_['TIMAGE_LABEL'], Type=>"langtext",Size=>60, Rows=>3));
    print "</td><td class='bgdown'>";
    if ($CanHasURL)
      {
      $_ENV->PutFormField(array(Type=>'inputmodal',Editable=>1,Caption=>$_['IMAGE_TARGETURL'],
        Style=>'vertical',
        Name=>'TargetURL',
        Value=>$imgdoc->TargetURL,
        InitCall=>"jsb.IPage.GetPageNameByURLValue",
        ModalCall=>ActionURL('jsb.IPage.SelectPageOrURL.b')));
      }
    $_ENV->PutFormField(array(Type=>'int',Caption=>$_['TIMAGE_ORDERNO'],Style=>'vertical',Value=>$OrderNo,Name=>'OrderNo'));
    }
  print "<tr><td colspan='2' align='right'>";
  if ($ImgID) {$_ENV->PutButton(array(Name=>'btn1',OnClick=>'DoUploadFormWoWindow()', Kind=>'ok'));}
  $_ENV->CloseForm();
  ?></td></tr></table></form></td></tr></table>
  <div id='UploadTnForm' style='position:absolute; left:0; top:0; visibility:hidden; text-align:center; width:100%' ><br/>
  <form name='tnfrm' method='post' ENCTYPE='multipart/form-data' action="<? print ActionURL('img.IImage.AcceptTnImage.b'); ?>">
  <input type='hidden' name='BindTo' value='<? print $BindTo;?>'>
  <input type='hidden' name='ImgID'  value='<? print $ImgID; ?>'>
  <input type='hidden' name='TnFormatNo'  value='0'>
  <div id='divtn'></div><br/><table><tr><td class='button'>
  <input type='file' onChange='OnTnFileSelected();' class='button' name='newtn'
  style='border:none; height:15px; width:20px' ></td></tr></table>
  Click here to upload specially designed thumbnail
  </form></div>
  <?
  if ($ImgID) {print "<script>onImageLoad(); var imgsrcobj=P$.find('source_image'); if (imgsrcobj) imgsrcobj.onLoad=onImageLoad();</script>";}
  return true;
  }
  

function GetAlbumFormats($args)
  {
  extract(param_extract(array(
    ControlID=>"int",
    DependProperties=>"array",
    Value=>"string",
    ),$args));

  if ((!$DependProperties['BindTo'])&&(!$DependProperties['LayoutAlbum']))
    {
    return "Select 'BindTo' or 'LayoutAlbum' to choose displaying format after";
    }

  if ($DependProperties['BindTo'])
    {
    list ($PageControlID,$Socket)=explode (":",$DependProperties[BindTo]);
    $BindTo=$args['PageControls'][$PageControlID]->Data[$Socket];
    }
  else
    {
    $BindTo="img.Album/".$DependProperties['LayoutAlbum'];
    }

  $r=$this->_getFormatInfo(array(BindTo=>$BindTo));
  if (!$r['Error'])
    {
    $qf=&$r[qf];
    $result=false;
    if (!$qf) {return false;}

    foreach ($qf->Rows as $TnFormatNo=>$row)
      {
      $s="$TnFormatNo:";
      if (!$TnFormatNo) $s="(original):";
      $result[$TnFormatNo]=$s.langstr_get($row->Caption);
      }
    return array(ListValues=>$result);
    }
  return $BindTo;
  }

function _getFormatInfo($args) # returns (Error) | (FormatID
  {
  extract(param_extract(array(
    BindTo=>"string",     # set BindTo or
    FormatID=>"int=0",    #     FormatID to acquire image format
    ),$args));
  global $cfg;
  $_=&$GLOBALS['_STRINGS']['img'];

  if ($BindTo)
    {
    $info=BindPathInfo($BindTo);
    $BindToID=intval($info->ID);

    $s="SELECT * FROM img_Albums WHERE BindToFolder='$info->Folder' AND (BindToID=0"
    .(($BindToID)?" OR BindToID=$BindToID":"")." )";
    $qic=DBQuery($s);
    $DefaultFormat=$ObjectFormat=false;
    if (!$qic)
      {
      return array(Error=>"Undefined image format for binding class and context",Details=>"$BindTo<br>$s");
      }
    foreach($qic->Rows as $i=>$row)
      {
      if (!$row->BintToID) $DefaultFormat=&$row;
      if ($row->BindToID == $BindToID) $ObjectFormat=&$row;
      }
    if (!$ObjectFormat) {$ObjectFormat=$DefaultFormat;}
    if ((!$ObjectFormat)||(!$ObjectFormat->FormatID))
      {
      return array(Error=>"Undefined image subformat for binding class and context",Details=>"$BindTo");
      }
    $FormatID=$ObjectFormat->FormatID;
    }

  if ($FormatID)
    {
    if (isset($this->_formatsRead[$FormatID]))
      {
      $qf=&$this->_formatsRead[$FormatID];
      }
    }

  if ((!$qf)&&($FormatID))
    {
    $qf=DBQuery ("SELECT * FROM img_Formats WHERE FormatID=$FormatID ORDER BY TnFormatNo","TnFormatNo");
    $this->_formatsRead[$FormatID]=&$qf;
    }
  return array(FormatID=>$FormatID,qf=>&$qf,CanHasURL=>$qic->Top->CanHasURL);
  }


function AcceptEdit($args)
  {
  extract(param_extract(array(
    Focuses   =>"string",
    ImgID     =>"int",
    BindTo    =>"string",
    Caption   =>"string",
    TargetURL =>"string",
    OrderNo   =>"int",
    ),$args));

  global $cfg;
  
  $_ENV->UnlockTwicePost();
  $_=&$GLOBALS['_STRINGS']['img'];
  $dirmode=$cfg['Resources']['files'][1]; if (!$dirmode) $dirmode=0777;
  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;

  
  $errcode=$_FILES['newimg']['error'];
  if ($errcode)
    {
    if (($errcode==1)||($errcode==2))
      {
      return array(Message=>$_['ERROR_TOO_LARGE_FILE'],
      	Details=>"Max=".get_post_max_size(),
      	ButtonClose=>1,
      	);
      }
    }

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo)
    {
    return array(Message=>$_['ERROR_TOO_LARGE_FILE'],
    	Details=>"Max=".get_post_max_size(),
    	ButtonClose=>1,
    	);
#    return array(Error=>$_['TIMAGE_BAD_BINDING']);
    }

  $r=$this->_getFormatInfo(array(BindTo=>$BindTo, FormatID=>$FormatID));

  if ($r['Error'])
    {
    return array(Error=>$r['Error'],Details=>$r['Details']);
    }
  $qf=&$r['qf'];
  $FormatID=$r['FormatID'];

  if (!$qf)
    {
    return array(Error=>"Undefined format",Details=>"FormatID=$FormatID");
    }
  if (!$qf->Rows[0])
    {
    return array(Error=>"Image format does not contains 'Storing format' with TnFormatNo=0",Details=>"FormatID=$FormatID");
    }


  $Img_file=$_FILES['newimg']['tmp_name'];  if ($Img_file=='none') $Img_file=false;
  $Img_name=$_FILES['newimg']['name'];
  $Img_size=$_FILES['newimg']['size'];
  $Img_mimetype=$_FILES['newimg']['type'];


  if ($Img_file)
    {
    $tmpname=tempnam($cfg['FilesPath'],"uploaded");
    move_uploaded_file($Img_file,$tmpname);
    $Img_file=$tmpname;
    list ($Img_width,$Img_height,$Img_type,$Img_attr)=@getimagesize($Img_file);
    if ((!$Img_type)||(($Img_type>4)&&($Img_type!=13)))
      {
      return array(Message=>$_['ERROR_NOT_IMAGE']." ".$Img_type,Details=>"MIME type:$Img_mimetype. <b>getimagesize()</b> returns type:$Img_type ($Img_mimetype)",IntruderAlert=>10);
      }
    }
  $TargetPath=$cfg['FilesPath'].'/img/'.$BindToInfo->Folder;
  if (!is_dir ($TargetPath)) mkdir_recursive($TargetPath,$dirmode);

  $img=false;
  if ($ImgID)
    {
    $qimg=DBQuery ("SELECT * FROM img_Documents WHERE ImgID=$ImgID AND BindTo='$BindTo'");
    if ($qimg)
      {
      $img=$qimg->Top;
      }
    }

   
  if ($Img_file)
    {
    # if new file uploading remove old files
    if ($img)
      {
      $tns=$_ENV->Unserialize($img->Filenames);
      foreach ($tns as $i=>$TnFile)
        {
        if (strpos($TnFile,'.img.')!==false) continue; # pass designed thumbnails
        $f=$TargetPath.basename($TnFile);
        if (is_file($f)) {
          trace ("Remove old file $f");
          unlink ($f);
          }
        }
      }

    
    # Resampling too large image downto $MaxImgWidth x $MaxImgWidth
    $SaveFormat=$qf->Rows[0];
    $Img_dstw=$Img_width;
    $Img_dsth=$Img_height;

    $aspect=$Img_width/$Img_height;
    $needresample=false;
    if ($SaveFormat->Watermark) {$needresample=true;}
    if ($Img_size>$SaveFormat->MaxFileSizeKb*1024) {
      trace ("File size bigger than format MaxFileSize=$SaveFormat->MaxFileSizeKb kB. Resamling is needed");
      $needresample=true;}

    if ($Img_dstw>$SaveFormat->Width)  {
      $Img_dstw=$SaveFormat->Width; $Img_dsth=ceil($Img_dstw/$aspect);
      trace ("Width of source image more than required. Resamling is needed");
      $needresample=true;
      }
    if ($Img_dsth>$SaveFormat->Height) {
      $Img_dsth=$SaveFormat->Height;$Img_dstw=ceil($Img_dsth*$aspect);
      trace ("Height of source image more than required. Resamling is needed");
      $needresample=true;
      }

    if (($SaveFormat->ImgType)&&($SaveFormat->ImgType!=$Img_type ))
      {
      trace ("Save format required other ImgType. Resamling is needed");
      $needresample=true;
      }

    if (($Img_type==4)||($Img_type==13))
      {
      # SWF
      $DestImgFile=$BindToInfo->ID.'_0_'.substr(uniqid(rand()),0,7).'.swf';
      $DestImgFilePath=$TargetPath.$DestImgFile;
      }
    else switch ($SaveFormat->ImgType)
      {
      case 1:
        $DestImgFile=$BindToInfo->ID.'_0_'.substr(uniqid(rand()),0,7).'.gif';
        $DestImgFilePath=$TargetPath.$DestImgFile;
        break;
      case 3:
        $DestImgFile=$BindToInfo->ID.'_0_'.substr(uniqid(rand()),0,7).'.png';
        $DestImgFilePath=$TargetPath.$DestImgFile;
        break;
      default:
        $DestImgFile=$BindToInfo->ID.'_0_'.substr(uniqid(rand()),0,7).'.jpg';
        $DestImgFilePath=$TargetPath.$DestImgFile;
      }


    if ($needresample)
      {
      switch ($Img_type)
        {
        case 1: $im=@imagecreatefromgif ($Img_file); break;
        case 2: $im=@imagecreatefromjpeg($Img_file); break;
        case 3: $im=@imagecreatefrompng ($Img_file); break;
        }
      # Use JPG format if GIF not supported
      if (($SaveFormat->ImgType==1)&&(!(imagetypes()&IMG_GIF ))) $SaveFormat->ImgType=2;
      # Use JPG format if PNG not supported
      if (($SaveFormat->ImgType==3)&&(!(imagetypes()&IMG_PNG ))) $SaveFormat->ImgType=2;
      # Use GIF format if JPG not supported
      if (($SaveFormat->ImgType==3)&&(!(imagetypes()&IMG_JPG ))) $SaveFormat->ImgType=1;

      if (function_exists("imagecreatetruecolor") && ($SaveFormat->ImgType!=1))
        $im2=@imagecreatetruecolor ($Img_dstw,$Img_dsth);
      else
        {
        $im2=@imagecreate ($Img_dstw,$Img_dsth);
        }

      if (function_exists("imagecopyresampled"))
           imagecopyresampled ($im2,$im,0,0, 0,0, $Img_dstw, $Img_dsth, $Img_width, $Img_height);
      else imagecopyresized   ($im2,$im,0,0, 0,0, $Img_dstw, $Img_dsth, $Img_width, $Img_height);

      if ($SaveFormat->Watermark)
        {
        global $_THEME_NAME;
        $Opacity=$SaveFormat->WatermarkOpacity;
        if (!$Opacity) {$Opacity=100;}
        $filename=$TemplateFile=$cfg['TemplatesPath'].'/'.$_THEME_NAME.'/'.$SaveFormat->Watermark;
        if (file_exists($filename))
          {
          $winfo=@getimagesize($filename);
          $imw=false;
          switch ($winfo[2])
            {
            case 1: $imw=imagecreatefromgif($filename); break;
            case 2: $imw=imagecreatefromjpeg($filename);  break;
            case 3: $imw=imagecreatefrompng($filename);
#                imageAlphaBlending($imw, true);
#                imageAlphaBlending($im2, true);
#                $black = ImageColorAllocate ($imw, 0, 0, 0);
#                ImageColorTransparent($imw , $black);
              break;
            }
          if ($imw)
            {
#              imagecopymerge($im2,$imw,$Img_dstw-$winfo[0],$Img_dsth-$winfo[1],0,0,$winfo[0],$winfo[1],70);
            imagecopy($im2,$imw,$Img_dstw-$winfo[0],$Img_dsth-$winfo[1],0,0,$winfo[0],$winfo[1]);
            }
          }
        }

      switch ($SaveFormat->ImgType)
        {
        case 1:  $ok=imagegif ($im2,$DestImgFilePath); break;
        case 3:  $ok=imagepng ($im2,$DestImgFilePath); break;
        default: $ok=imagejpeg($im2,$DestImgFilePath,$SaveFormat->Quality);
        }
      unlink($Img_file);
      if (!$ok)
        {
        return array(Error=>"Unable to save resampled original file",Details=>$DestImgFilePath);
        }
      chmod($DestImgFilePath,$filemode);
      }
    else
      {
      if (!rename($Img_file,$DestImgFilePath))
        {
        unlink ($Img_file);
        return array(Error=>"Unable to rename file. The system has to remove it",Details=>"$Img_file => $DestImgFilePath");
        }
      chmod($DestImgFilePath,$filemode);
      }

    $a=array($DestImgFile);
    $DestImgFilenames=$_ENV->Serialize($a);
    if (!$ImgID)
      {
      $ImgID=DBInsert (array(Table=>'img_Documents',GetAutoInc=>true,Values=>array(
				FormatID=>$FormatID,
				BindTo=>$BindTo,
				Filenames=>$DestImgFilenames,
				Caption=>$Caption,
				Focuses=>$Focuses,
				TargetURL=>$TargetURL,
				OrderNo=>$OrderNo)));
      if (!$ImgID)
        {
        return array(Error=>"SQL Error",Details=>"Unable to insert new image to database");
        }
      }
    else
      {
      $ok=DBUpdate (array(
      	Table=>'img_Documents',
      	Keys=>array(ImgID=>$ImgID,BindTo=>$BindTo),
      	Values=>array(Filenames=>$DestImgFilenames,Caption=>$Caption,Focuses=>$Focuses,TargetURL=>$TargetURL)));
      }
    $this->Regenerate(array(ImgID=>$ImgID, BindTo=>$BindTo));
    return array(ModalResult=>array(ForwardTo=>ActionURL("img.IImage.Edit.f",array(ImgID=>$ImgID))));
    } #if Img_file
  else
    {
    if ($img)
      {
      $ok=DBUpdate(array(Table=>"img_Documents",
        Values=>array(Caption=>$Caption,Focuses=>$Focuses,TargetURL=>$TargetURL,OrderNo=>$OrderNo),
        Keys=>array(ImgID=>$ImgID,BindTo=>$BindTo)));
      if (!$ok) {return array(Error=>"Image document update error",Details=>"SQL error while image updated");}
      $oldFocuses=$img->Focuses;
      if ($Focuses!=$oldFocuses)
        {
        $this->Regenerate(array(ImgID=>$ImgID, BindTo=>$BindTo));
        }
      }
    return array(ModalResult=>true);
    } #if no Img_file
  }

function AcceptTnImage($args)
  {
  extract(param_extract(array(
    ImgID     =>"int",
    BindTo    =>"string",
    TnFormatNo=>"int", # 1-first thumbnail, 2-second.   0-error
    ),$args));
  global $cfg;
  $_=&$GLOBALS[_STRINGS]['img'];
  $_ENV->UnlockTwicePost();

  if (!$ImgID)
    {
    return array(Error=>"No ImgID passed as argument");
    }

  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;
  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo)
    {
    return array(Error=>$_[TIMAGE_BAD_BINDING]);
    }
  if (!$TnFormatNo)
    {
    return array(Error=>"No TnFormatNo in arguments");
    }

  $r=$this->_getFormatInfo(array(BindTo=>$BindTo, FormatID=>$FormatID));

  if ($r['Error'])
    {
    return array(Error=>$r['Error'],Details=>$r['Details']);
    }
  $qf=&$r['qf'];
  $FormatID=$r['FormatID'];

  if (!$qf)
    {
    return array(Error=>"Undefined format",Details=>"FormatID=$FormatID");
    }
  if (!$qf->Rows[0])
    {
    return array(Error=>"Image format does not contains 'Storing format' with TnFormatNo=0",Details=>"FormatID=$FormatID");
    }

  $errcode=$_FILES['newtn']['error'];
  if ($errcode)
    {
    if (($errcode==1)||($errcode==2))
      {
      return array(Error=>$_[ERROR_TOO_LARGE_FILE]);
      }
    }

  $Img_file=$_FILES['newtn']['tmp_name'];
  if (($Img_file=='none')||(!$Img_file))
    {
    return array(Error=>"File not uploaded");
    }
  $Img_name=$_FILES['newtn']['name'];
  $Img_size=$_FILES['newtn']['size'];
  $Img_mimetype=$_FILES['newtn']['type'];

  chmod($Img_file,0666); # need to read getimagesize
  list ($Img_width,$Img_height,$Img_type,$Img_attr)=@getimagesize($Img_file);
  if ((!$Img_type) || (($Img_type>4) && ($Img_type!=13)))
    {
    print "ImgType:$Img_type";
    return array(Error=>$_['ERROR_NOT_IMAGE']." $Img_type",Details=>$Img_mimetype,IntruderAlert=>50);
    }
  $TargetPath=$cfg['FilesPath'].'/img/'.$BindToInfo->Folder;
  if (!is_dir ($TargetPath)) mkdir_recursive($TargetPath,$dirmode);

  $qimg=DBQuery ("SELECT * FROM img_Documents WHERE ImgID=$ImgID AND BindTo='$BindTo'");
  if (!$qimg)
    {
    return array(Error=>"Image not found in database","$BindTo $ImgID");
    }
  $img=$qimg->Top;
  $tns=$_ENV->Unserialize($img->Filenames);
  $oldtn=$tns[$TnFormatNo];
  if ($oldtn)
    {
    $f=$TargetPath.basename($oldtn);
    trace ("Unlink $f<br>");
    }

  $ext="";
  switch ($Img_type)
    {
    case 1: $ext='gif'; break;
    case 2: $ext='jpg'; break;
    case 3: $ext='png'; break;
    case 4: case 13: $ext='swf'; break;
    }

  $DestImgFile=$BindToInfo->ID.'_'.$TnFormatNo.'_'.substr(uniqid(rand()),0,7).'.img.'.$ext;
  $DestImgFilePath=$TargetPath.$DestImgFile;
  move_uploaded_file($Img_file,$DestImgFilePath);
  $tns[$TnFormatNo]=$DestImgFile;
  $Filenames=$_ENV->Serialize($tns);
  DBExec ("UPDATE img_Documents SET Filenames='$Filenames' WHERE ImgID=$ImgID AND BindTo='$BindTo'");
  return array(ModalResult=>true);
  }
}

?>

