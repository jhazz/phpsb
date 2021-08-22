<?php
class um_TLoginPanel
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  $this->Propdefs=array(
    Text_Login=>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TLOGINPANEL_CAP_LOGIN'],Caption=>$_['TLOGINPANEL_CAP_LOGIN']),
    Text_Pass=>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TLOGINPANEL_CAP_PASSWORD'],Caption=>$_['TLOGINPANEL_CAP_PASSWORD']),
    Caption_Register=>array (Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_CAP_REGISTER'],Caption=>$_['TLOGINPANEL_CAP_REGISTER']),
    Text_KeepPass=>array(Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_KEEP_SESSION_LOCKED'],Caption=>$_['TLOGINPANEL_KEEP_SESSION_LOCKED']),
    Button_Enter=>array (Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_CAP_ENTER'],Caption=>$_['TLOGINPANEL_CAP_ENTER']),

    Caption_Forget=>array (Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_CAP_FORGET']),
    Text_ForgetPassword=>array(Type=>"String",DefaultValue=>$_['TLOGINPANEL_FORGETPASSWORD']),

    URL_Success=>array (Caption=>$_['TLOGINPANEL_CAP_URL_SUCCESS'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_Error=>array   (Caption=>$_['TLOGINPANEL_CAP_URL_ERROR'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_Register=>array(Caption=>$_['TLOGINPANEL_CAP_URL_REGISTER'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_Reminded=>array(Caption=>$_['TLOGINPANEL_URL_REMINDER_SENT'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_OnLogout=>array(Caption=>$_['TLOGINPANEL_URL_HAVELOGGEDOUT'],Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    
    CSS_Text=>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_InputArea=>array(Type=>"CSS_Class",BaseCSSClass=>"input"),
    CSS_Button=>array(Type=>"CSS_Class",BaseCSSClass=>"input"),
    CSS_Link=>array(Type=>"CSS_Class",BaseCSSClass=>"a"),
    HideAfterLogin=>array(Type=>"Boolean",DefaultValue=>true,Caption=>$_['TLOGINPANEL_CAP_HideAfterLogin']),
    HideLogout=>array(Type=>"Boolean"),
    Text_EnterMyOffice=>array(Type=>"Caption",DefaultValue=>$_['TLOGINPANEL_TEXT_ENTERMYOFFICE'],Caption=>$_['TLOGINPANEL_TEXT_ENTERMYOFFICE']),
    Text_Logout=>array(Type=>"Caption",DefaultValue=>$_['LOGOUT'],Caption=>$_['LOGOUT']),
    CellPadding=>array (Type=>"Integer")
    );
  }

function Init(&$Control)
  {
  ?><script>
  function TLoginPanel_hash(str)
    {
    var v1,v2,hv,c,sz,h=new Array(),hpos=0;
    for (var i=0;i<str.length;i++)
      {
      c=Math.sqrt(str.charCodeAt(i)*((i+1)*100));
      c=(c-Math.floor(c)).toString();
      sz=c.length;
      if (sz<4) continue;
      sz-=4; if (sz>12) {sz=12;}
      c=c.substr(2,sz);
      for (k=0;k<sz;k++)
        {
        hv=h[hpos];
        if (!hv) {hv=0;}
        h[hpos]=(hv+Number(c.charAt(k)))%24;
        hpos++; if (hpos>10) {hpos=0;}
        }
      }
    res="";
    for (i=0;i<h.length;i++)
      {
      res+=String.fromCharCode(h[i]+97);
      }
    return res;
    }

  function showSecDet(FormID)
    {
    P$.find('p10_'+FormID).style.display='none';
    P$.find('p9_'+FormID).style.display='block';
    showDetailsMode=1;
    }
  function UpdateHash(FormID)
    {
    var info=P$.find('p9_'+FormID),s="";
    var f=P$.find(FormID);

    s=f.elements['hash'].value=TLoginPanel_hash(f.elements['serverkey'].value+":"+f.elements['login'].value+":"+f.elements['pass'].value);
    info.innerHTML='Server key: '+f.elements['serverkey'].value+'<br>Sending key: '+s;
    }

  function TLoginPanel_submit(FormID)
    {
    UpdateHash(FormID);
    var f=P$.find(FormID);
    f.elements['pass'].value="";
    f.elements['submit1'].disabled=f.elements['submit2'].disabled=1;
//    event.returnValue=false;
    }

  function TLoginPanel_forget(FormID)
    {
    P$.find('login_tab'+FormID).style.display='none';
    P$.find('forget_tab'+FormID).style.display='block';
    P$.find('forget_login'+FormID).value=P$.find('login'+FormID).value;
    }
    </script>
    <?
  return array(DisableCache=>true);
  }


function Render(&$Control)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['um'];
  global $cfg;
  extract ($Control->Properties);

  global $_USER;
  list($t,$button_class)=get_css_pair($CSS_Button,"input");
  list($t,$text_class)  =get_css_pair($CSS_Text,"p");
  list($t,$input_class) =get_css_pair($CSS_InputArea,"input");
  list($t,$link_class)  =get_css_pair($CSS_Link,"a");

  
  $id="login_".$Control->JSBPageControlID;
  $print="<table id='login_tab$id' border='0' ".
    (($Width)?(" width='$Width'"):("")).
    (($Height)?(" height='$Height'"):("")).
    (($CellPadding)?(" cellpadding='$CellPadding'"):("")).">";

    
  $print.="<tr><td $text_class align='right'>$Text_Login</td>
  <td><input size='12' onKeyUp='UpdateHash(\"$id\")' maxlength='22' $input_class type='text' name='login' id='login$id'></td></tr>
  <tr valign='top'><td $text_class align='right'>$Text_Pass</td>
  <td><input onKeyUp='UpdateHash(\"$id\")' $input_class size='12' maxlength='22' type='password' name='pass'>
  <br/><div id='p10_$id'><a class='tiny' href='javascript:;' onClick='showSecDet(\"$id\");'>$_[TLOGINPANEL_SHOWSECURITY]</a></div><div style='display:none;' class='tiny' id='p9_$id'>Waiting login name</div></td></tr>
  <tr><td align='right'><input type='checkbox' name='keeplocked' value='1'/></td><td $text_class>$Text_KeepPass</td></tr>
  <tr><td></td><td class='mini'>";
#  <input $button_class type='submit' name='submit1' value='$Button_Enter'>
  $print.=$_ENV->PutButton(array(ToString=>1,Caption=>$Button_Enter,Action=>'submit'));
  $print.="</td></tr>";
  $s="";

	if ($URL_OnLogout) {
		$inf=BindPathInfo($URL_OnLogout);
		$URL_OnLogout=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];
	}
  if (($Caption_Register)&&($URL_Register)&&(!$cfg['Settings']['um']['DisableRegistration']))
    {
    $inf=BindPathInfo($URL_Register);
    $URL_Register=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];
    $s="<a $link_class href='$URL_Register'>$Caption_Register</a>";
    }


  if (($Caption_Forget) && $_ENV->IsCartridgeActive('mail')) {
    if ($s) {$s.="<br>";}
    $s.="<a $link_class href='javascript:TLoginPanel_forget(\"$id\");'>$Caption_Forget</a>";
    }
  $print.="<tr><td></td><td >$s</td></tr>";
  $print.="</table>";

  $inf=BindPathInfo($URL_Success);
  $URL_Success=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];

  $inf=BindPathInfo($URL_Error);
  $URL_Error=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];

  $inf=BindPathInfo($URL_Reminded);
  $URL_Reminded=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];

  if (($HideAfterLogin) && ($_USER->UserID)  && (!$HideLogout))
    {
    print "<center><a href='$URL_Success'>$Text_EnterMyOffice</a><br><br>
    <form method='post' action='".ActionURL("um.ILogin.Logout.n",array(ForwardTo=>$URL_OnLogout))."'>";
    $_ENV->PutButton(array(Action=>'submit',Kind=>'cancel',Caption=>$Text_Logout));
    print "</form><br/></center>";
    }

  if ((!$Control->EditMode)&&( (!$HideAfterLogin) || (!$_USER->UserID)))
    {
    $ServerKey=uniqid("srv_"); $now=time();
    DBExec ("INSERT INTO um_PublishedKeys (PubKey,IPaddr,PubDate) VALUES ('$ServerKey','$_SERVER[REMOTE_ADDR]',$now)");

    $print="<form method='post' onSubmit='TLoginPanel_submit(\"$id\")' name='$id' id='$id' action='".
      ActionURL("um.ILogin.Enter.n")."'>
        $print
        <input type='hidden' name='URL_Success' value='$URL_Success'>
        <input type='hidden' name='URL_Error' value='$URL_Error'>
        <input type='hidden' readonly name='serverkey' value='$ServerKey'>
    <br><input type='hidden' readonly name='hash'>
        </form>
        ";
    if ($Caption_Forget) {
    	$print.="<form method='post' onSubmit='TLoginPanel_submit(\"$id\")' name='$id' id='$id' action='".
      ActionURL("um.ILogin.Forget.n")."'>
        <table id='forget_tab$id' style='display:none;'><tr><td>"
	       ."<input size='12' maxlength='22' $input_class type='text' name='login' id='forget_login$id'></td><td>"
	       .$_ENV->PutButton(array(ToString=>1,Caption=>$__['CAPTION_OK'],Action=>'submit'))
	       ."</td></tr><tr><td colspan='2'>$Text_ForgetPassword</td></tr></table>"
         ."<input type='hidden' name='URL_Reminded' value='$URL_Reminded'>"
         ."</form>";
    }
    print $print;
    }
  else
    {
    if ($Control->EditMode)
      {
      print $print;
      }
    }
  }
}
?>
