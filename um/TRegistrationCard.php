<?php
class um_TRegistrationCard
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. User management controls package";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $q=DBQuery ("SELECT UserTypeID,Caption FROM um_UserTypes","UserTypeID");
  if ($q) foreach ($q->Rows as $utid=>$row)  $UserTypes[$utid]=$row->Caption;

  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $this->Propdefs=array(
    Text_Login   =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TLOGINPANEL_CAP_LOGIN'],Caption=>$_['TLOGINPANEL_CAP_LOGIN']),
    Text_PassOld =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TLOGINPANEL_CAP_PASSWORD_OLD']),
    Text_Pass    =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TLOGINPANEL_CAP_PASSWORD']),
    Text_Pass2   =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TREGCARD_CAP_PASSWORD2']),
    Text_Email   =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TREGCARD_CAP_EMAIL']),
    Text_Place   =>array (Type=>"Caption",Required=>true,DefaultValue=>$_['TREGCARD_CAP_PLACE']),
    Text_Ok      =>array (Type=>"Caption",Required=>true,DefaultValue=>$__['CAPTION_OK'],Caption=>$__['CAPTION_OK']),

    UserTypeID=>array(Type=>"List",Caption=>$_['TREGCARD_P_USERTYPE'],Values=>$UserTypes),
    CSS_Text=>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_InputArea=>array(Type=>"CSS_Class",BaseCSSClass=>"input",DefaultValue=>"input.inputarea"),
    CSS_Link=>array(Type=>"CSS_Class",BaseCSSClass=>"a",DefaultValue=>"a"),
    CSS_Error=>array(Type=>"CSS_Class"),
    URL_Success=>array (Type=>"InputModal",Caption=>$_['TREGCARD_URL_SUCCESS'],Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    URL_AfterActivation=>array (Type=>"InputModal",Caption=>$_['TREGCARD_URL_AFTERACTIVATION'],Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    );
  }
function Init (&$Control) {
  if ($_GET['ActivationKey']) {
  	$ireg=$_ENV->LoadInterface("um.IRegistration");
  	$r=$ireg->_activateAccount(array(Login=>$_GET['Login'],ActivationKey=>$_GET['ActivationKey']));
  	if ($r['Ok'] && $Control->Properties['URL_AfterActivation']) {
  		return array(ForwardTo=>$Control->Properties['URL_AfterActivation']);
  	}
  	$Control->ActivationInfo=$r;
  }
  
}

function Render(&$Control)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['um'];
  global $cfg,$_USER;

  if ($cfg['Settings']['um']['DisableRegistration']) {
  	print "User registration disabled";
  	return;
  }

  extract ($Control->Properties);

  list($t,$text_class)  =get_css_pair($CSS_Text,"p");
  list($t,$input_class) =get_css_pair($CSS_InputArea,"input");
  list($t,$link_class)  =get_css_pair($CSS_Link,"a");

  if ($Control->ActivationInfo) {
  	print $Control->ActivationInfo['Message'];
  	return;
  }
  
  $errlist=$_GET['errors'];
  if ($errlist)
    {
    $errors=explode('_',$errlist);
    list($t,$cls)  =get_css_pair($CSS_Error,"span");
    $sf="<td><$t $cls><font color='red'>%s</font></$t></td>";
    if (in_array('1',$errors)) {$err1=sprintf($sf,$_['ERR_LOGINDEFINED']);}
    if (in_array('2',$errors)) {$err2=sprintf($sf,$_['ERR_EMPTYPASSWORD']);}
    if (in_array('3',$errors)) {$err3=sprintf($sf,$_['ERR_PASSWORDMISS']);}
    if (in_array('4',$errors)) {$err4=sprintf($sf,$_['ERR_NOEMAIL']);}
    if (in_array('5',$errors)) {$err5=sprintf($sf,$_['ERR_BADOLDPASSWORD']);}
    }
  switch ($_GET['msg'])
    {
    case '1': $message=$_['MSG_USER_REGISTERED']; break;
    case '2': $message=$_['MSG_USER_UPDATED']; break;
    case '3': $message=$_['MSG_ACTIVATION_KEY_SENT']; break;
#    case '4': $message=$_['REGISTRATION_ACTIVATED']; break;
    }

  if ($message) {
  	print $message;
  	return;
  }
  	
  $gch1="checked"; $gch2="";
  if ($_USER->UserID)
    {
    $Login=$_USER->Login;
    $Email=$_USER->Email;
    $Place=$_USER->Place;
    }

  $inf=BindPathInfo($URL_Success);
  $URL_Success=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];
  global $_THEME;


  ?>
  <script>
  var timeout1;
  var checkedLogin;
  var updateMode=<? print ($_USER->UserID)?"true":"false"; ?>;
  var identchars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
  function checkRegForm()
    {
  	if (timeout1!=undefined) window.clearTimeout(timeout1);
  	timeout1=window.setTimeout('timeoutCheckForm();',500);
  }
  function checkLogin() {
   	var login=document.getElementById('reg_Login').value;
   	if ((checkedLogin!=undefined)&&(checkedLogin==login)) return;
   	var img_DropWait=document.getElementById('img_DropWait');
   	var img_DropError=document.getElementById('img_DropError');
   	var img_DropOk=document.getElementById('img_DropOk');
   	img_DropOk.style.display=img_DropError.style.display='none';
   	if (login.length<3) {
    	if (timeout1!=undefined) {window.clearTimeout(timeout1); timeout1=undefined;}
   		img_DropWait.style.display='none';
   		return;
   	}
   	img_DropWait.style.display='block';
  	var d=document.getElementById('WarningDuplicate');
		var ifr=document.getElementById('iframe_reg');
		d.innerHTML='';
		ifr.src="<? print ActionURL("um.IRegistration.IFrameCheckLogin.n"); ?>?Login="+login;
	}
	function timeoutCheckForm() {
  	window.clearTimeout(timeout1); timeout1=undefined;
  	var btnSubmit=document.getElementById('btnSubmit');
    btnSubmit.disabled=true;
    
    if (!updateMode) {
			var login=document.getElementById('reg_Login').value;
			var v=login; var v2='';
      for (var i=0;i<v.length;i++){c=v.charAt(i);if(identchars.indexOf(c)!=-1){v2+=c;}}
      if (v2!=login) document.getElementById('reg_Login').value=v2;
			
			if ((checkedLogin==undefined)||(checkedLogin!=login)) {
				checkLogin();
			}
    }

    var passOld;
		var pass1=document.getElementById('reg_Pass1').value;
  	var pass2=document.getElementById('reg_Pass2').value;
  	var d=document.getElementById('WarningPass');
  	if ((pass1.length<2)||(pass2.length<2)) {
  		return;
  	}

  	if (pass1!=pass2) {
  		d.innerHTML='<font color="red"><? print $_['MSG_BAD_PASS_RETYPE']; ?></font>';
  		return;
  	} 
  	d.innerHTML="";

  	if (updateMode) {
  		passOld=document.getElementById('reg_PassOld').value;
	  	var d=document.getElementById('WarningOldPass');
  		if ((pass1.length>2)&&(pass2.length>2)&&(passOld.length==0)) {
  			d.innerHTML='<font color="red"><? print $_['ERR_BADOLDPASSWORD']; ?></font>';
  			return;
  		}
  		if (passOld.length==0) return;
  		d.innerHTML="";
  	}
  	
  	
  	var email=document.getElementById('reg_Email').value;
  	if (email.length<1) return;
  	var d=document.getElementById('WarningEmail');
    if ((email.indexOf('@')==-1)||(email.indexOf('.')==-1)||(email.indexOf('.')>(email.length-3))) {
  		d.innerHTML='<font color="red"><? print $_['ERR_NOEMAIL']; ?></font>';
  		return;
  	} 
  	d.innerHTML="";
		
    btnSubmit.disabled=false;
		
	}
  function loginExists(login,exists) {
  	
  	if ((checkedLogin!=undefined)&&(checkedLogin==login)) return;
   	var img_DropWait=document.getElementById('img_DropWait');
   	var img_DropError=document.getElementById('img_DropError');
   	var img_DropOk=document.getElementById('img_DropOk');
   	
   	img_DropWait.style.display='none';
  	var d=document.getElementById('WarningDuplicate');
  	checkedLogin=login;
  	checkedLoginExists=exists;
  	if (exists) {
  		img_DropOk.style.display='none';
  		img_DropError.style.display='block';
  		d.innerHTML='<font color="red">'+login+' - <? print $_['MSG_USER_ALREADY_EXISTS'];?></font>'; 
  		
  	} else {
  		img_DropOk.style.display='block';
  		img_DropError.style.display='none';
  		d.innerHTML='';
  	}
  }
  </script>
  <?
  print "<form id='regForm' method='post' action='".ActionURL("um.IRegistration.Update.f")."'>
    <table>"; 
    
  if ($_USER->UserID) 
  print "<tr><td $text_class align='right'>$Text_Login</td><td>$Login</td></tr>
     <tr valign='top'><td $text_class align='right'>$Text_PassOld<font color='red'>*</font></td><td><input type='password' id='reg_PassOld' name='PassOld' onKeyUp='checkRegForm()' onChange='checkRegForm()' $input_class maxlength='20' size='10'>
     <div id='WarningOldPass'></div></td>$err5</tr>";
  else print "<tr valign='top'><td $text_class align='right'>$Text_Login <font color='red'>*</font></td><td><table cellpadding='0' cellspacing='0'><tr><td><input type='text' id='reg_Login' name='Login' onChange='checkRegForm()' $input_class maxlength='20' size='10' value='$Login'></td>
      <td id='img_DropWait' style='display:none;'><img src='".$_THEME['SkinURL']."/btn-dropwait.gif'></td>
      <td id='img_DropError' style='display:none;'><a href='javascript:timeoutCheckForm();'><img src='".$_THEME['SkinURL']."/btn-droperror.gif' border='0'></a></td>
      <td id='img_DropOk' style='display:none;'><a href='javascript:timeoutCheckForm();'><img src='".$_THEME['SkinURL']."/btn-dropok.gif' border='0'></a></td>
      </tr></table>
    </td></tr><tr><td colspan='2'  id='WarningDuplicate' ></td></tr><tr>$err1</tr>";
  
  print "<tr valign='top'><td $text_class align='right'>$Text_Pass <font color='red'>*</font></td> <td><input type='password' id='reg_Pass1' name='Pass1' onKeyUp='checkRegForm()' onChange='checkRegForm()' $input_class maxlength='20' size='10'></td>$err2</tr>
    <tr valign='top'><td $text_class align='right'>$Text_Pass2 <font color='red'>*</font></td><td><input type='password' id='reg_Pass2' name='Pass2' onKeyUp='checkRegForm()' onChange='checkRegForm()' $input_class maxlength='20' size='10'>
   	 <div id='WarningPass'></div></td>$err3</tr>
    <tr valign='top'><td $text_class align='right'>$Text_Email <font color='red'>*</font></td><td><input type='text' id='reg_Email' name='Email' onKeyUp='checkRegForm()' onChange='checkRegForm()' $input_class maxlength='80' size='30' value='$Email'>
    <div id='WarningEmail'></div>
    </td>$err4</tr>";
  if ($Text_Place) 
    print "<tr valign='top'><td $text_class align='right'>$Text_Place</td><td><input type='text' name='Place' $input_class maxlength='80' size='30' value='$Place'></td></tr>";
  print "<tr valign='top'><td></td><td>"
    .$_ENV->PutButton(array(ToString=>1,ID=>'btnSubmit',Disabled=>1,Action=>'submit',Caption=>$Text_Ok))
    ."</td></tr>
    </table>
    <input type='hidden' name='UserTypeID' value='$UserTypeID'>
    <input type='hidden' name='URL_Success' value='$URL_Success'>
    </form><script>checkRegForm();</script>
    <iframe style='display:none;' id='iframe_reg'></iframe>";

  return array(DisableCache=>true);
  }

}
?>
