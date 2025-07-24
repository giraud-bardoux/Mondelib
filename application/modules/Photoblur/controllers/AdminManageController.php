<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('photoblur_admin_main', array(), 'photoblur_admin_main_manage');
    
    // Récupérer les statistiques
    $blurTable = Engine_Api::_()->getDbtable('blurs', 'photoblur');
    $db = $blurTable->getAdapter();
    
    // Nombre total de photos floutées
    $totalBlurs = $db->select()
      ->from($blurTable->info('name'), 'COUNT(*)')
      ->query()
      ->fetchColumn();
    
    // Nombre d'utilisateurs uniques
    $uniqueUsers = $db->select()
      ->from($blurTable->info('name'), 'COUNT(DISTINCT user_id)')
      ->query()
      ->fetchColumn();
    
    // Photos floutées aujourd'hui
    $today = date('Y-m-d 00:00:00');
    $todayBlurs = $db->select()
      ->from($blurTable->info('name'), 'COUNT(*)')
      ->where('creation_date >= ?', $today)
      ->query()
      ->fetchColumn();
    
    $this->view->totalBlurs = $totalBlurs;
    $this->view->uniqueUsers = $uniqueUsers;
    $this->view->todayBlurs = $todayBlurs;
    
    // Récupérer les dernières photos floutées
    $paginator = Zend_Paginator::factory($blurTable->select()->order('creation_date DESC'));
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    
    $this->view->paginator = $paginator;
  }
}