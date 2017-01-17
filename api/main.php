<?php 
/*
 * This file is part of the time.log-box package.
 *
 * (c) F.Jaton / log-box.ch <question@log-box.ch> / 17.01.2017
 *
 * This SOftware is distributed under GNU GENERAL PUBLIC LICENSE GPL3 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * THIS FILE is the SLIM Basic API for REST implementation for time.log-box  
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;       
require 'vendor/autoload.php';
include_once 'config/config.inc.php';

use Firebase\JWT\JWT;


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
$container['jwtsecret'] = function($c){ return $c['settings']['secret'];};


/* add Middleware to SLIM-API */             
$app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => ["/"],
    "passthrough" => ["/login"],
    "secret" => $container['jwtsecret'],
    "attribute" => "jwt",
    "algorithm" => ["HS256", "HS384"]   
]));

/* add default-route  */
/******************************************************************************
 * Default route for logging in
 * only this route is accessible without authentication
 * 
 * 1. Check for Authorization
 * 2. Create and calculate the JWT   
 * 
 * Return the hashed Token as JSON for further identification 
 *****************************************************************************/
$app->post('/login', function (Request $request, Response $response) {

      $bodyData = $request->getParsedBody();
      $user = filter_var($bodyData['user'], FILTER_SANITIZE_STRING);
      $password = filter_var($bodyData['password'], FILTER_SANITIZE_STRING);
      $pwd = sha1 ($password);
      
      $stmt = $this->db->prepare("SELECT id, email, name, firstname
                                  FROM user
                                  WHERE user.email=:user
                                  AND user.password=:pwd ");
                                  
      $stmt->bindValue(':user', $user, PDO::PARAM_STR);
      $stmt->bindValue(':pwd', $pwd, PDO::PARAM_STR);
      $stmt->execute();
      
      if($row = $stmt->fetch()) { 
       
        $user_id = $row['id'];
        $user_email = $row['email'];
        $user_name = $row['name'];
        $user_firstname = $row['firstname'];
 
        // prepare vars and generate JWT
        $now = new DateTime();
        $future = new DateTime("now +2 hours");
        $jti = substr(str_shuffle(str_repeat(implode('', array_merge(range('A','Z'),range('a','z'),range(0,9)) ),2)), 0, 16);
        //PHP7 coud be so easy ;-) //random_bytes(16) and encode in base 64 or base 62;
        
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "sub" => $user_firstname,
            "user_id" => $user_id,
            "logbox_mac" =>  "mobile-client",
            
        ];
        //$secret = getenv("JWT_SECRET");
        $secret = $this['jwtsecret'];
        $token = JWT::encode($payload, $secret, "HS256");
        $data["status"] = "ok";
        $data["token"] = $token;
        
        return $response->withStatus(201)
          ->withHeader("Content-Type", "application/json; charset=utf-8")
          ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); 
            
    }else {
        return $response->withStatus(401)
        ->withHeader("Content-Type", "application/json; charset=utf-8");
    }
});


/******************************************************************************
 * Default route for getting statistics, deliver Data for actual WeekOfYear 
 * in a Format for google-chart
 * needs user_id
 * 
 * Return as JSON a Array with summarized worjed hours each Weekday:
 *****************************************************************************/
