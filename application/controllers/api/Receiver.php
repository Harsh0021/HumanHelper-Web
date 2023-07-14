<?php

require APPPATH.'libraries/REST_Controller.php';

class Receiver extends REST_Controller{

  public function __construct(){
    
    parent::__construct();
    
    $this->load->model(array("api/receiver_model"));
    $this->load->database();
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
    date_default_timezone_set('Asia/Kolkata');
  }

  public function list_get(){
    
    $user_id = $this->input->get('user_id');
    $distance = $this->input->get('distance'); 
    $city_id = $this->input->get('city_id');
    $service_id = $this->input->get('service_id');
    $blood_group_id = $this->input->get('blood_group_id');
   
    $data_array = array(
        'receiver.service_id'=>$service_id,
        'receiver.blood_group_id'=>$blood_group_id,
        'receiver.status'=>1,
        'receiver.action'=>0
        );
    
      if(isset($user_id) and isset($distance))      {
      if(!empty($user_id) and !empty($distance))      {
          $data = $this->receiver_model->get_receiver_list($data_array, $user_id, $distance);  
      }else {$data = false;}
      }else {$data = false;}
      
        if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "Receiver Found"
              ), REST_Controller::HTTP_OK);
        }else{
              $this->response(array(
                "status" => 0,
                "message" => "Receiver Not Found"
              ), 400);
        }
  }

  public function index_get(){
    
    $id = $this->input->get('id');
    $user_id = $this->input->get('user_id');
    $data = false;
    
    if(isset($id)){
        if(!empty($id)){
            $data = $this->receiver_model->get_receiver($id, $user_id);    
        }
    }
          
    if($data){
        $this->response(array(
        "data"=>$data,
        "status" => 1,
        "message" => "Data Found"
      ), REST_Controller::HTTP_OK);
   }else{
        $this->response(array(
        "status" => 0,
        "message" => "Data Not Found"
        ), 400);
    }
  }
  
  public function response_get(){
    
    $id = $this->input->get('id');
    $user_id = $this->input->get('user_id');
    $data = false;
    
    if(isset($id)){
        if(!empty($id)){
            $data = $this->receiver_model->get_donorview($id, $user_id);    
        }
    }
          
    if($data){
        $this->response(array(
        "data"=>$data,
        "status" => 1,
        "message" => "Data Found"
      ), REST_Controller::HTTP_OK);
   }else{
        $this->response(array(
        "status" => 0,
        "message" => "Data Not Found"
        ), 400);
    }
  }
  
  public function index_post(){
      
      $data = false;
      $error = 'Donor Added not Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('service_id', 'service_id', 'required');
      $this->form_validation->set_rules('blood_group_id', 'blood_group_id', 'required');
      $this->form_validation->set_rules('title', 'title', 'required');
      $this->form_validation->set_rules('patient_name', 'patient_name', 'required');
      $this->form_validation->set_rules('patient_age', 'patient_age', 'required');
      $this->form_validation->set_rules('address', 'address', 'required');
      $this->form_validation->set_rules('latitude', 'latitude', 'required');
      $this->form_validation->set_rules('longitude', 'longitude', 'required');
      $this->form_validation->set_rules('pincode', 'pincode', 'required');
      
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $units = $this->input->post('units');
          $service_id = $this->input->post('service_id');
          $blood_group_id = $this->input->post('blood_group_id');
          $title = $this->input->post('title');
          $disc = $this->input->post('disc');
          $pincode = $this->input->post('pincode');
          $patient_name = $this->input->post('patient_name');
          $patient_age = $this->input->post('patient_age');
          $hospital_name = $this->input->post('hospital_name');
          $bed_no = $this->input->post('bed_no');
          $ward_no = $this->input->post('ward_no');
          $doctor_name = $this->input->post('doctor_name');
          $address = $this->input->post('address');
          $latitude = $this->input->post('latitude');
          $longitude = $this->input->post('longitude');
          $receiver_id = $this->input->post('receiver_id');
          $action = $this->input->post('action');
          $donor_userid = $this->input->post('donor_user_id');
          if(!isset($action)){
              $action =0;
          }elseif(empty($action)){$action = 0;}
          
          
          $city = $this->db->select('city_id as id')->from('location_pincode')->where('pincode', $pincode) ->get()->result();
          
          
          if(isset($_POST['receiver_id'])) {
              if(!empty($_POST['receiver_id'])) {
                 $data_array = array(
                    'units' =>$units,
                    'service_id' =>$service_id,
                    'blood_group_id' =>$blood_group_id,
                    'title' =>$title,
                    'disc' =>$disc,
                    'city_id' =>$city[0]->id,
                    'patient_name' =>$patient_name,
                    'patient_age' =>$patient_age,
                    'hospital_name' =>$hospital_name,
                    'bed_no' =>$bed_no,
                    'ward_no' =>$ward_no,
                    'doctor_name' =>$doctor_name,
                    'address' =>$address,
                    'latitude' =>$latitude,
                    'longitude' =>$longitude
                    );
              } else {$error = 'Receiver id is empty';}     
          }else {
              $data_array = array(
                'receiver_user_id' =>$user_id,
                'units' =>$units,
                'service_id' =>$service_id,
                'blood_group_id' =>$blood_group_id,
                'title' =>$title,
                'disc' =>$disc,
                'city_id' =>$city[0]->id,
                'patient_name' =>$patient_name,
                'patient_age' =>$patient_age,
                'hospital_name' =>$hospital_name,
                'bed_no' =>$bed_no,
                'ward_no' =>$ward_no,
                'doctor_name' =>$doctor_name,
                'address' =>$address,
                'latitude' =>$latitude,
                'longitude' =>$longitude,
                'status' =>1,
                'action'=>$action,
                'created_date' =>date('Y-m-d')
               );
          }
          
        $data = $this->receiver_model->save_receiver($data_array, $receiver_id, $action, $donor_userid);
        } else {
            $error = validation_errors();
        }
        
        if($data==true){
          $this->response(array(
            "data"=>$data,
            "status" => 1,
            "message" => "Donor Added Successfully"
          ), REST_Controller::HTTP_OK);
        } else{
          $this->response(array(
            "status" => 0,
            "message" => $error
          ), 400);
        }
        
  }
 
  public function response_post(){
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('receiver_id', 'receiver_id', 'required');
      $this->form_validation->set_rules('status', 'status', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $response_user_id = $this->input->post('user_id');
          $receiver_id = $this->input->post('receiver_id');
          $status = $this->input->post('status');
          $response_id = $this->input->post('response_id');
          
          if(isset($response_id) and !empty($response_id)){
            $data_array = array('status' =>$status);
          }elseif(!isset($response_id)){
            $data_array = array(
                'receiver_id' =>$receiver_id,
                'response_user_id' =>$response_user_id,
                'status' =>$status,
                'created_date' =>date('Y-m-d h:m:s')
            ); 
          }  
          
        $data = $this->receiver_model->response_receiver($data_array);
        if($data == 1){
          $this->response(array(
            "status" => 1,
            "message" => "Response Accept Successfully"
          ), REST_Controller::HTTP_OK);
        }elseif($data == 2){
        $this->response(array(
            "status" => 2,
            "message" => "Response Update Successfully"
          ), REST_Controller::HTTP_OK);
        }else{
          $this->response(array(
            "status" => 0,
            "message" => "Response Not Successfully"
          ), 400);
        }
      }else{
        $this->response(array(
            "status" => 0,
            "message" => "All Field Are Needed"
        ), 400);  
      }    
  }
  
  public function response_receiver_post(){
      
      $this->form_validation->set_rules('status', 'status', 'required');
      $this->form_validation->set_rules('response_id', 'response_id', 'required');
      
      if ($this->form_validation->run() == true) {
          $response_id = $this->input->post('response_id');
          $data_array = array('status'=>$this->input->post('status'));
          $data = $this->receiver_model->response_receiver($response_id, $data_array);
          
          if($data==2){
          $this->response(array(
            "status" => 2,
            "message" => "Response Update Successfully"
              ), REST_Controller::HTTP_OK);
            }else{
              $this->response(array(
                "status" => 0,
                "message" => "Response Update not Successfully"
              ), 400);
            }
        } else {
          $this->response(array(
                "status" => 0,
                "message" => "All Fied are Needed"
              ), 400);  
        }
  }
  
  public function favorite_post(){
    
    $data = false;
      $error = 'Receiver not Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('id', 'id', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $id = $this->input->post('id');
          
          $data = $this->receiver_model->favorite_receiver($user_id, $id);
      } else {$error = validation_errors();}
    
    if($data==1){
        $this->response(array(
        "status" => 1,
        "message" => "Favorite Status added Successfully"
        ), REST_Controller::HTTP_OK);
    }elseif($data==2){
        $this->response(array(
        "status" => 2,
        "message" => "Favorite Status deleted Successfully"
        ), REST_Controller::HTTP_OK);
    }else{ 
        
        $this->response(array(
        "status" => 0,
        "message" => "Favorite Status not Successfully"
        ), 400);
    }
  }
  
  public function action_post(){
      $this->form_validation->set_rules('receiver_id', 'receiver_id', 'required'); 
      $this->form_validation->set_rules('status', 'status', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $data_array = array('status' => $this->input->post('status'));
          $data = $this->receiver_model->action_receiver($this->input->post('receiver_id') , $data_array);
          
          if($data){
          $this->response(array(
            "status" => 1,
            "message" => "Request action Successfully"
              ), REST_Controller::HTTP_OK);
            }else{
              $this->response(array(
                "status" => 0,
                "message" => "request Action not Successfully"
              ), 400);
            }
        } else {
          $this->response(array(
                "status" => 0,
                "message" => "All Feild are Needed"
              ), 400);  
        }
  }
  
  function user_check($id){
     if(count($this->db->select('id')->from('user')->where('id', $id)->get()->result())>0)
     {return true;} else { return false;}
  }
}

?>
