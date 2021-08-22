<?
class img
{
function img()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  $this->Title=$_['TITLE'];
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  return array(
TImage=>array(Description=>$_['TIMAGE_CAPTION']),
TImgIndex=>array(Caption=>$_['TIMGINDEX_CAPTION'],Description=>$_['TIMGINDEX_DESCRIPTION']),
TMonitor=>array(Caption=>$_['TMONITOR_CAPTION'],Description=>$_['TMONITOR_DESCRIPTION']),
    );
  }


function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  return array (
    array
      (
      PutToCategory=>"content",
      Items=>array(
        array(Caption=>$_['IMG_ALBUMS'],Call=>"img.IAlbums.GlobalAlbums.bm"),
        )
      ),
    array
      (
      PutToCategory=>"admin",
      Items=>array(
        array(Caption=>$_['IMG_ALBUMCONTEXTS'],Call=>"img.IAlbums.Albums.bm"),
        array(Caption=>$_['IMG_IMAGEFORMATS'],Call=>"img.IFormats.Browse.bm")
        )
      ),
    );
  }

function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  return array
    (
    ImageViewContext=>array(Caption=>$_['IMGVIEW_CONTEXT'],Type=>'syscontext',DefaultValue=>'imgview'),
    );
  }


function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  return array
    (
    "img.Image"=>array (Caption=>"Image",UseSettingsContext=>"ImageViewContext",Bindable=>1),
    "img.Album"=>array (Caption=>"An image album"),
    );
  }



}
?>

