<?
class pub
{
function pub()
  {
  $_=&$GLOBALS['_STRINGS']['pub'];
  $this->Title=$_['TITLE'];
  $this->Roles=array(
    Moderator=>$_['ROLE_MODERATOR'],
    CanAddTerms=>$_['ROLE_CANADDTERMS'],
    );
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['pub'];
  return array(
    TMyArticles=>array(Caption=>$_['TMYARTICLES']),
    TGlossary=>array(Caption=>$_['TGLOSSARY']),
    );
  }
/*
function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['pub'];
  return array (
    array
      (
      Category=>"pub", Caption=>'Публикации пользователей',
      Items=>array(
        array(Caption=>'Термины',Call=>"pub.IGlossary.Browse.bm"),
        )
      ),
    );
  }
*/
function Settings()
  {
  return array
    (
    SubjectsContext=>array(Caption=>'Subjects context',Type=>'syscontext',DefaultValue=>"subjects"),
    );
  }
}
?>
