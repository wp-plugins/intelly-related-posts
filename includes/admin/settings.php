<?php
function irp_ui_tracking($override=FALSE) {
    global $irp;

    $track=$irp->Utils->qs('track', '');
    if($track!='') {
        $track=intval($track);
        $irp->Options->setTrackingEnable($track);
        $irp->Tracking->sendTracking(TRUE);
    }

    $uri=IRP_TAB_SETTINGS_URI.'&track=';
    if($irp->Options->isTrackingEnable()) {
        if($override) {
            $uri.='0';
            $irp->Options->pushSuccessMessage('EnableAllowTrackingNotice', $uri);
        }
    } else {
        $uri.='1';
        $irp->Options->pushErrorMessage('DisableAllowTrackingNotice', $uri);
    }
    $irp->Options->writeMessages();
}
function irp_io_first_time() {
    global $irp;
    if($irp->Options->isShowActivationNotice()) {
        $irp->Options->pushSuccessMessage('FirstTimeActivation');
        $irp->Options->writeMessages();
        $irp->Options->setShowActivationNotice(FALSE);
    }
}
function irp_ui_box_preview() {
    global $irp;

    $count=$irp->Options->getRewritePostsInBoxCount();
    $count=$irp->Utils->iqs('rewritePostsInBoxCount', $count);
    $relatedText=$irp->Options->getRelatedText();
    $relatedText=$irp->Utils->qs('relatedText', $relatedText);
    $relatedTextColor=$irp->Options->getTemplateRelatedTextColor();
    $relatedTextColor=$irp->Utils->qs('relatedTextColor', $relatedTextColor);
    $backgroundColor=$irp->Options->getTemplateBackgroundColor();
    $backgroundColor=$irp->Utils->qs('backgroundColor', $backgroundColor);
    $borderColor=$irp->Options->getTemplateBorderColor();
    $borderColor=$irp->Utils->qs('borderColor', $borderColor);
    $shadow=$irp->Options->isTemplateShadow();
    $shadow=$irp->Utils->iqs('shadow', $shadow);
    $showPoweredBy=$irp->Options->isShowPoweredBy();
    $showPoweredBy=$irp->Utils->iqs('showPoweredBy', $showPoweredBy);

    $posts=get_posts('orderby=rand&numberposts='.$count);
    $ids=array();
    foreach($posts as $p) {
        $ids[]=$p->ID;
    }
    if(count($ids)==0) {
        //TODO: please define at least one post
        return;
    }

    $args=array(
        'embedCss'=>TRUE
        , 'relatedText'=>$relatedText
        , 'relatedTextColor'=>$relatedTextColor
        , 'backgroundColor'=>$backgroundColor
        , 'borderColor'=>$borderColor
        , 'shadow'=>$shadow
        , 'showPoweredBy'=>$showPoweredBy
    );
    $box=irp_ui_get_box($ids, $args);
    echo $box;
    die();
}
function irp_ui_settings() {
    global $irp;
    irp_io_first_time();
    irp_ui_tracking(FALSE);

    ?>
    <script>
        jQuery(function() {
            function irp_val(name) {
                return jQuery('[name='+name+']').val();
            }
            function irp_check(name) {
                return (jQuery('[name='+name+']').is(':checked') ? 1 : 0);
            }
            function irp_changeRelatedBox() {
                var request=jQuery.ajax({
                    url: ajaxurl
                    , method: "POST"
                    , data: {
                        'action': 'do_action'
                        , 'irp_action': 'ui_box_preview'
                        , 'relatedText': irp_val('irpText')
                        , 'relatedTextColor': irp_val('irpTemplateRelatedTextColor')
                        , 'backgroundColor': irp_val('irpTemplateBackgroundColor')
                        , 'borderColor': irp_val('irpTemplateBorderColor')
                        , 'shadow': irp_check('irpTemplateShadow')
                        , 'showPoweredBy': irp_check('irpShowPoweredBy')
                    }
                    , dataType: "html"
                });

                request.done(function(html) {
                    jQuery("#relatedBoxExample").html(html);
                });
            }

            var array=['irpText', 'irpTemplateRelatedTextColor', 'irpTemplateBackgroundColor', 'irpTemplateBorderColor', 'irpTemplateShadow', 'irpShowPoweredBy'];
            for(i=0; i<array.length; i++) {
                jQuery('[name='+array[i]+']').change(function() {
                    irp_changeRelatedBox();
                });
            }
            irp_changeRelatedBox();
        });
    </script>
    <?php

    $irp->Form->prefix='Settings';
    $irp->Form->helps=FALSE;
    if($irp->Check->nonce('irp_settings')) {
        $irp->Options->resetMaxExecutionTime();
        $irp->Options->setActive($irp->Utils->iqs('irpActive'));
        $irp->Options->setRelatedText($irp->Utils->qs('irpText', ''));

        $irp->Options->setTemplateRelatedTextColor($irp->Utils->qs('irpTemplateRelatedTextColor'));
        $irp->Options->setTemplateBackgroundColor($irp->Utils->qs('irpTemplateBackgroundColor'));
        $irp->Options->setTemplateBorderColor($irp->Utils->qs('irpTemplateBorderColor'));
        $irp->Options->setTemplateShadow($irp->Utils->iqs('irpTemplateShadow'), 0);
        $irp->Options->setShowPoweredBy($irp->Utils->iqs('irpShowPoweredBy'));

        $irp->Options->setRewriteActive($irp->Utils->iqs('irpRewriteActive'));
        $irp->Options->setRewriteBoxesCount($irp->Utils->iqs('irpRewriteBoxesCount', 1));
        //$irp->Options->setRewritePostsInBoxCount(intval($irp->Utils->qs('irpRewritePostsInBoxCount', '')));
        $irp->Options->setRewriteThreshold($irp->Utils->iqs('irpRewriteThresold', 300));
        $irp->Options->setRewriteAtEnd($irp->Utils->iqs('irpRewriteAtEnd'));

        $irp->Options->setEngineSearch($irp->Utils->iqs('irpEngineSearch', IRP_ENGINE_SEARCH_CATEGORIES_TAGS));

        $options=$irp->Options->getRewritePostTypes();
        foreach($options as $k=>$v) {
            $v=intval($irp->Utils->qs('irpRewritePostType_'.$k, 0));
            $options[$k]=$v;
        }
        $irp->Options->setRewritePostTypes($options);

        $options=$irp->Options->getMetaboxPostTypes();
        foreach($options as $k=>$v) {
            $v=intval($irp->Utils->qs('metabox_'.$k, 0));
            $options[$k]=$v;
        }
        $irp->Options->setMetaboxPostTypes($options);
    }

    $irp->Form->formStarts();
    $irp->Form->p('GeneralSection');

    $args=array('class'=>'irp-hideShow irp-checkbox'
    , 'irp-hideIfTrue'=>'false'
    , 'irp-hideShow'=>'irp-active-box');
    $irp->Form->checkbox('irpActive', $irp->Options->isActive(), 1, $args);
    $args=array('id'=>'irp-active-box', 'name'=>'irp-active-box', 'style'=>'margin-top:10px;');
    $irp->Form->divStarts($args);
    {
        $irp->Form->text('irpText', $irp->Options->getRelatedText());

        $value=$irp->Options->getTemplateRelatedTextColor();
        $options=$irp->Options->getStyleRelatedTextColors();
        $irp->Form->colorSelect('irpTemplateRelatedTextColor', $value, $options);
        $value=$irp->Options->getTemplateBackgroundColor();
        $options=$irp->Options->getStyleBackgroundColors();
        $irp->Form->colorSelect('irpTemplateBackgroundColor', $value, $options);
        $value=$irp->Options->getTemplateBorderColor();
        $options=$irp->Options->getStyleDarkBorderColors();
        $irp->Form->colorSelect('irpTemplateBorderColor', $value, $options);
        $irp->Form->checkbox('irpTemplateShadow', $irp->Options->isTemplateShadow());
        $irp->Form->checkbox('irpShowPoweredBy', $irp->Options->isShowPoweredBy());
        ?>
        <p style="font-weight:bold;">
            <?php
            $irp->Lang->P('PreviewSection');
            $t=$irp->Options->getMaxExecutionTime();
            if($t>0) { ?>
                <br/>
                <span style="font-weight:normal;">
                    <i><?php $irp->Lang->P('PreviewSectionMaxTime', $t)?></i>
                </span>
            <?php } ?>
        </p>
        <p id="relatedBoxExample" style="width:auto;"></p>
        <?php
        $irp->Form->p('RewriteSection');
        $args=array('class'=>'irp-hideShow irp-checkbox'
        , 'irp-hideIfTrue'=>'false'
        , 'irp-hideShow'=>'irp-rewrite-box');
        $irp->Form->checkbox('irpRewriteActive', $irp->Options->isRewriteActive(), 1, $args);
        $args=array('id'=>'irp-rewrite-box', 'name'=>'irp-rewrite-box', 'style'=>'margin-top:10px;');
        $irp->Form->divStarts($args);
        {
            //$irp->Form->checkbox('irpRewriteAtEnd', $irp->Options->isRewriteAtEnd());
            $options=array();
            $options[]=array('id'=>1, 'name'=>1);
            $options[]=array('id'=>2, 'name'=>2);
            $options[]=array('id'=>3, 'name'=>3);
            //$irp->Form->select('irpRewritePostsInBoxCount', $irp->Options->getRewritePostsInBoxCount(), $options);
            $irp->Form->select('irpRewriteBoxesCount', $irp->Options->getRewriteBoxesCount(), $options);
            $irp->Form->text('irpRewriteThresold', $irp->Options->getRewriteThreshold());
            $irp->Form->checkbox('irpRewriteAtEnd', $irp->Options->isRewriteAtEnd());
            $irp->Form->p('');

            $options=$irp->Options->getRewritePostTypes();
            $types=$irp->Utils->query(IRP_QUERY_POST_TYPES);
            foreach($types as $v) {
                $v=$v['name'];
                $irp->Form->checkbox('irpRewritePostType_'.$v, $options[$v]);
            }
        }
        $irp->Form->divEnds();

        $irp->Form->p('EngineSection');
        $options=array(IRP_ENGINE_SEARCH_CATEGORIES_TAGS
            , IRP_ENGINE_SEARCH_CATEGORIES
            , IRP_ENGINE_SEARCH_TAGS);
        $irp->Form->select('irpEngineSearch', $irp->Options->getEngineSearch(), $options);

        $irp->Form->p('MetaboxSection');
        $metaboxes=$irp->Options->getMetaboxPostTypes();
        $types=$irp->Utils->query(IRP_QUERY_POST_TYPES);
        foreach($types as $v) {
            $v=$v['name'];
            $irp->Form->checkbox('metabox_'.$v, $metaboxes[$v]);
        }
    }
    $irp->Form->divEnds();

    $irp->Form->nonce('irp_settings');
    $irp->Form->submit('Save');
    $irp->Form->formEnds();
    $irp->Form->helps=FALSE;
}