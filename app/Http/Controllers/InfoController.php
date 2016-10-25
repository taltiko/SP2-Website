<?php


namespace App\Http\Controllers;
define("main", true);
ini_set("allow_url_fopen", true);
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InfoController extends Controller {
	public function getRouteInfo(){
		$data = "";



		$stepOn = htmlspecialchars($_POST['stepOn']);
		$stepOff = htmlspecialchars($_POST['stepOff']);

		//$url = "http://www.taltffiko.com";
		$url = sprintf("https://traintracks.online/api/Route/%s/%s/", $stepOn, $stepOff);


		$content = @file_get_contents($url);
		if($content == FALSE){
			$shorturl = substr($url, strrpos($url, "//")+2);
			return view('errors.API_down')->with('url', $url)->with('shorturl', $shorturl);
		}
		$result = json_decode($content);


		foreach($result->Routes as $route){
		

		$data = $data . "<ul>";
			
			$data = $data . sprintf("<li><b>Van:</b> %s<br /><b>Naar:</b> %s</li>", $result->StepOn, $result->StepOff);
			
			$data = $data . "<ul>";
			foreach($route->TransferStations as $ts){
				$data = $data . "<li>";
				
				$data = $data . sprintf("<b>%s</b> Richting %s", $ts->FullId, $ts->TerminusStation, $ts->TransferAt);
				
				if($ts->TransferAt != null)
				$data = $data . sprintf(" (Overstap te %s)", $ts->TransferAt);
				$data = $data . "<br />";
				
				if($ts->ArrivalPlatform != null)
					$data = $data . sprintf("Aankomst op spoor %s om %s, vertrek op spoor %s om %s<br />", $ts->ArrivalPlatform, $ts->ArrivalTime, $ts->DeparturePlatform, $ts->DepartureTime);
				
				$data = $data . "</li>";
			}
			$data = $data . "</ul>";
			
			$data = $data . "</ul>";
		}
		
		$data = $data . "<hr><br />";




		return view('pages.route-info')
			->with('data', $data);
	}

	public function getTreinInfo(){
		$data = "";
		$id = htmlspecialchars($_POST['treinId']);
		$url = sprintf("https://traintracks.online/api/train/%s", $id);
		$result = json_decode(file_get_contents($url));

		$data = $data . sprintf("Het id van uw trein: %s",$result->Number);

		$data = $data . "<ul>";
		$data = $data . sprintf("<li><b>Van: </b> %s <br /><b>Naar:</b> %s</li>",$result->DepartureStation,$result->TerminusStation);

		
		foreach ($result->Stops as $stops) {
			$countArray = count ($stops);
			$countArray=($countArray-1);

			$data = $data . "<table class='testTable'>";
			for($i = 0 ;$i<=($countArray);$i++){
				
				$data = $data . "<tr class='testTr'>";
				$data = $data . "<th class='testTh'>Halte: " . $result->Stops->Stations[$i]->Name . "</th>";
				$data = $data . "<td class='testTd'>Perron : " . $result->Stops->Stations[$i]->DeparturePlatform . "</td>";
				$data = $data . "<td class='testTd'>Tijdstip van aankomst: " . $result->Stops->Stations[$i]->Time->ActualArrival . "</td>";
				$data = $data . "<td class='testTd'>Tijdstip van vertrek: " . $result->Stops->Stations[$i]->Time->ActualDeparture . "</td>";
				$data = $data . "</tr>";
				
			}

			$data = $data . "</table>";
		}


		return view('pages.trein-info')->with('data', $data);
	}

	public function getStationInfo(){

		$data = "";

		$date = htmlspecialchars($_POST['treinTijd']);
		$date.=" GMT";
		$timestamp = strtotime($date);

		$name = htmlspecialchars($_POST['name']);
		$url = sprintf("https://traintracks.online/api/stationBoard/%s/%s", $name,$timestamp);
	 	$result = json_decode(file_get_contents($url));

		$data = $data . sprintf("Mijn vertrek station: %s", $result[0]->Stops->Stations[0]->Name);
		$numbers = array("");
		$aantalTrein = count($result);



		for($i=1;$i<$aantalTrein;$i++){
			$nummer= $result[$i]->Number;
			array_push($numbers, $nummer);
			$data = $data . $nummer . "  .  ";

		}
		

		$begin = 1;
		$einde = 100;
		//$einde = $begin + 10;
		$l = $begin;
		while($l < $einde && $l < count($numbers)){
			$treinId = $numbers[$l];
			$url2 = sprintf("https://traintracks.online/api/train/%s", $treinId);
			$resultTrein = json_decode(file_get_contents($url2));

			$data = $data . sprintf("<li><b>Van: </b> %s <br /><b>Naar:</b> %s</li>",$resultTrein->DepartureStation,$resultTrein->TerminusStation);
			$data = $data . "<ul>";

			$countArray =$resultTrein->Stops->Count;
			$plaatsStation = -1;

			$i = 0;
			while($plaatsStation == -1){

				$halte = $resultTrein->Stops->Stations[$i]->Name;

				if($pos = strpos($halte, $name) !== false ){
					$plaatsStation = $i;
				}


				$i++;
			}


			for($i = $plaatsStation;$i<$countArray;$i++){
				$echtVertrek = $resultTrein->Stops->Stations[$i]->Time->ActualDeparture;
				$geplandVertrek = $resultTrein->Stops->Stations[$i]->Time->Departure;
				$geplandAankomen = $resultTrein->Stops->Stations[$i]->Time->Arrival;
				$echtAankomen= $resultTrein->Stops->Stations[$i]->Time->ActualArrival;

				$lengthGeplandVertrek = strlen($geplandVertrek);
				$lengthEchtAankomen = strlen($echtAankomen);
				$lengthEchtVertrek = strlen($echtVertrek);
				$lengthGeplandAankomen = strlen($geplandAankomen);


				$vertrekMinuut1 = substr($geplandVertrek, 14,2);
				$vertrekMinuut2 = substr($echtVertrek, 14,2);
				$vertrekUur1 = substr($geplandVertrek, 11,2);
				$vertrekUur2 = substr($echtVertrek, 11,2);
				$delayMinutenVertrek = $vertrekMinuut2 - $vertrekMinuut1;
				$delayUrenVertrek = $vertrekUur2 - $vertrekUur1;
				$echoUrenVertrek = "";
				$echoMinuutVertrek = "";


				$aankomstMinuut1 = substr($geplandVertrek, 14,2);
				$aankomstMinuut2 = substr($echtVertrek, 14,2);
				$aankomstUur1 = substr($geplandVertrek, 11,2);
				$aankomstUur2 = substr($echtVertrek, 11,2);
				$delayMinutenAankomst = $aankomstMinuut2 - $aankomstMinuut1;
				$delayUrenAankomst = $aankomstUur2 - $aankomstUur1;
				$echoUrenAankomst = "";
				$echoMinuutAankomst = "";

				$vertrekMinuut1 = substr($geplandVertrek, 14,2);
				$vertrekMinuut2 = substr($echtVertrek, 14,2);
				$vertrekUur1 = substr($geplandVertrek, 11,2);
				$vertrekUur2 = substr($echtVertrek, 11,2);
				$delayMinutenVertrek = $vertrekMinuut2 - $vertrekMinuut1;
				$delayUrenVertrek = $vertrekUur2 - $vertrekUur1;
				$echoUrenVertrek = "";
				$echoMinuutVertrek = "";


				$aankomstMinuut1 = substr($geplandVertrek, 14,2);
				$aankomstMinuut2 = substr($echtVertrek, 14,2);
				$aankomstUur1 = substr($geplandVertrek, 11,2);
				$aankomstUur2 = substr($echtVertrek, 11,2);
				$delayMinutenAankomst = $aankomstMinuut2 - $aankomstMinuut1;
				$delayUrenAankomst = $aankomstUur2 - $aankomstUur1;
				$echoUrenAankomst = "";
				$echoMinuutAankomst = "";

				if($lengthGeplandAankomen != $lengthEchtAankomen){
					$delayUrenAankomst = 0;
					$delayMinutenAankomst = 0;
				}

				if($lengthEchtVertrek != $lengthGeplandVertrek){
					$delayMinutenVertrek = 0;
					$delayUrenVertrek = 0;
				}



				if($delayUrenAankomst != 0){
					$echoUrenAankomst.=$delayUrenAankomst;
					$echoUrenAankomst.=" uur/uren ";

					if($delayMinutenAankomst != 0){ 
			 			$echoUrenAankomst.=" + ";
			 			$echoUrenAankomst.=$delayUrenAankomst;
			 			$echoUrenAankomst.=" minuten vertraging.";
			
						if($i == 0){
							$data = $data . "Halte: " . $resultTrein->Stops->Stations[$i]->Name . " , Perron : " . $resultTrein->Stops->Stations[$i]->DeparturePlatform;
			 		    }
					 	else{
				 			$data = $data . sprintf("Halte: %s , Perron : %s, Tijdstip van aankomst: %s + %s, ", $resultTrein->Stops->Stations[$i]->Name, $resultTrein->Stops->Stations[$i]->DeparturePlatform,$resultTrein->Stops->Stations[$i]->Time->Arrival,$echoUrenAankomst);
				 		}

					}
				}
				else {
					if($delayMinutenAankomst != 0){ 
			 			$echoMinuutAankomst.=$delayMinutenAankomst;
			 			$echoMinuutAankomst.=" minuten vertraging.";

					 	if($i==0){
							$data = $data . sprintf("Halte: %s , Perron : %s, ", $resultTrein->Stops->Stations[$i]->Name, $resultTrein->Stops->Stations[$i]->DeparturePlatform);
				 		}
					 	else{
					 		$data = $data . sprintf("Halte: %s , Perron : %s, Tijdstip van aankomst: %s + %s, ", $resultTrein->Stops->Stations[$i]->Name, $resultTrein->Stops->Stations[$i]->DeparturePlatform,$resultTrein->Stops->Stations[$i]->Time->Arrival,$echoMinuutAankomst);
					 		}
					 	}
					else {
						if($i==0){
				 		 	$data = $data . sprintf("Halte: %s , Perron : %s,", $resultTrein->Stops->Stations[$i]->Name, $resultTrein->Stops->Stations[$i]->DeparturePlatform);
					 	}	
					 	else{
					 		$data = $data . sprintf("Halte: %s , Perron : %s, Tijdstip van aankomst: %s", $resultTrein->Stops->Stations[$i]->Name, $resultTrein->Stops->Stations[$i]->DeparturePlatform,$resultTrein->Stops->Stations[$i]->Time->Arrival);
				 		}
					 }
					if($delayUrenVertrek != 0){
				 		$echoUrenVertrek.=$delayUrenVertrek;
				 		$echoUrenVertrek.=" uur/uren ";

						if($delayMinutenVertrek != 0){ 
				 			$echoUrenVertrek.="+";
				 			$echoUrenVertrek.=$delayMinutenVertrek;
				 			$echoUrenVertrek.=" minuten ";
				 		 	$echoUrenVertrek.="vertraging.";

							if($i != ($countArray - 1)){
								$data = $data . sprintf(" Tijdstip van vertrek: %s + %s ",$resultTrein->Stops->Stations[$i]->Time->Departure,$echoUrenVertrek);
					 		}
							else{
								$data = $data . "eindstation.";
							}
						}
					}
					else{
						if($delayMinutenVertrek != 0){
				 			$echoMinuutVertrek.=$delayMinutenVertrek;
				 			$echoMinuutVertrek.=" minuten vertraging.";

						 	if($i != ($countArray -1)){
								$data = $data . sprintf(" Tijdstip van vertrek: %s + %s ",$resultTrein->Stops->Stations[$i]->Time->Departure,$echoMinuutVertrek);
								$data = $data . "<br>";
					 		}
						 	else{
						 		$data = $data . " Eindstation";
						 		$data = $data . "<br>";
					 		}
						}
						else{
							if($i != ($countArray -1)){
					 		 	$data = $data . sprintf(" Tijdstip van vertrek: %s",$resultTrein->Stops->Stations[$i]->Time->Departure);
					 		 	$data = $data . "<br>";
							 }	
							else{
						 		$data = $data . sprintf(" Eindstation");
						 		$data = $data . "<br>";
				 			}
						}
						 
					}
				}		
				$data = $data . ("</br>");
				$data = $data . ("</li>");

			}
			$data = $data . ("</ul>");
			


			$l++;

		}
		return view('pages.station-info')->with('data', $data);
	}
}