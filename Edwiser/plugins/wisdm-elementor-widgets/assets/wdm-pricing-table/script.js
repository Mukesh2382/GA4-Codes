!function($){function headerScroll(){headerScrollHandle();var duringScrollCallback=throttle(headerScrollHandle,100);function headerScrollHandle(){window.outerWidth>767&&($.fn.isInViewport=function(){if(void 0!==$(this).offset()){var elementTop=$(this).offset().top,elementBottom=elementTop+$(this).outerHeight(),viewportTop=$(window).scrollTop(),viewportBottom=viewportTop+$(window).height();return elementBottom>viewportTop&&elementTop<viewportBottom}},$(window).scroll((function(){$("#ups-main-id").isInViewport()&&!$("#ups-plan-w-toggle").isInViewport()?($(".header-pricing-w").css("display","flex"),$(".site-header").hide()):($(".header-pricing-w").hide(),$(".site-header").show())})))}document.addEventListener("scroll",duringScrollCallback,{capture:!0,passive:!0})}function throttle(callback,limit){var wait=!1;return function(){wait||(callback.apply(null,arguments),wait=!0,setTimeout((function(){wait=!1}),limit))}}function handleModal(detachClass,closeClass,closeCallback,closeIconClass){$("body").append($(detachClass).detach()),$(closeClass).click((function(event){event.target===this&&closeCallback()})),$(closeIconClass).click((function(){closeCallback()}))}function handleFeaturesModal(){handleModal(".upsmdl",".upsmdl",closeUpsmdl,".upsmdl-close-icon-w")}function closeUpsmdl(){closeModal(".upsmdl",".upsmdl-c-w")}function closeModal(modal,contentWrap){$(modal).removeClass("show"),$(modal).one(window.transitionEvent,(function(event){$(contentWrap).html("")}))}function priceSwitcher(){$(".upspd-switch-label").toggleClass("active"),$(".upspd-h5").toggleClass("active"),$(".wdm-pricing-button").toggleClass("wdm-hide"),$(".tooltiptext").toggleClass("wdm-hide"),$(".ups-toggle-txt").each((function(index,element){switchPriceText(element)})),$(".header-price-txt").each((function(index,element){switchPriceText(element)}))}function switchPriceText(element){var text=$(element).text().trim(),toggleText=$(element).attr("data-toggle-txt");$(element).text(toggleText),toggleText.length>0?($(element).parent(".upp-sale-price").siblings(".upp-reg-price").addClass("strike"),$(element).parent(".upp-sale-price").show(),$(element).parent(".upp-price-diff-inr").show()):($(element).parent(".upp-sale-price").siblings(".upp-reg-price").removeClass("strike"),$(element).parent(".upp-sale-price").hide(),$(element).parent(".upp-price-diff-inr").hide()),$(element).attr("data-toggle-txt",text)}function pricingShowMore(){$(".ups-viewmore").click((function(){$(".ups-viewmore-w").toggleClass("hide"),$(".ups-hide-w").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-complimentary-0").click((function(){$(".ups-hide-w-complimentary-0").toggleClass("hide"),$(".ups-viewmore-w-complimentary-0").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-0").click((function(){$(".ups-hide-w-subfeatures-0").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-0").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-1").click((function(){$(".ups-hide-w-subfeatures-1").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-1").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-2").click((function(){$(".ups-hide-w-subfeatures-2").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-2").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-3").click((function(){$(".ups-hide-w-subfeatures-3").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-3").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-4").click((function(){$(".ups-hide-w-subfeatures-4").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-4").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-5").click((function(){$(".ups-hide-w-subfeatures-5").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-5").toggleClass("hide"),pricingSetColWidth()})),$(".ups-viewmore-subfeatures-6").click((function(){$(".ups-hide-w-subfeatures-6").toggleClass("hide"),$(".ups-viewmore-w-subfeatures-6").toggleClass("hide"),pricingSetColWidth()}))}function pricingSetColWidth(){var widthArr=[];$(".uprh-inr").each((function(index,element){widthArr.push($(element).outerWidth())}));var maxWidth=Math.max.apply(null,widthArr)+1;$(".uprh-inr").css("min-width",maxWidth+"px")}function priceTableHover(){var rowEnter=null,rowLeave=null;$(".ups-cell").mouseenter((function(){pricingRowLeaveHandle(rowEnter=$(this).attr("data-highlight"),rowLeave),$("."+rowEnter).addClass("hover")})),$(".ups-cell").mouseleave((function(){rowLeave=$(this).attr("data-highlight"),pricingRowLeaveHandle(rowEnter,rowLeave)})),$(".ups-table").mouseleave((function(){$(".ups-cell").removeClass("hover"),$(".uprh-info-w.active").removeClass("active")})),$(".ups-plan-w.empty, .upp-plan-w, .ups-viewmore-w, .ups-col-btn-w").mouseenter((function(){$(".ups-cell").removeClass("hover"),$(".uprh-info-w.active").removeClass("active")})),$(".uprh-info-w").mouseenter((function(){$(this).addClass("active")})),$(".uprh-info-w").mouseleave((function(){$(this).removeClass("active")})),$(".uprh-popup-w").mouseenter((function(){$(this).closest(".uprh-info-w").addClass("active")})),$(".uprh-popup-w").mouseleave((function(){$(this).closest(".uprh-info-w").removeClass("active")}))}function pricingRowLeaveHandle(rowEnter,rowLeave){rowLeave!==rowEnter&&($(".ups-cell").removeClass("hover"),$(".uprh-info-w.active").removeClass("active"))}function toggleFaqs(){$("body").on("click",".ufqi-title-w",(function(){var currentItem=$(this).parent(".ufq-itm"),activeItem=currentItem.siblings(".ufq-itm.active");!currentItem.hasClass("active")&&activeItem.length>0&&(activeItem.children(".ufqi-c").slideToggle(),activeItem.removeClass("active")),currentItem.toggleClass("active"),$(this).siblings(".ufqi-c").slideToggle("fast",(function(){}))}))}function pricingFeaturesPopup(){$(".upp-viewmore").click((function(){featureModalContent($(this)),$(".upsmdl").addClass("show")}))}function featureModalContent($this){var $sibling=$this.siblings(".upp-plan-w"),$title=$sibling.find(".upp-title");$(".upsmdl-head-title").text($title.text()),$(".upsmdl-head-title").css("color",$title.css("color")),$(".upsmdl-head-price-w").html($sibling.children(".upp-price-w").html()),$(".upsmdl-c-w").html($this.siblings(".upp-popup-c").html())}function pricingMobileSlider(){$(".price-slick").each((function(){var $carousel=$(this);if($(window).width()>767)$carousel.hasClass("slick-initialized")&&$carousel.slick("unslick");else if(!$carousel.hasClass("slick-initialized")){var position=$(".ups-col.highlighted").data("highlight");$carousel.slick({centerMode:!0,centerPadding:"40px",slidesToShow:1,infinite:!1,initialSlide:position,adaptiveHeight:!1,mobileFirst:!0,focusOnSelect:!1})}}))}function load(){var default_toggle;window.popupContentIndex={index:0,indexes:{}},window.popupContents=[],handleFeaturesModal(),pricingFeaturesPopup(),pricingSetColWidth(),pricingShowMore(),priceTableHover(),pricingMobileSlider(),toggleFaqs(),headerScroll(),"lifetime"==$("#ups-main-id").data("default-toggle")&&priceSwitcher(),$(".upspd-switch-label").click((function(){priceSwitcher()})),$(".wdm-free-download-modal-btn").click((function(){$("#wdm-free-download-modal").css("display","block")})),$("#wdm-free-download-modal .close").click((function(){$("#wdm-free-download-modal").css("display","none")})),$(document).on("click","#wdm-free-download",(function(){var downloadId=$(this).data("download-id");$.ajax({type:"POST",dataType:"json",url:free_download_ajax.ajaxurl,data:{action:"increase_count_of_free_download",downloadId:downloadId,security:free_download_ajax.security},success:function(response){$.each(response.data.downloadurl,(function(index,value){setTimeout((function(){$("#download-edd-file").prop("href",value),$("a#download-edd-file")[0].click()}),1e3);}))},error:function(errorThrown){console.log(errorThrown)}})}))}$(window).on("resize orientationchange",(function(){pricingMobileSlider()}));var pricing_table=function($scope,$){load()};function editor_mode_on(){let url;return window.location.href.includes("elementor-preview")}$(document).ready((function(){editor_mode_on()||load()})),$(window).on("elementor/frontend/init",(function(){editor_mode_on()&&elementorFrontend.hooks.addAction("frontend/element_ready/wisdm-pricing-table-widget-id.default",pricing_table)}))}(jQuery);