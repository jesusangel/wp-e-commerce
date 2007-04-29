$.fn.slideNews = function(settings) {
    alert("test");
    settings = $.extend({
        headline: "Shopping Cart",
        newsWidth: 74,
        newsSpeed: "normal"
    }, settings);
    return this.each(function(i){
        //$(".messaging",this).css("display","none");
        //$("a:eq(0)",this).attr("href","#skip_to_news_" + i);
        //$("a:eq(4)",this).attr("name","skip_to_news_" + i);
        itemLength = $(".item",this).length;
        newsContainerWidth = itemLength * settings.newsWidth;
        $(".container",this).css("width",newsContainerWidth + "px");
        //$(".news_items",this).prepend("<p class='view_all'>" + settings.headline + " [ " + itemLength + " total ] &nbsp;-&nbsp; <a href='#'>View All</a></p>");
        /*$("a:eq(3)",this).click(function() {
            thisSlider = $(this).parent().parent().parent();
            $(".next",thisSlider).css("display","none");
            $(".prev",thisSlider).css("display","none");
            $(".container",thisSlider).css("left","0px");
            $(".container",thisSlider).css("width",settings.newsWidth * 2 + "px");
           //$(".view_all",thisSlider).css("display","none");
        });*/
        $(".next",this).css("display","block");
        animating = false;
        $(".next",this).click(function() {
            thisParent = $(this).parent();
            if (animating == false) {
                animating = true;
                animateLeft = parseInt($(".container",thisParent).css("left")) - (settings.newsWidth * 1);
                if (animateLeft + parseInt($(".container",thisParent).css("width")) > 0) {
                    $(".prev",thisParent).css("display","block");
                    $(".container",thisParent).animate({left: animateLeft}, settings.newsSpeed, function() {
                        $(this).css("left",animateLeft);
                        if (parseInt($(".container",thisParent).css("left")) + parseInt($(".container",thisParent).css("width")) <= settings.newsWidth * 2) {
                            $(".next",thisParent).css("display","none");
                        }
                        animating = false;
                    });
                } else {
                    animating = false;
                }
                return false;
            }
        });
        $(".prev",this).click(function() {
            thisParent = $(this).parent();
            if (animating == false) {
                animating = true;
                animateLeft = parseInt($(".container",thisParent).css("left")) + (settings.newsWidth * 1);
                if ((animateLeft + parseInt($(".container",thisParent).css("width"))) <= parseInt($(".container",thisParent).css("width"))) {
                    $(".next",thisParent).css("display","block");
                    $(".container",thisParent).animate({left: animateLeft}, settings.newsSpeed, function() {
                        $(this).css("left",animateLeft);
                        if (parseInt($(".container",thisParent).css("left")) == 0) {
                            $(".prev",thisParent).css("display","none");
                        }
                        animating = false;
                    });
                } else {
                    animating = false;
                }
                return false;
            }
        });
    });
};