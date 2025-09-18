<?php
class Erp extends CI_Controller
{
  var $user_id;
  var $db;
  var $url;
  var $password;
  var $ep_common = "xmlrpc/2/common";
  var $ep_object = "xmlrpc/2/object";
  var $resp_error_code = 404;
  var $resp_success_code = 200;

  //var $t_db = "12_pii_dev2";
  //var $t_url = "http://sumihai.ddns.net:12070";
  //var $t_db = "UAT_2";
  var $t_db = "PII_LIVE";
  //var $t_url = "http://117.53.45.236:12059";
  var $t_url = "http://34.87.72.186:12069/";
  var $t_username = "admin01";
  //var $t_password = "a";
  var $t_password = "@6789";
  var $parameter_harga_tahunan = 300000;

  var $UP_2024 = 18;
  var $UKT_2024 = 19;
  var $IP_2024 = 20;


  public function __construct()
  {
    parent::__construct();

    date_default_timezone_set('Asia/Jakarta');
    $this->load->model('main_mod');

    $this->load->library('ripcord/ripcord');
    $this->load->helper('response');
    //$this->load->config ( 'odoo' );
    //$t_db = '12_pii_dev2'; //$this->config->item ( 'db' );
    //$t_url = 'http://sumihai.ddns.net:12070'; //$this->config->item ( 'url' );
    // GETTING U/P FROM URL REQUEST
    $username = $this->t_username; //$this->input->post_get ( 'u' );
    //$t_password = 'a'; //$this->input->post_get ( 'p' );

    // AUTH
    $this->user_id = $this->ripcord->client($this->t_url . $this->ep_common)->authenticate($this->t_db, $username, $this->t_password, array());
    if ((int) $this->user_id == 0) {
      send_response($this->resp_error_code, 'Wrong Credential');
    }
  }
  public function index()
  {
    print "TEST";
    #$this->_resp_success ( 'Hello From API' );
  }
  // ######################
  // ##### CORES ##########
  // ######################
  private function _call($obj, $method, $arg1 = array(), $arg2 = array())
  {
    $exec = $this->ripcord->client($this->t_url . $this->ep_object)->execute_kw($this->t_db, $this->user_id, $this->t_password, $obj, $method, $arg1, $arg2);
    log_message('debug', '[SIMPONI] ' . __CLASS__ . ' ' . __FUNCTION__ . ' - Odoo Response: ' . @json_encode($exec));
    $this->_resp_error($exec);
    $this->_resp_success($exec);
  }
  private function _call_ada($obj, $method, $arg1 = array(), $arg2 = array())
  {
    $exec = $this->ripcord->client($this->t_url . $this->ep_object)->execute_kw($this->t_db, $this->user_id, $this->t_password, $obj, $method, $arg1, $arg2);
    $this->_resp_error($exec);
    //$this->_resp_success ( $exec );
    return ($exec);
  }
  private function _call_search_read($obj, $domain = array(), $params = array())
  {
    $this->_call($obj, 'search_read', $domain, $params);
  }
  private function _call_search_read_ada($obj, $domain = array(), $params = array())
  {
    return $this->_call_ada($obj, 'search_read', $domain, $params);
  }
  private function _call_read($obj, $id = 0, $fields = array('name'))
  {
    $this->_call($obj, 'read', array(
      $id
    ), $fields);
  }
  private function _call_write($obj, $id = 0, $data = array())
  {
    $this->_call($obj, 'write', array(
      array(
        $id
      ),
      $data
    ));
  }
  private function _call_write_ada($obj, $id = 0, $data = array())
  {
    return $this->_call_ada($obj, 'write', array(
      array(
        $id
      ),
      $data
    ));
  }
  private function _call_create($obj, $data = array())
  {
    $this->_call($obj, 'create', $data);
  }
  private function _call_create_ada($obj, $data = array())
  {
    return $this->_call_ada($obj, 'create', $data);
  }
  private function _call_unlink($obj, $id = 0)
  {
    $this->_call($obj, 'unlink', array(
      array(
        $id
      )
    ));
  }
  private function _call_custom($obj, $method, $id = 0)
  {
    $this->_call($obj, $method, array(
      $id
    ));
  }
  private function _resp_error($exec)
  {
    if (isset($exec['faultCode'])) {
      send_response($this->resp_error_code, $exec['faultString']);
    } else {
      log_message("debug", "[SIMPONI] _resp_error - Checking if this is error? faultCode not found.  Response: " . json_encode($exec));
    }
  }
  private function _resp_success($exec)
  {
    send_response($this->resp_success_code, 'OK', $exec);
  }

  // ######END OF CORES######

  // MASTER
  public function asset()
  {
    $this->_call_search_read('hr.equipment');
  }
  public function order()
  {
    $this->_call_search_read('kt.order');
  }
  public function partner_one_id()
  {
    $id = (int) $this->uri->segment(6, 0);
    $this->_call_read('res.partner', array(
      $id
    ), array(
      'fields' => array(
        'name'
      )
    ));
  }
  public function partner_unlink()
  {
    $id = (int) $this->uri->segment(6, 0);
    $this->_call_unlink('res.partner', $id);
  }
  public function partner()
  {
    $result = $this->_call_search_read('res.partner', array(
      array(
        array(
          'customer',
          '=',
          TRUE
        )
      )
    ), array(
      'fields' => array(
        'name',
        'email',
        'no_kta',
        'ref'
      )
    ));

    echo "RESULT:\n";
    print_r($result);
  }
  public function country()
  {
    $this->_call_search_read('res.country');
  }
  public function state()
  {
    $this->_call_search_read('res.country.state', array(
      array(
        array(
          'country_id',
          '=',
          100
        )
      )
    ), array(
      'fields' => array(
        'name',
        'ref'
      )
    ));
  }
  public function wilayah()
  {
    $this->_call_search_read('res.partner', array(
      array(
        array(
          'is_wilayah',
          '=',
          TRUE
        )
      )
    ), array(
      'fields' => array(
        'name',
        'ref'
      )
    ));
  }
  public function cabang()
  {
    $this->_call_search_read('res.partner', array(
      array(
        array(
          'is_cabang',
          '=',
          TRUE
        )
      )
    ), array(
      'fields' => array(
        'name',
        'ref'
      )
    ));
  }
  public function bk()
  {
    $this->_call_search_read('res.partner', array(
      array(
        array(
          'is_bk',
          '=',
          TRUE
        )
      )
    ), array(
      'fields' => array(
        'name',
        'ref'
      )
    ));
  }
  public function first_title()
  {
    $this->_call_search_read('res.partner.ft', array(
      array()
    ), array(
      'fields' => array(
        'display_name',
        'ref'
      )
    ));
  }
  public function last_title()
  {
    $this->_call_search_read('res.partner.lt', array(
      array()
    ), array(
      'fields' => array(
        'display_name',
        'ref'
      )
    ));
  }
  public function payment_term()
  {
    $this->_call_search_read('account.payment.term');
  }
  public function product()
  {
    $this->_call_search_read('product.product');
  }


