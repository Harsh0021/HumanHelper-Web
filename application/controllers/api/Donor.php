<?php

require APPPATH.'libraries/REST_Controller.php';

class Donor extends REST_Controller{

  public function __construct(){
    
    parent::__construct();
    
    $this->load->model(array("api/donor_model"));
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
          $data = $this->donor_model->get_donor_list($user_id, $distance);  
          
      }else {$data = false;}
      }else {$data = false;}
      
        if($data){
              $this->response(array(
                "data"=>$data,
                "status" => 1,
                "message" => "Donor Found"
              ), REST_Controller::HTTP_OK);
        }else{
              $this->response(array(
                "status" => 0,
                "message" => "Donor Not Found !!"
              ), 400);
        }
  }

  public function index_get(){
    
    $user_id = $this->input->get('user_id');
    $data = false;
    
    if(isset($user_id)){
        if(!empty($user_id)){
            $data = $this->donor_model->get_donor($user_id);    
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
      
      //$this->form_validation->set_rules('user_id', 'user_id', 'required|callback_user_check');
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('service_id', 'service_id', 'required');
      $this->form_validation->set_rules('disc', 'disc', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $service_id = $this->input->post('service_id');
          $disc = $this->input->post('disc');
          
          if(isset($_POST['id'])) {
              if(!empty($_POST['id'])) {
                 $data_array = array(
                    'user_id'=>$user_id,
                    'service_id'=>$service_id,
                    'discription'=>$disc,
                    );
              $data = $this->donor_model->save_donor($data_array);  
              } else {$error = 'donor id is empty';}     
          }else{
              $data_array = array(
                  'user_id'=>$user_id,
                'category_id' =>1,
                'service_id'=>$service_id,
                'discription'=>$disc,
                'status'=>1,
                'created_date'=>date('Y-m-d h:i:sa')
                );
              $data = $this->donor_model->save_donor($data_array);    
          }
        } else {$error = validation_errors();}
        
        if($data==true){
          $this->response(array(
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
  
  public function favorite_post(){
    
    $data = false;
      $error = 'Donor Added not Successfully';
      
      $this->form_validation->set_rules('user_id', 'user_id', 'required');
      $this->form_validation->set_rules('category_id', 'category_id', 'required');
      $this->form_validation->set_rules('category_table_sno', 'category_table_sno', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $user_id = $this->input->post('user_id');
          $category_id = $this->input->post('category_id');
          $category_table_sno = $this->input->post('category_table_sno');
          
          $data = $this->donor_model->favorite_donor($user_id, $category_id, $category_table_sno);
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
  
  public function status_post(){
      
      $this->form_validation->set_rules('id', 'id', 'required');
      
      if ($this->form_validation->run() == true) {
          
          $id = $this->input->post('id');
          $data = $this->donor_model->status_donor($id);
        if($data ==1){$status = 'Donor disable Successfully';}elseif($data == 2){$status = 'Donor enable Successfully';}
        if($data==true){
            $this->response(array(
            "status" => $data,
            "message" => $status
            ), REST_Controller::HTTP_OK);
        }else{
            $this->response(array(
            "status" => 0,
            "message" => "Donor Status not Successfully"
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
