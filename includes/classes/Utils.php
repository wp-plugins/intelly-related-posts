<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class IRP_Utils {

    function format($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        if($v1 || $v2 || $v3 || $v4 || $v5) {
            $message=sprintf($message, $v1, $v2, $v3, $v4, $v5);
        }
        return $message;
    }
    function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    function endsWith($haystack, $needle) {
        $length = strlen($needle);
        $start = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }
    function substr($text, $start=0, $end=-1) {
        if($end<0) {
            $end=strlen($text);
        }
        $length=$end-$start;
        return substr($text, $start, $length);
    }

    //WOW! $end is passed as reference due to we can change it if we found \n character after
    //substring to avoid having these characters after or before
    function substrln($text, $start=0, &$end=-1) {
        if($end<0) {
            $end=strlen($text);
        }

        do {
            $loop=FALSE;
            $c=substr($text, $end, 1);
            if($c=="\n" || $c=="\r" || $c==".") {
                $end += 1;
                $loop=TRUE;
            }
        } while($loop);

        $length=$end-$start;
        return substr($text, $start, $length);
    }


    function toCommaArray($array, $isNumeric=TRUE, $isTrim=TRUE) {
        if(is_string($array)) {
            if(trim($array)=='') {
                $array=array();
            } else {
                $array=explode(',', $array);
            }
        }
        if(!is_array($array)) {
            $array=array();
        }
        for($i=0; $i<count($array); $i++) {
            if($isTrim) {
                $array[$i]=trim($array[$i]);
            }
            if($isNumeric) {
                $array[$i]=floatval($array[$i]);
            }
        }
        return $array;
    }
    //verifica se il parametro needle è un elemento dell'array haystack
    //se il parametro needle è a sua volta un array verifica che almeno un elemento
    //sia contenuto all'interno dell'array haystack
    function inArray($needle, $haystack) {
        if (is_string($haystack)) {
            //from string to numeric array
            $temp = explode(',', $haystack);
            $haystack = array();
            foreach ($temp as $v) {
                $v = trim($v);
                $v = intval($v);
                if ($v > 0) {
                    $haystack[] = $v;
                }
            }
        }

        $result = FALSE;
        foreach ($haystack as $v) {
            $v = intval($v);
            //if one element of the array have -1 value means i select "all" option
            if ($v < 0) {
                $result = TRUE;
                break;
            }
        }

        if ($result) {
            return TRUE;
        }

        $result = FALSE;
        if (is_array($needle)) {
            foreach ($needle as $v) {
                $v = trim($v);
                $v = intval($v);
                if (in_array($v, $haystack)) {
                    $result = TRUE;
                    break;
                }
            }
        } else {
            //built-in comparison
            $result = in_array($needle, $haystack);
        }
        return $result;
    }

    function is($name, $compare, $default='', $ignoreCase=TRUE) {
        $what=$this->qs($name, $default);
        $result=FALSE;
        if(is_string($compare)) {
            $compare=explode(',', $compare);
        }
        if($ignoreCase){
            $what=strtolower($what);
        }

        foreach($compare as $v) {
            if($ignoreCase){
                $v=strtolower($v);
            }
            if($what==$v) {
                $result=TRUE;
                break;
            }
        }
        return $result;
    }

    public function twitter($name) {
        ?>
        <a href="https://twitter.com/<?php echo $name?>" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @<?php echo $name?></a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    <?php
    }

    function iqs($name, $default = 0) {
        return intval($this->qs($name, $default));
    }
    //per ottenere un campo dal $_GET oppure dal $_POST
    function qs($name, $default = '') {
        $result = $default;
        if (isset($_GET[$name])) {
            $result = $_GET[$name];
        } elseif (isset($_POST[$name])) {
            $result = $_POST[$name];
        }

        if (is_string($result)) {
            $result = urldecode($result);
            $result = trim($result);
        }

        return $result;
    }

    function query($query, $args = NULL) {
        global $irp;

        $defaults = array('post_type' => '', 'all' => FALSE, 'select' => FALSE);
        $args = wp_parse_args($args, $defaults);

        $result = $irp->Options->getCache('Query', $query . '_' . $args['post_type']);
        if (!is_array($result) || count($result) == 0) {
            $q = NULL;
            $id = 'ID';
            $name = 'post_title';
            $function='';
            switch ($query) {
                case IRP_QUERY_POSTS_OF_TYPE:
                    $options = array('posts_per_page' => -1, 'post_type' => $args['post_type']);
                    $q = get_posts($options);
                    $function='get_permalink';
                    break;
                case IRP_QUERY_CATEGORIES:
                    $options = array('posts_per_page' => -1);
                    $q = get_categories($options);
                    $id = 'cat_ID';
                    $name = 'cat_name';
                    $function='get_category_link';
                    break;
                case IRP_QUERY_TAGS:
                    $q = get_tags();
                    $id = 'term_id';
                    $name = 'name';
                    $function='get_tag_link';
                    break;
            }

            $result = array();
            if ($q) {
                foreach ($q as $v) {
                    $result[] = array('id' => $v->$id, 'name' => $v->$name);
                }
            } elseif ($query == IRP_QUERY_POST_TYPES) {
                //$options = array('public' => TRUE, '_builtin' => FALSE);
                //$q = get_post_types($options, 'names');
                $q=array();
                $q = array_merge($q, array('post', 'page'));
                sort($q);
                foreach ($q as $v) {
                    $result[] = array('id' => $v, 'name' => $v);
                }
            }

            if($function!='' && function_exists($function)) {
                for($i=0; $i<count($result); $i++) {
                    $v=$result[$i];
                    $v['url']=call_user_func_array($function, array($v['id']));
                    $result[$i]=$v;
                }
            }
            $irp->Options->setCache('Query', $query . '_' . $args['post_type'], $result);
        }

        if ($args['all']) {
            $first = array();
            $first[] = array('id' => -1, 'name' => '[' . $irp->Lang->L('All') . ']', 'url'=>'');
            $result = array_merge($first, $result);
        }
        if ($args['select']) {
            $first = array();
            $first[] = array('id' => 0, 'name' => '[' . $irp->Lang->L('Select') . ']', 'url'=>'');
            $result = array_merge($first, $result);
        }

        return $result;
    }

    //send remote request to our server to store tracking and feedback
    function remotePost($action, $data = '') {
        global $irp;

        $data['secret'] = 'WYSIWYG';
        $response = wp_remote_post(IRP_INTELLYWP_RECEIVER . '?iwpm_action=' . $action, array(
            'method' => 'POST'
            , 'timeout' => 20
            , 'redirection' => 5
            , 'httpversion' => '1.1'
            , 'blocking' => TRUE
            , 'body' => $data
            , 'user-agent' => 'IRP/' . IRP_PLUGIN_VERSION . '; ' . get_bloginfo('url')
        ));
        $data = json_decode(wp_remote_retrieve_body($response), TRUE);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200
            || !isset($data['success']) || !$data['success']
        ) {
            $irp->Logger->error('ERRORS SENDING REMOTE-POST ACTION=%s DUE TO REASON=%s', $action, $response);
            $data = FALSE;
        } else {
            $irp->Logger->debug('SUCCESSFULLY SENT REMOTE-POST ACTION=%s RESPONSE=%s', $action, $data);
        }
        return $data;
    }

    //wp_parse_args with null correction
    function parseArgs($args, $defaults) {
        if (is_null($args) || !is_array($args)) {
            $args = array();
        }
        foreach ($args as $k => $v) {
            if (is_null($args[$k])) {
                //so can take the default value
                unset($args[$k]);
            } elseif (is_string($args[$k]) && $args[$k] == '' && isset($defaults[$k]) && is_array($defaults[$k])) {
                //a very strange case, i have a blank string for rappresenting an empty array
                unset($args[$k]);
            }
        }
        $result = wp_parse_args($args, $defaults);
        return $result;
    }

    function redirect($location) {
        //seems that if you have installed xdebug (or some version of it) doesnt work so js added
        wp_redirect($location);
        ?>
        <script> window.location.replace('<?php echo $location?>'); </script>
    <?php }

    //return the element inside array with the specified key
    function getArrayValue($key, $array, $value='') {
        $result=FALSE;
        if (isset($array[$key])) {
            $result=$array[$key];
            $result['name']=$key;
        }
        if($result!==FALSE && $value!='') {
            if(isset($result[$value])) {
                $result=$result[$value];
            }
        }
        return $result;
    }

    var $_sortField;
    var $_ignoreCase;
    function aksort(&$array, $sortField='name', $ignoreCase=TRUE) {
        $this->_sortField=$sortField;
        $this->_ignoreCase=$ignoreCase;
        usort($array, array($this, "aksortCompare"));
    }
    //not thread-safe!
    private function aksortCompare($a, $b) {
        if ($a===$b || $a==$b) {
            return 0;
        }

        $result=0;
        $a=$a[$this->_sortField];
        $b=$b[$this->_sortField];
        if(is_numeric($a) && is_numeric($b)) {
            $result=($a < $b) ? -1 : 1;
        } else {
            $a.='';
            $b.='';
            if($this->_ignoreCase) {
                $result=strcasecmp($a, $b);
            } else {
                $result=strcmp($a.'', $b);
            }
        }
        return $result;
    }

    function printScriptCss() {
        global $irp;
        $uri=get_bloginfo('wpurl');
        $irp->Tabs->enqueueScripts();
        //wp_enqueue_style('buttons', $uri.'/wp-includes/css/buttons.min.css');
        //wp_enqueue_style('editor', $uri.'/wp-includes/css/editor.min.css');
        //wp_enqueue_style('jquery-ui-dialog', $uri.'/wp-includes/css/jquery-ui-dialog.min.css');
        $styles='dashicons,admin-bar,buttons,media-views,wp-admin,wp-auth-check,wp-color-picker';
        $styles=explode(',', $styles);
        foreach($styles as $v) {
            wp_enqueue_style($v);
        }

        remove_all_actions('wp_print_scripts');
        print_head_scripts();
        print_admin_styles();
    }
}
