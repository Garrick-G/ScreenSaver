

<?php

  date_default_timezone_set('CET');
  /*
  *ics class, initialized to convert the ics files to objects
  */
  class ics{
    //called by ics object to convert file to array
    function icsToArray($file){
      $icsString = file_get_contents($file);
      $icsDates = array();
      $icsData = explode("BEGIN", $icsString);
      foreach ($icsData as $key => $value){
        $icsDatesMeta [$key] = explode ("\n", $value);
      }
      foreach ($icsDatesMeta as $key => $value){
        foreach ($value as $subKey => $subValue){
          $icsDates = $this->getICSDates($key, $subKey, $subValue, $icsDates);
        }
      }
      return $icsDates;
    }
    //function called by icsToArray function to get dates of the object
    function getICSDates($key, $subKey, $subValue, $icsDates){
      if($key!=0 && $subKey==0){
        $icsDates [$key] ["BEGIN"] = $subValue;
      }
      else {
        $subValueArr = explode( ":", $subValue, 2);
        if (isset ($subValueArr [1] )){
          $icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
        }
      }
      return $icsDates;
    }
  }



function calTestFunc($arrayOfFiles, $colorsArray){
  //gets the string value between a start and end value (starts at the first occurence of start and ends at first occurence of end);
  function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
  }


  //initialize array to hold all the events
  $arrayOfEvents = array();
  //Creates multi dimensional array of objects
  $x = 1;
  $replacing = array('.ics');
  foreach($arrayOfFiles as $key => $item){
    $obj = new ics();
    $icsEvents = $obj->icsToArray($item);
    $icsEvents[1]['X-WR-CALNAME'] = str_replace($replacing, '', $key);
    array_push($arrayOfEvents, $icsEvents);
    $x++;
  }

  $timeZone = 'America/Chicago';

  function cmp($object1, $object2){
    return ((isset($object1 ['DTSTART;VALUE=DATE']) ? $object1 ['DTSTART;VALUE=DATE'] : $object1 ['DTSTART']) > (isset($object2 ['DTSTART;VALUE=DATE']) ? $object2 ['DTSTART;VALUE=DATE'] : $object2 ['DTSTART']));
  }



  //loop through multi dimensional array of objects
  $tempArr = array();
  foreach ($arrayOfEvents as $event) {
    $x = 1;
    //saving the name of the calendar
    $tempName = str_replace($replacing, "", $event[1]['X-WR-CALNAME']);
      //deleting the values that arent events
      /*if(isset($event[$x]['BEGIN'])){
      if($event[$x]['BEGIN'] !== 'VEVENT'){ unset($event[$x]); }
      else{continue;}
      }*/

    unset($event[1]);
    if(trim($event[2]['BEGIN']) === ':VTIMEZONE'){
      unset($event[2]);
      unset($event[3]);
      unset($event[4]);
    }
    //Loops through each array within the multidimensional array
    foreach($event as $value){
      $x++;
      //Depending on the key, get the start date
      foreach($value as $key => $item){
        if (strpos($key, 'TZID') !== false){
          $tempKey = str_split($key);
          if(strpos($key, 'DTSTART') !== false){
            array_splice($tempKey, 7);
          }
          elseif(strpos($key, 'DTEND') !== false){
            array_splice($tempKey, 5);
          }
          unset($value[$key]);
          $key = implode('', $tempKey);
          $item = trim($item);
          $value[$key] = $item."Z";
        }
      }
      $start = isset($value ['DTSTART;VALUE=DATE']) ? $value['DTSTART;VALUE=DATE'] : $value['DTSTART'];
      //convert start date to DateTime object
      $startDt = new DateTime($start);
      //sets the timezone for the DateTime object
      //if(isset($value['DTSTART'])){ $startDt->setTimeZone(new DateTimeZone($timeZone));}
      //sets startdate variable to a specific date format depending on the key
      isset($value ['DTSTART;VALUE=DATE']) ? $startDate = $startDt->format('m/d/y') : $startDate = $startDt->format('m/d/y h:i');
      //Depending on the key, set end date
      $end = isset($value ['DTEND;VALUE=DATE']) ? $value ['DTEND;VALUE=DATE'] : $value ['DTEND'];
      //convert end date to DateTime object
      $endDt = new DateTime($end);
      //set the timezone for the DateTime object
      //if(isset($value['DTEND'])){ $endDt->setTimeZone(new DateTimeZone($timeZone));}
      //if the length of the event is more than a day: create new dates for time between start and end
      if(ceil(($startDt->diff($endDt))->format('%d.%h')) > 1 or isset($value[''])){

        //loop through until i equals length of event in days
        for ($i=0; $i < ceil(($startDt->diff($endDt))->format('%d.%h')) ; $i++) {
          //through each loop, copying the current array to a new array
          $newEvent = $value;
          //set temporary start date equal to the copied events start date value
          $tempDate = isset($newEvent ['DTSTART;VALUE=DATE']) ? $newEvent ['DTSTART;VALUE=DATE'] : $newEvent['DTSTART'];
              //convert temporary start date to DateTime object
              $tempStart = new DateTime($tempDate);
              //set timezone for start date
              // $tempStart -> setTimeZone(new DateTimeZone($timeZone));
              //convert start date to formatted date
              $tempStartDt = $tempStart->format('m/d/y');
              $tempEnd = isset($newEvent ['DTEND;VALUE=DATE']) ? $newEvent ['DTEND;VALUE=DATE'] : $newEvent['DTEND'];
              $tempEndDt = new DateTime($tempDate);
              $tempEndDate = $tempEndDt->format('m/d/y');
              //set start date in copied event to start + i
              if(isset($newEvent['DTSTART;VALUE=DATE'])){
                $newEvent ['DTSTART;VALUE=DATE'] = date('Ymd', strtotime($tempStartDt.'+'.$i.'days'));
              }
              else{
                $newEvent ['DTSTART'] = date('YmdThis', strtotime($startDate.'+'.$i.'days'));
                $tempDate = $newEvent['DTSTART'];
                //convert temporary start date to DateTime object
                $tempStart = new DateTime($tempDate);
                //$tempStart -> setTimeZone(new DateTimeZone($timeZone));
                //convert start date to formatted date
                $tempStartDt = $tempStart->format('m/d/y');
                if(ceil($tempStartDt==$endDt->format('m/d/y'))){
                  $newEvent['DTSTART;VALUE=DATE'] = date('Ymd', strtotime($startDate.'+'.$i.'days'));
                  unset($newEvent['DTSTART']);
                  //var_dump($tempStartDt==$endDt->format('m/d/y'));
                  //echo ceil(($tempStart->diff($endDt))->format('%d.%h'));
                }
                else{
                  $newEvent ['DTSTART;VALUE=DATE'] = date('Ymd', strtotime($startDate.'+'.$i.'days'));
                  unset($newEvent['DTSTART']);
                  $newEvent['DTEND;VALUE=DATE'] = date('Ymd', strtotime($tempEndDate.'+'.$i.'days'));
                  unset($newEvent['DTEND']);
                }
              }

              //set a new key in the array equal to the calendar name
              $newEvent['CALNAME'] = $tempName;
              //echo $value['CALNAME'];
              //push the new event to tempArr array
              array_push($tempArr, $newEvent);
            }
          }

          elseif (isset($value['RRULE'])) {
            $rule = trim($value['RRULE']);
            $start = isset($value['DTSTART'])?$value['DTSTART']:$value['DTSTART;VALUE=DATE'];
            $startDt = new DateTime($start);
            $startDate = $startDt -> format('m/d/y');
            $today = date('m/d/y', strtotime('+ 3 days'));
            $maxDay = new DateTime($today);
            if(trim($value['RRULE']) === 'FREQ=DAILY'){
              //if the start date is before or on 3 days after todays date
              if ($startDate <= date('m/d/y', strtotime('+3 days'))){
                if($startDate <= date('m/d/y')){
                  for($x = 0; $x < 4; $x++){
                    $newEvent = $value;
                    echo date('YmdTHis', strtotime('+'.$x.'days'));
                    isset($newEvent['DTSTART'])?$newEvent['DTSTART']=date('YmdTHis',strtotime('+'.$x.'days')):$newEvent['DTSTART;VALUE=DATE']=date('Ymd', strtotime('+'.$x.'days'));
                    isset($newEvent['DTEND'])?$newEvent['DTEND']=date('YmdTHis',strtotime('+'.$x.'days')):$newEvent['DTEND;VALUE=DATE']=date('Ymd', strtotime('+'.$x.'days'));
                    $newEvent['CALNAME'] = $tempName;
                    array_push($tempArr, $newEvent);
                  }
                }
                elseif($startDate > date('m/d/y') and $startDate < date('m/d/y', strtotime('+ 3 days'))){
                  for($x = 1; $x <= ($startDt->diff($maxDay))->format('%d'); $x++){
                    $newEvent = $value;
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                    isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                    echo $newEvent['DTSTART'];
                    /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=date('YmdTHis',strtotime('+'.$x.'days')):$newEvent['DTSTART;VALUE=DATE']=date('Ymd', strtotime('+'.$x.'days'));
                    isset($newEvent['DTEND'])?$newEvent['DTEND']=date('YmdTHis',strtotime('+'.$x.'days')):$newEvent['DTEND;VALUE=DATE']=date('Ymd', strtotime('+'.$x.'days'));*/
                    $newEvent['CALNAME'] = $tempName;
                    array_push($tempArr, $newEvent);
                  }
                }
                else{continue;}
              }
            }
            elseif(strpos($rule, 'FREQ=WEEKLY') !== false){
              if(strpos($rule, 'INTERVAL') !== false){
                $interval = get_string_between($rule, 'INTERVAL=',';');
                $rule = str_replace('FREQ=WEEKLY;WKST=SU;INTERVAL='.$interval.';BYDAY=', '', $rule);
              }
              else{
                $rule = str_replace('FREQ=WEEKLY;BYDAY=', '', $rule);
              }

              if(strpos($rule, ',') !== false){
                $repeat = explode(',', $rule);
                foreach($repeat as $key){
                  switch($key){
                    case 'MO':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Mon'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'TU':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Tue'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$repeatDay:$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      */
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'WE':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Wed'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'TH':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Thu'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'FR':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Fri'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'SA':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Sat'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    case 'SU':
                    for($x = 0; $x < 4; $x++){
                      $tempTime = new DateTime();
                      $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                      $tempTime->add(new DateInterval('P'.$x.'D'));
                      if ($tempTime->format('D') == 'Sun'){
                        $newEvent = $value;
                        if(isset($newEvent['DTSTART'])){
                          $start = new dateTime($value['DTSTART']);
                          $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                          $newEvent['DTSTART'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        if(isset($newEvent['DTEND'])){
                          $end = new dateTime($value['DTEND']);
                          $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                          $newEvent['DTEND'] = $repeatDay;
                        }
                        else{
                          $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                        }
                        /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                        isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                        $newEvent['CALNAME'] = $tempName;
                        array_push($tempArr, $newEvent);
                      }
                    }
                    break;
                    default:
                    echo "error";
                  }
                }
              }
              else{
                $tempTime = new DateTime();
                $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                switch($rule){
                  case 'MO':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Mon'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'TU':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Tue'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'WE':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Wed'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'TH':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Thu'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'FR':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Fri'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'SA':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Sat'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  case 'SU':
                  for($x = 0; $x < 4; $x++){
                    $tempTime = new DateTime();
                    $tempTime ->setTimeZone( new DateTimeZone($timeZone));
                    $tempTime->add(new DateInterval('P'.$x.'D'));
                    if ($tempTime->format('D') == 'Sun'){
                      $newEvent = $value;
                      if(isset($newEvent['DTSTART'])){
                        $start = new dateTime($value['DTSTART']);
                        $repeatDay = date($tempTime->format('Ymd').$start->format('His'));
                        $newEvent['DTSTART'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      if(isset($newEvent['DTEND'])){
                        $end = new dateTime($value['DTEND']);
                        $repeatDay = date($tempTime->format('Ymd').$end->format('His'));
                        $newEvent['DTEND'] = $repeatDay;
                      }
                      else{
                        $newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');
                      }
                      /*isset($newEvent['DTSTART'])?$newEvent['DTSTART']=$tempTime->format('YmdTHis'):$newEvent['DTSTART;VALUE=DATE']=$tempTime->format('Ymd');
                      isset($newEvent['DTEND'])?$newEvent['DTEND']=$tempTime->format('YmdTHis'):$newEvent['DTEND;VALUE=DATE']=$tempTime->format('Ymd');*/
                      $newEvent['CALNAME'] = $tempName;
                      array_push($tempArr, $newEvent);
                    }
                  }
                  break;
                  default:
                  echo "error";
                }
              }
            }
          }
          //if only a one day event: add the calendar name and push the copied event
          else{
            $newEvent = $value;
            //isset($newEvent ['DTSTART;VALUE=DATE']) ? $newEvent ['DTSTART;VALUE=DATE'] = date('m/d/y', strtotime($startDate)) : $newEvent ['DTSTART'] = date('m/d/y h:i', strtotime($startDate));
            if(isset($newEvent['DTSTART'])){
              $start = $newEvent['DTSTART'];
              $startDt = new DateTime($start);
              $startDt->setTimeZone(new DateTimeZone($timeZone));
              $startDate = $startDt -> format('m/d/y H:i');
              $newEvent['DTSTART'] = date('YmdTHis', strtotime($startDate));
              $end = $newEvent['DTEND'];
              $endDt = new DateTime($end);
              $endDt->setTimeZone(new DateTimeZone($timeZone));
              $endDate = $endDt -> format('m/d/y H:i');
              //echo $startDate;
              $newEvent['DTEND'] = date('YmdTHis', strtotime($endDate));
            }
            $newEvent['CALNAME'] = $tempName;
            //echo $value['SUMMARY'];
            array_push($tempArr, $newEvent);
          }
        }
      }
      usort($tempArr, 'cmp');
      $dateArray = array();
      //$html = '<table><tr><td>Event</td><td>Start At</td><td>End At</td></tr>';
      foreach($tempArr as $icsEvent){
        if( isset($icsEvent ['DTSTART;VALUE=DATE'])){
          $start= $icsEvent ['DTSTART;VALUE=DATE'];
          $startDt = new DateTime($start);
          $startDate = $startDt->format('m/d/y');
        }
        else{
          $start = $icsEvent ['DTSTART'];
          $startDt = new DateTime($start);
          $startDate = $startDt->format('m/d/y');
        }
        $tempTime = new DateTime();
        $tempTime ->setTimeZone( new DateTimeZone($timeZone));
        if($tempTime->format('m/d/y') >  $startDate or $startDate > ($tempTime->add(new DateInterval('P4D'))->format('m/d/y'))){
          continue;
        }
        if(!array_key_exists($startDate, $dateArray)){
          $dateArray[$startDate] = array($icsEvent);
        }
        else{
          array_push($dateArray[$startDate], $icsEvent);
        }
        $end = isset($icsEvent ['DTEND;VALUE=DATE']) ? $icsEvent ['DTEND;VALUE=DATE'] : $icsEvent ['DTEND'];
        $endDt = new DateTime($end);
        $endDate = $endDt->format('m/d/y h:i');
        $eventName = $icsEvent['SUMMARY'];
        //  $html .= '<tr><td>'.$eventName.'</td><td>'.$startDate.'</td><td>'.$endDate.'</td></tr>';
      }
      $keyArr = array();
      $keyArr = array_keys($dateArray);
      $x = 0;




      foreach($dateArray as $event){
        usort($event, 'cmp');
        $testDate = new DateTime($keyArr[$x]);
        $now = new DateTime();
        if($now->format('m/d/y') === $testDate->format('m/d/y')){echo '<div class="event"><h3>Today</h3>';}
        else{echo '<div class="event"><h3>'.$testDate->format('D, M d').'</h3>';}
        foreach($event as $item){
          //echo $item['CALNAME'];
          if(isset($item ['DTSTART;VALUE=DATE'])){
            $start = $item ['DTSTART;VALUE=DATE'];
            $startDt = new DateTime($start);
          //  $startDt->setTimeZone(new DateTimeZone($timeZone));
            //$startDate = $startDt->format('m/d/y');

            if(isset($item['DTEND'])){
              $end = $item ['DTEND'];
              $endDt = new DateTime($end);
              $endDt->setTimeZone(new DateTimeZone($timeZone));
              $endDate = $endDt->format ('h:iA');
              echo '<li class="'.$item['CALNAME'].'">'.$item['SUMMARY'].'<br>'.'until '.$endDate.'</li>';
            }
            else{
              echo '<li class="'.$item['CALNAME'].'">'.$item['SUMMARY'].'</li>';
            }
            //continue;
          }
          else{
            $start = $item ['DTSTART'];
            $startDt = new DateTime($start);
            //$startDt->setTimeZone(new DateTimeZone($timeZone));
            $startDate = $startDt->format('h:iA');
            $end = isset($item ['DTEND;VALUE=DATE']) ? $item ['DTEND;VALUE=DATE'] : $item ['DTEND'];
            $endDt = new DateTime($end);
            //$endDt->setTimeZone(new DateTimeZone($timeZone));
            //var_dump(($startDt->format('m/d/y')));
            $endDate = $endDt->format('h:iA');
            echo '<li class="'.$item['CALNAME'].'">'.$item['SUMMARY'].'<br>'.$startDate.' - '.$endDate.'</li>';
          }

        }
        $x++;
        echo '</div>';
      }
      /*foreach($dateArray as $icsEvent){
        $html = '<table><tr><td>Event</td><td>Start At</td><td>End At</td></tr>';
        foreach ($icsEvent as $event) {
        $start = isset($event ['DTSTART;VALUE=DATE']) ? $event ['DTSTART;VALUE=DATE'] : $event ['DTSTART'];
        $startDt = new DateTime($start);
        $startDt->setTimeZone(new DateTimeZone($timeZone));
        $startDate = $startDt->format('m/d/y h:i');
        $end = isset($event ['DTEND;VALUE=DATE']) ? $event ['DTEND;VALUE=DATE'] : $event ['DTEND'];
        $endDt = new DateTime($end);
        //$endDt->setTimeZone(new DateTimeZone($timeZone));
        $endDate = $endDt->format('m/d/y h:i');
        //echo ($endDt);
        //echo abs(round((strtotime($endDate) - strtotime($startDate))/86400));
        //if(date('m/d/y h:i') >  $startDate or $startDate > Date('m/d/y h:i', strtotime('+ 4 days'))){
          //continue;
        //}
        $eventName = $event['SUMMARY'];
        $html .= '<tr><td>'.$eventName.'</td><td>'.$startDate.'</td><td>'.$endDate.'</td></tr>';
        echo $html.'</table>';
        }
      }*/
      //print_r($dateArray['03/01/19']);
      /*usort($icsEvents, 'cmp');

      $html = '<table><tr><td>Event</td><td>Start At</td><td>End At</td></tr>';
      foreach($icsEvents as $icsEvent){
        $start = isset($icsEvent ['DTSTART;VALUE=DATE']) ? $icsEvent ['DTSTART;VALUE=DATE'] : $icsEvent ['DTSTART'];
        $startDt = new DateTime($start);
        $startDt->setTimeZone(new DateTimeZone($timeZone));
        $startDate = $startDt->format('m/d/y h:i');
        $end = isset($icsEvent ['DTEND;VALUE=DATE']) ? $icsEvent ['DTEND;VALUE=DATE'] : $icsEvent ['DTEND'];
        $endDt = new DateTime($end);
        $endDate = $endDt->format('m/d/y h:i');
        if(date('m/d/y h:i') >  $endDate){
          continue;
        }
        $eventName = $icsEvent['SUMMARY'];
        $html .= '<tr><td>'.$eventName.'</td><td>'.$startDate.'</td><td>'.$endDate.'</td></tr>';
      }
      echo $html.'</table>';*/
    ?>
    <script type="text/javascript">
    <?php foreach($colorsArray as $key => $value){ ?>
     var y = document.getElementsByClassName('<?php echo $key ?>');
      if(y.length > 0){
        for(var x = 0; x < y.length; x++){
          y[x].style.color = '<?php echo $value ?>';
        }
      }
    <?php  }?>
    </script>
<?php
}
?>
