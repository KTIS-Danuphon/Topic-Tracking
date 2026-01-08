<?php
class Encrypt_data{

    public function EnCrypt_pass($data){//$data ข้อมูลที่ Key เข้ามา เพื่อที่จะเข้ารหัส เช่น พาสเวิด , $IV ตัวบิทที่เข้ารหัส อย่างน้อยคือตัวเลข 16 หลัก เพื่อป้องการการเข้ารหัส ซ้ำ  กรณ๊นี้ใช้ id auto ที่รันจากฐานข้อมูล
        //$ID = $this->CheckDigit($IV);1122334455667788
        
        $dataRe = openssl_encrypt($data, "seed", "KTIS_group_it_ElephantTeam",0,"1122334455667788");
        $dataRe = $this->base64url_encode($dataRe);
        return $dataRe;
    }

    public function DeCrypt_pass($data){
        //$ID = $this->CheckDigit($IV);1122334455667788
        $dataRe = $this->base64url_decode($data);
        $dataRe = openssl_decrypt($dataRe, "seed", "KTIS_group_it_ElephantTeam",0,"1122334455667788");
        return $dataRe;
    }

    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
      }
      
    public function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), 4 - ((strlen($data) % 4) ?: 4), '=', STR_PAD_RIGHT));
      }

}

// $spit = new Encrypt_data();
// echo "Token :".$spit->DeCrypt_pass('ItZXr3N/QwGr2/UxeQr6KXAWovdOLoPARK/j1zpCu2c=','1');
// echo "<br>";
// echo "Now :".date('Y-m-d H:i:s', strtotime('0 seconds'))
//echo $spit->EnCrypt_pass("pily","1");
// echo MD5($spit->EnCrypt_pass("123456789","25"));
?>