<?php
include_once("config.php");

class Csi{
  public $error_message;
  private $Db;
  private $Sql;

  function __construct(){
      $this->Db = new DatabasePDO("ACC");
  }
  function __destruct(){

  }

  public function AddCreditCategory($rating_code_uid,$code,$name){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `credits_category` WHERE `category_code`=:code AND `rating_code_uid`=:rtuid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':code',$code,PDO::PARAM_STR);
      $sth->bindParam(':rtuid',$rating_code_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $hasduplicate=true;
      }
    }
    if($hasduplicate){
      $this->error_message="Category code name already exist";
      $retval = false;
      return $retval;
    }
    $uid = $this->GenerateUID("credits_category","CCAT");
    $inSql="INSERT INTO `credits_category` (	`uid`,`rating_code_uid`, `category_name`, `category_code`) VALUES(:uid, :rtuid, :cat_name, :code)";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':rtuid',$rating_code_uid,PDO::PARAM_STR);
      $sth->bindParam(':cat_name',$name,PDO::PARAM_STR);
      $sth->bindParam(':code',$code,PDO::PARAM_STR);
      $sth->execute();
      $retval=$uid;
    }
    return $retval;
  }
  public function GetCreditCategory($cat_uid){
    $retval = false;
    $inSql="SELECT * FROM `credits_category` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$cat_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row=$sth->fetch(PDO::FETCH_OBJ);
        $rt = new stdClass;
        $rt->rating_code_uid = $row->rating_code_uid;
        $rt->category_name = $row->category_name;
        $rt->category_code = $row->category_code;
        $retval = $rt;
      }
    }
    return $retval;
  }

  public function GetCreditCategoryList($rating_code_uid){
    $retval = array();
    $inSql="SELECT * FROM `credits_category` WHERE `rating_code_uid`=:rtuid ORDER BY `category_code` ASC";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':rtuid',$rating_code_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){

        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $rt = new stdClass;
          $rt->uid = String2Hex($row->uid);
          $rt->rating_code_uid = String2Hex($row->rating_code_uid);
          $rt->category_name=$row->category_name;
          $rt->category_code=$row->category_code;
          $retval[] = $rt;
        }
      }
    }
    return $retval;
  }

  public function GetCredits($cat_uid,$rating_code_uid){
    $retval = array();
    $inSql="SELECT * FROM `master_credits` WHERE `rating_code_uid`=:rtuid AND `credit_cat_uid`=:catuid ORDER BY `credit` ASC";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':rtuid',$rating_code_uid,PDO::PARAM_STR);
      $sth->bindParam(':catuid',$cat_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $crd = new stdClass;
          $crd->credit = $row->credit;
		  $crd->status = $row->status;
          $crd->credit_id = String2Hex($row->credit_id);
          $crd->description = utf8_encode($row->description);
          $crd->code_type = $row->code_type; 
		  $ctype = ($row->mandatory=="yes")? "Mandatory":"Optional";
		  $cpoints = ($row->mandatory=="yes")? "N/A":$row->points;
          $crd->max_points = $cpoints;
		  $crd->mandatory = $ctype;
          $retval[] = $crd;
        }
      }
    }
    return $retval;
  }

  public function AddRatingCode($code_name){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `rating_code` WHERE `code_name`=:codename";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':codename',$code_name,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $hasduplicate=true;
      }
    }
    if($hasduplicate){
      $this->error_message="rating code name already exist";
      $retval = false;
      return $retval;
    }
    $uid = $this->GenerateUID("rating_code","RC");
    $inSql="INSERT INTO `rating_code` (	`uid`, `code_name`, `date_created`) VALUES(:uid, :code_name, NOW())";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':code_name',$code_name,PDO::PARAM_STR);
      $sth->execute();
      $retval=$uid;
    }
    return $retval;
  }
  public function UpdateRatingCode($uid,$code_name,$color0,$color1,$color2){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `rating_code` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
      if(!$sth->rowCount()){
        $this->error_message="Record does not exist";
        return false;
      }
    }
    $inSql="UPDATE `rating_code`
    	SET
    	`code_name` = :code_name ,
		`text_color`=:textcolor,
    	`foreground_color` = :fcolor ,
    	`background_color` = :bcolor
    	WHERE
    	`uid` = :uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':code_name',$code_name,PDO::PARAM_STR);
	  $sth->bindParam(':textcolor',$color0,PDO::PARAM_STR);
      $sth->bindParam(':fcolor',$color1,PDO::PARAM_STR);
      $sth->bindParam(':bcolor',$color2,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }
    return $retval;
  }
 
  public function SetRatingCodeStatus($uid,$rtstatus){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `rating_code` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
      if(!$sth->rowCount()){
        $this->error_message="Record does not exist";
        return false;
      }
    }
    $inSql="UPDATE `rating_code`
    	SET
    	`status` = :status
    	WHERE
    	`uid` = :uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':status',$rtstatus,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }
    return $retval;
  } 
  
  public function UpdateCreditCategory($cat_uid,$cat_name,$cat_code){ 
    $retval = false;
    $hasduplicate = false;
	$rating_code_uid ="";
	$orig_cat_code ="";
	
    $inSql="SELECT * FROM `credits_category` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$cat_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
		  $row=$sth->fetch(PDO::FETCH_OBJ);
		  $rating_code_uid =$row->rating_code_uid;
		  $orig_cat_code =$row->category_code;
      }else{
        $this->error_message="Record does not exist";
        return false;
	  }
    }
    $inSql="UPDATE `credits_category`
    	SET
    	`category_name` = :category_name,
    	`category_code` = :category_code 
    	WHERE
    	`uid` = :uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$cat_uid,PDO::PARAM_STR);
      $sth->bindParam(':category_name',$cat_name,PDO::PARAM_STR);
      $sth->bindParam(':category_code',$cat_code,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }
	//update all credits prefix 
    $inSql="SELECT * FROM `master_credits` WHERE `rating_code_uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$rating_code_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
		
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
		  $credit = $row->credit;
		  if(strpos($credit,$orig_cat_code)!==false){ 
			  $code_tmp = str_replace($orig_cat_code,"",$credit);		  
			  $new_credit = $cat_code.$code_tmp;	
			  
			  $cr = new Credit($row->credit_id); 
			  $arg = array();
			  $arg["credit"] = $new_credit;	
			  $arg["code"] = $code_tmp; 				  
			  $cr->UpdateCredit($arg);			  
		  }
          //$rtl = new stdClass;
          //$rtl->rating_level=$row->rating_level;
          //$rtl->status=$row->status;
          //$retval[]=$rtl;
        }
      }
    }	
	
    return $retval;
  }  
  
  public function UpdateRatingCodeRatingLevels($uid,$level_names_array){
    $inSql="DELETE FROM `rating_code_rating_levels` WHERE `rating_code_uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
    }
    $inSql="INSERT INTO `rating_code_rating_levels`(`rating_code_uid`,`rating_level`) VALUES(:rating_code_uid,:rating_level)";
    if($sth = $this->Db->link->prepare($inSql)){
      foreach($level_names_array as $level){
      $sth->bindParam(':rating_code_uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':rating_level',$level,PDO::PARAM_STR);
      $sth->execute();
      }
    }
    return true;
  }
  public function UpdateRatingCodeBuildingTypes($uid,$type_names_array){
    $inSql="DELETE FROM `rating_code_building_types` WHERE `rating_code_uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
    }
    $inSql="INSERT INTO `rating_code_building_types`(`rating_code_uid`,`building_type`) VALUES(:rating_code_uid,:building_type)";
    if($sth = $this->Db->link->prepare($inSql)){
      foreach($type_names_array as $btype){
      $sth->bindParam(':rating_code_uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':building_type',$btype,PDO::PARAM_STR);
      $sth->execute();
      }
    }
    return true;
  }
  public function UpdateRatingCodeLogo($rating_code_uid,$logo_post){
    $retval=false;
    if(trim($logo_post)==""){
      return $retval;
    }
    $upload = new Upload();
    $upload->SetTargetFolder("ecoss/".$rating_code_uid."/logo");
		$upload->SetFilename($rating_code_uid."-logo-".date("Y-m-d-h-i-s").rand(100,999999));
		$upload->SetPostName("logo");
    if($upload->Execute())
    {
      $inSql = "UPDATE `rating_code` SET `logo`=:logo WHERE `uid`=:uid";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':uid',$rating_code_uid,PDO::PARAM_STR);
        $sth->bindParam(':logo',$upload->FinalFileName,PDO::PARAM_STR);
        $sth->execute();
      }
      $retval=$upload->FinalFileName;
    }
    return $retval;
  }

  public function GetRatingCodeInfo($rtuid){
    $retval = array();
    $inSql="SELECT * FROM `rating_code` WHERE `uid`=:uid  ORDER BY `code_name`";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$rtuid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row=$sth->fetch(PDO::FETCH_OBJ);
        $rt = new stdClass;
        $rt->uid = String2Hex($row->uid);
        $rt->code_name = $row->code_name;
        if(trim($row->logo)){
            $path = ZipStr("../../uploads/ecoss/".$row->uid."/logo/".$row->logo);
            $rt->logo="readfile.php?file=".$path;
        }else{
            $rt->logo="";
        }
        $rt->foreground_color=$row->foreground_color;
        $rt->background_color=$row->background_color;
		$rt->text_color=$row->text_color;
        $rt->status=$row->status;
        $retval=$rt;
      }
    }
    return $retval;
  }

  public function GetRatingLevels($rtuid){
    $retval = array();
    $inSql="SELECT * FROM `rating_code_rating_levels` WHERE `rating_code_uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$rtuid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $rtl = new stdClass;
          $rtl->rating_level=$row->rating_level;
          $rtl->status=$row->status;
          $retval[]=$rtl;
        }
      }
    }
    return $retval;
  }
  public function GetRatingStage($rs_uid){
    $retval = false;
    $inSql="SELECT * FROM `rating_stage` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$rs_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $retval = $sth->fetch(PDO::FETCH_OBJ);
      }
    }
    return $retval;
  }  
  public function GetBuildingTypes($rtuid){
    $retval = array();
    $inSql="SELECT * FROM `rating_code_building_types` WHERE `rating_code_uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$rtuid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $rtl = new stdClass;
          $rtl->building_type = $row->building_type;
          $rtl->status = $row->status;
          $retval[]=$rtl;
        }
      }
    }
    return $retval;
  }

  public function GetRatingCodeList($enable_only=false){
    $retval = array();
	if($enable_only==true){
		$inSql="SELECT * FROM `rating_code` WHERE `status`='enable' ORDER BY `code_name`";
	}else{
		$inSql="SELECT * FROM `rating_code` ORDER BY `code_name`";
	}
	
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->execute();
      if($sth->rowCount()){
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $rt = new stdClass;
          $rt->uid = String2Hex($row->uid);
          $rt->code_name = $row->code_name;
          if(trim($row->logo)){
              $path = ZipStr("../../uploads/ecoss/".$row->uid."/logo/".$row->logo);
              $rt->logo="readfile.php?file=".$path;
          }else{
              $rt->logo="";
          }
          $rt->foreground_color=$row->foreground_color;
          $rt->background_color=$row->background_color;
          $rt->status=$row->status;
          $retval[] = $rt;
        }
      }
    }
    return $retval;
  }

  public function AddDivision($div_number,$div_name){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `project_div` WHERE `code`=:divnumber";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':divnumber',$div_number,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $hasduplicate=true;
      }
    }
    if($hasduplicate){
      $retval = false;
      return $retval;
    }
    $uid = $this->GenerateUID("project_div","DIV");
    $inSql="INSERT INTO `project_div` (	`uid`, `code`, `div_name`) VALUES(:uid, :code, :div_name)";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->bindParam(':code',$div_number,PDO::PARAM_STR);
      $sth->bindParam(':div_name',$div_name,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }
    return $retval;
  }

  public function DeleteDivision($div_uid){
    $retval = false;
    $hasduplicate = false;
    $inSql="DELETE FROM `project_div` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$div_uid,PDO::PARAM_STR);
      $sth->execute();
      $retval = true;
    }
    return $retval;
  }

  public function UpdateDivision($div_uid,$div_number,$div_name,$div_status){
    $retval = false;
    $hasduplicate = false;
    $inSql="SELECT * FROM `project_div` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$div_uid,PDO::PARAM_STR);
      $sth->execute();
      if(!$sth->rowCount()){
        $this->error_message="Record does not exist";
        return false;
      }
    }

    $inSql="UPDATE `project_div`
    	SET
    	`code` = :code ,
    	`div_name` = :div_name ,
    	`status` = :status

    	WHERE
    	`uid` = :uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$div_uid,PDO::PARAM_STR);
      $sth->bindParam(':code',$div_number,PDO::PARAM_STR);
      $sth->bindParam(':div_name',$div_name,PDO::PARAM_STR);
      $sth->bindParam(':status',$div_status,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }

    $inSql="UPDATE `csi_codes`
    	SET
    	`group_category` = :category,
      `div_uid`=:uid
    	WHERE
    	`div_01` = :d1";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':d1',$div_number,PDO::PARAM_STR);
      $sth->bindParam(':category',$div_name,PDO::PARAM_STR);
      $sth->bindParam(':uid',$div_uid,PDO::PARAM_STR);
      $sth->execute();
      $retval=true;
    }
    return $retval;
  }

  public function GenerateDivButtons(){
    $retval="";
    $inSql="SELECT * FROM `project_div` ORDER BY `code`";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->execute();
      if($sth->rowCount()){
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
          $status_color=($row->status=="disable")? "btn-danger":"btn-default";
          $retval.="<a href='javascript:void(0)' rel='$row->code' class='btn $status_color  btn-small popovers btn_div' style='margin-bottom:3px;width:60px' data-trigger='hover' data-placement='top' data-content='$row->div_name' data-original-title='$row->code'>$row->code</a>";
        }
      }
    }
    return $retval;
  }
  public function GetDivs(){
    $inSql="SELECT * FROM `project_div` ORDER BY `code`";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->execute();
      if($sth->rowCount()){
        $retval = $sth->fetchAll(PDO::FETCH_OBJ);
      }
    }
    return $retval;
  }
  public function GetDivInfo($uid){
    $retval=false;
    $inSql="SELECT * FROM `project_div` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row = $sth->fetch(PDO::FETCH_OBJ);
        $retval = new stdClass;
        $retval->code = $row->code;
        $retval->div_name = $row->div_name;
        $retval->status = $row->status;
        $retval->uid = String2Hex($row->uid);
      }
    }
    return $retval;
  }
  public function GetDivInfoByCode($code){
    $retval=false;
    $inSql="SELECT * FROM `project_div` WHERE `code`=:code";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':code',$code,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $retval = $sth->fetch(PDO::FETCH_OBJ);
      }
    }
    return $retval;
  }
  public function AddCsi($csi_code,$desc,$heading,$csi_01,$csi_02,$csi_03,$csi_04,$csi_comments){
    $retval=false;
    $inSql="SELECT * FROM `csi_codes` WHERE `div_01`=:d1 AND `div_02`=:d2 AND `div_03`=:d3 AND `div_04`=:d4";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':d1',$csi_01,PDO::PARAM_STR);
      $sth->bindParam(':d2',$csi_02,PDO::PARAM_STR);
      $sth->bindParam(':d3',$csi_03,PDO::PARAM_STR);
      $sth->bindParam(':d4',$csi_04,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
          $this->error_message="Duplicate found for the csi ".$csi_code;
          return false;
      }
    }

    $inSql="INSERT INTO `csi_codes`
    	(`uid`,
    	`div_number`,
    	`div_01`,
    	`div_02`,
    	`div_03`,
    	`div_04`,
    	`product_material`,
    	`group_category`,
    	`dateadded`,
    	`comments`,
    	`heading`
    	)
    	VALUES
    	(:uid,
    	:div_number,
    	:div_01,
    	:div_02,
    	:div_03,
    	:div_04,
    	:product_material,
    	:group_category,
    	NOW(),
    	:comments,
    	:heading
      )";
      $uid = $this->GenerateUID("csi_codes","CSI");
      $div_inf = $this->GetDivInfoByCode($csi_01);
      $group_category = $div_inf->div_name;
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':div_01',$csi_01,PDO::PARAM_STR);
        $sth->bindParam(':div_02',$csi_02,PDO::PARAM_STR);
        $sth->bindParam(':div_03',$csi_03,PDO::PARAM_STR);
        $sth->bindParam(':div_04',$csi_04,PDO::PARAM_STR);
        $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
        $sth->bindParam(':div_number',$csi_code,PDO::PARAM_STR);
        $sth->bindParam(':product_material',$desc,PDO::PARAM_STR);
        $sth->bindParam(':comments',$csi_comments,PDO::PARAM_STR);
        $sth->bindParam(':heading',$heading,PDO::PARAM_STR);
        $sth->bindParam(':group_category',$group_category,PDO::PARAM_STR);
        $sth->execute();
        $retval=$uid;
      }
    return $retval;
  }


  public function UpdateCsiCode($csi_uid,$cat_desc,$cat_heading,$csi_02,$csi_03,$csi_04,$csi_comments,$status){
    $retval = false;
    $div_01 ="";
    $div_number ="";
    $inSql="SELECT * FROM `csi_codes` WHERE `uid` = :uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$csi_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row = $sth->fetch(PDO::FETCH_OBJ);
        $div_01 = $row->div_01;
      }else{
        return false;
      }
    }
    $div_number =$div_01." ".$csi_02." ".$csi_03." ".$csi_04;


    $inSql="UPDATE `csi_codes`
    	SET
    	`div_number` = :div_number,
    	`div_02` = :div_02 ,
    	`div_03` = :div_03 ,
    	`div_04` = :div_04 ,
    	`product_material` = :product_material,
    	`comments` = :comments,
    	`heading` = :heading,
    	`updated` = NOW(),
    	`status` = :status

    	WHERE
    	`uid` = :uid";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':uid',$csi_uid,PDO::PARAM_STR);
        $sth->bindParam(':div_02',$csi_02,PDO::PARAM_STR);
        $sth->bindParam(':div_03',$csi_03,PDO::PARAM_STR);
        $sth->bindParam(':div_04',$csi_04,PDO::PARAM_STR);
        $sth->bindParam(':product_material',$cat_desc,PDO::PARAM_STR);
        $sth->bindParam(':comments',$csi_comments,PDO::PARAM_STR);
        $sth->bindParam(':heading',$cat_heading,PDO::PARAM_STR);
        $sth->bindParam(':status',$status,PDO::PARAM_STR);
        $sth->bindParam(':div_number',$div_number,PDO::PARAM_STR);
        $sth->execute();
        $retval=true;
      }

    return $retval;
  }
  public function DeleteCsi($uid){
    $inSql="DELETE FROM `csi_codes` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
    }
    return true;
  }

  public function GetCsiDetails($uid){

    $inSql="SELECT * FROM `csi_codes` WHERE `uid`=:uid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':uid',$uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row = $sth->fetch(PDO::FETCH_OBJ);
        $retval= new stdClass;
        $retval->uid = String2Hex($row->uid);
        $retval->div_number = $row->div_number;
        $retval->div_01 = $row->div_01;
        $retval->div_02 = $row->div_02;
        $retval->div_03 = $row->div_03;
        $retval->div_04 = $row->div_04;
        $retval->category_desc  = $row->product_material;
        $retval->category = $row->group_category;
        $retval->status= $row->status;
        $retval->comments= $row->comments;
        $retval->header= $row->heading;
      }else{
        $retval=false;
      }
    }
    return $retval;
  }
  public function GetCsiPerDiv($div_num){
    $retval= new stdClass;
    $retval->div_uid="";
    $retval->div_num="";
    $retval->div_name="";
    $retval->div_status="";
    $retval->csi_list=array();
    $hasdiv=false;
    $inSql="SELECT * FROM `csi_codes` WHERE `div_01`=:div";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':div',$div_num,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row = $sth->fetch(PDO::FETCH_OBJ);
        $retval->div_uid    = String2Hex($row->uid);
        $retval->div_num    = $row->div_number;
        $retval->div_name   = $row->product_material;
        $retval->div_status = $row->status;
        $hasdiv=true;
      }
    }
    if($hasdiv){
      $inSql="SELECT * FROM `csi_codes` WHERE `div_01`=:div";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':div',$div_num,PDO::PARAM_STR);
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $csi = new stdClass;
            $csi->uid = String2Hex($row->uid);
			$csi->div_uid = String2Hex($row->uid);
            $csi->div_number = $row->div_number;
			$csi->div_num = $row->div_number;
            $csi->division   = $row->group_category;
            $csi->category_desc   = $row->product_material;
			$csi->div_name   = $row->product_material;
            $csi->csi_status   = $row->status;
			$csi->div_status   = $row->status;
            $retval->csi_list[] =$csi;
          }
        }
      }
    }
    return $retval;
  }
  
  
  
  public function GetCsiPerDivWithCredits($div_num,$rating_code_uid){
    $retval= new stdClass;
    $retval->div_uid="";
    $retval->div_num="";
    $retval->div_name="";
    $retval->div_status="";
    $retval->csi_list=array();
	
    $hasdiv=false;
    $inSql="SELECT * FROM `csi_codes` WHERE `div_01`=:div";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':div',$div_num,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $row = $sth->fetch(PDO::FETCH_OBJ);
        $retval->div_uid    = String2Hex($row->uid);
        $retval->div_num    = $row->div_number;
        $retval->div_name   = $row->product_material;
        $retval->div_status = $row->status;
        $hasdiv=true;
      }
    }
    if($hasdiv){
		
	  $rt = $this->GetRatingCodeInfo($rating_code_uid);
	  $act = new SystemActivity();
		
      $inSql="SELECT * FROM `csi_codes` WHERE `div_01`=:div";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':div',$div_num,PDO::PARAM_STR);
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $csi = new stdClass;
            $csi->uid = String2Hex($row->uid);
            $csi->div_number = $row->div_number;
            $csi->division   = $row->group_category;
            $csi->category_desc   = $row->product_material;
            $csi->csi_status   = $row->status;
			$csi->bg_color = $rt->foreground_color;
			$csi->text_color = $rt->text_color;
			$csi->hard_credits = $this->GetHardCreditListByRatingCode($row->uid,$rating_code_uid);
			$csi->soft_credits = $this->GetSoftCreditListByRatingCode($row->uid,$rating_code_uid);
			
			$csi->last_activity_soft=$act->GetLastLogInfo("","setting","",$row->uid,"la_soft_".String2Hex($row->uid).String2Hex($rating_code_uid));
			$csi->last_activity_hard=$act->GetLastLogInfo("","setting","",$row->uid,"la_hard_".String2Hex($row->uid).String2Hex($rating_code_uid));			
			
            $retval->csi_list[] =$csi;
          }
        }
      }
    }
    return $retval;
  }
  
  public function GetHardCreditListByRatingCode($csi_uid,$rt_uid){
	  $retval = array();
      $inSql="SELECT * FROM `csi_credits` WHERE `csi_uid`=:csiuid AND `credit_type`='hard' AND `rating_code_uid`=:rating_code_uid";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':csiuid',$csi_uid,PDO::PARAM_STR);
		$sth->bindParam(':rating_code_uid',$rt_uid,PDO::PARAM_STR);
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $c = new stdClass;
            $c->uid = String2Hex($row->uidx);
			$cr = new Credit(); 
			$cinf = $cr->CreditInfo($row->credit_uid);
            $c->credit = $cinf->credit;
            $retval[] =$c;
          }
        }
      }
	return $retval;
  }
    
  public function GetSoftCreditListByRatingCode($csi_uid,$rt_uid){
	  $retval = array();
      $inSql="SELECT * FROM `csi_credits` WHERE `csi_uid`=:csiuid AND `credit_type`='soft' AND `rating_code_uid`=:rating_code_uid";
      if($sth = $this->Db->link->prepare($inSql)){
        $sth->bindParam(':csiuid',$csi_uid,PDO::PARAM_STR);
		$sth->bindParam(':rating_code_uid',$rt_uid,PDO::PARAM_STR);		
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $c = new stdClass;
            $c->uid = String2Hex($row->uidx);
            $c->credit = $row->credit;
            $retval[] =$c;
          }
        }
      }
	return $retval;
  }	

  public function GetCsiCredits($rt_uid,$csi_uid){ 
	  $retval = array();
      $inSql="SELECT * FROM `master_credits` WHERE `rating_code_uid`=:rating_code_uid AND `status`='enable'";
      if($sth = $this->Db->link->prepare($inSql)){
		$sth->bindParam(':rating_code_uid',$rt_uid,PDO::PARAM_STR);		
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $c = new stdClass;
            $c->uid = String2Hex($row->credit_id);
            $c->credit = $row->credit;
			$cc = $this->GetCreditCsiInfo($csi_uid,$row->credit_id);
			$c->type ="";
			if($cc){
				$c->type =$cc->credit_type;
			}
            $retval[] =$c;
          }
        }
      }
	return $retval;	  
  }
  
  public function GetCreditsByCsi($rt_uid,$csi_uid){ 
	  $retval = array();
      $inSql="SELECT * FROM `csi_credits` WHERE `rating_code_uid`=:rating_code_uid AND `csi_uid`=:csi_uid";
      if($sth = $this->Db->link->prepare($inSql)){
		$sth->bindParam(':rating_code_uid',$rt_uid,PDO::PARAM_STR);	
		$sth->bindParam(':csi_uid',$csi_uid,PDO::PARAM_STR);			
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $c = new stdClass;
            $c->uid = $row->credit_uid;
            $c->credit = $row->credit;
			$cc = $this->GetCreditCsiInfo($csi_uid,$row->credit_uid);
			$c->type ="";
			if($cc){
				$c->type =$cc->credit_type;
			}
            $retval[] =$c;
          }
        }
      }
	return $retval;	  
  }  
  
  public function GetCsiCreditsByRatingCode($rt_uid,$csi_uid){ 
	  $retval = array();
      $inSql="SELECT * FROM `csi_credits` WHERE `rating_code_uid`=:rating_code_uid AND `csi_uid`=:csi_uid";
      if($sth = $this->Db->link->prepare($inSql)){
		$sth->bindParam(':rating_code_uid',$rt_uid,PDO::PARAM_STR);		
		$sth->bindParam(':csi_uid',$csi_uid,PDO::PARAM_STR);		
        $sth->execute();
        if($sth->rowCount()){
          foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $c = new stdClass;
            $c->uid = $row->credit_uid;
            $c->credit = $row->credit;
			$cc = $this->GetCreditCsiInfo($csi_uid,$row->credit_uid);
			$c->type ="";
			if($cc){
				$c->type =$cc->credit_type;
			}
            $retval[] =$c;
          }
        }
      }
	return $retval;	  
  }  
  
  private function GetCreditCsiInfo($csi_uid,$credit_id){
	  $retval = false;
      $inSql="SELECT * FROM `csi_credits` WHERE `csi_uid`=:csi AND `credit_uid`=:creditid";
      if($sth = $this->Db->link->prepare($inSql)){
		$sth->bindParam(':csi',$csi_uid,PDO::PARAM_STR);		
		$sth->bindParam(':creditid',$credit_id,PDO::PARAM_STR);		
        $sth->execute();
        if($sth->rowCount()){
			$row =  $sth->fetch(PDO::FETCH_OBJ);
			$retval=$row;
        }
      } 
	return $retval;
  }
  public function GetCsiByCode($csi_code){ 
	  $retval = false;
      $inSql="SELECT * FROM `csi_codes` WHERE `div_number`=:csicode";
      if($sth = $this->Db->link->prepare($inSql)){
		$sth->bindParam(':csicode',$csi_code,PDO::PARAM_STR);		
        $sth->execute();
        if($sth->rowCount()){
			$row =  $sth->fetch(PDO::FETCH_OBJ);
			$retval=$row;
        }
      } 
	return $retval;
  }  

  
  public function SetCreditCsiStatus($csi_uid,$credit_uid,$rc_uid,$ctype,$cstat){  
	  $retval = false;
	  
	  if($cstat=="off"){
		  $inSql="DELETE FROM `csi_credits` WHERE `csi_uid`=:csi AND `credit_uid`=:creditid AND `rating_code_uid`=:rc_uid";
		  if($sth = $this->Db->link->prepare($inSql)){
			$sth->bindParam(':csi',$csi_uid,PDO::PARAM_STR);		
			$sth->bindParam(':creditid',$credit_uid,PDO::PARAM_STR);
			$sth->bindParam(':rc_uid',$rc_uid,PDO::PARAM_STR);
			$sth->execute();
		   $retval = true;
		  } 
	  }else{
		  $cr = new Credit();
		  $cr_inf = $cr->CreditDetails($credit_uid);
		  $csi_credit_uid = GenerateUID("csi_credits","CCRD");
		  $csi = $this->GetCsiDetails($csi_uid);
		  $div_number =$csi->div_number;
		  
		  $inSql="INSERT INTO `csi_credits`(`uidx`,`csi_uid`,`div_number`,`credit`,`credit_type`,`rating_code_uid`,`credit_uid`,`date_added`) ";	
		  $inSql.="VALUES(:uidx,:csi,:div,:credit,:credit_type,:rating_code_uid,:credit_uid,NOW())";
		  if($sth = $this->Db->link->prepare($inSql)){
			$sth->bindParam(':uidx',$csi_credit_uid,PDO::PARAM_STR);		
			$sth->bindParam(':csi',$csi_uid,PDO::PARAM_STR);
			$sth->bindParam(':div',$div_number,PDO::PARAM_STR);
			$sth->bindParam(':credit',$cr_inf->credit,PDO::PARAM_STR);
			$sth->bindParam(':credit_type',$ctype,PDO::PARAM_STR);
			$sth->bindParam(':rating_code_uid',$rc_uid,PDO::PARAM_STR);
			$sth->bindParam(':credit_uid',$credit_uid,PDO::PARAM_STR);
			$sth->execute();
			$retval = true;
		  } 		  
	  }
	return $retval;
  }	
  
  public function GetAllCredits(){
    $retval = false;
    $inSql="SELECT * FROM `master_credits` WHERE `credit_cat_uid` IS NULL";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->execute();
      if($sth->rowCount()){
        $retval = array();
        foreach($sth->fetchAll(PDO::FETCH_OBJ) as $row){
            $crd = new stdClass;
            $crd->credit = $row->credit;
            $crd->credit_id = $row->credit_id;
            $crd->description = utf8_encode($row->description);
            $crd->code_type = $row->code_type;
            $retval[] = $crd;
        }
      }
    }
    return $retval;
  }

  public function GetCreditList($rating_code){
    $code_type="";
    switch ($rating_code) {
        case "estidama_pbrs":
        case "estidama_pcrs":
        case "estidama_pvrrs":
        case "estidama_prrs":
            $code_type="estidama";
        break;
        case "leed3":
            $code_type="leed3";
        break;
        case "leed4":
            $code_type="leed4";
        break;
        case "dgbr":
            $code_type="dgbr";
        break;
        case "qsas":
            $code_type="qsas";
        break;
    }


    $retval = false;
    $inSql="SELECT * FROM `master_credits` WHERE `code_type`=:ratingcode";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':ratingcode',$code_type,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $retval = $sth->fetchAll(PDO::FETCH_OBJ);
      }
    }
    return $retval;
  }
  
  public function GetCreditsByRatingCode($rating_code_uid){
    $retval = false;
    $inSql="SELECT * FROM `master_credits` WHERE `rating_code_uid`=:ratingcodeuid";
    if($sth = $this->Db->link->prepare($inSql)){
      $sth->bindParam(':ratingcodeuid',$rating_code_uid,PDO::PARAM_STR);
      $sth->execute();
      if($sth->rowCount()){
        $retval = $sth->fetchAll(PDO::FETCH_OBJ);
      }
    }
    return $retval;
  }  
  
  
  
  
  private function GenerateCreditHeader($credit_code){
      $tmp_str = $credit_code;
      $slen = strlen($tmp_str);
      $htmp ="";
      for($i=0;$i<=$slen;$i++){
          $tmpc = $tmp_str[$i];
          if((is_numeric($tmpc))||($tmpc=="-")){
            break;
          }else{
            $htmp.=$tmpc;
          }
      }
      return $htmp;
  }

  public function GenerateUID($tablename,$prefix){
		$new_id_temp="";
    $this->Db = new DatabasePDO("ACC");
		if($sth = $this->Db->link->prepare("SHOW TABLE STATUS LIKE '".$tablename."'")){
			$sth->execute();
			$row=$sth->fetchObject();
			$next_id = $row->Auto_increment;
			$new_id = $prefix.str_pad( $next_id,6,"0",STR_PAD_LEFT);
			$new_id_temp = $new_id."-".$this->GenRandomNum();
		}
		return $new_id_temp;
	}
  private function GenRandomNum($len = 8){
      $pass = '';
      $lchar = 0;
      $char = 0;
      for($i = 0; $i < $len; $i++){
          while($char == $lchar){
              $char = rand(48, 109);
              if($char > 57) $char += 7;
              if($char > 90) $char += 6;
          }
          $pass .= chr($char);
          $lchar = $char;
      }
      return $pass;
  }

}

?>
