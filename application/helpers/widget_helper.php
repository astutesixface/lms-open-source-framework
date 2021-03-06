<?php 
/*******************************************************************************/
/* Purpose 		: Managing the widget related request and response.
/* Created By 	: Jaiswar Vipin Kumar R.
/*******************************************************************************/
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget{
	private $_databaseObject	= null;
	private $_intCompanyCode	= 0;
	private $_strTableName		= "master_widget";
	private $_frameworkObj		= '';
	/***************************************************************************/
	/* Purpose	: Initialization
	/* Inputs 	: pDatabaesObjectRefrence :: Database object reference,
				: $pIntCompanyCode :: company code
	/* Returns	: None.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function __construct($pDatabaesObjectRefrence, $pIntCompanyCode = 0){
		/* database reference */
		$this->_databaseObject	= $pDatabaesObjectRefrence;
		/* Company Code */
		$this->_intCompanyCode	= $pIntCompanyCode;
		/* CI instance reference */
		$this->_frameworkObj =& get_instance();
	}
	
	/***************************************************************************/
	/* Purpose	: get widget list.
	/* Inputs 	: None
	/* Returns	: widegt array details.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getWidgetList(){
		/* Query builder Array */
		$strFilterArr	= array(
									'table'=>$this->_strTableName,
									'where'=>array(),
									'column'=>array('id', 'description')
							);
		
		/* getting record from location */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr);
	}
	
	/***************************************************************************/
	/* Purpose	: Get dynamic form based on attributes assigned to the same widget.
	/* Inputs 	: $pStrModuleURL :: Module URL,
				: $pIsFormNeeded :: If form needed, if not hen returns only records. 
	/* Returns	: Dynamic Width Form
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getWidgetAttributesWithLayout($pStrModuleURL = '', $pIsFormNeeded = true){
		/* Variable initialization */
		$strReturmHTML	= '<div class="row">';
		
		if($pStrModuleURL == ''){
			/* Return dynamic Form HTML */
			return $strReturmHTML.'</div>';
		}
		/* Creating lead instance object */
		$leadObj		= new Lead($this->_databaseObject, $this->_intCompanyCode);
		/* Get the 	attributes assign to the requested module */
		$strLeadAttrArr	=  $leadObj->getLeadAttributesListByModuleUrl($pStrModuleURL);
		/* Removed used variables */
		unset($leadObj);
		
		/* if form not needed then return only records */
		if(!$pIsFormNeeded){
			/* Return records */
			return $strLeadAttrArr;
		}
		
		/* if no attributes assigned then do needful */
		if(empty($strLeadAttrArr)){
			/* Return dynamic Form HTML */
			return $strReturmHTML.'</div>';
		}
		
		$strReturmHTML.=	'<form name="frmAddNewLead" id="frmAddNewLead" method="post" action="'.SITE_URL.'leadsoperation/leadsoperation/setNewLeadDetails">';
		
		/* iterating the loop */ 
		foreach($strLeadAttrArr as $strLeadAttrArrKey => $strLeadAttrArrValue){
			/* Variable initialization */
			$strMandatory	= '';
			/* checking for mandatory */
			if($strLeadAttrArrValue['is_mandatory'] == 1){
				/* Value over ridding */
				$strMandatory	= '*';
			}
			
			/* Checking the attribute type */
			switch($strLeadAttrArrValue['attri_data_type']){
				case 'textbox':
					$strReturmHTML.=	'<div class="input-field col s12 no-search">
											<input class="validate" type="text" name="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" id="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" data-set="'.$strLeadAttrArrValue['attri_slug_key'].'" />
											<label for="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'">Enter '.$strLeadAttrArrValue['attri_slug_name'].' '.$strMandatory.'</label>
										</div>';
					break;
				case 'checbox':
					break;
				case 'radio':
					break;
				case 'dropdown':
					$strReturmHTML.=	'<div class="input-field col s12 no-search">
											<select name="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" id="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" data-set="'.$strLeadAttrArrValue['attri_slug_key'].'">'.$strLeadAttrArrValue['attri_value_list'].'</select>
											<label for="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'">Select '.$strLeadAttrArrValue['attri_slug_name'].' '.$strMandatory.'</label>
										</div>';
					break;
				case 'select':
					$strItemArr	   = (empty(unserialize($strLeadAttrArrValue['attri_value_list']))?array(): unserialize($strLeadAttrArrValue['attri_value_list']));
					/* if option list found then do needful */
					if(!empty($strItemArr)){
						/* Iterating the loop */
						foreach($strItemArr as $strItemArrKey => $strItemArrValue){
							/* Setting the new value a key */
							$strItemArr[$strItemArrValue]	= $strItemArrValue;	
							/* removing the original index */
							unset($strItemArr[$strItemArrKey]);	
						}
					}
					/* Creating form object */
					$objForm	= new Form();
					/* Creating the dropdown index */
					$strReturmHTML.=	'<div class="input-field col s12 no-search">
											<select name="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" id="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'" data-set="'.$strLeadAttrArrValue['attri_slug_key'].'">'.$objForm->getDropDown($strItemArr, '').'</select>
											<label for="txtWidget'.$strLeadAttrArrValue['attri_slug_key'].'">Select '.$strLeadAttrArrValue['attri_slug_name'].' '.$strMandatory.'</label>
										</div>';
					/* removed used variables */
					unset($objForm);
					
					break;
			}
		}
		
		/* Creating lead source instance object */
		$leadSourceObj	= new leadSource($this->_databaseObject, $this->_intCompanyCode);
		/* Get the lead source by role code */
		$strLeadSource	=  $leadSourceObj->getLeadSourceByCompanyCode();
		/* Removed used variables */
		unset($leadSourceObj);
		
		/* Creating form object */
		$objForm	= new Form();
		
		/* if add new lead panel request then add lead source option as well */
		if($pStrModuleURL == ADD_NEW_LEAD_MODULE_URL){
			/* setting led source */
			$strReturmHTML.=	'<div class="input-field col s12 no-search">
									<select name="cboWidgetLeadSource" id="cboWidgetLeadSource" data-set="lead_source_code">'.$objForm->getDropDown(getArrByKeyvaluePairs($strLeadSource,'id','description')).'</select>
									<label for="cboWidgetLeadSource">Select Lead source *</label>
								</div>';
		}
		
		/* removed used variables */
		unset($objForm);
										
		/* Closing the form */
		$strReturmHTML.=	'</form>';
		
		/* removed sued variables */
		unset($strLeadAttrArr);
		
		/* Return dynamic Form HTML */
		return $strReturmHTML.'</div>';
	}
	
	/***************************************************************************/
	/* Purpose	: Get module column as search panel.
	/* Inputs 	: $pStrColumnArray :: Column Array. 
	/* Returns	: Search HTML of respective panel.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getColumnAsSearchPanel($pStrColumnArray = array()){
		/* Variable initialization */
		$strDisplayClass	= '';
		$strElementPrefix	= 'txtProfile';
		
		/* if form index set then do needful */
		if(isset($pStrColumnArray['frmName'])){
			/* Set the class */
			$strDisplayClass	= 'no-add';
			$strElementPrefix	= 'txtSearch';
		}
		
		/* Variable initialization */
		$strReturmHTML	= '<div class="row '.$strDisplayClass.'">';
		
		/* if column array is empty then of needful */
		if(empty($pStrColumnArray)){
			/* Return empty HTML */
			return $strReturmHTML.'</div>';
		}
		
		/* if form object is set then do needful */
		if(isset($pStrColumnArray['frmName'])){
			$strReturmHTML.=	'<form name="'.$pStrColumnArray['frmName'].'" id="'.$pStrColumnArray['frmName'].'" method="post" action="'.$pStrColumnArray['action'].'">';
		}
		
		/* removed not required index */
		unset($pStrColumnArray['action']);
		
		
		/* Iterating the loop */
		foreach($pStrColumnArray as $pStrColumnArrayKey => $pStrColumnArrayValue){
			/* if column index is not set the do not render that element */
			if(!isset($pStrColumnArrayValue['column'])){
				/* Set the pointer to next index */
				continue;
			}
			
			/* disabled flag */
			$strDisabled	= '';
			/* checking disabled flag set */
			if(isset($pStrColumnArrayValue['disabled'])){
				/* Set the disabled flag */
				$strDisabled	= 'disabled="disabled" ';
			}
			
			/* Checking for element type */
			if(isset($pStrColumnArrayValue['is_date'])){
				/* Checking for date range element */
				if((string)$pStrColumnArrayKey == 'date_range'){
					$strReturmHTML.=	'<div class="input-field col s12">
											<label for="'.$strElementPrefix.'FromDate">From Date</label>
											<input type="text" name="'.$strElementPrefix.'FromDate" id="'.$strElementPrefix.'FromDate" class="datepicker" '.$strDisabled.'/>
										</div>
										<div class="input-field col s12">
												<label for="'.$strElementPrefix.'ToDate">To Date</label>
												<input type="text" name="'.$strElementPrefix.'ToDate" id="'.$strElementPrefix.'ToDate" class="datepicker" '.$strDisabled.'/>
										</div>';
				}
			}else if(isset($pStrColumnArrayValue['dropdown'])){
				$strReturmHTML.=	'<div class="input-field col s12">
										<select name="'.$strElementPrefix.$pStrColumnArrayValue['column'].'" id="'.$strElementPrefix.$pStrColumnArrayValue['column'].'" data-set="'.$pStrColumnArrayValue['column'].'" '.$strDisabled.'>'.$pStrColumnArrayValue['data'].'</select>
										<label for="'.$strElementPrefix.$pStrColumnArrayValue['column'].'">Select '.$pStrColumnArrayValue['label'].'</label>
									</div>';
			}else{
					$strReturmHTML.=	'<div class="input-field col s12">
											<input class="validate" type="text" name="'.$strElementPrefix.$pStrColumnArrayValue['column'].'" id="'.$strElementPrefix.$pStrColumnArrayValue['column'].'" data-set="'.$pStrColumnArrayValue['column'].'" '.$strDisabled.'/>
											<label for="'.$strElementPrefix.$pStrColumnArrayValue['column'].'">Enter '.$pStrColumnArrayValue['label'].'</label>
										</div>';
			}
		}
		
		/* if form object is set then do needful */
		if(isset($pStrColumnArray['frmName'])){
			/* Closing the form */
			$strReturmHTML.=	'<input type="hidden" name="txtSearch" id="txtSearch" value="" /></form>';
		}
		
		/* Return dynamic Form HTML */
		return $strReturmHTML.'</div>';
	}
}