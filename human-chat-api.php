<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
require_once 'config/database.php';require_once 'config/human-chat.php';
$db=(new Database())->getConnection();ensureHumanChatTables($db);
$input=json_decode((string)file_get_contents('php://input'),true) ?: [];$action=(string)($input['action']??'');
function out(array $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
if($action==='start'){
 $token=bin2hex(random_bytes(32));$name=cleanChatName((string)($input['name']??''),'Guest');
 $s=$db->prepare('INSERT INTO human_chat_sessions(public_token,visitor_name) VALUES(?,?)');$s->execute([$token,$name]);$id=(int)$db->lastInsertId();
 $m=$db->prepare("INSERT INTO human_chat_messages(session_id,sender,sender_name,message) VALUES(?,'system','Serendib Pathways',?)");$m->execute([$id,'Your request has reached our travel team. A person can now join this conversation.']);
 out(['token'=>$token,'status'=>'waiting','visitor_name'=>$name]);
}
$session=findHumanSession($db,(string)($input['token']??''));if(!$session)out(['error'=>'Chat session not found.'],404);
if($action==='send'){
 if($session['status']==='closed')out(['error'=>'This conversation has been closed.'],409);
 $message=trim((string)($input['message']??''));if($message===''||mb_strlen($message)>2000)out(['error'=>'Enter a message under 2,000 characters.'],422);
 $s=$db->prepare("INSERT INTO human_chat_messages(session_id,sender,sender_name,message) VALUES(?,'visitor',?,?)");$s->execute([$session['id'],$session['visitor_name'],$message]);
 $db->prepare('UPDATE human_chat_sessions SET last_activity=NOW() WHERE id=?')->execute([$session['id']]);out(['success'=>true]);
}
if($action==='poll'){
 $after=max(0,(int)($input['after']??0));$s=$db->prepare('SELECT id,sender,sender_name,message,created_at FROM human_chat_messages WHERE session_id=? AND id>? ORDER BY id');$s->execute([$session['id'],$after]);
 out(['messages'=>$s->fetchAll(PDO::FETCH_ASSOC),'status'=>$session['status'],'agent_name'=>$session['agent_name']]);
}
out(['error'=>'Unsupported action.'],400);
