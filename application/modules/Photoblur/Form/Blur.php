<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_Form_Blur extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Flouter la photo')
      ->setDescription('Choisissez le niveau de flou à appliquer à la photo.')
      ->setAttrib('class', 'global_form_box');

    // Niveau de flou
    $this->addElement('Select', 'blur_level', array(
      'label' => 'Niveau de flou',
      'description' => 'Plus le niveau est élevé, plus la photo sera floutée.',
      'multiOptions' => array(
        '1' => 'Léger (1)',
        '3' => 'Moyen (3)',
        '5' => 'Fort (5)',
        '7' => 'Très fort (7)',
        '10' => 'Maximum (10)',
      ),
      'value' => '5',
      'required' => true,
    ));

    // Boutons
    $this->addElement('Button', 'submit', array(
      'label' => 'Flouter la photo',
      'type' => 'submit',
      'decorators' => array('ViewHelper'),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Annuler',
      'link' => true,
      'prependText' => ' ou ',
      'decorators' => array('ViewHelper'),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}