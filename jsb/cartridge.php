<?
class jsb
{
function jsb()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  $this->Title=$_[TITLE];
  $this->Roles=array(
    MainDesigner=>$_['ROLE_MAINDESIGNER'],
    Composer=>$_['ROLE_COMPOSER']
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  return array (

    array
      (
      CreateCategory=>"design",
      Block=>0,
      Caption=>$_['TITLE_SITE_EDITOR'],
      Icon=>"ico_design.gif",
      Items=>array(
        array(Caption=>$_['CAPTION_PAGEDESIGN'],Call=>"jsb.ISiteExplorer.Open.n"),
        array(Caption=>$_['TITLE_THESE_ARE_LAYOUTS'],Call=>"jsb.ILayouts.Browse.bm"),
        array(Caption=>$_['TITLE_EDIT_THEME'],Call=>"jsb.ITheme.Edit.bm"),
        )
      ),

    array
      (
      PutToCategory=>"tools",
      Block=>2,
      Items=>array(
        array(Caption=>$_['CLEAR_TMP'],Call=>"jsb.IClearTmp.All.bm"),
        array(Caption=>$_['ICLEAN_TITLE'],Call=>"jsb.ICleaner.View.bm"),
        )
      ),

    array
      (
      PutToCategory=>"admin",
      Block=>0,
      Items=>array(
        array(Caption=>$_['CAPTION_CONTEXTS'],Call=>"jsb.IContexts.Browse.bm"),
        )
      ),

    );
  }


function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  return array
    (
    WebSiteTitle=>array(Caption=>$_['WEBSITETITLE'],Type=>'langstring',DefaultValue=>''),
    HomeContext=>array(Caption=>$_['HOME_CONTEXT'],Type=>'syscontext',DefaultValue=>'site',Required=>1),
    ActiveTheme=>array(Caption=>$_['MAIN_THEME'],Type=>'call',Action=>'jsb.ITheme.SettingGetTheme',DefaultValue=>'theme1'),
    DisableCache=>array(Caption=>$_['SETTING_DISABLECACHE'],Type=>'boolean'),
    ShowControlIcons=>array(Caption=>$_['SETTING_SHOWCONTROLICONS'],Type=>'boolean'),
    UseNativeHTTPError=>array(Type=>'boolean'),
    UsePNGPatchForIE=>array(Type=>'boolean'),
    );
  }

function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  return array
    (
    "jsb.Page"=>array(Caption=>"Website page",UseSettingsContext=>"HomeContext",'Interface'=>"jsb.IPage"),
    );
  }


}
?>