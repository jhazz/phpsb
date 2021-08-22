<?
class msg
{
function msg()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->Title=$_['MSG_CARTRIDGETITLE'];
  $this->Roles=array(
    Moderator=>$_['ROLE_MODERATOR'],
    );
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  return array(
    TPoll=>array(Caption=>$_['TPOLL_CAPTION'],Description=>$_['TPOLL_DESCRIPTION'],Icon=>''),
    TQna=>array(Caption=>$_['TQNA_CAPTION'],Description=>$_['TQNA_DESCRIPTION'],Icon=>''),
    TRateit=>array(Caption=>$_['TRATEIT_CAPTION'],Description=>$_['TRATEIT_DESCRIPTION'],Icon=>''),
    TRateitImg=>array(Caption=>$_['TRATEITIMG_CAPTION'],Description=>$_['TRATEITIMG_DESCRIPTION'],Icon=>''),
#    TTopic=>array(Caption=>$_['TTOPIC_CAPTION'],Description=>$_['TTOPIC_DESCRIPTION'],Icon=>''),
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];

  return array(
    array(
      PutToCategory=>"content",
      CreateCategory=>"msg",
      Caption=>$_['MSG_CARTRIDGETITLE'],
      Items=>array(
        array(Caption=>$_['MENUCAPTION_QNA'],Call=>"msg.IQnaManager.BrowseThreads.bm"),
        array(Caption=>$_['MENU_RATEIT'],Call=>"msg.IRateManager.Browse.bm"),
        array(Caption=>$_['POLLS'],Call=>"msg.IPoll.Browse.bm"),
        )
      ),
    );

  }

function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  return array
    (
    ForumContext=>array(Caption=>"Context contains forum structure",Type=>'syscontext',DefaultValue=>'forum',Required=>1),
    DateFormat=>array(Caption=>$_['SETTING_MSG_DATE_FORMAT'],Type=>'DateFormat',DefaultValue=>'shortdate'),
    MessagesRowCount=>array(Caption=>$_['SETTING_MSG_ROWCOUNT'],Type=>'int',DefaultValue=>20),
    QuarantineMode=>array(Caption=>$_['SETTING_MSG_QUARANTINE_MODE'],Type=>'boolean',DefaultValue=>1),
    QuarantineTime=>array(Caption=>$_['SETTING_MSG_QUARANTINE_TIME'],Type=>'int',DefaultValue=>48),
    );
  }

function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  return array
    (
    "msg.Forum"=>array(Caption=>"Forum",UseSettingsContext=>"ForumContext"),
    );
  }

}
?>
