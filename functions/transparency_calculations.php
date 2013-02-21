<?php
/*
 * This takes the data created by the count_attributes function and formats into a json structure before saving it to a file
 * 
 * name: format_as_json
 * 
 * @$output_dir       string    The directory to save files into 
 * @$corpus           string    This is a directory in which there may be a number of iati activity files
 * @$path_to_corpus   string    This is the path the the directory containing the files we want to examine
 * 
 * @return            null      The function saves data to a file
 * 
 */

function format_as_json ($output_dir,$corpus,$path_to_corpus,$file_path = FALSE) {
  global $test_meta_file; //The tests to perform in json format
  global $country_map;    //Maps lanaguages to countries
  global $metadata;       //Holds the data that will be saved to json. Declared as global so we can use it in the get_score function
  global $test_meta;      //This is the data about the tests held in an accessible array. Declared as global so we can use it in the get_score function
  
  $output_file = $output_dir . $corpus . '_transparency.json';
  //echo $output_file;
  //Get the existing data on this provider from a file
  //If file doesn't exist create the start of one
  if (!file_exists($output_file)) {
    $metadata_header = array( "provider" => $corpus,
                              "hierarchy" => 0,
                              "activityCount" => 100
                             );
    $json = json_encode($metadata_header); 
    file_put_contents($output_file,$json);     //die; 
  }

  $json = file_get_contents($output_file);
  $metadata = json_decode($json,true);
  //print_r($metadata); //die;

  //Get the test metadata so we know what threasholds and scores to apply to this test
  $test_meta = file_get_contents($test_meta_file);
  $test_meta = json_decode($test_meta,true);
  //print_r($test_meta); die;

  //test the providers files and return the data for us
  if ($file_path) {
    $data = count_attributes($path_to_corpus,basename($file_path));
    //echo "yes";
  } else {
    $data = count_attributes($path_to_corpus);
  }
  //print_r($data) ; die;
  //Set the hierarcy that we're going to test one. the rule is the lowest i.e. highest number which should be the last value of $data["hierarchies"]
  $metadata["hierarchy"] = end($data["hierarchies"]);
  reset($data["hierarchies"]); //end moves the array pointer to the end of the array, so lets rest it
  // Number of activities
  $metadata["activityCount"] = $data["no-activities"][$metadata["hierarchy"]]; //number of activities tested at our hierarchy level
  //echo $metadata["activityCount"]; die;
  //get the results for the json file
  //sTet up some variables for activiy-date element first
  //$attribute_values = array("start-planned","start-actual","end-planned","end-actual");
  $attribute_values = array("start","end");
  //$attribute_start_values = array("start-planned","start-actual");
  //$attribute_end_values = array("end-planned","end-actual");
  $start = $end = 0;

  //print_r($data["participating_org_implementing"]); die;


  foreach ($data["hierarchies"] as $hierarchy) { //The count attributes routine fetches data at all hierarchies, but we only need to test the lowest usually. 
    if ($hierarchy == $metadata["hierarchy"]) { //Set for each provider in a metadata file
      //Elements
      $document_links = $result_element = $conditions = 0;
      if (isset($data["document_links"][$hierarchy])) { $document_links = count($data["document_links"][$hierarchy]); }
      if (isset($data["result_element"][$hierarchy])) { $result_element = count($data["result_element"][$hierarchy]); }
      if (isset($data["conditions"][$hierarchy])) { $conditions = count($data["conditions"][$hierarchy]); }
      
      //Participating org test
      $participating_org_accountable  = $participating_org_implementing = 0;
      if (isset($data["participating_org_accountable"][$hierarchy])) { 
        $participating_org_accountable  = array_unique($data["participating_org_accountable"][$hierarchy]); 
        $participating_org_accountable  = count($participating_org_accountable);
      }
      if (isset($data["participating_org_implementing"][$hierarchy])) { 
        $participating_org_implementing = array_unique($data["participating_org_implementing"][$hierarchy]); 
        $participating_org_implementing = count($participating_org_implementing);
      }
      //echo $participating_org_implementing; die;
      
      //Budget/Planned Disbursement
      $budget = 0;
      if (isset($data["budget"][$hierarchy])) { $budget = count($data["budget"][$hierarchy]); }
      
      //Identifiers
      $unique_ids = array_unique($data["identifiers"][$hierarchy]["good"]); //In case identifiers are used more than once
      $good_ids = count($unique_ids);
      
      //Transactions 
      $transaction_type_commitment = $transaction_type_disbursements = $transaction_type_expenditure = 0;
      $unique_commitments = $unique_disbursements = $unique_expenditure = array();
      if (isset($data["transaction_type_commitment"][$hierarchy])) {
        $unique_commitments = array_unique($data["transaction_type_commitment"][$hierarchy]); //In case one activity has more than one commitment
        $transaction_type_commitment = count($unique_commitments);
      }
      
      if (isset($data["transaction_type_disbursement"][$hierarchy])) {
        $unique_disbursements = array_unique($data["transaction_type_disbursement"][$hierarchy]); //In case one activity has more than one commitment
        $transaction_type_disbursements = count($unique_disbursements);
        //echo $transaction_type_disbursements; //die;
      }
      if (isset($data["transaction_type_expenditure"][$hierarchy])) {
        $unique_expenditure = array_unique($data["transaction_type_expenditure"][$hierarchy]); //In case one activity has more than one commitment
        $transaction_type_expenditure = count($unique_expenditure);
      }
      //echo $transaction_type_expenditure; die;
      //Test to see if an activity has either
      //Both a disbursement and an expenditure
      /* This would mean looping through one array and seeing if iati-identifier values are in both. And getting a count 
       * Or if one array is smaller than the treashold then it's a fail??
       * Or an array diff would give us numbers of activites that don't have both (but not the numbers without any!!
      //OR
      //Either a disbursement or an expenditure
      * We could merge the arrays, and then array unique it to get our count
      */
      $disbursements_expenditure = array_merge($unique_disbursements,$unique_expenditure);
      $unique_disbursements_expenditure = array_unique($disbursements_expenditure);
      $disbursements_expenditure_count = count($unique_disbursements_expenditure);
      
      //Tracable Transactions
      $no_disbursements = $data["no_disbursements"][$hierarchy];
      //echo $no_disbursements . PHP_EOL;
      $no_incoming_funds = $data["no_incoming_funds"][$hierarchy];
      //echo $no_incoming_funds . PHP_EOL;
      $transactions_that_should_be_traced = $no_disbursements + $no_incoming_funds;
      //echo $transactions_that_should_be_traced . PHP_EOL;
      $no_tracable_transactions = $data["no_tracable_transactions"][$hierarchy];
      //echo $no_tracable_transactions . PHP_EOL;
     // die;
      if ($no_tracable_transactions > 0 ) { //avoid divide by zero
        $percent_tracable = 100 * round(( $no_tracable_transactions / $transactions_that_should_be_traced),2);
        $tracable_threashold = $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["threashold"];
        //echo $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["threashold"]; 
        if ($percent_tracable >= $tracable_threashold) {
          //echo "yes"; die;
          $tracable_score = $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["score"];
        } else {
          $tracable_score = 0;
        }
      } else {
        $tracable_score = $percent_tracable = 0;
      }
      
      
      //Location
      //$activities_with_location = $activities_with_coordinates = $activities_with_adminstrative = 0;
      //Count activities with structured location data
      //This is either co-ordinates present or
      //administartive element and adm1 or adm2 attribute
      //This MUST be a subset of the location count (mustn't it?)
      $activities_with_coordinates = $activities_with_adminstrative = array();//set up empty arrays first
      if (isset($data["activities_with_coordinates"][$hierarchy])) { 
        $activities_with_coordinates = array_unique($data["activities_with_coordinates"][$hierarchy]);
      }
      if (isset($data["activities_with_adminstrative"][$hierarchy])) { 
        $activities_with_adminstrative = array_unique($data["activities_with_coordinates"][$hierarchy]);
      }
      $activities_with_structure_locations = array_merge($activities_with_coordinates,$activities_with_adminstrative); //if arrays are empty this is ok!
      $activities_with_structure_locations = array_unique($activities_with_structure_locations); //need to unique them as activities can have both
      $activities_with_structure_locations_count = count($activities_with_structure_locations);
      //echo $activities_with_structure_locations_count . PHP_EOL;

      $activities_with_location_count = 0;
      $activities_with_location = array();
      if (isset($data["activities_with_location"][$hierarchy])) { 
        $activities_with_location =  array_unique($data["activities_with_location"][$hierarchy]); 
      }
      //$activities_with_location = array_merge($activities_with_location,$activities_with_structure_locations);
      $activities_with_location = array_unique($activities_with_location);
      $activities_with_location_count = count($activities_with_location);
      //echo $activities_with_location_count . PHP_EOL;
      
      //
      $location_level_1 = $activities_with_location_count;
      $location_level_2 = $activities_with_structure_locations_count;
        
       //die;
       
      //Sector
      $activities_sector_declared_dac = $activities_sector_assumed_dac = array();
      if (isset($data["activities_sector_declared_dac"][$hierarchy])) { 
        $activities_sector_declared_dac = $data["activities_sector_declared_dac"][$hierarchy];
        $activities_sector_declared_dac = array_unique($activities_sector_declared_dac);
      }
      if (isset($data["activities_sector_assumed_dac"][$hierarchy])) { 
        $activities_sector_assumed_dac = $data["activities_sector_assumed_dac"][$hierarchy];
        $activities_sector_assumed_dac = array_unique($activities_sector_assumed_dac);
      }
      $activities_with_dac_sector = array_merge($activities_sector_declared_dac,$activities_sector_assumed_dac); //probably don't need to 'unique' this, but won't hurt
      $activities_with_dac_sector = array_unique($activities_with_dac_sector);
      $activities_with_dac_sector_count = count($activities_with_dac_sector);
      //echo $activities_with_dac_sector_count . PHP_EOL;
     // die;
     // die;
      //Last-updated-datetime
      //$most_recent = $data["most_recent"][$hierarchy];
      
      //Activity Dates
      foreach ($attribute_values as $value) { //Loop through all possible results
        
        if (isset($data["activities_with_attribute"][$hierarchy][$value])) {
          //echo $value . PHP_EOL;
          $count = count(array_unique($data["activities_with_attribute"][$hierarchy][$value]));
          //if (in_array($value, $attribute_start_values)) {
          if ($value == "start") {
            $start = $start + $count;
          //} elseif (in_array($value, $attribute_end_values)) {
          } else if ($value == "end") {
            $end = $end + $count;
          }
        }
      }
      
      //Languages
      $activies_in_country_lang = array();
      if (isset($data["activies_in_country_lang"][$hierarchy])) {
        $activies_in_country_lang = $data["activies_in_country_lang"][$hierarchy];
        $activies_in_country_lang = array_unique($activies_in_country_lang);
      }
      $activies_in_country_lang_count = count($activies_in_country_lang);
      //echo $activies_in_country_lang_count; //die;
    } 
  }
  //echo $start . PHP_EOL;
  //echo $end . PHP_EOL;

  //Work out the scores
  $start_score = get_score($start,"Activity Dates (Start Date)");
  $end_score = get_score($end,"Activity Dates (End Date)");
  $document_links_score = get_score($document_links,"Activity Documents");
  $conditions_score = get_score($conditions,"Text of Conditions");
  $result_element_score = get_score($result_element,"Results data");
  $participating_org_accountable_score = get_score($participating_org_accountable,"Participating Organisation (Accountable)");
  $participating_org_implementing_score = get_score($participating_org_implementing,"Participating Organisation (Implementing)");
  $budget_score = get_score($budget,"Activity Budget / Planned Disbursement");
  $good_ids_score = get_score($good_ids,"IATI activity identifier");
  $transaction_type_commitment_score = get_score($transaction_type_commitment,"Financial transaction (Commitment)");
  $transaction_type_disb_expend_score = get_score($disbursements_expenditure_count,"Financial transaction (Disbursement & Expenditure)");

  $location_level_1_score = get_score($location_level_1,"Sub-national Geographic Location (text)");
  $location_level_2_score = get_score($location_level_2,"Sub-national Geographic Location (structure)");

  $activities_with_dac_sector_score = get_score($activities_with_dac_sector_count,"Sector (DAC CRS)");

  $activies_in_country_lang_score = get_score($activies_in_country_lang_count,"Activity Title or Description (Recipient language)");



  $metadata["tests"]["activityDateStart"]["count"] = $start;
  $metadata["tests"]["activityDateStart"]["score"] = $start_score["score"];
  $metadata["tests"]["activityDateStart"]["percentage"] = $start_score["percentage"];

  $metadata["tests"]["activityDateEnd"]["count"] = $end;
  $metadata["tests"]["activityDateEnd"]["score"] = $end_score["score"];
  $metadata["tests"]["activityDateEnd"]["percentage"] = $end_score["percentage"];

  $metadata["tests"]["documentLink"]["count"] = $document_links;
  $metadata["tests"]["documentLink"]["score"] = $document_links_score["score"];
  $metadata["tests"]["documentLink"]["percentage"] = $document_links_score["percentage"];

  $metadata["tests"]["result"]["count"] = $result_element;
  $metadata["tests"]["result"]["score"] = $result_element_score["score"];
  $metadata["tests"]["result"]["percentage"] = $result_element_score["percentage"];

  $metadata["tests"]["conditions"]["count"] = $conditions;
  $metadata["tests"]["conditions"]["score"] = $conditions_score["score"];
  $metadata["tests"]["conditions"]["percentage"] = $conditions_score["percentage"];

  $metadata["tests"]["participatingOrgImplementing"]["count"] = $participating_org_implementing;
  $metadata["tests"]["participatingOrgImplementing"]["score"] = $participating_org_implementing_score["score"];
  $metadata["tests"]["participatingOrgImplementing"]["percentage"] = $participating_org_implementing_score["percentage"];

  $metadata["tests"]["participatingOrgAccountable"]["count"] = $participating_org_accountable;
  $metadata["tests"]["participatingOrgAccountable"]["score"] = $participating_org_accountable_score["score"];
  $metadata["tests"]["participatingOrgAccountable"]["percentage"] = $participating_org_accountable_score["percentage"];

  $metadata["tests"]["budget"]["count"] = $budget;
  $metadata["tests"]["budget"]["score"] = $budget_score["score"];
  $metadata["tests"]["budget"]["percentage"] = $budget_score["percentage"];

  $metadata["tests"]["iatiIdentifier"]["count"] = $good_ids;
  $metadata["tests"]["iatiIdentifier"]["score"] = $good_ids_score["score"];
  $metadata["tests"]["iatiIdentifier"]["percentage"] = $good_ids_score["percentage"];

  $metadata["tests"]["transactionTypeCommitment"]["count"] = $transaction_type_commitment;
  $metadata["tests"]["transactionTypeCommitment"]["score"] = $transaction_type_commitment_score["score"];
  $metadata["tests"]["transactionTypeCommitment"]["percentage"] = $transaction_type_commitment_score["percentage"];

  $metadata["tests"]["transactionTypeDisbursementExpenditure"]["count"] = $disbursements_expenditure_count;
  $metadata["tests"]["transactionTypeDisbursementExpenditure"]["score"] = $transaction_type_disb_expend_score["score"];
  $metadata["tests"]["transactionTypeDisbursementExpenditure"]["percentage"] = $transaction_type_disb_expend_score["percentage"];

  $metadata["tests"]["transactionTracability"]["eligable"] = $transactions_that_should_be_traced;
  $metadata["tests"]["transactionTracability"]["count"] = $no_tracable_transactions;
  $metadata["tests"]["transactionTracability"]["score"] = $tracable_score;
  $metadata["tests"]["transactionTracability"]["percentage"] = $percent_tracable;

  $metadata["tests"]["locationText"]["count"] = $location_level_1;
  $metadata["tests"]["locationText"]["score"] = $location_level_1_score["score"];
  $metadata["tests"]["locationText"]["percentage"] = $location_level_1_score["percentage"];

  $metadata["tests"]["locationStructure"]["count"] = $location_level_2;
  $metadata["tests"]["locationStructure"]["score"] = $location_level_2_score["score"];
  $metadata["tests"]["locationStructure"]["percentage"] = $location_level_2_score["percentage"];

  $metadata["tests"]["sector"]["count"] = $activities_with_dac_sector_count;
  $metadata["tests"]["sector"]["score"] = $activities_with_dac_sector_score["score"];
  $metadata["tests"]["sector"]["percentage"] = $activities_with_dac_sector_score["percentage"];

  $metadata["tests"]["language"]["count"] = $activies_in_country_lang_count;
  $metadata["tests"]["language"]["score"] = $activies_in_country_lang_score["score"];
  $metadata["tests"]["language"]["percentage"] = $activies_in_country_lang_score["percentage"];

  $json = json_encode($metadata); 
  $json = json_format($json); //pretty it up . Function from include functions/pretty_json.php
  file_put_contents($output_file,$json);
        //die;
}//end if excluded
//} //end foreach dirs as corpus

