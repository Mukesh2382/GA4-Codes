document.addEventListener("DOMContentLoaded", function(event) {
    var url = window.location.href;
    var parts = url.split("/");
    var lastPart = parts[parts.length - 2];
    if(lastPart === 'remui' ){
        console.log("remui");
        let view_demo_btns = document.querySelectorAll(".wdm-try-demo");
        view_demo_btns.forEach(function(view_demo_btn){
            view_demo_btn.addEventListener("click", function(e){
                window.dataLayer.push({
                    'demo_type': 'remui',
                    'event': 'view_demo',
                  });
            });
        });
    }

    if(lastPart === 'reports' ){
        console.log("reports");
        let view_demo_btns = document.querySelectorAll(".wdm-try-demo");
        view_demo_btns.forEach(function(view_demo_btn){
            view_demo_btn.addEventListener("click", function(e){
                window.dataLayer.push({
                    'demo_type': 'reports',
                    'event': 'view_demo',
                  });
            });
        });
    }
  });