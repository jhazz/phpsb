<?
class stdctrls_PThemeStyles
{
#var $d=1;
function GetHorizontalSeparators ($args)
  {
  $Separators=&$GLOBALS['_THEME']['HorizontalSeparators'];
  $ListValues=array();
  foreach ($Separators  as $Name=>$Data)
    {
    $ListValues[$Name]=($Data['Caption'])?$Data['Caption']:$Name;
    }
  return array(ListValues=>$ListValues);
  }

function GetButtonStyles ($args)
  {
  $Buttons=&$GLOBALS['_THEME']['Buttons'];
  $ListValues=array();
  foreach ($Buttons as $ButtonName=>$ButtonData)
    {
    $ListValues[$ButtonName]=($ButtonData['Caption'])?$ButtonData['Caption']:$ButtonName;
    }
  return array(ListValues=>$ListValues);
  }
}

?>
