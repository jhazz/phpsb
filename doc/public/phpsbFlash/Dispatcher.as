class phpsbFlash.Dispatcher extends MovieClip {
	static var symbolOwner:Object = phpsbFlash.Dispatcher;
	var className:String = "Dispatcher";
	static var SIDE_NAMES:Object={float:0,top:1,right:2,bottom:3,left:4};
	static var SEPARATOR_WIDTH=4;
	static var SIDESIZER_WIDTH=6;
	static var PANEL_TITLE_HEIGHT=20;
	private var doLaters:Array;
	private var doLatersNext:Array;
	private var eventListeners:Array;
	public var windows:Array;
	public var panels:Array;
	public var sides:Object;
	public var corners:Array;
	var windowWidth:Number;
	var windowHeight:Number;
	var dragMode:Number; // 1-sideSizing
	var dragStartX:Number;
	var dragStartY:Number;
	var dragObject:Object;
	var mouseListener:Object;
	var mcPanels:MovieClip;
	var mcSeparators:MovieClip; // for separatorsContainer 
	var separatorsContainer:MovieClip;
	var MainContextMenu:ContextMenu;
//	var dockContainer:MovieClip;
	var mcCursor:MovieClip;
	#include "ComponentVersion.as"

	function onContextMenu()
	{
		trace ("selected");
	}
	function onMouseWheel(delta)
	{
		for (var p in panels)
		{
			var panel=panels[p];
			if (!panel.collapsed)
			{
				var x=_xmouse-panel._x;
				var y=_ymouse-panel._y;
				if ((x>=0)&&(x<panel.width)&&(y>=0)&&(y<panel.height))
				{
					panel.onMouseWheel(delta,x,y);
				}
			}
		}
	}
	
	function Dispatcher() {
		panels=new Array();
		mcPanels.lastDepth=0;
		mouseListener={dispatcher:this,onMouseWheel:function(delta){this.dispatcher.onMouseWheel(delta);}};
		Mouse.addListener(mouseListener);
		Stage.addListener(this);
//		target=this;
		
//		dockContainer=target.createEmptyMovieClip("dockContainer",1);
//		mcCursor=target.createEmptyMovieClip("mcCursor",3);
		
		MainContextMenu = new ContextMenu ();
		MainContextMenu.hideBuiltInItems();
		MainContextMenu.customItems.push(new ContextMenuItem("Open diagram", onContextMenu));
		MainContextMenu.customItems.push(new ContextMenuItem("Save diagram", onContextMenu));
		_root.menu=MainContextMenu;
		_x=_y=0;
		eventListeners = new Array();
		_xscale = _yscale=100;
		corners=[0,0,0,1]; // [TopLeft TopRight BottomRight BottomLeft] Horizontal=1, Vertical=0
		sides=[
			{stack:new Array()},            // 0-float
			{height:130,stack:new Array(),sideNo:1}, // 1-top
			{width:200,stack:new Array(),sideNo:2},  // 2-right
			{height:130,stack:new Array(),sideNo:3}, // 3-bottom
			{width:200,stack:new Array(),sideNo:4}   // 4-left
			];

	}
	function setCursor(idName)
	{
		if (idName==undefined) {
			mcCursor._visible=false;
			mcCursor.stopDrag();
			Mouse.show();
		} else {
			Mouse.hide();
			mcCursor.attachMovie(idName,"x",1);
			mcCursor._x=_xmouse;
			mcCursor._y=_ymouse;
			mcCursor.startDrag(true);
			mcCursor._visible=true;
		}
	}
	
	function onResize() {
		rearrangeDockSides();
		dispatchEvent({type:"stageResized",width:Stage.width,height:Stage.height});

	}
	function rearrangeDockSides() {
		windowWidth=Stage.width;
		windowHeight=Stage.height;
		
		if (panels==undefined) {
			trace ("You should define panels");
		}
		if (separatorsContainer!=undefined)
		{
			separatorsContainer.removeMovieClip();
		}
		
		separatorsContainer=mcSeparators.createEmptyMovieClip("separatorsContainer",1);
		separatorsContainer.sepDepth=0;

		var p,panel,i,l,side,sideNo,panelCount,minw,minh,sidesToRearrange:Object={};
		for (p in panels)
		{
			panel=panels[p];
			if (panel.stacked!=true)
			{
				sideNo=SIDE_NAMES[panel.sideName];
				if (panel.dockedIn!=undefined)
				{
					panel.dockedIn.stack.splice(panel.stackedPos,1);
				}
				panel.dockedIn=sides[sideNo];
				panel.dockedIn.visible=true;
				panel.dockedIn.needRescale=true;
				panel.stackedPos=panel.dockedIn.stack.push(panel)-1;
			}
		}
		// Check for empty sides and turn them to invisible state
		for (sideNo=1;sideNo<sides.length;sideNo++) {
			side=sides[sideNo];
			if (side.stack.length==0) side.visible=false;
		}
//		separatorsContainer.clear();
		for (sideNo=1;sideNo<sides.length;sideNo++)
		{
			side=sides[sideNo];
			side.minWidth=50;
			side.minHeight=10;
			panelCount=side.stack.length;
			for (p=0;p<panelCount;p++)
			{
				panel=side.stack[p];
				panel._visible=side.visible;
			}
			if (side.visible==false) continue;
			side.dockY=side.dockX=0;
			side.dockSpaceWidth=windowWidth;
			side.dockSpaceHeight=windowHeight;

			switch(sideNo)
			{
				case 1: // top
					if ((corners[0]==0)&&(sides[4].visible)) {
						side.dockX+=sides[4].width+SIDESIZER_WIDTH; side.dockSpaceWidth-=sides[4].width+SIDESIZER_WIDTH;}
					if ((corners[1]==0)&&(sides[2].visible)) {
						side.dockSpaceWidth-=sides[2].width+SIDESIZER_WIDTH;}
						side.sizer=createSideSizer(side.dockX,side.height,side.dockSpaceWidth,side);
					break;
				case 2: // right
					if ((corners[1]==1)&&(sides[1].visible)) {
						side.dockY+=sides[1].height+SIDESIZER_WIDTH; side.dockSpaceHeight-=sides[1].height+SIDESIZER_WIDTH;}
					if ((corners[2]==1)&&(sides[3].visible)) {
						side.dockSpaceHeight-=sides[3].height+SIDESIZER_WIDTH;}
					side.dockX=windowWidth-side.width;
					side.sizer=createSideSizer(
						windowWidth-side.width-SIDESIZER_WIDTH,side.dockY,
						side.dockSpaceHeight,side);
					break;
				case 3: // bottom
					if ((corners[3]==0)&&(sides[4].visible)) {
						side.dockX+=sides[4].width+SIDESIZER_WIDTH; side.dockSpaceWidth-=sides[4].width+SIDESIZER_WIDTH;}
					if ((corners[2]==0)&&(sides[2].visible)) {
						side.dockSpaceWidth-=sides[2].width+SIDESIZER_WIDTH;}
					side.dockY=windowHeight-side.height;
					side.sizer=createSideSizer(side.dockX,side.dockY-SIDESIZER_WIDTH,
								side.dockSpaceWidth,side);
					break;
				case 4: // left
					if ((corners[0]==1)&&(sides[1].visible)) {
						side.dockY+=sides[1].height+SIDESIZER_WIDTH; side.dockSpaceHeight-=sides[1].height+SIDESIZER_WIDTH;}
					if ((corners[3]==1)&&(sides[3].visible)) {
						side.dockSpaceHeight-=sides[3].height+SIDESIZER_WIDTH;}
					side.sizer=createSideSizer(side.dockX+side.width,side.dockY,
								side.dockSpaceHeight,side);
					break;
			}
			// rescale panels
			// first is calculate sum
			var sumWidth,sumHeight;
			sumWidth=sumHeight=-SEPARATOR_WIDTH;
			var subWidth=0, subHeight=0;
			for (p=0;p<panelCount;p++)
			{
				panel=side.stack[p];
				if (!panel.collapsed)
				{
					subWidth+=PANEL_TITLE_HEIGHT;
					subHeight+=PANEL_TITLE_HEIGHT;
				}
				sumWidth +=panel.dockWidth+SEPARATOR_WIDTH;
				sumHeight+=panel.dockHeight+SEPARATOR_WIDTH; // dockHeight=height+PANEL_TITLE_HEIGHT
			}
			var dockPosX=side.dockX;
			var dockPosY=side.dockY;
			var dockScaleWidth=(side.dockSpaceWidth-subWidth)/sumWidth;
			var dockScaleHeight=(side.dockSpaceHeight-subHeight)/sumHeight;
			var isLast,nextPanel;
			for (p=0;p<panelCount;p++)
			{
				isLast=(p==(panelCount-1));
				panel=side.stack[p];
				nextPanel=(isLast)?undefined:side.stack[p+1];
				switch(sideNo)
				{
					case 1: case 3: // top or bottom
						panel.dockWidth=((panel.stacked!=true)&& isLast)? side.dockSpaceWidth-(dockPosX-side.dockX): Math.ceil(panel.dockWidth*dockScaleWidth);
						if (panel.dockWidth<panel.minWidth) panel.dockWidth=panel.minWidth;
						panel._x=dockPosX;
						panel._y=side.dockY+PANEL_TITLE_HEIGHT;
						panel.setSize(panel.dockWidth,side.height-PANEL_TITLE_HEIGHT);
						dockPosX+=panel.dockWidth;
						if (!isLast)
						{
							panel.widthSeparator=createSeparator(
								dockPosX,side.dockY,0,side.height,
								false,side,panel,nextPanel);
							dockPosX+=SEPARATOR_WIDTH;
						}
						if (side.minHeight<panel.minHeight) side.minHeight=panel.minHeight;
						panel.stacked=true;
						break;
					case 2: case 4: // right or left
						panel.dockHeight=((panel.stacked!=true)&& isLast)? side.dockSpaceHeight-(dockPosY-side.dockY) : Math.ceil(panel.dockHeight*dockScaleHeight);
						if ((panel.dockHeight-PANEL_TITLE_HEIGHT)<panel.minHeight) panel.dockHeight=panel.minHeight+PANEL_TITLE_HEIGHT;
						panel._x=side.dockX;
						panel._y=dockPosY+PANEL_TITLE_HEIGHT;
						if (panel.collapsed)
						{
							dockPosY+=PANEL_TITLE_HEIGHT;
						}
						else
						{
							panel.setSize(side.width,panel.dockHeight-PANEL_TITLE_HEIGHT);
							dockPosY+=panel.dockHeight;
							if (!isLast)
							{
								panel.heightSeparator=createSeparator(
									side.dockX,dockPosY,side.width,0,
									true,side,panel,nextPanel);
								dockPosY+=SEPARATOR_WIDTH;
							}
						}
						if (side.minWidth<panel.minWidth) side.minWidth=panel.minWidth;
						panel.stacked=true;
						break;
				} // switch(sideNo)
			} // for (panelCount)
		} // for (side)
	}

	function createSideSizer(x,y,length,side)
	{
		var sep=separatorsContainer.attachMovie("SideSizer","siz"+separatorsContainer.sepDepth
			,separatorsContainer.sepDepth++
			,{_x:x,_y:y, length:length, side:side});
		return sep;
	}
	function createSeparator(x,y,w,h,horizontal,side,prevPanel,nextPanel)
	{
		var sep=separatorsContainer.attachMovie("Separator","sep"+separatorsContainer.sepDepth,separatorsContainer.sepDepth++
			,{_x:x,_y:y, _width:w, _height:h,
			horizontal:horizontal,data:{side:side,prevPanel:prevPanel,nextPanel:nextPanel}});
		return sep;
	}
	function recursiveShrinkWidthLeft(panel,delta)
	{
		var pos=panel.stackedPos;
		var w=panel.width-delta;
		
		if (w<panel.minWidth)
		{
			if (pos>0) 
				{
					delta=recursiveShrinkWidthLeft(panel.dockedIn.stack[pos-1],panel.minWidth-w);
					panel._x-=delta;
					delta+=panel.width-panel.minWidth
				} 
				else 
				{
					delta=panel.width-panel.minWidth;
				}
			w=panel.minWidth;
		} 
		panel.setSize(w,panel.height);
		panel.widthSeparator._x=panel._x+w;
		return delta;
	}

	function recursiveShrinkWidthRight(panel,delta)
	{
		var pos=panel.stackedPos;
		var isLast=(pos>=(panel.dockedIn.stack.length-1));
		var w=panel.width-delta;
		if (w<panel.minWidth)
		{
			if (!isLast) 
				{
					delta=panel.width-panel.minWidth+recursiveShrinkWidthRight(panel.dockedIn.stack[pos+1],
								panel.minWidth-w);
				} else {delta=panel.width-panel.minWidth;}
			w=panel.minWidth;
		} 
		panel.setSize(w,panel.height);
		panel._x+=delta;
		panel.widthSeparator._x=panel._x+w;
		return delta;
	}

	
	function recursiveShrinkHeightUp(panel,delta)
	{
		var pos=panel.stackedPos;
		var h=panel.height-delta;
		if (h<panel.minHeight)
		{
			if (pos>0) 
				{
					delta=recursiveShrinkHeightUp(panel.dockedIn.stack[pos-1],panel.minHeight-h);
					panel._y-=delta;
					delta+=panel.height-panel.minHeight
				} 
				else 
				{
					delta=panel.height-panel.minHeight;
				}
			h=panel.minHeight;
		} 
		panel.setSize(panel.width,h);
		panel.heightSeparator._y=panel._y+h;
		return delta;
	}

	function recursiveShrinkHeightDown(panel,delta)
	{
		var pos=panel.stackedPos;
		var isLast=(pos>=(panel.dockedIn.stack.length-1));
		var h=panel.height-delta;
		if (h<panel.minHeight)
		{
			if (!isLast) 
				{
					delta=panel.height-panel.minHeight+recursiveShrinkHeightDown(panel.dockedIn.stack[pos+1],
								panel.minHeight-h);
				} else {delta=panel.height-panel.minHeight;}
			h=panel.minHeight;
		} 
		panel.setSize(panel.width,h);
		panel._y+=delta;
		panel.heightSeparator._y=panel._y+h;
		return delta;
	}
	
		 
	function onDockSeparatorMove(separator)
	{
//		var l:Number=separator.side.stack.length;
		if (!separator.horizontal)
		{// horizontal movement
			var panel=separator.data.prevPanel;
			var dx=(separatorsContainer._xmouse-panel._x)-panel.width;
			if (dx<0) 
			{
				dx=recursiveShrinkWidthLeft(panel,-dx);
				if (dx!=0)
				{
					panel=separator.data.nextPanel;
					panel._x-=dx;
					panel.setSize(panel.width+dx,panel.height);
				}
			}
			else
			{
				dx=recursiveShrinkWidthRight(separator.data.nextPanel,dx);
				if (dx!=0)
				{
					panel=separator.data.prevPanel;
					panel.setSize(panel.width+dx,panel.height);
					panel.widthSeparator._x=panel._x+panel.width;
				}
				
			}
		
		}
		else
		{
			var panel=separator.data.prevPanel;
			var dy=(separatorsContainer._ymouse-panel._y)-panel.height;
			if (dy<0) 
			{
				dy=recursiveShrinkHeightUp(panel,-dy);
				if (dy!=0)
				{
					panel=separator.data.nextPanel;
					panel._y-=dy;
					panel.setSize(panel.width,panel.height+dy);
				}
			}
			else
			{
				dy=recursiveShrinkHeightDown(separator.data.nextPanel,dy);
				if (dy!=0)
				{
					panel=separator.data.prevPanel;
					panel.setSize(panel.width,panel.height+dy);
					panel.heightSeparator._y=panel._y+panel.height;
				}
				
			}
		
			
		}
//		trace ("Dock separator down me");
	}

	function removeEventListener(event:String, obj, func):Void {
		if (eventListeners[event] != undefined) {
			var l:Number = eventListeners.length;
			var i:Number;
			for (i=0; i<l; i++) {
				var handler = eventListeners[i];
				if ((handler.o == obj) && (handler.f == func)) {
					eventListeners.splice(i, 1);
					return;
				}
			}
		}
	}
	/*
	*/
	function addEventListener(eventType:String, obj, func) {
		if (eventListeners[eventType] == undefined) {
			eventListeners[eventType] = new Array();
		}
		eventListeners[eventType].push({o:obj, f:func});
	}
	/*
	*/
	function dispatchEvent(eventObj:Object) {
		var q = eventListeners[eventObj.type];
		if (q != undefined) {
			for (var i in q) {
				var handler = q[i];
				handler.o[handler.f](eventObj);
			}
		}
	}
	/*
	*/
	/*
	function doLater(obj:Object, funcName:String, args, dropIfExist:Boolean, earlier:Boolean):Boolean {
		var e:Array;
		var i:Number;
		if (!doLatersNext) {
			doLatersNext = new Array();
		}
		if ((doLatersNext.length>0) && (dropIfExist==true)) {
			for (i in doLatersNext) {
				e = doLatersNext[i];
				if ((e[0] == obj) && (e[1] == funcName)) {
					return false;
				}
			}
		}
		if (earlier) {
			doLatersNext.unshift([obj,funcName, args]);
		} else {
			doLatersNext.push([obj,funcName, args]);
		}
		onEnterFrame = doLaterDispatcher;
		return true;
	}
	function doLaterDispatcher() {
		var fa:Object;
		delete onEnterFrame;
		doLaters = doLatersNext;
		doLatersNext = new Array();
		while (fa=doLaters.shift()) {
			//			trace ("  [--"+className+":"+fa[0]+"--]");
			fa[0][fa[1]](fa[2]);
		}
	}
	*/
	function onMouseMove() {
		switch (dragMode)
		{
			case 1: 
				switch (dragObject.sideNo)
				{
					case 1: dragObject.height=_ymouse-dragStartY;	break;
					case 2: dragObject.width=windowWidth-_xmouse-dragStartX; break;
					case 3: dragObject.height=windowHeight-_ymouse-dragStartY; break;
					case 4: dragObject.width=_xmouse-dragStartX; break;
				}

			if (dragObject.width<dragObject.minWidth) dragObject.width=dragObject.minWidth;
			if (dragObject.height<dragObject.minHeight) dragObject.height=dragObject.minHeight;
			rearrangeDockSides();
			break; // end setDockSideSize
		}
	}
	function onMouseUp() {
		dragMode=0;
		setCursor();
	}

	function onDockSideSizerStartDrag(side)
	{
		switch (side.sideNo)
		{
			case 1: dragStartY=_ymouse-side.height; break;
			case 2: dragStartX=side.width-(windowWidth-_xmouse); break;
			case 3: dragStartY=side.height-(windowHeight-_ymouse); break;
			case 4: dragStartX=_xmouse-side.width; break;
		}
		dragMode=1;
		dragObject=side;
	}
	
	function loadWorkspace()
	{
	createDockablePanel({caption:"panel1",sideName:"top",minWidth:200,minHeight:100,collapsed:false});
	createDockablePanel({caption:"panel2",sideName:"right",minWidth:200,minHeight:100,collapsed:false});
	createDockablePanel({caption:"panel3",sideName:"right",minWidth:200,minHeight:100,collapsed:false});
	createDockablePanel({caption:"panel4",sideName:"right",minWidth:200,minHeight:100,collapsed:false});
	}
	
	function createDockablePanel(initObj:Object)
	{
		var s="dockpanel"+mcPanels.lastDepth;
		var p=mcPanels.attachMovie("DockablePanel",s,mcPanels.lastDepth++,initObj);
//		p.init();
//		var p=mcPanels.createEmptyMovieClip(s,mcPanels.lastDepth++);
//		Object.registerClass(p,phpsbFlash.DockablePanel);
//		trace (p);
//		p.draw();
		panels.push(p);
	}
	
}
