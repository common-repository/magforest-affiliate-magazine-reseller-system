<?php
class MagazineTable extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Magazine', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Magazines', 'sp' ), //plural name of the listed records
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

		if(isset($_GET['mag_search'])) {
			return magforest_get_products($page_number, trim($_GET['mag_search']));
		} else {
			return magforest_get_products($page_number);
		}
	}




	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		if(isset($_GET['mag_search'])) {
			return  magforest_get_count(trim($_GET['mag_search']));
		} else {
			return  magforest_get_count();
		}
		
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No magazines.', 'magforest' );
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
			case 'photo':
					return "<img alt=\"thumbnail\" style=\"max-width:100%;max-height:500px;\" src=\"".esc_attr($item->info->thumbnail)."\" />";
				break;
			
			default:
				return nl2br(print_r( $item, true )); //Show the whole array for troubleshooting purposes
		}
	}

	
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="import_include[]" value="%s" />', absint( $item->info->id )
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'magforest_copy_post' );
		$template_nonce = wp_create_nonce( 'magforest_create_bulk' );

		$title = '<strong>' . $item->info->title . '</strong>';

		$actions = array(
			/*'template' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Create Template Post</a>', esc_attr( $_REQUEST['page'] ), 'template', absint( $item->info->id ), $template_nonce ),*/
		/*	'copy' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Create link post</a>', esc_attr( $_REQUEST['page'] ), 'copy', absint( $item->info->id ), $delete_nonce ), */
		
		'template' => sprintf( '<a href="?action=%s&page=%s&import_include[]=%s&_wpnonce_quick=%s&cat=0&import_state=publish&import_create_tags=yes&import_create_categories=yes&import_append_tags=&import_tag=&import_publisher=&import_category=&go=Import&paged=%s&was_paged=%s">Create Template Post</a>', 'bulk',esc_attr( $_REQUEST['page'] ), absint( $item->info->id ), $template_nonce,esc_attr( $_REQUEST['paged'] ),esc_attr( $_REQUEST['paged'] ) ),
			'visit' =>  sprintf( '<a href="%s">View on Magforest</a>', esc_attr( $item->info->link ))
		);

		return $title . $this->row_actions( $actions, true );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb' => __('Select','magforest'),
			'name'    => __( 'Title', 'magforest' ),
			'photo' => __('Thumbnail','magforest')
			
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

		$per_page     = $this->get_items_per_page( 'requests_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		) );

		$this->items = self::get_requests( $per_page, $current_page );
	}
	private function table_data()
    {
    	return $this->items;
    }
    
    
	
}
