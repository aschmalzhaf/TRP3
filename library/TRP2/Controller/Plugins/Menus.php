<?php


class Controller_Plugin_Menus extends Zend_Controller_Plugin_Abstract
{

    protected $_acl = null;
	protected $_MenuEntries = array(array('Basics',			array('Start',						'/Basic/index'),
															array('Eigene Daten',				'/Basic/changeaccount'),
															array('Passwort ändern',			'/Basic/changepassword'),
															array('Wunschliste',				'/Basic/wishlist'),),
									array('Mitarbeiter',	array('MA verwalten (TNT)',			'/Captteam/index'),
															array('MA anzeigen/ändern',			'/Staff/viewlist')),
									array('Veranstaltungen',array('Veranst. anzeigen/ändern',	'/Eventshift/index')),
									array('Berichte',	    array('Jobübersichten', 			'/Eventshift/walklist'),
															array('Veranstaltungs-Übersicht',	'/Eventshift/overview'),
															array('Verantwortlichkeits Liste',	'/Eventshift/responsiblelist'),
															array('Komplett-Übersicht html',	'/Eventshift/generateshifttable'),
															array('Komplett-Übersicht Excel',	'/Eventshift/exportshifttable')),
									array('Teilnehmer',		array('Angemeldete (alle Daten)',	'/Participants/viewlist'),
															array('Angemeldete (Übersicht)',	'/Participants/viewlist1'),
															array('Eintragen',					'/Participants/index'),
															array('Statistik',	'/Participants/statistik')),		
									array('Einstellungen',  array('Wünsche einrichten',        '/Wishoptions/index'))
															);
    
    

//---------------------------------------------------------------------------------------
									
	public function __construct(Zend_Acl $acl)
    {
        $this->_acl = $acl;
    }
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
//@TODO Überlegen, ob man diese Überprüfung vermeiden kann!
    	$session = new Zend_Session_Namespace();
		if($session->user != NULL){
		
			//set userdata-placeholder for topbar of view
    		$phname = new Zend_View_Helper_Placeholder();
			$phname->placeholder('user')->exchangeArray($session->user);
			
			//logged in - autorization?
			$model = new Model_Acl();
			$role = $model->getRoleByID($session->user['role']);
			
			//resources are menu entries...
			$menu_sec_entries = "";
			$menu = "<dl class=\"menu\">";
			foreach($this->_MenuEntries as $MenuEntry){
				$menu_section = "<dt class=\"menusection\">". $MenuEntry[0] . "</dt>";
				unset($MenuEntry[0]);
				$menu_sec_entries = "";
				foreach($MenuEntry as $MenuItem){
					$resource = substr($MenuItem[1],1,stripos($MenuItem[1],"/",1)-1);
					
					
					if ($this->_acl->isAllowed($role, $resource, 'view')) {
						
						$menu_sec_entries .= "<dd class=\"menusectionentry\"><a href=\"" . $MenuItem[1] . "\">" . $MenuItem[0] . "</a></dd>";
					}
					
				}
						
				if($menu_sec_entries != ""){
					$menu .= $menu_section . $menu_sec_entries;
					unset($menu_section);
					unset($menu_sec_entries);
				}
			}
			$menu .= "</dl>";
			$phname->placeholder('mainmenu')->set($menu);

			
		}
    	
     	
    		
    }
}
