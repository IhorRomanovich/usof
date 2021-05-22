<?php
$source = file_get_contents('example.php');
$tokens = token_get_all($source);

foreach ($tokens as $token) {
   if (is_string($token)) {
       // простая однабуквенная лексема
       echo $token;
   } else {
       // массив с лексемой
       list($id, $text) = $token;

       switch ($id) {
           case T_DOC_COMMENT:
               // нет действий для комментариев
               break;

           default:
               // все остальное -> выводим как есть
               echo $text;
               break;
       }
   }
}