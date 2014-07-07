<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once realpath(dirname(__FILE__)) . '/classes/ElasticsearchHelper.php';
require_once realpath(dirname(__FILE__)) . '/classes/ElasticsearchUtils.php';

/**
 * Fulltextsearch component controller.
 *
 * @category   OntoWiki
 * @package    Extensions_Fulltextsearch
 * @author     Sebastian Nuck
 * @copyright  Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FulltextsearchController extends OntoWiki_Controller_Component
{
    
    public function fulltextsearchAction() {
        
        // tells the OntoWiki to not apply the template to this action
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $store = $this->_erfurt->getStore();
        $this->_erfurt->authenticate();

        $translate = $this->_owApp->translate;
        
        if ($this->_request->query !== null) {
            $searchText = htmlspecialchars(trim($this->_request->query));
        }
        
        $error = false;
        $errorMsg = '';
        
        // check if search already contains an error
        if (!$error) {
            $esHelper = new ElasticsearchHelper($this->_privateConfig);
            
            // $esHelper = new ElasticsearchHelperOld();
            $result = $esHelper->search($searchText);
        }
        
        // if error occured set output for error page
        if ($error) {
            
            $this->view->errorMsg = $errorMsg;
        } else {
            
            // set redirect to effective search controller
            //$url = new OntoWiki_Url(array('controller' => 'list'), array());
            //$url->setParam('s', $searchText);
            //$url->setParam('init', '1');
            //$this->_redirect($url);
            
            
        }
        
        $this->_response->setBody(json_encode($result));
    }
    
    public function searchAction() {
        OntoWiki::getInstance()->logger->info('searchAction');
        $translate = $this->_owApp->translate;

        $this->view->placeholder('main.window.title')->set($translate->_('Fulltext Search'));
        $this->addModuleContext('main.window.fulltextsearch.search');
        $store = $this->_erfurt->getStore();
        $this->_erfurt->authenticate();
        
        $params = $this->_request->getParams();
        $input = $params['input'];
        
        $indices = '';
        if (isset($params['indices'])) {
            $indices = $params['indices'];
        }
        $this->view->input = $input;
        
        $esHelper = new ElasticsearchHelper($this->_privateConfig);
        
        $result = $esHelper->searchAndReturnEverything($input, $indices);
        $this->view->jsonResult = $result['resultSet'];

        $this->view->availableIndices = $esHelper->getAvailableIndices(); 

        // transform comma separated indices to array
        $this->view->selectedIndices = str_getcsv($indices); 
        
        $this->view->resultArray = ElasticsearchUtils::extractResults($result['resultSet']);
        $this->view->query = $result['query'];
        
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
    }
}
