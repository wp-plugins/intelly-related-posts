<?php
add_filter('wp_head', 'irp_head');
function irp_head(){
    global $post, $irp;

    $irp->Logger->startTime('irp_head');
    $irp->Options->initRelatedPostsIds(NULL);
    $irp->Options->setPostShown(NULL);
    if($post && isset($post->ID) && is_single($post->ID)) {
        $irp->Options->setPostShown($post);
        $args=array('postId'=>$post->ID, 'shuffle'=>TRUE, 'count'=>-1);
        $ids=$irp->Manager->getRelatedPostsIds($args);
        $irp->Options->initRelatedPostsIds($ids);
        $irp->Logger->info('POST ID=%s IS SHOWN, RELATED POSTS=%s', $post->ID, $ids);
    }
    $irp->Logger->pauseTime();
}
add_filter('wp_footer', 'irp_footer');
add_filter('admin_footer', 'irp_footer');
function irp_footer() {
    global $irp;

    $irp->Logger->startTime('irp_footer');
    //print only the required css, the css already written
    //skip this part...now we write inline style
    /*
    $css=$irp->Options->getUsedCssTemplates();
    if(count($css)>0) {
        echo '<style>';
        foreach($css as $k=>$v) {
            echo '.'.$v.' {';
            $v=$irp->Options->getCssTemplate($k);
            echo '  '.$v;
            echo '}';
        }
        echo '</style>';
    }
    */
    $irp->Logger->pauseTime();
    $irp->Logger->stopTime();
}
add_shortcode('irp', 'irp_shortcode');
function irp_shortcode($atts, $content='') {
    global $irp;

    $irp->Logger->startTime('irp_shortcode');
    $post=$irp->Options->getPostShown();
    if(!$post|| !$irp->Options->isActive() || $irp->Options->isPostShownExcluded()) {
        return '';
    }

    $default=array('posts'=>'', 'cats'=>'', 'tags'=>'', 'count'=>1);
    $args=shortcode_atts($default, $atts);
    if(isset($args['postId'])) {
        unset($args['postId']);
    }
    $args['count']=intval($args['count']);
    if($args['count']<=0) {
        return '';
    }

    if($args['posts']=='' && $args['cats']=='' && $args['tags']=='') {
        //dynamic
        $ids=$args['count'];
    } else {
        //static
        $ids=$irp->Manager->getRelatedPostsIds($args);
    }

    $ids=$irp->Options->getToShowPostsIds($ids, TRUE);
    $result=irp_ui_get_box($ids);
    if($result!='') {
        $irp->Options->setShortcodeUsed(TRUE);
    }

    $irp->Logger->pauseTime();
    return $result;
}

