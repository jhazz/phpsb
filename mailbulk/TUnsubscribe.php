<?
class mbulk_TUnsubscribe
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Mail bulk sender";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][mbulk];
  $__=&$GLOBALS[_STRINGS][_];
  $this->Propdefs=array(
    Text_TypeYourEmail=>array(Type=>"Caption",Required=>true,DefaultValue=>"Type your email"),
    Text_Ok           =>array(Type=>"String",Required=>true,Caption=>"Unsubscribe button caption",DefaultValue=>"Unsubscribe"),
    CSS_Text          =>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_InputArea     =>array(Type=>"CSS_Class",BaseCSSClass=>"input",DefaultValue=>"input.inputarea"),
    URL_ThankYou      =>array(Type=>"LocalURL",Caption=>"Page to go to after unsubscribing"),
    Align=>array(Type=>"Align"),
    );
  }

function Init(&$Control)
  {
  global $_SESSION,$_USER;
  if ($_POST['UnsubscribeEmail'])
    {
    extract(param_extract(array(
      UnsubscribeEmail=>"string",
      ),$_POST));
    if ($UnsubscribeEmail) DBExec ("UPDATE mbulk_Subscribers SET Unsubscribing=1 WHERE Email='$UnsubscribeEmail'");
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

  print "<form name='unregform' method='post'><table border=0>
  <tr><$td_tag $td_class>$Text_TypeYourEmail<br/>
  <input type='text' $input_class name='UnsubscribeEmail' onChange='checkUEmail()' onKeyUp='checkUEmail()'/>
  </td></tr><tr><td>";
  $_ENV->PutButton(array(Name=>'btnok',Action=>'submit',Disabled=>true,Caption=>$Text_Ok));
  print "</td></tr></table></form>";
  ?>
  <script>
   function checkUEmail()
     {
     unregform.btnok.disabled=(unregform.UnsubscribeEmail.value=='');
     }
  </script>
  <?
  return array(DisableCache=>true);
  }
}
?>
