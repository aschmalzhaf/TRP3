<?php

class Model_Participants{
	
	protected $_ParticipantsTable;
	protected $_UserTable;
	
	
	
	public function getGenericParticipantsData($cols, $where, $sort, $range = Array(0, 0)){
		
		$table = $this->getParticipantsTable();
		
		$cols [] = "MA";	//MA hinzufügen

		$genParticipantsData = $table->getGenericParticipantsData($cols, $where, $sort, $range);

		$genParticipants = $genParticipantsData['data'];
		
		$genParticipantsOut = Array();
		
		$table_usr = $this->getUserTable();
		foreach($genParticipants as $genParticipant){
		    if($genParticipant['MA']!=NULL){

		        $usr = $table_usr->getUserDataByID($genParticipant['MA']);
			$genParticipant['name']=$usr['lname'];
			$genParticipant['vorname']=$usr['fname'];
			$genParticipant['ort']=$usr['city'];
			$genParticipant['strasse']=$usr['street'];
			$genParticipant['tel']=$usr['phone'];
			$genParticipant['mail']=$usr['email'];
			$genParticipant['geb']=substr($usr['birthday'],8,2).".".substr($usr['birthday'],5,2).".".substr($usr['birthday'],0,4);
			
		    }
			$genParticipant['zelt']="";
			$genParticipant['gzelt']="";
			$genParticipant['masse']="";
			$genParticipant['privat']="";
			$genParticipant['zimmer']="";
			$genParticipant['wohnmobil']="";
			if($genParticipant['schlafen']=="zelt") {$genParticipant['schlafen']="eigenes Zelt";
				$genParticipant['zelt']="x";				
			}
			elseif($genParticipant['schlafen']=="gzelt") {$genParticipant['schlafen']="Gemeinschaftszelt (SWD)";
				$genParticipant['gzelt']="x";			
			}
			elseif($genParticipant['schlafen']=="masse") {$genParticipant['schlafen']="Massenlager";
				$genParticipant['masse']="x";
			}
			elseif($genParticipant['schlafen']=="privat") {$genParticipant['schlafen']="Privatunterkunft";
				$genParticipant['privat']="x";				
			}
			elseif($genParticipant['schlafen']=="zimmer") {$genParticipant['schlafen']="Zimmer";
				$genParticipant['zimmer']="x";				
			}
			elseif($genParticipant['schlafen']=="wohnmobil") {$genParticipant['schlafen']="Wohnmobil";
				$genParticipant['wohnmobil']="x";				
			}



			unset($genParticipant['MA']);
		    $genParticipantsOut[] = $genParticipant;
		}
		
		$genParticipantsData['data'] = $genParticipantsOut;
		return $genParticipantsData;
	}
	
	


    
	public function saveParticipant(array $data){
		
		$table = $this->getParticipantsTable();
		
		
    		$res = $table->insert($data);
		if($res == null){
			return null;
		}else{
			$data['ID'] = $res;
			return $data;
		}
		
	}
	
	public function deleteParticipants($ID){
    	
    		$table = $this->getParticipantsTable();
    	
    		return $table->deleteParticipants($ID);
    	}
			      
	public function updateParticipants(array $data){
		
		$table = $this->getParticipantsTable();
		
		
		return $table->updateParticipants($data);
	}

	//check Mail-Adress
	public function checkmail($data){
		
		$table = $this->getParticipantsTable();
		
		return $table->checkmail($data);
	}


	public function getParticipantsDataByID($ID){
    
	    	$table = $this->getParticipantsTable();
	    	
	    	return $table->getParticipantsDataByID($ID);
    	}
	
	public function getAllParticipants(){
    
	    	$table = $this->getParticipantsTable();
	    	
	    	return $table->getAllParticipants();
    	}

	public function getAllParticipantsWhere($where){
    
	    	$table = $this->getParticipantsTable();
	    	
	    	return $table->getAllParticipantsWhere($where);
    	}


