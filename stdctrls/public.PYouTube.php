<?
class stdctrls_PYouTube{
	function ShowV($args) {
		global $cfg;
		$YouTubeV=$args['v'];
		print "<body bgcolor='#000000'>";
		
		
		?>
		<object width="480" height="385">
		<param name="movie" value="http://www.youtube.com/v/<? print $YouTubeV; ?>&hl=ru_RU&fs=1&"></param>
		<param name="allowFullScreen" value="true"></param>
		<param name="allowscriptaccess" value="always"></param>
		<embed src="http://www.youtube.com/v/<? print $YouTubeV; ?>&hl=ru_RU&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed>
		</object>
		<?
/*		$_ENV->PutSWF(array(
		  src=>"$cfg[PublicURL]/media/player.swf",
		  width=>425,height=>344,
		  flashvars=>"file=http://www.youtube.com/watch%3Fv%3D$YouTubeV",
		  allowfullscreen=>"true"
		  ));
		  */
	}
	
}
?>