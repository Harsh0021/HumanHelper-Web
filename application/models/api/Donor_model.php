<?php

class Donor_model extends CI_Model{

  public function __construct(){
    parent::__construct();
  }
  
  public function get_donor_list($user_id, $distance){
    
    $result_donor = [];
    $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
    if(count($user_map)==0){return false;}
    
    $this->db->select('donor.id as donor_id, donor.discription as donor_discription, donor.service_id, donor.status as donor_status,
                    user.latitude as donor_latitude, user.longitude as donor_longitude, 
                    user.id as donor_user_id, user.name as donor_name, user.image,
                    user.mobile as donor_mobile,user.mobile_status as donor_mobile_status, 
                    user.email as donor_email, user.age as donor_age, 
                    user.address1 as donor_address1, user.address2 as donor_address2, 
                    user.latitude as donor_latitude, user.longitude as donor_longitude, 
                    location_city.name as donor_city, blood_group.id as donor_blood_group_id, blood_group.name as donor_blood_group');
                    
    $this->db->from('donor');
    $this->db->where('donor.status', 1);
    $this->db->where('donor.user_id !=', $user_id);
    $this->db->join('user','user.id = donor.user_id');
    $this->db->join('blood_group','blood_group.id = user.blood_group_id');
    $this->db->join('location_city', 'location_city.id=user.city_id');
    $query_donor = $this->db->get()->result();
    
    if(count($query_donor)>0) {  $i =0;
      foreach($query_donor as $qudo) { $ix =0;  $donation_last_date = '';
        $donation_details = $this->db->select('date')->from('donation')->where('donor_userid', $qudo->donor_user_id)->order_by('id', 'desc')->limit(1)->get()->result();
        if(count($donation_details)>0) { $donation_last_date = $donation_details[0]->date; }
        
        $donation_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>1, 'user_id'=> $user_id, 'category_table_sno'=>$qudo->donor_id))->get()->num_rows();
        
        $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $qudo->donor_latitude, $qudo->donor_longitude);
        
        $seid = json_decode($qudo->service_id);
        
        foreach($seid as $se){
            $query_se = $this->db->select('name')->from('service')->where('id', $se)->get()->result();
            $service_name[$ix] = $query_se[0]->name;
            $ix++;
        }
        
        if($distance1<=$distance){
          $user_array = array(
           'donor_id'=>$qudo->donor_id,
           'donor_user_id'=> $qudo->donor_user_id,
           'donor_image'=> $qudo->image,
           'donor_name'=>$qudo->donor_name, 
           'donor_mobile'=>$qudo->donor_mobile, 
           'donor_email'=>$qudo->donor_email, 
           'donor_age'=>$qudo->donor_age, 
           'donor_address1'=>$qudo->donor_address1, 
           'donor_address2'=>$qudo->donor_address2, 
           'donor_city'=>$qudo->donor_city, 
           'donor_service'=>$service_name,
           'donor_blood_group'=>$qudo->donor_blood_group, 
           'donor_discription'=>$qudo->donor_discription,
           'donor_mobile_status'=>$qudo->donor_mobile_status,
           'donor_longitude'=>$qudo->donor_longitude,
           'donor_latitude'=>$qudo->donor_latitude,
           'donor_status'=>$qudo->donor_status,
           'distance'=>$distance1,
           'donation_favorite'=>$donation_favorite,
           'last_donation'=>$donation_last_date
        );
        
        $result_donor[$i] = $user_array;
        $service_name =[];
        $i++;
        }
      }
      return $result_donor;
    } else { return $result_donor;}    
  }
  
  public function save_donor($data_array){
    $donor_id = $this->input->post('id');
    if(isset($donor_id)){
      if(!empty($donor_id)){
        $this->db->where('id', $donor_id);
        return $this->db->update('donor', $data_array); 
      }else{return false;}
    }else{ return $this->db->insert('donor', $data_array); }    
  }
  
  public function favorite_donor($user_id, $category_id, $category_table_sno){
    
    $query = $this->db->select('id')->where(array('user_id'=>$user_id, 'category_id'=>$category_id, 'category_table_sno'=>$category_table_sno))->get('favorite')->result();
    if(count($query)>0)
    {
      $this->db->where('id', $query[0]->id);
      $result =  $this->db->delete('favorite');
      if($result == true) {return 2; } 
    }else
    {
      $result =  $this->db->insert('favorite', array('user_id'=>$user_id, 'category_id'=>$category_id, 'category_table_sno'=>$category_table_sno)); 
      if($result == true) {return 1; } 
    }
    
  }
  
  public function get_donor00($user_id){
      
    $this->db->select('donor.id as donor_id, service_type.name  as donor_service_type, donor.status as donor_status');
    $this->db->from('donor');
    $this->db->where('donor.user_id', $user_id);
    $this->db->join('service_type','service_type.id=donor.donor_type_id');
    $query_donor = $this->db->get()->result();
    
    if(count($query_donor)>0)
    {
        $data['donor'] =  $query_donor;
    }else
    {
        $data['donor'] =  [];
    }
    
    
    $this->db->select('user.name as receiver_name, service_type.name as supply_name, date');
    $this->db->from('donation');
    $this->db->where('donation.donor_userid', $user_id);
    $this->db->join('user', 'user.id=donation.receiver_userid');
    $this->db->join('service_type','service_type.id=donation.type_id');
    $query_donation = $this->db->get()->result();
    
    if(count($query_donation)>0)
    {
        $data['donation'] =  $query_donation;
    }else
    {
        $data['donation'] =  [];
    }
        
    return $data;
    
  }
  
  public function delete_donor00($donor_id){
    $query = $this->db->where('id', $donor_id)->get('donor')->result();
    if(count($query)>0)
    {  
     $this->db->where('id', $donor_id);   
     return $this->db->delete('donor');
    }
  }
  
  public function status_donor($donor_id){
    $query = $this->db->select('status')->where('id', $donor_id)->get('donor')->result();
    if(count($query)>0)
    {
     if($query[0]->status ==0) $status = 1; else $status = 0   ;
     $this->db->where(array('id'=>$donor_id));   
     $this->db->update('donor', array('status'=>$status)); 
     return ($status+1);
    } 
  }
  
  public function request_donor00($donor_id){
    $query = $this->db->select('status')->where('id', $donor_id)->get('donor')->result();
    if(count($query)>0)
    {
     if($query[0]->status ==0) $status = 1; else $status = 0   ;
     $this->db->where(array('id'=>$donor_id));   
     return $this->db->update('donor', array('status'=>$status)); 
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
  
}
 ?>
