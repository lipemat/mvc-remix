/**
 * Sidr - The Mobile Menu Creator
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 * https://github.com/lipemat/sidr  - forked
 *
 * @filter mvc-sidr
 *
 */

jQuery(function($) {

	$(Sidr.menu_button).sidr({
		name : 'the-main-menu',
		source : Sidr.menu
	});
	
});

//Below is compressed version https://github.com/lipemat/sidr/blob/master/dist/jquery.sidr.js
(function(e){var t=false,n=false;var r={html5:false,init:function(t){this.menu=t;this.mainUl=this.menu.find(".sidr-class-menu");this.pos=0;this.speed=t.data("speed");this.side=t.data("side");this.width=t.outerWidth(true);if(this.html5||typeof Sidr.html5!="undefined"&&Sidr.html5){this.menu.find(".sidr-class-menu-item-has-children > a").append('<span class="sf-sub-indicator">&rarr;</span>').find(".sf-sub-indicator").click(function(){r.moveDown(e(this).parent());return false})}else{this.menu.find(".sidr-class-sf-sub-indicator").html("&rarr;").click(function(){r.moveDown(e(this).parent());return false})}this.menu.find("ul ul").prepend('<li class="sidr-class-menu-item back"><a title="Go Back on Level" href="javascript:void(0)"><span>&larr; Back</a></li>');this.menu.find("div > ul").prepend('<li class="sidr-class-menu-item close"><a title="Close Menu" href="javascript:void(0)"><span>Close X</a></li>');this.menu.find(".back").click(function(){r.moveUp()});this.menu.find(".close").click(function(){e.sidr("close",t.attr("id"))})},moveUp:function(e){r.slide("up")},moveDown:function(e){e.parent().parent().find("ul").hide();e.parent().find("ul").first().show();r.slide("down")},slide:function(e){this.pos=this.mainUl.position();switch(e){case"up":this.mainUl.animate({left:this.pos.left+this.width},this.speed);break;case"down":this.mainUl.animate({left:this.pos.left-this.width},this.speed);break}}};var i={isUrl:function(e){var t=new RegExp("^(https?:\\/\\/)?"+"((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|"+"((\\d{1,3}\\.){3}\\d{1,3}))"+"(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*"+"(\\?[;&a-z\\d%_.~+=-]*)?"+"(\\#[-a-z\\d_]*)?$","i");if(!t.test(e)){return false}else{return true}},loadContent:function(e,t){e.html(t);r.init(e)},addPrefix:function(e){var t=e.attr("id"),n=e.attr("class");if(typeof t==="string"&&""!==t){e.attr("id",t.replace(/([A-Za-z0-9_.\-]+)/g,"sidr-id-$1"))}if(typeof n==="string"&&""!==n&&"sidr-inner"!==n){e.attr("class",n.replace(/([A-Za-z0-9_.\-]+)/g,"sidr-class-$1"))}e.removeAttr("style")},execute:function(r,i,o){if(typeof i==="function"){o=i;i="sidr"}else if(!i){i="sidr"}var u=e("#"+i),a=e(u.data("body")),f=e("html"),l=u.outerWidth(true),c=u.data("speed"),h=u.data("side"),p,d,v;if("open"===r||"toogle"===r&&!u.is(":visible")){if(u.is(":visible")||t){return}if(n!==false){s.close(n,function(){s.open(i)});return}t=true;if(h==="left"){p={left:l+"px"};d={left:"0px"}}else{p={right:l+"px"};d={right:"0px"}}v=f.scrollTop();f.css("overflow-x","hidden").scrollTop(v);a.css({width:a.width(),position:"absolute"}).animate(p,c);u.css("display","block").animate(d,c,function(){t=false;n=i;if(typeof o==="function"){o(i)}})}else{if(!u.is(":visible")||t){return}t=true;if(h==="left"){p={left:0};d={left:"-"+l+"px"}}else{p={right:0};d={right:"-"+l+"px"}}v=f.scrollTop();f.removeAttr("style").scrollTop(v);a.animate(p,c);u.animate(d,c,function(){u.removeAttr("style");a.removeAttr("style");e("html").removeAttr("style");t=false;n=false;if(typeof o==="function"){o(i)}})}}};var s={open:function(e,t){i.execute("open",e,t)},close:function(e,t){i.execute("close",e,t)},toogle:function(e,t){i.execute("toogle",e,t)}};e.sidr=function(t){if(s[t]){return s[t].apply(this,Array.prototype.slice.call(arguments,1))}else if(typeof t==="function"||typeof t==="string"||!t){return s.toogle.apply(this,arguments)}else{e.error("Method "+t+" does not exist on jQuery.sidr")}};e.fn.sidr=function(t){var n=e.extend({name:"sidr",speed:200,side:"left",source:null,renaming:true,body:"body"},t);var r=n.name,o=e("#"+r);if(o.length===0){o=e("<div />").attr("id",r).appendTo(e("body"))}o.addClass("sidr").addClass(n.side).data({speed:n.speed,side:n.side,body:n.body});if(typeof n.source==="function"){var u=n.source(r);i.loadContent(o,u)}else if(typeof n.source==="string"&&i.isUrl(n.source)){e.get(n.source,function(e){i.loadContent(o,e)})}else if(typeof n.source==="string"){var a="",f=n.source.split(",");e.each(f,function(t,n){a+='<div class="sidr-inner">'+e(n).html()+"</div>"});if(n.renaming){var l=e("<div />").html(a);l.find("*").each(function(t,n){var r=e(n);i.addPrefix(r)});a=l.html()}i.loadContent(o,a)}else if(n.source!==null){e.error("Invalid Sidr Source")}return this.each(function(){var t=e(this),n=t.data("sidr");if(!n){t.data("sidr",r);t.click(function(e){e.preventDefault();s.toogle(r)})}})}})(jQuery)


