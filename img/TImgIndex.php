<?
class img_TImgIndex
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Image;
var $Subscribers="BindTo";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS]['img'];
  $this->Propdefs=array(
    LayoutAlbum=>array(Type=>"List",GetListValuesFrom=>"img.IAlbums.GetAlbumbsList",Caption=>"Web site custom album"),
    TnFormatNo =>array(Type=>"List",DefaultValue=>1,DependOn=>"LayoutAlbum,BindTo",GetListValuesFrom=>"img.IImage.GetAlbumFormats",Caption=>"Displaying thumbnails format"),
    Padding=>array(Type=>"integer"),
    BindTo     =>array(Type=>"Binding",DataType=>"img.Image",Caption=>$_['P_BINDTO']),
    ImagesPerPage=>array(Type=>"Integer",DefaultValue=>0),
    OnShow=>array (Type=>"List",Caption=>$_[IMAGE_P_ONSHOW],
            Values=>array(
                    mw=>$_['IMAGE_V_ONSHOW_MODALWINDOW'],
                    w=>$_['IMAGE_V_ONSHOW_WINDOW'],
                    m=>$_['IMAGE_V_ONSHOW_IMGMONITOR'],
                    b=>$_['IMAGE_V_ONSHOW_NEWBLANKWINDOW'],
                    n=>$_['IMAGE_V_ONSHOW_NONE'],
                    c=>$_['IMAGE_V_ONSHOW_OPENCONTEXT'],
                    hmw=>$_['IMAGE_V_ONSHOW_OPENCONTEXT_MODALWINDOW'],
                    hbw=>$_['IMAGE_V_ONSHOW_OPENCONTEXT_BLANK'],
                    ),
            DefaultValue=>'w'),
    ImgViewContext=>array(Type=>"SysContext",ObjectClass=>'img.Image',Caption=>$_['IMGVIEW_CONTEXT'],DefaultValue=>$cfg['Settings']['img']['ImageViewContext']),
    OpeningWindowAddSize=>array(Type=>"Dim",DefaultValue=>"40x40"),
    Monitor=>array (Type=>"Binding",DataType=>"img.Monitor",Caption=>$_['P_BINDTOMONITOR']),
    ColumnCount=>array(Type=>"int",DefaultValue=>3,Caption=>$_['TIMGINDEX_P_COLUMNCOUNT']),
    ShowCaptions=>array (Type=>"Boolean",Caption=>$_['P_SHOWCAPTIONS'],DefaultValue=>true),
    ImageStyle=>array(Type=>"ThemeElement",Section=>"ImageStyles",Caption=>$_['IMAGE_STYLE']),
    ForceTnSize=>array(Type=>"Dim",Caption=> $_['FORCED_TNSIZE']),
    );
  $this->Datadefs=array(
      Pages =>array(DataType=>"Pages",Caption=>"Album pages"),
      );
  }

function AfterInit(&$Control)
  {
  $Control->PageNo=$Control->Arguments['p'];
  $ImagesPerPage=intval($Control->Properties['ImagesPerPage']);

  $BindTo=$Control->BindTo;
  $LayoutAlbum=$Control->Properties['LayoutAlbum'];
  if ($LayoutAlbum) $BindTo="img.Album/$LayoutAlbum";

  if (($ImagesPerPage)&&($BindTo))
    {
    $q=DBQuery("SELECT COUNT(*) AS ImgCount FROM img_Documents WHERE BindTo='$BindTo'");
    if ($q) $Control->ImgCount=$q->Top->ImgCount;
    $PageCount=ceil($Control->ImgCount/$ImagesPerPage);

    $Control->Data['Pages']=array(
      PageCount=>$PageCount,
      PageNo=>$Control->PageNo,
      JSBPageControlID=>$Control->JSBPageControlID);
    }

  if (($Control->Properties->OnShow=='mw')||($Control->Properties->ColumnCount==0))
    {
    $_ENV->InitPage();
    }
  }

function Render(&$Control)
  {
  global $cfg;
  $_=&$_STRINGS['img'];

  $imgPath=$cfg['FilesPath']."/img";
  $imgURL =$cfg['FilesURL']."/img";
  $TImageBindings=false;

  extract ($Control->Properties);
  if ($Control->BindTo)
    {
    if ($Control->EditableContent)
      {
      $Selectable=true;
      }
    $BindToDetails=BindPathInfo($Control->BindTo);
    if (!$BindToDetails)
      {
      return array(Error=>"Binding reference invalid");
      return;
      }
    }

  if ($LayoutAlbum)
    {
    $Control->BindTo="img.Album/$LayoutAlbum";
    }


  list($AddWndWidth,$AddWndHeight)=explode("x",$OpeningWindowAddSize);
  $iimg=&$_ENV->LoadInterface("img.IImgIndex");
  $r=$iimg->View (array(
    TnFormatNo   =>$TnFormatNo,
    TnFormats    =>$TnFormats,
    ShowCaptions =>$ShowCaptions,
    ColumnCount  =>$ColumnCount,
    BindTo       =>$Control->BindTo,
    DummyMode    =>$Control->EditMode,
    ForceTnSize  =>$ForceTnSize,
    OnShow       =>$OnShow,
    Monitor      =>$Monitor,
    ImagesPerPage=>$ImagesPerPage,
    Padding      =>$Padding,
    PageNo       =>$Control->PageNo,
    ImgViewContext=>$ImgViewContext,
    AddWndWidth  =>$AddWndWidth,
    AddWndHeight =>$AddWndHeight,
    ImageStyle   =>$ImageStyle,
    ImgAlign     =>$ImgAlign
    ));
  }

}

?>

