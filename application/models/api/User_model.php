<?php
class User_model extends CI_Model{

  public function __construct(){
    parent::__construct();
  }
  
  public function dashboard($id){
      
    $data =[]; $last_donation_date =0; $last_donation_type =0;
    
    $data['app_data'] = $this->db->select('description')->from('app_data')->where('status', 1)->get()->result();
    $data['app_version'] = $this->db->select('*')->from('app_version')->where('status', 1)->get()->result();
    $data['user_count'] = $this->db->select('id')->from('user')->get()->num_rows();
    
    $data_donor_count = $this->db->select('id')->from('donor')->get()->num_rows();
    $data_receiver_count = $this->db->select('id')->from('receiver')->get()->num_rows();
    $data['donor_live_status'][0] = array('donor'=>$data_donor_count, 'need_blood' => $data_receiver_count);
    
    $data_donor = $this->db->select('id')->from('donor')->where('user_id', $id)->get()->num_rows();
    $data['donor_user_action'][0] = array('donor_action'=> $data_donor);
    $data['donor_view'] = $this->db->select('*')->from('donor')->where('user_id', $id)->get()->result();
    
    $data['blood_group'] = $this->db->select('id, name')->from('blood_group')->get()->result();
    $data['homepage_banner_image'] = $this->db->select('image_url')->from('homepage_banner_image')->where('status', 1)->get()->result();
    $data['homepage_modules'] = $this->db->select('title, discription, url')->from('homepage_modules')->where('status', 1)->get()->result();
    
    
    $data['service'] = $this->db->select('id, name')->from('service')->get()->result();
    $data['category'] = $this->db->select('id, name')->from('category')->get()->result();
    $data['version'] = $this->db->select('id, name')->from('app_version')->where('status', 1)->get()->result();
    $data['notification'] = $this->db->select('id, name')->from('notification')->order_by('id', 'desc')->limit(1)->get()->result();
    
    
    $this->db->select('user.id, user.name, user.mobile, user.mobile_status, user.email, user.status, user.age, user.blood_group_id, user.refferal, blood_group.name as blood_group_name, user.image');
    $this->db->from('user');
    $this->db->where('user.id', $id);
    $this->db->join('blood_group', 'blood_group.id = user.blood_group_id');
    $data['query_user'] = $this->db->get()->result();
    
    
    $this->db->select('user.address1, user.address2, user.city_id, user.address_type, location_city.name as city_name, user.longitude, user.latitude');
    $this->db->from('user');
    $this->db->where('user.id', $id);
    $this->db->join('location_city', 'location_city.id=user.city_id');
    $data['user_address'] = $this->db->get()->result();
    
    $this->db->select('user.*, location_city.name as city_name, blood_group.name as blood_group_name');
    $this->db->from('user');
    $this->db->where('user.id', $id);
    $this->db->join('blood_group', 'blood_group.id = user.blood_group_id');
    $this->db->join('location_city', 'location_city.id=user.city_id');
    $data['user'] = $this->db->get()->result();
    
    $this->db->select('service.name as service_name, donation.date, donation.receiver_id');
    $this->db->from('donation');
    $this->db->where('donation.donor_userid', $id);
    $this->db->join('service', 'service.id = donation.service_id');
    $this->db->order_by('donation.date', 'desc');
    $this->db->limit(1);
    $data['last_donation'] = $this->db->get()->result();
    
    $this->db->select('donation.id as donation_id, donation.receiver_id, service.name as service_name, donation.date as donation_date, donation.camp_id');
    $this->db->from('donation');
    $this->db->where('donation.donor_userid', $id);
    $this->db->join('service', 'service.id = donation.service_id');
    $this->db->order_by('donation.date', 'desc');
    $data_donate = $this->db->get()->result();
    
    if(count($data_donate)>0){
    foreach($data_donate as $donate){
        if($donate->receiver_id == NULL && $donate->camp_id == NULL){
            $array = array(
                'receiver_id'=> NULL,
                'camp_id'=>NULL,
                'donation_id'=>$donate->donation_id,
                'service_name'=>$donate->service_name,
                'donation_date'=>$donate->donation_date,
                );
        }elseif($donate->camp_id != NULL){
            $array = array(
                'receiver_id'=> NULL,
                'camp_id'=>$donate->camp_id,
                'donation_id'=>$donate->donation_id,
                'service_name'=>$donate->service_name,
                'donation_date'=>$donate->donation_date,
                );
        }
        else{
            $rec = $this->db->select('receiver.patient_name, blood_group.name as blood_group, receiver.patient_age, receiver.address, location_city.name as city')
                ->from('receiver')
                ->where('receiver.id', $donate->receiver_id)
                ->join('location_city', 'location_city.id = receiver.city_id')
                ->join('blood_group', 'blood_group.id = receiver.blood_group_id')
                ->get()->result();
            $array = array(
                'receiver_id'=>$donate->receiver_id,
                'donation_id'=>$donate->donation_id,
                'service_name'=>$donate->service_name,
                'blood_group'=>$rec[0]->blood_group,
                'donation_date'=>$donate->donation_date,
                'patient_name'=>$rec[0]->patient_name,
                'patient_age'=>$rec[0]->patient_age,
                'patient_address'=>$rec[0]->address,
                'patient_city'=>$rec[0]->city
                );
        }
        
       $data_ress[]=$array; 
    }
    }else{$data_ress=[];}
    $data['my_donation'] = $data_ress;
    
    $this->db->select('r.id, r.patient_name, r.patient_age, r.title, r.created_date, blood_group.name as blood_group, service.name as service, r.status');
    $this->db->from('receiver as r');
    $this->db->where('r.receiver_user_id', $id);
    $this->db->join('blood_group','blood_group.id = r.blood_group_id');
    $this->db->join('service','service.id = r.service_id');
    $data['my_request'] = $this->db->get()->result();
    
    $favorite = $this->db->select('category_table_sno as camp_id')->from('favorite')->where('user_id', $id)->where('category_id', 4)->get()->result();
    $data_fav =[];
    if(count($favorite)>0){
    foreach($favorite as $fav){
        $result_fav = $this->camp_list($fav->camp_id, $id);
        if($result_fav){ $data_fav[] = $result_fav; }
    }
    $data['my_favorite']['camp'] = $data_fav;
    }else {$data['my_favorite']['camp'] = array();}
    
    
    $favorite = $this->db->select('category_table_sno as receiver_id')->from('favorite')->where('user_id', $id)->where('category_id', 2)->get()->result();
    $data_fav =[];
    if(count($favorite)>0){
    foreach($favorite as $fav){
        $data_fav[]= $this->receiver_list($fav->receiver_id, $id);
    }
    $data['my_favorite']['receiver'] = $data_fav;
    }else {$data['my_favorite']['receiver'] = array();}
    
    $favorite = $this->db->select('category_table_sno as donor_id')->from('favorite')->where('user_id', $id)->where('category_id', 1)->get()->result();
    $data_fav =[];
    if(count($favorite)>0){
    foreach($favorite as $fav){
        $data_fav[]= $this->donor_list($fav->donor_id, $id);
    }
    $data['my_favorite']['donor'] = $data_fav;
    }else {$data['my_favorite']['donor'] = array();}
    
    $receiver_response = $this->db->select('id as response_id, receiver_id, status')->from('receiver_response')->where('response_user_id',$id)->get()->result();
    if(count($receiver_response)>0){
        foreach($receiver_response as $rec_res){
            $response_array[] = $this->receiver_response_function($id, $rec_res->receiver_id, $rec_res->response_id, $rec_res->status);
        }
    }else{$response_array = [];}
    
    
    $data['my_receive'] = $response_array;
    return $data;
  }
  
