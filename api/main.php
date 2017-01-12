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


  

$app->get('/projects', function (Request $request, Response $response) {
    
    // List of Projects with: 
    // project_id(String), project_name(String), client_id(String), 
    // client_logo(img), client_name(String),  client_project_owner_id(String), 
    // client_project_owner_name(String), client_project_owner_tel(String),

    $jsonData="";
    $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                      c.logo client_logo, c.company client_name, 
                      c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                      client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c  
               WHERE p.client_id = c.id ";
    $stmt = $this->db->query($sqlquery); 
    while($row = $stmt->fetch()) {
      $jsonData .= json_encode($row);    
    }
               
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});



/*

Bitte so lassen ;-)
Danke & Gruss Flo

$app->post('/project/entry', function (Request $request, Response $response) {
   
//"Insert new entry for Project by ID with actual Timestamp
//has to close a previous open Entry of that user with actual Timestamp
//is used to reopen new entry in same/other project (stop-watch-like)"	

//200, single entry: id int(11), logbox_mac varchar(255), logbox_ip varchar(255), 
//project_id int(11), user_id int(11), start datetime, stop datetime, 
//category int(11), notes varchar(255)  


      
      $jsonData="";  
      $project_id = $request->getAttribute('id');     
      $bodyData = $request->getParsedBody();
      $user_id = filter_var($bodyData['user_id'], FILTER_SANITIZE_STRING);
      $logbox_mac = filter_var($bodyData['logbox_mac'], FILTER_SANITIZE_STRING);
      $go_to_standby = filter_var($bodyData['go_to_standby'], FILTER_SANITIZE_STRING);
      
      $jsonData .="!---$bodyData, $project_id, $user_id, $logbox_mac, $go_to_standby---!";
      
      $logbox_ip = "unset";
      if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
          $logbox_ip = $_SERVER['REMOTE_ADDR'];
      }
        

//      $project_id = $request->getAttribute('id');
//      $user_id = $request->getAttribute('user_id');
//     $jsonData .= "|--".$parsedBody ."--".$user_id."--|";
//      $logbox_mac = $request->getAttribute('logbox_mac');      
//      $logbox_ip = "unset";
//      if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
//          $logbox_ip = $_SERVER['REMOTE_ADDR'];
//      }
//      $go_to_standby = $request->getAttributes('go_to_standby');

      
  //IF entrey has no STOP timestamp set it
      $sqlquery="SELECT `id`,`logbox_mac`,`project_id`,`user_id`,`stop` FROM `entries`
                 WHERE `logbox_mac` = $logbox_mac 
                 AND `project_id` = $project_id 
                 AND `user_id` = $user_id 
                 AND stop IS NULL";
      $query=mysql_query($sqlquery);          
      if(@mysql_num_rows($query)>0){
        list($entry_id, $logged_logbox_mac, $logged_project_id, $logged_user_id, $stop)=mysql_fetch_row($query);
        $query=mysql_query("UPDATE `entries` set `stop` = NOW() WHERE `id`= $entry_id");
      }

      
  //then insert a new entry wit just the START timestamp, if not have to go to standby   
      if($go_to_standby = 1){
        $sqlquery="INSERT INTO `entries` (`logbox_mac`,`logbox_ip`,`project_id`,`user_id`,`start`) values($logbox_mac, $logbox_ip, $project_id, $user_id, NOW())";
        $jsonData .= $sqlquery;
        $query=mysql_query($sqlquery);
        $jsonData .= mysql_error(); 
      } 

      
  //get the actual Project to return
      $sqlquery="SELECT p.id, p.name, c.id, c.logo, c.company, c.id, concat(c.name,' ',c.firstname) name, c.phone 
               FROM `projects` p,`clients` c  
               WHERE p.id = $project_id
               AND p.client_id = c.id "; 
                              
      $query=mysql_query($sqlquery);
      
      if(@mysql_num_rows($query)>0){
          for($i=0; list($project_id, $project_name, $client_id, $client_logo, $client_name, $client_project_owner_id, $client_project_owner_name, $client_project_owner_tel)= mysql_fetch_row($query);$i++) {
           $jsonData .="{project_id:'$project_id', project_name:'$project_name', client_id:'$client_id', client_logo:'$client_logo', client_name:'$client_name',client_project_owner_id:'$client_project_owner_id',client_project_owner_name:'$client_project_owner_name',client_project_owner_tel:'$client_project_owner_tel'}\n";   
          }
      }    
             
    //$jsonData .= mysql_error(); 
      
    //$response->getBody()->write($jsonData);
    $response->getBody()->write(json_encode($jsonData));
    return $response->withHeader('Content-Type', 'application/json');
});
*/


$app->run();
