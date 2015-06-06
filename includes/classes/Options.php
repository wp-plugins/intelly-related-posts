<?php
/**
 * Created by PhpStorm.
 * User: alessio
 * Date: 24/03/2015
 * Time: 08:45
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class IRP_Options {
    var $vars;
    public function __construct() {
        $this->vars=array();
    }

    //always add a prefix to avoid conflicts with other plugins
    private function getKey($key) {
        return 'IRP_'.$key;
    }
    //option
    private function removeOption($key) {
        $key=$this->getKey($key);
        delete_option($key);
    }
    private function getOption($key, $default=FALSE) {
        $key=$this->getKey($key);
        $result=get_option($key, $default);
        if(is_string($result)) {
            $result=trim($result);
        }
        return $result;
    }
    private function setOption($key, $value) {
        $key=$this->getKey($key);
        if(is_bool($value)) {
            $value=($value ? 1 : 0);
        }
        update_option($key, $value);
    }

    //$_SESSION
    private function removeSession($key) {
        global $wp_session;

        $key=$this->getKey($key);
        if(isset($wp_session[$key])) {
            unset($wp_session[$key]);
        }
    }
    private function getSession($key, $default=FALSE) {
        global $wp_session;

        $key=$this->getKey($key);
        $result=$default;
        if(isset($wp_session[$key])) {
            $result=$wp_session[$key];
        }
        if(is_string($result)) {
            $result=trim($result);
        }
        return $result;
    }
    private function setSession($key, $value) {
        global $wp_session;

        $key=$this->getKey($key);
        $wp_session[$key]=$value;
    }

    //$_REQUEST
    //However WP enforces its own logic - during load process wp_magic_quotes() processes variables to emulate magic quotes setting and enforces $_REQUEST to contain combination of $_GET and $_POST, no matter what PHP configuration says.
    private function removeRequest($key) {
        $key=$this->getKey($key);
        if(isset($this->vars[$key])) {
            unset($this->vars[$key]);
        }
    }
    private function getRequest($key, $default=FALSE) {
        $key=$this->getKey($key);
        $result=$default;
        if(isset($this->vars[$key])) {
            $result=$this->vars[$key];
        }
        return $result;
    }
    private function setRequest($key, $value) {
        $key=$this->getKey($key);
        $this->vars[$key]=$value;
    }

    //TrackingEnable
    public function isTrackingEnable() {
        return $this->getOption('TrackingEnable', 0);
    }
    public function setTrackingEnable($value) {
        $this->setOption('TrackingEnable', $value);
    }
    //TrackingNotice
    public function isTrackingNotice() {
        return $this->getOption('TrackingNotice', 1);
    }
    public function setTrackingNotice($value) {
        $this->setOption('TrackingNotice', $value);
    }

    public function hasRelatedPostsIds() {
        $array=$this->getRequest('RelatedPostsIds', array());
        return (is_array($array) && count($array)>0);
    }
    public function initRelatedPostsIds($ids) {
        $this->setRequest('RelatedPostsIds', $ids);
        if($ids) {
            shuffle($ids);
        }
        $this->setRequest('ToShowPostsIds', $ids);
        $this->setRequest('ShownPostsIdsSequence', array());
    }
    //if you pass maxIds as a number this function take next [maxIds] posts to show as related
    //if you pass maxIds as an array of postsIds will return this array as posts to show as related
    public function getToShowPostsIds($maxIds, $repeat=FALSE) {
        $result=array();
        if(!$this->hasRelatedPostsIds()) {
            return $result;
        }

        $toShow=$this->getRequest('ToShowPostsIds', array());
        if(is_numeric($maxIds)) {
            if(!is_array($toShow) || (count($toShow)==0 && !$repeat)) {
                return $result;
            }
            while($maxIds>0) {
                if(count($toShow)==0) {
                    if($repeat) {
                        //i can use again the posts shown
                        $toShow=$this->getRequest('RelatedPostsIds');
                        shuffle($toShow);
                    } else {
                        break;
                    }
                }

                $postId=array_pop($toShow);
                $result[]=$postId;
                --$maxIds;
            }
        } elseif(is_array($maxIds)) {
            $toShow=array_diff($toShow, $maxIds);
            $result=$maxIds;
        }
        $this->setRequest('ToShowPostsIds', $toShow);

        //update the sequence shown
        $array=$this->getRequest('ShownPostsIdsSequence', array());
        $array[]=$result;
        $this->setRequest('ShownPostsIdsSequence', $array);

        return $result;
    }
    public function getShownPostsIdsSequence() {
        return $this->getRequest('ShownPostsIdsSequence', array());
    }

    public function getPostShown() {
        return $this->getRequest('PostShown', NULL);
    }
    public function setPostShown($value) {
        $this->setRequest('PostShown', $value);
    }
    public function isPostShownExcluded() {
        global $irp;
        $array=$this->getExcludedPostsIds();
        $post=$this->getPostShown();

        $result=FALSE;
        if(!$post || !isset($post->ID)) {
            $result=TRUE;
        } elseif(in_array($post->ID, $array)) {
            $irp->Logger->info('POST ID=%s IN RELATED POSTS EXCLUDE LIST', $post->ID);
            $result=TRUE;
        } else {
            $result=FALSE;
        }
        return $result;
    }
    public function isShortcodeUsed() {
        return $this->getRequest('ShortcodeUsed', 0);
    }
    public function setShortcodeUsed($value) {
        $this->setRequest('ShortcodeUsed', $value);
    }

    //is related posts active?
    public function isActive() {
        return $this->getOption('Active', 0);
    }
    public function setActive($value) {
        $this->setOption('Active', $value);
    }
    public function isShowPoweredBy() {
        return $this->getOption('ShowPoweredBy', TRUE);
    }
    public function setShowPoweredBy($value) {
        $this->setOption('ShowPoweredby', $value);
    }
    public function getLinkRel() {
        return $this->getOption('LinkRel', 'nofollow');
    }
    public function setLinkRel($value) {
        $this->setOption('LinkRel', $value);
    }
    //is related posts active in posts without any [irl] shortcodes defined
    public function isRewriteActive() {
        return $this->getOption('RewriteActive', 1);
    }
    public function setRewriteActive($value) {
        $this->setOption('RewriteActive', $value);
    }
    public function getExcludedPostsIds() {
        return $this->getOption('ExcludedPostsIds', array());
    }
    public function setExcludedPostsIds($value) {
        $value=array_unique($value);
        $this->setOption('ExcludedPostsIds', $value);
    }
    public function getMetaboxPostTypes($create=TRUE) {
        global $irp;
        $result=$this->getOption('MetaboxPostTypes', array());
        if($create) {
            $types=$irp->Utils->query(IRP_QUERY_POST_TYPES);
            foreach($types as $v) {
                $v=$v['id'];
                if(!isset($result[$v]))  {
                    $result[$v]=($v=='post' ? 1 : 0);
                }
            }
        }
        return $result;
    }
    public function setMetaboxPostTypes($values) {
        $this->setOption('MetaboxPostTypes', $values);
    }
    //is integrated with which post types?
    public function getRewritePostTypes($create=TRUE) {
        global $irp;
        $result=$this->getOption('RewritePostTypes', array());
        if($create) {
            $types=$irp->Utils->query(IRP_QUERY_POST_TYPES);
            foreach($types as $v) {
                $v=$v['id'];
                if(!isset($result[$v]))  {
                    $result[$v]=($v=='post' ? 1 : 0);
                }
            }
        }
        return $result;
    }
    public function setRewritePostTypes($values) {
        $this->setOption('RewritePostTypes', $values);
    }
    //how many related posts boxes we have to include?
    public function getRewriteBoxesCount() {
        return $this->getOption('RewriteBoxesCount', 3);
    }
    public function setRewriteBoxesCount($value) {
        $this->setOption('RewriteBoxesCount', $value);
    }
    //how many related posts we see in each box?
    public function getRewritePostsInBoxCount() {
        //return $this->getOption('RewritePostsInBoxCount', 1);
        return 1;
    }
    public function setRewritePostsInBoxCount($value) {
        $this->setOption('RewritePostsInBoxCount', $value);
    }
    //how many words we have to "wait" before inserting a related box
    public function getRewriteThreshold() {
        return $this->getOption('RewriteThreshold', 250);
    }
    public function setRewriteThreshold($value) {
        $this->setOption('RewriteThreshold', $value);
    }
    //include also a related box in the end?
    public function isRewriteAtEnd() {
        return $this->getOption('RewriteAtEnd', TRUE);
    }
    public function setRewriteAtEnd($value) {
        $this->setOption('RewriteAtEnd', $value);
    }
    //how many boxes are already been written?
    public function getRewriteBoxesWritten() {
        return $this->getRequest('RewriteBoxesWritten', 0);
    }
    public function setRewriteBoxesWritten($value) {
        $this->setRequest('RewriteBoxesWritten', $value);
    }

    /*
    public function isEngineWithAuthors() {
        return $this->getOption('EngineWithAuthors', 1);
    }
    public function setEngineWithAuthors($value) {
        $this->setOption('EngineWithAuthors', $value);
    }
    public function isEngineWithTags() {
        return $this->getOption('EngineWithTags', 1);
    }
    public function setEngineWithTags($value) {
        $this->setOption('EngineWithTags', $value);
    }
    public function isEngineWithCategories() {
        return $this->getOption('EngineWithCategories', 1);
    }
    public function setEngineWithCategories($value) {
        $this->setOption('EngineWithCategories', $value);
    }
    */
    public function getEngineSearch() {
        return $this->getOption('EngineSearch', IRP_ENGINE_SEARCH_CATEGORIES_TAGS);
    }
    public function setEngineSearch($value) {
        $this->setOption('EngineSearch', $value);
    }

    public function getRelatedText() {
        return $this->getOption('RelatedText', 'Related:');
    }
    public function setRelatedText($value) {
        $this->setOption('RelatedText', $value);
    }

    /*
    public function getBackgroundColor() {
        return $this->getOption('BackgroundColor', '');
    }
    public function setBackgroundColor($value) {
        $this->setOption('BackgroundColor', $value);
    }
    public function getBorderColor() {
        return $this->getOption('BorderColor', '');
    }
    public function setBorderColor($value) {
        $this->setOption('BorderColor', $value);
    }
    public function getTemplateBackground() {
        return $this->getOption('TemplateBackground', '');
    }
    public function setTemplateBackground($value) {
        $this->setOption('TemplateBackground', $value);
    }
    */

    public function getTrackingLastSend() {
        return $this->getOption('TrackingLastSend['.IRP_PLUGIN_NAME.']', 0);
    }
    public function setTrackingLastSend($value) {
        $this->setOption('TrackingLastSend['.IRP_PLUGIN_NAME.']', $value);
    }
    public function getPluginInstallDate() {
        return $this->getOption('PluginInstallDate['.IRP_PLUGIN_NAME.']', 0);
    }
    public function setPluginInstallDate($value) {
        $this->setOption('PluginInstallDate['.IRP_PLUGIN_NAME.']', $value);
    }
    public function getPluginUpdateDate() {
        return $this->getOption('PluginUpdateDate['.IRP_PLUGIN_NAME.']', 0);
    }
    public function setPluginUpdateDate($value) {
        $this->setOption('PluginUpdateDate['.IRP_PLUGIN_NAME.']', $value);
    }

    //template style
    public function getTemplateRelatedTextColor() {
        $result=$this->getOption('TemplateRelatedTextColor', '');
        return $result;
    }
    public function setTemplateRelatedTextColor($name) {
        return $this->setOption('TemplateRelatedTextColor', $name);
    }
    public function getTemplateBackgroundColor() {
        $result=$this->getOption('TemplateBackgroundColor', '');
        return $result;
    }
    public function setTemplateBackgroundColor($name) {
        return $this->setOption('TemplateBackgroundColor', $name);
    }
    public function getTemplateBorderColor() {
        $result=$this->getOption('TemplateBorderColor', '');
        return $result;
    }
    public function setTemplateBorderColor($name) {
        return $this->setOption('TemplateBorderColor', $name);
    }
    public function isTemplateShadow() {
        return $this->getOption('TemplateShadow', 1);
    }
    public function setTemplateShadow($name) {
        return $this->setOption('TemplateShadow', $name);
    }

    public function getStyleRelatedTextColors() {
        $array=$this->getOption('StyleRelatedTextColors', array());
        ksort($array);
        return $array;
    }
    public function setStyleRelatedTextColors($value) {
        $this->setOption('StyleRelatedTextColors', $value);
    }
    public function getStyleBackgroundColors() {
        $array=$this->getOption('StyleBackgroundColors', array());
        ksort($array);
        return $array;
    }
    public function setStyleBackgroundColors($value) {
        $this->setOption('StyleBackgroundColors', $value);
    }
    public function getStyleLightBorderColors() {
        $array=$this->getOption('StyleLightBorderColors', array());
        ksort($array);
        return $array;
    }
    public function setStyleLightBorderColors($value) {
        $this->setOption('StyleLightBorderColors', $value);
    }
    public function getStyleDarkBorderColors() {
        $array=$this->getOption('StyleDarkBorderColors', array());
        ksort($array);
        return $array;
    }
    public function setStyleDarkBorderColors($value) {
        $this->setOption('StyleDarkBorderColors', $value);
    }
    public function getStyleShadow() {
        return $this->getOption('StyleShadow', '');
    }
    public function setStyleShadow($value) {
        $this->setOption('StyleShadow', $value);
    }

    public function isPluginFirstInstall() {
        return $this->getOption('PluginFirstInstall', FALSE);
    }
    public function setPluginFirstInstall($value) {
        $this->setOption('PluginFirstInstall', $value);
    }
    public function isShowActivationNotice() {
        return $this->getOption('ShowActivationNotice', FALSE);
    }
    public function setShowActivationNotice($value) {
        $this->setOption('ShowActivationNotice', $value);
    }

    //LoggerEnable
    public function isLoggerEnable() {
        return ($this->getOption('LoggerEnable', FALSE) || (defined('IRP_LOGGER') && IRP_LOGGER));
    }
    public function setLoggerEnable($value) {
        $this->setOption('LoggerEnable', $value);
    }

    public function getMaxExecutionTime(){
        return $this->getOption('MaxExecutionTime', -1);
    }
    public function resetMaxExecutionTime(){
        $this->setOption('MaxExecutionTime', -1);
    }
    public function updateMaxExecutionTime($value){
        $now=$this->getMaxExecutionTime();
        if($value>$now) {
            $this->setOption('MaxExecutionTime', $value);
        }
    }

    //Cache
    public function getCache($name, $id) {
        return $this->getRequest('Cache_'.$name.'_'.$id);
    }
    public function setCache($name, $id, $value) {
        $this->setRequest('Cache_'.$name.'_'.$id, $value);
    }

    public function getFeedbackEmail() {
        return $this->getOption('FeedbackEmail', get_bloginfo('admin_email'));
    }
    public function setFeedbackEmail($value) {
        $this->setOption('FeedbackEmail', $value);
    }

    public function useCssTemplate($name) {
        $array=$this->getUsedCssTemplates();
        if(isset($array[$name])) {
            $class=$array[$name];
        } else {
            $class='b'.md5($name.'-'.time());
            $array[$name]=$class;
            $this->setRequest('UsedCssTemplates', $array);
        }
        return $class;
    }
    public function getUsedCssTemplates() {
        return $this->getRequest('UsedCssTemplates', array());
    }

    private function hasGenericMessages($type) {
        $result=$this->getRequest($type.'Messages', NULL);
        return (is_array($result) && count($result)>0);
    }

    private function pushGenericMessage($type, $message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $irp;
        $array=$this->getRequest($type.'Messages', array());
        $array[]=$irp->Lang->L($message, $v1, $v2, $v3, $v4, $v5);
        $this->setRequest($type.'Messages', $array);
    }
    private function writeGenericMessages($type, $clean=TRUE) {
        $result=FALSE;
        $array=$this->getRequest($type.'Messages', array());
        if(is_array($array) && count($array)>0) {
            $result=TRUE;
            ?>
            <div class="irp-box-<?php echo strtolower($type)?>"><?php echo wpautop(implode("\n", $array)); ?></div>
        <?php }
        if($clean) {
            $this->removeRequest($type.'Messages');
        }
        return $result;
    }
    //WarningMessages
    public function hasWarningMessages() {
        return $this->hasGenericMessages('Warning');
    }
    public function pushWarningMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Warning', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeWarningMessages($clean=TRUE) {
        return $this->writeGenericMessages('Warning', $clean);
    }
    //SuccessMessages
    public function hasSuccessMessages() {
        return $this->hasGenericMessages('Success');
    }
    public function pushSuccessMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Success', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeSuccessMessages($clean=TRUE) {
        return $this->writeGenericMessages('Success', $clean);
    }
    //InfoMessages
    public function hasInfoMessages() {
        return $this->hasGenericMessages('Info');
    }
    public function pushInfoMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Info', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeInfoMessages($clean=TRUE) {
        return $this->writeGenericMessages('Info', $clean);
    }
    //ErrorMessages
    public function hasErrorMessages() {
        return $this->hasGenericMessages('Error');
    }
    public function pushErrorMessage($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        return $this->pushGenericMessage('Error', $message, $v1, $v2, $v3, $v4, $v5);
    }
    public function writeErrorMessages($clean=TRUE) {
        return $this->writeGenericMessages('Error', $clean);
    }

    public function writeMessages($clean=TRUE) {
        $result=FALSE;
        if($this->writeInfoMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeSuccessMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeWarningMessages($clean)) {
            $result=TRUE;
        }
        if($this->writeErrorMessages($clean)) {
            $result=TRUE;
        }

        return $result;
    }
    public function pushMessage($success, $message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        if($success) {
            $this->pushSuccessMessage($message.'Success', $v1, $v2, $v3, $v4, $v5);
        } else {
            $this->pushErrorMessage($message.'Error', $v1, $v2, $v3, $v4, $v5);
        }
    }
}