	public function getAnmeldeliste($where){	

		$akt_zeile = 0;
		$list = array(); //  0     1      2          3      4               5               6       7                8      9          10        11            12                  13                 14                15                      16            17            18     19  20             19        20
		$list_names = array('ID', 'Name', 'Vorname', 'Ort', 'Teennight', 'Teennightplus', 'Jufa', 'Unterschrift', 'FrSo', 'SaSo', 'FrSaTG', 'eigZeltSaSo', 'SWD-ZeltFrSa', 'SWD-ZeltSaSo', 'MassenlagerSaSo', 'SonntagFR', 'SonntagTG', 'Privatunterkunft', 'Zimmer', 'Wohnmobil', 'geb', 'voranmeldung');

			
		$tns = $this->getAllParticipantsWhere($where);
		//array("ID", "Name", "Vorname", "plz","Ort", "Strasse", "tel", "geb", "art", "schlafen", "samstag", "unterschrift", "kommentar", "mail", "lastchanged", "datum", "addedby")); 

		foreach ( $tns as $tn ) {
    			
			$list[$akt_zeile] = array('ID'=>'', 'Name'=>'', 'Vorname'=>'','Ort'=>'', 'Teennight'=>'', 'Teennightplus'=>'', 'Jufa'=>'', 'Unterschrift'=>'', 'FrSo'=>'', 'SaSo'=>'', 'FrSaTG'=>'', 'eigZeltSaSo'=>'', 'SWD-ZeltFrSa'=>'', 'SWD-ZeltSaSo'=>'', 'MassenlagerSaSo'=>'', 'SonntagFR'=>'', 'SonntagTG'=>'', 'Privatunterkunft'=>'', 'Zimmer'=>'', 'Wohnmobil'=>'', 'geb'=>'', 'voranmeldung'=>'');

			$list[$akt_zeile][$list_names[0]] = $tn['ID'];
			if($tn['MA']!=NULL){
				$list[$akt_zeile][$list_names[1]] = $tn['Name1'];
				$list[$akt_zeile][$list_names[2]] = $tn['Vorname1'];
				$list[$akt_zeile][$list_names[3]] = $tn['Ort1'];
				if ($tn['geb1'] != null) {
					$list[$akt_zeile][$list_names[20]] = substr($tn['geb1'],8,2).".".substr($tn['geb1'],5,2).".".substr($tn['geb1'],0,4);
				} else {
					$list[$akt_zeile][$list_names[20]] = "";
				}

			}
			else{
				$list[$akt_zeile][$list_names[0]] = $tn['ID'];
				$list[$akt_zeile][$list_names[1]] = $tn['Name'];
				$list[$akt_zeile][$list_names[2]] = $tn['Vorname'];
				$list[$akt_zeile][$list_names[3]] = $tn['Ort'];
				$list[$akt_zeile][$list_names[20]] = $tn['geb'];

			}
			if($tn['unterschrift'] == "1")	$list[$akt_zeile][$list_names[7]] = "X";	//Unterschrift
			
			

			if($tn['art'] == "teennight") {
				$list[$akt_zeile][$list_names[4]] = $tn['anzahl'];	//Teennight

				if($tn['samstag']!="nichts"){
				$list[$akt_zeile][$list_names[10]] = $tn['anzahl'];	//Fr/Sa TG, weil KIS oder Sportturnier oder Kanufahren
				}
			}
			elseif($tn['art'] == "teennightplus") {
				$list[$akt_zeile][$list_names[5]] = $tn['anzahl'];	//Teennightplus
				$list[$akt_zeile][$list_names[8]] = $tn['anzahl'];	//Fr-So
				
				if($tn['schlafen']== "masse") {
					$list[$akt_zeile][$list_names[14]] = $tn['anzahl'];	//Massenlager
				}
				elseif($tn['schlafen']== "zelt") {
					$list[$akt_zeile][$list_names[11]] = $tn['anzahl'];	//eigenes Zelt Sa/So
				}
				elseif($tn['schlafen']== "gzelt") {
					$list[$akt_zeile][$list_names[12]] = $tn['anzahl'];	//Zelt SWD Fr/Sa
					$list[$akt_zeile][$list_names[13]] = $tn['anzahl'];	//Zelt SWD Sa/So
				}
				elseif($tn['schlafen']== "privat") {
					$list[$akt_zeile][$list_names[17]] = $tn['anzahl'];	//Privat
				}
				elseif($tn['schlafen']== "zimmer") {
					$list[$akt_zeile][$list_names[18]] = $tn['anzahl'];	//Zimmer
				}
				elseif($tn['schlafen']== "wohnmobil") {
					$list[$akt_zeile][$list_names[19]] = $tn['anzahl'];	//Wohnmobil
				}
				$list[$akt_zeile][$list_names[15]] = $tn['anzahl'];	//Sonntag Frühstück
			}
			elseif($tn['art'] == "jufa") {
				$list[$akt_zeile][$list_names[6]] = $tn['anzahl'];	//JuFa
				$list[$akt_zeile][$list_names[9]] = $tn['anzahl'];	//Sa-So

				if($tn['schlafen']== "masse") {
					$list[$akt_zeile][$list_names[14]] = $tn['anzahl'];	//Massenlager
				}
				elseif($tn['schlafen']== "zelt") {
					$list[$akt_zeile][$list_names[11]] = $tn['anzahl'];	//eigenes Zelt Sa/So
				}
				elseif($tn['schlafen']== "gzelt") {
					$list[$akt_zeile][$list_names[13]] = $tn['anzahl'];	//Zelt SWD Sa/So
				}
				elseif($tn['schlafen']== "privat") {
					$list[$akt_zeile][$list_names[17]] = $tn['anzahl'];	//Privat
				}
				elseif($tn['schlafen']== "zimmer") {
					$list[$akt_zeile][$list_names[18]] = $tn['anzahl'];	//Zimmer
				}
				elseif($tn['schlafen']== "wohnmobil") {
					$list[$akt_zeile][$list_names[19]] = $tn['anzahl'];	//Wohnmobil
				}
				$list[$akt_zeile][$list_names[15]] = $tn['anzahl'];	//Sonntag Frühstück
			}
			elseif($tn['art'] == "tg_sa") {
				$list[$akt_zeile][$list_names[15]] = "1";	//Tagesgast Samstag
			}
			elseif($tn['art'] == "tg_so") {
				$list[$akt_zeile][$list_names[16]] = "1";	//Tagesgast Sonntag
			}
		
			//Anmeldedatum
			$anmeldeschluss = array('21', '06', '2011');
			$tmp_dat = explode(',', $tn['datum']);	//Datum und Uhrzeit trennen
			$anmeldedatum = explode('.', $tmp_dat[0]);

			if($anmeldedatum[2]>=$anmeldeschluss[2]){

				if($anmeldedatum[2]==$anmeldeschluss[2] && $anmeldedatum[1]>=$anmeldeschluss[1]){

					if($anmeldedatum[1]==$anmeldeschluss[1] && $anmeldedatum[0]>$anmeldeschluss[0]){
						

					}else $list[$akt_zeile][$list_names[21]]="x";
				}else $list[$akt_zeile][$list_names[21]]="x";	
			}else $list[$akt_zeile][$list_names[21]]="x";


			$akt_zeile = $akt_zeile+1;
		}

		$res=array();
		

    	$res['count'] = count($list);
    	$res['data'] = $list;
	
		return $res;
	}

