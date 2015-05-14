<?php
register_activation_hook(IRP_PLUGIN_FILE, 'irp_install');
function irp_install($networkwide=NULL) {
	global $wpdb, $irp;

    $time=$irp->Options->getPluginInstallDate();
    if($time==0) {
        $irp->Options->setPluginInstallDate(time());
    }
    $irp->Options->setPluginUpdateDate(time());

    $shadow='box-shadow: 0 1px 2px rgba(0, 0, 0, 0.17); -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.17); -o-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.17); -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.17);';
    $irp->Options->setStyleShadow($shadow);

    $colors=array();
    $colors['(Default)']=array('color'=>'', 'fontColor'=>'#464646');
    $colors['WHITE']=array('color'=>'#FFFFFF', 'fontColor'=>'#464646');
    $colors['LIGHT GREY']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');
    $colors['DARK GREY']=array('color'=>'#555555');
    $colors['BLACK']=array('color'=>'#000000');
    $irp->Options->setStyleRelatedTextColors($colors);

    $colors=array();
    $colors['(Transparent)']=array('color'=>'', 'fontColor'=>'#464646');
    $colors['WHITE']=array('color'=>'#FFFFFF', 'fontColor'=>'#464646');
    $colors['LIGHT GREY']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');
    $colors['DARK GREY']=array('color'=>'#555555');
    $colors['BLACK']=array('color'=>'#000000');
    $irp->Options->setStyleBackgroundColors($colors);

    $colors=array();
    $colors['(Transparent)']=array('color'=>'', 'fontColor'=>'#464646');
    $colors['TURQUOISE']=array('color'=>'#1ABC9C');
    $colors['EMERALD']=array('color'=>'#2ECC71');
    $colors['PETER RIVER']=array('color'=>'#3498DB');
    $colors['AMETHYST']=array('color'=>'#9B59B6');
    $colors['WET ASPHALT']=array('color'=>'#34495E');
    $colors['SUN FLOWER']=array('color'=>'#F1C40F');
    $colors['CARROT']=array('color'=>'#E67E22');
    $colors['ALIZARIN']=array('color'=>'#E74C3C');
    $colors['CLOUDS']=array('color'=>'#ECF0F1', 'fontColor'=>'#464646');
    $colors['CONCRETE']=array('color'=>'#95A5A6');
    $irp->Options->setStyleLightBorderColors($colors);

    $colors=array();
    $colors['(Transparent)']=array('color'=>'', 'fontColor'=>'#464646');
    $colors['GREEN SEA']=array('color'=>'#16A085');
    $colors['NEPHRITIS']=array('color'=>'#27AE60');
    $colors['BELIZE HOLE']=array('color'=>'#2980B9');
    $colors['WISTERIA']=array('color'=>'#8E44AD');
    $colors['MIDNIGHT BLUE']=array('color'=>'#2C3E50');
    $colors['ORANGE']=array('color'=>'#F39C12');
    $colors['PUMPKIN']=array('color'=>'#D35400');
    $colors['POMEGRANATE']=array('color'=>'#C0392B');
    $colors['SILVER']=array('color'=>'#BDC3C7');
    $colors['ASBESTOS']=array('color'=>'#7F8C8D');
    $irp->Options->setStyleDarkBorderColors($colors);
    $irp->Options->setPluginFirstInstall(TRUE);
}

add_action('admin_init', 'irp_first_redirect');
function irp_first_redirect() {
    global $irp;
    if ($irp->Options->isPluginFirstInstall()) {
        $irp->Options->setPluginFirstInstall(FALSE);
        $irp->Options->setShowActivationNotice(TRUE);
        $irp->Utils->redirect(IRP_PAGE_SETTINGS);
    }
}



