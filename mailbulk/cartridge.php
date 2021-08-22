<?
class mailbulk
{
function mailbulk()
  {
  $_=&$GLOBALS['_STRINGS']['mailbulk'];
  $this->Title=$_['TITLE'];
  $this->Roles=array(
    EditSubscriberList=>$_['ROLE_EDITSUBSCRIBERLIST'],
    BulkOperator=>$_['ROLE_BULKOPERATOR'],
    );
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['mailbulk'];
  return array(
    TSubscribeForm=>array(Caption=>$_['TSUBSCRIBEFORM_CAPTION'],Description=>$_['TSUBSCRIBEFORM_DESCRIPTION'],Icon=>''),
    TUnsubscribe=>array(Caption=>$_['TUNSUBSCRIBE_CAPTION'],Description=>$_['TUNSUBSCRIBE_DESCRIPTION'],Icon=>''),
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['mailbulk'];
  return array (
    array
      (
      PutToCategory=>"tools",
      CreateCategory=>"mailer",
      Caption=>$_['TITLE'],
        Items=>array(
        array(Caption=>$_['MENUCAPTION_EDIT_SUBSCRIBERS'],Call=>"mailbulk.ISubscribers.Browse.bm"),
        array(Caption=>$_['MENUCAPTION_BULKEMAIL'],Call=>"mailbulk.IBulkSend.Prepare.bm"),
        array(Caption=>$_['MENUCAPTION_EDIT_SUBJECTS'],Call=>"mailbulk.ISubscribers.EditSubjects.bm"),
        )
      ),
    );
  }
}
?>