	public function getStatistik($statistik_tage)
    {
		$table = $this->getParticipantsTable();
	    	
	    	$tns = $table->getAllParticipants();

		$statistik = array();		//[Teennight, Tennightplus]
		for($i=0;$i<count($statistik_tage);$i++){ $statistik[0][$i]=0;$statistik[1][$i]=0;}
		foreach($tns as $tn){

		
			$tmp_dat = explode(', ', $tn['datum']);	//Datum und Uhrzeit trennen
			$anmeldedatum = explode('.', $tmp_dat[0]);
			$anmeldeuhrzeit = explode(':', $tmp_dat[1]);
			if($tn['art']== "teennight"){
				for($i=0;$i<count($statistik_tage);$i++){
					if($anmeldedatum[2]>$statistik_tage[$i][2]){}	//späteres Jahr
					elseif($anmeldedatum[2]==$statistik_tage[$i][2]){ //gleiches Jahr	
						if($anmeldedatum[1]>$statistik_tage[$i][1]){} //späterer Monat
						elseif($anmeldedatum[1]==$statistik_tage[$i][1]){ //gleicher Monat
							if($anmeldedatum[0]>$statistik_tage[$i][0]){} //späterer Tag
							else if($anmeldedatum[0]==$statistik_tage[$i][0]){ //gleicher Tag
								if($anmeldeuhrzeit[0]>$statistik_tage[$i][3]){} //gspätere Stunde
								elseif($anmeldeuhrzeit[0]==$statistik_tage[$i][3]){ //gleiche Stunde
									if($anmeldeuhrzeit[1]>$statistik_tage[$i][4]){} //spätere Minute
									else $statistik[0][$i] = $statistik[0][$i]+$tn['anzahl'];
								}else $statistik[0][$i] = $statistik[0][$i]+$tn['anzahl'];
							}else $statistik[0][$i] = $statistik[0][$i]+$tn['anzahl'];
						}else $statistik[0][$i] = $statistik[0][$i]+$tn['anzahl'];

					}else $statistik[0][$i] = $statistik[0][$i]+$tn['anzahl'];
				}
			}
			if($tn['art']=="teennightplus"){
				for($i=0;$i<count($statistik_tage);$i++){
					if($anmeldedatum[2]>$statistik_tage[$i][2]){}	//späteres Jahr
					elseif($anmeldedatum[2]==$statistik_tage[$i][2]){ //gleiches Jahr	
						if($anmeldedatum[1]>$statistik_tage[$i][1]){} //späterer Monat
						elseif($anmeldedatum[1]==$statistik_tage[$i][1]){ //gleicher Monat
							if($anmeldedatum[0]>$statistik_tage[$i][0]){} //späterer Tag
							else if($anmeldedatum[0]==$statistik_tage[$i][0]){ //gleicher Tag
								if($anmeldeuhrzeit[0]>$statistik_tage[$i][3]){} //gspätere Stunde
								elseif($anmeldeuhrzeit[0]==$statistik_tage[$i][3]){ //gleiche Stunde
									if($anmeldeuhrzeit[1]>$statistik_tage[$i][4]){} //spätere Minute
									else $statistik[1][$i] = $statistik[1][$i]+$tn['anzahl'];
								}else $statistik[1][$i] = $statistik[1][$i]+$tn['anzahl'];
							}else $statistik[1][$i] = $statistik[1][$i]+$tn['anzahl'];
						}else $statistik[1][$i] = $statistik[1][$i]+$tn['anzahl'];

					}else $statistik[1][$i] = $statistik[1][$i]+$tn['anzahl'];
				}
			}
		}

		return $statistik;
    }

	public function getUserDataByID($ID)
    {
    	$table = $this->getUserTable();
    	
    	return $table->getUserDataByID($ID);
    }
   	private function getUserTable()
    {
        if (null === $this->_UserTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_User.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_UserTable = new Db_Table_User(array('db' => $db));
        }
        return $this->_UserTable;
    } 

	public function getParticipantsTable()
    {
        if (null === $this->_ParticipantsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Participants.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_ParticipantsTable = new Db_Table_Participants(array('db' => $db));
        }
        return $this->_ParticipantsTable;
    }

}
