<?
class um_IUsers
{
var $CopyrightText="(c)2005 JhAZZ Site Builder.User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
    UserManager=>"ViewIAccess,RestoreUser,View,Edit,EditPassword,Browse,UpdateUserData,UpdateUsers,PurgeRemovedUsers,ModifyPassword"
   );

function um_IUsers()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  $this->Title=$_['TITLE_USER_MANAGEMENT_SYSTEM'];
  }

function RestoreUser($args)
  {
  extract(param_extract(array(
    UserID=>'int'),$args));
  if ($UserID)
    {
    DBExec ("UPDATE um_Users SET Removed=0 WHERE UserID=$UserID");
    }
  return array(ModalResult=>true);
  #array(ForwardTo=>ActionURL ("um.IUsers.Browse.bm",$args));
  }

function tab_Groups($k,$row,$v)
  {
  global $quingroups,$qgroups;
  if (!$quingroups->Rows[$k])
    {
    print "<b>[NO GROUP SET]</b>";
    return;
    }
  foreach ($quingroups->Rows[$k] as $GroupID=>$rr)
    {
    $g=$qgroups->Rows[$GroupID];
    if ($g) $s=$g->Caption; else $s="[$GroupID: REMOVED GROUP]";
    if ($GroupID==-3) $s="<font color='red'><b>$s</b></font>";
    print "<li>$s</li>";
    }
  }
function tab_Login($k,$row,$v)
  {
  print "<a href='javascript:;' onClick='W.openModal({url:\""
  .ActionURL("um.IUsers.Edit.b",array(UserID=>$k))."\",w:550,h:350,Title:\"Edit the user\",reloadOnOk:1})'>$row->Login</a>";
  }


