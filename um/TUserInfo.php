<?php
class um_TUserInfo
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  $this->Propdefs=array(
    CSS_Cell=>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_Link=>array(Type=>"CSS_Class",BaseCSSClass=>"a"),
#    HidePanel=>array(Type=>"Boolean"),
    Text_EnterMyOffice=>array(Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_TEXT_ENTERMYOFFICE'],Caption=>$_[TLOGINPANEL_TEXT_ENTERMYOFFICE]),
    Text_Logout=>array(Type=>"Caption",DefaultValue=>$_['LOGOUT']),

    Caption_Register=>array (Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_CAP_REGISTER'],Caption=>$_['TLOGINPANEL_CAP_REGISTER']),
    URL_Register=>array(Caption=>$_['TLOGINPANEL_CAP_URL_REGISTER'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_OnLogout=>array(Caption=>$_['TLOGINPANEL_URL_HAVELOGGEDOUT'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_OnLogin=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_OnEdit=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_KickIfUnathorized=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    CellPadding=>array (Type=>"Integer"),
    );
  $this->Datadefs=array(
  	UserLogin=>array(Type=>'string',Caption=>'User login name'),
  );
  }

function Init(&$Control)
  {
  	global $_USER;
  	$Control->Data['UserLogin']=$_USER->Login;
  	if (!$_USER->UserID) {
  		$kick=$Control->Properties['URL_KickIfUnathorized'];
			if ($kick) {
				return array(ForwardTo=>$kick);
			}
  	}
  }


  function Render(&$Control) {
  	$__=&$GLOBALS['_STRINGS']['_'];
  	$_ =&$GLOBALS['_STRINGS']['um'];
  	global $cfg;
  	extract ($Control->Properties);

  	global $_USER;

  	list($t,$td_class)  =get_css_pair($CSS_Cell,"td");
  	list($t,$link_class)  =get_css_pair($CSS_Link,"a");

  	print "<table cellpadding='$CellPadding' cellspacing='0' border='0'><tr><td$td_class>";
  	if ($_USER->UserID) {
  		print "<a href='$URL_OnEdit' $link_class>$_USER->Login</a></td><td$td_class> | </td><td$td_class>"
  		."<form style='padding:0; margin:0' method='post' name='TUserInfoLogoutForm' action='".ActionURL("um.ILogin.Logout.n",array(ForwardTo=>$URL_OnLogout))."'>"
  		."<a $link_class href='javascript:TUserInfoLogoutForm.submit();'>$Text_Logout</a>"
  		."</form>";
  	} else {
  		print "<a $link_class href='$URL_OnLogin'>$Text_EnterMyOffice</a>";
  		if ($Caption_Register && $URL_Register) print " | <a $link_class href='$URL_Register'>$Caption_Register</a>";
  	}
  	print "</td></tr></table>";
  }
}
?>
