<?php
class MFCronTable extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Task', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Tasks', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		) );
		
	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_requests( $per_page = 20, $page_number = 1 ) {
		return magforest_crons();
	}




	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		
			return  count( magforest_crons() );
		
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No scheduled tasks.', 'magforest' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			default:
				return nl2br(print_r( $item, true )); //Show the whole array for troubleshooting purposes
		}
	}

	
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_task( $item ) {

		$delete_nonce = wp_create_nonce( 'magforest_kill_task' );

		$content = '';
		
		if($item['args'][0]['import_tag'] > 0 && ($tg = magforest_tag_with_id($item['args'][0]['import_tag'])))
			$content .= "<strong>Tag:</strong>&nbsp;".$tg->name.'<br/>';
			
		if($item['args'][0]['import_publisher'] > 0 && ($pb = magforest_publisher_with_id($item['args'][0]['import_publisher'])))
			$content .= "<strong>Publisher:</strong>&nbsp;".$pb->display_name.'<br/>';
			
		if($item['args'][0]['import_category'] > 0 && ($ct = magforest_category_with_id($item['args'][0]['import_category'])))
			$content .= "<strong>Category:</strong>&nbsp;".$ct->name.'<br/>';

		if(empty($content)) {
			$content .= '<strong>All Magazines</strong><br/>';
		}

		$actions = array(
			'delete' => sprintf( '<a href="?page=%s&action=%s&iha=%s&_wpnonce=%s">Remove Task</a>', esc_attr( $_REQUEST['page'] ), 'rmtask', $item['hash'], $delete_nonce )
		);

		return $content . $this->row_actions( $actions, true );
	}

	function column_options( $item ) {


		$content = '';
		
		$content .= '<strong>Interval:</strong>&nbsp; '.wp_get_schedules()[$item['schedule']]['display'].'<br/>';
		
		if($item['args'][0]['target_category'] > 0)
			$content .= "<strong>Target Category:</strong>&nbsp;".get_cat_name($item['args'][0]['target_category']).'<br/>';

		if(!empty($item['args'][0]['append_tags'])) 
			$content .= "<strong>Add Tags:</strong>&nbsp;".$item['args'][0]['append_tags'].'<br/>';

		if($item['args'][0]['is_draft'])
			$content .= "<strong>Post Status:</strong>&nbsp;Draft<br/>";
		else
			$content .= "<strong>Post Status:</strong>&nbsp;Publish<br/>";
			
		if($item['args'][0]['create_tags'])
			$content .= "<strong>Copy Tags:</strong>&nbsp;&#10003;<br/>";
		else
			$content .= "<strong>Copy Tags:</strong>&nbsp;&#10008;<br/>";
			
		if($item['args'][0]['create_cats'])
			$content .= "<strong>Copy Categories:</strong>&nbsp;&#10003;<br/>";
		else
			$content .= "<strong>Copy Categories:</strong>&nbsp;&#10008;<br/>";

		return $content ;
	}

	function column_nexttime( $item ) {
			return magforest_get_next_cron_execution($item['ts']);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'task'    => __( 'Query', 'magforest' ),
			'options' => __('Options','magforest'),
			'nexttime'=> __('Next Sync','magforest')
			
		);

		return $columns;
	}
	/**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = array( self::get_columns(), self::get_hidden_columns(), self::get_sortable_columns(), 'name' );

		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $total_items //WE have to determine how many items to show on a page
		) );

		$this->items = self::get_requests( $total_items, 1 );
	}
	private function table_data()
    {
    	return $this->items;
    }
    
    
	
}