  public function register($data_array, $id, $mobile){
    
    $query_mobile = $this->db->select('id')->from('user')->where('mobile', $mobile)->get()->num_rows();
    if(isset($id)){
        if(!empty($id)){
            $query_user = $this->db->select('mobile')->from('user')->where('id', $id)->get()->result();
            if($query_user[0]->mobile == $mobile){
                $this->db->where('id', $id);    
                $this->db->update('user', $data_array); 
                return 3;
            }else{
                if(count($query_mobile)==0){
                    $this->db->where('id', $id);    
                    return $this->db->update('user', $data_array); 
                    return 3;
                }else{return 2;}
            }                   // if id mobile match update else check condition
        }else{return 0;} // if not empty id else empty id
    }elseif($query_mobile==0){
        $insert = $this->db->insert('user', $data_array);  // if isset id else not isset id and if insert
        if($insert == TRUE) return 1; else return 0;
    }else {return 2;}
  }
  
  public function address($data_array, $user_id){
    
    $this->db->where('id', $user_id);
    return $this->db->update('user', $data_array);   
  }
  
  public function last_donation($donor_userid, $date){
    
    $donation = $this->db->select('id, receiver_id')->from('donation')->where('donor_userid', $donor_userid)->get()->result();
    if(count($donation)==0)
    {
        $this->db->insert('donation', array('donor_userid'=>$donor_userid, 'service_id'=>5, 'date'=>$date)); 
        return 1;
    }
    if(count($donation)==1 and $donation[0]->receiver_id == NULL){
        $this->db->where('id', $donation[0]->id);
        $this->db->update('donation', array('date'=>$date)); 
        return 2;
    }
    
    return 0;
  }
  
