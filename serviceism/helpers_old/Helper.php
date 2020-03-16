<?php
namespace serviceism\helpers;

/**
 * 
 */
class Helper
{
	public function encrypt($val){
        $key='ismhcm2019#$';
        $sResult = '';
        for($i=0;$i<strlen($val);$i++){
                $sChar    = substr($val, $i, 1);
                $keyChar  = substr($key, ($i % strlen($key)) - 1, 1);
                $sChar    = chr(ord($sChar) + ord($keyChar));
                $sResult .= $sChar;
        }
        return $this->enbase64($sResult);
    }

    public function decrypt($val){
        $key='ismhcm2019#$';
        $sResult = '';
        $val   = $this->debase64($val);
        for($i=0;$i<strlen($val);$i++){
                $sChar    = substr($val, $i, 1);
                $keyChar  = substr($key, ($i % strlen($key)) - 1, 1);
                $sChar    = chr(ord($sChar) - ord($keyChar));
                $sResult .= $sChar;
        }
        return $sResult;
    }

    private function enbase64($val){
        $sBase64 = base64_encode($val);
        return strtr($sBase64, '+/', '-_');
    }
    
    private function debase64($val){
        $sBase64 = strtr($val, '-_', '+/');
        return base64_decode($sBase64);
    }

    public function GroupName($stream)
    {
        $arr = [
            0=>"General",
            1=>"Close",
            2=>"Private",
        ];

        return $arr[$stream];
    }

    public function serverName()
    {
        $server = "https://taskmgmt.telkomsel.co.id/api";
        return $server;
    }

    public function getPicturesProfile()
    {
        $server = "https://hcm.telkomsel.co.id/index.php?r=site%2Fpictures&pid=";
        return $server;
    }
}