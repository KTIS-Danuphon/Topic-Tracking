<?php
class Util
{
  // Method of input value sanitization
  public function testInput($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);

    $specialChars = array(
      '/', '\\', '?', '|', '||', '=', ':', ';', '"', "'", ',', '!',
      '^', '&', '$', '%', '*', '{', '}', '[', ']', '(', ')', '..'
    );
    $specialKeywords = array(
      'select', 'from', 'insert', 'into', 'delete', 'update', 'http', 'https', 'php', 'html', 'js', 'java', 'Jscript', 'function',
      'css', 'scss', 'script', 'chr', 'socget', 'ini', 'windows',  'JPG', 'file', 'txt',
      'shells', 'etc', 'count', 'max', 'min', 'row', 'get', 'post', 'put', 'or', 'if', 'sys', 'dart', 'exe', 'system32', 'sysdate',
      'asp', 'aspx'
    );

    $filteredInput = str_replace($specialChars, '', $data);
    $filteredInput = preg_replace('/\b(' . implode('|', $specialKeywords) . ')\b/i', '***', $filteredInput);
    $data = trim($filteredInput);
    // $data = trim($data);
    return $data;
  }
}