function Browse ($args)
  {
  extract(param_extract(array(
    ShowRemoved=>'int=0',
    PageNo=>'int=1',
    RowsPerPage=>'int=20',
    Groups=>'int_checkboxes'),$args));
  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$qgroups,$quingroups;

  print "<h2>$_[MSG_THESE_ARE_USERS]</h2>";
  $s="";
  if ($ShowRemoved) $s.="u.Removed=1"; else $s.="u.Removed=0";
  if ($Groups) {$s=(($s)?"$s AND ":"")." ig.GroupID IN (".implode(",",array_keys($Groups)).")";}
  $ss1=(($s)?"AND $s":"");
  $ss2=(($s)?"WHERE $s":"");

  $s1="SELECT COUNT(*) AS UsersCount FROM um_Users u,um_UserInGroups ig WHERE ig.UserID=u.UserID $ss1 GROUP BY u.UserID";
  $s2="SELECT u.UserID, u.Login, u.Email, u.Place, ig.GroupID, u.Activated
  FROM um_UserInGroups AS ig RIGHT JOIN um_Users AS u ON ig.UserID = u.UserID
  $ss2 GROUP BY u.UserID ORDER BY u.Login LIMIT ".(($PageNo-1)*$RowsPerPage).",".$RowsPerPage;

  $qcount=DBQuery ($s1);
  $qgroups=DBQuery ("SELECT * FROM um_UserGroups ORDER BY GroupID","GroupID");
  $qusers=false;

  print "<table width='100%' cellspacing='10' border='0'>";
  print "<tr><td></td><td>";

  if ($ShowRemoved)
    {
    $a=ActionURL ("um.IUsers.Browse.bm",array(ShowRemoved=>0)+$args);
    $b=ActionURL ("um.IUsers.PurgeRemovedUsers.bm",$args);
    print "<a href='$a'>$_[CAPTION_HIDEREMOVED]</a> | <a href='$b'>$_[CAPTION_PURGEREMOVED]</a>";
    }
  else
    {
    $a=ActionURL ("um.IUsers.Browse.bm",array(
      ShowRemoved=>1,
      RowsPerPage=>$RowsPerPage,
      Groups=>$Groups,
      PageNo=>$PageNo));
    print "<a href='$a'>$_[CAPTION_SHOWREMOVED]</a>";

    }

  print "</td><td align='right'>";
  if ($qcount)
    {
    $qusers=DBQuery ($s2,"UserID");
    $Count=$qcount->RowCount;
    $PageCount=ceil($Count/$RowsPerPage);
    $Pages="";
    $_ENV->PutPages(array(
      PageCount=>$PageCount,
      PageNo=>$PageNo,
      ToURL=>ActionURL ("um.IUsers.Browse.bm",array(
          ShowRemoved=>$ShowRemoved,
          RowsPerPage=>$RowsPerPage,
          Groups=>$Groups))
      ));

/*      if ($PageCount>1) { $Pages="Pages: "; for ($i=1;$i<=$PageCount;$i++)
          {
          $ii=$i;
          $a=ActionURL ("um.IUsers.Browse.bm",array(
            ShowRemoved=>$ShowRemoved,
            RowsPerPage=>$RowsPerPage,
            Groups=>$Groups,
            PageNo=>$i-1));

          if ($i!=$PageNo+1) {$ii="<a href='$a'>$ii</a>";}
          $Pages.=$ii.' ';
          }
          */
#        }
#      print $Pages;
    }

  print "</td></tr><tr valign='top'><td width='25%'>";
  if ($qgroups)
    {
    $a=ActionURL ("um.IUsers.Browse.bm",array(
      ShowRemoved=>$ShowRemoved,
      RowsPerPage=>$RowsPerPage
      ));
    print "<form method='get' action='$a'>";

    foreach ($qgroups->Rows as $GroupID=>$group)
      {
      print "<input type='checkbox' name='Groups[$GroupID]' value='1'".(($Groups[$GroupID])?" checked":"").">$group->Caption<br>";
      }
    print "<br>$_[CAPTION_SHOWUSERINGROUPS]<br><br>";
    $_ENV->PutButton(array(Caption=>$__['CAPTION_SHOW'],Action=>'submit'));
    #<input class='button' type='submit' value='$__[CAPTION_SHOW]'>
    print "</form>";
    }

  print "</td><td align='center'>";
  if ($qusers)
    {
    $userlist=implode (",",array_keys($qusers->Rows));

    $quingroups=DBQuery ("SELECT UserID,GroupID FROM um_UserInGroups WHERE UserID IN ($userlist)",array("UserID","GroupID"));
#      require_once ($cfg['CorePath']."/tabloid.inc.php");

    $SubactionList=array('activate'=>$_['CAPTION_ACTIVATE'],'a'=>$_['CAPTION_INCLUDE_TOGROUP']);
    foreach ($qgroups->Rows as $GroupID=>$g)
      {
      $SubactionList["b_".$GroupID]="[+] ".$g->Caption;
      }
    $SubactionList['c']=$_['CAPTION_EXCLUDE_FROMGROUP'];
    foreach ($qgroups->Rows as $GroupID=>$g)
      {
      $SubactionList["d_".$GroupID]="[-] ".$g->Caption;
      }

    $_ENV->PrintTable($qusers,array(
      Action=>"um.IUsers.UpdateUsers.b",
      Modal=>1,
      Fields=>array(UserID=>'#',Login=>$_['CAPTION_LOGIN'],
        Email=>$_['CAPTION_EMAIL'],
        Place=>"",
        Groups=>$_['CAPTION_GROUPS'],
        Activated=>$_['CAPTION_ACTIVATED']),
      ShowCheckers=>true,
      FieldHooks=>array(Groups=>tab_Groups,Login=>tab_Login),
      ShowDelete=>true,
      ShowOk=>true,
      CSS_TabHead=>"tabhead",
      CSS_Row=>"tab",
      TableStyle=>1,
      SubactionList=>$SubactionList,
      ThisObject=>&$this
      ));

    print "Total users:$Count";
    }
  print "</td></tr></table>";
  }