  public function mobile_status($user_id){
    $query = $this->db->select('mobile_status')->where('id', $user_id)->get('user')->result();
    if(count($query)>0)
    {
     if($query[0]->mobile_status == 0) $status = 1; else $status = 0   ;
     $this->db->where(array('id'=>$user_id));   
     return $this->db->update('user', array('mobile_status'=>$status)); 
    } 
  }
  
  public function mobile_get($mobile){
      
    return  $this->db->select('id')->from('user')->where('mobile', $mobile)->get()->result();
  }
  
  public function pincity_get($pincode){
      
    $city =   $this->db->select('location_pincode.city_id as id, location_city.name as name')->from('location_pincode')
            ->where('pincode', $pincode)->join('location_city', 'location_city.id = location_pincode.city_id')
            ->get()->result();
    
    return $city;    
        
    
  }
  
  public function city_get(){
      
    return  $this->db->select('id, name')->from('location_city')->get()->result();
  }
  
  public function get_splash(){
    $data['blood_group'] = $this->db->select('id, name')->from('blood_group')->get()->result();
    $data['category'] = $this->db->select('id, name')->from('category')->get()->result();
    $data['service_type'] = $this->db->select('id, name')->from('service_type')->get()->result();
    $data['version'] = $this->db->select('id, name')->from('app_version')->where('status', 1)->get()->result();
    //$data['city'] = $this->db->select('id, name')->from('location_city')->get()->result();
    return  $data;
  }
  
  public function profile_get00($id){
    
    $data =[]; $last_donation_date =0; $last_donation_type =0;
    
    $query_user = $this->db->select('*')->from('user')->where('id', $id)->get()->result();
    $query_blood_group = $this->db->select('name')->from('blood_group')->where('id', $query_user[0]->blood_group_id)->get()->result();
    $query_donation = $this->db->select('type_id, date')->from('donation')->where('donor_userid', $id)->get()->result();
    
    if(count($query_donation)>0)
    {
        foreach($query_donation as $qudo)
        {
            $last_donation_date = $qudo->date;
            $last_donation_type = $qudo->type_id;
        }
    }
    
    if(count($query_user)>0)
    {
        foreach($query_user as $qus)
        {
            $result_user = array(
                'name'=>$qus->name,
                'mobile'=>$qus->mobile,
                'email'=>$qus->email,
                'age'=>$qus->age,
                'blood_group'=>$query_blood_group[0]->name,
                'mobile_status'=>$qus->mobile_status,
                'last_donation_date'=>$last_donation_date,
                'last_donation_type'=>$last_donation_type
                );  
        }
    $data['profile'] = $result_user;
    }
    
    $query_address = $this->db->select('*')->from('user_address')->where('user_id', $id)->get()->result();
    
    if(count($query_address)>0)
    {
       $data['address'] = $query_address;
    }
    return $data;
  }
  
  public function favorite_get00($id){
    
    $query_favorite = $this->db->select('*')->from('favorite')->where('user_id', $id)->get()->result();
    return $query_favorite;
  }
  
  public function citypin_get00($city){
      
    $data =   $this->db->select('pincode')->from('pincode')->where('city', $city)->get()->result_array();
    
    return array_unique($data, SORT_REGULAR);
  }
  
