<?
class backend
{
function backend()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];
  $this->Title=$_['BACKEND_CARTRIDGE'];
  $this->Roles=array(
    BackendAccess =>$_['ROLE_BACKENDACCESS'],
    BackupManager =>$_['ROLE_BACKUPMANAGER'],
    ChangeConfig  =>$_['ROLE_CHANGE_CONFIG'],
    ChangeSettings=>$_['ROLE_CHANGE_SETTINGS']
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];

  $Languages=&$GLOBALS['_LANGUAGE_DISPATCHER']->LoadLanguages();
  foreach ($Languages as $id=>$r) {
    if ($r['Enabled']) $viewitems[]=array(Caption=>$r['Caption'],Call=>"backend.ISetLanguage.Execute.n?chooselanguage=$id");
    }

  $viewitems[]=array(Caption=>$_['SET_BACKEND_SKIN'],Call=>"backend.IBackendSkin.Browse.bm");
  $viewitems[]=array(Block=>100,Caption=>$_['CAPTION_LOGOUT'],Call=>"backend.ILogout.Execute.n");

  return array (
    array
      (
      PutToCategory=>"",
      CreateCategory=>"view",
      Caption=>$_['MENUCATEGORY_VIEW'],
      Block=>101,
      Items=>$viewitems,
      ),

   array
      (
      PutToCategory=>"",
      CreateCategory=>"content",
      Caption=>$_['MENUCATEGORY_CONTENT'],
      Block=>2,
      Icon=>"ico_content.gif",
      ),

   array
      (
      PutToCategory=>"",
      CreateCategory=>"tools",
      Caption=>$_['MENUCATEGORY_TOOLS'],
      Block=>3,
      Icon=>"ico_tools.gif",
      Items=>array(
        array(Caption=>$_['TITLE_BACKUPSYSTEM'],Call=>"backend.IBackupDbase.Backup.bm"),
#        array(Caption=>$_['MENU_MAILQUEUES'],Call=>"backend.IMailQueues.Browse.bm"),
        )
      ),

    array
      (
      PutToCategory=>"",
      CreateCategory=>"admin",
      Caption=>$_['MENUCATEGORY_ADMIN'],
      Icon=>"ico_admin.gif",
      Block=>100,
      Items=>array(
        array(Caption=>$_['TITLE_SETTINGS'],Block=>11,Call=>"backend.ISettings.Edit.bm"),
        array(Caption=>$_['TITLE_FRONTDOORS'],Block=>11,Call=>"backend.IFrontdoors.Browse.bm"),
        )
      ),

    );


  }

function BackendInfoBlocks()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];
  return array(
    array(Column=>1,Caption=>"Statistics",Call=>"backend.IInfoBlocks.Stat"),
    array(Column=>1,Caption=>"Cartridges",Call=>"backend.IInfoBlocks.Cartridges"),
    array(Column=>2,Caption=>"Who Is Online",Call=>"backend.IInfoBlocks.Whoisonline"),
    );
  }
}
?>
