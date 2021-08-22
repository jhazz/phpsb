<?
class um_IRegistration
  {
var $CopyrightText="(c)2005 JhAZZ Site Builder. User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

  function um_IUserGroups()
    {
    $_=&$GLOBALS['_STRINGS']['um'];
    $this->Title=$_['TITLE_USER_MANAGEMENT_SYSTEM'];
    }

  function Activate ($args) {
    if (!$args['ForwardTo']) $args['ForwardTo']=$_SERVER['HTTP_REFERER'];
    $r=$this->_activateAccount();
    $r['TimeoutForwardTo']=$args['ForwardTo'];
    return $r;
  }

  function _activateAccount($args) {
    extract(param_extract(array(
      Login=>'*string',
      ActivationKey=>'*string',
      ForwardTo=>'string'
      ),$args));
    $_ =&$GLOBALS['_STRINGS']['um'];
    $__=&$GLOBALS['_STRINGS']['_'];
    global $cfg,$_USER;
    if (!$cfg['Settings']['um']['CaseSensitiveLogin']) $wh="Login='$Login'"; else $wh="Login LIKE '$Login'";
    $q=DBQuery("SELECT UserID,RandomKey,Activated FROM um_Users WHERE $wh");
    if ((!$q)||($q->Top->Activated==1)) {return array(Message=>'Login or key is invalid'); }
    $UserID=$q->Top->UserID;
    DBExec ("UPDATE um_Users SET Activated=1,RandomKey='' WHERE UserID=$UserID");
    $_USER->AuthorizeSessionToUserID($UserID);
    return array(Message=>$_['REGISTRATION_ACTIVATED'],Ok=>1);
  }
  function IFrameCheckLogin($args)
    {
    $_ =&$GLOBALS['_STRINGS']['um'];
    $__=&$GLOBALS['_STRINGS']['_'];
    global $cfg,$_USER;

    extract(param_extract(array(
      Login=>'string'
      ),$args));
      
    if (!$cfg['Settings']['um']['CaseSensitiveLogin']) $wh="Login='$Login'"; else $wh="Login LIKE '$Login'";
    $s="SELECT UserID FROM um_Users WHERE $wh";
    $q=DBQuery ($s);
    print "<script>parent.loginExists('$Login',".(($q)?1:0).");</script>";
    }

  function Update($args)
    {
    extract(param_extract(array(
      Login=>'string',
      PassOld=>'string',
      Pass1=>'string',
      Pass2=>'string',
      Email=>'string',
      Place=>'string',
      Gender=>'string',
      URL_Success=>'string',
      UserTypeID=>'int'
      ),$args));

    $_ =&$GLOBALS['_STRINGS']['um'];
    $__=&$GLOBALS['_STRINGS']['_'];
    global $cfg,$_USER,$_LANGUAGE;
    
  	$referer=$_SERVER['HTTP_REFERER'];
  	$i=strpos($referer,"?"); if ($i!==false) $referer=substr($referer,0,$i);
    if (!$URL_Success) $URL_Success=$referer;
    
		# CHECK FOR USER ACCESS TO ITS OWN REGCARD
    $errors="";
    if ($_USER->UserID)
      {
			$q=DBQuery ("SELECT PASSWORD FROM um_Users WHERE UserID='$_USER->UserID'");
			if ($q->Top->PASSWORD!=$PassOld){$errors.='_5';}
      if (($Pass1)&&($Pass1!=$Pass2)) {$errors.='_3';}
      if (!$Email) {$errors.='_4';}
      if ($errors) {return array(ForwardTo=>"$referer?errors=$errors");}
      $ps="";
      if ($Pass1) {$ps.=" PASSWORD='$Pass1',";}
      if ($q) {$Login="";}
      if ($Login) {$ps.=" Login='$Login',";}
      $s="UPDATE um_Users SET $ps Email='$Email',Place='$Place',Gender='$Gender' WHERE UserID=$_USER->UserID";
      DBExec ($s);
      $URL_Success=concat_url_args($URL_Success,'msg=2');
      }
    else {
      # REGISTER NEW
      if ($cfg['Settings']['um']['DisableRegistration']) {
      	return array(Message=>"User registration disabled");
      }
      $qut=DBQuery ("SELECT * FROM um_UserTypes WHERE UserTypeID=UserTypeID");
      if (!$qut) {$Pass1="";}
      $utgroups=explode (",",$qut->Top->GroupsIn);
      if (!$utgroups) {$Pass1="";}

      $q=DBQuery ("SELECT Login FROM um_Users WHERE Login='$Login'");
      if ($q) {$errors='_1';}
      if (!$Pass1) {$errors.='_2';} else if ($Pass1!=$Pass2) {$errors.='_3';}
      if (!$Email) {$errors.='_4';}
      if ($errors) {return array(ForwardTo=>"$referer?errors=$errors");}

      # NO LOGIN ACCOUNT FOUND WITH THIS NAME
      $Activated=($cfg['Settings']['um']['ActivatesViaEmail'])?0:1;
      $DateCreate=time();
      $RandomKey=md5(rand());
      $UserID=DBGetID("um.User");
      
      if (!DBExec ("INSERT INTO um_Users (UserID,Login,PASSWORD,Email,Place,Gender,ManagedBy,DateCreate,Activated,RandomKey)
      VALUES ($UserID,'$Login','$Pass1','$Email','$Place','$Gender',1,$DateCreate,$Activated,'$RandomKey')"))
        {
        return array(Error=>$_['ERR_NOUSERADDING'],Details=>"Login: $Login, Email:$Email, ID:$UserID");
        }
      foreach ($utgroups as $GroupID){
        $GroupID=intval($GroupID);
        if ($GroupID) DBExec ("INSERT INTO um_UserInGroups (UserID,GroupID) VALUES ($UserID,$GroupID)");
        }

    	$URL_Login=$cfg['Settings']['um']['URLLoginForm'];
    	if ($URL_Login) {
    		$inf=BindPathInfo($URL_Login);
		  	$URL_Login="http://$cfg[SiteURL]$cfg[RootURL]/$_LANGUAGE/$inf->Context/$inf->ID.$cfg[VirtualExtension]";
    	} else $URL_Login="http://$cfg[SiteURL]$cfg[RootURL]/$_LANGUAGE/";

      $imail=&$_ENV->LoadInterface("mail.PMailSender");
      if (is_object($imail)) {
	      if ($cfg['Settings']['um']['ActivatesViaEmail']) {
		      if ($imail->EnqueueMessage(array(
		      	Cartridge=>"um",
		      	TemplateName=>"AccountActivation",
		      	Language=>$_LANGUAGE,
		      	MailFrom=>$cfg['Settings']['um']['RegistratorEmail'],
	      		MailTo=>$Email,
		      	QueueName=>"UM:Registration activating for [$Login]",
	      		FieldValues=>array(
			        LOGIN_URL=>$URL_Login,
			        ACTIVATION_URL=>$referer."?Login=$Login&ActivationKey=$RandomKey",
			        LOGIN=>$Login,
			        PASS=>$Pass1)
		        ))) return array(ForwardTo=>$referer."?msg=3"); # 3-Activation key has been sent
	      } else {
	      	$_USER->AuthorizeSessionToUserID($UserID);
		      if ($imail->EnqueueMessage(array(
		      	Cartridge=>"um",
		      	TemplateName=>"Registration",
		      	Language=>$_LANGUAGE,
		      	MailFrom=>$cfg['Settings']['um']['RegistratorEmail'],
	      		MailTo=>$Email,
		      	QueueName=>"UM:Registration ok [$Login]",
		      	FieldValues=>array(
			        LOGIN_URL=>$URL_Login,
			        LOGIN=>$Login,
			        PASS=>$Pass1
		        )))) return array(ForwardTo=>$URL_Success);
	      }
      } # if mail cartridge is active
    return array (ForwardTo=>concat_url_args($URL_Success,'msg=1'));
    }
  }

  }