  function get_donor00($user_id, $category_id){
    
    $user_details = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
    $user_latitude = $user_details[0]->latitude;
    $user_longitude = $user_details[0]->longitude;
  
    $this->db->select('donor.id as donor_id, user.id as donor_user_id, donor.service_id, user.name as donor_name, user.mobile as donor_mobile, user.email as donor_email, user.age as donor_age, user.address1 as donor_address1, user.address2 as donor_address2, location_city.name as donor_city, user.latitude as donor_latitude, user.longitude as donor_longitude, blood_group.id as donor_blood_group_id, blood_group.name as donor_blood_group, donor.status as donor_status');
    $this->db->from('donor');
    $this->db->where('favorite.user_id', $user_id);
    $this->db->where('favorite.category_id', 1);
    $this->db->where('donor.status', 1);
    $this->db->join('favorite', 'favorite.category_table_sno=donor.id');
    $this->db->join('user','user.id = donor.user_id');
    $this->db->join('blood_group','blood_group.id = user.blood_group_id');
    $this->db->join('location_city', 'location_city.id=user.city_id');
    $query_donor = $this->db->get()->result();
    
    if(count($query_donor)>0)
    {
      $i =0;
      foreach($query_donor as $qudo)
      {
        $donation_details = $this->db->select('date')->from('donation')->where('donor_userid', $qudo->donor_user_id)->order_by('id', 'desc')->limit(1)->get()->result();
        if(count($donation_details)>0) {
            $donation_user_id =$donation_details[0]->date;
        }else {$donation_user_id = '';}
        $distance1 = $this->distance($user_latitude, $user_longitude, $qudo->donor_latitude, $qudo->donor_longitude);
        
        $seid = json_decode($qudo->service_id);
        $ix =0; $si = 0;
        foreach($seid as $se)
        {
            $query_se = $this->db->select('name')->from('service')->where('id', $se)->get()->result();
            $service_name[$ix] = $query_se[0]->name;
            $ix++;
        }
        if($qudo->donor_user_id != $user_id)
        {
          $user_array = array(
           'donor_id'=>$qudo->donor_id,
           'donor_user_id'=> $qudo->donor_user_id,
           'donor_name'=>$qudo->donor_name, 
           'donor_mobile'=>$qudo->donor_mobile, 
           'donor_email'=>$qudo->donor_email, 
           'donor_age'=>$qudo->donor_age, 
           'donor_address1'=>$qudo->donor_address1, 
           'donor_address2'=>$qudo->donor_address2, 
           'donor_city'=>$qudo->donor_city, 
           'donor_service'=>$service_name,
           'donor_blood_group'=>$qudo->donor_blood_group, 
           'donor_status'=>$qudo->donor_status,
           'distance'=>$distance1,
           'last_donation'=>date('d-m-Y')
        );
        
        $result_donor[$i] = $user_array;
        $service_name =[];
        $i++;
        }
      }
      return $result_donor;
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
  
  public function status00($table, $id){
    $db = 'pprvwsmy_donner';
    $tables=$this->db->query("SHOW TABLES FROM $db")->result_array(); 
    $in = 'Tables_in_'. $db;
    
    foreach($tables as $t)
    {
        if($t[$in] == $table){
            $query = $this->db->select('status')->where('id', $id)->get($table)->result();
            if(count($query)>0)
            {
             if($query[0]->status ==0) $status = 1; else $status = 0   ;
             $this->db->where(array('id'=>$id));   
             return $this->db->update($table, array('status'=>$status)); 
            }   
        }
     }
  }
  
  function receiver_list($receiver_id, $user_id){
  
   $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
   
   $this->db->select('receiver.id as receiver_id, receiver.patient_name, receiver.patient_age, 
                receiver.title as receiver_title, service.name as receiver_service_name,  
                receiver.latitude as receiver_latitude, receiver.longitude as receiver_longitude, receiver.status,
                user.mobile as contact_mobile, user.email as contact_email,  user.image,
                location_city.name as receiver_city, blood_group.name as receiver_blood_group');
    $this->db->from('receiver');
    $this->db->where('receiver.id', $receiver_id);
    $this->db->join('user','user.id = receiver.receiver_user_id');
    $this->db->join('service','service.id = receiver.service_id');
    $this->db->join('blood_group','blood_group.id = receiver.blood_group_id');
    $this->db->join('location_city', 'location_city.id = receiver.city_id');
    $query_receiver = $this->db->get()->result();
     
    $response_count = $this->db->select('id')->from('receiver_response')->where('receiver_id', $receiver_id)
                        ->where('response_user_id', $user_id)->get()->num_rows();
        
    $receiver_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>2, 'user_id'=> $user_id, 'category_table_sno'=>$receiver_id))->get()->num_rows();
        
    $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $query_receiver[0]->receiver_latitude, $query_receiver[0]->receiver_longitude);
    $user_array =[];
    $user_array = array(
           'receiver_id'=>$receiver_id,
           'patient_name'=>$query_receiver[0]->patient_name,
           'receiver_image'=>$query_receiver[0]->image,
           'patient_age'=>$query_receiver[0]->patient_age,
           'receiver_mobile'=>$query_receiver[0]->contact_mobile, 
           'receiver_email'=>$query_receiver[0]->contact_email, 
           'receiver_city'=>$query_receiver[0]->receiver_city, 
           'receiver_service'=>$query_receiver[0]->receiver_service_name,
           'receiver_blood_group'=>$query_receiver[0]->receiver_blood_group, 
           'receiver_title'=>$query_receiver[0]->receiver_title,
           'distance'=>$distance1,
           'favorite'=>$receiver_favorite,
           'status'=>$query_receiver[0]->status,
           'receiver_response'=>$response_count
        );
    return $user_array; 
  }
  
