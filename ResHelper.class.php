<?php
class ResHelper{
    private static $lastException   = null;
    private static $lastTimes      =  null;
    private function __construct()
    {
    }
    private static function loadCSV($filename){
        $delimiter = ",";
        if(!file_exists($filename) || !is_readable($filename)) {
            return [];
        }
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter,"'")) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    try {
                        $data[] = array_combine($header, $row);
                    }catch(Exception $exception){
                        return [];
                    }
            }
            fclose($handle);
        }else{
            return [];
        }
        return $data;
    }
    private static function sendData($data,$settings){
        foreach ($data as $k => $row){
            $c = curl_init($settings['action']);
            curl_setopt_array($c,[
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $row,
                CURLOPT_RETURNTRANSFER => TRUE,
            ]);
            try{
                $result = curl_exec($c);
                self::$lastTimes[$k] = curl_getinfo($c,CURLINFO_TOTAL_TIME)."s";
                if (curl_error($c)){
                    throw new Exception(curl_error($c));
                }
            }catch (Exception $exception){
                self::$lastException = $exception;
                return false;
            }
        }
        return true;
    }
    public static function sendFromCSV($filename,$settings){
        return self::sendData(self::loadCSV($filename), $settings);
    }
    public static function getLastException(){
        return self::$lastException;
    }
    public static function getLastTimes(){
        return self::$lastTimes;
    }
}