function Edit($args)
  {
  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  extract(param_extract(array(
    UserID=>'int'
    ),$args));

  $qgroups=DBQuery("SELECT * FROM um_UserGroups ORDER BY GroupID","GroupID");
  $quser=DBQuery ("SELECT * FROM um_Users WHERE UserID=$UserID");
  $qgin=DBQuery ("SELECT * FROM um_UserInGroups WHERE UserID=$UserID","GroupID");

  if ($quser)
    {
    $v=$quser->Top;
    }
  else
    {
    $v=new stdclass;
    $v->Activated=1;
    }

  print "<form method='post' action='".ActionURL("um.IUsers.UpdateUserData.b")."'>";
  printf ("<h2>$_[TITLE_EDITUSER]</h2><center>",$v->Login);
  print "<table border=0 cellspacing='10'>";
  print "<tr><td align='right'><b>$_[CAPTION_LOGIN]:</b></td><td><input type='text' class='inputarea' name='Login' value='$v->Login'></td><td>";

  if ($v->Removed==1)
    {
    print "<a href='".ActionURL("um.IUsers.RestoreUser.bm",$args)."'>$_[IUSERS_RESTORE_REMOVED_USER]</a>";
    }
  print "</td></tr>";

  if ($UserID)
    {
    print "<tr valign='top'><td align='right'><b>$_[CAPTION_PASSWORD]:</b></td>
    <td>$v->PASSWORD<br><a href='javascript:;' onClick='W.openModal({url:\""
    .ActionURL("um.IUsers.EditPassword.b",array(UserID=>$UserID))."\",w:350,h:200,Title:\"$_[CAPTION_CHANGE_PASS]\",reloadOnOk:1})'>$_[CAPTION_CHANGE_PASS]</a><br><br></td><td></td></tr>";
    }

  print "<tr valign='top'><td align='right'>$_[CAPTION_GROUPSIN]:</td><td>";
  while (list($k,$v1)=each($qgroups->Rows))
    {
    $ch="";
    if ($qgin->Rows[$k]) {$ch="checked";}
    print "<input type='checkbox' name='Groups[$k]' value=1 $ch>$v1->Caption<br>";
    }

  print "</td>
  <td colspan='2'>
  <b>$_[CAPTION_EMAIL]<br><input type='text' class='inputarea' name='Email' size='40' maxlength='200' value='$v->Email'></b>
  <br><input name='Activated' type='checkbox' value='1'".(($v->Activated)?" checked":"").">$_[CAPTION_ACTIVATED]
  <br><b>$_[CAPTION_REMARKS]</b><br><textarea class='inputarea' rows='5' cols='40'  name='About'>$v->About</textarea>
  
  </td></tr>
  </table>
  <input type='hidden' name='UserID' value='$UserID'>";
  $_ENV->PutButton('submit');
  $_ENV->PutButton('cancel');
  print "<br><br><hr>";
  $_ENV->PutButton(array(Caption=>"Access details",OnClick=>"W.openModal({url:'"
  .ActionURL("um.IUsers.ViewIAccess.b",array(UserID=>$UserID))."',w:700,h:550})"));

  }

function UpdateUserData($args)
  {
  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  extract(param_extract(array(
    Login=>'string',
    About=>'string',
    Email=>'string',
    PASSWORD=>'string',
    UserID=>'int',
    Activated=>'int',
    Groups=>'int_array'),$args));

  $result=array(ModalResult=>true);
  if ($UserID)
    {
    DBExec ("UPDATE um_Users SET Activated=$Activated,Email='$Email',Login='$Login', About='$About' WHERE UserID=$UserID");
    DBExec ("DELETE FROM um_UserInGroups WHERE UserID=$UserID");
    }
  else
    {
    $q=DBQuery ("SELECT UserID FROM um_Users WHERE Login='$Login'");
    if ($q)
      {
      return array(Message=>$_['MSG_USER_ALREADY_EXISTS'],Details=>"'$Login'");
      }
    $UserID=DBGetID("um.User");
    if (!DBExec ("INSERT INTO um_Users (UserID,Login,Email,About,PASSWORD,Activated) VALUES ($UserID,'$Login','$Email','$About','$PASSWORD',$Activated)"))
      {
      return array(Error=>"Cannot create new User",Details=>"UserID: $UserID, Login: $Login");
      }
    $result=array(ForwardTo=>ActionURL("um.IUsers.Browse.bm"));
    }
  if ($Groups)
   {
   foreach ($Groups as $k=>$v)
     {
     $s="INSERT INTO um_UserInGroups (UserID,GroupID) VALUES ($UserID,$k)";
     if (!DBExec ($s)) return;
     }
   }
  return $result;
  }

