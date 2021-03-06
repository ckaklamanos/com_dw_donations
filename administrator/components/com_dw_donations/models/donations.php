<?php

/**
 * @version     1.1.0
 * @package     com_dw_donations
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charalampos Kaklamanos <dev.yesinternet@gmail.com> - http://www.yesinternet.gr
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Dw_donations records.
 */
class Dw_donationsModelDonations extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'created_by', 'a.created_by',
                'created', 'a.created',
                'modified', 'a.modified',
                'donor_id', 'a.donor_id',
                'beneficiary_id', 'a.beneficiary_id',
                'fname', 'a.fname',
                'lname', 'a.lname',
                'email', 'a.email',
                'amount', 'a.amount',
                'country', 'a.country',
                'anonymous', 'a.anonymous',
                'payment_method', 'a.payment_method',
                'order_code', 'a.order_code',
                'transaction_id', 'a.transaction_id',
                'parameters', 'a.parameters',
                'language', 'a.language',

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        
		//Filtering created
		$this->setState('filter.created.from', $app->getUserStateFromRequest($this->context.'.filter.created.from', 'filter_from_created', '', 'string'));
		$this->setState('filter.created.to', $app->getUserStateFromRequest($this->context.'.filter.created.to', 'filter_to_created', '', 'string'));

		//Filtering modified
		$this->setState('filter.modified.from', $app->getUserStateFromRequest($this->context.'.filter.modified.from', 'filter_from_modified', '', 'string'));
		$this->setState('filter.modified.to', $app->getUserStateFromRequest($this->context.'.filter.modified.to', 'filter_to_modified', '', 'string'));

		//Filtering donor_id
		$this->setState('filter.donor_id', $app->getUserStateFromRequest($this->context.'.filter.donor_id', 'filter_donor_id', '', 'string'));

		//Filtering beneficiary_id
		$this->setState('filter.beneficiary_id', $app->getUserStateFromRequest($this->context.'.filter.beneficiary_id', 'filter_beneficiary_id', '', 'string'));

		//Filtering country
		$this->setState('filter.country', $app->getUserStateFromRequest($this->context.'.filter.country', 'filter_country', '', 'string'));

		//Filtering payment_method
		$this->setState('filter.payment_method', $app->getUserStateFromRequest($this->context.'.filter.payment_method', 'filter_payment_method', '', 'string'));


        // Load the parameters.
        $params = JComponentHelper::getParams('com_dw_donations');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.state', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'DISTINCT a.*'
                )
        );
        $query->from('`#__dw_donations` AS a');

        
		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.state LIKE '.$search.'  OR  a.country LIKE '.$search.' )');
            }
        }

        

		//Filtering created
		$filter_created_from = $this->state->get("filter.created.from");
		if ($filter_created_from) {
			$query->where("a.created >= '".$db->escape($filter_created_from)."'");
		}
		$filter_created_to = $this->state->get("filter.created.to");
		if ($filter_created_to) {
			$query->where("a.created <= '".$db->escape($filter_created_to)."'");
		}

		//Filtering modified
		$filter_modified_from = $this->state->get("filter.modified.from");
		if ($filter_modified_from) {
			$query->where("a.modified >= '".$db->escape($filter_modified_from)."'");
		}
		$filter_modified_to = $this->state->get("filter.modified.to");
		if ($filter_modified_to) {
			$query->where("a.modified <= '".$db->escape($filter_modified_to)."'");
		}

		//Filtering donor_id

		//Filtering beneficiary_id

		//Filtering country
		$filter_country = $this->state->get("filter.country");
		if ($filter_country) {
			$query->where("a.country = '".$db->escape($filter_country)."'");
		}

		//Filtering payment_method
		$filter_payment_method = $this->state->get("filter.payment_method");
		if ($filter_payment_method != '') {
			$query->where("a.payment_method = '".$db->escape($filter_payment_method)."'");
		}


        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();
        
		foreach ($items as $oneItem) {

			if (isset($oneItem->donor_id)) {
				$values = explode(',', $oneItem->donor_id);

				$textValue = array();
				foreach ($values as $value){
					if(!empty($value)){
						$db = JFactory::getDbo();
						$query = "SELECT id,name FROM #__users HAVING id LIKE '" . $value . "'";
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->name;
						}
					}
				}

			$oneItem->donor_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->donor_id;

			}

			if (isset($oneItem->beneficiary_id)) {
				$values = explode(',', $oneItem->beneficiary_id);

				$textValue = array();
				foreach ($values as $value){
					if(!empty($value)){
						$db = JFactory::getDbo();
						$query = "SELECT id,name FROM #__users HAVING id LIKE '" . $value . "'";
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->name;
						}
					}
				}

			$oneItem->beneficiary_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->beneficiary_id;

			}
					$oneItem->country = JText::_('COM_DW_DONATIONS_DONATIONS_COUNTRY_OPTION_' . strtoupper($oneItem->country));
					$oneItem->payment_method = JText::_('COM_DW_DONATIONS_DONATIONS_PAYMENT_METHOD_OPTION_' . strtoupper($oneItem->payment_method));
		}
        return $items;
    }

}