function irp_ui_get_box($ids, $options=NULL) {
    global $irp;

    if(!is_array($ids) || count($ids)==0) {
        return "";
    }

    $irp->Logger->startTime('irp_ui_get_box');
    $defaults=array(
        'embedCss'=>TRUE
        , 'relatedText'=>$irp->Options->getRelatedText()
        , 'relatedTextColor'=>$irp->Options->getTemplateRelatedTextColor()
        , 'backgroundColor'=>$irp->Options->getTemplateBackgroundColor()
        , 'borderColor'=>$irp->Options->getTemplateBorderColor()
        , 'shadow'=>$irp->Options->isTemplateShadow()
        , 'showPoweredBy'=>$irp->Options->isShowPoweredBy()
        , 'comment'=>''
    );
    $options=$irp->Utils->parseArgs($options, $defaults);

    $posts=array();
    foreach($ids as $postId) {
        $v=get_post($postId);
        if($v) {
            $posts[]=$v;
        }
    }
    $body='';
    if(count($posts)>0) {
        if(TRUE || $options['embedCss']) {
            $key=$options['relatedTextColor'];
            $array=$irp->Options->getStyleRelatedTextColors();
            $relatedTextColor=$irp->Utils->getArrayValue($key, $array, 'color');

            $key=$options['backgroundColor'];
            $array=$irp->Options->getStyleBackgroundColors();
            $backgroundColor=$irp->Utils->getArrayValue($key, $array, 'color');

            $key=$options['borderColor'];
            $array=$irp->Options->getStyleDarkBorderColors();
            $borderColor=$irp->Utils->getArrayValue($key, $array, 'color');
            if($borderColor===FALSE) {
                $key=$options['borderColor'];
                $array=$irp->Options->getStyleLightBorderColors();
                $borderColor=$irp->Utils->getArrayValue($key, $array, 'color');
            }

            $shadow=intval($options['shadow']);
            $style='padding:10px; font-weight:bold; margin-top:1em; margin-bottom:1em';
            if($relatedTextColor!==FALSE && $relatedTextColor!='') {
                $relatedTextColor='; color:'.$relatedTextColor;
            }
            if($backgroundColor!==FALSE && $backgroundColor!='') {
                $style.='; background-color:'.$backgroundColor;
            }
            if($borderColor!==FALSE && $borderColor!='') {
                $style.='; border-left:4px solid '.$borderColor;
            }
            if($shadow!==FALSE && $shadow) {
                $style.='; '.$irp->Options->getStyleShadow();
            }

            $body.="<div style=\"".$style."\">";
        } else {
            $class=$irp->Options->useCssTemplate($options['template']);
            $body.="<div class=\"".$class."\">";
        }
        if($options['comment']!='') {
            $body.=$options['comment'];
        }
        if($options['relatedText']!='') {
            $body.="<span style=\"font-weight:bold".$relatedTextColor."\">".$options['relatedText']."</span>&nbsp;";
        }

        $first=TRUE;
        foreach($posts as $v) {
            if(!$first) {
                $body.=',&nbsp;';
            } else {
                $first=FALSE;
            }
            $body.="<a style=\"font-weight:bold;\" href=\"".get_permalink($v->ID)."\" target=\"_blank\">".$v->post_title."</a>";
        }
        if($options['showPoweredBy'] && $body!='') {
            $body.="<div style=\"width:100&#37;; text-align:right; font-weight: normal;\">";
            $body.="<div style=\"font-size:10px;\">";
            $body.="<span style=\"".$relatedTextColor."\">Intelly powered by</span>&nbsp;";
            $body.="<a style=\"font-weight:bold;\" href=\"".IRP_PAGE_WORDPRESS."\" target=\"_blank\">";
            $body.="Inline Related Posts";
            $body.="</a>";
            $body.="</div>";
            $body.="</div>";
        }
        $body.="</div>";
    }
    $irp->Logger->pauseTime();
    return $body;
}

//add_filter('the_content', 'irp_the_content');
add_filter('wp_head', 'irp_the_content');
function irp_the_content() {
    global $irp, $post;

    $irp->Logger->startTime('irp_the_content');
    if(!$post || !isset($post->post_content) || $post->post_content=='') {
        return;
    }

    $irp->Options->setPostShown($post);
    if(!$post || $irp->Options->isPostShownExcluded()) {
        $irp->Logger->error('TheContent: POST UNDEFINED OR POST EXCLUDED');
        return;
    }
    if(!$irp->Options->isActive() || !$irp->Options->isRewriteActive()
        || $irp->Options->isShortcodeUsed() || !$irp->Options->hasRelatedPostsIds()) {
        $irp->Logger->error('TheContent: NOT ACTIVE OR SHORTCODE USED OR WITHOUT RELATED POSTS');
        return;
    }

    $body=$post->post_content;
    if(strpos($body, '[irp')!==FALSE) {
        $irp->Logger->error('TheContent: SHORTCODE DETECTED');
        $irp->Options->setShortcodeUsed(TRUE);
        return;
    }

    $context=new IRP_HTMLContext();
    $body=$context->execute($body);
    $irp->Logger->pauseTime();
    $irp->Logger->info('TheContent: BODY SUCCESSULLY CREATED');
    $post->post_content=$body;
}

