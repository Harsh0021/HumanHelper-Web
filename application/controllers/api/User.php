<?php

require APPPATH.'libraries/REST_Controller.php';

class User extends REST_Controller{

  public function __construct(){
    
    parent::__construct();
    
    $this->load->model(array("api/user_model"));
    $this->load->database();
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
    date_default_timezone_set('Asia/Kolkata');
  }

  public function mobile_status_get(){
    
    $id = $this->security->xss_clean($this->input->get("id"));
    
    if(!isset($id)){
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ) , 400);
    }
    else{
        if(!empty($id)){
            $data = $this->user_model->mobile_status($id);
            if($data){
                $this->response(array(
                "status" => 1,
                "message" => "Mobile Status Update"
                ), REST_Controller::HTTP_OK);
            }
            else{
                $this->response(array(
                "status" => 0,
                "message" => "Mobile Status Not Update"
                ), 400);
            }
        }
        else{
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed !!!"
        ), 400);
      }
    }  
  }
  
  public function last_donation_post(){
    
    $date = $this->security->xss_clean($this->input->post("date"));  
    $id = $this->security->xss_clean($this->input->post("id"));
    
    if(!isset($date) and !isset($id)){
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ) , 400);
    }
    else{
        if(!empty($date) and !empty($id) and preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date) ){
            $data = $this->user_model->last_donation($id, $date);
            if($data==1){
                $this->response(array(
                "status" => 1,
                "message" => "Last Donation Added Successfully"
                ), REST_Controller::HTTP_OK);
            }elseif($data == 2) {
                $this->response(array(
                "status" => 2,
                "message" => "Last Donation Update Successfully"
                ), REST_Controller::HTTP_OK);
            }else{
                $this->response(array(
                "status" => 0,
                "message" => "Last Donation Not Update"
                ), 400);
            }
        }else{
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed !!!"
        ), 400);
      }
    }  
  }
  
  public function mobile_get(){
    
    $mobile = $this->security->xss_clean($this->input->get("mobile"));  
    
    if(!isset($mobile)){
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ) , 400);
    }
    else{
      if(!empty($mobile)){
          $data = $this->user_model->mobile_get($mobile);
          if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "User Found"
              ), REST_Controller::HTTP_OK);
          }
          else{
              $this->response(array(
                "status" => 0,
                "message" => "User Not Found"
              ), 400);
          }
      }
      else{
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed !!!"
        ), 400);
      }
    }
  }

  public function pincity_get(){
    
    $id = $this->security->xss_clean($this->input->get("pincode"));  
    
    if(!isset($id)){
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ) , 400);
    }
    else{
      if(!empty($id)){
          $data = $this->user_model->pincity_get($id);
          if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "User Found"
              ), REST_Controller::HTTP_OK);
          }
          else{
              $this->response(array(
                "status" => 0,
                "message" => "User Not Found"
              ), 400);
          }
      }
      else{
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed !!!"
        ), 400);
      }
    }
  }
  
  public function dashboard_get(){
    
     $id = $this->security->xss_clean($this->input->get("id"));  
    
    if(!isset($id)){
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ) , 400);
    }else{
      if(!empty($id)){
          $data = $this->user_model->dashboard($id);
            if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "User Found"
              ), REST_Controller::HTTP_OK);
            }else{
              $this->response(array(
                "status" => 0,
                "message" => "User Not Found"
              ), 400);
            }
      }else{
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed !!!"
        ), 400);
      }
    }
  }
  
  public function regi_post(){
      
      $this->form_validation->set_rules('name', 'name', 'required');
      //$this->form_validation->set_rules('mobile', 'mobile', 'required|regex_match[/^[0-9]{10}$/]');
      $this->form_validation->set_rules('mobile', 'mobile', 'required');
      $this->form_validation->set_rules('age', 'age', 'required');
      $this->form_validation->set_rules('blood_group_id', 'blood_group_id', 'required');
      $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
      
      if ($this->form_validation->run() == true) {
          $name = $this->security->xss_clean($this->input->post("name")); 
          $mobile = $this->security->xss_clean($this->input->post('mobile'));
          $age = $this->security->xss_clean($this->input->post('age'));
          $blood_group_id = $this->security->xss_clean($this->input->post('blood_group_id'));
          $refferal = $this->security->xss_clean($this->input->post('refferal'));
          $email = $this->security->xss_clean($this->input->post('email'));
          $id = $this->input->post('id');
          
        if(isset($id)){
             if(!empty($id)){
                 $data_array = array(
                    'name'=>$name,
                    'mobile'=>$mobile,
                    'email'=>$email,
                    'age'=>$age,
                    'blood_group_id'=>$blood_group_id,
                    );
                $data = $this->user_model->register($data_array, $id, $mobile);  
             }else{$data = 0;}
        }else{     
            $data_array = array(
                'name'=>$name,
                'mobile'=>$mobile,
                'email'=>$email,
                'age'=>$age,
                'blood_group_id'=>$blood_group_id,
                'refferal'=>$refferal,
                'status'=>1,
                'mobile_status'=>1,
                'created_date'=>date('Y-m-d')
            );
            $data = $this->user_model->register($data_array, $id, $mobile);          
        }
        if($data==1){
          $this->response(array(
            "status" => 1,
            "message" => "Register Successfully"
          ), REST_Controller::HTTP_OK);
        }elseif($data == 2){
          $this->response(array(
            "status" => 0,
            "message" => "Mobile No Already Registered"
          ), 400);
        }elseif($data == 3){
          $this->response(array(
            "status" => 3,
            "message" => "Update Successfully"
          ), REST_Controller::HTTP_OK);  
        }else{
          $this->response(array(
            "status" => 0,
            'data'=>$data,
            "message" => "Not Successfully"
          ), 400);
        }
      }else{
          $this->response(array(
                "status" => 0,
                "message" => "Form validation error"
            ), 400);
      }
  }
  
  public function address_post(){
      
      $data = false;
      $error = 'Address Not added Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('address1', 'address1', 'required');
      $this->form_validation->set_rules('address2', 'address2', 'required');
      $this->form_validation->set_rules('city_id', 'city_id', 'required');
      $this->form_validation->set_rules('latitude', 'latitude', 'required');
      $this->form_validation->set_rules('longitude', 'longitude', 'required');
      $this->form_validation->set_rules('save_as', 'save_as', 'required');
      
      if ($this->form_validation->run() == true) {
          $user_id = $this->input->post('user_id');
          $address1 = $this->input->post('address1');
          $address2 = $this->input->post('address2');
          $city_id = $this->input->post('city_id');
          $latitude = $this->input->post('latitude');
          $longitude = $this->input->post('longitude');
          $address_type = $this->input->post('save_as');
         
          $data_array = array(
                'address1'=>$address1,
                'address2'=>$address2,
                'city_id'=>$city_id,
                'latitude'=>$latitude,
                'longitude'=>$longitude,
                'address_type'=>$address_type
                );
          
        if(count($this->db->select('id')->from('user')->where('id', $user_id)->get()->result())>0)
        {    
            $data = $this->user_model->address($data_array, $user_id);
        }
        } else { $error = validation_errors(); }
        
        if($data==true){
          $this->response(array(
            "status" => 1,
            "message" => "Address added Successfully"
          ), REST_Controller::HTTP_OK);
        } else{
          $this->response(array(
            "status" => 0,
            "message" => $error
          ), 400);
        }
    
  }
  
  public function upload_post(){
      
      $data = false;
      $error = 'Image Not added Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      
      if ($this->form_validation->run() == true) {
          $user_id = $this->input->post('user_id');
        if(count($this->db->select('id')->from('user')->where('id', $user_id)->get()->result())>0){    
            $data = $this->user_model->upload($user_id);
            if($data['image_metadata']){
              $this->response(array(
                "status" => 1,
                "message" => "Image added Successfully"
              ), REST_Controller::HTTP_OK);
            } else{
              $this->response(array(
                "status" => 0,
                "message" => $data['error']
              ), 400);
            }
            
        } else{$this->response(array("status" => 0,"message" => "User Not Exists"), 400);}
        } else { $this->response(array("status" => 0,"message" => validation_errors()), 400);}
        
      
    
  }
  
}

?>
