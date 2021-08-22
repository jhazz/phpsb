<?
class stdctrls_PButton
{

 function stdctrls_PButton () {
   $this->CopyrightText="(c)2003 JhAZZ Site Builder. Button";
   $this->CopyrightURL="http://www.jhazz.com/jsb";
   $this->ComponentVersion="1.0";
 }

 function LoadXML ($args) {
   $Caption=$args['Caption'];
   if ($Caption) {$Caption="caption='$Caption'";}
   return array(XML=>"<open $Caption>".urldecode($args['Link'])."</open>");
 }

function GetListValues ($args)
  {
  $Buttons=$GLOBALS['_THEME']['Buttons'];
  $ListValues=array();
  foreach ($Buttons as $ButtonName=>$ButtonData)
    {
    $ListValues[$ButtonName]=$ButtonData['Caption'];
    }
  return array(ListValues=>$ListValues);
  }

}
?>
