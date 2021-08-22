<?
class stdctrls_IRichtextImg
  {
  function Save()
    {
    $__=&$GLOBALS[_STRINGS][_];
    $_=&$GLOBALS[_STRINGS][stdctrls];
    global $cfg;

    $ImgID=$_POST['ImgID'];
    $BindTo=$_POST['BindTo'];

    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo) return array(Error=>'[[BAD_BINDTO_PATH]]',Details=>$BindTo);
    $d0=$cfg['FilesPath']."/stdctrls.RichtextImg/$BindToInfo->Folder";
    $url=$cfg['FilesRelativeURL']."/stdctrls.RichtextImg/$BindToInfo->Folder";
    if (!is_dir($d0)) {mkdir_recursive ($d0,$cfg['Resources']['files'][1]);}
    trace ("Target dir: '$d0'");

    # check file for upload error or mime error
    $errcode=$_FILES['ImgFile']['error'];
    if ($errcode)
      {
      trace ("Get _FILES error: $errcode");
      if (($errcode==1)||($errcode==2))
        {
        return array(Message=>$_[RT_IMG_TOOLARGE]);
        }
      }

    $Img_file=$_FILES['ImgFile']['tmp_name'];
    if ($Img_file=='none') {$Img_file=false;}
    if ($Img_file)
      {
      trace("cmod ('$Img_file',066)");
      chmod($Img_file,0666);
      }

    if ($ImgID)
      {
      $q=DBQuery ("SELECT * FROM stdctrls_RichtextImgs WHERE ImgID=$ImgID AND BindTo='$BindTo'");
      if ($doc)
        {
        $Dest_img_file=$q->Top->ImgFile;
        $Dest_tn_file =$q->Top->TnFile;
        }
      else $ImgID=0;
      }

    if ($Img_file)
      {
      $Img_type=$_FILES['ImgFile']['type'];
      $Img_info=@getimagesize($Img_file);
      $Tn_w=$Img_w=$Img_info[0];
      $Tn_h=$Img_h=$Img_info[1];
      $aspect=$Img_w/$Img_h;
      $resizabe=false; $ext=false;
      switch ($Img_info[2])
        {
        case 1: $ext='gif'; break;
        case 2: $ext='jpg'; $resizable=true; break;
        case 3: $ext='png'; break;
        case 4: $ext='swf'; break;
        default: return array(Message=>$_['RT_IMG_BADTYPE'],Details=>$Img_type);
        }

      if (!$ImgID)
        {
        $Dest_img_file=md5(uniqid(rand())).'.'.$ext;
        }

      $s=$d0.$Dest_img_file;
      move_uploaded_file ($Img_file,$s);
      trace ("Save source image file to '$s'");
      $Img_file=$s;
      if (!file_exists($Img_file))
        {
        return array(Error=>"move_uploaded_file() does not work",Details=>"$Img_file -> $s");
        }

      if ($resizable)
        {
        if (!$ImgID)
          {
          $Dest_tn_file=md5(uniqid(rand())).'.jpg';
          }
        $TnQuality=intval($cfg['Settings']['stdctrls']['RichtextImagesQuality']);
        list($Max_w,$Max_h)=explode ("x",$cfg['Settings']['stdctrls']['RichtextImagesLimitSize']);
        $Max_w=intval($Max_w); $Max_h=intval($Max_h);
        if (!$Max_w) $Max_w=500;
        if (!$Max_h) $Max_h=500;
        if (!$TnQuality) $TnQuality=90;
        if ($Tn_w>$Max_w) {$Tn_w=$Max_w; $Tn_h=ceil($Tn_w/$aspect); }
        if ($Tn_h>$Max_h) {$Tn_h=$Max_h; $Tn_w=ceil($Tn_h*$aspect); }
        trace ("Resized image  [$Tn_w x $Tn_h] save to '$d0$Dest_tn_file' [Quality: $TnQuality]");
        $im1=imagecreatefromjpeg ($Img_file);
        if (!$im1) return array(Error=>"imagecreatefromjpeg() does not working properly. It returns false when opening JPEG file",Details=>"Source file:$Img_file");
        $im2 = (function_exists("imagecreatetruecolor")) ? @imagecreatetruecolor ($Tn_w,$Tn_h): @imagecreate ($Tn_w,$Tn_h);
        if (!$im2)  return array(Error=>"imagecreate() does not working properly. It returns false",Details=>"$Tn_w x $Tn_h");
        if (function_exists("imagecopyresampled")) imagecopyresampled ($im2,$im1,0,0, 0,0, $Tn_w, $Tn_h, $Img_w, $Img_h);
        else imagecopyresized ($im2,$im1,0,0, 0,0, $Tn_w, $Tn_h, $Img_w, $Img_h);
        imagejpeg ($im2,$d0.$Dest_tn_file,$TnQuality);
        if (!file_exists($d0.$Dest_tn_file))
          {
          return array(Error=>"Strange! JPEG thumbnail have not been created",Details=>"$Img_file -> $Dest_tn_file");
          }
        }
      else
        {
        # don't resize
        trace ("Image type '$ext' is not resizable. Keep it as is");
        $Dest_tn_file=$Dest_img_file;
        }

      if ($ImgID)
        {
        DBExec ("UPDATE stdctrls_RichtextImgs
          SET ImgFile='$Dest_img_file',TnFile='$Dest_tn_file'
          WHERE ImgID=$ImgID AND BindTo='$BindTo'");
        }
      else
        {
        $ImgID=DBGetID("stdctrls.RichtextImg");
        DBExec ("INSERT INTO stdctrls_RichtextImgs (ImgID,BindTo,ImgFile,TnFile) VALUES
            ($ImgID,'$BindTo','$Dest_img_file','$Dest_tn_file')");
        }

      $ImgSrc=$url.$Dest_tn_file;
      $result=array(
        ImgID=>$ImgID,
        ImgSrc=>$ImgSrc,
        Width=>$Tn_w,
        Height=>$Tn_h,
        SrcWidth=>$Img_w,
        SrcHeight=>$Img_h
        );
      trace ("Returning values to callback",1);
      trace ($result);
      return array(ModalResult=>$result);
      }
#    return array(ModalResult=>true);
    }

  function Resize($args)
    {
    extract(param_extract(array(
      ImgID=>'int',
      Tn_w=>'int',   # - new width
      Tn_h=>'int',   # - new height
      ),$args));

    $q=DBQuery ("SELECT * FROM stdctrls_RichtextImgs WHERE ImgID=$ImgID");
    if (!$q)
      {
      return array(Error=>"Image document is absent",Details=>$ImgID);
      }
    global $cfg;

    $BindTo=$q->Top->BindTo;
    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);

    $d0=$cfg['FilesPath']."/stdctrls.RichtextImg/$BindToInfo->Folder";

    $ImgFile=$q->Top->ImgFile;
    $TnFile=$q->Top->TnFile;
    if (!file_exists($d0.$ImgFile))
      {
      return array(Error=>"Image file that declared in document is absent",Details=>$ImgID);
      }

    $Img_info=@getimagesize ($d0.$ImgFile);
    if ($Img_info[2]!=2)
      {
      # If it is not jpeg file .. just return
      return;
      }
    $Img_w=$Img_info[0];
    $Img_h=$Img_info[1];
    $TnQuality=$cfg['Settings']['stdctrls']['RichtextImagesQuality'];

    $im1=imagecreatefromjpeg ($d0.$ImgFile);
    $im2 = (function_exists("imagecreatetruecolor"))
       ? @imagecreatetruecolor ($Tn_w,$Tn_h)
       : @imagecreate ($Tn_w,$Tn_h);
    if (function_exists("imagecopyresampled"))
      imagecopyresampled ($im2,$im1,0,0, 0,0, $Tn_w, $Tn_h, $Img_w, $Img_h);
    else
      imagecopyresized ($im2,$im1,0,0, 0,0, $Tn_w, $Tn_h, $Img_w, $Img_h);

    trace("Save new image to $d0$TnFile");
    imagejpeg ($im2,$d0.$TnFile,$TnQuality);
    }

  function Edit($args)
    {
    # args[BindTo]      - where document should be bind
    # args[ImgID]       - if AddDoc is exists set AddDocID

    $__=&$GLOBALS[_STRINGS][_];
    $_=&$GLOBALS[_STRINGS][stdctrls];
    global $cfg,$_CORE;

    extract ($args);
    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);
    $d0=$cfg['FilesPath']."/stdctrls.RichtextImg/$BindToInfo->Folder";
    $url=$cfg['FilesRelativeURL']."/stdctrls.RichtextImg/$BindToInfo->Folder";

    print "<form method='post' ENCTYPE='multipart/form-data' action='".ActionURL('stdctrls.IRichtextImg.Save.f')."'>";
    if ($ImgID)
      {
      $q=DBQuery ("SELECT * FROM stdctrls_RichtextImgs WHERE ImgID=$ImgID");
      if ($q)
        {
        $ImgFile=$q->Top->ImgFile;
        $TnFile=$q->Top->TnFile;
        $imginfo=@getimagesize($d0.'/'.$ImgFile);
        $imginfo2=@getimagesize($d0.'/'.$TnFile);
        print "Image size: $imginfo[0]x$imginfo[1], ".ceil(filesize($d0.'/'.$ImgFile)/1024)." kBytes<br>";
        print "Rescaled to $imginfo2[0]x$imginfo2[1], ".ceil(filesize($d0.'/'.$TnFile)/1024)." kBytes<br>";
        print "<img src='$url/$TnFile' $imginfo2[3]>";
        }
      print "<input type='hidden' name='ImgID' value='$ImgID'>";
      print "<title>Image # $ImgID</title>";
      }
    else
      {
      print "<title>$_[RT_IMG_TITLEINSERT]</title>";
      }

    print "<input type='hidden' name='BindTo' value='$BindTo'>";
    print "<table border='0' width='100%'><tr><td>$_[RT_IMG_SELECTFILE]<br><br><input type='file' size='40' class='inputarea' name='ImgFile'></td></tr>";
    print "</table><table border='0' width='100%'><tr><td align='right'>";
    $_CORE->PutButton('submit');
    $_CORE->PutButton('cancel');
    print "</td></tr></table></form>";
    return true;
    }


  }

?>
