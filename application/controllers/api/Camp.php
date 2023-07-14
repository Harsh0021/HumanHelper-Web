<?php

require APPPATH.'libraries/REST_Controller.php';

class Camp extends REST_Controller{

  public function __construct(){
    
    parent::__construct();
    
    $this->load->model(array("api/camp_model"));
    $this->load->database();
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
    date_default_timezone_set('Asia/Kolkata');
  }

  public function list_get(){
    
    $user_id = $this->input->get('user_id');
    $distance = $this->input->get('distance'); 
    
    if(isset($user_id) and isset($distance))    {
      if(!empty($user_id) and !empty($distance))      {
          $data = $this->camp_model->get_camp_list($user_id, $distance);  
          
      }else {$data = false;}
      }else {$data = false;}
      
        if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "Camp Found"
              ), REST_Controller::HTTP_OK);
        }else{
              $this->response(array(
                "status" => 0,
                "message" => "Camp Not Found !!"
              ), 400);
        }
  }

  public function index_get(){
    
    $id = $this->input->get('id');
    $user_id = $this->input->get('user_id');
    $data = false;
    
    if(isset($id)){
        if(!empty($id)){
            $data = $this->camp_model->get_camp($id , $user_id);    
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


  public function mycamplist_get(){
    
    $user_id = $this->input->get('user_id');
    $data = false;
    
    if(isset($user_id)){
        if(!empty($user_id)){
            $data = $this->camp_model->get_mycamps($user_id);    
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



  public function mycamp_get(){
    
    $user_id = $this->input->get('user_id');
    $camp_id = $this->input->get('camp_id');
    
    $data = false;
    
    if(isset($user_id) and isset($camp_id)){
        if(!empty($user_id) and !empty($camp_id)){
            $data = $this->camp_model->get_mycamp($user_id, $camp_id);    
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
      $error = 'Camp Added not Successfully';
      
  //  $this->form_validation->set_rules('user_id', 'user_id', 'required|callback_user_check');
  //  this->form_validation->set_rules('user_id', 'user_id', 'required');
  
      $this->form_validation->set_rules('organise_by', 'organise_by', 'required');
      $this->form_validation->set_rules('date', 'date', 'required');
      $this->form_validation->set_rules('mobile', 'mobile', 'required|min_length[10]|max_length[10]');
      $this->form_validation->set_rules('email', 'email', 'required|valid_email');
      $this->form_validation->set_rules('start_time', 'start_time', 'required');
      $this->form_validation->set_rules('end_time', 'end_time', 'required');
      $this->form_validation->set_rules('lat', 'lat', 'required');
      $this->form_validation->set_rules('long', 'long', 'required');
      $this->form_validation->set_rules('address', 'address', 'required');
      $this->form_validation->set_rules('city', 'city', 'required');
      $this->form_validation->set_rules('title', 'title', 'required');
      $this->form_validation->set_rules('disc', 'disc', 'required');
        
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $organise_by = $this->input->post('organise_by');
          $date = $this->input->post('date');
          $mobile = $this->input->post('mobile');
          $email = $this->input->post('email');
          $start_time = $this->input->post('start_time');
          $end_time = $this->input->post('end_time');
          $lat = $this->input->post('lat');
          $long = $this->input->post('long');
          $address = $this->input->post('address');
          $city = $this->input->post('city');
          $title = $this->input->post('title');
          $disc = $this->input->post('disc');
          
          
          if(isset($_POST['id'])) {
              if(!empty($_POST['id'])) {
                 $data_array = array(
                    'organise_by' => $organise_by,
                    'date'=>$date,
                    'mobile'=>$mobile,
                    'email'=>$email,
                    'start_time'=>$start_time,
                    'end_time'=>$end_time,
                    'latitude'=>$lat,
                    'longitude'=>$long,
                    'address'=>$address,
                    'city'=>$city,
                    'title'=>$title,
                    'discription'=>$disc,
                    'created_date'=>date('Y-m-d h:i:sa')
                    );
              $data = $this->camp_model->save_camp($data_array);  
              } else {$error = 'Camp id is empty';}     
          }else{
              $data_array = array(
                'user_id'=>$user_id,
                'organise_by' => $organise_by,
                'date'=>$date,
                'mobile'=>$mobile,
                'email'=>$email,
                'start_time'=>$start_time,
                'end_time'=>$end_time,
                'latitude'=>$lat,
                'longitude'=>$long,
                'address'=>$address,
                'city'=>$city,
                'title'=>$title,
                'discription'=>$disc,
                'status'=>1,
                'created_date'=>date('Y-m-d h:i:sa')
                );
              $data = $this->camp_model->save_camp($data_array);    
          }
        } else {$error = validation_errors();}
        
        if($data==true){
          $this->response(array(
            "status" => 1,
            "message" => "Camp Added Successfully"
          ), REST_Controller::HTTP_OK);
        } else{
          $this->response(array(
            "status" => 0,
            "message" => $error
          ), 400);
        }
  }
  
  public function favorite_post(){
    
    $data = false;
      $error = 'Camp Added not Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('category_id', 'category_id', 'required');
      $this->form_validation->set_rules('category_table_sno', 'category_table_sno', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $category_id = $this->input->post('category_id');
          $category_table_sno = $this->input->post('category_table_sno');
          
          $data = $this->camp_model->favorite_camp($user_id, $category_id, $category_table_sno);
      } else {$error = validation_errors();}
    
    if($data==true){
        $this->response(array(
        "status" => $data,
        "message" => "Favorite Status Successfully"
        ), REST_Controller::HTTP_OK);
    }else{
        $this->response(array(
        "status" => 0,
        "message" => "Favorite Status not Successfully"
        ), 400);
    }
  }
 
 
  public function deleteimage_post(){
    
    $data = false;
      $error = 'Camp Added not Successfully';
      
      $this->form_validation->set_rules('camp_image_id', 'camp_image_id', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $camp_image_id = $this->input->post('camp_image_id');
          
          $data = $this->camp_model->delete_camp_image($camp_image_id);
      } else {$error = validation_errors();}
    
    if($data==true){
        $this->response(array(
        "status" => $data,
        "message" => "Image Delete Successfully"
        ), REST_Controller::HTTP_OK);
    }else{
        $this->response(array(
        "status" => 0,
        "message" => "Image Delete Not Successfully"
        ), 400);
    }
  }
 
 
  public function donation_post(){
    
    $data = false;
      $error = 'Donation not Successful';
      
      $this->form_validation->set_rules('donor_userid', 'donor_userid', 'required');
      $this->form_validation->set_rules('service_id', 'service_id', 'required');
      //$this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('camp_id', 'camp_id', 'required');
      
      
      if ($this->form_validation->run() == true) {
         
          $array_donation = array(
                'service_id'=>$this->input->post('service_id'), 
                'donor_userid'=>$this->input->post('donor_userid'), 
                'camp_id'=>$this->input->post('camp_id'), 
                'date' => date('Y-m-d h:i:sa'));
                
          $data = $this->camp_model->donation_camp($array_donation);
          
      } else {$error = validation_errors();}
    
    if($data==true){
        $this->response(array(
        "status" => $data,
        "message" => "Donattion Successfully"
        ), REST_Controller::HTTP_OK);
    }else{
        $this->response(array(
        "status" => 0,
        "message" => "Donate not Successfully"
        ), 400);
    }
  }
  
  
  public function participate_post(){
    
    $data = false;
      $error = 'Participated not Successful';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('camp_id', 'camp_id', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $camp_id = $this->input->post('camp_id');
          
          $data = $this->camp_model->participate_camp($user_id, $camp_id);
      } else {$error = validation_errors();}
    
    if($data==true){
        $this->response(array(
        "status" => $data,
        "message" => "Participate Successfully"
        ), REST_Controller::HTTP_OK);
    }else{
        $this->response(array(
        "status" => 0,
        "message" => "Participate not Successfully"
        ), 400);
    }
  }
  
  public function status_post(){
      
      $this->form_validation->set_rules('id', 'id', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $id = $this->input->post('id');
          $data = $this->camp_model->status_camp($id);
        if($data ==1){$status = 'Camp disable Successfully';}elseif($data == 2){$status = 'Camp enable Successfully';}
        if($data==true){
            $this->response(array(
            "status" => $data,
            "message" => $status
            ), REST_Controller::HTTP_OK);
        }else{
            $this->response(array(
            "status" => 0,
            "message" => "Camp Status not Successfully"
            ), 400);
        }      
          
      } else{
            $this->response(array(
            "status" => 0,
            "message" => validation_errors()
            ), 400);
        }  
        
  }
  
  function delete_post(){
     
     $this->form_validation->set_rules('id', 'id', 'required');
      
     if ($this->form_validation->run() == true) {
          
        $id = $this->input->post('id');
        $data = $this->camp_model->delete_camp($id);
        
        if($data==1){
            $this->response(array(
            "status" => $data,
            "message" => 'Camp Delete Successfully'
            ), REST_Controller::HTTP_OK);
        }elseif($data==2){
            $this->response(array(
            "status" => 0,
            "message" => "Participate added. Camp Delete not Successfully"
            ), 400);
        }else{
            $this->response(array(
            "status" => 0,
            "message" => "Camp not Not Found"
            ), 400);
        }      
          
      } else{
            $this->response(array(
            "status" => 0,
            "message" => validation_errors()
            ), 400);
        }  
        
     
     
  }
  
  function user_check($id){
     if(count($this->db->select('id')->from('user')->where('id', $id)->get()->result())>0)
     {return true;} else { return false;}
  }
  
}

?>
