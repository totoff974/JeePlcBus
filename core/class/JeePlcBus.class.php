<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../core/php/JeePlcBus.inc.php';
require_once dirname(__FILE__) . '/../../core/config/JeePlcBus.config.php';
include_file('core', 'JeePlcBus', 'config', 'JeePlcBus');

class JeePlcBus extends eqLogic {
    /*     * *************************Attributs****************************** */

	
    /*     * ***********************Methode static*************************** */
	
	
	public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = '/tmp/JeePlcBus_dep';
		if (exec('sudo dpkg --get-selections | grep -E "libdevice-serialport-perl" | grep -v desinstall | wc -l') >= 1) {
			$return['state'] = 'ok';
		} else {
			$return['state'] = 'nok';
		}
		return $return;
	}
	public static function dependancy_install() {
		log::remove('JeePlcBus_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= '&& ';
		$cmd .= 'sudo cp ' . dirname(__FILE__) . '/../../ressources/IOSelectBuffered /etc/perl/SerialLibs/IOSelectBuffered.pm';
		$cmd .= ' >> ' . log::getPathToLog('JeePlcBus_dependancy') . ' 2>&1 &';
		exec($cmd);
	}
	
	public static function deamon_info() {

	}

	public static function deamon_start() {

	}

	public static function deamon_stop() {

	}
	
    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */
	
    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {
       
    }

    public function postSave() {
		if (!$this->getId())
          return;
    }

    public function preUpdate() {
		if ($this->getConfiguration('device_code') == '') {
            throw new Exception(__('Merci de renseigner le Device code.',__FILE__));	
        }
		$this->autoAjoutCommande($this->getConfiguration('device_code'), $this->getConfiguration('dimmable'));		
    }

    public function postUpdate() {

    }

    public function preRemove() {
		 
    }

    public function postRemove() {
        
    }
	
    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */
	
	public function ActionCommande($d_code, $d_cmd, $data1, $data2, $retour_etat, $test_com) {

		
		// chemin du dossier de la passerelle
		$plcbus_path = realpath(dirname(__FILE__) . '/../../ressources/JeePlcBus');
		
		// port du plcbus /dev/tty...
		$port = config::byKey('port', 'JeePlcBus');
		if ($port != 'auto') {
			$port = jeedom::getUsbMapping($port);
		}
		
		// recuperation des parametres plcbus
		if (config::byKey('plcbus_usercode', 'JeePlcBus') != '') {
			$plcbus_usercode = config::byKey('plcbus_usercode', 'JeePlcBus');
		}
		else {
			$plcbus_usercode = 'FF';
		}		
		if (config::byKey('phase', 'JeePlcBus') != '') {
			$phase = config::byKey('phase', 'JeePlcBus');
		}
		else {
			$phase = '1';
		}
		
		// construction de la commande
		$cmd_exe = 'sudo /usr/bin/perl ' . $plcbus_path . '/srv_plc';
		$cmd_exe .= ' --device=' . $port;
		$cmd_exe .= ' --user=' . $plcbus_usercode;
		$cmd_exe .= ' --phase=' . $phase;		
	    
		if ($d_code !='') {
			$cmd_exe .= ' --d_code=' . $d_code;
		}
		if ($d_cmd !='') {
			$cmd_exe .= ' --d_cmd=' . $d_cmd;
		}
		if ($data1 !='') {
			$cmd_exe .= ' --d_data1=' . $data1;
		}
		if ($data2 !='') {
			$cmd_exe .= ' --d_data2=' . $data2;
		}
		
		// execution de la commande
		$retour_action = shell_exec($cmd_exe);

		// traitement de la reponse si demande
		if ($retour_etat == true) {
			$tab_retour = explode("::", $retour_action);
			
			if ($test_com == true) {
				return $tab_retour[0];
			}
			
			if ($test_com == false) {
				// si pas d'erreur de reponse du module
				if ($tab_retour[0] != 'ERREUR pas de reponse' OR $d_cmd != 'STATUS_REQUEST') {
					$act_ret = explode(",", $tab_retour[0]);

					// vérification que l'ordre envoyé et le 1er retour d'état est identique pour mettre à jour l'état de l'équipement
					if (($act_ret[0] == $d_code AND $act_ret[1] == $d_cmd AND $act_ret[2] == $data1 AND $act_ret[3] == $data2) OR $d_cmd == 'STATUS_REQUEST') {
						
						// si vrai alors on met à jour l'équipement avec le retour d'état
						foreach (eqLogic::getCmd() as $info) {
							$info->setValue($act_ret[2]);
							$info->save();
							$info->event($act_ret[2]);
							
						}
						log::add('JeePlcBus', 'debug', 'Retour état réel : ' . $act_ret[0] . ',' . $act_ret[1] . ',' . $act_ret[2] . ',' . $act_ret[3]);
					}
					
					// Sinon on essaye une interrogation forcée du module
					else {
						log::add('JeePlcBus', 'debug', 'MAJ forcée retour état réel pour ' . $d_code);
						self::Requete_MaJ($d_code, false);
					}
					return $tab_retour[0];
				}
				
				// en cas d'erreur on log l'erreur et on met le module à zero
				else {
					log::add('JeePlcBus', 'debug', 'Attention retour d\'état défaillant pour le module ' . $d_code);
					log::add('JeePlcBus', 'debug', 'désactiver le mode retour d\'état ou vérifier le câblage.');
					foreach (eqLogic::getCmd() as $info) {
						$info->setValue(0);
						$info->save();
						$info->event(0);
					}
					return $tab_retour[0];
				}
			}
		}
		
		else {
			// si pas de retour d'état on simule l'état en fonction de la commande initiale
			foreach (eqLogic::getCmd() as $info) {
				$info->setValue($data1);
				$info->save();
				$info->event($data1);
			}
			$retour_sim = $d_code . ',' . $d_cmd . ',' . $data1 . ',' . $data2;
			log::add('JeePlcBus', 'debug', 'Retour état simulé : ' . $retour_sim);
			return $retour_sim;			
		}
	}
	
 	public function Requete_MaJ($d_code, $test_com) {
		$requete_Info = self::ActionCommande($d_code, 'STATUS_REQUEST', NULL, NULL, true, $test_com);	
		return $requete_Info;
	}

	public function force_du_signal($d_code, $test_com) {
		$requete_force = self::ActionCommande($d_code, 'GET_SIGNAL_STRENGTH', NULL, NULL, true, $test_com);
		$force_signal = explode(",", $requete_force);
		return $force_signal[2];
	}
	
	public function intensite_du_bruit($d_code, $test_com) {
		$requete_bruit = self::ActionCommande($d_code, 'GET_NOISE_STRENGTH', NULL, NULL, true, $test_com);
		$intensite_signal = explode(",", $requete_bruit);
		return $intensite_signal[2];
	}
    public function autoAjoutCommande($device_code, $dimmable) {
		if ($dimmable == 1) {
			global $listCmdJeePlcBus_DIM;
			$list_cmd = $listCmdJeePlcBus_DIM;
		} else {
			global $listCmdJeePlcBus_NODIM;
			$list_cmd = $listCmdJeePlcBus_NODIM;
		}
		
        foreach ($list_cmd as $cmd) {
			   if (cmd::byEqLogicIdCmdName($this->getId(), $cmd['name']))
					return;
				
			   if ($cmd) {
					$JeePlcBusCmd = new JeePlcBusCmd();
					$JeePlcBusCmd->setName(__($cmd['name'], __FILE__));
					$JeePlcBusCmd->setEqLogic_id($this->id);
					$JeePlcBusCmd->setConfiguration('tmps_dim', $cmd['configuration']['tmps_dim']);
					$JeePlcBusCmd->setType($cmd['type']);
					$JeePlcBusCmd->setSubType($cmd['subType']);
					$JeePlcBusCmd->setOrder($cmd['order']);
					$JeePlcBusCmd->setDisplay('generic_type', $cmd['generic_type']);
					$JeePlcBusCmd->setDisplay('forceReturnLineAfter', $cmd['forceReturnLineAfter']);
					$JeePlcBusCmd->save();
			   }

        }        
    }



    /*     * **********************Getteur Setteur*************************** */
	
}

class JeePlcBusCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

	
    public function execute($_options = array()) 
	{
		$eqLogic = $this->getEqLogic();
		$device_code = $eqLogic->getConfiguration('device_code');
		$retour_etat = $eqLogic->getConfiguration('retour_etat', 0);		
		$tmps_dim = $this->getConfiguration('tmps_dim');

		switch ($this->getName()) {
/* 			case 'DIM':
				$dim = $_options['slider'];
				log::add('JeePlcBus', 'debug', 'Action DIMMER détectée sur ' . $device_code . ' a ' . $dim . ' en ' . $tmps_dim);
				$eqLogic->ActionCommande($device_code, 'PRESET_DIM', $dim, $tmps_dim, $retour_etat, false);
				break; */
				
			case 'On':
				log::add('JeePlcBus', 'debug', 'Action ON détectée sur ' . $device_code);
				$eqLogic->ActionCommande($device_code, 'ON', '100', '0', $retour_etat, false);
				break;
				
			case 'Off':
				log::add('JeePlcBus', 'debug', 'Action OFF détectée sur ' . $device_code);
				$eqLogic->ActionCommande($device_code, 'OFF', '0', '0', $retour_etat, false);
				break;
				
			case '25':
				log::add('JeePlcBus', 'debug', 'Action 25% détectée sur ' . $device_code. ' en ' . $tmps_dim);
				$eqLogic->ActionCommande($device_code, 'PRESET_DIM', '25', $tmps_dim, $retour_etat, false);
				break;	

			case '50':
				log::add('JeePlcBus', 'debug', 'Action 50% détectée sur ' . $device_code. ' en ' . $tmps_dim);
				$eqLogic->ActionCommande($device_code, 'PRESET_DIM', '50', $tmps_dim, $retour_etat, false);
				break;	

			case '75':
				log::add('JeePlcBus', 'debug', 'Action 75% détectée sur ' . $device_code . ' en ' . $tmps_dim);
				$eqLogic->ActionCommande($device_code, 'PRESET_DIM', '75', $tmps_dim, $retour_etat, false);
				break;	
				
			case 'Refresh':
				log::add('JeePlcBus', 'debug', 'Refresh détectée sur ' . $device_code);
				$eqLogic->Requete_MaJ($device_code , false);
				break;						
		}
		
		return;
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>