"use strict";$(function(){var t=$("#butwhy"),e=$("#thisiswhy");t.on("click",function(a){a.preventDefault(),a.stopPropagation(),t.addClass("hidden"),e.removeClass("hidden")}),Chart.defaults.global.responsive=!0,Chart.defaults.global.maintainAspectRatio=!1,Chart.defaults.global.animation=!1;var a=$("#stats"),s=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:.2,a=t.red+","+t.green+","+t.blue,s="rgb("+a+")";return{lineTension:0,backgroundColor:0===e?"transparent":"rgba("+a+","+e+")",borderColor:s,borderWidth:2,pointBackgroundColor:s,pointRadius:3,pointHitRadius:6,pointBorderColor:"#fff",pointBorderWidth:2,pointHoverBackgroundColor:"#fff",pointHoverBorderColor:s}},o={position:"bottom",labels:{boxWidth:12}},n=[{type:"time",time:{unit:"day",unitStepSize:1,displayFormats:{day:"Do MMM"}},ticks:{autoSkip:!0,maxTicksLimit:15}}],i=[{type:"linear",ticks:{autoSkip:!0,maxTicksLimit:6}}],r=a.children(".stats-posts"),d=r.children("h3"),l=r.children(".legend"),p=r.find("canvas").get(0).getContext("2d"),m=void 0,u=["#46ACD3","#5240C3"];$.post("/about/stats?stat=posts",$.mkAjaxHandler(function(){if(!this.status)return r.remove();var t=this.data;if($.mk("p").append("Last updated: ",$.mk("time").attr("datetime",t.timestamp)).insertAfter(d),Time.Update(),0===t.datasets.length)return l.html("<strong>No data available</strong>");l.remove(),$.each(t.datasets,function(e,a){var o=$.RGBAColor.parse(u[a.clrkey]);$.extend(t.datasets[e],s(o))}),m=new Chart.Line(p,{data:t,options:{tooltips:{mode:"label",callbacks:{title:function(t){return moment(t[0].xLabel).format("Do MMMM, YYYY")}}},legend:o,scales:{xAxes:n,yAxes:i}}}),$w.on("resize",function(){m.resize()})}));var c=a.children(".stats-approvals"),h=c.children("h3"),f=c.children(".legend"),v=c.find("canvas").get(0).getContext("2d"),b=void 0,g=$.RGBAColor.parse("#4DC742");$.post("/about/stats?stat=approvals",$.mkAjaxHandler(function(){if(!this.status)return c.remove();var t=this.data,e=g;if($.mk("p").append("Last updated: ",$.mk("time").attr("datetime",t.timestamp)).insertAfter(h),Time.Update(),0===t.datasets.length)return f.html("<strong>No data available</strong>");f.remove(),$.extend(t.datasets[0],s(e)),b=new Chart.Line(v,{data:t,options:{tooltips:{mode:"label",callbacks:{title:function(t){return moment(t[0].xLabel).format("Do MMMM, YYYY")},label:function(t){var e=parseInt(t.yLabel,10);return(0===e?"No":e)+" post"+(1!==e?"s":"")+" approved"}}},legend:o,scales:{xAxes:n,yAxes:i}}}),$w.on("resize",function(){b.resize()})}));var k=a.children(".stats-alltimeposts"),x=k.children("h3"),C=k.children(".legend"),A=k.find("canvas").get(0).getContext("2d"),M=void 0,y=["#E64C8D","#46ACD3","#5240C3"];$.post("/about/stats?stat=alltimeposts",$.mkAjaxHandler(function(){if(!this.status)return k.remove();var t=this.data;if($.mk("p").append("Last updated: ",$.mk("time").attr("datetime",t.timestamp)).insertAfter(x),Time.Update(),0===t.datasets.length)return C.html("<strong>No data available</strong>");C.remove(),$.each(t.datasets,function(e,a){var o=$.RGBAColor.parse(y[a.clrkey]);$.extend(t.datasets[e],s(o,0===e?0:.1))}),M=new Chart.Line(A,{data:t,options:{tooltips:{mode:"label",callbacks:{title:function(t){return"Totals as of "+moment(t[0].xLabel).format("MMM 'YY")},label:function(t){var e=parseInt(t.yLabel,10);return 0===t.datasetIndex?(0===e?"No":e)+" approved post"+(1!==e?"s":""):(0===e?"No":e)+" "+(1===t.datasetIndex?"request":"reservation")+(1!==e?"s":"")}}},legend:o,scales:{xAxes:[{type:"time",time:{unit:"month",unitStepSize:1,displayFormats:{month:"MMM 'YY"}},ticks:{autoSkip:!0}}],yAxes:i}}}),$w.on("resize",function(){M.resize()})}))});
//# sourceMappingURL=/js/min/pages/about/index.js.map
