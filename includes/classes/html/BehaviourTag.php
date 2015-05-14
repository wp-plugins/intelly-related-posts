<?php
if (!defined('ABSPATH')) exit;

class IRP_BehaviourTag extends IRP_HTMLTag {
    var $allowBoxBefore;
    var $allowBoxAfter;

    var $ensureUncuttable;

    var $ensureWithoutPreviousBox;
    var $ensureWithoutNextBox;

    public function __construct() {
        parent::__construct();
        $this->allowBoxBefore=FALSE;
        $this->allowBoxAfter=FALSE;
        $this->ensureUncuttable=FALSE;
        $this->ensureWithoutPreviousBox=FALSE;
        $this->ensureWithoutNextBox=FALSE;
    }

    public function write(IRP_HTMLContext $context) {
        if($this->ensureWithoutPreviousBox) {
            //if present remove the last related box
            $args=array('last'=>TRUE);
            $context->popRelatedBox($args);
        }
        if($this->allowBoxBefore) {
            $context->writeRelatedBoxIfNeeded();
        }

        if($this->ensureUncuttable) {
            $previous=$context->isUncuttable();
            $context->setUncuttable(TRUE);
        }
        parent::write($context);
        if($this->ensureUncuttable) {
            $context->setUncuttable($previous);
        }

        if($this->allowBoxAfter) {
            $context->writeRelatedBoxIfNeeded();
        }
        if($this->ensureWithoutNextBox) {
            $context->setWithoutNextBox();
        }
    }
}
