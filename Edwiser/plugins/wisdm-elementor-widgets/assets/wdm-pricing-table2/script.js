!function($){function pricing_toggle(){$(document).on("click",".wdm-p2-annual-btn",(function(){var parent=$(this).closest(".wdm-p2-wrap"),annual_elements=parent.find(".wdm-p2-annual"),lifetime_elements=parent.find(".wdm-p2-lifetime"),annual_btn=parent.find(".wdm-p2-annual-btn"),lifetime_btn=parent.find(".wdm-p2-lifetime-btn");$(lifetime_btn).removeClass("active"),$(annual_btn).addClass("active"),$(lifetime_elements).hide(),$(annual_elements).show()})),$(document).on("click",".wdm-p2-lifetime-btn",(function(){var parent=$(this).closest(".wdm-p2-wrap"),annual_elements=parent.find(".wdm-p2-annual"),lifetime_elements=parent.find(".wdm-p2-lifetime"),annual_btn=parent.find(".wdm-p2-annual-btn"),lifetime_btn=parent.find(".wdm-p2-lifetime-btn");$(annual_btn).removeClass("active"),$(lifetime_btn).addClass("active"),$(annual_elements).hide(),$(lifetime_elements).show()}))}function headerScroll(){headerScrollHandle();var duringScrollCallback=throttle(headerScrollHandle,200);function headerScrollHandle(){window.outerWidth>767&&($.fn.isInViewport=function(bottomAdjustment=0){if(void 0!==$(this).offset()){var elementTop=$(this).offset().top,elementBottom=elementTop+$(this).outerHeight(),viewportTop=$(window).scrollTop(),viewportBottom=viewportTop+$(window).height();return(elementBottom+=bottomAdjustment)>viewportTop&&elementTop<viewportBottom}},$(window).scroll((function(){if($(".wdm-p2-main").isInViewport(-100)){let table_header_visible=$(".wdm-p2-main .wdm-p2-product-title").isInViewport(-100),table_features_visible=$(".wdm-p2-main .wdm-p2-features").isInViewport();!table_header_visible&&table_features_visible?($(".wdm-p2-sticky-header").show(),$(".wdm-fixed-header").hide()):($(".wdm-p2-sticky-header").hide(),$(".wdm-scroll-header").show())}else $(".wdm-p2-sticky-header").hide(),$(".wdm-scroll-header").show()})))}document.addEventListener("scroll",duringScrollCallback,{capture:!0,passive:!0})}function throttle(callback,limit){var wait=!1;return function(){wait||(callback.apply(null,arguments),wait=!0,setTimeout((function(){wait=!1}),limit))}}function pricingShowMore(){$(document).on("click",".wdm-p2-showmore-features",(function(){var parent,hidden_group=$(this).closest(".wdm-p2-features-general").find(".wdm-p2-hidden-features");$(hidden_group).show(),$(this).hide()})),$(document).on("click",".wdm-p2-showless-features",(function(){var parent=$(this).closest(".wdm-p2-features-general"),parent_hidden_group=$(this).closest(".wdm-p2-hidden-features"),showmore_btn=parent.find(".wdm-p2-showmore-features");$(showmore_btn).show(),$(parent_hidden_group).hide()})),$(document).on("click",".wdm-p2-showmore-subfeatures",(function(){var parent=$(this).closest(".wdm-p2-features-groups"),hidden_class=$(this).data("hideclass"),hidden_group=parent.find("."+hidden_class);$(hidden_group).show(),$(this).hide()})),$(document).on("click",".wdm-p2-showless-subfeatures",(function(){var parent=$(this).closest(".wdm-p2-features-groups"),parent_hidden_group=$(this).closest(".wdm-p2-hidden-features"),showmore_class=$(this).data("showclass"),showmore_btn=parent.find("."+showmore_class);$(showmore_btn).show(),$(parent_hidden_group).hide()}))}function load(){window.popupContentIndex={index:0,indexes:{}},window.popupContents=[],pricing_toggle(),headerScroll(),pricingShowMore(),$(".wdm-free-download-modal-btn").click((function(){$("#wdm-free-download-modal").css("display","block")})),$("#wdm-free-download-modal .close").click((function(){$("#wdm-free-download-modal").css("display","none")})),$(document).on("click","#wdm-free-download",(function(){var downloadId=$(this).data("download-id");$.ajax({type:"POST",dataType:"json",url:free_download_ajax.ajaxurl,data:{action:"increase_count_of_free_download",downloadId:downloadId,security:free_download_ajax.security},success:function(response){$.each(response.data.downloadurl,(function(index,value){setTimeout((function(){$("#download-edd-file").prop("href",value),$("a#download-edd-file")[0].click()}),1e3);}))},error:function(errorThrown){console.log(errorThrown)}})}))}$(window).on("resize orientationchange",(function(){}));var pricing_table=function($scope,$){load()};function editor_mode_on(){let url;return window.location.href.includes("elementor-preview")}$(document).ready((function(){editor_mode_on()||load()})),$(window).on("elementor/frontend/init",(function(){editor_mode_on()&&elementorFrontend.hooks.addAction("frontend/element_ready/wisdm-pricing-table2-widget-id.default",pricing_table)}))}(jQuery);