"use strict";!function(e,o){"function"==typeof define&&define.amd?define(["exports"],o):"undefined"!=typeof exports?o(exports):o(e.dragscroll={})}(window,function(e){var o,n,t=window,l=document,c="addEventListener",s="removeEventListener",m=[],r=function(e,r){for(e=0;e<m.length;)(r=(r=m[e++]).container||r)[s]("mousedown",r.md,0),t[s]("mouseup",r.mu,0),t[s]("mousemove",r.mm,0);for(m=[].slice.call(l.getElementsByClassName("dragscroll")),e=0;e<m.length;)!function(e,s,m,r,i,u){(u=e.container||e)[c]("mousedown",u.md=function(o){console.log("mousedown"),e.hasAttribute("nochilddrag")&&l.elementFromPoint(o.pageX,o.pageY)!=u||(r=1,s=o.clientX,m=o.clientY,o.preventDefault())},0),t[c]("mouseup",u.mu=function(){r=0},0),t[c]("mousemove",u.mm=$.throttle(100,function(t){console.log("mousemove",r),r&&((i=e.scroller||e).scrollLeft-=o=-s+(s=t.clientX),i.scrollTop-=n=-m+(m=t.clientY),e==l.body&&((i=l.documentElement).scrollLeft-=o,i.scrollTop-=n))}),0)}(m[e++])};"complete"==l.readyState?r():t[c]("load",r,0),e.reset=r});
//# sourceMappingURL=/js/min/dragscroll.js.map
