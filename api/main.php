<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;       
require 'vendor/autoload.php';
include_once 'config/config.inc.php';

$app = new \Slim\App(["settings" => $config]);


$container = $app->getContainer();   
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
                                   $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}; 


$app->get('/stats/week', function (Request $request, Response $response) {
    
/*OPTIONAL;
    of_category_id Array of int(11)
    project_id int(11)
    search_text varchar(255)
    start_date date
    end_date date
    user_id int(11) (just if admin!)"	"get List of all Entries of that user within actual Week(7days)
    is used to show worked time this week"
*/

    
    
    $jsonData="";
    
    $dayOfWeek =  array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $projectsWithProgress =  array(9,10,11);
    $user_id=3;
    
    //$arrayDataTable =  array('WEEKDAY','Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $projectsWithProgress  = [
    '9' => ["Proj 1"],
    '10' => ["Proj 2"],
    '11' => ["Proj 3"],
    ];
    
    $jsonData .="['WEEKDAY'";
    foreach ($projectsWithProgress as &$proj) {
      $jsonData .= ",'".$proj[0]."'";
    }
    $jsonData .= "]";
    
/*    
  [
      ['WEEKDAY', 'Log-Box, Universal Link',
          'Redesign Web, Swisscom AG',
          'Mobile App, Kuster AG',
          'Req. Analyse, FHNW',
          'Int. Buchhaltung',
          'Int. Diversa'],
      ['Mo.', 2, 1, 3, 1, 0, 0],
      ['Di.', 3, 0.5, 1, 1, 0, 0],
      ['Mi.', 0.5, 4, 6, 0, 0, 0],
      ['Do.', 1, 0.25, 1, 2, 0, 0],
      ['Fr.', 0.5, 1, 2, 1, 0, 1],
      ['Sa.', 0, 0, 0, 0, 3, 0.5,]
  ]
*/    

    foreach ($dayOfWeek as &$day) {
        $jsonData.= ",['$day'"; 
        foreach ($projectsWithProgress as $projId => $projName) {
            $sqlquery="SELECT sum(round((TIME_TO_SEC(TIMEDIFF(`stop`,`start`))/60/60),2)) h from entries e WHERE `user_id`=$user_id AND DATE_FORMAT(`start`,'%a')='$day' AND e.project_id=".$projId;
            $stmt = $this->db->query($sqlquery);    
            while($row = $stmt->fetch()) {
              $jsonData.= ",".floatval (($row['h'] != "") ? $row['h'] : 0 ); 
            }
            $stmt = null;
        }
        $jsonData.= "]"; 
    }
    
    //$jsonData .= json_encode($arrayDataTable); 

    //$stmt = $this->db->query($sqlquery); 
    //while($row = $stmt->fetch()) {
    //  $jsonData .= json_encode($row).",";    
    //}
    //FIXME: replace that String hack with a solkution that generates a clean and secure JSON
    //$jsonData = substr_replace($jsonData, "]", strrpos ( $jsonData , ",")); 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});



  

$app->get('/projects', function (Request $request, Response $response) {
    
    // List of Projects with: 
    // project_id(String), project_name(String), client_id(String), 
    // client_logo(img), client_name(String),  client_project_owner_id(String), 
    // client_project_owner_name(String), client_project_owner_tel(String),

    $jsonData="[";
    $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                      c.logo client_logo, c.company client_name, 
                      c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                      client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c  
               WHERE p.client_id = c.id ";
    $stmt = $this->db->query($sqlquery); 
    while($row = $stmt->fetch()) {
      $jsonData .= json_encode($row).",";    
    }
    //FIXME: replace that String hack with a solkution that generates a clean and secure JSON
    $jsonData = substr_replace($jsonData, "]", strrpos ( $jsonData , ",")); 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});




$app->get('/openentry', function (Request $request, Response $response) {
   
  /*
  * needs logbox_mac varchar(255), user_id int(11) from JWT-Token
  * check for a open entry and return project_id and start datetimestamp if found
  * used to mark project-logbutton green an set open-timestamp
  */
      $getParams = $request->getQueryParams();
      
      //FIXME: take values from JWT-Token
      $user_id = ($getParams['user_id'] != "") ? (int)$getParams['user_id'] : 3;
      $logbox_mac = ($getParams['logbox_mac'] != "") ? $getParams['logbox_mac'] : "138.174.117.190";

      $sqlquery="SELECT `project_id`,`start` FROM `entries` WHERE `logbox_mac`='$logbox_mac' AND `user_id`=$user_id AND stop IS NULL";
      $stmt = $this->db->query($sqlquery); 
	  
	  $jsonData="[";
	  
      while($row = $stmt->fetch()) {
        $jsonData .= json_encode($row).",";    
      }
	  
	  $jsonData = substr_replace($jsonData, "]", strrpos ( $jsonData , ",")); 
      
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});





$app->post('/project/entry', function (Request $request, Response $response) {
   
//"Insert new entry for Project by ID with actual Timestamp
//has to close a previous open Entry of that user with actual Timestamp
//is used to reopen new entry in same/other project (stop-watch-like)"	

//200, single entry: id int(11), logbox_mac varchar(255), logbox_ip varchar(255), 
//project_id int(11), user_id int(11), start datetime, stop datetime, 
//category int(11), notes varchar(255)  


      $jsonData="";
      $bodyData = $request->getParsedBody();
      $user_id = (int)filter_var($bodyData['user_id'], FILTER_SANITIZE_STRING);
      $logbox_mac = filter_var($bodyData['logbox_mac'], FILTER_SANITIZE_STRING);
      $project_id = (int)filter_var($bodyData['project_id'], FILTER_SANITIZE_STRING);
      $go_to_standby = filter_var($bodyData['go_to_standby'], FILTER_SANITIZE_STRING);
      
      $user_id =  3;
      $logbox_mac = "138.174.117.190";
      
      
      $logbox_ip = "unset";
      if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
          $logbox_ip = $_SERVER['REMOTE_ADDR'];
      }
      
     //$jsonData.= $user_id." | ".$project_id." | ".$logbox_mac." | ".$go_to_standby." | ".$logbox_ip;
      
//IF entrey has no STOP timestamp set it
      $sqlquery="SELECT `id`,`logbox_mac`,`project_id`,`user_id`,`stop` FROM `entries` WHERE `logbox_mac`='$logbox_mac' AND `project_id`=$project_id AND `user_id`=$user_id AND stop IS NULL";
      $stmt = $this->db->query($sqlquery); 
      while($row = $stmt->fetch()) { 
        $updatestmt = $this->db->query("UPDATE `entries` set `stop` = NOW() WHERE `id`=".$row['id']);
      }

//then insert a new entry with just the START timestamp, if not have to go to standby   
      if($go_to_standby == 0){
        $sqlquery="INSERT INTO `entries` (`logbox_mac`,`logbox_ip`,`project_id`,`user_id`,`start`) values('$logbox_mac', '$logbox_ip', $project_id, $user_id, NOW())";
        $stmt = $this->db->query($sqlquery); 
      }
      
//get the actual Project to return
      $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                        c.logo client_logo, c.company client_name, 
                        c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                        client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c  
               WHERE p.id = $project_id AND p.client_id = c.id"; 
      $stmt = $this->db->query($sqlquery); 
      while($row = $stmt->fetch()) {
        $jsonData .= json_encode($row);    
      }

    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});


$app->run();
