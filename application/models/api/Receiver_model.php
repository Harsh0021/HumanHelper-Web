<?php

class Receiver_model extends CI_Model{

  public function __construct(){
    parent::__construct();
  }
  
  public function get_Receiver_list($data_array, $user_id, $distance){
  
  $result_receiver = [];
  if(isset($user_id) and !empty($user_id)){
       $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
  }else{ return false; }  
    
  $this->db->select('receiver.id as receiver_id, receiver.patient_name, receiver.patient_age, 
                receiver.title as receiver_title, service.name as receiver_service_name,  
                receiver.latitude as receiver_latitude, receiver.longitude as receiver_longitude, receiver.status,
                user.mobile as contact_mobile, user.email as contact_email, user.image, 
                location_city.name as receiver_city, blood_group.name as receiver_blood_group');
    $this->db->from('receiver');
    $this->db->where($data_array);
    $this->db->where_not_in('receiver.receiver_user_id', $user_id);
    $this->db->join('user','user.id = receiver.receiver_user_id');
    $this->db->join('service','service.id = receiver.service_id');
    $this->db->join('blood_group','blood_group.id = receiver.blood_group_id');
    $this->db->join('location_city', 'location_city.id = receiver.city_id');
    $query_receiver = $this->db->get()->result();
    
    if(count($query_receiver)>0) {
      $i =0;
      foreach($query_receiver as $qudo) {
        
      $response_array = $this->db->select('id, status')->from('receiver_response')->where('receiver_id', $qudo->receiver_id)
                        ->where('response_user_id', $user_id)->get()->result();
      if(count($response_array)>0){$response_id = $response_array[0]->id; $response_status = $response_array[0]->status;}
      else{$response_id = 0; $response_status =0;}    
        
        $receiver_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>2, 'user_id'=> $user_id, 'category_table_sno'=>$qudo->receiver_id))->get()->num_rows();
        
        $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $qudo->receiver_latitude, $qudo->receiver_longitude);
        
        if($distance1<=$distance) {
          $user_array = array(
           'receiver_id'=>$qudo->receiver_id,
           'patient_name'=>$qudo->patient_name,
           'receiver_image'=>$qudo->image,
           'patient_age'=>$qudo->patient_age,
           'receiver_mobile'=>$qudo->contact_mobile, 
           'receiver_email'=>$qudo->contact_email, 
           'receiver_city'=>$qudo->receiver_city, 
           'receiver_service'=>$qudo->receiver_service_name,
           'receiver_blood_group'=>$qudo->receiver_blood_group, 
           'receiver_title'=>$qudo->receiver_title,
           'distance'=>$distance1,
           'favorite'=>$receiver_favorite,
           'status'=>$qudo->status,
           'receiver_response'=>count($response_array),
           'response_id'=>$response_id,
           'respnse_status'=>$response_status
        );
        
        $result_receiver[$i] = $user_array;
        $service_name =[];
        $i++;
        }
      }
      return $result_receiver;
    } else { return $result_receiver;}    
  }
  
  public function get_receiver($receiver_id, $user_id){
    $this->db->select('r.id as receiver_id, r.receiver_user_id, r.units, r.title, r.disc, r.patient_name, r.patient_age, 
                    r.hospital_name, r.bed_no, r.ward_no, r.doctor_name, r.latitude, r.longitude, r.address, r.created_date,r.status,
                    location_city.name as city,
                    service.name  as service_name,
                    blood_group.name as blood_group,
                    u.name as contact_name, u.mobile as contact_mobile, u.email as contact_email, u.address1 as contact_add1, u.address2 as contact_add2, u.image
                    ');
    $this->db->from('receiver as r');
    $this->db->where('r.id', $receiver_id);
    $this->db->join('location_city','location_city.id=r.city_id');
    $this->db->join('service','service.id=r.service_id');
    $this->db->join('blood_group','blood_group.id=r.blood_group_id');
    $this->db->join('user as u','u.id=r.receiver_user_id');
    $data_receiver = $this->db->get()->result();
    $data['receiver'] = $data_receiver;
    
    $this->db->select('receiver_response.id as response_id, receiver_response.response_user_id, user.name, user.mobile , receiver_response.status');
    $this->db->from('receiver_response');
    $this->db->where('receiver_id', $receiver_id);
    $this->db->join('user', 'user.id = receiver_response.response_user_id');
    $data['response']= $this->db->get()->result();
    
    $this->db->select('id');
    $this->db->from('receiver_response');
    $this->db->where('receiver_id', $receiver_id);
    $this->db->where('response_user_id', $user_id);
    $data['response_count']= $this->db->get()->num_rows();
    
    return $data;
    
  }
  
  public function get_donorview($receiver_id, $user_id){
    $this->db->select('r.id as receiver_id, r.receiver_user_id, r.units, r.title, r.disc, r.patient_name, r.patient_age, 
                    r.hospital_name, r.bed_no, r.ward_no, r.doctor_name, r.latitude, r.longitude, r.address, r.created_date,r.status,
                    location_city.name as city,
                    service.name  as service_name,
                    blood_group.name as blood_group,
                    u.name as contact_name, u.mobile as contact_mobile, u.email as contact_email, u.address1 as contact_add1, u.address2 as contact_add2, u.image
                    ');
    $this->db->from('receiver as r');
    $this->db->where('r.id', $receiver_id);
    $this->db->join('location_city','location_city.id=r.city_id');
    $this->db->join('service','service.id=r.service_id');
    $this->db->join('blood_group','blood_group.id=r.blood_group_id');
    $this->db->join('user as u','u.id=r.receiver_user_id');
    $data_receiver = $this->db->get()->result();
    $data['receiver'] = $data_receiver;
    
    $this->db->select('receiver_response.id as response_id, user.name, user.mobile , receiver_response.status');
    $this->db->from('receiver_response');
    $this->db->where('receiver_id', $receiver_id);
    $this->db->where('response_user_id', $user_id);
    $this->db->join('user', 'user.id = receiver_response.response_user_id');
    $data['response']= $this->db->get()->result();
    
    $this->db->select('id');
    $this->db->from('receiver_response');
    $this->db->where('receiver_id', $receiver_id);
    $this->db->where('response_user_id', $user_id);
    $data['response_count']= $this->db->get()->num_rows();
    
    return $data;
    
  }
  
  public function favorite_receiver($user_id, $category_table_sno){
    $query = $this->db->select('id')->where(array('user_id'=>$user_id, 'category_id'=>2, 'category_table_sno'=>$category_table_sno))->get('favorite')->result();
    if(count($query)>0)
    {
      $this->db->where('id', $query[0]->id);
      $this->db->delete('favorite');
      return 2;
    }else
    {
      $this->db->insert('favorite', array('user_id'=>$user_id, 'category_id'=>2, 'category_table_sno'=>$category_table_sno)); 
      return 1;
    }
  }
  
  public function response_receiver($response_id, $data_array){
    if(isset($response_id)){
        $result = $this->db->select('*')->from('receiver_response')->get()->result();
        if(count($result)>0){
        $this->db->where('id', $response_id);
        $this->db->update('receiver_response', $data_array); 
        if($data_array['status'] == 2){
        $donation = array('service_id'=>5, 'donor_userid'=>$result[0]->response_user_id, 'receiver_id'=>$result[0]->receiver_id,'date'=>date('Y-m-d'));
        $data =$this->db->insert('donation', $donation);
        }else{$data = false;}
        if($data){return 2;}else{return 0;}
        }else{return 0;}
    }else{ return 0; }    
  }
  
  public function get_myrequest00($user_id){
    $this->db->select('r.id, r.patient_name, r.patient_age, r.title, r.created_date, blood_group.name as blood_group, service.name as service, r.status');
    $this->db->from('receiver as r');
    $this->db->where('r.receiver_user_id', $user_id);
    $this->db->join('blood_group','blood_group.id = r.blood_group_id');
    $this->db->join('service','service.id = r.service_id');
    $data = $this->db->get()->result();
    return $data;
  }
  
  public function save_receiver($data_array, $receiver_id, $action, $donor_userid){
    $user_id = $this->input->post('user_id');
    if(isset($receiver_id))    {
    if(!empty($receiver_id))    {
        $this->db->where('id', $receiver_id);
        return $this->db->update('receiver', $data_array); 
    }else{return false;}
    }else{
        $result = $this->db->insert('receiver', $data_array);
        $insert_id = $this->db->insert_id();
        if($action == 1){
        $insert_response = array(
            'receiver_id'=>$insert_id,
            'response_user_id'=>$donor_userid,
            'created_date'=>date('Y-m-d'),
            'status'=>3
            );
        $this->db->insert('receiver_response', $insert_response);  
        }
    return $result;    
    }    
  }
  
  public function action_receiver($receiver_id, $data_array){
    $query = $this->db->select('status')->where('id', $receiver_id)->get('receiver')->result();
    if(count($query)>0){
     $this->db->where(array('id'=>$receiver_id));   
     return $this->db->update('receiver', $data_array); 
    } 
  }
  
  function distance($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;
    $kilometers = $miles * 1.609344;
    return round($kilometers, 0);
 }
  
  public function delete_receiver00($receiver_id){
    $query = $this->db->where('id', $receiver_id)->get('receiver')->result();
    if(count($query)>0)
    {  
     $this->db->where('id', $receiver_id);   
     return $this->db->delete('receiver');
    }
  }
  
  public function status_receiver00($receiver_id){
    $query = $this->db->select('status')->where('id', $receiver_id)->get('receiver')->result();
    if(count($query)>0)
    {
     if($query[0]->status ==0) $status = 1; else $status = 0   ;
     $this->db->where(array('id'=>$receiver_id));   
     return $this->db->update('receiver', array('status'=>$status)); 
    } 
  }
  
}
 ?>
