<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: SesbasicFieldValueLoop.php 2016-05-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Fields_View_Helper_CategoryFieldValueLoop extends Fields_View_Helper_FieldAbstract
{

  public function categoryFieldValueLoop($subject, $contentShow = true, $labelBold = true)
  {

    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
    $partialStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($subject);
    if (empty($partialStructure))
      return '';

    if (!($subject instanceof Core_Model_Item_Abstract) || !$subject->getIdentity())
      return '';

    $notice = '';
    $viewer = Engine_Api::_()->user()->getViewer();
    $usePrivacy = ($subject instanceof User_Model_User);
    if ($usePrivacy) {
      $relationship = 'everyone';
      if ($viewer && $viewer->getIdentity()) {
        if ($viewer->getIdentity() == $subject->getIdentity())
          $relationship = 'self';
        else if ($viewer->membership()->isMember($subject, true))
          $relationship = 'friends';
        else
          $relationship = 'registered';
      }
    }

    // Generate
    $content = '';
    $lastContents = '';
    $lastHeadingTitle = null;

    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $action = $front->getRequest()->getActionName();
    $controller = $front->getRequest()->getControllerName();

    $show_hidden = true;

    $flag = 0;
    foreach ($partialStructure as $map) {

      // Get field meta object
      $field = $map->getChild();
      $value = $field->getValue($subject);
      if (!$field || $field->type == 'profile_type')
        continue;
      if (!$field->display && !$show_hidden)
        continue;
      $isHidden = !$field->display;

      // Get first value object for reference
      $firstValue = $value;
      if (is_array($value) && !empty($value)) {
        $firstValue = $value[0];
      }

      // Evaluate privacy
      if ($usePrivacy && !empty($firstValue->privacy) && $relationship != 'self') {
        if ($firstValue->privacy == 'self' && $relationship != 'self') {
          $isHidden = true; //continue;
        } else if ($firstValue->privacy == 'friends' && ($relationship != 'friends' && $relationship != 'self')) {
          $isHidden = true; //continue;
        } else if ($firstValue->privacy == 'registered' && $relationship == 'everyone') {
          $isHidden = true; //continue;
        }
      }

      // Render
      if ($field->type == 'heading') {
        // Heading
        if (!empty($lastContents)) {
          $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
          $lastContents = '';
        }
        if ($contentShow) {
          $lastHeadingTitle = $this->view->translate($field->label);
        }
      } else {
        // Normal fields
        $tmp = $this->getFieldValueString($field, $value, $subject, $map, $partialStructure);
        if (!empty($firstValue->value) && !empty($tmp)) {

          if (!$isHidden || $show_hidden) {
            $icon = "";
            if (isset($field->icon) && $field->icon)
              $icon = '<i class="' . $field->icon . '"></i>';
            $label = $icon . $this->view->translate($field->label);
            if ($labelBold) {
              $lastContents .= <<<EOF
  <span class='sesbasic_list_customfield' data-field-id={$field->field_id}>{$notice}<span class='sesbasic_list_customfield_lable'><b>{$label}</b>: </span><span class='sesbasic_list_customfield_value '>{$tmp}</span></span>
EOF;
            } else {
              $lastContents .= <<<EOF
  <span class='sesbasic_list_customfield' data-field-id={$field->field_id}>{$notice}<span class='sesbasic_list_customfield_lable'>{$label}: </span><span class='sesbasic_list_customfield_value'>{$tmp}</span></span>
EOF;
            }
            $flag++;
          }
        }
      }
    }

    if (!empty($lastContents)) {
      $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
    }

    return $content;
  }

  public function getFieldValueString($field, $value, $subject, $map = null, $partialStructure = null)
  {
    if ((!is_object($value) || !isset($value->value)) && !is_array($value)) {
      return null;
    }

    $helperName = Engine_Api::_()->fields()->getFieldInfo($field->type, 'helper');
    if (!$helperName) {
      return null;
    }

    $helper = $this->view->getHelper($helperName);
    if (!$helper) {
      return null;
    }

    $helper->structure = $partialStructure;
    $helper->map = $map;
    $helper->field = $field;
    $helper->subject = $subject;
    $tmp = $helper->$helperName($subject, $field, $value);
    unset($helper->structure);
    unset($helper->map);
    unset($helper->field);
    unset($helper->subject);

    return $tmp;
  }

  protected function _buildLastContents($content, $title)
  {
    if (!$title) {
      return $content;
    }
    return <<<EOF
          <h4><span class='sesbasic_list_customfield_title'>{$title}: </span></h4>{$content}
EOF;
  }

}
