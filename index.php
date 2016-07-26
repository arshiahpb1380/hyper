<?php
ob_start();
define('API_KEY','197850164:AAFdeK-42BPrrkSRoMqPhUPgpd98yZPy0QQ');
$admin = '46853604';
include("telegram.php");
$telegram = new Telegram(API_KEY);
function httpt($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
function download($file,$urll){
  $url  = $urll;
  $path = $file;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $data = curl_exec($ch);
  curl_close($ch);
  file_put_contents($path, $data);
}
$update = json_decode(file_get_contents('php://input'));
if(isset($update->message->text)){
  $matches = explode(" ", $update->message->text);
  $chat_id = $update->message->chat->id;
  if($matches[0] == '/start'){
    var_dump(httpt('sendMessage',[
      'chat_id'=>$update->message->chat->id,
      'text'=>"سلام خوش اومدی\nمن ربات فرمت نویسی متن هستم\nدستور\n/help\nرو ارسال کن",
      'parse_mode'=>'HTML',
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [
            ['text'=>'Time ⏱','callback_data'=>'time']
          ]
        ]
      ])
   ]));
  }
  if($update->message->text == '/help'){
    var_dump(httpt('sendMessage',[
      'chat_id'=>$update->message->chat->id,
      'text'=>"
سلام خوش اومدی
لیست دستورات
👇
/bold [متن]
بولد نویسی متن
/italic [متن]
کج نویسی متن
/code [متن]
کد نویسی متن
/info
دریافت مشخصات شما


سازنده:@arshiahp
",
      'parse_mode'=>'HTML',
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [
            ['text'=>'رفتن به حالت اینلاین','switch_inline_query'=>'']
          ]
        ]
      ])
   ]));
  }
  if($matches[0] == '/bold'){
    $text = str_replace('/bold','',$update->message->text);
    var_dump(httpt('sendMessage',[
      'chat_id'=>$update->message->chat->id,
      'text'=>'<b>' . ($text) . '</b>',
      'parse_mode'=>'HTML'
    ]));
  }
  if($matches[0] == '/italic'){
    $text = str_replace('/italic','',$update->message->text);
    var_dump(httpt('sendMessage',[
      'chat_id'=>$update->message->chat->id,
      'text'=>"<i>".($text)."</i>",
      'parse_mode'=>'HTML'
    ]));
  }
  if($matches[0] == '/code'){
    $text = str_replace('/code','',$update->message->text);
    var_dump(httpt('sendMessage',[
      'chat_id'=>$update->message->chat->id,
      'text'=>"<code>".($text)."</code>",
      'parse_mode'=>'HTML'
    ]));
  }
  if($matches[0] == '/info'){
    $id = $update->message->from->id;
    $name = $update->message->from->first_name;
    $username = $update->message->from->username;
    $s = httpt('getUserProfilePhotos',['user_id'=>$id]);
    if($s->result->photos[0][3]->file_id or $s->result->photos[0][2]->file_id or $s->result->photos[0][1]->file_id){
      $telegram->sendChatAction(array('chat_id'=>$chat_id,'action'=>'upload_photo'));
      $send = $s->result->photos[0][3]->file_id;
      httpt('sendPhoto',[
        'chat_id'=>$chat_id,
        'photo'=>$send,
        'caption'=>"Your ID : $id\n🔴🔴🔴\nYour Username : @$username\n🔵🔵🔵",
        'reply_markup'=>json_encode([
          'inline_keyboard'=>[
            [
              ['text'=>"$username",'url'=>"https://telegram.me/$username"]
            ]
          ]
        ])
      ]);
    }
    if(!$s->result->photos[0][1]->file_id){
      httpt('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"Your ID : $id\n🔴🔴🔴\nYour Username : @$username\n🔵🔵🔵"
      ]);
    }
  }
}
if(isset($update->message->sticker)){
  httpt('sendMessage',[
    'chat_id'=>$update->message->chat->id,
    'text'=>'Emoji : '.($update->message->sticker->emoji)
  ]);
}
if(isset($update->inline_query)){
  $inline_id = $update->inline_query->id;
  $inline_m = $update->inline_query->query;
  httpt('answerInlineQuery',[
    'inline_query_id'=>$inline_id,
    'results'=>json_encode([
      [
        'type'=>'article',
        'id'=>base64_encode(1),
        'title'=>'Bold '.($inline_m),
        'input_message_content'=>[
          'message_text'=>'<b>'.($inline_m).'</b>',
          'parse_mode'=>'HTML'
        ]
      ],
      [
        'type'=>'article',
        'id'=>base64_encode(2),
        'title'=>'Italic '.($inline_m),
        'input_message_content'=>[
          'message_text'=>'<i>'.($inline_m).'</i>',
          'parse_mode'=>'HTML'
        ]
      ],
      [
        'type'=>'article',
        'id'=>base64_encode(3),
        'title'=>'Code '.($inline_m),
        'input_message_content'=>[
          'message_text'=>'<code>'.($inline_m).'</code>',
          'parse_mode'=>'HTML'
        ]
      ]
    ])
  ]);
}
if(isset($update->callback_query)){
  $id = $update->callback_query->id;
  $q = $update->callback_query->data;
  $js = json_decode(file_get_contents('http://api.gpmod.ir/time/'));
  if($q == 'time'){
    httpt('answerCallbackQuery',[
      'callback_query_id'=>$id,
      'text'=>$js->ENtime
    ]);
  }
}
