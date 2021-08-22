<?
class um
{
function um()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  $this->Title=$_['TITLE_USER_MANAGEMENT_SYSTEM'];
  $this->Roles=array(
    UserGroupManager=>$_['ROLE_USERGROUPMANAGER'],
    UserManager=>$_['ROLE_USERMANAGER'],
    );
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  return array(
		TLoginPanel=>array(Caption=>$_['TLOGINPANEL_CAPTION'],Description=>$_['TLOGINPANEL_DESCRIPTION'],Icon=>''),
		TRegistrationCard=>array(Caption=>$_['TREGISTRATIONCARD_CAPTION'],Description=>$_['TREGISTRATIONCARD_DESCRIPTION'],Icon=>''),
		TUserInfo=>array(Caption=>$_['TUSERINFO_CAPTION'],Description=>$_['TUSERINFO_DESCRIPTION'],Icon=>''),
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  return array (
    array
      (
      PutToCategory=>"admin",
      CreateCategory=>"um",
      Block=>"10",
      Caption=>$_['TITLE_USER_MANAGEMENT_SYSTEM'],
      Icon=>"ico_users.gif",
      Items=>array(
        array(Caption=>$_['CAPTION_GROUPS'],Call=>"um.IUserGroups.Browse.bm"),
        array(Caption=>$_['CAPTION_MANAGEUSERS'],Call=>"um.IUsers.Browse.bm"),
        array(Caption=>$_['CAPTION_ADDUSER'],Call=>"um.IUsers.Edit.bm"),
        array(Caption=>$_['CAPTION_REGENERATE_ACCESS'],Call=>"um.IUserGroups.UpdateAccessTables.bm"),
        )
      ),
    );
  }
  
function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  global $cfg;
  return array
    (
    RegistratorEmail=>array(Caption=>$_['REGISTRATOR_EMAIL'],Type=>'string',DefaultValue=>$cfg['HelpEmail']),
    URLLoginForm=>array(Caption=>$_['LOGIN_FORM_PAGE'],Type=>'inputmodal',Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL.b"),
    ActivatesViaEmail=>array(Caption=>$_['NEW_USER_ACTIVATES_VIA_EMAIL'],Type=>'boolean'),
    DisableRegistration=>array(Caption=>$_['DISABLE_REGISTRATION'],Type=>'boolean'),
    CaseSensitiveLogin=>array(Caption=>$_['CASE_SENSITIVE_LOGIN'],Type=>'boolean'),
    
    );
  }

}
?>
