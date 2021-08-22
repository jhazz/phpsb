<?
class mship
{
var $Roles=array(
  EditOwnProfile   =>"Create and edit its own profile",
  MultipleProfiles =>"Add more than one member profile",
  BindObjects      =>"Can bind any objects to profile",
  Moderator        =>"Edit and approve member's profiles",
  );

function mship()
  {
  $_=&$GLOBALS[_STRINGS][mship];
  $this->Title=$_['MSHIP_TITLE'];
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  return array(
    TMyOffice=>array(Caption=>$_['TMYOFFICE_CAPTION'],Description=>$_['TMYOFFICE_DESCRIPTION'],Icon=>''),
    TMembersList=>array(Caption=>$_['TMEMBERSLIST_CAPTION'],Description=>$_['TMEMBERSLIST_DESCRIPTION'],Icon=>''),
    TMember=>array(Caption=>$_['TMEMBER_CAPTION'],Description=>$_['TMEMBER_DESCRIPTION'],Icon=>''),
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  return array (
    array
      (
      PutToCategory=>"admin",
      Caption=>$_['MSHIP_TITLE'],
      CreateCategory=>"mship",
      Items=>array(
        array(Caption=>$_['MEMBERS'],Call=>"mship.IGroup.Browse.bm"),
        array(Caption=>$_['MEMBER_CATALOGSECTIONS'],Call=>"mship.IGroup.Explore.n"),
        )
      ),
    );
  }


function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  return array
    (
    CatalogContext=>array(Caption=>$_['MEMBERS_CATALOG_CONTEXT'],Type=>'syscontext',DefaultValue=>'mship'),
    MemberContext=>array(Caption=>$_['MEMBER_CONTEXT'],Type=>'syscontext',DefaultValue=>'member'),
    );
  }

function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  return array
    (
    "mship.Group" =>array (Caption=>"Member group",UseSettingsContext=>"CatalogContext"),
    "mship.Member"=>array (Caption=>"A member",UseSettingsContext=>"MemberContext"),
    );
  }

}
?>