  public function partner_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.partner', array(
      array(
        array(
          'customer',
          '=',
          TRUE
        ),
        array(
          $where,
          '=',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'email',
        'no_kta',
        'ref',
        'bk_ids'
      )
    ));
  }
  public function country_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.country', array(
      array(
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'display_name',
        'ref'
      )
    ));
  }
  public function state_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.country.state', array(
      array(
        array(
          'country_id',
          '=',
          100
        ),
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'display_name',
        'ref'
      )
    ));
  }
  public function wilayah_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.partner', array(
      array(
        array(
          'is_wilayah',
          '=',
          TRUE
        ),
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'display_name',
        'ref'
      )
    ));
  }
  public function cabang_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.partner', array(
      array(
        array(
          'is_cabang',
          '=',
          TRUE
        ),
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'display_name',
        'ref'
      )
    ));
  }
  public function bk_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.partner', array(
      array(
        array(
          'is_bk',
          '=',
          TRUE
        ),
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'name',
        'display_name',
        'ref'
      )
    ));
  }
  public function experience_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('res.experience', array(
      array(
        array(
          $where,
          'in',
          $str
        )
      )
    ), array(

      'limit' => 5
    ));

    /*$this->_call_read ( 'res.experience', array (
				$str 
		), array (
				'fields' => array (
						'name' 
				) 
		) );*/
  }
  public function payment_one($str = null, $where = null)
  {
    $str = $this->input->get('val') <> null ? $this->input->get('val') : $str;
    $where = $this->input->get('where') <> null ? $this->input->get('where') : $where;
    return $this->_call_search_read_ada('payment.request', array(
      array(
        array(
          $where,
          'like',
          $str
        )
      )
    ), array(
      'fields' => array(
        'billkey1',
        'trxdatetime_func',
        'payment_ids',
        'ref',
        'bk_ids'
      )
    ));
  }


  // #### TRANS

  public function reg_member()
  {
    $id_pay = $this->input->get('id') <> null ? $this->input->get('id') : $this->session->userdata('id');
    $id = $this->input->get('user_id') <> null ? $this->input->get('user_id') : $this->session->userdata('user_id');

    //$id = $this->input->get('user_id');
    $sukarela = $this->input->get('sukarela') <> null ? $this->input->get('sukarela') : 0;
    $pangkal = $this->input->get('pangkal') <> null ? $this->input->get('pangkal') : 0;
    $tahunan = $this->input->get('tahunan') <> null ? $this->input->get('tahunan') : 0;
    $typeX = $this->input->get('type') <> null ? $this->input->get('type') : 0;
    $totalX = $this->input->get('total') <> null ? $this->input->get('total') : 0;
    $tipe_faip = $this->input->get('tipe_faip') <> null ? $this->input->get('tipe_faip') : 0;
    $tahun_her = $this->input->get('tahun_her') <> null ? $this->input->get('tahun_her') : 0;

    /*$id = 30168;
		$sukarela = 1000000;
		$pangkal = 0;
		$tahunan = 900000;*/

    $check_wna = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->row();
    $is_wna = 0;
    if ($check_wna->warga_asing == 1) $is_wna = 1;

    $seq = $tahunan / $this->parameter_harga_tahunan;

    if ($is_wna == 1) {
      $check_periode = $this->main_mod->msrwhere('members', array('person_id' => $id), 'id', 'desc')->row();
      $tahun = 1;
      if (isset($check_periode->thru_date)) {
        $tm = substr($check_periode->thru_date, 0, 4);
        $tahun = date('Y') - $tm;
      }
      $seq = $tahun;
    }

    $validity_date = date('Y-m-d', strtotime('+' . $seq . ' years'));

    $members = $this->main_mod->msrwhere('members', array('person_id' => $id), 'person_id', 'desc')->result();

    $temp = $this->partner_one($id, "ref");
    $is_exist = "0";
    $rel_detail = 0;
    if (isset($temp[0]['id'])) {
      if (count($temp) == 1) {
        $is_exist = $temp[0]['id'];
        $rel_detail = 1;
      }
    }

    if (isset($members[0]->no_kta) && $is_exist == "0") {
      $temp = $this->partner_one(str_pad($members[0]->no_kta, 6, "0", STR_PAD_LEFT), "no_kta");
      if (isset($temp[0]['id'])) {
        if (count($temp) == 1) {
          $is_exist = $temp[0]['id'];
          $rel_detail = 1;
        }
      }
    }

    if ($is_exist == "0") {
      $temp = $this->partner_one($id, "simponi_uid");
      if (isset($temp[0]['id'])) {
        if (count($temp) == 1) {
          $is_exist = $temp[0]['id'];
          $rel_detail = 1;
        }
      }
    }


    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    //$user_edu=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id,'status'=>1,'type'=>1),'user_id','desc')->result();	
    //$user_exp=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id,'status'=>1),'user_id','desc')->result();
    //$user_cert=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>1),'user_id','desc')->result();		
    $users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->result();
    $user_address = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1, 'is_mailing' => 1), 'user_id', 'desc')->result();
    $user_address2 = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1, 'is_mailing<>1' => null), 'user_id', 'desc')->result();
    $contacts = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'status' => 1), 'user_id', 'desc')->result();

    $function = '';
    $f_title = '';
    $l_title = '';

    $b64image = '';
    if ($user_profiles[0]->photo != '') {
      if (strtolower(substr($user_profiles[0]->photo, -4)) != '.pdf')
        $b64image = base64_encode(@file_get_contents(base_url() . 'assets/uploads/' . $user_profiles[0]->photo));
    }

    $country_id = "100";
    $state_id = "";
    if (isset($user_address[0]->province)) {
      $state = $this->state_one(str_replace("Provinsi ", "", $user_address[0]->province), "name");
      $state_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      //print_r($state);
    }
    $tags = "new";
    $wilayah_id = "";
    $cabang_id = "";
    $bk_ids = "";
    if (isset($members[0]->no_kta)) {
      $tags = "non-active";
      if ($members[0]->thru_date >= date("Y-m-d")) $tags = "active";
      $state = $this->wilayah_one(str_pad(substr($members[0]->code_wilayah, 0, 2), 2, '0', STR_PAD_LEFT), "ref");
      $wilayah_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      $state = $this->cabang_one(str_pad($members[0]->code_wilayah, 4, "0", STR_PAD_LEFT), "ref");
      $cabang_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      $state = $this->bk_one(str_pad($members[0]->code_bk_hkk, 2, "0", STR_PAD_LEFT), "ref");
      $bk_ids = (isset($state[0]['id']) ? $state[0]['id'] : "");

      $date = strtotime($members[0]->thru_date);
      $validity_date = date('Y-m-d', strtotime("+" . $seq . " year", $date));
    }

    //EDUCATION
    $edu_arr = array();
    /*$i=0;
		foreach($user_edu as $val){
			
			$att ='';
			if($val->attachment!='')
				$att = base64_encode(@file_get_contents(base_url().'assets/uploads/'.$val->attachment));
			$edu_arr [$i][0]= $rel_detail;
			$edu_arr [$i][1]= 0;
			$edu_arr [$i][] = array(
				'name'=>(isset($val->school)?$val->school:''),
				'start_year'=>(isset($val->startdate)?($val->startdate!='')?$val->startdate:1970:1970),
				'end_year'=>(isset($val->enddate)?($val->enddate!='')?$val->enddate:1970:1970),
				'degree'=>(isset($val->degree)?strtolower(($val->degree=='S1/D4' || $val->degree=='')?'S1':$val->degree):''),
				'majors'=>(isset($val->mayor)?($val->mayor):''),
				'score'=>(isset($val->score)?str_replace(',', '.', $val->score):''),
				'title'=>(isset($val->title)?($val->title):''),
				'organization'=>(isset($val->activities)?($val->activities):''),
				'description'=>(isset($val->description)?($val->description):''),
				'attachment_edu'=>$att
				);
			
			$f_title = $f_title.(isset($val->title_prefix)?', '.$val->title_prefix:'');
			$l_title = $l_title.(isset($val->title)?', '.$val->title:'');		
				
			$i++;
		}*/

    //EXP
    $exp_arr = array();
    /*$i=0;
		foreach($user_exp as $val){
			
			//$att ='';
			//if($val->attachment!='')
			//	$att = base64_encode(@file_get_contents(base_url().'assets/uploads/'.$val->attachment));
			$exp_arr [$i][0]= $rel_detail;
			$exp_arr [$i][1]= 0;
			
			if($val->startmonth=='') $val->startmonth = 1;
			//if($val->endmonth=='' && $val->is_present!='1') {$val->endmonth = 1;}
			
			$function = isset($val->title)?($val->title):'';
			
			$exp_arr [$i][] = array(
				'name'=>(isset($val->company)?$val->company:''),
				'start_date'=>str_pad($val->startyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->startmonth,2,"0", STR_PAD_LEFT).'-01',
				'end_date'=>($val->is_present!='1'?str_pad($val->endyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->endmonth,2,"0", STR_PAD_LEFT).'-01':false),
				'currently'=>($val->is_present=='1'?"true":"false"),
				'position'=>(isset($val->title)?($val->title):''),
				'street_1'=>(isset($val->provinsi)?($val->provinsi):''),
				'street_2'=>(isset($val->negara)?($val->negara):''),
				'city'=>(isset($val->location)?($val->location):''),
				'description'=>(isset($val->description)?($val->description):''),
				//'attachment_exp'=>$att
				);
			$i++;
		}*/

    //CERT
    $cert_arr = array();
    /*$i=0;
		foreach($user_cert as $val){			
			$att ='';
			if($val->attachment!='')
				$att = base64_encode(@file_get_contents(base_url().'assets/uploads/'.$val->attachment));
			if($val->cert_name!=''){
				$cert_arr [$i][0]= $rel_detail;
				$cert_arr [$i][1]= 0;
				$cert_arr [$i][] = array(
					'certification_name'=>(isset($val->cert_name)?$val->cert_name:''),
					'start_month'=>str_pad($val->startyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->startmonth,2,"0", STR_PAD_LEFT).'-01',
					'end_month'=>str_pad($val->endyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->endmonth,2,"0", STR_PAD_LEFT).'-01',
					'certification_auth'=>(isset($val->cert_auth)?($val->cert_auth):''),
					'license_no'=>(isset($val->lic_num)?($val->lic_num):''),
					'certification_url'=>(isset($val->cert_url)?($val->cert_url):''),
					'description'=>(isset($val->cert_title)?($val->cert_title):''),
					'attachment'=>$att
					);
				$i++;
			}
		}*/

    //CHILD
    $child_arr = array();
    $i = 0;
    foreach ($user_address2 as $val) {
      if ($val->address != '') {
        $child_arr[$i][0] = $rel_detail;
        $child_arr[$i][1] = 0;
        $child_arr[$i][] = array(
          'type' => 'other',
          'street' => (isset($val->address) ? $val->address : ''),
          'street2' => (isset($val->province) ? $val->province : ''),
          'city' => (isset($val->city) ? $val->city : ''),
          'state_id' => '',
          'zip' => (isset($val->zipcode) ? $val->zipcode : ''),
          'country_id' => '',
          'email' => (isset($val->email) ? $val->email : ''),
          'phone' => (isset($val->phone) ? $val->phone : ''),
        );
        $i++;
      }
    }
    foreach ($contacts as $val) {
      if ($val->contact_value != '') {
        $child_arr[$i][0] = $rel_detail;
        $child_arr[$i][1] = 0;
        $child_arr[$i][] = array(
          'name' => $val->contact_type,
          'type' => 'contact',
          'email' => (isset($val->contact_value) ? (strpos($val->contact_type, '_email') !== false) ? $val->contact_value : '' : ''),
          'phone' => (isset($val->contact_value) ? (strpos($val->contact_type, '_phone') !== false) ? $val->contact_value : '' : ''),
        );
        $i++;
      }
    }
    //print_r($child_arr);

    /*$att_arr =  [{
                'res_model' : 'res.partner',
                'name' : 'WinUpdate Image',
                'res_id' : id,
                'type' : 'binary',
                'mimetype' : 'image/png',
                'store_fname' : 'WinUpdate1.png',
                'datas' : imageBase64
                }];*/

    $dob = $user_profiles[0]->dob;
    if ($user_profiles[0]->dob == '0000-00-00' || $user_profiles[0]->dob == '' || $user_profiles[0]->dob == 'NULL') $dob = '1970-01-01';

    $update_data = array(
      'ref'          => $id,
      'company_type'      => 'person',
      'image'          => $b64image,
      'name'          => $user_profiles[0]->firstname . ($user_profiles[0]->lastname != "" ? ' ' . $user_profiles[0]->lastname : ''),
      'first_name'      => $user_profiles[0]->firstname,
      'last_name'        => $user_profiles[0]->lastname,
      'no_kta'        => (isset($members[0]->no_kta) ? str_pad($members[0]->no_kta, 6, "0", STR_PAD_LEFT) : ''),
      'street'        => (isset($user_address[0]->address) ? $user_address[0]->address : ''),
      'street2'        => "",
      'city'          => (isset($user_address[0]->city) ? $user_address[0]->city : ''),
      'state_id'        => $state_id,
      'zip'          => (isset($user_address[0]->zipcode) ? $user_address[0]->zipcode : ''),
      'country_id'      => $country_id,


      'wilayah_id'      => $wilayah_id,
      'cabang_id'        => $cabang_id,
      'bk_ids'        => array(array(6, 0, array($bk_ids))), //array($bk_ids),
      'gender'        => $user_profiles[0]->gender == '' ? 'male' : strtolower($user_profiles[0]->gender),
      'pob'          => $user_profiles[0]->birthplace,
      'dob'          => $dob,
      'type_id'        => ($user_profiles[0]->idtype == ' ' || $user_profiles[0]->idtype == '') ? 'citizen' : strtolower($user_profiles[0]->idtype),
      'id_number'        => $user_profiles[0]->idcard,

      'mandiri_ar_va'      => $user_profiles[0]->va,
      'simponi_uid'      => $user_profiles[0]->user_id,
      'simponi_id'      => (isset($members[0]->person_id) ? $members[0]->person_id : ''),
      'vat'          => "",

      'function'        => $function,
      'phone'          => "",
      'mobile'        => $user_profiles[0]->mobilephone,
      'email'          => $users[0]->email,
      'website'        => $user_profiles[0]->website,
      'f_title'        => "",
      'l_title'        => "",
      'publication_material'  => ($user_profiles[0]->is_public == "1" ? "true" : "false"),
      'submit_personal_data'  => ($user_profiles[0]->is_datasend == "1" ? "true" : "false"),
      'tags'          => $tags,

      'education_ids'      => $edu_arr,
      'experience_ids'    => $exp_arr,
      'exp_certificate_ids'  => $cert_arr,
      'child_ids'        => $child_arr,

      //'ir.attachment'			=> $att_arr,

      'property_account_receivable_id'    => 936,
      'property_account_payable_id'      => 743,

    );




    //print_r($update_data);
    //print_r($typeX);
    //print_r($temp);


    log_message("debug", "[SIMPONI] Erp reg_member - We will create ORDER. Checking type based on passing param, type: " . $typeX);
    if ($typeX == '1') {
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();
      if ($pay->order_id == 0) $this->reg_order($is_exist, $bk_ids, $validity_date, $sukarela, $pangkal, $tahunan, $typeX, $tahun_her, $is_wna);
    } else if ($typeX == '2') {
      $is_exist2 = '';
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();

      log_message("debug", "[SIMPONI] Erp reg_member - Checking if Odoo's SO id is already exist for this VA. " . $pay->order_id);
      if ($pay->order_id == 0) {
        //CHECK PERIODE
        $check_periode = $this->main_mod->msrwhere('members', array('person_id' => $user_profiles[0]->user_id), 'id', 'desc')->row();
        $tahun = 1;
        if (isset($check_periode->thru_date)) {
          $tm = substr($check_periode->thru_date, 0, 4);
          $tahun = date('Y') - $tm;
        }

        log_message("debug", "[SIMPONI] Erp reg_member - Checking if this payment is for more than 1 years ago. Years: " . $tahun);
        if ($tahun > 1) {
          $pay2 = $this->main_mod->msrwhere('user_transfer_detail', array('pay_id' => $id_pay, 'tahun' => $tahun_her), 'id', 'desc')->row();
          if (!isset($pay2->order_id)) {
            log_message("debug", "[SIMPONI] Erp reg_member - UNIQCODE-P21 Calling Odoo to crease Sale Order");
            $this->reg_order($is_exist, $bk_ids, $validity_date, $sukarela, $pangkal, $tahunan, $typeX, $tahun_her, $is_wna);
          } else {
            log_message("debug", "[SIMPONI] Erp reg_member - user_transfer_detail already have HER year (tahun_her) " . $tahun_her . " ..so I won't create an Sale Order in Odoo");
          }
        } else {
          log_message("debug", "[SIMPONI] Erp reg_member - UNIQCODE-P22 Calling Odoo to crease Sale Order");
          $this->reg_order($is_exist, $bk_ids, $validity_date, $sukarela, $pangkal, $tahunan, $typeX, $tahun_her, $is_wna);
        }
      } else {
        log_message("debug", "[SIMPONI] The VA entry has Odoo's SO id  ..so I won't create an Sale Order in Odoo");
      }
    } else if ($typeX == '3') {
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();
      if ($pay->order_id == 0) $this->reg_order_umum($is_exist, $bk_ids, $validity_date, $totalX, 444, $this->IP_2024, 'IP Awal (IPP)'); //IPP
    } else if ($typeX == '4') {
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);
      $name = 'IP Awal (IPP)';
      $tag = $this->IP_2024; //IPP
      $p = 444;
      if ($totalX == '1650000') {
        $p = 445;
        $tag = $this->IP_2024;
        $name = 'Fullpayment IPM';
      } //IPM 
      else if ($totalX == '2200000') {
        $p = 446;
        $tag = $this->IP_2024;
        $name = 'Fullpayment IPU';
      } //IPU 
      else if ($tipe_faip == 'IPM') {
        $p = 472;
        $tag = $this->IP_2024;
        $name = 'IPM Pelunasan';
      } //IPM 
      else if ($tipe_faip == 'IPU') {
        $p = 473;
        $tag = $this->IP_2024;
        $name = 'IPU Pelunasan';
      } //IPU 

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();
      if ($pay->order_id == 0) $this->reg_order_umum($is_exist, $bk_ids, $validity_date, $totalX, $p, $tag, $name);
    } else if ($typeX == '6') {
      //echo 'a';
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();
      if ($pay->order_id == 0) {
        $this->reg_order($is_exist, $bk_ids, $validity_date, $sukarela, $pangkal, $tahunan, $typeX, $totalX, $tipe_faip);
      }
    } else if ($typeX == '7') {
      if ($is_exist == "0")
        $is_exist = $this->_call_create_ada('res.partner', array($update_data));
      else $is_exist2 = $this->_call_write_ada('res.partner', $is_exist, $update_data);
      $name = 'IPP Perpanjang';
      $tag = $this->IP_2024; //IPP
      $p = 490;
      if ($tipe_faip == 'IPU') {
        $p = 491;
        $tag = $this->IP_2024;
        $name = 'IPU Perpanjang (Peningkatan)';
      } //IPM 
      else if ($tipe_faip == 'IPM') {
        $p = 492;
        $tag = $this->IP_2024;
        $name = 'IPM Perpanjang (Peningkatan)';
      } //IPU 

      $pay = $this->main_mod->msrwhere('user_transfer', array('id' => $id_pay), 'id', 'desc')->row();
      if ($pay->order_id == 0) $this->reg_order_umum($is_exist, $bk_ids, $validity_date, $totalX, $p, $tag, $name);
    } else {
      log_message("debug", "[SIMPONI] Erp reg_member - Type of payment is not recognized, so I won't create an Sale Order in Odoo");
    }


    /*
		array( 
				array(0,0,array (
				'name'=>(isset($user_edu[0]->school)?$user_edu[0]->school:''),
				'start_year'=>(isset($user_edu[0]->startdate)?$user_edu[0]->startdate:''),
				'end_year'=>(isset($user_edu[0]->enddate)?$user_edu[0]->enddate:''),
				'degree'=>(isset($user_edu[0]->degree)?strtolower($user_edu[0]->degree):''),
				'majors'=>(isset($user_edu[0]->mayor)?($user_edu[0]->mayor):''),
				'score'=>(isset($user_edu[0]->score)?($user_edu[0]->score):''),
				'title'=>(isset($user_edu[0]->title)?($user_edu[0]->title):'')
				))
			)
		*/
  }
  public function reg_order($partner_id = null, $bk_id = null, $validity_date = null, $sukarela = 0, $pangkal = 0, $tahunan = 0, $type = 0, $tahun_her = 0, $is_wna = 0)
  {
    $partner_id = $this->input->get('partner_id') <> null ? $this->input->get('partner_id') : $partner_id;
    $bk_id = $this->input->get('bk_id') <> null ? $this->input->get('bk_id') : $bk_id;
    $validity_date = $this->input->get('validity_date') <> null ? $this->input->get('validity_date') : $validity_date; //"01/01/2021"
    $sukarela = $this->input->get('sukarela') <> null ? $this->input->get('sukarela') : $sukarela;
    $pangkal = $this->input->get('pangkal') <> null ? $this->input->get('pangkal') : $pangkal;
    $tahunan = $this->input->get('tahunan') <> null ? $this->input->get('tahunan') : $tahunan;
    $type = $this->input->get('type') <> null ? $this->input->get('type') : $type;
    $tahun_her = $this->input->get('tahun_her') <> null ? $this->input->get('tahun_her') : $tahun_her;
    $is_wna = $this->input->get('is_wna') <> null ? $this->input->get('is_wna') : $is_wna;
    $validity_date = date('Y') . '-12-31';
    if ($partner_id == null) {
      $id = $this->session->userdata('user_id');
      //$id = 30168;
      $temp = $this->partner_one($id, "ref");
      if (isset($temp[0]['id'])) {
        if (count($temp) == 1) {
          $partner_id = $temp[0]['id'];
        }
      }
    }
    if ($partner_id != null) {
      $arr = array();
      if ($type == 2) {
        //HER
        /*$arr = array (
					'partner_id'=>(int)$partner_id,
					'bk_id'=>$bk_id,
					'validity_date'=>$validity_date,
					'payment_term_id'=>"1",
					'order_line'=>array( 
						array(0,0,array (
						'product_id'=>441,
						'product_uom_qty'=>1,//($tahunan/$this->parameter_harga_tahunan),
						'price_unit'=>$this->parameter_harga_tahunan,
						'analytic_tag_ids'=> array(array(6,0,array(1))),
						'name'=>'Iuran Anggota Perpanjang',				 
						)),
						$arr_sukarela
					)
				);*/
        $arr_her = array();
        if ($sukarela == 0) {
          $arr_her = array(
            array(0, 0, array(
              //'product_id'=>($is_wna==1)?469:441,
              'product_id' => ($is_wna == 1) ? 469 : 483,
              'product_uom_qty' => 1, //($tahunan/$this->parameter_harga_tahunan),
              'price_unit' => $tahunan, //$this->parameter_harga_tahunan,
              //'analytic_tag_ids'=> ($is_wna==1)?'':array(array(6,0,array(1))),
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UKT_2024))),
              'name' => $tahun_her, //'Iuran Anggota Perpanjang',				 
            )),
          );
        } else {
          $arr_her = array(
            array(0, 0, array(
              //'product_id'=>($is_wna==1)?469:441,
              'product_id' => ($is_wna == 1) ? 469 : 483,
              'product_uom_qty' => 1, //($tahunan/$this->parameter_harga_tahunan),
              'price_unit' => $tahunan, //$this->parameter_harga_tahunan,
              //'analytic_tag_ids'=> ($is_wna==1)?'':array(array(6,0,array(1))),
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UKT_2024))),
              'name' => $tahun_her, //'Iuran Anggota Perpanjang',				 
            )),
            array(0, 0, array(
              'product_id' => 447,
              'product_uom_qty' => 1,
              'price_unit' => $sukarela,
              'name' => 'Iuran Sukarela'
            ))
          );
        }
        $arr = array(
          'partner_id' => (int)$partner_id,
          'bk_id' => $bk_id,
          'validity_date' => $validity_date,
          'payment_term_id' => "1",
          'order_line' => $arr_her
        );
      } else if ($type == 1) {
        //REG
        /*$arr = array (
					'partner_id'=>(int)$partner_id,
					'bk_id'=>$bk_id,
					'validity_date'=>$validity_date,
					'payment_term_id'=>"1",
					'order_line'=>array( 
						array(0,0,array (
						'product_id'=>436, //Iuran Pendaftaran
						'product_uom_qty'=>1,
						'price_unit'=>$pangkal,						
						'name'=>'Iuran Pendaftaran'
						)),
						array(0,0,array (
						'product_id'=>($tahunan==300000?439:470), //Iuran Anggota Baru (gelombang 1 Jan sd Jun)
						'product_uom_qty'=>1,//($tahunan/$this->parameter_harga_tahunan),
						'price_unit'=>$tahunan,
						'analytic_tag_ids'=> array(array(6,0,array(($tahunan==300000?1:11)))),
						'name'=>($tahunan==300000?'Iuran Anggota Baru (gelombang 1 Jan sd Jun)':'Iuran Anggota Baru (gelombang 2 Jul sd Des)')																																			 
						)),
						$arr_sukarela
					)
				);*/

        $month = date('m');
        $val_product = 0;
        $val_product2 = "";
        if ($month >= 1 && $month <= 3) {
          $val_product = 479;
          $val_product2 = "Iuran Anggota Baru (Kuartal 1)";
        } else if ($month >= 4 && $month <= 6) {
          $val_product = 480;
          $val_product2 = "Iuran Anggota Baru (Kuartal 2)";
        } else if ($month >= 7 && $month <= 9) {
          $val_product = 481;
          $val_product2 = "Iuran Anggota Baru (Kuartal 3)";
        } else if ($month >= 10 && $month <= 12) {
          $val_product = 482;
          $val_product2 = "Iuran Anggota Baru (Kuartal 4)";
        }

        $arr_reg = array();
        if ($sukarela == 0) {
          $arr_reg = array(
            array(0, 0, array(
              'product_id' => ($is_wna == 1) ? 437 : 436, //Iuran Pendaftaran
              'product_uom_qty' => 1,
              'price_unit' => $pangkal,
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UP_2024))),
              'name' => ($is_wna == 1) ? 'Iuran Pendaftaran Asing' : 'Iuran Pendaftaran'
            )),
            array(0, 0, array(
              //'product_id'=>($is_wna==1)?469:($tahunan==300000?439:470), //Iuran Anggota Baru (gelombang 1 Jan sd Jun)
              'product_id' => ($is_wna == 1) ? 469 : $val_product, //Iuran Anggota Baru (gelombang 1 Jan sd Jun) ($tahunan==300000?439:481)
              'product_uom_qty' => 1, //($tahunan/$this->parameter_harga_tahunan),
              'price_unit' => $tahunan,
              //'analytic_tag_ids'=> ($is_wna==1)?'':array(array(6,0,array(($tahunan==300000?1:11)))),
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UKT_2024))), //($tahunan==300000?15:15)
              'name' => ($is_wna == 1) ? 'Iuran Anggota Asing' : $val_product2

              //($tahunan==300000?'Iuran Anggota Baru (gelombang 1 Jan sd Jun)':'Iuran Anggota Baru (Kuartal 3)')																																			 
              //'name'=>($is_wna==1)?'Iuran Anggota Asing':($tahunan==300000?'Iuran Anggota Baru (gelombang 1 Jan sd Jun)':'Iuran Anggota Baru (gelombang 2 Jul sd Des)')																																			 
            )),
          );
        } else {
          $arr_reg = array(
            array(0, 0, array(
              'product_id' => ($is_wna == 1) ? 437 : 436, //Iuran Pendaftaran
              'product_uom_qty' => 1,
              'price_unit' => $pangkal,
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UP_2024))),
              'name' => ($is_wna == 1) ? 'Iuran Pendaftaran Asing' : 'Iuran Pendaftaran'
            )),
            array(0, 0, array(
              //'product_id'=>($is_wna==1)?469:($tahunan==300000?439:470), //Iuran Anggota Baru (gelombang 1 Jan sd Jun)
              'product_id' => ($is_wna == 1) ? 469 : $val_product, //Iuran Anggota Baru (gelombang 1 Jan sd Jun) ($tahunan==300000?439:481)
              'product_uom_qty' => 1, //($tahunan/$this->parameter_harga_tahunan),
              'price_unit' => $tahunan,
              //'analytic_tag_ids'=> ($is_wna==1)?'':array(array(6,0,array(($tahunan==300000?1:11)))),
              'analytic_tag_ids' => ($is_wna == 1) ? '' : array(array(6, 0, array($this->UKT_2024))),  //($tahunan==300000?15:15)
              'name' => ($is_wna == 1) ? 'Iuran Anggota Asing' : $val_product2

              //($tahunan==300000?'Iuran Anggota Baru (gelombang 1 Jan sd Jun)':'Iuran Anggota Baru (Kuartal 3)')
              //'name'=>($is_wna==1)?'Iuran Anggota Asing':($tahunan==300000?'Iuran Anggota Baru (gelombang 1 Jan sd Jun)':'Iuran Anggota Baru (gelombang 2 Jul sd Des)')																																			 
            )),
            array(0, 0, array(
              'product_id' => 447,
              'product_uom_qty' => 1,
              'price_unit' => $sukarela,
              'name' => 'Iuran Sukarela'
            ))
          );
        }

        $arr = array(
          'partner_id' => (int)$partner_id,
          'bk_id' => $bk_id,
          'validity_date' => $validity_date,
          'payment_term_id' => "1",
          'order_line' => $arr_reg
        );
      } else if ($type == 6) {

        $arr_reg = array();
        //if($sukarela==0){
        $ar_t = array();

        $param = $sukarela / 200000;
        $run_num = $is_wna;

        //echo $param.'aaa'.$run_num;

        for ($i = 0; $i < $param; $i++) {


          $val_product = 0;
          $val_product2 = "";
          if ($run_num == 1) {
            $val_product = 485;
            $val_product2 = "PKB (Tahun 1)";
          } else if ($run_num == 2) {
            $val_product = 486;
            $val_product2 = "PKB (Tahun 2)";
          } else if ($run_num == 3) {
            $val_product = 487;
            $val_product2 = "PKB (Tahun 3)";
          } else if ($run_num == 4) {
            $val_product = 488;
            $val_product2 = "PKB (Tahun 4)";
          } else if ($run_num == 5) {
            $val_product = 489;
            $val_product2 = "PKB (Tahun 5)";
          }


          $ar_t[] = array(0, 0, array(
            'product_id' => $val_product,
            'product_uom_qty' => 1,
            'price_unit' => 200000,
            'analytic_tag_ids' => array(array(6, 0, array($this->IP_2024))),
            'name' => $val_product2
          ));
          $run_num++;
        }

        /*$arr_reg = array( 
						array(0,0,array (
						'product_id'=>470, //Iuran Anggota Baru (gelombang 1 Jan sd Jun) ($tahunan==300000?439:481)
						'product_uom_qty'=>1,//($tahunan/$this->parameter_harga_tahunan),
						'price_unit'=>200000,
						'analytic_tag_ids'=> array(array(6,0,array(11))), //($tahunan==300000?15:15)
						'name'=>'PKB (Tahun 1)'
																																							 
						)),
					);*/

        $arr_reg = $ar_t;
        //}

        $arr = array(
          'partner_id' => (int)$partner_id,
          'bk_id' => $bk_id,
          'validity_date' => $validity_date,
          'payment_term_id' => "1",
          'order_line' => $arr_reg
        );

        //print_r($arr);

      }



      $this->_call_create('sale.order', array(
        $arr
      ));
    }
  }
  public function reg_order_umum($partner_id = null, $bk_id = null, $validity_date = null, $biaya = 0, $product_id = 0, $tag = 0, $name = '')
  {
    //$id = $this->session->userdata('user_id');

    $id = $this->input->get('user_id') <> null ? $this->input->get('user_id') : $this->session->userdata('user_id');

    $members = $this->main_mod->msrwhere('members', array('person_id' => $id), 'person_id', 'desc')->result();

    if (isset($members[0]->no_kta)) {
      //443 basic uji, 444 IPP, 445 IPM, 446 IPU
      $partner_id = $this->input->get('partner_id') <> null ? $this->input->get('partner_id') : $partner_id;
      //$bk_id = $this->input->get('bk_id')<>null?$this->input->get('bk_id'):$members[0]->code_bk_hkk;
      $validity_date = $this->input->get('validity_date') <> null ? $this->input->get('validity_date') : $members[0]->thru_date; //"01/01/2021"
      $biaya = $this->input->get('biaya') <> null ? $this->input->get('biaya') : $biaya;
      $product_id = $this->input->get('product_id') <> null ? $this->input->get('product_id') : $product_id;
      $tag = $this->input->get('tag') <> null ? $this->input->get('tag') : $tag;
      $name = $this->input->get('name') <> null ? $this->input->get('name') : $name;
      $validity_date = date('Y') . '-12-31';

      $state = $this->bk_one(str_pad($members[0]->code_bk_hkk, 2, "0", STR_PAD_LEFT), "ref");
      $bk_ids = (isset($state[0]['id']) ? $state[0]['id'] : "");

      if ($partner_id == null) {
        log_message('debug', '[SIMPONI] ' . __CLASS__ . ' ' . __FUNCTION__ . ' - Call Odoo partner_onei. partner_id is null');
        $temp = $this->partner_one($id, "ref");
        if (isset($temp[0]['id'])) {
          if (count($temp) == 1) {
            $partner_id = $temp[0]['id'];
          }
        }
      }

      if ($partner_id != null) {
        $arr = array();
        if ($biaya != 0) {

          log_message('debug', '[SIMPONI] ' . __CLASS__ . ' ' . __FUNCTION__ . ' - Call Odoo create SaleOrder');
          $arr = array(
            'partner_id' => (int)$partner_id,
            'bk_id' => $bk_ids,
            'validity_date' => $validity_date,
            'payment_term_id' => "1",
            'order_line' => array(
              array(0, 0, array(
                'product_id' => (int)$product_id,
                'product_uom_qty' => 1,
                'price_unit' => $biaya,
                'analytic_tag_ids' => array(array(6, 0, array($tag))),
                'name' => $name,
              ))
            )
          );
          $this->_call_create('sale.order', array(
            $arr
          ));
        }
      }
    }
  }

  public function unit_test()
  {
    /*$this->_call_create ( 'sale.order', array (
		array (
			'partner_id'=>"1294",
			'bk_id'=>"26",
			'validity_date'=>"01/01/2021",
			'payment_term_id'=>"1",
			'order_line'=>array( 
				array(0,0,array (
				'product_id'=>7,
				'product_uom_qty'=>1
				))
			)
		)) );*/
    $temp = $this->partner_one(1, "ref");
    print_r($temp);
  }
  public function unit_test_2()
  {
    print_r($this->experience_one("31495", "partner_id"));
  }
  public function unit_test_3()
  {
    $this->reg_order_umum(31542, 54, date('Y-m-d'), 900000, 443);
  }


  public function unit_test_am()
  {
    print_r($this->partner_one(68971, "ref"));
  }
  public function unit_reg()
  {
    $id_total = 83246;
    $checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $id_total), 'id', 'desc')->result();

    //SEND ODOO
    $sukarela = $checkx[0]->sukarelatotal - $checkx[0]->iurantahunan - $checkx[0]->iuranpangkal;
    $tipe = '';
    if ($checkx[0]->pay_type == '4') {
      $check_faip = $this->main_mod->msrwhere('asesor_faip', array('faip_id' => $checkx[0]->rel_id), 'id', 'desc')->row();
      $check_faip22 = $this->main_mod->msrwhere('user_faip', array('id' => $checkx[0]->rel_id), 'id', 'desc')->row();

      if (isset($check_faip->keputusan)) {
        if ($check_faip->keputusan == "IPM" || $check_faip->keputusan == "Memenuhi persyaratan untuk sertifikasi IPM" || $check_faip->keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM")
          $tipe = 'IPM';
        else if ($check_faip->keputusan == "IPU" || $check_faip->keputusan == "Memenuhi persyaratan untuk IPU")
          $tipe = 'IPU';
        else $tipe = 'IPP';
      } else {
        if ($check_faip22->keputusan == "2" && $check_faip22->is_manual == "1")
          $tipe = 'IPM';
        else if ($check_faip22->keputusan == "3" && $check_faip22->is_manual == "1")
          $tipe = 'IPU';
        else $tipe = 'IPP';
      }
    }

    //$result= file_get_contents(site_url('erp/reg_member?user_id='.$checkx[0]->user_id.'&sukarela='.$sukarela.'&pangkal='.$checkx[0]->iuranpangkal.'&tahunan='.$checkx[0]->iurantahunan.'&type='.$checkx[0]->pay_type.'&total='.$checkx[0]->sukarelatotal.'&tipe_faip='.$tipe));


    $id = $checkx[0]->user_id;

    //$id = $this->input->get('user_id');
    //$sukarela = $this->input->get('sukarela')<>null?$this->input->get('sukarela'):0;
    $pangkal = $checkx[0]->iuranpangkal;
    $tahunan = $checkx[0]->iurantahunan;
    $typeX = $checkx[0]->pay_type;
    $totalX = $checkx[0]->sukarelatotal;
    $tipe_faip = $tipe;

    /*$id = 30168;
		$sukarela = 1000000;
		$pangkal = 0;
		$tahunan = 900000;*/

    $seq = $tahunan / $this->parameter_harga_tahunan;

    $validity_date = date('Y-m-d', strtotime('+' . $seq . ' years'));

    $members = $this->main_mod->msrwhere('members', array('person_id' => $id), 'person_id', 'desc')->result();

    $temp = $this->partner_one($id, "ref");
    $is_exist = "0";
    $rel_detail = 0;
    if (isset($temp[0]['id'])) {
      if (count($temp) == 1) {
        $is_exist = $temp[0]['id'];
        $rel_detail = 1;
      }
    }

    if (isset($members[0]->no_kta) && $is_exist == "0") {
      $temp = $this->partner_one(str_pad($members[0]->no_kta, 6, "0", STR_PAD_LEFT), "no_kta");
      if (isset($temp[0]['id'])) {
        if (count($temp) == 1) {
          $is_exist = $temp[0]['id'];
          $rel_detail = 1;
        }
      }
    }

    if ($is_exist == "0") {
      $temp = $this->partner_one($id, "simponi_uid");
      if (isset($temp[0]['id'])) {
        if (count($temp) == 1) {
          $is_exist = $temp[0]['id'];
          $rel_detail = 1;
        }
      }
    }


    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    $user_edu = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1, 'type' => 1), 'user_id', 'desc')->result();
    $user_exp = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'user_id', 'desc')->result();
    $user_cert = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 1), 'user_id', 'desc')->result();
    $users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->result();
    $user_address = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1, 'is_mailing' => 1), 'user_id', 'desc')->result();
    $user_address2 = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1, 'is_mailing<>1' => null), 'user_id', 'desc')->result();
    $contacts = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'status' => 1), 'user_id', 'desc')->result();

    $function = '';
    $f_title = '';
    $l_title = '';

    $b64image = '';
    if ($user_profiles[0]->photo != '')
      $b64image = base64_encode(@file_get_contents(base_url() . 'assets/uploads/' . $user_profiles[0]->photo));

    $country_id = "100";
    $state_id = "";
    if (isset($user_address[0]->province)) {
      $state = $this->state_one(str_replace("Provinsi ", "", $user_address[0]->province), "name");
      $state_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      //print_r($state);
    }
    $tags = "new";
    $wilayah_id = "";
    $cabang_id = "";
    $bk_ids = "";
    if (isset($members[0]->no_kta)) {
      $tags = "non-active";
      if ($members[0]->thru_date >= date("Y-m-d")) $tags = "active";
      $state = $this->wilayah_one(str_pad(substr($members[0]->code_wilayah, 0, 2), 2, '0', STR_PAD_LEFT), "ref");
      $wilayah_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      $state = $this->cabang_one(str_pad($members[0]->code_wilayah, 4, "0", STR_PAD_LEFT), "ref");
      $cabang_id = (isset($state[0]['id']) ? $state[0]['id'] : "");
      $state = $this->bk_one(str_pad($members[0]->code_bk_hkk, 2, "0", STR_PAD_LEFT), "ref");
      $bk_ids = (isset($state[0]['id']) ? $state[0]['id'] : "");

      $date = strtotime($members[0]->thru_date);
      $validity_date = date('Y-m-d', strtotime("+" . $seq . " year", $date));
    }

    //EDUCATION
    $edu_arr = array();
    $i = 0;
    foreach ($user_edu as $val) {

      $att = '';
      if ($val->attachment != '')
        $att = base64_encode(@file_get_contents(base_url() . 'assets/uploads/' . $val->attachment));
      $edu_arr[$i][0] = $rel_detail;
      $edu_arr[$i][1] = 0;
      $edu_arr[$i][] = array(
        'name' => (isset($val->school) ? $val->school : ''),
        'start_year' => (isset($val->startdate) ? ($val->startdate != '') ? $val->startdate : 1970 : 1970),
        'end_year' => (isset($val->enddate) ? ($val->enddate != '') ? $val->enddate : 1970 : 1970),
        'degree' => (isset($val->degree) ? strtolower($val->degree == 'S1/D4' ? 'S1' : $val->degree) : ''),
        'majors' => (isset($val->mayor) ? ($val->mayor) : ''),
        'score' => (isset($val->score) ? str_replace(',', '.', $val->score) : ''),
        'title' => (isset($val->title) ? ($val->title) : ''),
        'organization' => (isset($val->activities) ? ($val->activities) : ''),
        'description' => (isset($val->description) ? ($val->description) : ''),
        'attachment_edu' => $att
      );

      $f_title = $f_title . (isset($val->title_prefix) ? ', ' . $val->title_prefix : '');
      $l_title = $l_title . (isset($val->title) ? ', ' . $val->title : '');

      $i++;
    }

    //EXP
    $exp_arr = array();
    /*$i=0;
		foreach($user_exp as $val){
			
			//$att ='';
			//if($val->attachment!='')
			//	$att = base64_encode(@file_get_contents(base_url().'assets/uploads/'.$val->attachment));
			$exp_arr [$i][0]= $rel_detail;
			$exp_arr [$i][1]= 0;
			
			if($val->startmonth=='') $val->startmonth = 1;
			//if($val->endmonth=='' && $val->is_present!='1') {$val->endmonth = 1;}
			
			$function = isset($val->title)?($val->title):'';
			
			$exp_arr [$i][] = array(
				'name'=>(isset($val->company)?$val->company:''),
				'start_date'=>str_pad($val->startyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->startmonth,2,"0", STR_PAD_LEFT).'-01',
				'end_date'=>($val->is_present!='1'?str_pad($val->endyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->endmonth,2,"0", STR_PAD_LEFT).'-01':false),
				'currently'=>($val->is_present=='1'?"true":"false"),
				'position'=>(isset($val->title)?($val->title):''),
				'street_1'=>(isset($val->provinsi)?($val->provinsi):''),
				'street_2'=>(isset($val->negara)?($val->negara):''),
				'city'=>(isset($val->location)?($val->location):''),
				'description'=>(isset($val->description)?($val->description):''),
				//'attachment_exp'=>$att
				);
			$i++;
		}*/

    //CERT
    $cert_arr = array();
    /*$i=0;
		foreach($user_cert as $val){			
			$att ='';
			if($val->attachment!='')
				$att = base64_encode(@file_get_contents(base_url().'assets/uploads/'.$val->attachment));
			if($val->cert_name!=''){
				$cert_arr [$i][0]= $rel_detail;
				$cert_arr [$i][1]= 0;
				$cert_arr [$i][] = array(
					'certification_name'=>(isset($val->cert_name)?$val->cert_name:''),
					'start_month'=>str_pad($val->startyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->startmonth,2,"0", STR_PAD_LEFT).'-01',
					'end_month'=>str_pad($val->endyear,4,"0", STR_PAD_LEFT).'-'.str_pad($val->endmonth,2,"0", STR_PAD_LEFT).'-01',
					'certification_auth'=>(isset($val->cert_auth)?($val->cert_auth):''),
					'license_no'=>(isset($val->lic_num)?($val->lic_num):''),
					'certification_url'=>(isset($val->cert_url)?($val->cert_url):''),
					'description'=>(isset($val->cert_title)?($val->cert_title):''),
					'attachment'=>$att
					);
				$i++;
			}
		}*/

    //CHILD
    $child_arr = array();
    $i = 0;
    foreach ($user_address2 as $val) {
      if ($val->address != '') {
        $child_arr[$i][0] = $rel_detail;
        $child_arr[$i][1] = 0;
        $child_arr[$i][] = array(
          'type' => 'other',
          'street' => (isset($val->address) ? $val->address : ''),
          'street2' => (isset($val->province) ? $val->province : ''),
          'city' => (isset($val->city) ? $val->city : ''),
          'state_id' => '',
          'zip' => (isset($val->zipcode) ? $val->zipcode : ''),
          'country_id' => '',
          'email' => (isset($val->email) ? $val->email : ''),
          'phone' => (isset($val->phone) ? $val->phone : ''),
        );
        $i++;
      }
    }
    foreach ($contacts as $val) {
      if ($val->contact_value != '') {
        $child_arr[$i][0] = $rel_detail;
        $child_arr[$i][1] = 0;
        $child_arr[$i][] = array(
          'name' => $val->contact_type,
          'type' => 'contact',
          'email' => (isset($val->contact_value) ? (strpos($val->contact_type, '_email') !== false) ? $val->contact_value : '' : ''),
          'phone' => (isset($val->contact_value) ? (strpos($val->contact_type, '_phone') !== false) ? $val->contact_value : '' : ''),
        );
        $i++;
      }
    }
    //print_r($child_arr);

    /*$att_arr =  [{
                'res_model' : 'res.partner',
                'name' : 'WinUpdate Image',
                'res_id' : id,
                'type' : 'binary',
                'mimetype' : 'image/png',
                'store_fname' : 'WinUpdate1.png',
                'datas' : imageBase64
                }];*/

    $dob = $user_profiles[0]->dob;
    if ($user_profiles[0]->dob == '0000-00-00') $dob = '1970-01-01';

    $update_data = array(
      'ref'          => $id,
      'company_type'      => 'person',
      'image'          => $b64image,
      'name'          => $user_profiles[0]->firstname . ($user_profiles[0]->lastname != "" ? ' ' . $user_profiles[0]->lastname : ''),
      'first_name'      => $user_profiles[0]->firstname,
      'last_name'        => $user_profiles[0]->lastname,
      'no_kta'        => (isset($members[0]->no_kta) ? str_pad($members[0]->no_kta, 6, "0", STR_PAD_LEFT) : ''),
      'street'        => (isset($user_address[0]->address) ? $user_address[0]->address : ''),
      'street2'        => "",
      'city'          => (isset($user_address[0]->city) ? $user_address[0]->city : ''),
      'state_id'        => $state_id,
      'zip'          => (isset($user_address[0]->zipcode) ? $user_address[0]->zipcode : ''),
      'country_id'      => $country_id,


      'wilayah_id'      => $wilayah_id,
      'cabang_id'        => $cabang_id,
      'bk_ids'        => array(array(6, 0, array($bk_ids))), //array($bk_ids),
      'gender'        => strtolower($user_profiles[0]->gender),
      'pob'          => $user_profiles[0]->birthplace,
      'dob'          => $dob,
      'type_id'        => $user_profiles[0]->idtype == ' ' ? '' : strtolower($user_profiles[0]->idtype),
      'id_number'        => $user_profiles[0]->idcard,

      'mandiri_ar_va'      => $user_profiles[0]->va,
      'simponi_uid'      => $user_profiles[0]->user_id,
      'simponi_id'      => (isset($members[0]->person_id) ? $members[0]->person_id : ''),
      'vat'          => "",

      'function'        => $function,
      'phone'          => "",
      'mobile'        => $user_profiles[0]->mobilephone,
      'email'          => $users[0]->email,
      'website'        => $user_profiles[0]->website,
      'f_title'        => "",
      'l_title'        => "",
      'publication_material'  => ($user_profiles[0]->is_public == "1" ? "true" : "false"),
      'submit_personal_data'  => ($user_profiles[0]->is_datasend == "1" ? "true" : "false"),
      'tags'          => $tags,

      'education_ids'      => $edu_arr,
      'experience_ids'    => $exp_arr,
      'exp_certificate_ids'  => $cert_arr,
      'child_ids'        => $child_arr,

      //'ir.attachment'			=> $att_arr,

      'property_account_receivable_id'    => 936,
      'property_account_payable_id'      => 743,

    );




    //print_r($update_data);


    /*if($is_exist=="0")
				$is_exist = $this->_call_create_ada ( 'res.partner', array($update_data) );
		else {
			$is_exist2 = $this->_call_write_ada ( 'res.partner',$is_exist, $update_data );
			print_r($is_exist2);
		}*/


    print_r($validity_date);
    print_r($totalX);

    //$this->reg_order_umum ( $is_exist, $bk_ids, $validity_date, $totalX, 444, 2 );//IPP










    /*if($typeX=='1' || $typeX=='2') 
		{
			if($is_exist=="0")
				$is_exist = $this->_call_create_ada ( 'res.partner', array($update_data) );
			else $is_exist2 = $this->_call_write_ada ( 'res.partner',$is_exist, $update_data );
			
			$this->reg_order ( $is_exist, $bk_ids, $validity_date, $sukarela, $pangkal, $tahunan, $typeX );
		}
		else if($typeX=='3') 
		{
			if($is_exist=="0")
				$is_exist = $this->_call_create_ada ( 'res.partner', array($update_data) );
			else $is_exist2 = $this->_call_write_ada ( 'res.partner',$is_exist, $update_data );
			
			$this->reg_order_umum ( $is_exist, $bk_ids, $validity_date, $totalX, 444, 2 );//IPP
		}
		else if($typeX=='4')
		{			
			if($is_exist=="0")
				$is_exist = $this->_call_create_ada ( 'res.partner', array($update_data) );
			else $is_exist2 = $this->_call_write_ada ( 'res.partner',$is_exist, $update_data );
			
			$tag = 2;//IPP
			$p = 444;
			if($totalX == '1650000') { $p = 445;$tag = 3;}//IPM 
			else if($totalX == '2200000') { $p = 446;$tag = 4;}//IPU 
			else if($tipe_faip == 'IPM') { $p = 472;$tag = 3;}//IPM 
			else if($tipe_faip == 'IPU') { $p = 473;$tag = 4;}//IPU 
			$this->reg_order_umum ( $is_exist, $bk_ids, $validity_date, $totalX, $p,$tag );
		}*/
  }

  /**
   * UNIT TEST?
   * ER: Query data payment di Odoo secara manual dengan input array dari Virtual Accounts (VAs)
   * Contoh call: https://updmember.pii.or.id/erp/unit_payment?val=<VALUE>
   * Contoh value dari parameter `val`: 8969909040239291,8969909040139328,8969923050945155
   */
  public function unit_payment()
  {

    //ER: FIXME - No Authz?

    $multiple_vas = $this->input->get('val');

    if ($multiple_vas <> null) {
      $multiple_vas = explode(',', $multiple_vas); //Change to array

      $this->output->set_content_type('text/plain');
      foreach ($multiple_vas as $va) {
        $odoo_result = $this->payment_one($va, "billkey1");
        echo 'input_va:' . $va . ";\n\n";
        echo "id;billkey1;trxdatetime_func;payment_ids\n";
        foreach ($odoo_result as $v) {
          echo $v['id'] . ';' .
            $v['billkey1'] . ';' .
            $v['trxdatetime_func'] . ';' .
            (isset($v['payment_ids'][0]) ? $v['payment_ids'][0] : '') . ";" .
            "\n";
        }
        echo "\n";
      }
    }
  }

  public function unit_pay()
  {
    $idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
    $payment = $this->input->post('payment') <> null ? $this->input->post('payment') : "";
    $date_pay = '2022-';
    $this->load->model('main_mod');
    //if($idmember!=''){
    try {

      $checkStatus = $this->main_mod->msrwhere('user_transfer', array('id' => $idmember), 'id', 'desc')->result();

      if (!isset($checkStatus[0])) {
        echo "not valid";
      } else {
        $where = array(
          "id" => $idmember
        );
        $row = array();
        $row = array(
          'status' => $payment,
          'modifieddate' => $date_pay,
          'modifiedby' => 8,
        );
        $update = $this->main_mod->update('user_transfer', $where, $row);

        if ($checkStatus[0]->pay_type == "3" && $payment == "1") {

          $check = $this->main_mod->msrwhere('user_faip', array('id' => $checkStatus[0]->rel_id), 'id', 'desc')->result();
          $rowInsert = array(
            'faip_id' => $checkStatus[0]->rel_id,
            'old_status' => $check[0]->status_faip,
            'new_status' => 5,
            'notes' => 'finance',
            'createdby' => 8,
          );
          $this->main_mod->insert('log_status_faip', $rowInsert);


          $where = array(
            "id" => $checkStatus[0]->rel_id
          );
          $row = array(
            'status_faip' => 5,
            //'remarks' => $remarks,
            'modifieddate' => $date_pay,
            'modifiedby' => 8,
          );
          $update = $this->main_mod->update('user_faip', $where, $row);
        } else if ($checkStatus[0]->pay_type == "4" && $payment == "1") {
          $check = $this->main_mod->msrwhere('user_faip', array('id' => $checkStatus[0]->rel_id), 'id', 'desc')->result();
          $rowInsert = array(
            'faip_id' => $checkStatus[0]->rel_id,
            'old_status' => $check[0]->status_faip,
            'new_status' => 11,
            'notes' => 'finance',
            'createdby' => 8,
          );
          $this->main_mod->insert('log_status_faip', $rowInsert);

          $where = array(
            "id" => $checkStatus[0]->rel_id
          );
          $row = array(
            'status_faip' => 11,
            //'remarks' => $remarks,
            'modifieddate' => $date_pay,
            'modifiedby' => 8,
          );
          $update = $this->main_mod->update('user_faip', $where, $row);
        } else if ($checkStatus[0]->pay_type == "1" || $checkStatus[0]->pay_type == "2") {
          $rowInsert = array(
            'pay_id' => $idmember,
            'old_status' => $checkStatus[0]->status,
            'new_status' => $payment,
            'notes' => 'finance',
            'createdby' => 8,
          );
          $this->main_mod->insert('log_status_kta', $rowInsert);

          if ($checkStatus[0]->pay_type == "1" && $payment == "1") {
            $check_kta = $this->main_mod->msrwhere('members', array('person_id' => $checkStatus[0]->user_id), 'id', 'desc')->result();
            //REMOVE BECAUSE VA
            if (isset($check_kta[0])) {
              $this->db->set('username', $check_kta[0]->no_kta);
              $this->db->where('id', $checkStatus[0]->user_id);
              $this->db->update('users');
            }
          } else if ($checkStatus[0]->pay_type == "2" && $payment == "1") {
            $check_log = $this->main_mod->msrwhere('log_her_kta', array('id_pay' => $idmember), 'id', 'desc')->result();
            //REMOVE BECAUSE VA
            if (isset($check_log[0])) {
              $where = array(
                "person_id" => $checkStatus[0]->user_id
              );
              $row = array(
                'from_date' => date('Y-m-d', strtotime($check_log[0]->from_date)),
                'thru_date' => date('Y-m-d', strtotime($check_log[0]->thru_date)),
                'updated_at' => $date_pay,
                'updated_by' => 8,
              );
              $update = $this->main_mod->update('members', $where, $row);
            }
          }
        }


        echo "valid";
      }
    } catch (Exception $e) {
      //print_r($e);
      echo "not valid";
    }
  }


  /**
   * ER: Mendapatkan SaleOrder id dan name dari Oddo
   * Contoh call: https://updmember.pii.or.id/erp/get_so_from_id?val=<VALUE>&where=id
   * Contoh value dari parameter `val`: 13253,13950,14965
   * Contoh response: 
   * 		13253;SO14822
   * 		13950;SO13794
   * 		13253;SO13109
   */
  public function get_so_from_id()
  {
    //ER: FIXME - No Authz?

    $multiple_ids = $this->input->get('val');
    $where = $this->input->get('where') <> null ? $this->input->get('where') : 'id';

    if ($multiple_ids <> null) {
      $multiple_ids = explode(',', $multiple_ids); //Change to array
      $result = $this->_call_search_read_ada(
        'sale.order',
        array(
          array(
            array(
              $where,
              'in',
              $multiple_ids
            )
          )
        ),
        array(
          //'limit'=>5,
          'fields' => array('name', 'id')
        )
      );

      $this->output->set_content_type('text/plain');
      echo "sales_order_id;sale_order_name;";
      foreach ($result as $sale_order) {
        echo $sale_order['id'] . ';' . $sale_order['name'] . "\n";
      }
    }
  }
}
