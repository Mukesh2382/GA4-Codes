<?php

if (strpos( get_site_url() , 'wisdm' ) !== false) {
    // WISDMLABS
    define('WDM_PRIMARY_COLOR','#a73232');
    define('WDM_SECONDARY_COLOR','#a73232');
    define('WDM_TICK_IMAGE_SM','wdm-tick-sm.svg');
    define('WDM_TICK_IMAGE_LG','wdm-tick-lg.svg');
}
else{
    // EDWISER
    define('WDM_PRIMARY_COLOR','#f55d26');
    define('WDM_SECONDARY_COLOR','#00A1A8');
    define('WDM_TICK_IMAGE_SM','tick-sm.svg');
    define('WDM_TICK_IMAGE_LG','tick-lg.svg');
}

