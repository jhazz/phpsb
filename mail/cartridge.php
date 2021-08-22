<?
class mail
{
function mail()
  {
  $_=&$GLOBALS['_STRINGS']['mail'];
  $this->Title=$_['MAIL_CARTRIDGE'];
  $this->Roles=array(
		QueueManager=>$_['ROLE_QUEUE_MANAGER'],
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['mail'];

  return array (
   array
      (
      PutToCategory=>"tools",
      Items=>array(
        array(Icon=>"ico_outgoing.gif",Caption=>$_['MENU_MAILQUEUES'],Call=>"mail.IMailQueues.Browse.bm"),
        )
      ),
   array
      (
      PutToCategory=>"admin",
      Items=>array(
        array(Icon=>"ico_mailtemplate.gif",Caption=>$_['MENU_MAILTEMPLATES'],Call=>"mail.IMailTemplates.Browse.bm"),
        )
      ),

    );


  }
function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['mail'];
  return array
    (
    ArchiveCompleteQueues=>array(Caption=>$_['SETTING_ARCHIVE_COMPLETE_QUEUES'],Type=>'boolean',DefaultValue=>'1'),
    ArchiveQueueMessages=>array(Caption=>$_['SETTING_ARCHIVE_SENT_MESSAGES'],Type=>'boolean'),
    LifetimeSentMessages=>array(Caption=>$_['SETTING_ARCHIVE_SENT_MESSAGES_LIFE'],Type=>'int',DefaultValue=>'3'),
    LifetimeUnsentMessages=>array(Caption=>$_['SETTING_ARCHIVE_UNSENT_MESSAGES_LIFE'],Type=>'int',DefaultValue=>'10'),
    EmailFooter=>array(Type=>'langtext'),
    );
  }


function BackendInfoBlocks()
  {
  $_=&$GLOBALS['_STRINGS']['mail'];
  return array(
    array(Column=>1,Caption=>$_['INFOBLOCK_MAILQUEUES'],Call=>"mail.IInfoBlocks.QueuesStatus"),
    );
  }
}

?>
