<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user=require_login(true);
$tables=[
'plan'=>['table'=>'business_plans','fields'=>['section','title','details','status'],'owned'=>true],
'goals'=>['table'=>'goals','fields'=>['title','objective','target_date','progress','status'],'owned'=>true],
'accounting'=>['table'=>'transactions','fields'=>['reference','customer','type','amount','category','status','transaction_date'],'owned'=>true],
'payments'=>['table'=>'payments','fields'=>['reference','customer','method','amount','status'],'owned'=>true],
'notifications'=>['table'=>'notifications','fields'=>['title','message','audience','channel','status'],'owned'=>true],
'rewards'=>['table'=>'rewards','fields'=>['name','points','redemptions','status'],'owned'=>true],
'content'=>['table'=>'contents','fields'=>['title','content_type','status'],'owned'=>false],
'users'=>['table'=>'users','fields'=>['organization_id','name','email','role','status'],'owned'=>true],
'support'=>['table'=>'support_tickets','fields'=>['subject','customer','priority','status'],'owned'=>true]];
function tenant_clause(array $user,bool $owned,array &$params):string{if(!$owned||is_admin($user))return'';$params['organization_id']=(int)$user['organization_id'];return' WHERE organization_id=:organization_id';}
try{
$pdo=db();$resource=$_GET['resource']??'dashboard';$method=$_SERVER['REQUEST_METHOD'];
if($resource==='me')json_response(['ok'=>true,'user'=>$user]);
if($resource==='reports'){
require_permission($user,'reports','view');$org=(int)$user['organization_id'];
$stmt=$pdo->prepare("SELECT type,category,SUM(amount) total FROM transactions WHERE organization_id=? AND status='Paid' GROUP BY type,category");$stmt->execute([$org]);$rows=$stmt->fetchAll();$income=0;$expenses=0;foreach($rows as $row){if($row['type']==='Income')$income+=(float)$row['total'];else $expenses+=(float)$row['total'];}
json_response(['ok'=>true,'summary'=>['income'=>$income,'expenses'=>$expenses,'net_income'=>$income-$expenses,'owner_equity'=>$income-$expenses],'breakdown'=>$rows]);}
if($resource==='profile'){
require_permission($user,'setup','view');$org=(int)($user['organization_id']??1);
if($method==='GET'){$stmt=$pdo->prepare('SELECT business_name,owner_name,industry,email,phone,address,setup_progress FROM business_profile WHERE organization_id=? LIMIT 1');$stmt->execute([$org]);json_response(['ok'=>true,'profile'=>$stmt->fetch()]);}
$input=json_decode(file_get_contents('php://input'),true)?:[];$fields=['business_name','owner_name','industry','email','phone','address'];$data=array_intersect_key($input,array_flip($fields));if(!$data)json_response(['ok'=>false,'message'=>'Enter your business details.'],422);$sets=array_map(fn($f)=>"`$f`=:$f",array_keys($data));$data['organization_id']=$org;$stmt=$pdo->prepare('UPDATE business_profile SET '.implode(',',$sets).',setup_progress=100 WHERE organization_id=:organization_id');$stmt->execute($data);json_response(['ok'=>true,'message'=>'Business profile saved.']);}
if($resource==='permissions'){
if(!is_admin($user))json_response(['ok'=>false,'message'=>'Only administrators can change permissions.'],403);
$input=json_decode(file_get_contents('php://input'),true)?:[];$id=(int)($input['id']??0);$permissions=$input['permissions']??[];
if($method!=='PATCH'||!$id||!is_array($permissions))json_response(['ok'=>false,'message'=>'Invalid permission update.'],422);
$allowed=['setup','plan','goals','notifications','accounting','reports','content','users','performance','support'];$clean=[];
foreach($permissions as $module=>$level)if(in_array($module,$allowed,true)&&in_array($level,['none','view','edit'],true))$clean[$module]=$level;
$stmt=$pdo->prepare("UPDATE users SET permissions=? WHERE id=? AND role!='Administrator'");$stmt->execute([json_encode($clean),$id]);json_response(['ok'=>true,'message'=>'Permissions updated.']);}
if($resource==='dashboard'){
$params=[];$scope=is_admin($user)?'':' AND organization_id=:organization_id';if(!is_admin($user))$params['organization_id']=(int)$user['organization_id'];
$queries=['revenue'=>"SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='Income' AND status='Paid' AND MONTH(transaction_date)=MONTH(CURDATE())$scope",'customers'=>"SELECT COUNT(DISTINCT customer) FROM transactions WHERE 1=1$scope",'pending'=>"SELECT COUNT(*) FROM transactions WHERE status IN ('Pending','Overdue')$scope",'tickets'=>"SELECT COUNT(*) FROM support_tickets WHERE status!='Resolved'$scope"];$metrics=[];
foreach($queries as $key=>$sql){$stmt=$pdo->prepare($sql);$stmt->execute($params);$metrics[$key]=$key==='revenue'?(float)$stmt->fetchColumn():(int)$stmt->fetchColumn();}
$profile=$pdo->query('SELECT * FROM business_profile ORDER BY id LIMIT 1')->fetch();json_response(['ok'=>true,'metrics'=>$metrics,'profile'=>$profile,'activity'=>[],'role'=>$user['role']]);}
if(!isset($tables[$resource]))json_response(['ok'=>false,'message'=>'Unknown resource.'],404);
require_permission($user,$resource,$method==='GET'?'view':'edit');$config=$tables[$resource];$table=$config['table'];$fields=$config['fields'];$owned=$config['owned'];
if($method==='GET'){$params=[];$where=tenant_clause($user,$owned,$params);if($resource==='users'&&!is_admin($user)){$where=' WHERE id=:current_id OR organization_id=:organization_id';$params=['current_id'=>(int)$user['id'],'organization_id'=>(int)$user['organization_id']];}$select=$resource==='users'?'id,organization_id,name,email,role,status,permissions,created_at':'*';$stmt=$pdo->prepare("SELECT $select FROM `$table`$where ORDER BY id DESC");$stmt->execute($params);json_response(['ok'=>true,'items'=>$stmt->fetchAll(),'canEdit'=>permission_level($user,$resource)==='edit','isAdmin'=>is_admin($user)]);}
$input=json_decode(file_get_contents('php://input'),true)?:[];
if($method==='POST'){$data=array_intersect_key($input,array_flip($fields));if(!$data)json_response(['ok'=>false,'message'=>'Please complete the required fields.'],422);if($owned&&!is_admin($user))$data['organization_id']=(int)$user['organization_id'];if($resource==='users'){if(!is_admin($user)){$data['role']='Staff';$data['organization_id']=(int)$user['organization_id'];}$data['password']=(string)($input['password']??'Welcome123!');}$columns=array_keys($data);$marks=array_map(fn($f)=>":$f",$columns);$stmt=$pdo->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$marks).')');$stmt->execute($data);json_response(['ok'=>true,'message'=>'Record created.','id'=>(int)$pdo->lastInsertId()],201);}
$id=(int)($input['id']??($_GET['id']??0));if(!$id)json_response(['ok'=>false,'message'=>'Invalid record.'],422);$scopeParams=['id'=>$id];$scopeSql='id=:id';if($owned&&!is_admin($user)){$scopeSql.=' AND organization_id=:organization_id';$scopeParams['organization_id']=(int)$user['organization_id'];}
if($method==='PATCH'){$data=array_intersect_key($input,array_flip($fields));if(!$data)json_response(['ok'=>false,'message'=>'Invalid update.'],422);$sets=array_map(fn($f)=>"`$f`=:$f",array_keys($data));$stmt=$pdo->prepare('UPDATE `'.$table.'` SET '.implode(',',$sets).' WHERE '.$scopeSql);$stmt->execute(array_merge($data,$scopeParams));json_response(['ok'=>true,'message'=>'Record updated.']);}
if($method==='DELETE'){if($resource==='users')$scopeSql.=" AND role!='Administrator'";$stmt=$pdo->prepare("DELETE FROM `$table` WHERE $scopeSql");$stmt->execute($scopeParams);if(!$stmt->rowCount())json_response(['ok'=>false,'message'=>'This account cannot be deleted.'],403);json_response(['ok'=>true,'message'=>'Record deleted.']);}
json_response(['ok'=>false,'message'=>'Method not allowed.'],405);
}catch(PDOException $e){json_response(['ok'=>false,'message'=>'Database migration is required. Import migration_auth.sql, then refresh.'],503);}
