<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Recent extends skylight {

    function __construct() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {

        $configured_fields = $this->config->item('skylight_fields');
        $configured_filters = $this->config->item('skylight_filters');
        $configured_date_filters = $this->config->item('skylight_date_filters');
        $delimiter = $this->config->item('skylight_filter_delimiter');
        $recorddisplay = $this->config->item('skylight_recorddisplay');
        $display_thumbnail = $this->config->item('skylight_display_thumbnail');
        $thumbnail_field = $this->config->item('skylight_thumbnail_field');

        // TODO: get rid of this, it's bad
        $title = $this->skylight_utilities->getField('Title');

        $recentitems = $this->solr_client->getRecentItems();
        $data['recentitems'] = $recentitems['recent_items'];
        $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
        $data['delimiter'] = $delimiter;
		
        // Check for zero results
        if (count($recentitems['recent_items']) == 0) {
            $this->view('recent_items_none');
            return;
        }

        if(array_key_exists('Author', $recorddisplay)) {
            $data['author_field'] = $recorddisplay['Author'];
        } else {
            $data['author_field'] = 'dccreator';
        }

        $data['hide_header'] = true;
        $data['display_thumbnail'] = $display_thumbnail;
        $data['thumbnail_field'] = 'solr_'.str_replace('.','',$thumbnail_field);
        $this->view('recent_items', $data);
    }


}