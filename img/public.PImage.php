<?
class img_PImage
  {
  function img_PImage()
    {
    $_=&$GLOBALS[_STRINGS][img];

    $this->CopyrightText="(c)2003 JhAZZ Site Builder. Image control cartridge. Public interface";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
    }

  function LoadXML($args)
    {
    extract(param_extract(array(
      BindTo=>"string",
      TnFormat=>"int",  // 0-as is , 1-A, 2-B, ...
      ),$args));

    global $cfg;
    $_=&$GLOBALS[_STRINGS]['img'];


    if ($BindTo)
      {
      $BindInfo=BindPathInfo($BindTo);
      if (!$BindInfo)
        {
        return array(Error=>$_[ERROR_BINDTO],Details=>$BindTo);
        }
      $qimg=DBQuery ("SELECT ImgName,Caption,TnNames FROM img_Documents
         WHERE BindTo='$BindTo' ORDER BY ImgID LIMIT 1");
      }

    if ($qimg)
      {
      $caption=nl2br($qimg->Top->Caption);

      $TnNames=explode ("|",$qimg->Top->TnNames);
      if ($TnFormat) $ImgFileName=basename($TnNames[$TnFormat-1]);
      else $ImgFileName=basename($qimg->Top->ImgName);

      $ImgURL= $cfg['FilesURL']. "/img/".$BindInfo->Class.'/'.$ImgFileName;
      $ImgFile=$cfg['FilesPath']."/img/".$BindInfo->Class.'/'.$ImgFileName;
      $ImgInfo=@getimagesize($ImgFile);
      if ($ImgInfo)
        {
        $result="<img src='$ImgURL' $ImgInfo[3] caption='$caption'/>";
        return array(XML=>$result);
        }
      }
    }


  }

?>

