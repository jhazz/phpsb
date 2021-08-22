class phpsbFlash.ScrollBar extends phpsbFlash.Control
{
	#include "ComponentVersion.as"
	static var symbolName:String = "phpsbFlash.ScrollBar";
	static var symbolOwner:Object = phpsbFlash.ScrollBar;
	var className:String = "phpsbFlash.ScrollBar";
	
	var btn:MovieClip;
	var scrollSpace:MovieClip;
	var dragMode:Boolean;
	

	private var _maxscroll:Number;
	private var _pos:Number;
	var owner:Object;

	function ScrollBar() {
		_pos=1; _maxscroll=1;
		init();
	}

	function init() {
		btn.owner=this;
		btn.onReleaseOutside=btn.onRelease=onScrollerRelease;
		btn.onPress=this.onScrollerPress;
		btn.onMouseMove=onScrollerMouseMove;
	}
	function size() {draw();}
	
	function draw() {
		if (_maxscroll==1) {_visible=false; return;}
		_visible=true;
		scrollSpace.vertline._height=height;
		scrollSpace.bot._y=height;
		
		var maxHeight:Number=scrollSpace._height-btn._height;
		btn._y=((_pos-1) / (_maxscroll-1)) * maxHeight;
		if (btn._y<0) {btn._y=0;}
	}
	
	function set pos(v:Number) {
		if (v>_maxscroll) {v=_maxscroll;}
		_pos=(v>0)?v:0; invalidate();
	}

	function get pos():Number {
		return _pos;
	}

	
	function set maxscroll(v:Number) {
		_maxscroll=v; 
		if (_pos>v) {_pos=v;}
		invalidate();
	}
	function get maxscroll():Number {
		return _maxscroll;
	}
	
	// Button related methods
	function onScrollerPress() {
		if (_root.dispatcher.editMode) {return;}
		var scroller:jhazz.TScroller=Object(this).owner;
		MovieClip(this).startDrag(false,0,0,0,scroller.scrollSpace._height-this._height);
		MovieClip(this).gotoAndStop(2);
		owner.dragMode=true;
	}
	function onScrollerMouseMove() {
		var scroller:jhazz.TScroller=Object(this).owner;
		if (scroller.dragMode) {
			var maxHeight=scroller.scrollSpace._height-MovieClip(this)._height;
			var p:Number=Math.ceil((MovieClip(this)._y/maxHeight)* (scroller._maxscroll));
			if (p<0) p=0;
			if (p>_maxscroll) p=_maxscroll;
			scroller.buttonMovedTo(p);
		}
	}
	function onScrollerRelease() {
		var scroller:jhazz.TScroller=Object(this).owner;
		scroller.dragMode=false;
		scroller.draw();
		MovieClip(this).stopDrag();
		MovieClip(this).gotoAndStop(1);
	}

	function buttonMovedTo(p:Number) {
		owner.onScrollButton(p);
		_pos=p;
	}

}