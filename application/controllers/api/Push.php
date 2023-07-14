<?php

require APPPATH.'libraries/REST_Controller.php';
define( 'API_ACCESS_KEY', 'AAAAWXr6x5Q:APA91bEkH6XaXPXovvLVqF1KNpWRmwkrBh8MYfI7sDli23ElPoudINMtscIWAiGMLX0WaaCIO_efRruXA1nSNLdm6R0Kfdlchg7xFgP8_HBBCfEr8nJqV61s4dDtIx5Ksyul_pLIl4N5' ); // get API access key from Google/Firebase API's Console

class Push extends REST_Controller{

  public function __construct(){
    
    parent::__construct();
}


public function index_get(){
    $this->response(array("status" => 0, "message" => "This Function Not Work"), 400);
}

public function index_post(){
        
        $token = array( $this->input->post('token')); //Replace this with your device token
        $url = 'https://fcm.googleapis.com/fcm/send';   //For firebase, use https://fcm.googleapis.com/fcm/send
        
        // Modify custom payload here
        $msg = array
        (
                'title'     => $this->input->post('title'),  //Title
                'body'      => $this->input->post('body'),   // body,
                'image'     => $this->input->post('image'),  //Image
                'name'     => $this->input->post('name')  //name
        );
        $fields = array
        (
            'registration_ids'      => $token,
            'data'    => $msg
        );
        
        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $url); 
        curl_setopt( $ch,CURLOPT_POST, true );
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        
        if($result){
            $this->response(array("data"=>$result, "status" => 1, "message" => "Data Found"), REST_Controller::HTTP_OK);
        }else{
            $this->response(array("status" => 0, "message" => "Data Not Found"), 400);
        }
    }

}
?>