  function donor_list($donor_id, $user_id){
    
    $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
    
    $this->db->select('donor.id as donor_id, donor.discription as donor_discription, donor.service_id, donor.status as donor_status,
                    user.latitude as donor_latitude, user.longitude as donor_longitude, 
                    user.id as donor_user_id, user.name as donor_name, user.image,
                    user.mobile as donor_mobile,user.mobile_status as donor_mobile_status, 
                    user.email as donor_email, user.age as donor_age, 
                    user.address1 as donor_address1, user.address2 as donor_address2, 
                    user.latitude as donor_latitude, user.longitude as donor_longitude, 
                    location_city.name as donor_city, blood_group.id as donor_blood_group_id, blood_group.name as donor_blood_group');
                    
    $this->db->from('donor');
    $this->db->where('donor.id', $donor_id);
    $this->db->join('user','user.id = donor.user_id');
    $this->db->join('blood_group','blood_group.id = user.blood_group_id');
    $this->db->join('location_city', 'location_city.id=user.city_id');
    $query_donor = $this->db->get()->result();
      
    $donation_details = $this->db->select('date')->from('donation')->where('donor_userid', $user_id)->order_by('id', 'desc')->limit(1)->get()->result();
    if(count($donation_details)>0) { $donation_last_date = $donation_details[0]->date; } else{$donation_last_date = '';}
        
    $donation_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>1, 'user_id'=> $user_id, 'category_table_sno'=>$donor_id))->get()->num_rows();
        
