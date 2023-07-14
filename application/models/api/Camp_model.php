<?php

class Camp_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    
    
  }
  
  public function get_camp_list($user_id, $distance){
    
    $result_camp = [];
    $user_map = $this->db->select('latitude, longitude')->from('user')->where('id', $user_id)->get()->result();
    if(count($user_map)==0){return false;}
    
    $this->db->select('id as camp_id, user_id as camp_userid, title, organise_by, address, date, start_time, end_time, latitude, longitude');
    $this->db->from('camp');
    $this->db->where('status', 1);
    $this->db->where('date >=', date('Y-m-d'));
    $this->db->where('user_id !=', $user_id);
    $this->db->order_by('date', 'asc');
    $query_camp = $this->db->get()->result();
    
    if(count($query_camp)>0) {  $i =0;
      foreach($query_camp as $qudo) { $ix =0;  
        $participate = $this->db->select('*')->from('camp_participate')->where('camp_id', $qudo->camp_id)
            ->get()->num_rows();
        
        $participate_user = $this->db->select('*')->from('camp_participate')->where('camp_id', $qudo->camp_id)->where('donor_userid', $user_id)
            ->get()->num_rows();
            
        $camp_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>4, 'user_id'=> $user_id, 'category_table_sno'=>$qudo->camp_id))->get()->num_rows();
        
        $distance1 = $this->distance($user_map[0]->latitude, $user_map[0]->longitude, $qudo->latitude, $qudo->longitude);
        
        $camp_image = $this->db->select('id, name')->from('camp_image')->where('camp_id', $qudo->camp_id)->get()->result();
        
        if($distance1<=$distance){
          $user_array = array(
           'camp_id'=>$qudo->camp_id,
           'camp_userid'=>$qudo->camp_userid,
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
        
        $result_camp[$i] = $user_array;
        $service_name =[];
        $i++;
        }
      }
      return $result_camp;
    } else { return $result_camp;}    
  }
  
  public function get_camp($camp_id, $user_id){
      
 //   $this->db->select('*, (SELECT id FROM camp_image WHERE camp_id = '.$camp_id.') as camp_image_id, (SELECT name FROM camp_image WHERE camp_id = '.$camp_id.') as camp_image');

    $this->db->select('*');
    $this->db->from('camp');
    $this->db->where('id', $camp_id);
    $query_camp = $this->db->get()->result();
    
    $participate = $this->db->select('*')->from('camp_participate')->where('camp_id', $camp_id)
            ->get()->num_rows();
    
    $participate_user = $this->db->select('*')->from('camp_participate')->where('camp_id', $camp_id)->where('donor_userid', $user_id)->get()->num_rows();
        
    $camp_favorite = $this->db->select('id')->from('favorite')->where(array('category_id'=>4, 'user_id'=> $user_id, 'category_table_sno'=>$camp_id))->get()->num_rows();
      
    $this->db->select('user.id as donor_userid, user.age, user.blood_group_id, user.mobile, user.name, 
    (SELECT COUNT(*) FROM donation WHERE donor_userid = camp_participate.donor_userid AND camp_id = '.$camp_id.') AS donation_status');
    $this->db->from('camp_participate');
    $this->db->join('user', 'user.id=camp_participate.donor_userid');
    $this->db->where('camp_participate.camp_id', $camp_id);
    $query_participate = $this->db->get()->result();
    
    $query_camp_image = $this->db->select('id, name')->from('camp_image')->where('camp_id', $camp_id)->get()->result();
    
    if(count($query_camp)>0)
    {
        $data['camp'] =  $query_camp;
        $data['camp_image'] =  $query_camp_image;
        $data['participate'] =  $participate;
        $data['participate_user'] =  $participate_user;
        $data['camp_favorite'] =  $camp_favorite;
        $data['camp_participate'] =  $query_participate;
    }else
    {
        $data['camp'] =  [];
    }
        
    return $data;
    
  }
  
  public function save_camp($data_array){
    
    $camp_id = $this->input->post('id');
    $delete_array = $this->input->post('delete_array');
    $delete_array = json_decode($delete_array);
    
    if(!isset($camp_id)){
      $this->db->insert('camp', $data_array);
      $camp_id = $this->db->insert_id();
      
    if(isset($_FILES['image'])){  
      $cpt = count($_FILES['image']['name']);
      $files = $_FILES;
      for($i=0; $i<$cpt; $i++){ 
      if(!empty($files['image']['name'][$i]) && ($files['image']['error'][$i]==0 || $files['image']['size'][$i] < 1047152)){
    
        $this->db->insert('camp_image', array('name' => '', 'camp_id' => $camp_id));
        $camp_image_id = $this->db->insert_id();
        
        $image_upload['name']= $files['image']['name'][$i];
        $image_upload['type']= $files['image']['type'][$i];
        $image_upload['tmp_name']= $files['image']['tmp_name'][$i];
        $image_upload['error']= $files['image']['error'][$i];
        $image_upload['size']= $files['image']['size'][$i];  
        
        $camp_image = $this->upload($image_upload, $camp_id);
        $this->db->where('id', $camp_image_id);
        $this->db->update('camp_image', array('name' => $camp_image));
      } 
      }
    }  
    
    return true;
    
    }elseif(!empty($camp_id)){
      if(count($delete_array)>0){
        foreach($delete_array as $name){
          $file_path  = 'uploads/camp/'.$camp_id . '/'.$name;  // For check file exists base_url() . 
          if(is_file($file_path)){
              
               unlink($file_path); // Delete files 
               $this->db->where('name', $name);   
               $this->db->delete('camp_image');
          }
        }
      }
      
      $camp = $this->db->from('camp')->where('id', $camp_id)->get()->num_rows();  
        
      if($camp>0){
          
        $this->db->where('id', $camp_id);
        $this->db->update('camp', $data_array); 
        
       if(isset($_FILES['image'])){  
          $cpt = count($_FILES['image']['name']);
          $files = $_FILES;
          for($i=0; $i<$cpt; $i++){
          if(!empty($files['image']['name'][$i]) && ($files['image']['error'][$i]==0 || $files['image']['size'][$i] < 1047152)){      
            $camp_image_count = $this->db->from('camp_image')->where('name', $files['image']['name'][$i])->get()->num_rows();  
        //    if($camp_image_count == 0){
            $this->db->insert('camp_image', array('name' => '', 'camp_id' => $camp_id));
            $camp_image_id = $this->db->insert_id();
            
            $image_upload['name']= $files['image']['name'][$i];
            $image_upload['type']= $files['image']['type'][$i];
            $image_upload['tmp_name']= $files['image']['tmp_name'][$i];
            $image_upload['error']= $files['image']['error'][$i];
            $image_upload['size']= $files['image']['size'][$i];  
            
            
            $camp_image = $this->upload($image_upload, $camp_id);
            $this->db->where('id', $camp_image_id);
            $this->db->update('camp_image', array('name' => $camp_image));
           }
          }
        }  
        
        return true;
        
      }else{return false;}
      
      }else{return false;}      
  
  }

  function upload($image_upload, $camp_id) { 
      
    $uploaddir = './uploads/camp/'.$camp_id.'/';
            if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                mkdir('./uploads/camp/' . $camp_id, 0777, TRUE);
            }  
    $config['upload_path'] = './uploads/camp/'.$camp_id.'/';
    $config['allowed_types'] = 'gif|jpg|png|jpeg';
    $config['max_size']     = '2048';
  //$config['max_width'] = '1024';
  //$config['max_height'] = '768';
    
    $this->load->library('upload', $config);
    
    if(!empty($image_upload['name']) && ($image_upload['error']==0 || $image_upload['size']<1047152)){
    $_FILES['image']['name']= $image_upload['name'];
    $_FILES['image']['type']= $image_upload['type'];
    $_FILES['image']['tmp_name']= $image_upload['tmp_name'];
    $_FILES['image']['error']= $image_upload['error'];
    $_FILES['image']['size']= $image_upload['size'];  
    
    $this->upload->do_upload('image');
    
    return $_FILES['image']['name'];
    }else{return '';}
    
   
  }
  
  public function favorite_camp($user_id, $category_id, $category_table_sno){
    
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
  
  public function donation_camp($data){
   
      return  $this->db->insert('donation', $data); 
     
  }
  
  public function participate_camp($user_id, $camp_id){
    
    $query = $this->db->select('id')->where(array('donor_userid'=>$user_id, 'camp_id'=>$camp_id))->get('camp_participate')->result();
    if(count($query)>0)
    {
      $this->db->where('id', $query[0]->id);
      $result =  $this->db->delete('camp_participate');
      if($result == true) {return 2; } 
    }else
    {
      $result =  $this->db->insert('camp_participate', array('donor_userid'=>$user_id, 'camp_id'=>$camp_id, 'status' => 1, 'created_date' => date('Y-m-d H:i:s'))); 
      if($result == true) {return 1; } 
    }
    
  }
  
  public function delete_camp($camp_id){
    $query_camp_participate = $this->db->select('id')->where('camp_id', $camp_id)->get('camp_participate')->result();
    if(count($query_camp_participate)>0){
      return 2;    
    }else{
    $query = $this->db->where('id', $camp_id)->get('camp')->result();
    if(count($query)>0){ 
      $dir_path  = 'uploads/camp/'.$camp_id;  // For check folder exists
      $del_path  = './uploads/camp/'.$camp_id.'/'; // For Delete folder
      if(is_dir($dir_path))
      {
           delete_files($del_path, true); // Delete files into the folder
           rmdir($del_path); // Delete the folder
      }
    
       
     $this->db->where('camp_id', $camp_id);   
     $this->db->delete('camp_image');
     
     $this->db->where('id', $camp_id);   
     $this->db->delete('camp');
     return 1;
    }else{return 0;}
    }
  }
  
  public function status_camp($camp_id){
    $query = $this->db->select('status')->where('id', $camp_id)->get('camp')->result();
    if(count($query)>0)
    {
     if($query[0]->status ==0) $status = 1; else $status = 0   ;
     $this->db->where(array('id'=>$camp_id));   
     $this->db->update('camp', array('status'=>$status)); 
     return ($status+1);
    } 
  }
 
  public function delete_camp_image($id){
    $query = $this->db->select('name, camp_id')->where('id', $id)->get('camp_image')->result();
    if(count($query)>0)
    {
     unlink(base_url("uploads/camp/".$query[0]->camp_id.'/'.$query[0]->name));
     $this->db->where(array('id'=>$id));   
     return $this->db->delete('camp_image'); 
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
  
  public function get_mycamps($user_id){
    $result_camp = [];
    $this->db->select('id as camp_id, user_id as camp_userid, title, organise_by, address, date, start_time, end_time, latitude, longitude');
    $this->db->from('camp');
    $this->db->where('status', 1);
 // $this->db->where('date >=', date('Y-m-d'));
    $this->db->where('user_id', $user_id);
     $this->db->order_by('date', 'asc');
    $query_camp = $this->db->get()->result();
    
    if(count($query_camp)>0) {  $i =0;
      foreach($query_camp as $qudo) { $ix =0;  
        $participate = $this->db->select('*')->from('camp_participate')->where('camp_id', $qudo->camp_id)
            ->get()->num_rows();
        $camp_image = $this->db->select('id, name')->from('camp_image')->where('camp_id', $qudo->camp_id)->get()->result(); 
          $user_array = array(
           'camp_id'=>$qudo->camp_id,
           'camp_userid'=>$qudo->camp_userid,
           'camp_title'=> $qudo->title,
           'camp_organise_by'=>$qudo->organise_by, 
           'camp_address'=>$qudo->address, 
           'camp_latitude'=>$qudo->latitude, 
           'camp_longitude'=>$qudo->longitude, 
           'camp_date'=>$qudo->date, 
           'camp_start_time'=>$qudo->start_time, 
           'camp_end_time'=>$qudo->end_time, 
           'camp_participate'=>$participate,
           'distance'=>0,
           'camp_favorite'=>0,
           'participate_user'=>0,
           'camp_image'=>$camp_image
        );
        
        $result_camp[$i] = $user_array;
        $i++;
        }
      
      return $result_camp;
    } else { return $result_camp;}    
  }
  
  public function get_mycamp($camp_id, $user_id){
      
  //  $this->db->select('*, (SELECT id FROM camp_image WHERE camp_id = '.$camp_id.') as camp_image_id, (SELECT name FROM camp_image WHERE camp_id = '.$camp_id.') as camp_image');
    
    $this->db->select('*, (SELECT id FROM camp_image WHERE camp_id = '.$camp_id.') as camp_image_id');
    $this->db->from('camp');
    $this->db->where('id', $camp_id);
    $query_camp = $this->db->get()->result();
    
    $participate = $this->db->select('*')->from('camp_participate')->where('camp_id', $camp_id)
            ->get()->num_rows();
      
    $this->db->select('user.id as user_id, user.age, user.blood_group_id, user.mobile, user.name');
    $this->db->from('camp_participate');
    $this->db->join('user' , 'user.id=camp_participate.donor_userid');
    $this->db->where('camp_participate.camp_id', $camp_id);
    $query_participate = $this->db->get()->result();
    
    $query_camp_image = $this->db->select('id, name')->from('camp_image')->where('camp_id', $camp_id)->get()->result();
    
    
    if(count($query_camp)>0)
    {
        $data['camp'] =  $query_camp;
        $data['camp_image'] =  $query_camp_image;
        $data['participate'] =  $participate;
        $data['participate_user'] =  0;
        $data['camp_favorite'] =  0;
        $data['camp_participate'] =  $query_participate;
    }else
    {
        $data['camp'] =  [];
    }
        
    return $data;
    
  }
  
}
 ?>
