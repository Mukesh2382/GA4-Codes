(function ($) {
    $(document).ready(function () {
        window.calcScrollTopPosition = function () {
            if (window.outerWidth > 767) {
                // window.secondaryScrollTop = Math.round($(".scroll-top-placeholder").offset().top - 70);
                window.pricingScrollTopStart = Math.round($(".ups-col-1 > .ups-col-inr").offset().top - 70);
                window.pricingScrollTopEnd = Math.round($(".pricing-section-placeholder").offset().top - 70);
            }
        };
        headerScroll();
        var resizeCallback = debounce(function () {
            window.calcScrollTopPosition();
        }, 250);
        window.addEventListener("resize", resizeCallback);
        $('.eLumine-vid-tml').on('click', '.tml-vid-link', function (e) {
            e.preventDefault();
            $(this).hide();
            $(this).siblings('.vid-frame').show();
            $(this).siblings('.tml-close-vid').show();
            $(this).siblings('.tml-close-vid').css("display", "block");
            $(this).parent().siblings('.tml-p2').hide();
        });
        $('.eLumine-vid-tml').on('click', '.tml-read-more', function (e) {
            e.preventDefault();
            $(this).parents('.tml-p2').removeClass('h300');
            $(this).hide();
            $(this).parents('.tml-p2').children('.tml-read').html('<a class="tml-show-less">Show Less</a>');
        });
        $('.eLumine-vid-tml').on('click', '.tml-show-less ', function (e) {
            e.preventDefault();
            $(this).parents('.tml-p2').addClass('h300');
            $(this).hide();
            $(this).parents('.tml-p2').children('.tml-read').html('<a class="tml-read-more">Read More</a>');
        });
        $('.eLumine-vid-tml').on('click', '.tml-close-vid', function (e) {
            e.preventDefault();
            $(this).siblings('.vid-frame').children('iframe').attr('src', $(this).siblings('.vid-frame').children('iframe').attr('src'));
            $(this).hide();
            $(this).siblings('.vid-frame').hide();
            $(this).parents('.tml-p3').siblings('.tml-p2').show();
            $(this).siblings('.tml-vid-link').show();
            $(".tml-p2").each(function () {
                if ($(this).height() > maxHeight) {
                    $(this).children('.tml-read').html('<a class="tml-read-more">Read More</a>');
                }
                $(this).addClass('h300');
            });
        });
        var maxHeight = 300;
        $(".eLumine-vid-tml .tml-p2").each(function () {
            if ($(this).height() > maxHeight) {
                $(this).children('.tml-read').html('<a class="tml-read-more">Read More</a>');
            }
            $(this).addClass('h300');
        });
    });

    function headerScrollReverse() {
        window.calcScrollTopPosition();
        var headerElem = $(".title-wrap");
        var secondaryElem = $(".secondary-header");
        var pricingElem = $(".header-pricing-w");
        if (window.outerWidth > 767) {
            headerElem.fadeOut(200);
            secondaryElem.fadeIn(200);
            pricingElem.fadeOut(200);
        }
        var thisScrollTop = 0;
        var lastScrollTop = 0;
        headerScrollHandle();
        var duringScrollCallback = throttle(headerScrollHandle, 100);

        function headerScrollHandle() {
            if (window.outerWidth > 767) {
                thisScrollTop = Math.round($(window).scrollTop());
                if (thisScrollTop < window.secondaryScrollTop) {
                    headerElem.fadeOut(200);
                    secondaryElem.fadeIn(200);
                    pricingElem.fadeOut(200);
                } else if (thisScrollTop > window.pricingScrollTopStart && thisScrollTop < window.pricingScrollTopEnd) {
                    headerElem.fadeOut(200);
                    secondaryElem.fadeOut(200);
                    pricingElem.fadeIn(200);
                } else if (thisScrollTop > window.pricingScrollTopEnd || thisScrollTop > window.secondaryScrollTop) {
                    headerElem.fadeOut(200);
                    secondaryElem.fadeIn(200);
                    pricingElem.fadeOut(200);
                }
            }
        }
        document.addEventListener("scroll", duringScrollCallback, {
            capture: true,
            passive: true
        });
    }

    function headerScroll() {
        window.calcScrollTopPosition();
        var headerElem = $(".title-wrap");
        var secondaryElem = $(".secondary-header");
        var pricingElem = $(".header-pricing-w");
        var thisScrollTop = 0;
        var lastScrollTop = 0;
        headerScrollHandle();
        var duringScrollCallback = throttle(headerScrollHandle, 100);

        function headerScrollHandle() {
            if (window.outerWidth > 767) {
                thisScrollTop = Math.round($(window).scrollTop());
                if (thisScrollTop < window.secondaryScrollTop) {
                    headerElem.fadeIn(200);
                    secondaryElem.fadeOut(200);
                    pricingElem.fadeOut(200);
                } else if (thisScrollTop > window.pricingScrollTopStart && thisScrollTop < window.pricingScrollTopEnd) {
                    headerElem.fadeOut(200);
                    secondaryElem.fadeOut(200);
                    pricingElem.fadeIn(200);
                } else if (thisScrollTop > window.pricingScrollTopEnd || thisScrollTop > window.secondaryScrollTop) {
                    headerElem.fadeOut(200);
                    secondaryElem.fadeIn(200);
                    pricingElem.fadeOut(200);
                }
            }
        }
        document.addEventListener("scroll", duringScrollCallback, {
            capture: true,
            passive: true
        });
    }
})(jQuery);
(function ($) {
    $(document).ready(function () {
        window.customMobSliders = [];
        window.popupContentIndex = {
            index: 0,
            indexes: {}
        };
        window.popupContents = [];
        window.mobSliderParam = {
            init: false,
            slidesPerView: "auto",
            pagination: {
                el: ".swiper-dots-wrap",
                type: "bullets",
                clickable: true,
                bulletClass: "swiper-dot",
                bulletActiveClass: "swiper-dot-active"
            }
        };
        window.desktopSliderParam = {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-dots-wrap",
                type: "bullets",
                clickable: true,
                bulletClass: "swiper-dot",
                bulletActiveClass: "swiper-dot-active"
            }
        };
        window.mobSliderParamNum = {
            init: false,
            slidesPerView: "auto",
            pagination: {
                el: ".swiper-dots-wrap",
                type: "fraction"
            }
        };
        window.transitionEvent = whichTransitionEvent();
        // Page Specific Modifications Start
        // handleInternalPageScroll();
        handleMainModal();
        // showVideoPopup();
        // featuresPopup();
        handleFeaturesModal();
        demosSlider();
        // featuresMobileSlider();
        // featuresShowMore();
        pricingFeaturesPopup();
        integrationViewMoreToggle();
        documentationMobileRedirect();
        toggleTabs();
        pricingSetColWidth();
        priceSwitcher();
        pricingShowMore();
        priceTableHover();
        pricingMobileSlider();
        toggleFaqs();
        testimonialSlider();
        demosSliderDesktop();
        handleLightBox();
        screenshotsPopup();
        handleGoToTop();
        // Page Specific Modifications End
        handleMobileSlider();
            var resizeCallback = debounce(function() {
            handleMobileSlider();
        }, 250);
        window.addEventListener("resize", resizeCallback);
    });

    function handleInternalPageScroll() {
        var selectors = ".uhrd-lk, .uhrd-buy-w, .wdm-total-count";
        var classname = document.querySelectorAll(selectors);
        for (var i = 0; i < classname.length; i++) {
            classname[i].removeEventListener('click', internalLinkScriptCallback, false);
        }
        $(selectors).click(function (e) {
            e.preventDefault();
            handlePageScroll($(this).attr("href"));
        });
        $(selectors).on("contextmenu", function (e) {
            return false;
        });
    }

    function handlePageScroll(selector) {
        var offsetTop =
            $(selector).offset().top - ($(".site-header-alt").outerHeight() + 5);
        $("html, body").animate({
                scrollTop: offsetTop
            },
            400
        );
    }
    // function headerScroll() {
    //   var lastScrollTop = 0;
    //   var headerElem = $(".site-header-alt");
    //   var afterScrollCallback = debounce(function() {
    //     var st = window.pageYOffset || document.documentElement.scrollTop; // Credits: "https://github.com/qeremy/so/blob/master/so.dom.js#L426"
    //     if (st >= lastScrollTop) {
    //       headerElem.removeClass("hidden");
    //     }
    //     lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling
    //   }, 250);
    //   document.addEventListener("scroll", afterScrollCallback, {
    //     capture: true,
    //     passive: true
    //   });
    // }

    function handleModal(detachClass, closeClass, closeCallback, closeIconClass) {
        $("body").append($(detachClass).detach());
        $(closeClass).click(function (event) {
            if (event.target !== this) {
                return;
            }
            closeCallback();
        });
        $(closeIconClass).click(function () {
            closeCallback();
        });
    }

    function handleMainModal() {
        handleModal(
            ".umdl",
            ".umdl-inr, umdl-c-w",
            closeUmdl,
            ".umdl-close-icon-w"
        );
    }

    function handleLightBox() {
        handleModal(
            ".ulbx",
            ".ulbx-inr, ulbx-c-w",
            closeLightbox,
            ".ulbx-close-icon-w"
        );
    }

    function handleFeaturesModal() {
        handleModal(".upsmdl", ".upsmdl", closeUpsmdl, ".upsmdl-close-icon-w");
    }

    function closeUmdl() {
        closeModal(".umdl", ".umdl-c-w");
    }

    function closeUpsmdl() {
        closeModal(".upsmdl", ".upsmdl-c-w");
    }

    function closeModal(modal, contentWrap) {
        $(modal).removeClass("show");
        $(modal).one(window.transitionEvent, function (event) {
            $(contentWrap).html("");
        });
    }

    function showVideoPopup() {
        $(".ubnr-btn").click(function () {
            $(".umdl-c-w").append(
                '<div class="ubnr-vid-w">\
            <div class="ubnr-vid-inr">\
                <iframe src=' +
                $(this).attr("data-vid-link") +
                ' frameborder="0" class="ubnr-vid"></iframe>\
            </div></div>'
            );
            $(".umdl").addClass("show");
        });
    }

    function featuresPopup() {
        $(".uftr-i-w,.view-more-ss").click(function (e) {
            e.preventDefault();
            if (window.outerWidth >= 768) {
                var $elem = $(this).closest(".uftr-inr");
                var currentIndex = parseInt($elem.attr("data-index"));
                showFeatureLightbox($elem, currentIndex);
            }
        });
        $(".uftr-inr").click(function () {
            if (window.outerWidth < 768) {
                var $elem = $(this);
                var currentIndex = parseInt($elem.attr("data-index"));
                showFeatureLightbox($elem, currentIndex);
            }
        });
        $(".ulbx-arr").hover(function () {
            $(this)
                .parent(".swiper-arr-w")
                .toggleClass("hover");
        });
    }

    function showFeatureLightbox($elem, currentIndex) {
        var content = "";
        var newCurrentIndex = 0;
        var currentIndexArr = [0];
        var $elems = $elem.closest(".uftrs").find(".uftr-inr");
        var elemClass = " hide";
        $elems.each(function (index, element) {
            if (currentIndex <= index) {
                elemClass = "";
            }
            $like_icon = $(element)
                .find(".wdm-like-count-and-action")
                .clone()
                .prop("outerHTML");
            content += getFeaturePopupContent(
                $(element),
                index,
                elemClass,
                $like_icon
            );
        });
        $elems.each(function (index) {
            if (currentIndex === index) {
                return false;
            }
            if (window.uftrPopupData[index] != null) {
                newCurrentIndex = newCurrentIndex + window.uftrPopupData[index].length;
                currentIndexArr.push(newCurrentIndex);
            }
        });
        showLightBox(content, currentIndexArr);
    }

    function getFeaturePopupContent($parent, index, elemClass, $like_icon) {
        var title = $parent.find(".uftr-th3").text();
        var subtitle =
            '<div class="ulbx-itm-st-txt">' +
            $parent.find(".uftr-st-txt-inr").text() +
            "</div>";
        if ($parent.find(".uftr-st-tg").length > 0) {
            subtitle += $parent.find(".uftr-st-tg").prop("outerHTML");
        }
        var desc = $parent.find(".uftr-ds").text();
        var imageUrl = "";
        var content = "";
        var oldIndex = window.popupContentIndex.index;
        if (window.uftrPopupData[index] != null) {
            window.uftrPopupData[index].forEach(function (data, innerIndex) {
                imageUrl = data.src;
                desc = data.desc.length > 0 ? data.desc : desc;
                if (innerIndex === 0) {
                    for (
                        var contentIndex = 0; contentIndex < window.uftrPopupData[index].length; contentIndex++
                    ) {
                        window.popupContentIndex.indexes[
                            window.popupContentIndex.index + contentIndex
                        ] = window.popupContentIndex.index;
                    }
                    window.popupContentIndex.index =
                        window.popupContentIndex.index + window.uftrPopupData[index].length;
                    window.popupContents.push(
                        '<div class="ulbx-itm-c ulbx-itm-c-alt ulbx-item-c-alt-' +
                        oldIndex +
                        elemClass +
                        '">\
                  <div class="ulbx-itm-t">' +
                        title +
                        '</div>\
                  <div class="ulbx-itm-subtxt-wrap hide-m">\
                    <div class="ulbx-itm-st">' +
                        subtitle +
                        '</div>\
                    <div class="ulbx-itm-desc">' +
                        desc +
                        "</div>\
                  </div>" +
                        $like_icon +
                        "</div>"
                    );
                }
                content +=
                    '<div class="ulbx-itm swiper-slide swiper-slide-sm">\
                <div class="ulbx-itm-c">\
                  <div class="ulbx-itm-t">' +
                    title +
                    '</div>\
                  <div class="ulbx-itm-subtxt-wrap hide-m">\
                    <div class="ulbx-itm-st">' +
                    subtitle +
                    '</div>\
                    <div class="ulbx-itm-desc">' +
                    desc +
                    "</div>\
                  </div>" +
                    '</div>\
                <div class="ulbx-itm-i-w" style="background-image:url(' +
                    imageUrl +
                    ');">\
                  <img src="' +
                    imageUrl +
                    '" alt="" class="ulbx-itm-i">\
                </div>' +
                    $like_icon +
                    "</div>";
            });
        }
        return content;
    }

    function showLightBox(content, currentIndexArr) {
        $(".ulbx-c-w")
            .find(".swiper-arr-r")
            .removeClass("swiper-arr-disabled");
        $(".ulbx-c-w")
            .find(".swiper-arr-l")
            .addClass("swiper-arr-disabled");
        $(".ulbx-itm").remove();
        $(".popup-content-wrap").remove();
        window.popupContents.reverse();
        var innerContent =
            '<div class="popup-content-wrap">' +
            window.popupContents.join("") +
            "</div>";
        window.popupContents = [];
        window.popupContentIndex.index = 0;
        $(".ulbx-c-inr").append(innerContent);
        $(".ulbx-c-main").html(content);
        var swiperInstance = window.customMobSliders.find(function (elem) {
            return elem.name === "lightBoxSlider";
        });
        if (swiperInstance) {
            swiperInstance.slider.update();
            swiperInstance.slider.slideTo(
                currentIndexArr[currentIndexArr.length - 1],
                0
            );
        }
        $(".ulbx").addClass("show");
        $(".ulbx").one(window.transitionEvent, function (event) {
            if (swiperInstance) {
                swiperInstance.slider.update();
                return;
            }
            var swiperInstance = new Swiper(".ulbx-c-inr", {
                autoHeight: true,
                // allowTouchMove: false,
                pagination: {
                    el: "#lightbox-pagination",
                    type: "progressbar"
                }
            });
            swiperInstance.slideTo(currentIndexArr[currentIndexArr.length - 1], 0);
            handleArrowOnSlideChange(swiperInstance);
            window.customMobSliders.push({
                name: "lightBoxSlider",
                slider: swiperInstance,
                destroy: false
            });
            handleSwiperArrow(swiperInstance, ".ulbx-c-outer");
        });
    }

    function closeLightbox() {
        $(".ulbx").removeClass("show");
        window.popupContentIndex = {
            index: 0,
            indexes: {}
        };
    }

    function screenshotsPopup() {
        $(".uss-itm-fg-inr").click(function () {
            if (window.outerWidth >= 768) {
                var currentIndex = parseInt($(this).attr("data-index"));
                var content = "";
                var elemClass = " hide";
                $(this)
                    .closest(".uss-tab-panel-wrap")
                    .find(".uss-itm-fg-inr")
                    .each(function (index, element) {
                        if (currentIndex <= index) {
                            elemClass = "";
                        }
                        content += getScreenshotPopupContent(
                            $(element),
                            $(element)
                            .find(".uss-itm-i")
                            .attr("data-large-src"),
                            index,
                            elemClass
                        );
                    });
                showLightBox(content, [currentIndex]);
            }
        });
    }

    function getScreenshotPopupContent($elem, imageUrl, index, elemClass) {
        window.popupContentIndex.indexes[index] = index;
        var title = $elem.find(".uss-itm-fgc").text();
        var tag = $elem.attr("data-main-title");
        var str =
            '<div class="ulbx-itm-c ulbx-itm-c-alt ulbx-item-c-alt-' +
            index +
            elemClass +
            '">';
        if (tag) {
            str +=
                '<div class="ulbx-itm-tag-w"><span class="ulbx-itm-tag">' +
                tag +
                "</span></div>";
        }
        str += '<div class="ulbx-itm-t">' + title + "</div>\
      </div>";
        window.popupContents.push(str);
        return (
            '<div class="ulbx-itm swiper-slide swiper-slide-sm">\
            <div class="ulbx-itm-i-w" style="background-image:url(' +
            imageUrl +
            ');">\
              <img src="' +
            imageUrl +
            '" alt="" class="ulbx-itm-i">\
            </div>\
          </div>'
        );
    }

    function featuresMobileSlider() {
        var mobSlider = new Swiper(
            ".uftrs.swiper-container-sm",
            window.mobSliderParamNum
        );
        var fractionProgressElem = $(mobSlider.$el).children(".swiper-dots-wrap");
        mobSlider.on("touchStart", function () {
            fractionProgressElem.addClass("active");
        });
        mobSlider.on("transitionEnd", function () {
            setTimeout(function () {
                fractionProgressElem.removeClass("active");
            }, 1500);
        });
        window.customMobSliders.push({
            name: "featuresSlider",
            slider: mobSlider
        });
    }

    function demosSlider() {
        if ($(".uidms.swiper-container-sm").length > 0 && $('#demos').length > 0) {
            var mobSlider = new Swiper(
                ".uidms.swiper-container-sm",
                window.mobSliderParam
            );
            window.customMobSliders.push({
                name: "demosSlider",
                slider: mobSlider
            });
        }
    }

    function integrationViewMoreToggle() {
        $(".uint-viewmore").click(function () {
            $(".uint-view-w").toggleClass("hide");
            $(".uint-hide-w").slideToggle("fast");
        });
    }

    function documentationMobileRedirect() {
        $(".udoci-inr").click(function () {
            if (window.outerWidth < 768) {
                window.location.href = $(this)
                    .find(".udoci-lk")
                    .attr("href");
            }
        });
    }

    function toggleTabs() {
        $(".uss-thli").click(function () {
            $(".uss-thli, .uss-tab-panel").removeClass("active");
            $(this).addClass("active");
            $("#" + $(this).attr("aria-controls")).addClass("active");
        });
    }

    function priceSwitcher() {
        $(".upspd-switch-label").click(function () {
            $(".upspd-switch-label").toggleClass("active");
            $(".upspd-h5").toggleClass("active");
            $(".ups-col-btn").toggleClass("hide");
            $(".ups-toggle-txt").each(function (index, element) {
                switchPriceText(element);
            });
            $(".header-price-txt").each(function (index, element) {
                switchPriceText(element);
            });
            // Added for Black Friday Sales
            $(".header-sale-price-txt").each(function (index, element) {
                switchPriceText(element);
            });
            // Added for Black Friday Sales
            $(".header-yousave-price-txt").each(function (index, element) {
                switchPriceText(element);
            });
            // If has class active it means lifetime is selected
            if ($(this).hasClass('active')) {
                $('.sing-prod-price').each(function (i) {
                    if (typeof $(this).data('lifetime') !== 'undefined') {
                        $(this).text($(this).data('lifetime'));
                    }
                });
            } else {
                $('.sing-prod-price').each(function (i) {
                    if (typeof $(this).data('yearly') !== 'undefined') {
                        $(this).text($(this).data('yearly'));
                    }
                });
            }
        });

        function switchPriceText(element) {
            var text = $(element)
                .text()
                .trim();
            var toggleText = $(element).attr("data-toggle-txt");
            $(element).text(toggleText);
            if (toggleText.length > 0) {
                $(element).parent(".upp-sale-price").siblings(".upp-reg-price").addClass("strike");
                $(element).parent(".upp-sale-price").show();
                $(element).parent(".upp-price-diff-inr").show();
            } else {
                $(element).parent(".upp-sale-price").siblings(".upp-reg-price").removeClass("strike");
                $(element).parent(".upp-sale-price").hide();
                $(element).parent(".upp-price-diff-inr").hide();
            }
            $(element).attr("data-toggle-txt", text);
        }
    }

    function pricingShowMore() {
        $(".ups-viewmore").click(function () {
            var str1 = 'Less';
            if ($(this).html().indexOf(str1) != -1) {
                var offset = $(this).closest('.ups-viewmore-w').closest('.ups-col-inr').find('.ups-viewmore-w').first().prev('.ups-cell').offset();
                $('html, body').animate({
                    scrollTop: offset.top - 200,
                    scrollLeft: offset.left
                }, 500);
            }
            $(".ups-viewmore-w").toggleClass("hide");
            
            $(".ups-hide-w").slideToggle("fast", function () {
                window.calcScrollTopPosition();
                pricingSetColWidth();
            });
        });
        $(".ups-viewmore-high").click(function () {
            var str1 = 'Less';
            if ($(this).html().indexOf(str1) != -1) {
                var offset = $(this).closest('.ups-viewmore-highlights').prev('.ups-parent').find('.ups-viewmore-highlights').first().prev('.ups-cell').offset();
                $('html, body').animate({
                    scrollTop: offset.top - 200,
                    scrollLeft: offset.left
                }, 500);
            }
            var row_id = $(this).attr('data-row_id');
            $(".ups-hide-w-" + row_id).toggleClass("hide");
            $(".ups-viewmore-w-" + row_id).toggleClass("hide");
            pricingSetColWidth();
        });
    }

    function pricingSetColWidth() {
        var widthArr = [];
        $(".uprh-inr").each(function (index, element) {
            widthArr.push($(element).outerWidth());
        });
        var maxWidth = Math.max.apply(null, widthArr) + 1;
        $(".uprh-inr").css("min-width", maxWidth + "px");
    }

    function priceTableHover() {
        var rowEnter = null,
            rowLeave = null;
        $(".ups-cell").mouseenter(function () {
            rowEnter = $(this).attr("data-highlight");
            pricingRowLeaveHandle(rowEnter, rowLeave);
            $("." + rowEnter).addClass("hover");
        });
        $(".ups-cell").mouseleave(function () {
            rowLeave = $(this).attr("data-highlight");
            pricingRowLeaveHandle(rowEnter, rowLeave);
        });
        $(".ups-table").mouseleave(function () {
            $(".ups-cell").removeClass("hover");
            $(".uprh-info-w.active").removeClass("active");
        });
        $(
            ".ups-plan-w.empty, .upp-plan-w, .ups-viewmore-w, .ups-col-btn-w"
        ).mouseenter(function () {
            $(".ups-cell").removeClass("hover");
            $(".uprh-info-w.active").removeClass("active");
        });
        $(".uprh-info-w").mouseenter(function () {
            $(this).addClass("active");
        });
        $(".uprh-info-w").mouseleave(function () {
            $(this).removeClass("active");
        });
        $(".uprh-popup-w").mouseenter(function () {
            $(this)
                .closest(".uprh-info-w")
                .addClass("active");
        });
        $(".uprh-popup-w").mouseleave(function () {
            $(this)
                .closest(".uprh-info-w")
                .removeClass("active");
        });
    }

    function pricingRowLeaveHandle(rowEnter, rowLeave) {
        if (rowLeave !== rowEnter) {
            $(".ups-cell").removeClass("hover");
            $(".uprh-info-w.active").removeClass("active");
        }
    }

    function toggleFaqs() {
        $("body").on("click", ".ufqi-title-w", function () {
            var currentItem = $(this).parent(".ufq-itm");
            var activeItem = currentItem.siblings(".ufq-itm.active");
            if (!currentItem.hasClass("active") && activeItem.length > 0) {
                activeItem.children(".ufqi-c").slideToggle();
                activeItem.removeClass("active");
            }
            currentItem.toggleClass("active");
            $(this)
                .siblings(".ufqi-c")
                .slideToggle("fast", function () {
                    window.calcScrollTopPosition();
                });
        });
    }

    function testimonialSlider() {
        if ($('#testimonials').length > 0) {
            var tmlSwiper = new Swiper(".tml-w-inr", {
                slidesPerView: 1,
                spaceBetween: 30,
                autoHeight: true,
                pagination: {
                    el: ".tml-w-inr .swiper-dots-wrap",
                    type: "bullets",
                    clickable: true,
                    bulletClass: "swiper-dot",
                    bulletActiveClass: "swiper-dot-active"
                }
            });
            window.customMobSliders.push({
                name: "testimonialSlider",
                slider: tmlSwiper,
                destroy: false
            });
            handleSwiperArrow(tmlSwiper, ".tml-w-outer");
            /*Tml Video Slider code*/
            tmlSwiper.on('slideChange', function () {
                var maxHeight = 300;
                $(".eLumine-vid-tml .tml-p2").each(function () {
                    if ($(this).height() > maxHeight) {
                        $(this).children('.tml-read').html('<a class="tml-read-more">Read More</a>');
                    }
                    $(this).addClass('h300');
                });
            });
        }
    }

    function demosSliderDesktop() {
        if ($(window).width() > 767) {
            var demoSwiper = new Swiper(".uidms", {
                slidesPerView: 3.4,
                spaceBetween: 10,
                autoHeight: true,
                pagination: {
                    el: ".uidms .swiper-dots-wrap",
                    type: "bullets",
                    clickable: true,
                    bulletClass: "swiper-dot",
                    bulletActiveClass: "swiper-dot-active"
                },
            });
            window.customMobSliders.push({
                name: "demosSlider",
                slider: demoSwiper,
                destroy: false
            });
            handleSwiperArrow(demoSwiper, ".uidms-w-outer");
        }
    }
    /*Ends Here*/
    function handleSwiperArrow(swiper, parent) {
        $(parent).on("click", ".swiper-arr-r", function () {
            handleLightBoxRightNavigation(swiper.activeIndex + 1, parent);
            swiper.slideNext(250);
        });
        $(parent).on("click", ".swiper-arr-l", function () {
            handleLightBoxLeftNavigation(swiper.activeIndex - 1, parent);
            swiper.slidePrev(250);
        });
        swiper.on("slideChangeTransitionStart", function () {
            handleArrowOnSlideChange(this);
        });
    }

    function handleArrowOnSlideChange(swiperInstance) {
        var wrapperElem = $(swiperInstance.$el).parent();
        if (swiperInstance.isEnd) {
            wrapperElem.find(".swiper-arr-r").addClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-rd").removeClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-l").removeClass("swiper-arr-disabled");
        } else if (swiperInstance.isBeginning) {
            wrapperElem.find(".swiper-arr-l").addClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-ld").removeClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-r").removeClass("swiper-arr-disabled");
        } else {
            wrapperElem.find(".swiper-arr-r").removeClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-l").removeClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-rd").addClass("swiper-arr-disabled");
            wrapperElem.find(".swiper-arr-ld").addClass("swiper-arr-disabled");
        }
    }

    function handleLightBoxRightNavigation(mainIndex, parent) {
        if (
            parent === ".ulbx-c-outer" &&
            typeof window.popupContentIndex.indexes[mainIndex] !== "undefined"
        ) {
            var $elem = $(
                ".ulbx-item-c-alt-" + window.popupContentIndex.indexes[mainIndex]
            );
            $elem.fadeIn(0);
            $elem.next().fadeOut(0);
        }
    }

    function handleLightBoxLeftNavigation(mainIndex, parent) {
        if (
            parent === ".ulbx-c-outer" &&
            typeof window.popupContentIndex.indexes[mainIndex] !== "undefined"
        ) {
            var $elem = $(
                ".ulbx-item-c-alt-" + window.popupContentIndex.indexes[mainIndex]
            );
            $elem.fadeIn(0);
            $elem.prev().fadeOut(0);
        }
    }

    function pricingFeaturesPopup() {
        $(".upp-viewmore").click(function () {
            featureModalContent($(this));
            $(".upsmdl").addClass("show");
        });
    }

    function featureModalContent($this) {
        var $sibling = $this.siblings(".upp-plan-w");
        var $title = $sibling.find(".upp-title");
        $(".upsmdl-head-title").text($title.text());
        $(".upsmdl-head-title").css("color", $title.css("color"));
        $(".upsmdl-head-price-w").html($sibling.children(".upp-price-w").html());
        $(".upsmdl-c-w").html($this.siblings(".upp-popup-c").html());
    }

    function pricingMobileSlider() {
        if ($(".uiprc-w").length > 0) {
            var mobSlider = new Swiper(".uiprc-w.swiper-container-sm", {
                init: false,
                centeredSlides: true,
                slidesPerView: "auto"
            });
            mobSlider.on("init", function () {
                mobSlider.slideTo(2, 0);
            });
            window.customMobSliders.push({
                name: "pricingSlider",
                slider: mobSlider
            });
        } else {
            var mobSlider = new Swiper(".upsm.swiper-container-sm", {
                init: false,
                centeredSlides: true,
                slidesPerView: "auto"
            });
            mobSlider.on("init", function () {
                mobSlider.slideTo(1, 0);
            });
            window.customMobSliders.push({
                name: "pricingSlider",
                slider: mobSlider
            });
        }
    }

    function handleMobileSlider() {
        window.customMobSliders.forEach(function (element) {
            var slider = element.slider;
            if (window.outerWidth < 768) {
                slider.init();
                slider.updateSize();
                return;
            }
            if (
                typeof element.destroy === "undefined" &&
                typeof slider.initialized !== "undefined"
            ) {
                slider.destroy(false);
            }
        });
    }

    function featuresShowMore() {
        $(".uftrs-toggle-btn").click(function () {
            $(".uftrs-showmore-inr").slideToggle(.1, function () {
                window.calcScrollTopPosition();
                if (!$(".uftrs-showmore-w").hasClass("uftrs-showmore-partial")) {
                    handlePageScroll(".uftrs-showmore-w");
                }
            });
            $(".uftrs-showmore-w").toggleClass("uftrs-showmore-partial");
            $(".uftrs-showmore-btn-w").toggleClass("hide");
        });
        $('.uftrs-showless-btn').on('click', function (e) {
            handlePageScroll(".uftrs-showmore-w");
        });
    }

    function handleGoToTop() {
        $(".ugtp-i").click(function () {
            $("html, body").animate({
                    scrollTop: 0
                },
                400
            );
        });
    }
})(jQuery);

function whichTransitionEvent() {
    var t,
        el = document.createElement("fakeelement");

    var transitions = {
        transition: "transitionend",
        OTransition: "oTransitionEnd",
        MozTransition: "transitionend",
        WebkitTransition: "webkitTransitionEnd"
    };

    for (t in transitions) {
        if (el.style[t] !== undefined) {
            return transitions[t];
        }
    }
}

function debounce(func, wait, immediate) {
    var timeout = void 0;
    return function () {
        var context = this,
            args = arguments,
            later = function later() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            },
            callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function throttle(callback, limit) {
    var wait = false;
    return function () {
        if (!wait) {
            callback.apply(null, arguments);
            wait = true;
            setTimeout(function () {
                wait = false;
            }, limit);
        }
    };
}