function EditPassword($args)
  {
  $_= &$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  extract(param_extract(array(
    UserID=>'int'),$args));

  $q=DBQuery ("SELECT Login FROM um_Users WHERE UserID=$UserID");
  if (!$q)
    {
    return array(Error=>"User not found",Details=>$UserID,IntruderAlert=>100);
    }
  printf ("<h2>$_[TITLE_CHANGE_PASSWORD]</h2>",$q->Top->Login);
  if ($err) {print "<h3><font color='red'>$_[CAPTION_ERROR]$err</font></h3>";}
  print "<form method='post' action='".ActionURL("um.IUsers.ModifyPassword.b",array(UserID=>$UserID))."'><input type='hidden' name='action' value='domodifypassword'>";
  print "<table><tr>
  <td>$_[CAPTION_ENTER_NEW_PASSWORD]</td><td><input type='password' name='pass1'></td></tr>
  <tr><td>$_[CAPTION_RETYPE_PASSWORD]</td><td><input type='password' name='pass2'></td></tr>
  <tr><td align='right'>";
  $_ENV->PutButton('submit');
  print "</td><td>";
  $_ENV->PutButton('cancel');
  print "</td></tr></table>";
  }

function PurgeRemovedUsers($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  $q=DBQuery ("SELECT Login FROM um_Users WHERE Removed=1","Login");
  $Count=$q->RowCount;
  print "<table width='100%'>";

  if ($Count)
    {
    if (DBExec ("DELETE FROM um_Users WHERE Removed=1"))
      {
      print "<tr><td align='center'><h2>$_[CAPTION_PURGE_OK]</h2>";
      foreach ($q->Rows as $Login=>$r)
        {
        print "'$Login' ";
        }
      print "</td></tr>";
      }
    }
  else
    {
    print "<tr><td align='center'>$_[MSG_NOTHING_TO_PURGE]</td></tr>";
    }
    print "<tr><td align='center'><br><input class='button' type='button' value='$__[CAPTION_OK]' onClick='location.href=\"".ActionURL("um.IUsers.Browse.bm",$args)."\"'></td>
    </table>";
  }

function UpdateUsers($args)
  {
  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  extract(param_extract(array(
    UserID=>'int',
    subaction=>'string',
    action=>'string',
    check=>'int_array'),$args));

   

  if ($action=='delete')
    {
    DBExec ("UPDATE um_Users SET Removed=1 WHERE UserID IN (".implode (',',array_keys($check)).")");
    return array(ModalResult=>true);
    }

  if ($subaction=='activate') 
  {
    DBExec ("UPDATE um_Users SET Activated=1 WHERE UserID IN (".implode (',',array_keys($check)).")");
    return array(ModalResult=>true);
  }
  if (substr($subaction,0,2)=='b_')
    {
    $AppendToGroupID=intval(substr($subaction,2));
    foreach ($check as $UserID=>$x)
      {
      	$s="REPLACE um_UserInGroups (UserID,GroupID) VALUES ($UserID,$AppendToGroupID)";
	      DBExec ($s);
      }
    }

  if (substr($subaction,0,2)=='d_')
    {
    $RemoveFromGroupID=intval(substr($subaction,2));
    foreach ($check as $UserID=>$x)
      {
      DBExec ("DELETE FROM um_UserInGroups WHERE UserID=$UserID AND GroupID=$RemoveFromGroupID");
      }
    }
  return array(ModalResult=>true);
  }

