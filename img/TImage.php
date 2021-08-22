<?
class img_TImage
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Subscribers="BindTo";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  $this->Propdefs=array(
    LayoutAlbum=>array(Type=>"List",GetListValuesFrom=>"img.IAlbums.GetAlbumbsList",Caption=>$_['SELECT_GLOBAL_ALBUM']),
    TnFormatNo =>array(Type=>"List",DependOn=>"LayoutAlbum,BindTo",GetListValuesFrom=>"img.IImage.GetAlbumFormats",Caption=>"Displaying thumbnails format"),
    BindTo     =>array(Type=>"Binding",DataType=>"img.Image",Caption=>$_['P_BINDTO']),
    ShowCaption=>array(Type=>"Boolean",Caption=>$_['P_SHOWCAPTIONS'],DefaultValue=>true),
    ImgAlign=>array (Type=>"List",Caption=>"Image alignment",
      Values=>array(left=>'Left',right=>'Right',absmiddle=>'Absmiddle')),
    ImageStyle=>array(Type=>"ThemeElement",Section=>"ImageStyles",Caption=>$_['IMAGE_STYLE']),
    ImageSelected=>array(Type=>"Integer",DependOn=>"LayoutAlbum",Caption=>"Selected image from album to display"),
    ReplaceEmptyImg =>array(Caption=>$_['SELECT_EMPTY_IMAGE'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
    OnClickURL=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    ThemeDependentFormat=>array(Type=>"String",Caption=>"Which thumbnail format use for selected theme (i.e. 'theme1=2,theme2=1,theme3=2')"),
    BackgroundColor=>array(Type=>"color"),
    );
  }

function Init(&$Control)
  {
  if (!$Control->DesignMode) return;
  if ((!$Control->Propdefs['BindTo'])&&($Control->Properties['LayoutAlbum']))
    {
    $Control->EditableContent=true;
    }

  if (!$this->OnImageSelectedHasPut)
    {
    $this->OnImageSelectedHasPut=1;
    ?>
    <script>
    var UpdatingControl;
    var SysContext="<? print $Control->SysContext; ?>";
    var JSBPageID=<? print $Control->JSBPageID; ?>;
    function onImageSelected(mr)
      {
      W.openModal({url:"<? print ActionURL('jsb.IControl.UpdateFromPageEditor.b'); ?>",
       p_ImgID:mr, SysContext:SysContext,JSBPageControlID:UpdatingControl,JSBPageID:JSBPageID,
       w:500,h:500,Title:"Update control data",reloadOnOk:1});
      }
    </script>
    <?
    }
  }

function Render(&$Control)
  {
  extract ($Control->Properties);
  $IImage=&$_ENV->LoadInterface("img.IImage");
  if ($Control->DesignMode)
    {
    if ($Control->Properties['BindTo'])
      {
      list ($PageControlID,$Socket)=explode (":",$Control->Properties['BindTo']);
      $Control->BindTo=$GLOBALS['JSB_PageControlsByID'][$PageControlID]->Data[$Socket];
      }
    else
      {
      $Control->BindTo="img.Album/".$Control->Properties['LayoutAlbum'];
      }
    $r=$IImage->_getFormatInfo(array(BindTo=>$Control->BindTo));

    if ($r['Error'])
      {
      print_developer_warning($r['Error'],$r['Details']);
      }
    else
      {
      $TnFormatNo=intval($TnFormatNo);
      $TnFormatCaption=($TnFormatNo) ? $r['qf']->Rows[$TnFormatNo]->Caption : "Original image";

      $additional="";
      if (($Control->Properties['LayoutAlbum'])&&(!$Control->Properties['BindTo']))
        {
        $additional="<br>".$_ENV->PutButton(array(OnClick=>"UpdatingControl=$Control->JSBPageControlID; W.openModal({url:'".
          ActionURL("img.IImgIndex.View.f",array(
            BindTo=>$Control->BindTo,
            SelectOneImage=>1,
            EditMode=>1,
            Insertable=>1,
            Selected=>$ImageSelected)
            )."',callback:'onImageSelected',w:600,h:500,Title:'Select image to display'})",Caption=>"Choose other image",ToString=>1));
        }
#      print "<div style='background-color:#e0e0e0; color:#000000'><b>Format:</b> ".$r[qf]->Rows[0]->Caption."<br>
#      <b>Displaying [$TnFormatNo]:</b> $TnFormatCaption<br><b>BindTo:</b> $BindTo $additional</div>";
      print "<div style='position:absolute;'>$additional</div>";
      }
    }

  if ($Control->BindTo)
    {
    $BindToDetails=BindPathInfo($Control->BindTo);
    if (!$BindToDetails)
      {
      return array(Error=>"[[BAD_BINDTO_PATH]]");
      }
    $Control->Properties['BindTo']=$Control->BindTo;
    }
  else
    {
    $Control->Properties['BindTo']="";
    if ($Control->Properties['LayoutAlbum'])
      {
      $Control->Properties['BindTo']="img.Album/".$Control->Properties['LayoutAlbum'];
      }
    }

  trace ($Control->Properties);
  $IImage->View ($Control->Properties);
  }
}

