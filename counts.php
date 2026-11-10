<?php require_once __DIR__.'/config/config.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
if(!is_logged_in()){http_response_code(401);exit('{"error":"login required"}');}
$uid=current_user_id();session_write_close();
echo json_encode(unread_counts($conn,$uid));