function ModifyPassword($args)
  {
  $_ =&$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;

  extract(param_extract(array(
    UserID=>'int',
    pass1=>'string',
    pass2=>'string'
    ),$args));

  $q=DBQuery ("SELECT Login FROM um_Users WHERE UserID=$UserID");
  if (!$q) { $err=sprintf ($_[MSG_INVALID_TARGET_USER],$UserID);}
  if (!$pass1) {$err=$_[MSG_PASSWORD_EMPTY];}
  else
    {
    if ($pass1!=$pass2) {$err=$_[MSG_BAD_PASS_RETYPE];}
    }

  if (!$err)
    {
    DBExec ("UPDATE um_Users SET Password='$pass1' WHERE UserID=$UserID");
    $args['pass1']=$args['pass2']=false;
    return array(ModalResult=>true);
    }
  else
    {
    print "<script>alert ('$err'); history.back();</script>";
    }
  }

function ViewIAccess($args)
  {
  $_= &$GLOBALS['_STRINGS']['um'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;

  extract(param_extract(array(
    UserID=>'int',
    ),$args));

  $qprotected=DBQuery ("SELECT CONCAT(Cartridge,'.',InterfaceName) AS CartInterface FROM um_ProtectIntf","CartInterface");
  $qgropin=DBQuery ("SELECT GroupID FROM um_UserInGroups WHERE UserID=$UserID","GroupID");

  $AllowedMethods=false;
  $ApplyedRoles=false;
  if ($qgropin)
    {
    $GroupsList=implode (",",array_keys($qgropin->Rows));
    $qroles=DBQuery ("SELECT CONCAT(Cartridge,'.',Role,'.',AllowMode) AS RoleAndMode,AllowedMethods FROM um_GroupRoles WHERE GroupID IN ($GroupsList) GROUP BY RoleAndMode","RoleAndMode");
    if ($qroles)
      {
      foreach($qroles->Rows as $ram=>$r)
        {
        if (!$ram) {continue;}
        list ($Cartridge,$Role,$Mode)=explode (".",$ram,3);
        if (($Mode==2)||(isset($qroles->Rows["$Cartridge.$Role.2"])))
          {
          continue;
          }
        $ApplyedRoles["$Cartridge.$Role"]=true;
        $cimethods=explode ("|",$r->AllowedMethods);
        foreach ($cimethods as $cimethod)
          {
          if (!$cimethod) {continue;}
          list ($iname,$imethods)=explode (":",$cimethod);
          $methods=explode(",",$imethods);
          foreach ($methods as $method)
            {
            $AllowedMethods["$Cartridge.$iname.$method"]=true;
            }
          }
        }
      }


    if ($ApplyedRoles)
      {
      print "<h2>$_[TEXT_THESE_ARE_USERROLES]</h2><table border='0'><tr valign='top'>";
      $c="";
      $rowno=0;
      foreach ($ApplyedRoles as $cr=>$x)
        {
        $pc=$c;
        list ($c,$r)=explode (".",$cr);
        if ($c!=$pc)
          {
          if ($rowno>10)
            {
            $rowno=0;
            print "</table></td>";
            }
          if ($rowno==0)
            {
            print "<td><table border='0'>";
            }
          print "<tr><td class='tabhead'><br>$c</td></tr>";
          }
        print "<tr class='tab'><td>&nbsp$r</td></tr>";
        $rowno++;
        }
      print "</table></td></tr></table>";
      }
    else
      {
      print $_['TEXT_NO_ROLES_APPLYIED_TO_USER'];
      }

    if ($AllowedMethods)
      {
      print "<h2>$_[TEXT_THESE_ARE_USERMETHODACCESS]</h2><table border='0'><tr valign='top'>";
      $c=$i="";
      $rowno=0;
      foreach ($AllowedMethods as $cim=>$x)
        {
        $pc=$c; $pi=$i;
        list ($c,$i,$m)=explode (".",$cim);
        if ($c!=$pc)
          {
          if ($rowno>5)
            {
            $rowno=0;
            print "</table></td>";
            }
          if ($rowno==0)
            {
            print "<td><table border='0'>";
            }
          print "<tr><td class='tabhead'><br>$c</td></tr>"; $pi="";
          }
        if ($i!=$pi) {print "<tr><th>$i</td></tr>";}
        print "<tr class='tab'><td>&nbsp .$m</td></tr>";
        $rowno++;
        }
      print "</table></td></tr></table>";
      }

    }
  print "<hr>";
  $_ENV->PutButton("cancel");
  }
}