function get_score($count,$test) {
  global $metadata;
  global $test_meta;
  //echo $test . PHP_EOL;
  $percentage = 100 * round(($count/$metadata["activityCount"]),2);
  //echo $percentage;
  //echo  $test_meta["test"][$test]["threashold"];
  //echo $test_meta["test"][$test]["score"];
  if ($percentage != 0 && $percentage >= $test_meta["test"][$test]["threashold"]) { 
    $score = $test_meta["test"][$test]["score"];
  } else {
    $score = 0;
  }
  return array ("score" => $score,
                "percentage" => $percentage
                );
}

function count_attributes($dir,$single_file = FALSE) {
  $no_activity_dates = array();
  $activities_with_at_least_one = array();
  $no_activities = array();
  $found_hierarchies= array();
  $activities_with_attribute = array();
  $activity_by = array();
  
  $document_links = array();
  $result_element = array();
  $conditions = array();
  
  $participating_org_accountable = array();
  $participating_org_implementing = array();
  $budget = array();
  $identifiers = array();
  
  $transaction_type_commitment = array();
  $transaction_type_disbursement = array();
  $transaction_type_expenditure = array();
  $no_disbursements = $no_incoming_funds = $no_tracable_transactions = array();
  
  $activities_with_sector = array();
  
  $most_recent = array();
  
  $activities_with_location = array();
  $activities_with_coordinates = array();
  $activities_with_adminstrative = array();
  
  $activities_sector_assumed_dac = array();
  $activities_sector_declared_dac = array();
  
  $activies_in_country_lang = array();
  
  $i=0; //used to count bad id's
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            if ($single_file && $file != $single_file) { //skip all files except the one we want if set/requested.Handy to test just one file in a directory
              continue;
            }          
            
            //load the xml SAFELY
            /* Some safety against XML Injection attack
             * see: http://phpsecurity.readthedocs.org/en/latest/Injection-Attacks.html
             * 
             * Attempt a quickie detection of DOCTYPE - discard if it is present (cos it shouldn't be!)
            */
            $xml = file_get_contents($dir . $file);
            $collapsedXML = preg_replace("/[[:space:]]/", '', $xml);
            //echo $collapsedXML;
            if(preg_match("/<!DOCTYPE/i", $collapsedXML)) {
                //throw new InvalidArgumentException(
               //     'Invalid XML: Detected use of illegal DOCTYPE'
               // );
                //echo "fail";
              return FALSE;
            }
            $loadEntities  = libxml_disable_entity_loader(true);
            $dom = new DOMDocument;
            $dom->loadXML($xml);
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\ValueException(
                        'Invalid XML: Detected use of illegal DOCTYPE'
                    );
                    libxml_disable_entity_loader($loadEntities);
                    return FALSE;
                }
            }
            libxml_disable_entity_loader($loadEntities);
            
            
            if ($xml = simplexml_import_dom($dom)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $activities = $xml->{"iati-activity"};
                    //print_r($attributes); die;
                    foreach ($activities as $activity) {
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                        if ($hierarchy && $hierarchy !=NULL) {
                          $hierarchy = (string)$activity->attributes()->hierarchy;
                        } else {
                          $hierarchy = 0;
                        }
                        $found_hierarchies[] = $hierarchy; 
                        if (!isset($no_activities[$hierarchy])) {
                          $no_activities[$hierarchy] = 0;
                        }
                        $no_activities[$hierarchy]++;
                        
                        //Set up some more counters:
                        if (!isset($no_disbursements[$hierarchy])) {
                          $no_disbursements[$hierarchy] = 0;
                        }
                        if (!isset($no_incoming_funds[$hierarchy])) {
                          $no_incoming_funds[$hierarchy] = 0;
                        }
                        if (!isset($no_tracable_transactions[$hierarchy])) {
                          $no_tracable_transactions[$hierarchy] = 0;
                        }
                        
                        
                        
                        
                        
                        //Elements check
                        //is <document-link>,<conditions>,<result> present
                        if (count($activity->{"document-link"}) > 0) {
                          $document_links[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        if (count($activity->conditions) > 0) {
                          $conditions[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        if (count($activity->result) > 0) {
                          $result_element[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        
                        //More elements 
                        //Participating Organisation (Implementing)
                        $participating_orgs = $activity->{"participating-org"};
                        foreach ($participating_orgs as $participating_org) {
                          //echo (string)$activity->{"participating-org"}->attributes()->role;
                          if ((string)$participating_org->attributes()->role == "Implementing") {
                            //echo "yes";
                            $participating_org_implementing[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                          //Participating Organisation (Accountable)
                          if ((string)$participating_org->attributes()->role == "Accountable") {
                            $participating_org_accountable[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                        }
                        //Budget/Planned Disbursement
                        if ( count($activity->budget) > 0 || count($activity->{"planned-disbursement"}) > 0 ) {
                          $budget[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        
                        //Unique Identifier check
                        //Suck up all activity identifiers - check they start with the reporting org string
                        //We count by storing the activity id in an array
                        //if there is no identifier then set a dummy one to dump it into the 'bad' pile
                        if (!isset($activity->{'iati-identifier'})) { 
                            $iati_identifier = "noIdentifierGiven" . $i;
                            $i++;
                        } else {
                          $iati_identifier = (string)$activity->{'iati-identifier'};
                        }
                        if (isset($activity->{'reporting-org'}->attributes()->ref)) {
                          $reporting_org_ref = (string)$activity->{'reporting-org'}->attributes()->ref;
                          //echo $reporting_org_ref . PHP_EOL;
                          //echo $iati_identifier . PHP_EOL;
                          if (strpos($reporting_org_ref,$iati_identifier) == 0 ) {
                            //echo "yes";
                            $identifiers[$hierarchy]["good"][] = $iati_identifier;
                          } else {
                            //echo "no";
                            $identifiers[$hierarchy]["bad"][] = $iati_identifier;
                          }
                        } else {
                          $identifiers[$hierarchy]["bad"][] = $iati_identifier;
                        }
                          
                        
                        //Financial transaction (Commitment)
                        $transactions = $activity->transaction;
                        //if (count($transactions) == 0) {
                        //  echo $id;
                          //die;
                        //}

                        if (isset($transactions) && count($transactions) > 0) { //something not quite right here
                          //Loop through each of the elements
                          foreach ($transactions as $transaction) {
                            //print_r($transaction);
                            //Counts number of elements of this type in this activity
                            //$no_transactions[$hierarchy]++;
                            //$transaction_date = (string)$transaction->{'transaction-date'}->attributes()->{'iso-date'};
                            if (isset($transaction->{'transaction-type'})) {
                              $transaction_type = (string)$transaction->{'transaction-type'}->attributes()->{'code'};
                              if ($transaction_type == "C") {
                                $transaction_type_commitment[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                              if ($transaction_type == "D") {
                                $transaction_type_disbursement[$hierarchy][] = (string)$activity->{'iati-identifier'};
                                //Count the number of disbursements at this level
                                $no_disbursements[$hierarchy]++;
                                //now test it and count the passes
                                if (isset($transaction->{"receiver-org"})) {
                                  //We have a provider-org = pass!
                                  $no_tracable_transactions[$hierarchy]++;
                                }
                                //$no_disbursements = $no_incoming_funds = $no_tracable_transactions = array();
                              }
                              if ($transaction_type == "IF") {
                                //Count the number of IFs at this level
                                $no_incoming_funds[$hierarchy]++;
                                if (isset($transaction->{"provider-org"})) {
                                  //We have a provider-org = pass!
                                  $no_tracable_transactions[$hierarchy]++;
                                }
                              }
                              if ($transaction_type == "E") {
                                $transaction_type_expenditure[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                            }//if code attribute exists
                          }
                        }
                        //Going to need a count of disbursements and of IF transactions
                        //Then need to test each against a set of criteria
                            /*if ($transaction_type == NULL) {
                              $transaction_type = "Missing";
                              echo "missing";
                            }
                            if ($transaction_type !="D") {
                              echo $id;
                              //die;
                            }*/
                        
                        //Locations
                        //We can have more than one location, but they should add up to 100%
                        $locations = $activity->location;
                        //if (!isset($activities_with_location[$hierarchy])) {
                        //  $activities_with_location[$hierarchy] = 0;
                        //}
                        if (isset($locations) && count($locations) > 0) {
                          $activities_with_location[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          foreach ($locations as $location) {
                            if (isset($location->coordinates)) {
                              $activities_with_coordinates[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            }
                            if (isset($location->administrative)) {
                              if (isset($location->administrative->attributes()->adm1)) {
                                $adm1 = string($location->administrative->attributes()->adm1);
                              }
                              if (isset($location->administrative->attributes()->adm2)) {
                                $adm2 = string($location->administrative->attributes()->adm2);
                              }
                              if ( (isset($adm1) && len($adm1) > 0) || (isset($adm2) && len($adm2) > 0) ) {
                                $activities_with_adminstrative[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                            }
                          }
                        }
                        
                        //Sector
                        $sectors = $activity->sector;
                        if (isset($sectors) && count($sectors) > 0) {
                          //$activities_with_sector[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          foreach ($sectors as $sector) {
                            if (!isset($sector->attributes()->vocabulary)) {
                              $activities_sector_assumed_dac[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            } elseif ((string)$sector->attributes()->vocabulary == "DAC") {
                              //echo "DAC";
                              $activities_sector_declared_dac[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            }
                          }
                        }
                        //Last-updated-datetime
                        $last_updated = $activity->attributes()->{'last-updated-datetime'};
                        $last_updated = strtotime($last_updated);
                        if (!isset($most_recent[$hierarchy])) {
                          $most_recent[$hierarchy] = 0;
                        }
                        if ($last_updated > $most_recent[$hierarchy]) {
                          $most_recent[$hierarchy] = $last_updated;
                        }
                        
                        //Activity dates
                        $activity_dates = $activity->{"activity-date"};
                        //if (count($activity_dates) > 0) {
                        //if ($activity_dates !=NULL) {
                        //  $activities_with_at_least_one[$hierarchy]++;
                        //}
                        foreach ($activity_dates as $activity_date) {
                          //$attributes = array("end-actual","end-planned","start-actual","start-planned");
                         // $no_activity_dates[$hierarchy]++;
                          //foreach($attributes as $attribute) {
                          $type = (string)$activity_date->attributes()->type;
                          if ($type == "start-actual" || $type =="start-planned") {
                            $type = "start";
                          }
                          if ($type == "end-actual" || $type =="end-planned") {
                            $type = "end";
                          }
                          //$date = (string)$activity_date->attributes()->{'iso-date'};
                          //Special Case for DFID
                          //$date = (string)$activity_date;
                          //echo $date; die;
                         // $unix_time = strtotime($date);
                          //if ($unix_time) {
                          //  $year = date("Y",strtotime($date));
                          //} else {
                         //   $year = 0; //we could not parse the date, so store the year as 0
                         //// }
                          //$activity_by[$year][$hierarchy][$type]++;
                          
                          $activities_with_attribute[$hierarchy][$type][]=(string)$activity->{'iati-identifier'};
                           
                          //Languages
                          
                     // if($hierarchy == 2) {
                          $title_langs = $country_langs = $description_langs = $all_langs=  array(); //Reset each of these each run through
                          //Find default language of the activity
                          $default_lang = (string)$activity->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                          //echo $default_lang;
                          //Find recipient countries for this activity:
                          $recipient_countries = $activity->{"recipient-country"};
                            foreach ($recipient_countries as $country) {
                              $code = (string)$country->attributes()->code;
                              //Look up default language for this code:
                              $country_langs[] = look_up_lang($code);
                            }
                              //print_r($country_langs);
                          //Find all the different languages used on the title element
                          $titles = $activity->title;
                          foreach ($titles as $title) { //create an array of all declared languages on titles
                            $title_lang = (string)$title->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                            if ($title_lang == NULL) {
                              $title_langs[] = $default_lang;
                            } else {
                              $title_langs[] = $title_lang;
                            }
                            $title_lang = "";
                          }
                          //Find all the different languages used on the description element
                          $descriptions = $activity->description;
                          foreach ($descriptions as $description) { //create an array of all declared languages on titles
                            $description_lang = (string)$description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                            if ($description_lang == NULL) {
                              $description_langs[] = $default_lang;
                            } else {
                              $description_langs[] = $description_lang;
                            }
                            $description_lang = "";
                          }
                          //print_r($title_langs);
                          //die;
                          //Merge these arrays
                          $all_langs = array_merge($description_langs,$title_langs);
                          $all_langs = array_unique($all_langs);
                          //Loop through the country languiages and see if they are found on either the title or description
                          foreach ($country_langs as $lang) {
                            if (in_array($lang,$all_langs)) {
                              $activies_in_country_lang[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            }
                          }
                          //$description_lang = (string)$activity->description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                     // }
                          
                        }
                      
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  //if (isset($types)) {
    
    //echo "no_activities" . PHP_EOL;
    //print_r($no_activities);
    //echo "activities_with_at_least_one" . PHP_EOL;
    //print_r($activities_with_at_least_one);
    //echo "no_activity_dates" . PHP_EOL;
    //print_r($no_activity_dates);
    //echo "activity_by_year" . PHP_EOL;
    ksort($activity_by);
    //print_r($activity_by);
    //echo "activities_with_attribute" . PHP_EOL;
    //print_r($activities_with_attribute);
    //foreach($types as $attribute_name=>$attribute) {
    ///  echo $attribute_name;
//foreach($attribute as $hierarchy=>$values) {
     //   echo $hierarchy;
     //   print_r(array_count_values($values));
     // }
   // }
   
    //echo count($participating_org_implementing[0]); die;
    $found_hierarchies = array_unique($found_hierarchies);
    sort($found_hierarchies);
    //die;
    return array(//"types" => $types,
                  "no-activities" => $no_activities,
                  "activities_with_at_least_one" => $activities_with_at_least_one,
                  "no_activity_dates" => $no_activity_dates,
                  "activity_by_year" => $activity_by,
                  "hierarchies" => array_unique($found_hierarchies),
                  "activities_with_attribute" => $activities_with_attribute,
                  "document_links" => $document_links,
                  "result_element" => $result_element,
                  "conditions" => $conditions,
                  "participating_org_accountable" => $participating_org_accountable,
                  "participating_org_implementing" => $participating_org_implementing,
                  "budget" => $budget,
                  "identifiers" => $identifiers,
                  "transaction_type_commitment" => $transaction_type_commitment,
                  "transaction_type_disbursement" => $transaction_type_disbursement,
                  "transaction_type_expenditure" => $transaction_type_expenditure,
                  "no_disbursements" => $no_disbursements,
                  "no_tracable_transactions" => $no_tracable_transactions,
                  "no_incoming_funds" => $no_incoming_funds,
                  "activities_with_location" => $activities_with_location,
                  "activities_with_coordinates" => $activities_with_coordinates,
                  "activities_with_adminstrative" => $activities_with_adminstrative,
                  "activities_sector_assumed_dac" => $activities_sector_assumed_dac,
                  "activities_sector_declared_dac" => $activities_sector_declared_dac,
                  "most_recent" => $most_recent,
                  "activies_in_country_lang" => $activies_in_country_lang
                );
  //} else {
  //  return FALSE;
  //}
}

/*
 * Given a 2 letter country code, this will look up the default language
 * name: look_up_lang
 * @param
 * @return
 * 
 */

function look_up_lang ($code) {
  global $country_map;
  if (isset($country_map[$code])) {
    return $country_map[$code];
  } else {
    return NULL;
  }
}


?>