$app->get('/stats/week', function (Request $request, Response $response) {

    $jwt_token = (array)$request->getAttribute('jwt');
    
    $jsonData="";
    $dayOfWeek =  array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
    
    //get a array with projects on which a entry has stored between the date
    $stmt = $this->db->prepare("SELECT e.project_id id, p.name name 
          FROM entries e, projects p
          WHERE YEARWEEK(`start`) = YEARWEEK(NOW())
          AND `user_id`=:user_id
          AND p.id = e.project_id
          GROUP BY e.project_id, p.name "); 
       
    $stmt->bindValue(':user_id', $jwt_token['user_id'], PDO::PARAM_STR);
    $stmt->execute();
    while($row = $stmt->fetch()) {
      $projectsWithProgress[$row['id']]= $row['name']; 
    }
    $stmt = null;
    
    $jsonData .='[["WEEKDAY"';
    foreach ($projectsWithProgress as &$proj) {
      $jsonData .= ',"'.$proj.'"';
    }
    $jsonData .= ']';
    
    foreach ($dayOfWeek as &$day) {
        $jsonData.= ',["'.$day.'"'; 
        foreach ($projectsWithProgress as $projId => $projName) {
           
            $sqlquery="SELECT sum(round((TIME_TO_SEC(TIMEDIFF(`stop`,`start`))/60/60),2)) h 
            FROM entries e WHERE YEARWEEK(`start`) = YEARWEEK(NOW()) 
            AND`user_id`=".$jwt_token['user_id']." 
            AND DATE_FORMAT(`start`,'%a')='$day' 
            AND e.project_id=".$projId;
            
            $stmt = $this->db->query($sqlquery);    
            while($row = $stmt->fetch()) {
              $jsonData.= ",".floatval (($row['h'] != "") ? $row['h'] : 0 ); 
            }
            $stmt = null;
        }
        $jsonData.= "]"; 
    }
    $jsonData.= "]"; 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});

 
/******************************************************************************
 * Route for getting statistics with filter-funcinolity, 
 * deliver Data-Array for actual Set of Data in a compatible Format 
 *  for google-chart
 * 
 *Uses different Filter Values 
 * 
 * Return as JSON a Array with summarized worked hours each Weekday:
 *****************************************************************************/
$app->get('/stats', function (Request $request, Response $response) {
    
    $jsonData="";
    $jwt_token = (array)$request->getAttribute('jwt');
    $getParams = $request->getQueryParams();
      
    $start_date = $getParams['start_date'];
    $end_date =  $getParams['end_date'];
    
    //FIXME:
    // not nice but quick'n dirty to convert ISO_8601(json) to mysql datetime 
    // https://en.wikipedia.org/wiki/ISO_8601 )
    if($end_date !=""){
    $end_date = str_replace ( ".000Z" , "" , $end_date) ;
    $end_date = str_replace ( "T" , " " , $end_date);
    }
    if($start_date !=""){
    $start_date = str_replace ( ".000Z" , "" , $start_date) ;
    $start_date = str_replace ( "T" , " " , $start_date);
    }
    
    //get a array with all days on which a entry has stored  between the date
    $stmt = $this->db->prepare("SELECT distinct DATE_FORMAT(`start`,'%Y-%m-%d') d 
          FROM entries e
          WHERE `user_id`=:user_id
          AND `start` >= :start_date 
          AND `stop` <= ADDDATE(:end_date, +1)"); 
       
    $stmt->bindValue(':user_id', $jwt_token['user_id'], PDO::PARAM_STR);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->execute();
    while($row = $stmt->fetch()) {
      $workedDays[] = $row['d']; 
    }
    $stmt = null;
    
    
    //get a array with projects on which a entry has stored between the date
    $stmt = $this->db->prepare("SELECT e.project_id id, p.name name 
          FROM entries e, projects p
          WHERE `user_id`=:user_id
          AND p.id = e.project_id
		      AND `start` >= :start_date 
          AND `stop` <= ADDDATE(:end_date, +1) 
          GROUP BY e.project_id, p.name "); 
       
    $stmt->bindValue(':user_id', $jwt_token['user_id'], PDO::PARAM_STR);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->execute();
    while($row = $stmt->fetch()) {
      $projectsWithProgress[$row['id']]= $row['name']; 
    }
    $stmt = null;
    
    $jsonData .='[["Datum"';
    foreach ($projectsWithProgress as &$proj) {
      $jsonData .= ',"'.$proj.'"';
    }
    $jsonData .= ']';
    
    foreach ($workedDays as &$day) {
        $jsonData.= ',["'.$day.'"'; 
        foreach ($projectsWithProgress as $projId => $projName) {

          $stmt = $this->db->prepare("SELECT sum(round((TIME_TO_SEC(TIMEDIFF(`stop`,`start`))/60/60),2)) h 
          FROM entries e
          WHERE `user_id`=:user_id 
          AND e.project_id=:projId 
          AND `start` >= :start_date 
          AND `stop` <= ADDDATE(:end_date, +1)"); 
             
          $stmt->bindValue(':user_id', $jwt_token['user_id'], PDO::PARAM_STR);
          $stmt->bindValue(':projId', $projId, PDO::PARAM_INT);
          $stmt->bindValue(':start_date', $day, PDO::PARAM_STR);
          $stmt->bindValue(':end_date', $day, PDO::PARAM_STR);
          $stmt->execute();
   
          while($row = $stmt->fetch()) {
            $jsonData.= ",".floatval (($row['h'] != "") ? $row['h'] : 0 ); 
          }
          $stmt = null;   
          
        }                
        $jsonData.= "]";
         
    }
    $jsonData.= "]"; 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});

 
/******************************************************************************
 * Route for getting a list of active Project for the user 
 * needs user_id
 * 
 * Return as JSON a List of Projects with:
 * project_id, project_name, client_id, client_logo, client_name, 
 * client_project_owner_id, client_project_owner_name, client_project_owner_tel
 *****************************************************************************/
$app->get('/projects', function (Request $request, Response $response) {
    
    // List of Projects with: 
    // project_id(String), project_name(String), client_id(String), 
    // client_logo(img), client_name(String),  client_project_owner_id(String), 
    // client_project_owner_name(String), client_project_owner_tel(String),
    
    $jwt_token = (array)$request->getAttribute('jwt');
    
    $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                      c.logo client_logo, c.company client_name, 
                      c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                      client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c, `projects_has_user` pu  
               WHERE p.client_id = c.id 
               AND p.isactive = TRUE
               AND p.id = pu.projects_id
               AND pu.user_id = ".$jwt_token['user_id'];     
    $stmt = $this->db->query($sqlquery); 
    
    $jsonData="[";
	  if($stmt->rowCount() >0){
        while($row = $stmt->fetch()) {
            $jsonData .= json_encode($row).",";    
        }
        $jsonData = substr ( $jsonData , 0, (strlen ($jsonData)-1) );
    }
	  $jsonData .= "]";  
  
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});


/******************************************************************************
 * Route for checking for a open Entry 
 * needs logbox_mac varchar(255), user_id int(11) from JWT-Token
 * check for a open entry and return project_id and start datetimestamp if found
 * used to mark project-logbutton green an set open-timestamp
 * 
 * Return as JSON:
 * project_id, start 
 *****************************************************************************/
$app->get('/openentry', function (Request $request, Response $response) {
   

      $jwt_token = (array)$request->getAttribute('jwt');
      $sqlquery="SELECT `project_id`,`start` FROM `entries` WHERE `logbox_mac`='".$jwt_token['logbox_mac']."' AND `user_id`=".$jwt_token['user_id']." AND stop IS NULL";
      $stmt = $this->db->query($sqlquery); 
  	  $jsonData="[";
  	  if($stmt->rowCount() >0){
          while($row = $stmt->fetch()) {
              $jsonData .= json_encode($row).",";    
          }
          $jsonData = substr ( $jsonData , 0, (strlen ($jsonData)-1) );
      }
  	  $jsonData .= "]";  
      $response->getBody()->write($jsonData);   
      return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});


/******************************************************************************
 * Route for inserting new Entry with actual Timestamp 
 * has to close a previous open Entry of that user with actual Timestamp
 * is used to open or reopen new entry in same/other project (stop-watch-like)	
 * and stop logging time by sending Parameter "go_to_standby":1
 * 
 * Return as JSON:
 * project_id, project_name, client_id, client_logo, client_name, 
 * client_project_owner_id, client_project_owner_name, client_project_owner_tel 
 *****************************************************************************/
$app->post('/project/entry', function (Request $request, Response $response) {
   
      $jsonData="";
      $jwt_token = (array)$request->getAttribute('jwt');
      $bodyData = $request->getParsedBody();
      
      $project_id = (int)filter_var($bodyData['project_id'], FILTER_SANITIZE_STRING);
      $go_to_standby = filter_var($bodyData['go_to_standby'], FILTER_SANITIZE_STRING);
      $notes = $this->db->quote( filter_var($bodyData['message'], FILTER_SANITIZE_STRING) );
      
      $logbox_ip = "unset";
      if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
          $logbox_ip = $_SERVER['REMOTE_ADDR'];
      }
      
//IF entry has no STOP timestamp set it, bevore open new one
      $sqlquery="SELECT `id`,`logbox_mac`,`project_id`,`user_id`,`stop` FROM `entries` WHERE `logbox_mac`='".$jwt_token['logbox_mac']."' AND `user_id`=".$jwt_token['user_id']." AND stop IS NULL";
      logall($sqlquery);
      $stmt = $this->db->query($sqlquery); 
      while($row = $stmt->fetch()) { 
        $updatestmt = $this->db->query("UPDATE `entries` set `stop` = NOW(), `notes` = $notes WHERE `id`=".$row['id']);
      }

//then insert a new entry with just the START timestamp, if not have to go to standby   
      if($go_to_standby == 0){
        $sqlquery="INSERT INTO `entries` (`logbox_mac`,`logbox_ip`,`project_id`,`user_id`,`start`) values('".$jwt_token['logbox_mac']."', '$logbox_ip', $project_id, ".$jwt_token['user_id'].", NOW())";
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
