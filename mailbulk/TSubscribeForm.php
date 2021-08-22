<?
class mbulk_TSubscribeForm
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Mail bulk sender";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][mbulk];
  $__=&$GLOBALS[_STRINGS][_];
  $this->Propdefs=array(
    Text_Email       =>array(Type=>"Caption",Required=>true,DefaultValue=>"Email"),
    Text_FirstName   =>array(Type=>"Caption",Required=>true,DefaultValue=>"Your first name"),
    Text_LastName    =>array(Type=>"Caption",Required=>true,DefaultValue=>"Last name"),
    Text_Country     =>array(Type=>"Caption",Required=>true,DefaultValue=>"Country"),
    Text_State       =>array(Type=>"Caption",Required=>true,DefaultValue=>"State"),
    Text_City        =>array(Type=>"Caption",Required=>true,DefaultValue=>"City"),
    Text_SubscribeFor=>array(Type=>"Caption",Required=>true,DefaultValue=>"Select subjects you subscribing for"),
    Text_Ok          =>array(Type=>"Caption",Required=>true,DefaultValue=>"Subscribe"),
    Text_YouHaveSubscribed=>array(Type=>"Caption",Required=>true,DefaultValue=>"You have successfully registered. Thank you for interest"),
    CSS_Text         =>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_InputArea    =>array(Type=>"CSS_Class",BaseCSSClass=>"input",DefaultValue=>"input.inputarea"),
    URL_ThankYou     =>array(Type=>"LocalURL",Caption=>"Page to go to after subscribing"),

    AllowMultipleSubscription=>array (Type=>"Boolean",DefaultValue=>true,Caption=>"Allow visitor to subscribe more than once from one machine"),
    Align=>array(Type=>"Align"),
    );
  }

function Init(&$Control)
  {
  global $_SESSION,$_USER;
  if (!$this->ExistingSubcription)
    {
    $this->ExistingSubcription=DBQuery("SELECT RcptID
      FROM mbulk_Subscribers
      WHERE MachineKey='$_SESSION->MachineKey'","RcptID");
    }

  if ($_POST['Email'])
    {
    extract(param_extract(array(
      Email     =>"string",
      FName     =>"string",
      LName     =>"string",
      Country   =>"string",
      State     =>"string",
      City      =>"string",
      SubscribeFor=>"array",
      ),$_POST));

    if ((!$this->ExistingSubcription) || ($Control->Properties['AllowMultipleSubscription']))
      {
      $RcptID=DBGetID("mbulk.Subscriber");
      $Subject1=intval($SubscribeFor[1]);
      $Subject2=intval($SubscribeFor[2]);
      $Subject3=intval($SubscribeFor[3]);
      $Subject4=intval($SubscribeFor[4]);
      $Subject5=intval($SubscribeFor[5]);
      $time=time();
      DBExec ("INSERT INTO mbulk_Subscribers
        (RcptID,UserID,Email,MachineKey,FName,LName,Country,State,City,Subscribed,
         Subject1,Subject2,Subject3,Subject4,Subject5)
        VALUES($RcptID,$_USER->UserID,'$Email','$_SESSION->MachineKey',
        '$FName','$LName','$Country','$State','$City',$time,
        $Subject1,$Subject2,$Subject3,$Subject4,$Subject5)");
      }

    if ($Control->Properties['URL_ThankYou'])
      {
      return array(ForwardTo=>$Control->Properties['URL_ThankYou']);
      }
    }
  return array(DisableCache=>true);
  }

function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][mbulk];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg,$_USER;
  extract ($Control->Properties);
  list($td_tag,$td_class)=get_css_pair($CSS_Text,"td");
  list($input_tag,$input_class)=get_css_pair($CSS_InputArea,"input");
  if (($this->ExistingSubcription) && (!$Control->Properties['AllowMultipleSubscription']))
    {
    print $Text_YouHaveSubscribed;
    return;
    }


  print "<form name='regform' method='post'><table border=0>
  <tr><$td_tag $td_class align='right'>$Text_Email<font color='red'>*</font></td><td><input type='text' onChange='checkEmail()' onKeyUp='checkEmail()' $input_class name='Email'/></td></tr>
  <tr><$td_tag $td_class align='right'>$Text_FirstName</td><td><input type='text' $input_class name='FName'/></td></tr>
  <tr><$td_tag $td_class align='right'>$Text_LastName</td><td><input type='text' $input_class name='LName'/></td></tr>
  <tr><$td_tag $td_class align='right'>$Text_Country</td><td><input type='text' $input_class name='Country'/></td></tr>
  <tr><$td_tag $td_class align='right'>$Text_State</td><td><input type='text' $input_class name='State'/></td></tr>
  <tr><$td_tag $td_class align='right'>$Text_City</td><td><input type='text' $input_class name='City'/></td></tr>
  <tr><td></td><td><b>$Text_SubscribeFor</b><br/>";

  $q=DBQuery("SELECT SubjectID,Recommend,Caption,LastBulkDate,Recommend
    FROM mbulk_Subjects
    WHERE Hidden=0","SubjectID");
  if ($q)
    {
    foreach($q->Rows as $SubjectID=>$row)
      {
      print "<input type='checkbox' name='SubscribeFor[$SubjectID]' value='1' ".(($row->Recommend)?"checked":"").">$row->Caption<br/>";
      }
    }

  print "</td><tr><td></td><td>";
  $_ENV->PutButton(array(Name=>'btnok',Action=>'submit',Disabled=>true,Caption=>$Text_Ok));
  print "</td></tr></table></form>";
  ?>
  <script>
  var oldbg=regform.Email.style.backgroundColor;
  function checkEmail()
    {
    var st=regform.Email.style;
    if ((regform.Email.value.indexOf('@')==-1)
      ||(regform.Email.value.indexOf('.')==-1)
      ||(regform.Email.value.indexOf('.')>(regform.Email.value.length-3)))
      {
      if (st.backgroundColor!='#ffe0e0')
        {
        oldbg=st.backgroundColor;
        st.backgroundColor='#ffe0e0';
        regform.btnok.disabled=true;
        }
      }
    else
      {
      st.backgroundColor=oldbg;
      regform.btnok.disabled=false;
      }
    }
    </script>
  <?
  return array(DisableCache=>true);
  }
}
?>
