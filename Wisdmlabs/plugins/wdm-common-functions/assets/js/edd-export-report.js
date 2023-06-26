jQuery(document).ready(function(){
    jQuery('.wdm_exp_tr_ren').on('click',function(f){
        console.log(wdm_ajax_object.ajax_url);
        
        f.preventDefault();
        var e={
            action:"wdm_export_free_trial_renewal",
            sdate:jQuery("#start-date").val(),
            edate:jQuery("#end-date").val(),
            gateway: jQuery("#gateway").val()
        };
        jQuery.ajax(
            {
                url:wdm_ajax_object.ajax_url,
                data:e,
                type:"POST",
                beforeSend:function(e){},
                success:function(data){
                    /*
                    * Make CSV downloadable
                    */
                    var downloadLink = document.createElement("a");
                    var fileData = ['\ufeff'+data];

                    var blobObject = new Blob(fileData,{
                        type: "text/csv;charset=utf-8;"
                    });

                    var url = URL.createObjectURL(blobObject);
                    downloadLink.href = url;
                    downloadLink.download = "free-trial-renewals.csv";

                    /*
                    * Actually download CSV
                    */
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                }
            }
        );
    });
    jQuery('.wdm_exp_upgr').on('click',function(f){
        f.preventDefault();
        var e={
            action:"wdm_export_upgrades",
            sdate:jQuery("#start-date").val(),
            edate:jQuery("#end-date").val(),
            gateway: jQuery("#gateway").val()
        };
        jQuery.ajax(
            {
                url:wdm_ajax_object.ajax_url,
                data:e,
                type:"POST",
                beforeSend:function(e){},
                success:function(e){
                    // if(e==1){
                    //     MessageManager.show('<div class="updated notice"><p>Email sent successfully!</p></div>');
                    // }else{
                    //     MessageManager.show('<div class="error notice"><p>There is an issue in sending email functionality!</p></div>');
                    // }
                }
            }
        );
    });
});