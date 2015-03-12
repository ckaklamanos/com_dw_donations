<?php

/**
 * @version     1.0.0
 * @package     com_moneydonations
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charalampos Kaklamanos <dev.yesinternet@gmail.com> - http://www.yesinternet.gr
 */
// No direct access
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_dw_donations/controller.php';

class Dw_donationsControllerDwDonationReturn extends Dw_donationsController {
	
	public function get_response()
	{
		
		$transactionData=array();
		$jinput = JFactory::getApplication()->input;
		$app = JFactory::getApplication();
        $payments = $this->getModel('DwDonationForm', 'Dw_donationsModel');
		
		$transactionId=$jinput->get('t');
		$orderCode=$jinput->get('s');
		$transactionData=$this->fn_viva_request_authorization($transactionId);
		
		if(isset($transactionData['success'])){
			$transOrderCode=$transactionData['success']->Transactions[0]->Order->OrderCode;
			if($orderCode!=$transOrderCode){
				JError::raiseError(401, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
		}
		
		
		$payment_data=$app->getUserState('com_dw_donations.payment.data');
		if(isset($payment_data)){
			$payment_data=json_decode($payment_data);
		}else{
			JError::raiseError(402, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}
		
		$order_code=array('order_code'=>$orderCode);
		if($order_code['order_code']!=$payment_data->order_code)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;	
		}
		
		$time_updated = JFactory::getDate()->toSql();
		$payment = $payments->getTable();
		if($payment->load($order_code)){
			//var_dump($payment);
			if(empty($payment->transaction_id)){
				$data['id']=$payment->id;
				$data['transaction_id']=$transactionId;
				$data['state']=1;
				$data['modified']=$time_updated;
				$data['anonymous']=$payment->anonymous;
				$return=$payments->save($data);
				if ($return === false) {
					// ToDo: Error logging	
				}
			}
		}else{
			// ToDo: Error logging
		}
		
		//Notify Donor
		
		$donorwizMail = new DonorwizMail();
		$beneficiary = JFactory::getUser( $payment -> beneficiary_id );

		

		$mailParams = array();
		$mailParams['subject'] = JText::_('COM_DW_DONATIONS_EMAIL_DONOR_SUCCESS_SUBJECT') ;
		$mailParams['recipient'] = $payment->email;
		$mailParams['isHTML'] = true;
		$mailParams['layout'] = 'success_donor';
		$mailParams['layout_path'] = JPATH_ROOT .'/components/com_dw_donations/layouts/dwemails';
		$mailParams['layout_params'] = array( 'amount' => $payment -> amount , 'beneficiary' => $beneficiary -> name );
		
		$donorwizMail -> sendMail( $mailParams ) ;
			

		
		//Notify Beneficiary
		
		$donorwizMail = new DonorwizMail();
		$mailParams = array();
		$mailParams['subject'] = JText::_('COM_DW_DONATIONS_EMAIL_BENEFICIARY_SUCCESS_SUBJECT') ;
		$mailParams['recipient'] = $beneficiary->email;
		$mailParams['isHTML'] = true;
		$mailParams['layout'] = 'success_beneficiary';
		$mailParams['layout_path'] = JPATH_ROOT .'/components/com_dw_donations/layouts/dwemails';
		$mailParams['layout_params'] = array( 'amount' => $payment -> amount , 'donor' => $payment -> fname.' '.$payment -> lname );

		$donorwizMail -> sendMail( $mailParams ) ;
		

		//Notify beneficiary via messaging system ---------------------------------------------------------------------
		
		// JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dw_opportunities/models', 'Dw_opportunitiesModel');
		// $opportunityModel = JModelLegacy::getInstance('DwOpportunity', 'Dw_opportunitiesModel', array('ignore_request' => true));	
		// $opportunity = $opportunityModel -> getData( $data['opportunity_id']);

		// $donorwizMessaging = new DonorwizMessaging();
		
		// $messageParams = array();
		// $messageParams['actor_id'] = CFactory::getUser() -> id;
		// $messageParams['target'] = $opportunity -> created_by;
		// $messageParams['opportunity_title'] = $opportunity -> title;
		// $messageParams['link'] = JRoute::_('index.php?option=com_donorwiz&view=dashboard&layout=dwopportunity&Itemid=298&id='.$opportunity -> id).'#opportunityresponse'.$data['id'];
		// $messageParams['subject'] = $opportunity->title.': '.JText::_('COM_DW_OPPORTUNITIES_RESPONSES_NEW_RESPONSE_NOTIFICATION_SUBJECT');
		// $messageParams['body'] = JText::_('COM_DW_OPPORTUNITIES_RESPONSES_NEW_RESPONSE_NOTIFICATION_BODY');
		
		// $donorwizMessaging -> sendNotification ( $messageParams ) ;
		
		//----------------------------------------------------------------------------------------------------------------------------------

		
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url = (empty($item->link) ? 'index.php?option=com_dw_donations&view=dwdonationsuccess' : $item->link);
		$app->setUserState('com_moneydonations.payment.data', json_encode($payment));
		$this->setRedirect(JRoute::_($url, false));

	}
	
	private function fn_viva_request_authorization($transactionId)
	{
		
		$request =  'http://demo.vivapayments.com/api/transactions/';	// demo environment URL
		//$request =  'https://www.vivapayments.com/api/transactions';	// production environment URL
		
		// Your merchant ID and API Key can be found in the 'Security' settings on your profile.
		$MerchantId = '1ef183eb-94de-44dd-b682-3c404f74a267';
		$APIKey = 'vivavaskou';
		//Set the ID of the Initial Transaction
		$request .= $transactionId;
		
		// Get the curl session object
		$session = curl_init($request);
		// Set query data here with the URL
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$APIKey);
		$response = curl_exec($session);
		curl_close($session);
		
		// Parse the JSON response
		try {
			$resultObj=json_decode($response);
		} catch( Exception $e ) {
			return array('error'=>$e->getMessage());	
		}
		
		if ($resultObj->ErrorCode==0){
			// print JSON output
			return array('success'=>$resultObj);
		}
		else{
			return array('error'=>$resultObj->ErrorText);
		}
	
	}
}