    $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $query_donor[0]->donor_latitude, $query_donor[0]->donor_longitude);
        
    $seid = json_decode($query_donor[0]->service_id);
    
    ;    
    foreach($seid as $se){
        $query_se = $this->db->select('name')->from('service')->where('id', $se)->get()->result();
        $service_name[] = $query_se[0]->name;
       }
        
    $user_array = array(
           'donor_id'=>$donor_id,
           'donor_user_id'=> $query_donor[0]->donor_user_id,
           'donor_name'=>$query_donor[0]->donor_name, 
           'donor_image'=>$query_donor[0]->image, 
           'donor_mobile'=>$query_donor[0]->donor_mobile, 
           'donor_email'=>$query_donor[0]->donor_email, 
           'donor_age'=>$query_donor[0]->donor_age, 
           'donor_address1'=>$query_donor[0]->donor_address1, 
           'donor_address2'=>$query_donor[0]->donor_address2, 
           'donor_city'=>$query_donor[0]->donor_city, 
           'donor_service'=>$service_name,
           'donor_blood_group'=>$query_donor[0]->donor_blood_group, 
           'donor_discription'=>$query_donor[0]->donor_discription,
           'donor_mobile_status'=>$query_donor[0]->donor_mobile_status,
           'donor_longitude'=>$query_donor[0]->donor_longitude,
           'donor_latitude'=>$query_donor[0]->donor_latitude,
           'donor_status'=>$query_donor[0]->donor_status,
           'distance'=>$distance1,
           'donation_favorite'=>$donation_favorite,
           'last_donation'=>$donation_last_date
        );
    
    return $user_array;   
  }
  
  function camp_list($camp_id, $user_id){
    $result_camp = [];
    $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
    $this->db->select('id as camp_id, title, organise_by, address, date, start_time, end_time, latitude, longitude');
    $this->db->from('camp');
    $this->db->where('status', 1);
    $this->db->where('date >=', date('Y-m-d'));
    $this->db->where('user_id !=', $user_id);
     $this->db->where('id', $camp_id);
    $this->db->order_by('date', 'asc');
    $query_camp = $this->db->get()->result();
    
    if(count($query_camp)>0) {  $i =0;
      foreach($query_camp as $qudo) { $ix =0;  
        
        $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $qudo->latitude, $qudo->longitude);
       
        $participate = $this->db->select('*')->from('camp_participate')->where('camp_id', $qudo->camp_id)
            ->get()->num_rows();
        
        $participate_user = $this->db->select('*')->from('camp_participate')->where('camp_id', $qudo->camp_id)->where('donor_userid', $user_id)
            ->get()->num_rows();
            
        $camp_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>4, 'user_id'=> $user_id, 'category_table_sno'=>$qudo->camp_id))->get()->num_rows();
        
        $camp_image = $this->db->select('id, name')->from('camp_image')->where('camp_id', $qudo->camp_id)->get()->result();
        
        $result_camp = array(
           'camp_id'=>$qudo->camp_id,
           'camp_title'=> $qudo->title,
           'camp_organise_by'=>$qudo->organise_by, 
           'camp_address'=>$qudo->address, 
           'camp_latitude'=>$qudo->latitude, 
           'camp_longitude'=>$qudo->longitude, 
           'camp_date'=>$qudo->date, 
           'camp_start_time'=>$qudo->start_time, 
           'camp_end_time'=>$qudo->end_time, 
           'camp_participate'=>$participate,
           'distance'=>$distance1,
           'camp_favorite'=>$camp_favorite,
           'participate_user'=>$participate_user,
           'camp_image'=>$camp_image
        );
        $service_name =[];
        $i++;
      }
      return $result_camp;
    } else { return $result_camp;}    
    
  }
  
  
  function receiver_response_function($user_id, $receiver_id, $response_id, $status){
  
   $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
   
   $this->db->select('receiver.id as receiver_id, receiver.patient_name, receiver.patient_age, receiver.hospital_name, 
                receiver.address as receiver_address, receiver.bed_no, receiver.ward_no, receiver.created_date as request_date, 
                receiver.doctor_name, receiver.title as receiver_title, service.name as receiver_service_name,  
                receiver.latitude as receiver_latitude, receiver.longitude as receiver_longitude, receiver.status,
                user.mobile as contact_mobile, user.email as contact_email,  user.image,
                location_city.name as receiver_city, blood_group.name as receiver_blood_group');
    $this->db->from('receiver');
    $this->db->where('receiver.id', $receiver_id);
    $this->db->join('user','user.id = receiver.receiver_user_id');
    $this->db->join('service','service.id = receiver.service_id');
    $this->db->join('blood_group','blood_group.id = receiver.blood_group_id');
    $this->db->join('location_city', 'location_city.id = receiver.city_id');
    $query_receiver = $this->db->get()->result();
     
    $response_count = $this->db->select('id')->from('receiver_response')->where('receiver_id', $receiver_id)
                        ->where('response_user_id', $user_id)->get()->num_rows();
        
    $receiver_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>2, 'user_id'=> $user_id, 'category_table_sno'=>$receiver_id))->get()->num_rows();
        
    $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $query_receiver[0]->receiver_latitude, $query_receiver[0]->receiver_longitude);
    
    $user_array = [];
    $user_array = array(
           'receiver_id'=>$receiver_id,
           'patient_name'=>$query_receiver[0]->patient_name,
           'patient_age'=>$query_receiver[0]->patient_age,
           'hospital_name'=>$query_receiver[0]->hospital_name,
           'receiver_address'=>$query_receiver[0]->receiver_address,
           'bed_no'=>$query_receiver[0]->bed_no,
           'ward_no'=>$query_receiver[0]->ward_no,
           'request_date'=>$query_receiver[0]->request_date,
           'doctor_name'=>$query_receiver[0]->doctor_name,
           'receiver_mobile'=>$query_receiver[0]->contact_mobile, 
           'receiver_image'=>$query_receiver[0]->image, 
           'receiver_email'=>$query_receiver[0]->contact_email, 
           'receiver_city'=>$query_receiver[0]->receiver_city, 
           'receiver_service'=>$query_receiver[0]->receiver_service_name,
           'receiver_blood_group'=>$query_receiver[0]->receiver_blood_group, 
           'receiver_title'=>$query_receiver[0]->receiver_title,
           'distance'=>$distance1,
           'favorite'=>$receiver_favorite,
           'status'=>$query_receiver[0]->status,
           'receiver_response'=>$response_count,
           'response_id'=>$response_id,
           'response_status'=>$status
        );
    return $user_array; 
  }
  
  public function upload1($user_id){
    
    if(isset($_FILES['image'])){
      if(!empty($_FILES['image']['name']) && ($_FILES['image']['error']==0 || $_FILES['image']['size'] < 1047152)){
        $uploaddir = './uploads/profile/';
        $config['upload_path'] = './uploads/profile/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size']     = '2048';
        $config['max_width'] = '1024';
        $config['max_height'] = '768';
        
        $this->load->library('upload', $config);
        $this->upload->do_upload('image');
        $this->db->where('id', $user_id);
        return $this->db->update('user', array('image' => $_FILES['image']['name']));
      } else{return false;}
      } else{return false;}
  
  }
  
  public function upload2($user_id){
      if (isset($_FILES["image"]) && !empty($_FILES['image']['name'])) {
         if(!empty($_FILES['image']['name']) && ($_FILES['image']['error']==0 || $_FILES['image']['size'] < 1047152)){
            $uploaddir = './uploads/user_image/';
            $config['upload_path'] = './uploads/user_image/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size']     = '1024';
            $config['overwrite']     = TRUE;
            //$config['max_width'] = '1024';
            //$config['max_height'] = '768';
            
            $this->load->library('upload', $config); 
            
            $fileInfo = pathinfo($_FILES["image"]["name"]);
            $document = basename($_FILES['image']['name']);
            $file_name = $user_id . '.' . $fileInfo['extension'];
            //$_FILES["image"]["name"] = $user_id . '.' . $fileInfo['extension'];
            // $this->upload->do_upload('image');
            // $error = array('error' => $this->upload->display_errors());
            //$data = array('image_metadata' => $this->upload->data());
            // if (!$this->upload->do_upload('profile_image')) {
            //'overwrite' => TRUE,
            move_uploaded_file($_FILES["image"]["tmp_name"], $uploaddir . $file_name);
        
            $this->db->where('id', $user_id);
            $this->db->update('user', array('image' => $file_name));
            return true;             
         } else {return false;}
      }else{return false;}
  }
  
  public function upload($user_id){
      if (isset($_FILES["image"]) && !empty($_FILES['image']['name'])) {
         if(!empty($_FILES['image']['name']) && ($_FILES['image']['error']==0 || $_FILES['image']['size'] < 1047152)){
            $uploaddir = './uploads/user_image/';
            $fileInfo = pathinfo($_FILES["image"]["name"]);
            $file_name = $user_id . '.' . $fileInfo['extension'];
            $config['upload_path'] = './uploads/user_image/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['file_name'] = $file_name;
            $config['max_size']     = '1024';
            $config['overwrite']     = TRUE;
            //$config['max_width'] = '1024';
            //$config['max_height'] = '768';
            
            $this->load->library('upload', $config); 
            
            if (!$this->upload->do_upload('image')) {
                return array('error' => $this->upload->display_errors(), 'image_metadata'=>'');
            }else{
                $data = array('image_metadata' => $this->upload->data(), 'error'=>'');
                $this->db->where('id', $user_id);
                $this->db->update('user', array('image' => $file_name));
                return $data;
            } 
         } else {return array('error' => 'File Perameter Not set', 'image_metadata'=>'');}
      }else{return array('error' => 'Must add file', 'image_metadata'=>'');}
  }
  
}
?>
