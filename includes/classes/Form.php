<?php
/**
 * Created by PhpStorm.
 * User: alessio
 * Date: 28/03/2015
 * Time: 10:20
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class IRP_Form {
    var $prefix='Form';
    var $labels=TRUE;
    var $leftLabels=TRUE;
    var $newline;
    var $helps=FALSE;
    var $selectIdField='id';

    var $leftTags=FALSE;

    public function __construct() {
    }

    //args can be a string or an associative array if you want
    private function parseArgs($args, $defaults) {
        $result=$args;
        if(is_array($result) && count($result)>0) {
            $result='';
            foreach($args as $k=>$v) {
                $result.=' '.$k.'="'.$v.'"';
            }
        } elseif(!$args) {
            $result='';
        }
        if(is_array($defaults) && count($defaults)>0) {
            foreach($defaults as $k=>$v) {
                if(stripos($result, $k.'=')===FALSE) {
                    $result.=' '.$k.'="'.$v.'"';
                }
            }
        }
        return $result;
    }

    public function help($name) {
        global $irp;
        if(!$this->helps) return;

        $k=$this->prefix.'.'.$name.'.Tooltip';
        $label=$irp->Lang->L($k);
        ?>
        <img src="<?php echo IRP_PLUGIN_IMAGES.'question-mark.png'?>" class="irp-help" id="<?php echo $name?>-help" alt="<?php echo esc_attr($label)?>" />
    <?php
    }
    public function label($name, $args='') {
        global $irp;
        $defaults=array('class'=>'');
        $other=$this->parseArgs($args, $defaults);

        $k=$this->prefix.'.'.$name;
        $label=$irp->Lang->L($k);

        //check if is a mandatory field by checking the .txt language file
        $k=$this->prefix.'.'.$name.'.check';
        if($irp->Lang->H($k)) {
            $label.=' (*)';
        }

        $aClass='';
        ?>
        <label for="<?php echo $name?>" <?php echo $other?> >
            <span style="float:left; margin-right:5px;" class="<?php echo $aClass?>"><?php echo $label?></span>
        </label>
    <?php }

    public function leftInput($name, $args='') {
        if(!$this->labels) return;

        if($this->leftLabels) {
            $this->help($name);
            $this->label($name, $args);
        }

        if($this->newline) {
            $this->newline();
        }
    }

    public function newline() {
        ?><div class="irp-form-newline"></div><?php
    }

    public function rightInput($name, $args='') {
        if(!$this->labels) return;
        if (!$this->leftLabels) {
            $this->label($name, $args);
        }
        $this->newline();
    }

    public function formStarts($method='post', $action='', $args=NULL) {
        //$defaults=array('style'=>'margin:1em 0; padding:1px 1em; background:#fff; border:1px solid #ccc;'
        $defaults=array('class'=>'irp-form');
        $other=$this->parseArgs($args, $defaults);
        ?>
        <form method="<?php echo $method?>" action="<?php echo $action?>" <?php echo $other?> >
    <?php }

    public function formEnds() { ?>
        </form>
        <div style="clear:both;"></div>
    <?php }

    public function divStarts($args=array()) {
        $defaults=array();
        $other=$this->parseArgs($args, $defaults);
        ?>
        <div <?php echo $other?>>
    <?php }
    public function divEnds() { ?>
        </div>
        <div style="clear:both;"></div>
    <?php }

    public function p($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $irp;
        ?>
        <p style="font-weight:bold;">
            <?php
            $irp->Lang->P($message, $v1, $v2, $v3, $v4, $v5);
            if($irp->Lang->H($message.'Subtitle')) { ?>
                <br/>
                <span style="font-weight:normal;">
                    <i><?php $irp->Lang->P($message.'Subtitle', $v1, $v2, $v3, $v4, $v5)?></i>
                </span>
            <?php } ?>
        </p>
    <?php }

    public function textarea($name, $value='', $args=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('rows'=>10, 'class'=>'irp-textarea');
        $other=$this->parseArgs($args, $defaults);

        $args=array('class'=>'irp-label', 'style'=>'width:auto;');
        $this->newline=TRUE;
        $this->leftInput($name, $args);
        ?>
            <textarea dir="ltr" dirname="ltr" id="<?php echo $name ?>" name="<?php echo $name?>" <?php echo $other?> ><?php echo $value ?></textarea>
        <?php
        $this->newline=FALSE;
        $this->rightInput($name, $args);
    }

    public function text($name, $value='', $args=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('class'=>'irp-text');
        $other=$this->parseArgs($args, $defaults);

        $args=array('class'=>'irp-label');
        $this->leftInput($name, $args);
        ?>
            <input type="text" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" <?php echo $other?> />
        <?php
        $this->rightInput($name, $args);
    }

    public function hidden($name, $value='', $args=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array();
        $other=$this->parseArgs($args, $defaults);
        ?>
        <input type="hidden" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" <?php echo $other?> />
    <?php }

    public function nonce($action=-1, $name='_wpnonce', $referer=true, $echo=true) {
        wp_nonce_field($action, $name, $referer, $echo);
    }

    public function colorSelect($name, $value, $options, $multiple=FALSE, $args=NULL) {
        $array=array();
        foreach($options as $k=>$v) {
            $color=(isset($v['color']) ? $v['color'] : '');
            $fontColor=(isset($v['fontColor']) ? $v['fontColor'] : 'white');
            $style='';
            if($color!='') {
                if($style!='') {
                    $style.='; ';
                }
                $style.='background-color:'.$color;
            }
            if($fontColor!='') {
                if($style!='') {
                    $style.='; ';
                }
                $style.='color:'.$fontColor.'; font-weight:bold';
            }
            $v['id']=$k;
            $v['name']=$k;
            $v['style']=$style;
            $array[]=$v;
        }
        return $this->select($name, $value, $array, $multiple, $args);
    }
    public function select($name, $value, $options, $multiple=FALSE, $args=NULL) {
        global $irp;
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('class'=>'irp-select');
        $other=$this->parseArgs($args, $defaults);

        if(!is_array($value)) {
            $value=array($value);
        }
        if(is_string($options)) {
            $options=explode(',', $options);
        }
        if(is_array($options) && count($options)>0) {
            if(!isset($options[0][$this->selectIdField])) {
                //this is a normal array so I use the values for "id" field and the "name" into the txt file
                $temp=array();
                foreach($options as $v) {
                    $temp[]=array($this->selectIdField=>$v, 'name'=>$irp->Lang->L($this->prefix.'.'.$name.'.'.$v));
                }
                $options=$temp;
            }
        }

        $args=array('class'=>'irp-label');
        $this->leftInput($name, $args);
        ?>
            <select id="<?php echo $name ?>" name="<?php echo $name?><?php echo ($multiple ? '[]' : '')?>" <?php echo ($multiple ? 'multiple' : '')?> <?php echo $other?> >
                <?php
                foreach($options as $v) {
                    $style='';
                    if(isset($v['style'])) {
                        $style=$v['style'];
                    }

                    $selected='';
                    if(in_array($v[$this->selectIdField], $value)) {
                        $selected=' selected="selected"';
                    }
                    ?>
                    <option value="<?php echo $v[$this->selectIdField]?>" <?php echo $selected?> style="<?php echo $style?>"><?php echo $v['name']?></option>
                <?php } ?>
            </select>
        <?php
        $this->rightInput($name, $args);
    }

    public function submit($value='', $args=NULL) {
        global $irp;
        $defaults=array();
        $other=$this->parseArgs($args, $defaults);
        if($value=='') {
            $value='Send';
        }
        $this->newline();
        ?>
            <input type="submit" class="button-primary irp-button irp-submit" value="<?php $irp->Lang->P($value)?>" <?php echo $other?>/>
    <?php }

    public function delete($id, $action='delete', $args=NULL) {
        global $irp;
        $defaults=array();
        $other=$this->parseArgs($args, $defaults);
        ?>
            <input type="button" class="button irp-button" value="<?php $irp->Lang->P('Delete?')?>" onclick="if (confirm('<?php $irp->Lang->P('Are you sure you want to delete?')?>') ) window.location='<?php echo IRP_TAB_BUILDER_URI?>&action=<?php echo $action?>&id=<?php echo $id ?>&amp;irp_nonce=<?php echo esc_attr(wp_create_nonce('irp_delete')); ?>';" <?php echo $other?> />
            &nbsp;
        <?php
    }

    public function checkbox($name, $current=1, $value=1, $args=NULL) {
        global $irp;
        if(is_array($current) && isset($current[$name])) {
            $current=$current[$name];
        }
        $defaults=array('class'=>'irp-checkbox', 'style'=>'margin:0px; margin-right:4px;');
        $other=$this->parseArgs($args, $defaults);

        $args=array('class'=>'irp-label', 'style'=>'margin-top:-1px;');
        $this->leftInput($name, $args);
        ?>
            <input type="checkbox" id="<?php echo $name ?>" name="<?php echo $name?>" value="<?php echo $value?>" <?php echo($current!='' && $current==$value ? 'checked' : '') ?> <?php echo $other?> >
    <?php
        if(!$this->leftLabels) {
            $args['class']='';
        }
        $this->rightInput($name, $args);
    }

    public function checkText($nameActive, $nameText, $value) {
        global $irp;

        $args=array('class'=>'irp-hideShow irp-checkbox'
        , 'irp-hideIfTrue'=>'false'
        , 'irp-hideShow'=>$nameText.'Text');
        $this->checkbox($nameActive, $value, 1, $args);
        ?>
        <div id="<?php echo $nameText?>Text" style="float:left;">
            <?php
            $prev=$this->labels;
            $this->labels=FALSE;
            $args=array();
            $this->text($nameText, $value, $args);
            $this->labels=$prev;
            ?>
        </div>
    <?php }

    //create a checkbox with a left select visible only when the checkbox is selected
    public function checkSelect($nameActive, $nameArray, $value, $values) {
        global $irp;
        ?>
        <div id="<?php echo $nameArray?>Box" style="float:left;">
            <?php
            $args=array('class'=>'irp-hideShow irp-checkbox'
                , 'irp-hideIfTrue'=>'false'
                , 'irp-hideShow'=>$nameArray.'Tags');
            $this->checkbox($nameActive, $value, 1, $args);
            if(TRUE) { ?>
                <div id="<?php echo $nameArray?>Tags" style="float:left;">
                    <?php
                    $prev=$this->labels;
                    $this->labels=FALSE;
                    $args=array('class'=>'irp-select irpLineTags');
                    $this->select($nameArray, $value, $values, TRUE, $args);
                    $this->labels=$prev;
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php
        $this->newline();
    }
}