(function(c){
    c.fn.stickyfloat=function(l){
        var b=this,f=c(document),h=parseInt(b.parent().css("padding-top")),g=b.parent().offset().top,a,d,i,j,k,e;
        a=c.fn.stickyfloat.opts;
        c.extend(a,{
            startOffset:g,
            offsetY:h
        },l);
        b.css("position","absolute");
        c(window).bind("scroll.sticky",function(){
            b.stop();
            d=b.parent().height()-b.outerHeight()+h;
            d=0>d?0:d;
            i=f.scrollTop()>a.startOffset;
            j=b.offset().top>g;
            k=b.outerHeight()<c(window).height();
            if((i||j)&&k)e=a.stickToBottom?f.scrollTop()+c(window.top).height()-b.outerHeight()- g-a.offsetY:f.scrollTop()-g+a.offsetY,e>d?e=d:f.scrollTop()<a.startOffset&&!a.stickToBottom&&(e=h),5<a.duration?b.stop().delay(a.delay).animate({
                top:e
            },a.duration,a.easing):b.stop().css("top",e)
                })
        };
        
    c.fn.stickyfloat.opts={
        duration:200,
        lockBottom:!0,
        delay:0,
        easing:"linear",
        stickToBottom:!1
        }
    })(jQuery);