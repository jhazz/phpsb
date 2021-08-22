<?
class img_Image
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  $this->About=$_['TIMGDETAILS_ABOUT'];
  $this->Propdefs=array(
    Size=>array(Type=>"size",DefaultValue=>"500x600",Caption=>$_['IMGMONITOR_SIZE']),
    FitInside=>array(Type=>"Boolean",DefaultValue=>true,Caption=>$_['IMGMONITOR_FITINSIDE']),
    Align=>array(Type=>"Align"),
    TnFormatToShow=>array(Type=>'int'),
    HideImage=>array(Type=>'Boolean'),
    );
  $this->Datadefs=array(
    ImgCaption=>array(DataType=>"string",Caption=>$_['TIMGDETAILS_CAPTION']),
    Image=>array(DataType=>"img.Image",Caption=>$_['TIMGDETAILS_THEIMAGE']),
#    Image=>array(DataType=>"socket",Caption=>$_['TIMGDETAILS_BINDTO']),
    );
  }

function Init(&$Control)
  {
  global $cfg;
  $TnFormatToShow=intval($Control->Properties['TnFormatToShow']);

  $ImgID=$Control->JSBPageID;
  $q=DBQuery ("SELECT * FROM img_Documents WHERE ImgID='$ImgID'");
  if ($q)
    {
    $Control->Data['ImgCaption']=$q->Top->Caption;
#    $Control->Data['BindSocket']="img.Image/$ImgID"
    $Control->Data['Image']=$q->Top->BindTo."!$ImgID";
/*    $BindTo=$q->Top->BindTo;
    $BindInfo=BindPathInfo($BindTo);
    if (!$BindInfo)
      {
      return array(Error=>"Bad binding",Details=>$BindTo);
      }
    $Control->Data['BindTo']=$BindTo;
    $Files=$_ENV->Unserialize($q->Top->Filenames);

    $ImgName=$Files[$TnFormatToShow];

    if ($ImgName)
      {
      $s='/img/'.$BindInfo->Folder.$ImgName;
      $ImgPath=$cfg['FilesPath'].$s;
      $ImgURL=$cfg['FilesRelativeURL'].$s;
      if (!file_exists($ImgPath))
        {
        return array(Error=>"Image file not found",Details=>$ImgPath);
        }
      $info=getimagesize($ImgPath);
      $Control->img="<img src='$ImgURL' $info[3]>";
      }
    */
    }
  }
/*

function Render(&$Control)
  {
  global $cfg;
  extract(param_extract(array(
      Size=>"size=700x800",
      Caption_Inside=>"string",
      FitInside=>'int',
      HideImage=>'int',
      ),
    $Control->Properties));

  if (($Control->SysContext=='layouts')&&($Control->EditMode))
    {
    list($mw,$mh)=explode ('x',$Size);
    $s="<img border='0' src='$cfg[PublicURL]/img/dummy_img.gif' width='$mw' height='$mh'>";
    print $s;
    return;
    }

  if (($Control->img)&&(!$HideImage))
    {
    print $Control->img;
    }
  }
  */
}

